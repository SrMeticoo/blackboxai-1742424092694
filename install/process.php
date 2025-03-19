<?php
session_start();

try {
    // Validate form data
    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';

    // Validate required fields
    if (empty($admin_username) || empty($admin_password)) {
        throw new Exception('Por favor complete todos los campos requeridos');
    }

    // Database file path
    $db_path = __DIR__ . '/../database.sqlite';
    
    try {
        // Create SQLite database
        $pdo = new PDO("sqlite:$db_path");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Execute schema
        $schema = file_get_contents(__DIR__ . '/schema.sqlite');
        $statements = explode(';', $schema);
        foreach ($statements as $statement) {
            if (trim($statement) != '') {
                $pdo->exec($statement);
            }
        }

        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->execute([$admin_username, password_hash($admin_password, PASSWORD_DEFAULT)]);

        // Create config file content
        $config_content = <<<PHP
<?php
session_start();

// Database configuration - SQLite
define('DB_PATH', __DIR__ . '/../database.sqlite');

// Initialize PDO connection
try {
    \$pdo = new PDO("sqlite:" . DB_PATH);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die('Connection failed: ' . \$e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset(\$_SESSION['admin_user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/index.php');
        exit();
    }
}
PHP;

        // Create includes directory if it doesn't exist
        $includes_dir = __DIR__ . '/../includes';
        if (!file_exists($includes_dir)) {
            mkdir($includes_dir, 0777, true);
        }

        // Write config file with proper permissions
        $config_file = $includes_dir . '/config.php';
        file_put_contents($config_file, $config_content);
        chmod($config_file, 0644);

        // Create .installed file
        $installed_file = $includes_dir . '/.installed';
        file_put_contents($installed_file, date('Y-m-d H:i:s'));
        chmod($installed_file, 0644);

        // Redirect to admin login with success message
        $_SESSION['success'] = 'Sistema instalado exitosamente';
        header('Location: ../admin/index.php');
        exit();

    } catch (PDOException $e) {
        throw new Exception('Error de base de datos: ' . $e->getMessage());
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit();
}