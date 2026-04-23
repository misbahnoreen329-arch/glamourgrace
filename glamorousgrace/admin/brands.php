<?php
require_once '../includes/config.php';
requireAdminLogin();

// Define upload directory
$upload_dir = dirname(__DIR__) . '/assets/uploads/brands/';

// Ensure directory exists
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_brand'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Handle image upload
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_types)) {
                // Generate unique filename
                $image_name = 'brand_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $image_name;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Success
                } else {
                    $error = "Failed to upload image. Check folder permissions.";
                }
            } else {
                $error = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF, WEBP";
            }
        }
        
        if (empty($error)) {
            $query = "INSERT INTO brands (name, description, image) 
                      VALUES ('$name', '$description', '$image_name')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Brand added successfully!";
                // Refresh page to show new brand
                echo '<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>';
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle edit
    elseif (isset($_POST['edit_brand'])) {
        $id = intval($_POST['brand_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Check if new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_types)) {
                // Delete old image if exists
                $old_image_result = mysqli_query($conn, "SELECT image FROM brands WHERE id = $id");
                if ($old_image_row = mysqli_fetch_assoc($old_image_result)) {
                    if ($old_image_row['image'] && file_exists($upload_dir . $old_image_row['image'])) {
                        unlink($upload_dir . $old_image_row['image']);
                    }
                }
                
                // Upload new image
                $image_name = 'brand_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $image_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Update with new image
                    $query = "UPDATE brands SET 
                              name = '$name', 
                              description = '$description', 
                              image = '$image_name' 
                              WHERE id = $id";
                } else {
                    $error = "Failed to upload new image.";
                    $query = "UPDATE brands SET 
                              name = '$name', 
                              description = '$description' 
                              WHERE id = $id";
                }
            }
        } else {
            // No new image, keep old one
            $query = "UPDATE brands SET 
                      name = '$name', 
                      description = '$description' 
                      WHERE id = $id";
        }
        
        if (empty($error)) {
            if (mysqli_query($conn, $query)) {
                $success = "Brand updated successfully!";
                echo '<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>';
            } else {
                $error = "Update Error: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle delete
    elseif (isset($_POST['delete_brand'])) {
        $id = intval($_POST['brand_id']);
        
        // Get image name first
        $result = mysqli_query($conn, "SELECT image FROM brands WHERE id = $id");
        $brand = mysqli_fetch_assoc($result);
        
        // Check if brand has products
        $products_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE brand_id = $id");
        $products_count = mysqli_fetch_assoc($products_check)['count'];
        
        if ($products_count > 0) {
            $error = "Cannot delete brand. There are $products_count products associated with this brand.";
        } else {
            // Delete image file if exists
            if ($brand['image'] && file_exists($upload_dir . $brand['image'])) {
                unlink($upload_dir . $brand['image']);
            }
            
            // Delete from database
            mysqli_query($conn, "DELETE FROM brands WHERE id = $id");
            
            $success = "Brand deleted successfully!";
            echo '<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>';
        }
    }
}

// Get all brands
$brands = mysqli_query($conn, "SELECT * FROM brands ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Management - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .brand-logo-preview {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
        }
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            max-width: 95%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .close-modal {
            float: right;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            line-height: 1;
        }
        .close-modal:hover {
            color: #333;
        }
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
            object-fit: contain;
        }
        .upload-area {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #ff6b8b;
            background: #fff5f7;
        }
        .upload-area.drag-over {
            border-color: #28a745;
            background: #f0fff4;
        }
        .current-logo-container {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .no-logo {
            color: #666;
            font-style: italic;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Brand Management</h1>
                <button onclick="openAddModal()" class="btn">➕ Add New Brand</button>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success">
                    <?php echo $success; ?>
                    <button onclick="this.parentElement.style.display='none'" style="float:right;background:none;border:none;cursor:pointer;font-size:20px;">×</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error">
                    <?php echo $error; ?>
                    <button onclick="this.parentElement.style.display='none'" style="float:right;background:none;border:none;cursor:pointer;font-size:20px;">×</button>
                </div>
            <?php endif; ?>
            
            <!-- Debug Info -->
            <div style="background: #f0f8ff; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 12px;">
                <strong>Upload Path:</strong> <?php echo $upload_dir; ?><br>
                <strong>Status:</strong> 
                <?php 
                echo file_exists($upload_dir) ? 
                    '<span style="color:green">✓ Directory exists</span>' : 
                    '<span style="color:red">✗ Directory missing</span>';
                ?>
                <?php 
                if (file_exists($upload_dir)) {
                    echo is_writable($upload_dir) ? 
                        '<span style="color:green">✓ Writable</span>' : 
                        '<span style="color:red">✗ Not writable</span>';
                }
                ?>
            </div>
            
            <!-- Brands List -->
            <div class="table-section">
                <h2>All Brands</h2>
                
                <?php if (mysqli_num_rows($brands) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($brand = mysqli_fetch_assoc($brands)): 
                            // Count products
                            $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE brand_id = {$brand['id']}");
                            $product_count = mysqli_fetch_assoc($count_query)['count'];
                            
                            // Get image path
                            $image_path = '';
                            if ($brand['image'] && file_exists($upload_dir . $brand['image'])) {
                                $image_path = '../assets/uploads/brands/' . $brand['image'];
                            }
                        ?>
                        <tr>
                            <td><?php echo $brand['id']; ?></td>
                            <td>
                                <?php if ($image_path): ?>
                                    <img src="<?php echo $image_path; ?>" 
                                         class="brand-logo-preview" 
                                         alt="<?php echo htmlspecialchars($brand['name']); ?>"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjBGMEYwIi8+Cjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTAiPkxvZ288L3RleHQ+Cjwvc3ZnPgo='">
                                <?php else: ?>
                                    <div style="width:80px;height:80px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:5px;color:#666;font-size:12px;">
                                        No Logo
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($brand['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($brand['description'] ?? '', 0, 50)) . (strlen($brand['description'] ?? '') > 50 ? '...' : ''); ?></td>
                            <td><?php echo $product_count; ?> product<?php echo $product_count != 1 ? 's' : ''; ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button onclick="openEditModal(
                                        <?php echo $brand['id']; ?>,
                                        '<?php echo addslashes($brand['name']); ?>',
                                        '<?php echo addslashes($brand['description'] ?? ''); ?>',
                                        '<?php echo $brand['image'] ?? ''; ?>'
                                    )" class="btn-sm">✏️ Edit</button>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this brand?')">
                                        <input type="hidden" name="brand_id" value="<?php echo $brand['id']; ?>">
                                        <button type="submit" name="delete_brand" class="btn-sm btn-danger">🗑️ Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                        <p style="font-size: 1.2rem; color: #666;">No brands found. Add your first brand!</p>
                        <button onclick="openAddModal()" class="btn" style="margin-top: 15px;">➕ Add First Brand</button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Add Brand Modal -->
    <div id="addBrandModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addBrandModal')">&times;</span>
            <h2>➕ Add New Brand</h2>
            <form method="POST" enctype="multipart/form-data" id="addBrandForm">
                <div class="form-group">
                    <label>Brand Name *</label>
                    <input type="text" name="name" required placeholder="e.g., Maybelline">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Brand description..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Brand Logo</label>
                    <div class="upload-area" onclick="document.getElementById('addBrandImage').click()">
                        <p>Click to upload logo</p>
                        <p><small>Drag & drop or click to browse</small></p>
                        <input type="file" name="image" id="addBrandImage" accept="image/*" style="display: none;" onchange="previewAddImage(event)">
                    </div>
                    <div id="addImagePreview"></div>
                    <p class="help-text">Recommended: Square image, max 500KB, PNG or JPG</p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="add_brand" class="btn btn-primary">➕ Add Brand</button>
                    <button type="button" onclick="closeModal('addBrandModal')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Brand Modal -->
    <div id="editBrandModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editBrandModal')">&times;</span>
            <h2>✏️ Edit Brand</h2>
            <form method="POST" enctype="multipart/form-data" id="editBrandForm">
                <input type="hidden" name="brand_id" id="editBrandId">
                
                <div class="form-group">
                    <label>Brand Name *</label>
                    <input type="text" name="name" id="editBrandName" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editBrandDescription" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Current Logo</label>
                    <div class="current-logo-container" id="currentLogoContainer">
                        <!-- Current logo will be shown here -->
                    </div>
                    
                    <label>Upload New Logo (optional)</label>
                    <div class="upload-area" onclick="document.getElementById('editBrandImage').click()">
                        <p>Click to upload new logo</p>
                        <p><small>Leave empty to keep current logo</small></p>
                        <input type="file" name="image" id="editBrandImage" accept="image/*" style="display: none;" onchange="previewEditImage(event)">
                    </div>
                    <div id="editImagePreview"></div>
                    <p class="help-text">Max 500KB, PNG or JPG format</p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="edit_brand" class="btn btn-primary">💾 Save Changes</button>
                    <button type="button" onclick="closeModal('editBrandModal')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Modal functions
    function openAddModal() {
        document.getElementById('addBrandModal').style.display = 'block';
        document.getElementById('addImagePreview').innerHTML = '';
        document.getElementById('addBrandForm').reset();
    }
    
    function openEditModal(id, name, description, image) {
        document.getElementById('editBrandId').value = id;
        document.getElementById('editBrandName').value = name;
        document.getElementById('editBrandDescription').value = description;
        
        // Show current logo
        const container = document.getElementById('currentLogoContainer');
        if (image) {
            container.innerHTML = `
                <p><strong>Current Logo:</strong></p>
                <img src="../assets/uploads/brands/${image}" 
                     class="image-preview" 
                     alt="Current Logo"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDE1MCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjBGMEYwIi8+Cjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiPkxvZ28gTWlzc2luZzwvdGV4dD4KPC9zdmc+Cg==';">
                <p class="help-text">File: ${image}</p>
            `;
        } else {
            container.innerHTML = '<p class="no-logo">No logo uploaded for this brand.</p>';
        }
        
        // Clear preview
        document.getElementById('editImagePreview').innerHTML = '';
        document.getElementById('editBrandModal').style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Image preview functions
    function previewAddImage(event) {
        const preview = document.getElementById('addImagePreview');
        preview.innerHTML = '';
        
        const file = event.target.files[0];
        if (file) {
            // Validate file size (500KB max)
            if (file.size > 500 * 1024) {
                alert('File size exceeds 500KB limit');
                event.target.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
                event.target.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    }
    
    function previewEditImage(event) {
        const preview = document.getElementById('editImagePreview');
        preview.innerHTML = '';
        
        const file = event.target.files[0];
        if (file) {
            // Validate file size (500KB max)
            if (file.size > 500 * 1024) {
                alert('File size exceeds 500KB limit');
                event.target.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
                event.target.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    }
    
    // Drag and drop functionality
    function setupDragDrop() {
        const uploadAreas = document.querySelectorAll('.upload-area');
        
        uploadAreas.forEach(area => {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                area.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                area.classList.add('drag-over');
            }
            
            function unhighlight() {
                area.classList.remove('drag-over');
            }
            
            area.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (area.id === 'addBrandImageArea') {
                    document.getElementById('addBrandImage').files = files;
                    previewAddImage({ target: document.getElementById('addBrandImage') });
                } else {
                    document.getElementById('editBrandImage').files = files;
                    previewEditImage({ target: document.getElementById('editBrandImage') });
                }
            }
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal('addBrandModal');
            closeModal('editBrandModal');
        }
    }
    
    // Close with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('addBrandModal');
            closeModal('editBrandModal');
        }
    });
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        setupDragDrop();
    });
    </script>
</body>
</html>