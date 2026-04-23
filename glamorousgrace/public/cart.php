<?php
require_once '../includes/config.php';
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart via GET (for testing)
if (isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    
    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = 1;
    } else {
        $_SESSION['cart'][$product_id]++;
    }
    
    header('Location: cart.php');
    exit();
}

// Handle remove from cart
if (isset($_GET['remove_from_cart'])) {
    $product_id = intval($_GET['remove_from_cart']);
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    header('Location: cart.php');
    exit();
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
}

// Handle clear cart
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit();
}

// Calculate cart total
$cart_items = [];
$total_amount = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    
    $query = "SELECT p.*, pi.image_path 
              FROM products p 
              LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
              WHERE p.id IN ($product_ids)";
    
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
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 80px auto 40px;
            padding: 0 20px;
        }
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .cart-header h1 {
            color: #ff6b8b;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .empty-cart p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        .cart-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .cart-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .cart-product {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .cart-product img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .cart-product-info h3 {
            margin: 0 0 5px 0;
            font-size: 1rem;
        }
        .cart-product-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            text-align: center;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .remove-btn:hover {
            background: #c82333;
        }
        .cart-summary {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .summary-row.total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff6b8b;
            border-bottom: none;
        }
        .cart-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        .continue-shopping {
            display: inline-block;
            margin-top: 15px;
            color: #ff6b8b;
            text-decoration: none;
            font-weight: 500;
        }
        .continue-shopping:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            .cart-product {
                flex-direction: column;
                align-items: flex-start;
            }
            .cart-actions {
                flex-direction: column;
            }
            .cart-actions .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <?php if (!empty($cart_items)): ?>
                <a href="cart.php?clear_cart=1" class="btn btn-secondary" 
                   onclick="return confirm('Clear all items from cart?')">
                   🗑️ Clear Cart
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your shopping cart is empty</p>
                <a href="products.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" action="cart.php">
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
                                    <?php if ($product['image_path']): ?>
                                        <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div style="width:80px;height:80px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:5px;color:#666;">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="cart-product-info">
                                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <p>
                                            <?php 
                                            $brand = mysqli_fetch_assoc(mysqli_query($conn, 
                                                "SELECT name FROM brands WHERE id = {$product['brand_id']}"));
                                            echo $brand ? htmlspecialchars($brand['name']) : 'Unknown Brand';
                                            ?>
                                        </p>
                                        <?php if ($product['shade']): ?>
                                            <p>Shade: <?php echo htmlspecialchars($product['shade']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <td>$<?php echo number_format($product['sale_price'], 2); ?></td>
                            
                            <td>
                                <input type="number" 
                                       name="quantity[<?php echo $product['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="10" 
                                       class="quantity-input">
                            </td>
                            
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            
                            <td>
                                <a href="cart.php?remove_from_cart=<?php echo $product['id']; ?>" 
                                   class="remove-btn"
                                   onclick="return confirm('Remove this item from cart?')">
                                    Remove
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>$0.00</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    
                    <div class="cart-actions">
                        <button type="submit" name="update_cart" class="btn btn-secondary">
                            Update Cart
                        </button>
                        
                        <a href="checkout.php" class="btn btn-primary">
                            Proceed to Checkout
                        </a>
                        
                        <a href="products.php" class="btn">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Update cart count in header
    function updateCartCount() {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            // You can implement AJAX to get actual count
            // For now, we'll just show the count from session
            const count = <?php echo array_sum($_SESSION['cart']); ?>;
            cartCount.textContent = count;
        }
    }
    
    // Initialize
    updateCartCount();
    </script>
</body>
</html>