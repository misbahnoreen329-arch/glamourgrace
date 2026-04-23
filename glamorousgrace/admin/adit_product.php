<?php
require_once '../includes/config.php';
requireAdminLogin();

// Get brands and categories for dropdowns
$brands = mysqli_query($conn, "SELECT * FROM brands ORDER BY name");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $brand_id = intval($_POST['brand_id']);
    $category_id = intval($_POST['category_id']);
    $purchase_price = floatval($_POST['purchase_price']);
    $sale_price = floatval($_POST['sale_price']);
    $quantity = intval($_POST['quantity']);
    $shade = mysqli_real_escape_string($conn, $_POST['shade']);
    $skin_type = mysqli_real_escape_string($conn, $_POST['skin_type']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Insert product
    $query = "INSERT INTO products (name, description, brand_id, category_id, purchase_price, sale_price, quantity, shade, skin_type, is_published) 
              VALUES ('$name', '$description', $brand_id, $category_id, $purchase_price, $sale_price, $quantity, '$shade', '$skin_type', $is_published)";
    
    if (mysqli_query($conn, $query)) {
        $product_id = mysqli_insert_id($conn);
        
        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $default_set = false;
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === 0) {
                    $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                    $file_path = PRODUCT_UPLOAD_PATH . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $is_default = (!$default_set) ? 1 : 0;
                        $default_set = true;
                        
                        mysqli_query($conn, "INSERT INTO product_images (product_id, image_path, is_default) 
                                            VALUES ($product_id, '$file_name', $is_default)");
                    }
                }
            }
        }
        
        header('Location: products.php?success=1');
        exit();
    } else {
        $error = "Error adding product: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <h1>Add New Product</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="product-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Brand *</label>
                        <select name="brand_id" required>
                            <option value="">Select Brand</option>
                            <?php while($brand = mysqli_fetch_assoc($brands)): ?>
                                <option value="<?php echo $brand['id']; ?>"><?php echo $brand['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Skin Type</label>
                        <select name="skin_type">
                            <option value="All">All Skin Types</option>
                            <option value="Dry">Dry</option>
                            <option value="Oily">Oily</option>
                            <option value="Combination">Combination</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Purchase Price ($) *</label>
                        <input type="number" name="purchase_price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Sale Price ($) *</label>
                        <input type="number" name="sale_price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Shade / Color</label>
                    <input type="text" name="shade" placeholder="e.g., Ruby Red, Nude Beige">
                </div>
                
                <div class="form-group">
                    <label>Product Images *</label>
                    <input type="file" name="images[]" multiple accept="image/*" required>
                    <p class="help-text">First image will be set as default. You can change it later.</p>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" name="is_published" id="is_published" checked>
                    <label for="is_published">Publish this product</label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Add Product</button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>