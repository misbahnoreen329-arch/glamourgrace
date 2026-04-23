<?php
require_once '../includes/config.php';
requireAdminLogin();

// Get all customers
$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Customers Management</h1>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($customer = mysqli_fetch_assoc($customers)): 
                        // Count orders
                        $order_count = mysqli_fetch_assoc(mysqli_query($conn, 
                            "SELECT COUNT(*) as count FROM orders WHERE customer_id = {$customer['id']}"))['count'];
                    ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo $customer['name']; ?></td>
                        <td><?php echo $customer['email']; ?></td>
                        <td><?php echo $customer['phone']; ?></td>
                        <td><?php echo $order_count; ?></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>