<?php
require_once 'config.php';

// Function to generate order number
function generateOrderNumber() {
    return 'GG' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Function to get product images
function getProductImages($product_id) {
    global $conn;
    $query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_default DESC";
    return mysqli_query($conn, $query);
}

// Function to get default product image
function getDefaultProductImage($product_id) {
    global $conn;
    $query = "SELECT image_path FROM product_images WHERE product_id = $product_id AND is_default = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['image_path'];
    }
    return 'no-image.jpg';
}

// Function to get cart count
function getCartCount() {
    if (isset($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    }
    return 0;
}

// Function to check if product is in stock
function isProductInStock($product_id, $quantity = 1) {
    global $conn;
    $query = "SELECT quantity FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['quantity'] >= $quantity;
    }
    return false;
}

// Function to get brand name
function getBrandName($brand_id) {
    global $conn;
    $query = "SELECT name FROM brands WHERE id = $brand_id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['name'];
    }
    return 'Unknown Brand';
}

// Function to get category name
function getCategoryName($category_id) {
    global $conn;
    $query = "SELECT name FROM categories WHERE id = $category_id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['name'];
    }
    return 'Uncategorized';
}
?>