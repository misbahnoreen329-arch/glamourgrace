<?php
require_once '../includes/config.php';

// Get all products with filtering
$category_filter = '';
$brand_filter = '';
$search_filter = '';

if (isset($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $category_filter = " AND p.category_id = $category_id";
}

if (isset($_GET['brand'])) {
    $brand_id = intval($_GET['brand']);
    $brand_filter = " AND p.brand_id = $brand_id";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_filter = " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

// Get products
$products_query = "
    SELECT p.*, pi.image_path, b.name as brand_name, c.name as category_name 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_published = 1 $category_filter $brand_filter $search_filter
    ORDER BY p.created_at DESC
";
$products = mysqli_query($conn, $products_query);

// Get categories for filter
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

// Get brands for filter
$brands = mysqli_query($conn, "SELECT * FROM brands ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Our Products</h1>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Category:</label>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Brand:</label>
                    <select name="brand" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        <?php 
                        mysqli_data_seek($brands, 0); // Reset pointer
                        while($brand = mysqli_fetch_assoc($brands)): ?>
                            <option value="<?php echo $brand['id']; ?>"
                                <?php echo (isset($_GET['brand']) && $_GET['brand'] == $brand['id']) ? 'selected' : ''; ?>>
                                <?php echo $brand['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" name="search" placeholder="Search products..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn">Search</button>
                </div>
                
                <?php if (isset($_GET['category']) || isset($_GET['brand']) || isset($_GET['search'])): ?>
                    <a href="products.php" class="btn btn-secondary">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Grid -->
        <div class="products-grid">
            <?php if (mysqli_num_rows($products) > 0): ?>
                <?php while($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <?php if($product['quantity'] == 0): ?>
                        <span class="out-of-stock-badge">Out of Stock</span>
                    <?php elseif($product['quantity'] < 10): ?>
                        <span class="low-stock-badge">Low Stock</span>
                    <?php endif; ?>
                    
                    <?php if($product['image_path']): ?>
                        <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <div class="no-image">No Image</div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <span class="product-category"><?php echo $product['category_name']; ?></span>
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-brand"><?php echo $product['brand_name']; ?></p>
                        <p class="product-price">$<?php echo number_format($product['sale_price'], 2); ?></p>
                        
                        <?php if($product['shade']): ?>
                            <p class="product-shade">Shade: <?php echo $product['shade']; ?></p>
                        <?php endif; ?>
                        
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                            <?php if($product['quantity'] > 0): ?>
                                <button class="btn btn-secondary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-products">
                    <p>No products found.</p>
                    <a href="products.php" class="btn">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            
            // Send AJAX request
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
                } else {
                    // Create cart count if doesn't exist
                    const cartIcon = document.querySelector('.cart-icon');
                    if (cartIcon) {
                        cartIcon.innerHTML = '🛒 <span class="cart-count">' + count + '</span>';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
</body>
</html>