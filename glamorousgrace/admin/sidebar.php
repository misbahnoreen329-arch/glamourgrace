<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <h2>GlamorousGrace</h2>
            <p class="admin-email"><?php echo isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin'; ?></p>
        </div>
    </div>
    
    <div class="sidebar-menu-container">
        <nav class="sidebar-menu">
            <!-- Dashboard -->
            <div class="menu-section">
                <h3 class="section-title">MAIN</h3>
                <ul>
                    <li>
                        <a href="dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">📊</span>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Product Management -->
            <div class="menu-section">
                <h3 class="section-title">PRODUCTS</h3>
                <ul>
                    <li>
                        <a href="products.php" class="menu-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">📦</span>
                            <span class="menu-text">All Products</span>
                            <?php 
                            $total_products = mysqli_fetch_assoc(mysqli_query($GLOBALS['conn'], 
                                "SELECT COUNT(*) as count FROM products"))['count'];
                            ?>
                            <span class="menu-badge"><?php echo $total_products; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="add_product.php" class="menu-item <?php echo $current_page == 'add_product.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">➕</span>
                            <span class="menu-text">Add Product</span>
                        </a>
                    </li>
                    <li>
                        <a href="categories.php" class="menu-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">🏷️</span>
                            <span class="menu-text">Categories</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Sales & Orders -->
            <div class="menu-section">
                <h3 class="section-title">SALES</h3>
                <ul>
                    <li>
                        <a href="orders.php" class="menu-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">🛒</span>
                            <span class="menu-text">Orders</span>
                            <?php 
                            $pending_orders = mysqli_fetch_assoc(mysqli_query($GLOBALS['conn'], 
                                "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count'];
                            if ($pending_orders > 0): ?>
                            <span class="menu-badge badge-danger"><?php echo $pending_orders; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="customers.php" class="menu-item <?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">👥</span>
                            <span class="menu-text">Customers</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Content Management -->
            <div class="menu-section">
                <h3 class="section-title">CONTENT</h3>
                <ul>
                    <li>
                        <a href="banners.php" class="menu-item <?php echo $current_page == 'banners.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">🖼️</span>
                            <span class="menu-text">Banners</span>
                        </a>
                    </li>
                    <li>
                        <a href="brands.php" class="menu-item <?php echo $current_page == 'brands.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">🏭</span>
                            <span class="menu-text">Brands</span>
                        </a>
                    </li>
                    <li>
                        <a href="offers.php" class="menu-item <?php echo $current_page == 'offers.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">🎁</span>
                            <span class="menu-text">Offers</span>
                        </a>
                    </li>
                    <li>
                        <a href="sections.php" class="menu-item <?php echo $current_page == 'sections.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">📑</span>
                            <span class="menu-text">Sections</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Quick Links -->
            <div class="menu-section">
                <h3 class="section-title">QUICK LINKS</h3>
                <ul>
                    <li>
                        <a href="../public/index.php" target="_blank" class="menu-item">
                            <span class="menu-icon">🌐</span>
                            <span class="menu-text">View Website</span>
                            <span class="menu-icon external">↗</span>
                        </a>
                    </li>
                    <li>
                        <a href="../public/products.php" target="_blank" class="menu-item">
                            <span class="menu-icon">🛍️</span>
                            <span class="menu-text">Shop Page</span>
                            <span class="menu-icon external">↗</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Account -->
            <div class="menu-section">
                <h3 class="section-title">ACCOUNT</h3>
                <ul>
                    <li>
                        <a href="settings.php" class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                            <span class="menu-icon">⚙️</span>
                            <span class="menu-text">Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="menu-item logout-item">
                            <span class="menu-icon">🚪</span>
                            <span class="menu-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    
    <div class="sidebar-footer">
        <div class="version-info">
            <p>Admin Panel v1.0</p>
            <p class="copyright">© <?php echo date('Y'); ?> GlamorousGrace</p>
        </div>
    </div>
</aside>

<style>
/* Sidebar Styles */
.admin-sidebar {
    width: 260px;
    background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
    color: white;
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    box-shadow: 3px 0 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Sidebar Header */
.sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.2);
}

.logo-container h2 {
    margin: 0;
    color: #fff;
    font-size: 1.5rem;
    font-weight: 600;
    background: linear-gradient(90deg, #ff6b8b, #ff8e53);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.admin-email {
    margin: 5px 0 0 0;
    color: #bdc3c7;
    font-size: 0.85rem;
    opacity: 0.8;
}

/* Menu Container with Scroll */
.sidebar-menu-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
}

/* Custom Scrollbar */
.sidebar-menu-container::-webkit-scrollbar {
    width: 5px;
}

.sidebar-menu-container::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar-menu-container::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.sidebar-menu-container::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Menu Sections */
.menu-section {
    margin-bottom: 25px;
}

.section-title {
    color: #95a5a6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 20px 10px;
    margin: 0;
    font-weight: 500;
    opacity: 0.7;
}

.sidebar-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Menu Items */
.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    position: relative;
}

.menu-item:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: #3498db;
}

