<?php
require_once '../includes/config.php';
requireAdminLogin();

// First, let's create the offers table if it doesn't exist
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'offers'");
if (mysqli_num_rows($check_table) == 0) {
    // Create offers table
    $create_table = "CREATE TABLE offers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        start_date DATE,
        end_date DATE,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_table)) {
        // Insert some sample offers
        $sample_offers = [
            "INSERT INTO offers (title, description, discount_type, discount_value, start_date, end_date) 
             VALUES ('Summer Sale', 'Get amazing discounts on summer collection', 'percentage', 30, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY))",
            
            "INSERT INTO offers (title, description, discount_type, discount_value, start_date, end_date) 
             VALUES ('New Arrivals', 'Special discount on new products', 'percentage', 20, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY))",
            
            "INSERT INTO offers (title, description, discount_type, discount_value, start_date, end_date) 
             VALUES ('Free Shipping', 'Free shipping on orders above $50', 'fixed', 5, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY))"
        ];
        
        foreach ($sample_offers as $query) {
            mysqli_query($conn, $query);
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_offer'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $discount_type = mysqli_real_escape_string($conn, $_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle image upload
        $image_name = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_types)) {
                $image_name = 'offer_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = BANNER_UPLOAD_PATH . $image_name;
                
                if (!file_exists(BANNER_UPLOAD_PATH)) {
                    mkdir(BANNER_UPLOAD_PATH, 0777, true);
                }
                
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
            $query = "INSERT INTO offers (title, description, image, discount_type, discount_value, start_date, end_date, is_active) 
                      VALUES ('$title', '$description', '$image_name', '$discount_type', $discount_value, '$start_date', '$end_date', $is_active)";
            
            if (mysqli_query($conn, $query)) {
                $success = "Offer added successfully!";
                // Clear form or redirect
                echo '<script>setTimeout(function(){ window.location.href = "offers.php"; }, 1000);</script>';
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
    
    // Handle delete
    elseif (isset($_POST['delete_offer'])) {
        $id = intval($_POST['offer_id']);
        
        // Delete from database
        mysqli_query($conn, "DELETE FROM offers WHERE id = $id");
        
        $success = "Offer deleted successfully!";
        echo '<script>setTimeout(function(){ window.location.href = "offers.php"; }, 1000);</script>';
    }
    
    // Handle toggle status
    elseif (isset($_POST['toggle_status'])) {
        $id = intval($_POST['offer_id']);
        $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active FROM offers WHERE id = $id"));
        $new_status = $current['is_active'] ? 0 : 1;
        mysqli_query($conn, "UPDATE offers SET is_active = $new_status WHERE id = $id");
        $success = "Offer status updated!";
        echo '<script>setTimeout(function(){ window.location.href = "offers.php"; }, 1000);</script>';
    }
    
    // Handle edit
    elseif (isset($_POST['edit_offer'])) {
        $id = intval($_POST['offer_id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $discount_type = mysqli_real_escape_string($conn, $_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle image update
        $image_update = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_name = $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_types)) {
                // Delete old image if exists
                $old_image = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM offers WHERE id = $id"));
                if ($old_image['image'] && file_exists(BANNER_UPLOAD_PATH . $old_image['image'])) {
                    unlink(BANNER_UPLOAD_PATH . $old_image['image']);
                }
                
                // Upload new image
                $image_name = 'offer_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = BANNER_UPLOAD_PATH . $image_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_update = ", image = '$image_name'";
                }
            }
        }
        
        $query = "UPDATE offers SET 
                  title = '$title', 
                  description = '$description', 
                  discount_type = '$discount_type', 
                  discount_value = $discount_value, 
                  start_date = '$start_date', 
                  end_date = '$end_date', 
                  is_active = $is_active 
                  $image_update 
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            $success = "Offer updated successfully!";
            echo '<script>setTimeout(function(){ window.location.href = "offers.php"; }, 1000);</script>';
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all offers
$offers = mysqli_query($conn, "SELECT * FROM offers ORDER BY created_at DESC");

// Count offers
$total_offers = mysqli_num_rows($offers);
$active_offers = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM offers WHERE is_active = 1 
     AND start_date <= CURDATE() 
     AND end_date >= CURDATE()"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offers Management - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Modal Styles */
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
            width: 600px;
            max-width: 95%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            background: none;
            border: none;
        }
        .close-modal:hover {
            color: #333;
        }
        
        /* Offer Card Styles */
        .offer-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .offer-card:hover {
            transform: translateY(-5px);
        }
        .offer-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .offer-image {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
        }
        .offer-info {
            flex: 1;
        }
        .discount-badge {
            background: #ff6b8b;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        .date-range {
            color: #666;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        .offer-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .no-image {
            width: 150px;
            height: 100px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            color: #666;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .offer-header {
                flex-direction: column;
            }
            .offer-image {
                width: 100%;
                height: 150px;
                margin-right: 0;
                margin-bottom: 15px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Offers Management</h1>
                <button onclick="openAddModal()" class="btn">➕ Add New Offer</button>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($success)): ?>
                <div class="alert success" id="successMessage">
                    <?php echo $success; ?>
                    <button onclick="document.getElementById('successMessage').style.display='none'" 
                            style="float:right;background:none;border:none;cursor:pointer;font-size:20px;">×</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error" id="errorMessage">
                    <?php echo $error; ?>
                    <button onclick="document.getElementById('errorMessage').style.display='none'" 
                            style="float:right;background:none;border:none;cursor:pointer;font-size:20px;">×</button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <h3>Total Offers</h3>
                    <p class="stat-number"><?php echo $total_offers; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Offers</h3>
                    <p class="stat-number"><?php echo $active_offers; ?></p>
                </div>
            </div>
            
            <!-- Offers List -->
            <div class="table-section">
                <h2>All Offers</h2>
                
                <?php if (mysqli_num_rows($offers) > 0): ?>
                    <?php 
                    mysqli_data_seek($offers, 0); // Reset pointer
                    while($offer = mysqli_fetch_assoc($offers)): 
                        $is_current = ($offer['start_date'] <= date('Y-m-d') && $offer['end_date'] >= date('Y-m-d'));
                    ?>
                    <div class="offer-card">
                        <div class="offer-header">
                            <?php if($offer['image'] && file_exists(BANNER_UPLOAD_PATH . $offer['image'])): ?>
                                <img src="../assets/uploads/banners/<?php echo $offer['image']; ?>" 
                                     class="offer-image" alt="<?php echo $offer['title']; ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                            
                            <div class="offer-info">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <span class="discount-badge">
                                        <?php 
                                        if ($offer['discount_type'] == 'percentage') {
                                            echo $offer['discount_value'] . '% OFF';
                                        } else {
                                            echo '$' . number_format($offer['discount_value'], 2) . ' OFF';
                                        }
                                        ?>
                                    </span>
                                    
                                    <?php if($offer['is_active'] && $is_current): ?>
                                        <span class="status-published">Active</span>
                                    <?php elseif($offer['is_active'] && !$is_current): ?>
                                        <span class="status-draft">Scheduled</span>
                                    <?php else: ?>
                                        <span class="status-draft">Inactive</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($offer['title']); ?></h3>
                                
                                <?php if($offer['description']): ?>
                                    <p style="margin: 0 0 10px 0; color: #555;"><?php echo htmlspecialchars($offer['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="date-range">
                                    <strong>Valid:</strong> 
                                    <?php echo date('M d, Y', strtotime($offer['start_date'])); ?> 
                                    to 
                                    <?php echo date('M d, Y', strtotime($offer['end_date'])); ?>
                                </div>
                                
                                <div class="offer-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                        <button type="submit" name="toggle_status" class="btn-sm">
                                            <?php echo $offer['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    
                                    <button onclick="openEditModal(
                                        <?php echo $offer['id']; ?>,
                                        '<?php echo addslashes($offer['title']); ?>',
                                        '<?php echo addslashes($offer['description']); ?>',
                                        '<?php echo $offer['discount_type']; ?>',
                                        <?php echo $offer['discount_value']; ?>,
                                        '<?php echo $offer['start_date']; ?>',
                                        '<?php echo $offer['end_date']; ?>',
                                        <?php echo $offer['is_active']; ?>,
                                        '<?php echo $offer['image']; ?>'
                                    )" class="btn-sm">✏️ Edit</button>
                                    
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this offer?')">
                                        <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                        <button type="submit" name="delete_offer" class="btn-sm btn-danger">🗑️ Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                        <p style="font-size: 1.2rem; color: #666;">No offers found. Add your first offer!</p>
                        <button onclick="openAddModal()" class="btn" style="margin-top: 15px;">➕ Add First Offer</button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Add Offer Modal -->
    <div id="addOfferModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('addOfferModal')">&times;</button>
            <h2>➕ Add New Offer</h2>
            <form method="POST" enctype="multipart/form-data" id="addOfferForm">
                <div class="form-group">
                    <label>Offer Title *</label>
                    <input type="text" name="title" required placeholder="e.g., Summer Sale 50% OFF">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the offer..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Discount Type *</label>
                        <select name="discount_type" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount ($)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Discount Value *</label>
                        <input type="number" name="discount_value" step="0.01" min="0" required 
                               placeholder="e.g., 50 for 50% or 10 for $10">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" id="addStartDate" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" id="addEndDate" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Offer Image (Optional)</label>
                    <input type="file" name="image" id="addOfferImage" accept="image/*" onchange="previewAddImage(event)">
                    <div id="addImagePreview"></div>
                    <p class="help-text">Recommended: 800x400 pixels, JPG or PNG format</p>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" name="is_active" id="addIsActive" checked>
                    <label for="addIsActive">Activate this offer immediately</label>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="add_offer" class="btn btn-primary">➕ Add Offer</button>
                    <button type="button" onclick="closeModal('addOfferModal')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Offer Modal -->
    <div id="editOfferModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('editOfferModal')">&times;</button>
            <h2>✏️ Edit Offer</h2>
            <form method="POST" enctype="multipart/form-data" id="editOfferForm">
                <input type="hidden" name="offer_id" id="editOfferId">
                
                <div class="form-group">
                    <label>Offer Title *</label>
                    <input type="text" name="title" id="editOfferTitle" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editOfferDescription" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Discount Type *</label>
                        <select name="discount_type" id="editDiscountType" required>
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount ($)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Discount Value *</label>
                        <input type="number" name="discount_value" id="editDiscountValue" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" id="editStartDate" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" id="editEndDate" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Current Image:</label>
                    <div id="currentOfferImageContainer">
                        <!-- Current image will be shown here -->
                    </div>
                    
                    <label>Upload New Image (optional)</label>
                    <input type="file" name="image" id="editOfferImage" accept="image/*" onchange="previewEditImage(event)">
                    <div id="editImagePreview"></div>
                    <p class="help-text">Leave empty to keep current image</p>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" name="is_active" id="editIsActive">
                    <label for="editIsActive">Activate this offer</label>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="edit_offer" class="btn btn-primary">💾 Save Changes</button>
                    <button type="button" onclick="closeModal('editOfferModal')" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Modal Functions
    function openAddModal() {
        // Reset form
        document.getElementById('addOfferForm').reset();
        document.getElementById('addImagePreview').innerHTML = '';
        
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        const nextMonthStr = nextMonth.toISOString().split('T')[0];
        
        document.getElementById('addStartDate').value = today;
        document.getElementById('addEndDate').value = nextMonthStr;
        
        // Show modal
        document.getElementById('addOfferModal').style.display = 'block';
    }
    
    function openEditModal(id, title, description, discountType, discountValue, startDate, endDate, isActive, image) {
        // Set form values
        document.getElementById('editOfferId').value = id;
        document.getElementById('editOfferTitle').value = title;
        document.getElementById('editOfferDescription').value = description;
        document.getElementById('editDiscountType').value = discountType;
        document.getElementById('editDiscountValue').value = discountValue;
        document.getElementById('editStartDate').value = startDate;
        document.getElementById('editEndDate').value = endDate;
        document.getElementById('editIsActive').checked = isActive == 1;
        
        // Clear previews
        document.getElementById('editImagePreview').innerHTML = '';
        
        // Show current image
        const container = document.getElementById('currentOfferImageContainer');
        if (image) {
            container.innerHTML = `
                <p><strong>Current Image:</strong></p>
                <img src="../assets/uploads/banners/${image}" 
                     class="image-preview" 
                     alt="Current Offer Image"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <p style="display:none;color:#666;">Image file: ${image}</p>
            `;
        } else {
            container.innerHTML = '<p style="color:#666;">No image uploaded for this offer.</p>';
        }
        
        // Show modal
        document.getElementById('editOfferModal').style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Image Preview Functions
    function previewAddImage(event) {
        const preview = document.getElementById('addImagePreview');
        preview.innerHTML = '';
        
        const file = event.target.files[0];
        if (file) {
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size exceeds 2MB limit');
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
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size exceeds 2MB limit');
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
    
    // Form Validation
    document.getElementById('addOfferForm').addEventListener('submit', function(e) {
        const startDate = new Date(this.start_date.value);
        const endDate = new Date(this.end_date.value);
        
        if (startDate > endDate) {
            e.preventDefault();
            alert('End date must be after start date!');
            return false;
        }
        
        if (parseFloat(this.discount_value.value) <= 0) {
            e.preventDefault();
            alert('Discount value must be greater than 0!');
            return false;
        }
        
        return true;
    });
    
    document.getElementById('editOfferForm').addEventListener('submit', function(e) {
        const startDate = new Date(this.start_date.value);
        const endDate = new Date(this.end_date.value);
        
        if (startDate > endDate) {
            e.preventDefault();
            alert('End date must be after start date!');
            return false;
        }
        
        if (parseFloat(this.discount_value.value) <= 0) {
            e.preventDefault();
            alert('Discount value must be greater than 0!');
            return false;
        }
        
        return true;
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal('addOfferModal');
            closeModal('editOfferModal');
        }
    }
    
    // Close with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('addOfferModal');
            closeModal('editOfferModal');
        }
    });
    
    // Initialize date inputs with today's date
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) {
                input.value = today;
            }
        });
    });
    </script>
</body>
</html>