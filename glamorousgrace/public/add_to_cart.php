<?php
require_once '../includes/config.php';
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Check if product exists and is in stock
    $product_query = "SELECT quantity FROM products WHERE id = $product_id AND is_published = 1";
    $product_result = mysqli_query($conn, $product_query);
    
    if (mysqli_num_rows($product_result) > 0) {
        $product = mysqli_fetch_assoc($product_result);
        
        if ($product['quantity'] >= $quantity) {
            // Add to cart or update quantity
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            
            // Return cart count
            echo array_sum($_SESSION['cart']);
        } else {
            echo 'error:insufficient_stock';
        }
    } else {
        echo 'error:product_not_found';
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['get_count'])) {
    // Return cart count for AJAX requests
    echo array_sum($_SESSION['cart']);
} else {
    // Redirect to home if accessed directly
    header('Location: index.php');
    exit();
}
?>