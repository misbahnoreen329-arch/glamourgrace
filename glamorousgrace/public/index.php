<?php
require_once '../includes/config.php';

// Get active banners
$banners = mysqli_query($conn, "SELECT * FROM banners WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");

// Get new arrivals (latest 10 products)
$new_arrivals = mysqli_query($conn, "
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    WHERE p.is_published = 1 AND p.quantity > 0 
    ORDER BY p.created_at DESC 
    LIMIT 10
");

// Get top selling products
$top_sales = mysqli_query($conn, "
    SELECT p.*, pi.image_path, SUM(oi.quantity) as total_sold 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    LEFT JOIN order_items oi ON p.id = oi.product_id 
    LEFT JOIN orders o ON oi.order_id = o.id AND o.status IN ('delivered', 'sold')
    WHERE p.is_published = 1 AND p.quantity > 0 
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 10
");

// Get all brands
$brands = mysqli_query($conn, "SELECT * FROM brands ORDER BY name");

// Get featured products
$featured_products = mysqli_query($conn, "
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    WHERE p.is_published = 1 AND p.quantity > 0 
    ORDER BY RAND() 
    LIMIT 8
");
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
    <?php include '../includes/header.php'; ?>
    
    <!-- Banner Slider -->
    <section class="banner-slider">
        <div class="slider-container">
            <?php while($banner = mysqli_fetch_assoc($banners)): ?>
            <div class="slide">
                <img src="../assets/uploads/banners/<?php echo $banner['image']; ?>" alt="<?php echo $banner['title']; ?>">
                <div class="slide-content">
                    <h2><?php echo $banner['title']; ?></h2>
                    <p><?php echo $banner['subtitle']; ?></p>
                    <?php if($banner['link']): ?>
                        <a href="<?php echo $banner['link']; ?>" class="btn">Shop Now</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <button class="slider-btn prev">‹</button>
        <button class="slider-btn next">›</button>
    </section>
    
    <!-- New Arrivals -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <div class="product-slider">
                <div class="slider-controls">
                    <button class="slider-nav prev">‹</button>
                    <button class="slider-nav next">›</button>
                </div>
                <div class="products-grid" id="newArrivalsSlider">
                    <?php while($product = mysqli_fetch_assoc($new_arrivals)): ?>
                    <div class="product-card">
                        <?php if($product['quantity'] == 0): ?>
                            <span class="out-of-stock-badge">Out of Stock</span>
                        <?php endif; ?>
                        <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="product-brand">
                                <?php 
                                $brand = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM brands WHERE id = " . $product['brand_id']));
                                echo $brand['name'];
                                ?>
                            </p>
                            <p class="product-price">$<?php echo number_format($product['sale_price'], 2); ?></p>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                                <?php if($product['quantity'] > 0): ?>
                                    <button class="btn btn-secondary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Top Sales -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Top Selling Products</h2>
            <div class="product-slider">
                <div class="slider-controls">
                    <button class="slider-nav prev">‹</button>
                    <button class="slider-nav next">›</button>
                </div>
                <div class="products-grid" id="topSalesSlider">
                    <?php while($product = mysqli_fetch_assoc($top_sales)): ?>
                    <div class="product-card">
                        <?php if($product['quantity'] == 0): ?>
                            <span class="out-of-stock-badge">Out of Stock</span>
                        <?php endif; ?>
                        <span class="bestseller-badge">Bestseller</span>
                        <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p class="product-price">$<?php echo number_format($product['sale_price'], 2); ?></p>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                                <?php if($product['quantity'] > 0): ?>
                                    <button class="btn btn-secondary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Brands -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Our Brands</h2>
            <div class="brands-grid">
                <?php while($brand = mysqli_fetch_assoc($brands)): ?>
                <a href="brand-products.php?id=<?php echo $brand['id']; ?>" class="brand-card">
                    <?php if($brand['image']): ?>
                        <img src="../assets/uploads/brands/<?php echo $brand['image']; ?>" alt="<?php echo $brand['name']; ?>">
                    <?php else: ?>
                        <div class="brand-placeholder"><?php echo $brand['name']; ?></div>
                    <?php endif; ?>
                    <h3><?php echo $brand['name']; ?></h3>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <!-- Featured Products -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid">
                <?php while($product = mysqli_fetch_assoc($featured_products)): ?>
                <div class="product-card">
                    <?php if($product['quantity'] == 0): ?>
                        <span class="out-of-stock-badge">Out of Stock</span>
                    <?php endif; ?>
                    <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-price">$<?php echo number_format($product['sale_price'], 2); ?></p>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                            <?php if($product['quantity'] > 0): ?>
                                <button class="btn btn-secondary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Slider functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.banner-slider .slide');
    const totalSlides = slides.length;
    
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.display = i === index ? 'block' : 'none';
        });
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        showSlide(currentSlide);
    }
    
    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);
    
    // Initialize
    showSlide(0);
    
    // Product sliders
    function initProductSlider(containerId) {
        const container = document.getElementById(containerId);
        const products = container.querySelectorAll('.product-card');
        const prevBtn = container.parentElement.querySelector('.slider-nav.prev');
        const nextBtn = container.parentElement.querySelector('.slider-nav.next');
        
        let currentIndex = 0;
        const productsPerView = 4;
        
        function updateSlider() {
            products.forEach((product, index) => {
                product.style.display = (index >= currentIndex && index < currentIndex + productsPerView) ? 'block' : 'none';
            });
        }
        
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
        
        nextBtn.addEventListener('click', () => {
            if (currentIndex + productsPerView < products.length) {
                currentIndex++;
                updateSlider();
            }
        });
        
        updateSlider();
    }
    
    // Initialize sliders
    initProductSlider('newArrivalsSlider');
    initProductSlider('topSalesSlider');
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            // Add to cart via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'add-to-cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert('Product added to cart!');
                    updateCartCount();
                }
            };
            xhr.send('product_id=' + productId + '&quantity=1');
        });
    });
    
    function updateCartCount() {
        // Update cart count in navbar
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get-cart-count.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = xhr.responseText;
                }
            }
        };
        xhr.send();
    }
    </script>
</body>
</html>