<?php
require_once '../includes/config.php';
requireAdminLogin();

// Get brands and categories for dropdowns
$brands = mysqli_query($conn, "SELECT * FROM brands ORDER BY name");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
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
        $success = "Product added successfully!";
        
        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $default_set = false;
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === 0) {
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $file_name = $_FILES['images']['name'][$key];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed_types)) {
                        $image_name = time() . '_' . uniqid() . '_' . $key . '.' . $file_ext;
                        $upload_path = PRODUCT_UPLOAD_PATH . $image_name;
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            // Set first image as default
                            $is_default = $default_set ? 0 : 1;
                            $default_set = true;
                            
                            mysqli_query($conn, 
                                "INSERT INTO product_images (product_id, image_path, is_default) 
                                 VALUES ($product_id, '$image_name', $is_default)");
                        }
                    }
                }
            }
        }
        
        // Redirect to edit page or stay
        if (isset($_POST['add_another'])) {
            $success .= " Product ID: " . $product_id;
        } else {
            header("Location: edit_product.php?id=$product_id");
            exit();
        }
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
    <style>
        .image-upload-area {
            border: 2px dashed #ccc;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .image-upload-area:hover {
            border-color: #ff6b8b;
            background: #fff5f7;
        }
        .image-upload-area.drag-over {
            border-color: #28a745;
            background: #f0fff4;
        }
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .image-preview {
            position: relative;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .image-preview img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-preview .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Add New Product</h1>
                <a href="products.php" class="btn btn-secondary">Back to Products</a>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="addProductForm">
                <!-- Basic Information -->
                <div class="form-section">
                    <h2>Basic Information</h2>
                    
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required placeholder="e.g., Matte Lipstick - Ruby Red">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Brand *</label>
                            <select name="brand_id" required>
                                <option value="">Select Brand</option>
                                <?php while($brand = mysqli_fetch_assoc($brands)): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo $brand['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                mysqli_data_seek($categories, 0);
                                while($category = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4" placeholder="Describe the product..."></textarea>
                    </div>
                </div>
                
                <!-- Pricing & Stock -->
                <div class="form-section">
                    <h2>Pricing & Stock</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Purchase Price ($) *</label>
                            <input type="number" name="purchase_price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Sale Price ($) *</label>
                            <input type="number" name="sale_price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Quantity *</label>
                            <input type="number" name="quantity" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Shade / Color</label>
                            <input type="text" name="shade" placeholder="e.g., Ruby Red, Nude Beige">
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
                </div>
                
                <!-- Product Images -->
                <div class="form-section">
                    <h2>Product Images</h2>
                    
                    <div class="image-upload-area" id="imageUploadArea">
                        <p>Drag & drop images here or click to browse</p>
                        <p><small>First image will be set as default. You can upload multiple images.</small></p>
                        <input type="file" name="images[]" id="productImages" multiple accept="image/*" style="display: none;">
                    </div>
                    
                    <div id="imagePreviewContainer" class="image-preview-grid">
                        <!-- Image previews will appear here -->
                    </div>
                    
                    <p class="help-text">Recommended: Square images, 800x800 pixels, JPG or PNG format</p>
                </div>
                
                <!-- Status -->
                <div class="form-section">
                    <h2>Status</h2>
                    
                    <div class="form-group checkbox">
                        <input type="checkbox" name="is_published" id="is_published" checked>
                        <label for="is_published">Publish this product immediately</label>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="form-actions">
                    <button type="submit" name="save_product" class="btn btn-primary">Save Product</button>
                    <button type="submit" name="add_another" class="btn">Save & Add Another</button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </main>
    </div>
    
    <script>
    // Image upload functionality
    const imageUploadArea = document.getElementById('imageUploadArea');
    const fileInput = document.getElementById('productImages');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    // Click to upload
    imageUploadArea.addEventListener('click', () => {
        fileInput.click();
    });
    
    // Drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        imageUploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        imageUploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        imageUploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        imageUploadArea.classList.add('drag-over');
    }
    
    function unhighlight() {
        imageUploadArea.classList.remove('drag-over');
    }
    
    imageUploadArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        fileInput.files = files;
        handleFiles(files);
    }
    
    // Handle file selection
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        previewContainer.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Only image files are allowed');
                continue;
            }
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size exceeds 5MB limit: ' + file.name);
                continue;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    preview.remove();
                    updateFileInput();
                };
                
                preview.appendChild(img);
                preview.appendChild(removeBtn);
                previewContainer.appendChild(preview);
            };
            
            reader.readAsDataURL(file);
        }
    }
    
    function updateFileInput() {
        // This is a simplified approach - in a real app, you'd need to manage FileList
        alert('Note: To remove files before upload, you need to re-select all desired files.');
    }
    
    // Form validation
    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        const salePrice = parseFloat(document.querySelector('input[name="sale_price"]').value);
        const purchasePrice = parseFloat(document.querySelector('input[name="purchase_price"]').value);
        
        if (salePrice < purchasePrice) {
            e.preventDefault();
            alert('Sale price cannot be less than purchase price!');
            return false;
        }
        
        // Check if at least one image is uploaded
        const fileInput = document.getElementById('productImages');
        if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Please upload at least one product image.');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>