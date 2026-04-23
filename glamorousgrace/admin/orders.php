<?php
require_once '../includes/config.php';
requireAdminLogin();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id = intval($_POST['order_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
        $success = "Order status updated successfully!";
    }
}

// Get all orders with customer info
$orders_query = "
    SELECT o.*, c.name as customer_name, c.phone, c.email 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC
";
$orders = mysqli_query($conn, $orders_query);

// Get order counts for filter
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count'];
$approved_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'approved'"))['count'];
$delivered_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .order-status-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .status-filter-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background: #f0f0f0;
            color: #333;
        }
        .status-filter-btn.active {
            background: #ff6b8b;
            color: white;
        }
        .status-filter-btn:hover {
            background: #ff8e53;
            color: white;
        }
        .order-details-modal {
            max-width: 800px;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .order-items-table th {
            background: #f8f9fa;
            padding: 10px;
            text-align: left;
        }
        .order-items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Order Management</h1>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Order Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <p class="stat-number"><?php echo $pending_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Approved</h3>
                    <p class="stat-number"><?php echo $approved_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Delivered</h3>
                    <p class="stat-number"><?php echo $delivered_orders; ?></p>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="table-section">
                <h2>All Orders</h2>
                
                <?php if (mysqli_num_rows($orders) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td>
                                <strong><?php echo $order['customer_name']; ?></strong><br>
                                <small><?php echo $order['phone']; ?></small>
                            </td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                        class="btn-sm">View</button>
                                <button onclick="openStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" 
                                        class="btn-sm">Update Status</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No orders found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-content order-details-modal">
            <span class="close-modal" onclick="closeModal('orderDetailsModal')">&times;</span>
            <div id="orderDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('statusModal')">&times;</span>
            <h2>Update Order Status</h2>
            <form method="POST" id="statusForm">
                <input type="hidden" name="order_id" id="statusOrderId">
                
                <div class="form-group">
                    <label>Select Status:</label>
                    <select name="status" id="statusSelect" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="processing">Processing</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="sold">Sold</option>
                    </select>
                </div>
                
                <button type="submit" name="update_status" class="btn">Update Status</button>
            </form>
        </div>
    </div>
    
    <script>
    function viewOrderDetails(orderId) {
        // Show loading
        document.getElementById('orderDetailsContent').innerHTML = '<p>Loading order details...</p>';
        document.getElementById('orderDetailsModal').style.display = 'block';
        
        // Fetch order details via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_order_details.php?order_id=' + orderId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('orderDetailsContent').innerHTML = xhr.responseText;
            } else {
                document.getElementById('orderDetailsContent').innerHTML = '<p>Error loading order details.</p>';
            }
        };
        xhr.send();
    }
    
    function openStatusModal(orderId, currentStatus) {
        document.getElementById('statusOrderId').value = orderId;
        document.getElementById('statusSelect').value = currentStatus;
        document.getElementById('statusModal').style.display = 'block';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal('orderDetailsModal');
            closeModal('statusModal');
        }
    }
    
    // Close with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('orderDetailsModal');
            closeModal('statusModal');
        }
    });
    </script>
</body>
</html>