<?php
require_once '../includes/config.php';
requireAdminLogin();

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        mysqli_query($conn, "INSERT INTO categories (name, description) VALUES ('$name', '$description')");
        $success = "Category added successfully!";
    }
    
    if (isset($_POST['edit_category'])) {
        $id = intval($_POST['category_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        mysqli_query($conn, "UPDATE categories SET name = '$name', description = '$description' WHERE id = $id");
        $success = "Category updated successfully!";
    }
    
    if (isset($_POST['delete_category'])) {
        $id = intval($_POST['category_id']);
        mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
        $success = "Category deleted successfully!";
    }
}

// Get all categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .category-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .category-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Categories Management</h1>
                <button onclick="openAddModal()" class="btn">➕ Add Category</button>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="category-grid">
                <?php while($category = mysqli_fetch_assoc($categories)): ?>
                <div class="category-card">
                    <h3><?php echo $category['name']; ?></h3>
                    <p><?php echo $category['description']; ?></p>
                    <div class="category-actions">
                        <button onclick="openEditModal(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo addslashes($category['description']); ?>')" 
                                class="btn-sm">Edit</button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?')">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit" name="delete_category" class="btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
    
    <!-- Modals will go here -->
    <script>
    function openAddModal() {
        alert('Add category functionality - to be implemented');
        // You can implement modal similar to brands.php
    }
    function openEditModal(id, name, description) {
        alert('Edit category: ' + name);
        // You can implement modal similar to brands.php
    }
    </script>
</body>
</html>