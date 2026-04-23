<?php
require_once '../includes/config.php';

// Get all active offers
$offers_query = "
    SELECT * FROM offers 
    WHERE is_active = 1 
    AND start_date <= CURDATE() 
    AND end_date >= CURDATE() 
    ORDER BY created_at DESC
";
$offers = mysqli_query($conn, $offers_query);

// Get products with offers
$products_query = "
    SELECT p.*, pi.image_path 
    FROM products p 
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
    WHERE p.is_published = 1 AND p.quantity > 0 
    ORDER BY p.created_at DESC 
    LIMIT 12
";
$products = mysqli_query($conn, $products_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .offer-hero {
            background: linear-gradient(135deg, #ff6b8b, #ff8e53);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        .offer-hero h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .offer-hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .offer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        .offer-card-public {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .offer-card-public:hover {
            transform: translateY(-10px);
        }
        .offer-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .offer-content {
            padding: 20px;
        }
        .offer-badge {
            background: #ff6b8b;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        .offer-dates {
            color: #666;
            font-size: 0.9rem;
            margin: 10px 0;
        }
        .offer-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .no-offers {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .no-offers p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        .offer-products-title {
            text-align: center;
            margin: 50px 0 30px;
            color: #333;
        }
        .offer-products-title h2 {
            color: #ff6b8b;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="offer-hero">
        <div class="container">
            <h1>Special Offers</h1>
            <p>Discover amazing deals and discounts on your favorite makeup products</p>
            <a href="#offers" class="btn btn-white">View All Offers</a>
        </div>
    </section>
    
    <!-- Offers Section -->
    <section id="offers" class="section">
        <div class="container">
            <?php if (mysqli_num_rows($offers) > 0): ?>
                <div class="offer-grid">
                    <?php while($offer = mysqli_fetch_assoc($offers)): ?>
                    <div class="offer-card-public">
                        <?php if($offer['image']): ?>
                            <img src="../assets/uploads/banners/<?php echo $offer['image']; ?>" 
                                 class="offer-image" alt="<?php echo $offer['title']; ?>">
                        <?php else: ?>
                            <div class="offer-image" style="background: linear-gradient(135deg, #ff6b8b, #ff8e53); 
                                 display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                Special Offer
                            </div>
                        <?php endif; ?>
                        
                        <div class="offer-content">
                            <span class="offer-badge">
                                <?php 
                                if ($offer['discount_type'] == 'percentage') {
                                    echo $offer['discount_value'] . '% OFF';
                                } else {
                                    echo '$' . $offer['discount_value'] . ' OFF';
                                }
                                ?>
                            </span>
                            
                            <h3><?php echo $offer['title']; ?></h3>
                            
                            <div class="offer-dates">
                                <strong>Valid:</strong> 
                                <?php echo date('M d, Y', strtotime($offer['start_date'])); ?> 
                                - 
                                <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                            </div>
                            
                            <?php if($offer['description']): ?>
                                <p class="offer-description"><?php echo $offer['description']; ?></p>
                            <?php endif; ?>
                            
                            <a href="products.php" class="btn">Shop Now</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-offers">
                    <h3>No Active Offers</h3>
                    <p>There are no special offers available at the moment.</p>
                    <p>Check back soon for amazing deals!</p>
                    <a href="products.php" class="btn">Shop Products</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="offer-products-title">
                <h2>Featured Products</h2>
                <p>Check out our amazing collection</p>
            </div>
            
            <div class="products-grid">
                <?php while($product = mysqli_fetch_assoc($products)): ?>
                <div class="product-card">
                    <?php if($product['quantity'] == 0): ?>
                        <span class="out-of-stock-badge">Out of Stock</span>
                    <?php endif; ?>
                    
                    <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" 
                         alt="<?php echo $product['name']; ?>">
                    
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-price">$<?php echo number_format($product['sale_price'], 2); ?></p>
                        
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
        </div>
    </section>
    
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
    
    // Smooth scroll for anchor link
    document.querySelector('a[href="#offers"]').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#offers').scrollIntoView({
            behavior: 'smooth'
        });
    });
    </script>
</body>
</html>