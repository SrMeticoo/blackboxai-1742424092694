<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Get parameters
    $item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
    $count = isset($_GET['count']) ? (int)$_GET['count'] : 1;

    // Validate parameters
    if ($item_id <= 0 || $count <= 0) {
        throw new Exception('Invalid parameters');
    }

    // Get available balls
    $stmt = $pdo->prepare("
        SELECT number 
        FROM balls 
        WHERE item_id = ? AND status = 'available'
        ORDER BY RAND()
        LIMIT ?
    ");
    $stmt->execute([$item_id, $count]);
    $balls = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($balls)) {
        throw new Exception('No hay balotas disponibles');
    }

    echo json_encode([
        'success' => true,
        'numbers' => $balls,
        'count' => count($balls)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}