<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }

    // Validate required fields
    $required_fields = ['item_id', 'numbers', 'user'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $item_id = (int)$data['item_id'];
    $numbers = $data['numbers'];
    $user = $data['user'];

    // Validate user data
    if (empty($user['nombre']) || empty($user['apellido']) || empty($user['telefono'])) {
        throw new Exception('Missing user information');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Check if all balls are still available
        $placeholders = str_repeat('?,', count($numbers) - 1) . '?';
        $params = array_merge([$item_id], $numbers);
        
        $stmt = $pdo->prepare("
            SELECT number, status 
            FROM balls 
            WHERE item_id = ? AND number IN ($placeholders)
        ");
        $stmt->execute($params);
        $balls = $stmt->fetchAll();

        // Verify all balls exist and are available
        foreach ($balls as $ball) {
            if ($ball['status'] !== 'available') {
                throw new Exception("Ball {$ball['number']} is not available");
            }
        }

        // Get system configuration
        $stmt = $pdo->query("SELECT auto_release_enabled, auto_release_minutes FROM system_config LIMIT 1");
        $config = $stmt->fetch();

        // Calculate auto-release time if enabled
        $release_time = null;
        if ($config['auto_release_enabled']) {
            $release_time = date('Y-m-d H:i:s', strtotime("+{$config['auto_release_minutes']} minutes"));
        }

        // Update balls status
        $stmt = $pdo->prepare("
            UPDATE balls 
            SET status = 'reserved',
                user_name = ?,
                user_lastname = ?,
                user_phone = ?,
                reserved_at = NOW(),
                release_at = ?
            WHERE item_id = ? AND number IN ($placeholders)
        ");

        $params = [
            $user['nombre'],
            $user['apellido'],
            $user['telefono'],
            $release_time,
            $item_id
        ];
        $params = array_merge($params, $numbers);

        $stmt->execute($params);

        // If auto-release is enabled, schedule the release
        if ($config['auto_release_enabled']) {
            // Note: In a real production environment, you would set up a cron job or
            // use a task scheduler to handle the auto-release functionality
            // For now, we'll just store the release time in the database
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reserva realizada exitosamente'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}