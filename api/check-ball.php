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

    // Get ball status
    $stmt = $pdo->prepare("
        SELECT status, user_name, user_lastname 
        FROM balls 
        WHERE item_id = ? AND number = ?
    ");
    $stmt->execute([$item_id, $number]);
    $ball = $stmt->fetch();

    if (!$ball) {
        throw new Exception('Ball not found');
    }

    echo json_encode([
        'success' => true,
        'status' => $ball['status'],
        'user_name' => $ball['user_name'],
        'user_lastname' => $ball['user_lastname']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}