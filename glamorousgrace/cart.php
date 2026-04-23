<?php
require_once '../includes/config.php';
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
    } elseif (isset($_POST['remove'])) {
        $product_id = intval($_POST['product_id']);
        unset($_SESSION['cart'][$product_id]);
    }
}

// Get cart products
$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', $product_ids);
    
    $query = "SELECT p.*, pi.image_path 
              FROM products p 
              LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
              WHERE p.id IN ($ids_string)";
    $result = mysqli_query($conn, $query);
    
    while ($product = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['sale_price'] * $quantity;
        $total_amount += $subtotal;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Shopping Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="products.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" class="cart-form">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                            $product = $item['product'];
                        ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                                    <div>
                                        <h3><?php echo $product['name']; ?></h3>
                                        <p class="product-brand">
                                            <?php 
                                            $brand = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM brands WHERE id = " . $product['brand_id']));
                                            echo $brand['name'];
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>$<?php echo number_format($product['sale_price'], 2); ?></td>
                            <td>
                                <input type="number" 
                                       name="quantity[<?php echo $product['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo min($product['quantity'], 10); ?>">
                            </td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td>
                                <button type="submit" name="remove" class="btn-remove">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="summary-details">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($total_amount, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="cart-actions">
                        <button type="submit" name="update" class="btn">Update Cart</button>
                        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                        <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>