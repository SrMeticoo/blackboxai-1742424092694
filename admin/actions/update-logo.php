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

    // Validate data
    if (empty($image_url)) {
        throw new Exception('Por favor ingrese la URL del logo');
    }

    // Validate image URL
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        throw new Exception('La URL de la imagen no es válida');
    }

    // Update logo
    $stmt = $pdo->prepare("
        UPDATE logo 
        SET image_url = ?,
            updated_at = NOW()
        WHERE id = (SELECT id FROM (SELECT id FROM logo ORDER BY id DESC LIMIT 1) as t)
    ");

    $stmt->execute([$image_url]);

    // If no rows were updated, insert new logo
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO logo (image_url)
            VALUES (?)
        ");

        $stmt->execute([$image_url]);
    }

    $_SESSION['success'] = 'Logo actualizado exitosamente';

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../dashboard.php');
exit();