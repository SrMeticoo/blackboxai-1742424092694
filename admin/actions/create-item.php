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
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $total_balls = (int)($_POST['total_balls'] ?? 0);

    // Validate data
    if (empty($name) || empty($description) || empty($image_url) || $total_balls <= 0) {
        throw new Exception('Por favor complete todos los campos requeridos');
    }

    if ($total_balls > 99999) {
        throw new Exception('El número máximo de balotas permitido es 99999');
    }

    // Validate image URL
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        throw new Exception('La URL de la imagen no es válida');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Insert new item
        $stmt = $pdo->prepare("
            INSERT INTO raffle_items (name, description, image_url, total_balls)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$name, $description, $image_url, $total_balls]);
        $item_id = $pdo->lastInsertId();

        // Create balls for this item
        $values = [];
        $params = [];
        
        for ($i = 1; $i <= $total_balls; $i++) {
            $number = str_pad($i, 5, '0', STR_PAD_LEFT);
            $values[] = "(?, ?, 'available')";
            array_push($params, $item_id, $number);
        }

        $sql = "INSERT INTO balls (item_id, number, status) VALUES " . implode(", ", $values);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Commit transaction
        $pdo->commit();
        $_SESSION['success'] = 'Item creado exitosamente';

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../dashboard.php');
exit();