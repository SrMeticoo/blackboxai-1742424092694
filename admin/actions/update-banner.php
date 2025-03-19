<?php
require_once '../../includes/config.php';

// Check if user is logged in
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

try {
    // Get POST data
    $image_url = $_POST['image_url'] ?? '';
    $title = $_POST['title'] ?? '';

    // Validate data
    if (empty($image_url) || empty($title)) {
        throw new Exception('Por favor complete todos los campos requeridos');
    }

    // Validate image URL
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        throw new Exception('La URL de la imagen no es válida');
    }

    // Update banner
    $stmt = $pdo->prepare("
        UPDATE banner 
        SET image_url = ?,
            title = ?,
            updated_at = NOW()
        WHERE id = (SELECT id FROM (SELECT id FROM banner ORDER BY id DESC LIMIT 1) as t)
    ");

    $stmt->execute([$image_url, $title]);

    // If no rows were updated, insert new banner
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO banner (image_url, title)
            VALUES (?, ?)
        ");

        $stmt->execute([$image_url, $title]);
    }

    $_SESSION['success'] = 'Banner actualizado exitosamente';

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../dashboard.php');
exit();