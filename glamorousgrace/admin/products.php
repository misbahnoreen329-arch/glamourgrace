<?php
require_once '../includes/config.php';
requireAdminLogin();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    header('Location: products.php');
    exit();
}

// Handle publish/unpublish
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_published FROM products WHERE id = $id"));
    $new_status = $product['is_published'] ? 0 : 1;
    mysqli_query($conn, "UPDATE products SET is_published = $new_status WHERE id = $id");
    header('Location: products.php');
    exit();
}

// Get all products with brand and category info
$products = mysqli_query($conn, "
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p 
    LEFT JOIN brands b ON p.brand_id = b.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Product Management</h1>
                <a href="add_product.php" class="btn">Add New Product</a>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = mysqli_fetch_assoc($products)): 
                        // Get default image
                        $image_query = mysqli_query($conn, "SELECT image_path FROM product_images WHERE product_id = {$product['id']} AND is_default = 1 LIMIT 1");
                        $image = mysqli_fetch_assoc($image_query);
                        $image_path = $image ? '../assets/uploads/products/' . $image['image_path'] : '../assets/images/no-image.jpg';
                    ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><img src="<?php echo $image_path; ?>" alt="<?php echo $product['name']; ?>" class="product-thumb"></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['brand_name']; ?></td>
                        <td><?php echo $product['category_name']; ?></td>
                        <td>$<?php echo number_format($product['sale_price'], 2); ?></td>
                        <td>
                            <?php if($product['quantity'] == 0): ?>
                                <span class="out-of-stock">Out of Stock</span>
                            <?php elseif($product['quantity'] < 10): ?>
                                <span class="low-stock"><?php echo $product['quantity']; ?></span>
                            <?php else: ?>
                                <span class="in-stock"><?php echo $product['quantity']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($product['is_published']): ?>
                                <span class="status-published">Published</span>
                            <?php else: ?>
                                <span class="status-draft">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-sm">Edit</a>
                                <a href="products.php?toggle=<?php echo $product['id']; ?>" class="btn-sm">
                                    <?php echo $product['is_published'] ? 'Unpublish' : 'Publish'; ?>
                                </a>
                                <a href="products.php?delete=<?php echo $product['id']; ?>" 
                                   class="btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>