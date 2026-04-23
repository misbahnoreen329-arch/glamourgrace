<?php
require_once '../includes/config.php';

// Get all brands
$brands_query = "SELECT * FROM brands ORDER BY name";
$brands = mysqli_query($conn, $brands_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brands - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Our Brands</h1>
        <p class="page-description">Discover premium makeup brands from around the world.</p>
        
        <div class="brands-grid">
            <?php while($brand = mysqli_fetch_assoc($brands)): ?>
            <a href="brand-products.php?id=<?php echo $brand['id']; ?>" class="brand-card">
                <?php if($brand['image']): ?>
                    <img src="../assets/uploads/brands/<?php echo $brand['image']; ?>" 
                         alt="<?php echo $brand['name']; ?>">
                <?php else: ?>
                    <div class="brand-placeholder"><?php echo substr($brand['name'], 0, 2); ?></div>
                <?php endif; ?>
                
                <div class="brand-info">
                    <h3><?php echo $brand['name']; ?></h3>
                    <?php if($brand['description']): ?>
                        <p class="brand-description"><?php echo substr($brand['description'], 0, 100); ?>...</p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>