.menu-item.active {
    background: linear-gradient(90deg, rgba(255,107,139,0.2) 0%, rgba(255,142,83,0.1) 100%);
    color: #ff6b8b;
    border-left-color: #ff6b8b;
    font-weight: 500;
}

.menu-item.active .menu-icon {
    color: #ff6b8b;
}

.menu-icon {
    font-size: 1.2rem;
    margin-right: 15px;
    width: 24px;
    text-align: center;
    color: #bdc3c7;
}

.menu-text {
    flex: 1;
    font-size: 0.95rem;
    font-weight: 400;
}

.menu-badge {
    background: #3498db;
    color: white;
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
    min-width: 20px;
    text-align: center;
}

.badge-danger {
    background: #e74c3c;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.external {
    font-size: 0.9rem;
    opacity: 0.6;
}

/* Logout Item */
.logout-item {
    color: #e74c3c;
}

.logout-item:hover {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    border-left-color: #e74c3c;
}

.logout-item .menu-icon {
    color: #e74c3c;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.2);
    text-align: center;
}

.version-info p {
    margin: 5px 0;
    font-size: 0.8rem;
    color: #95a5a6;
}

.copyright {
    font-size: 0.75rem;
    opacity: 0.7;
}

/* Responsive Design */
@media (max-width: 768px) {
    .admin-sidebar {
        width: 70px;
        overflow: visible;
    }
    
    .sidebar-header {
        padding: 20px 10px;
        text-align: center;
    }
    
    .logo-container h2 {
        font-size: 1rem;
    }
    
    .admin-email,
    .section-title,
    .menu-text,
    .menu-badge,
    .external,
    .version-info,
    .copyright {
        display: none;
    }
    
    .menu-item {
        padding: 15px;
        justify-content: center;
        border-left: none;
        border-right: 3px solid transparent;
    }
    
    .menu-item.active {
        border-left: none;
        border-right: 3px solid #ff6b8b;
    }
    
    .menu-item:hover {
        border-left: none;
        border-right-color: #3498db;
    }
    
    .menu-icon {
        margin-right: 0;
        font-size: 1.3rem;
    }
    
    .sidebar-menu-container {
        padding: 10px 0;
    }
    
    /* Tooltip for mobile */
    .menu-item:hover::after {
        content: attr(data-title);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #2c3e50;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        white-space: nowrap;
        margin-left: 10px;
        box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        z-index: 1001;
        pointer-events: none;
    }
    
    /* Add data-title attribute to menu items */
    .menu-item[href="dashboard.php"]::after { content: "Dashboard"; }
    .menu-item[href="products.php"]::after { content: "Products"; }
    .menu-item[href="add_product.php"]::after { content: "Add Product"; }
    .menu-item[href="orders.php"]::after { content: "Orders"; }
    .menu-item[href="banners.php"]::after { content: "Banners"; }
    .menu-item[href="brands.php"]::after { content: "Brands"; }
    .menu-item[href="offers.php"]::after { content: "Offers"; }
    .menu-item[href="sections.php"]::after { content: "Sections"; }
    .menu-item[href="../public/index.php"]::after { content: "View Website"; }
    .menu-item[href="settings.php"]::after { content: "Settings"; }
    .menu-item[href="logout.php"]::after { content: "Logout"; }
}

/* Dark theme variations */
@media (prefers-color-scheme: dark) {
    .admin-sidebar {
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    }
}

/* Animation for menu items */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.menu-item {
    animation: slideIn 0.3s ease forwards;
}

.menu-item:nth-child(1) { animation-delay: 0.1s; }
.menu-item:nth-child(2) { animation-delay: 0.2s; }
.menu-item:nth-child(3) { animation-delay: 0.3s; }
.menu-item:nth-child(4) { animation-delay: 0.4s; }
</style>

<script>
// Add active class to current page
document.addEventListener('DOMContentLoaded', function() {
    // Highlight active menu item
    const currentPage = '<?php echo $current_page; ?>';
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href').includes(currentPage)) {
            item.classList.add('active');
        }
    });
    
    // Mobile menu toggle (if needed)
    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.admin-sidebar');
        const menuItems = document.querySelectorAll('.menu-item');
        
        // Add data-title for tooltips
        menuItems.forEach(item => {
            const text = item.querySelector('.menu-text');
            if (text) {
                item.setAttribute('data-title', text.textContent);
            }
        });
        
        // Auto-hide tooltips after delay
        let tooltipTimer;
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                clearTimeout(tooltipTimer);
            });
            item.addEventListener('mouseleave', function() {
                tooltipTimer = setTimeout(() => {
                    this.style.setProperty('--tooltip-display', 'none');
                }, 1000);
            });
        });
    }
    
    // Smooth scroll for sidebar
    const menuContainer = document.querySelector('.sidebar-menu-container');
    if (menuContainer) {
        menuContainer.addEventListener('wheel', function(e) {
            if (this.scrollHeight > this.clientHeight) {
                e.preventDefault();
                this.scrollTop += e.deltaY;
            }
        });
    }
    
    // Logout confirmation
    const logoutLink = document.querySelector('.logout-item');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    }
});
</script>