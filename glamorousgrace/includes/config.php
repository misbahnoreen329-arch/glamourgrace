<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'glamorousgrace');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);

// Remove double slashes and trailing slash
$base_path = rtrim(str_replace('\\', '/', $script_name), '/');
define('BASE_URL', $protocol . '://' . $host . $base_path . '/');
define('ADMIN_URL', BASE_URL . 'admin/');

// File paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');
define('PRODUCT_UPLOAD_PATH', UPLOAD_PATH . 'products/');
define('BANNER_UPLOAD_PATH', UPLOAD_PATH . 'banners/');
define('BRAND_UPLOAD_PATH', UPLOAD_PATH . 'brands/');

// Create upload directories if they don't exist
$directories = [UPLOAD_PATH, PRODUCT_UPLOAD_PATH, BANNER_UPLOAD_PATH, BRAND_UPLOAD_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Check admin authentication
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . 'index.php');
        exit();
    }
}
?>