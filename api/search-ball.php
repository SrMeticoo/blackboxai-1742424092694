<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Get parameters
    $item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
    $number = isset($_GET['number']) ? $_GET['number'] : '';

    // Validate parameters
    if ($item_id <= 0 || empty($number)) {
        throw new Exception('Invalid parameters');
    }

    // Format the number to match the database format (with leading zeros)
    $formatted_number = str_pad($number, 5, '0', STR_PAD_LEFT);

    // Search for the ball
    $stmt = $pdo->prepare("
        SELECT b.number, b.status, b.user_name, b.user_lastname, b.user_phone, b.reserved_at,
               r.name as item_name
        FROM balls b
        JOIN raffle_items r ON b.item_id = r.id
        WHERE b.item_id = ? AND b.number LIKE ?
    ");
    $stmt->execute([$item_id, "%$formatted_number%"]);
    $balls = $stmt->fetchAll();

    if (empty($balls)) {
        echo json_encode([
            'success' => true,
            'ball' => null,
            'message' => 'No se encontraron balotas con ese número'
        ]);
        exit;
    }

    // Return the first matching ball
    $ball = $balls[0];
    
    // Format the response
    $response = [
        'number' => $ball['number'],
        'status' => $ball['status'],
        'item_name' => $ball['item_name']
    ];

    // Add user information if the ball is reserved
    if ($ball['status'] === 'reserved' || $ball['status'] === 'blocked') {
        $response['user_name'] = $ball['user_name'];
        $response['user_lastname'] = $ball['user_lastname'];
        $response['user_phone'] = $ball['user_phone'];
        $response['reserved_at'] = $ball['reserved_at'];
    }

    echo json_encode([
        'success' => true,
        'ball' => $response
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}