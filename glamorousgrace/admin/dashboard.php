<?php
require_once '../includes/config.php';
requireAdminLogin();

// Get statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count'];
$total_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status IN ('delivered', 'sold')"))['total'];
$total_sales = $total_sales ? $total_sales : 0;

// Get low stock products
$low_stock = mysqli_query($conn, "SELECT * FROM products WHERE quantity < 10 ORDER BY quantity ASC LIMIT 10");

// Get recent orders
$recent_orders = mysqli_query($conn, "
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");

// Get daily sales for chart (last 7 days)
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = '$date' AND status IN ('delivered', 'sold')";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $sales_data[] = [
        'date' => date('M d', strtotime($date)),
        'total' => $row['total'] ? $row['total'] : 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <h1>Dashboard</h1>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p class="stat-number"><?php echo $total_products; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Orders</h3>
                    <p class="stat-number"><?php echo $pending_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Sales</h3>
                    <p class="stat-number">$<?php echo number_format($total_sales, 2); ?></p>
                </div>
            </div>
            
            <!-- Sales Chart -->
            <div class="dashboard-section">
                <h2>Sales Chart (Last 7 Days)</h2>
                <div class="chart-container">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="dashboard-section">
                <h2>Low Stock Alert (< 10)</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Current Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = mysqli_fetch_assoc($low_stock)): ?>
                        <tr>
                            <td><?php echo $product['name']; ?></td>
                            <td>
                                <?php 
                                $brand = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM brands WHERE id = " . $product['brand_id']));
                                echo $brand['name'];
                                ?>
                            </td>
                            <td><span class="low-stock"><?php echo $product['quantity']; ?></span></td>
                            <td><a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-sm">Restock</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Recent Orders -->
            <div class="dashboard-section">
                <h2>Recent Orders</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td><?php echo $order['customer_name']; ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><a href="orders.php?view=<?php echo $order['id']; ?>" class="btn-sm">View</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
    // Simple Chart using Canvas
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?php echo json_encode($sales_data); ?>;
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(item => item.date),
            datasets: [{
                label: 'Daily Sales ($)',
                data: salesData.map(item => item.total),
                borderColor: '#ff6b8b',
                backgroundColor: 'rgba(255, 107, 139, 0.1)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>
</html>