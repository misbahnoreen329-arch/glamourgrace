<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // In a real application, you would send an email here
    $success = "Thank you for your message, $name! We'll get back to you soon.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GlamorousGrace</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Contact Us</h1>
        
        <div class="contact-container">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <div class="contact-detail">
                    <h3>📍 Address</h3>
                    <p>123 Beauty Street<br>Makeup City, MC 12345</p>
                </div>
                <div class="contact-detail">
                    <h3>📞 Phone</h3>
                    <p>(555) 123-4567</p>
                </div>
                <div class="contact-detail">
                    <h3>📧 Email</h3>
                    <p>info@glamorousgrace.com</p>
                </div>
                <div class="contact-detail">
                    <h3>🕒 Hours</h3>
                    <p>Monday - Friday: 9AM - 6PM<br>Saturday: 10AM - 4PM<br>Sunday: Closed</p>
                </div>
            </div>
            
            <div class="contact-form">
                <h2>Send us a Message</h2>
                
                <?php if (isset($success)): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>