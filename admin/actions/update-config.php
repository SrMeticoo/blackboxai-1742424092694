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
    $country_code = $_POST['country_code'] ?? '';
    $whatsapp_number = $_POST['whatsapp_number'] ?? '';
    $auto_release_enabled = isset($_POST['auto_release_enabled']) ? 1 : 0;
    $auto_release_minutes = (int)($_POST['auto_release_minutes'] ?? 10);

    // Validate data
    if (empty($country_code) || empty($whatsapp_number)) {
        throw new Exception('Por favor complete todos los campos requeridos');
    }

    if ($auto_release_enabled && $auto_release_minutes <= 0) {
        throw new Exception('El tiempo de liberación automática debe ser mayor a 0 minutos');
    }

    // Clean whatsapp number (remove any non-numeric characters)
    $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_number);

    // Update configuration
    $stmt = $pdo->prepare("
        UPDATE system_config 
        SET country_code = ?,
            whatsapp_number = ?,
            auto_release_enabled = ?,
            auto_release_minutes = ?,
            updated_at = NOW()
        WHERE id = (SELECT id FROM (SELECT id FROM system_config ORDER BY id DESC LIMIT 1) as t)
    ");

    $stmt->execute([
        $country_code,
        $whatsapp_number,
        $auto_release_enabled,
        $auto_release_minutes
    ]);

    // If no rows were updated, insert new configuration
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO system_config 
            (country_code, whatsapp_number, auto_release_enabled, auto_release_minutes)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $country_code,
            $whatsapp_number,
            $auto_release_enabled,
            $auto_release_minutes
        ]);
    }

    $_SESSION['success'] = 'Configuración actualizada exitosamente';

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../dashboard.php');
exit();