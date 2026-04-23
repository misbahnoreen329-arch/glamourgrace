<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlamorousGrace - Premium Makeup & Beauty</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <span>Glamorous</span>Grace
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="brands.php">Brands</a></li>
                    <li><a href="offers.php">Offers</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <?php
                // Calculate cart count
                $cart_count = 0;
                if (isset($_SESSION['cart'])) {
                    $cart_count = array_sum($_SESSION['cart']);
                }
                ?>
                <a href="cart.php" class="cart-icon">
                    🛒
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="../admin/dashboard.php" class="btn btn-sm">Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>