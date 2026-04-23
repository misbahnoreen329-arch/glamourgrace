<?php
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: brands.php');
    exit();
}

$brand_id = intval($_GET['id']);

// Get brand details
$brand_query = "SELECT * FROM brands WHERE id = $brand_id";
$brand_result = mysqli_query($conn, $brand_query);

if (mysqli_num_rows($brand_result) == 0) {
    header('Location: brands.php');
    exit();
}

$brand = mysqli_fetch_assoc($brand_result);

// Get products by this brand
$products_query = "
    SELECT p.*, pi.image_path, c.name as category_name 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.brand_id = $brand_id AND p.is_published = 1 
    ORDER BY p.created_at DESC
";
$products = mysqli_query($conn, $products_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $brand['name']; ?> - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="brand-header">
            <?php if($brand['image']): ?>
                <img src="../assets/uploads/brands/<?php echo $brand['image']; ?>" 
                     alt="<?php echo $brand['name']; ?>" class="brand-logo">
            <?php endif; ?>
            <h1><?php echo $brand['name']; ?></h1>
            <?php if($brand['description']): ?>
                <p class="brand-description"><?php echo $brand['description']; ?></p>
            <?php endif; ?>
        </div>
        
        <h2>Products by <?php echo $brand['name']; ?></h2>
        
        <?php if (mysqli_num_rows($products) > 0): ?>
            <div class="products-grid">
                <?php while($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <?php if($product['quantity'] == 0): ?>
                        <span class="out-of-stock-badge">Out of Stock</span>
                    <?php endif; ?>
                    
                    <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" 
                         alt="<?php echo $product['name']; ?>">
                    
                    <div class="product-info">
                        <span class="product-category"><?php echo $product['category_name']; ?></span>
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-price">$<?php echo number_format($product['sale_price'], 2); ?></p>
                        
                        <?php if($product['shade']): ?>
                            <p class="product-shade">Shade: <?php echo $product['shade']; ?></p>
                        <?php endif; ?>
                        
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                            <?php if($product['quantity'] > 0): ?>
                                <button class="btn btn-secondary add-to-cart" 
                                        data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <p>No products found for this brand.</p>
                <a href="brands.php" class="btn">View All Brands</a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            
            fetch('add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(count => {
                alert('Product added to cart!');
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = count;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
</body>
</html>