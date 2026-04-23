<?php
require_once '../includes/config.php';
session_start();

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and save customer information
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Insert customer
    $customer_query = "INSERT INTO customers (name, email, phone, address, city) 
                       VALUES ('$name', '$email', '$phone', '$address', '$city')";
    mysqli_query($conn, $customer_query);
    $customer_id = mysqli_insert_id($conn);
    
    // Calculate total amount from cart
    $total_amount = 0;
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $products_query = "SELECT id, sale_price FROM products WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $products_query);
    
    while ($product = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $total_amount += $product['sale_price'] * $quantity;
    }
    
    // Generate order number
    $order_number = 'GG' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    // Insert order
    $order_query = "INSERT INTO orders (order_number, customer_id, total_amount, payment_method, notes) 
                    VALUES ('$order_number', $customer_id, $total_amount, 'COD', '$notes')";
    mysqli_query($conn, $order_query);
    $order_id = mysqli_insert_id($conn);
    
    // Insert order items and update product quantities
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT sale_price FROM products WHERE id = $product_id"));
        
        // Insert order item
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) 
                             VALUES ($order_id, $product_id, $quantity, {$product['sale_price']})");
        
        // Update product quantity
        mysqli_query($conn, "UPDATE products SET quantity = quantity - $quantity WHERE id = $product_id");
        
        // Record sale
        mysqli_query($conn, "INSERT INTO sales (order_id, product_id, quantity, amount, sale_date) 
                             VALUES ($order_id, $product_id, $quantity, {$product['sale_price']} * $quantity, CURDATE())");
    }
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Redirect to success page
    header("Location: order-success.php?order_id=$order_id");
    exit();
}

// Get cart total for display
$total_amount = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $result = mysqli_query($conn, "SELECT id, sale_price FROM products WHERE id IN ($ids_string)");
    while ($product = mysqli_fetch_assoc($result)) {
        $total_amount += $product['sale_price'] * $_SESSION['cart'][$product['id']];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Checkout</h1>
        
        <div class="checkout-container">
            <div class="checkout-form">
                <h2>Shipping Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email">
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Address *</label>
                        <textarea name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city">
                    </div>
                    
                    <div class="form-group">
                        <label>Order Notes</label>
                        <textarea name="notes" rows="3" placeholder="Any special instructions..."></textarea>
                    </div>
                    
                    <div class="payment-method">
                        <h3>Payment Method</h3>
                        <div class="payment-option">
                            <input type="radio" id="cod" name="payment" value="COD" checked>
                            <label for="cod">Cash on Delivery (COD)</label>
                        </div>
                        <p class="payment-note">Pay when you receive your order</p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="summary-items">
                    <?php foreach ($_SESSION['cart'] as $product_id => $quantity): 
                        $product = mysqli_fetch_assoc(mysqli_query($conn, "
                            SELECT p.*, pi.image_path 
                            FROM products p 
                            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
                            WHERE p.id = $product_id
                        "));
                    ?>
                    <div class="summary-item">
                        <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                        <div>
                            <h4><?php echo $product['name']; ?></h4>
                            <p>Quantity: <?php echo $quantity; ?></p>
                            <p>$<?php echo number_format($product['sale_price'], 2); ?> each</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-total">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>