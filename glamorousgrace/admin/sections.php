<?php
require_once '../includes/config.php';
requireAdminLogin();

// Handle section operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_section'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        
        $query = "INSERT INTO sections (name, type) VALUES ('$name', '$type')";
        mysqli_query($conn, $query);
        $section_id = mysqli_insert_id($conn);
        
        // Add products to section
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $product_id) {
                $product_id = intval($product_id);
                mysqli_query($conn, 
                    "INSERT INTO section_products (section_id, product_id) VALUES ($section_id, $product_id)");
            }
        }
        
        $success = "Section created successfully!";
    }
    
    elseif (isset($_POST['update_section'])) {
        $section_id = intval($_POST['section_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        mysqli_query($conn, 
            "UPDATE sections SET name = '$name', is_active = $is_active WHERE id = $section_id");
        
        // Update products
        mysqli_query($conn, "DELETE FROM section_products WHERE section_id = $section_id");
        
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $position => $product_id) {
                $product_id = intval($product_id);
                $position = intval($position) + 1;
                mysqli_query($conn, 
                    "INSERT INTO section_products (section_id, product_id, position) 
                     VALUES ($section_id, $product_id, $position)");
            }
        }
        
        $success = "Section updated successfully!";
    }
    
    elseif (isset($_POST['delete_section'])) {
        $section_id = intval($_POST['section_id']);
        mysqli_query($conn, "DELETE FROM sections WHERE id = $section_id");
        mysqli_query($conn, "DELETE FROM section_products WHERE section_id = $section_id");
        $success = "Section deleted successfully!";
    }
}

