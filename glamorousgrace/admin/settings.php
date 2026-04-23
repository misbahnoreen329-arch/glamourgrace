<?php
require_once '../includes/config.php';
requireAdminLogin();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = md5($_POST['current_password']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check current password
    $admin_id = $_SESSION['admin_id'];
    $check_query = "SELECT * FROM admin_users WHERE id = $admin_id AND password = '$current_password'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 1) {
        if ($new_password === $confirm_password) {
            $hashed_password = md5($new_password);
            mysqli_query($conn, "UPDATE admin_users SET password = '$hashed_password' WHERE id = $admin_id");
            $success = "Password changed successfully!";
        } else {
            $error = "New passwords don't match!";
        }
    } else {
        $error = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GlamorousGrace Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin">
    <?php include '../includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Settings</h1>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="settings-section">
                <h2>Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn">Change Password</button>
                </form>
            </div>
            
            <div class="settings-section">
                <h2>System Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                    </div>
                    <div class="info-item">
                        <strong>MySQL Version:</strong> <?php echo mysqli_get_server_info($conn); ?>
                    </div>
                    <div class="info-item">
                        <strong>Admin Email:</strong> <?php echo $_SESSION['admin_username']; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>