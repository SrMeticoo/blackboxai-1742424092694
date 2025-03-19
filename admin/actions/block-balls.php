<?php
require_once '../../includes/config.php';

// Check if user is logged in
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid request data');
    }

    $item_id = (int)($data['item_id'] ?? 0);
    $numbers = $data['numbers'] ?? [];

    // Validate data
    if ($item_id <= 0 || empty($numbers)) {
        throw new Exception('Invalid parameters');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Verify all balls exist and are reserved
        $placeholders = str_repeat('?,', count($numbers) - 1) . '?';
        $params = array_merge([$item_id], $numbers);
        
        $stmt = $pdo->prepare("
            SELECT number, status 
            FROM balls 
            WHERE item_id = ? AND number IN ($placeholders)
        ");
        $stmt->execute($params);
        $balls = $stmt->fetchAll();

        foreach ($balls as $ball) {
            if ($ball['status'] !== 'reserved') {
                throw new Exception("Ball {$ball['number']} is not in reserved status");
            }
        }

        // Update balls status to blocked
        $stmt = $pdo->prepare("
            UPDATE balls 
            SET status = 'blocked',
                updated_at = NOW()
            WHERE item_id = ? AND number IN ($placeholders)
        ");

        $stmt->execute($params);

        // Commit transaction
        $pdo->commit();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Balotas marcadas como pagadas exitosamente'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}