<?php
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);

// Get product details
$product_query = "
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = $product_id AND p.is_published = 1
";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) == 0) {
    header('Location: products.php');
    exit();
}

$product = mysqli_fetch_assoc($product_result);

// Get product images
$images_query = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_default DESC";
$images_result = mysqli_query($conn, $images_query);
$images = [];
while ($image = mysqli_fetch_assoc($images_result)) {
    $images[] = $image;
}

// Get related products (same category)
$related_query = "
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    WHERE p.category_id = {$product['category_id']} 
    AND p.id != $product_id 
    AND p.is_published = 1 
    AND p.quantity > 0 
    LIMIT 4
";
$related_products = mysqli_query($conn, $related_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="product-detail">
            <!-- Product Images -->
            <div class="product-images">
                <div class="main-image">
                    <?php if (!empty($images)): ?>
                        <img id="mainImage" src="../assets/uploads/products/<?php echo $images[0]['image_path']; ?>" 
                             alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <img id="mainImage" src="../assets/images/no-image.jpg" alt="No image">
                    <?php endif; ?>
                </div>
                
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-images">
                    <?php foreach ($images as $index => $image): ?>
                    <img src="../assets/uploads/products/<?php echo $image['image_path']; ?>" 
                         alt="Thumbnail <?php echo $index + 1; ?>"
                         onclick="changeImage('<?php echo $image['image_path']; ?>')"
                         class="<?php echo $index == 0 ? 'active' : ''; ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div class="product-info-detail">
                <h1><?php echo $product['name']; ?></h1>
                
                <div class="product-meta">
                    <span class="brand">Brand: <?php echo $product['brand_name']; ?></span>
                    <span class="category">Category: <?php echo $product['category_name']; ?></span>
                </div>
                
                <div class="price-section">
                    <span class="price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                    <span class="profit">Profit: $<?php echo number_format($product['profit'], 2); ?></span>
                </div>
                
                <?php if($product['shade']): ?>
                <div class="product-attribute">
                    <strong>Shade/Color:</strong>
                    <span class="shade"><?php echo $product['shade']; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($product['skin_type'] != 'All'): ?>
                <div class="product-attribute">
                    <strong>Skin Type:</strong>
                    <span class="skin-type"><?php echo $product['skin_type']; ?></span>
                </div>
                <?php endif; ?>
                
                <div class="product-attribute">
                    <strong>Stock Status:</strong>
                    <?php if($product['quantity'] == 0): ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php elseif($product['quantity'] < 10): ?>
                        <span class="low-stock">Low Stock (<?php echo $product['quantity']; ?> left)</span>
                    <?php else: ?>
                        <span class="in-stock">In Stock (<?php echo $product['quantity']; ?> available)</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <?php if($product['quantity'] > 0): ?>
                <form method="POST" action="add-to-cart.php" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" 
                               max="<?php echo min($product['quantity'], 10); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (mysqli_num_rows($related_products) > 0): ?>
        <div class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php while($related = mysqli_fetch_assoc($related_products)): ?>
                <div class="product-card">
                    <img src="../assets/uploads/products/<?php echo $related['image_path']; ?>" 
                         alt="<?php echo $related['name']; ?>">
                    <div class="product-info">
                        <h3><?php echo $related['name']; ?></h3>
                        <p class="product-price">$<?php echo number_format($related['sale_price'], 2); ?></p>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    function changeImage(imagePath) {
        document.getElementById('mainImage').src = '../assets/uploads/products/' + imagePath;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-images img').forEach(img => {
            img.classList.remove('active');
        });
        event.target.classList.add('active');
    }
    
    // Handle add to cart form
    const cartForm = document.querySelector('.add-to-cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(count => {
                alert('Product added to cart!');
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = count;
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
    </script>
</body>
</html>