// Get all sections
$sections = mysqli_query($conn, 
    "SELECT s.*, 
     (SELECT COUNT(*) FROM section_products WHERE section_id = s.id) as product_count 
     FROM sections s ORDER BY created_at DESC");

// Get all products for selection
$all_products = mysqli_query($conn, 
    "SELECT p.*, pi.image_path 
     FROM products p 
     LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
     WHERE p.is_published = 1 
     ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Sections - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .section-types {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        .section-type {
            border: 2px solid #ddd;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
        }
        .section-type.selected {
            border-color: #ff6b8b;
            background: #fff5f7;
        }
        .product-selection {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
        }
        .product-checkbox {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .product-checkbox:last-child {
            border-bottom: none;
        }
        .product-checkbox img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Custom Sections</h1>
                <button onclick="openAddModal()" class="btn">Add New Section</button>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Sections List -->
            <div class="table-section">
                <h2>All Sections</h2>
                <?php if (mysqli_num_rows($sections) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($section = mysqli_fetch_assoc($sections)): 
                            // Get section products
                            $section_products = mysqli_query($conn, 
                                "SELECT p.name 
                                 FROM section_products sp 
                                 JOIN products p ON sp.product_id = p.id 
                                 WHERE sp.section_id = {$section['id']} 
                                 ORDER BY sp.position 
                                 LIMIT 3");
                        ?>
                        <tr>
                            <td><?php echo $section['name']; ?></td>
                            <td><?php echo $section['type']; ?></td>
                            <td>
                                <?php echo $section['product_count']; ?> products
                                <?php if ($section_products && mysqli_num_rows($section_products) > 0): ?>
                                    <br><small>
                                    <?php 
                                    $product_names = [];
                                    while($product = mysqli_fetch_assoc($section_products)) {
                                        $product_names[] = $product['name'];
                                    }
                                    echo implode(', ', $product_names);
                                    ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($section['is_active']): ?>
                                    <span class="status-published">Active</span>
                                <?php else: ?>
                                    <span class="status-draft">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($section['created_at'])); ?></td>
                            <td>
                                <button onclick="openEditModal(<?php echo $section['id']; ?>, 
                                        '<?php echo addslashes($section['name']); ?>', 
                                        '<?php echo $section['type']; ?>', 
                                        <?php echo $section['is_active']; ?>)" 
                                        class="btn-sm">Edit</button>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Delete this section?')">
                                    <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                                    <button type="submit" name="delete_section" class="btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No sections created yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Add Section Modal -->
    <div id="addSectionModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('addSectionModal')">&times;</span>
            <h2>Add New Section</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Section Name *</label>
                    <input type="text" name="name" required placeholder="e.g., Trending Makeup">
                </div>
                
                <div class="form-group">
                    <label>Section Type *</label>
                    <div class="section-types">
                        <div class="section-type" onclick="selectType(this, 'Trending')">Trending</div>
                        <div class="section-type" onclick="selectType(this, 'Bridal')">Bridal</div>
                        <div class="section-type" onclick="selectType(this, 'DailyWear')">Daily Wear</div>
                        <div class="section-type" onclick="selectType(this, 'Featured')">Featured</div>
                    </div>
                    <input type="hidden" name="type" id="sectionType" required>
                </div>
                
                <div class="form-group">
                    <label>Select Products</label>
                    <div class="product-selection">
                        <?php 
                        mysqli_data_seek($all_products, 0);
                        while($product = mysqli_fetch_assoc($all_products)): ?>
                        <div class="product-checkbox">
                            <input type="checkbox" name="products[]" 
                                   value="<?php echo $product['id']; ?>" 
                                   id="product_<?php echo $product['id']; ?>">
                            <?php if($product['image_path']): ?>
                                <img src="../assets/uploads/products/<?php echo $product['image_path']; ?>" 
                                     alt="<?php echo $product['name']; ?>">
                            <?php endif; ?>
                            <label for="product_<?php echo $product['id']; ?>">
                                <?php echo $product['name']; ?> 
                                <br><small>$<?php echo number_format($product['sale_price'], 2); ?></small>
                            </label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <button type="submit" name="add_section" class="btn">Create Section</button>
            </form>
        </div>
    </div>
    
    <!-- Edit Section Modal -->
    <div id="editSectionModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editSectionModal')">&times;</span>
            <h2>Edit Section</h2>
            <form method="POST">
                <input type="hidden" name="section_id" id="editSectionId">
                
                <div class="form-group">
                    <label>Section Name *</label>
                    <input type="text" name="name" id="editSectionName" required>
                </div>
                
                <div class="form-group">
                    <label>Section Type</label>
                    <input type="text" id="editSectionTypeDisplay" readonly>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" name="is_active" id="editSectionActive">
                    <label for="editSectionActive">Active Section</label>
                </div>
                
                <div class="form-group">
                    <label>Select Products (drag to reorder)</label>
                    <div class="product-selection" id="editProductsContainer">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
                
                <button type="submit" name="update_section" class="btn">Update Section</button>
            </form>
        </div>
    </div>
    
    <script>
    function openAddModal() {
        document.getElementById('addSectionModal').style.display = 'block';
    }
    
    function openEditModal(id, name, type, is_active) {
        document.getElementById('editSectionId').value = id;
        document.getElementById('editSectionName').value = name;
        document.getElementById('editSectionTypeDisplay').value = type;
        document.getElementById('editSectionActive').checked = is_active == 1;
        
        // Load section products
        loadSectionProducts(id);
        
        document.getElementById('editSectionModal').style.display = 'block';
    }
    
    function selectType(element, type) {
        // Remove selected class from all
        document.querySelectorAll('.section-type').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Add selected class to clicked
        element.classList.add('selected');
        document.getElementById('sectionType').value = type;
    }
    
    function loadSectionProducts(section_id) {
        const container = document.getElementById('editProductsContainer');
        container.innerHTML = '<p>Loading products...</p>';
        
        // AJAX request to get section products
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'get_section_products.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                container.innerHTML = xhr.responseText;
                makeSortable();
            }
        };
        xhr.send('section_id=' + section_id);
    }
    
    function makeSortable() {
        const container = document.getElementById('editProductsContainer');
        if (container) {
            Sortable.create(container, {
                animation: 150,
                onEnd: function() {
                    updateProductOrder();
                }
            });
        }
    }
    
    function updateProductOrder() {
        const checkboxes = document.querySelectorAll('#editProductsContainer input[type="checkbox"]');
        checkboxes.forEach((checkbox, index) => {
            checkbox.name = 'products[' + index + ']';
        });
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
</body>
</html>