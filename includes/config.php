<?php
session_start();

// Database configuration - SQLite
define('DB_PATH', __DIR__ . '/../database.sqlite');

// Initialize PDO connection
try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['admin_user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/index.php');
        exit();
    }
}