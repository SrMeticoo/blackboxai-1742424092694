<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Get parameters
    $item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
    $start = isset($_GET['start']) ? (int)$_GET['start'] : 1;
    $end = isset($_GET['end']) ? (int)$_GET['end'] : 50;

    // Validate parameters
    if ($item_id <= 0) {
        throw new Exception('Invalid item ID');
    }

    // Get balls for the specified range
    $stmt = $pdo->prepare("
        SELECT number, status, user_name, user_lastname 
        FROM balls 
        WHERE item_id = ? AND CAST(number AS UNSIGNED) BETWEEN ? AND ?
        ORDER BY CAST(number AS UNSIGNED)
    ");
    $stmt->execute([$item_id, $start, $end]);
    $balls = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'balls' => $balls
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}