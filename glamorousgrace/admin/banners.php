<?php
require_once '../includes/config.php';
requireAdminLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_banner'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $subtitle = mysqli_real_escape_string($conn, $_POST['subtitle']);
        $link = mysqli_real_escape_string($conn, $_POST['link']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle image upload
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_types)) {
                $image_name = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = BANNER_UPLOAD_PATH . $image_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Resize image if needed
                    list($width, $height) = getimagesize($upload_path);
                    if ($width > 1920) {
                        resizeImage($upload_path, 1920, 600);
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image format. Allowed: JPG, JPEG, PNG, GIF, WEBP";
            }
        }
        
        if (empty($error)) {
            $query = "INSERT INTO banners (title, subtitle, image, link, is_active) 
                      VALUES ('$title', '$subtitle', '$image_name', '$link', $is_active)";
            
            if (mysqli_query($conn, $query)) {
                $success = "Banner added successfully!";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle delete
    elseif (isset($_POST['delete_banner'])) {
        $id = intval($_POST['banner_id']);
        
        // Get image name first
        $result = mysqli_query($conn, "SELECT image FROM banners WHERE id = $id");
        $banner = mysqli_fetch_assoc($result);
        
        // Delete from database
        mysqli_query($conn, "DELETE FROM banners WHERE id = $id");
        
        // Delete image file
        if ($banner['image'] && file_exists(BANNER_UPLOAD_PATH . $banner['image'])) {
            unlink(BANNER_UPLOAD_PATH . $banner['image']);
        }
        
        $success = "Banner deleted successfully!";
    }
    
    // Handle toggle status
    elseif (isset($_POST['toggle_status'])) {
        $id = intval($_POST['banner_id']);
        $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active FROM banners WHERE id = $id"));
        $new_status = $current['is_active'] ? 0 : 1;
        mysqli_query($conn, "UPDATE banners SET is_active = $new_status WHERE id = $id");
        $success = "Banner status updated!";
    }
}

// Get all banners
$banners = mysqli_query($conn, "SELECT * FROM banners ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Management - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .banner-preview {
            max-width: 300px;
            max-height: 150px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .image-upload-container {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .image-upload-container:hover {
            border-color: #ff6b8b;
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Banner Management</h1>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Add Banner Form -->
            <div class="form-section">
                <h2>Add New Banner</h2>
                <form method="POST" enctype="multipart/form-data" class="banner-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Subtitle</label>
                            <input type="text" name="subtitle">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Link URL</label>
                            <input type="url" name="link" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <div class="checkbox">
                                <input type="checkbox" name="is_active" id="is_active" checked>
                                <label for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Banner Image *</label>
                        <div class="image-upload-container">
                            <input type="file" name="image" id="bannerImage" accept="image/*" required 
                                   onchange="previewImage(this, 'bannerPreview')">
                            <p>Click to upload or drag and drop</p>
                            <p>Recommended size: 1920x600 pixels</p>
                            <img id="bannerPreview" class="banner-preview" style="display: none;">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_banner" class="btn">Add Banner</button>
                </form>
            </div>
            
            <!-- Existing Banners -->
            <div class="table-section">
                <h2>Existing Banners</h2>
                <?php if (mysqli_num_rows($banners) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Subtitle</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($banner = mysqli_fetch_assoc($banners)): ?>
                        <tr>
                            <td>
                                <?php if($banner['image']): ?>
                                    <img src="../assets/uploads/banners/<?php echo $banner['image']; ?>" 
                                         class="banner-preview" alt="<?php echo $banner['title']; ?>">
                                <?php else: ?>
                                    <span class="no-image">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $banner['title']; ?></td>
                            <td><?php echo $banner['subtitle']; ?></td>
                            <td>
                                <?php if($banner['is_active']): ?>
                                    <span class="status-published">Active</span>
                                <?php else: ?>
                                    <span class="status-draft">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($banner['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                    <button type="submit" name="toggle_status" class="btn-sm">
                                        <?php echo $banner['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Delete this banner?')">
                                    <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                                    <button type="submit" name="delete_banner" class="btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No banners found. Add your first banner above.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }
    
    // Drag and drop functionality
    const uploadContainer = document.querySelector('.image-upload-container');
    const fileInput = document.getElementById('bannerImage');
    
    if (uploadContainer && fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadContainer.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            uploadContainer.style.borderColor = '#ff6b8b';
            uploadContainer.style.backgroundColor = '#fff5f7';
        }
        
        function unhighlight() {
            uploadContainer.style.borderColor = '#ccc';
            uploadContainer.style.backgroundColor = 'transparent';
        }
        
        uploadContainer.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            fileInput.files = files;
            
            // Trigger change event
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
        
        // Click to upload
        uploadContainer.addEventListener('click', () => {
            fileInput.click();
        });
    }
    </script>
</body>
</html>