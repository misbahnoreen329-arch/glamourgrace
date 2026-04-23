<?php
require_once '../includes/config.php';
requireAdminLogin();

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    
    // Get order details
    $order_query = "
        SELECT o.*, c.name as customer_name, c.email, c.phone, c.address, c.city 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.id = $order_id
    ";
    $order_result = mysqli_query($conn, $order_query);
    $order = mysqli_fetch_assoc($order_result);
    
    // Get order items
    $items_query = "
        SELECT oi.*, p.name as product_name, p.sale_price, pi.image_path 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_default = 1 
        WHERE oi.order_id = $order_id
    ";
    $items_result = mysqli_query($conn, $items_query);
?>
    <h2>Order #<?php echo $order['order_number']; ?></h2>
    
    <div class="customer-info">
        <h3>Customer Information</h3>
        <p><strong>Name:</strong> <?php echo $order['customer_name']; ?></p>
        <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
        <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
        <p><strong>Address:</strong> <?php echo nl2br($order['address']); ?></p>
        <p><strong>City:</strong> <?php echo $order['city']; ?></p>
    </div>
    
    <div class="order-info">
        <h3>Order Information</h3>
        <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
        <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
        <p><strong>Status:</strong> <span class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
        <?php if ($order['notes']): ?>
            <p><strong>Notes:</strong> <?php echo nl2br($order['notes']); ?></p>
        <?php endif; ?>
    </div>
    
    <h3>Order Items</h3>
    <table class="order-items-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            while($item = mysqli_fetch_assoc($items_result)): 
                $item_total = $item['price'] * $item['quantity'];
                $subtotal += $item_total;
            ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php if($item['image_path']): ?>
                            <img src="../assets/uploads/products/<?php echo $item['image_path']; ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                        <?php endif; ?>
                        <div>
                            <strong><?php echo $item['product_name']; ?></strong>
                        </div>
                    </div>
                </td>
                <td>$<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo number_format($item_total, 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Shipping:</strong></td>
                <td><strong>Free</strong></td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
            </tr>
        </tfoot>
    </table>
<?php
}
?>