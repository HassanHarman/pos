<?php
// ============================================================
// ADMIN SIDEBAR - FULLY RESPONSIVE WITH REAL-TIME CLOCK
// Docks to left, content flows right, scrollable nav items
// Fixed logout button at bottom with real-time clock
// Preserves ALL original functionality and active page highlighting
// ============================================================

// expects $activePage string, e.g. 'dashboard','sales','products','customers','suppliers','salesreport','deliveries','lowstock','categories','receiving','purchase_orders','returns','users','settings','stock_audit','cash_float','advanced_reports','backup_export'
if (!isset($activePage)) {
    $activePage = '';
}

// reuse page-provided invoice code if available; otherwise generate a safe one without declaring functions
if (!isset($finalcode) || $finalcode === '') {
    $chars = '003232303232023232023456789';
    $pass = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < 8; $i++) {
        $pass .= $chars[random_int(0, $max)];
    }
    $finalcode = 'RS-' . $pass;
}

function owner_active($key, $activePage) {
    return $key === $activePage ? 'active' : '';
}
?>
<style>
        /* ============================================
           ADMIN SIDEBAR - MODERN RESPONSIVE DESIGN
           Fixed left, content flows right, scrollable nav
        ============================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Sidebar Container - Fixed on Left */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        /* Sidebar Header / Brand */
        .sidebar-brand {
            padding: 1.5rem 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }
        .sidebar-brand h3 {
            font-size: 1.2rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(120deg, #fff, #a5b4fc);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .sidebar-brand h3 i {
            color: #a5b4fc;
            font-size: 1.3rem;
        }
        .sidebar-brand small {
            font-size: 0.65rem;
            color: #64748b;
            display: block;
            margin-top: 0.3rem;
        }

        /* Scrollable Navigation Container */
        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.75rem 0;
        }
        
        /* Custom Scrollbar */
        .sidebar-nav-container::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-nav-container::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 10px;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb:hover {
            background: #6366f1;
        }

        /* Navigation Menu */
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-nav li {
            margin-bottom: 0.125rem;
        }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.2rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 0.85rem;
            border-left: 3px solid transparent;
        }
        .sidebar-nav li a i {
            width: 1.6rem;
            font-size: 1.1rem;
            text-align: center;
            transition: transform 0.2s;
        }
        .sidebar-nav li a:hover {
            background: rgba(165, 180, 252, 0.1);
            color: white;
            border-left-color: #a5b4fc;
        }
        .sidebar-nav li a:hover i {
            transform: translateX(3px);
        }
        
        /* Active Menu Item */
        .sidebar-nav li.active a {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.2), transparent);
            color: white;
            border-left-color: #4f46e5;
        }
        .sidebar-nav li.active a i {
            color: #a5b4fc;
        }

        /* Sidebar Footer - Fixed at Bottom */
        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem;
            background: rgba(15, 23, 42, 0.95);
        }
        
        /* Real-time Clock Widget */
        .clock-widget {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 0.8rem;
            text-align: center;
            margin-bottom: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .clock-time {
            font-size: 1.2rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            color: #a5b4fc;
        }
        .clock-date {
            font-size: 0.65rem;
            color: #64748b;
            margin-top: 0.2rem;
        }
        
        /* Logout Button */
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.3);
        }
        .logout-btn i {
            font-size: 1rem;
        }

        /* Main Content Wrapper - Pushed to Right */
        .admin-main-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* ============================================
           RESPONSIVE BREAKPOINTS
        ============================================ */
        @media (max-width: 992px) {
            .admin-sidebar {
                transform: translateX(-100%);
                z-index: 1050;
            }
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            .admin-main-wrapper {
                margin-left: 0 !important;
            }
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .sidebar-overlay.active {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 260px;
            }
        }

        /* Small screens */
        @media (max-width: 480px) {
            .admin-sidebar {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <!-- Brand Header -->
    <div class="sidebar-brand">
        <h3>
            <i class="fas fa-store"></i>
             Real Sisters POS
        </h3>
        <small>Administrator Panel</small>
    </div>
    
    <!-- Scrollable Navigation Container -->
    <div class="sidebar-nav-container">
        <ul class="sidebar-nav">
            <li class="<?php echo owner_active('dashboard', $activePage); ?>">
                <a href="index.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo owner_active('sales', $activePage); ?>">
                <a href="sales.php?id=cash&invoice=<?php echo $finalcode; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sales</span>
                </a>
            </li>
            <li class="<?php echo owner_active('products', $activePage); ?>">
                <a href="products.php">
                    <i class="fas fa-boxes"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="<?php echo owner_active('customers', $activePage); ?>">
                <a href="customer.php">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="<?php echo owner_active('suppliers', $activePage); ?>">
                <a href="supplier.php">
                    <i class="fas fa-truck"></i>
                    <span>Suppliers</span>
                </a>
            </li>
            <li class="<?php echo owner_active('salesreport', $activePage); ?>">
                <a href="salesreport.php?d1=0&d2=0">
                    <i class="fas fa-chart-bar"></i>
                    <span>Sales Report</span>
                </a>
            </li>
            <li class="<?php echo owner_active('deliveries', $activePage); ?>">
                <a href="deliveries.php">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Deliveries</span>
                </a>
            </li>
            <li class="<?php echo owner_active('lowstock', $activePage); ?>">
                <a href="lowstock.php">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Low Stock</span>
                </a>
            </li>
            <li class="<?php echo owner_active('zakat', $activePage); ?>">
                <a href="zakat.php">
                    <i class="fas fa-mosque"></i>
                    <span>Zakat</span>
                </a>
            </li>
            <li class="<?php echo owner_active('stock_portal', $activePage); ?>">
                <a href="../stock/index.php">
                    <i class="fas fa-warehouse"></i>
                    <span>Stock Portal</span>
                </a>
            </li>
            <li class="<?php echo owner_active('categories', $activePage); ?>">
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="<?php echo owner_active('receiving', $activePage); ?>">
                <a href="receiving.php">
                    <i class="fas fa-download"></i>
                    <span>Receiving</span>
                </a>
            </li>
            <li class="<?php echo owner_active('purchase_orders', $activePage); ?>">
                <a href="purchase_orders.php">
                    <i class="fas fa-file-invoice"></i>
                    <span>Purchase Orders</span>
                </a>
            </li>
            <li class="<?php echo owner_active('returns', $activePage); ?>">
                <a href="returns.php">
                    <i class="fas fa-undo-alt"></i>
                    <span>Returns</span>
                </a>
            </li>
            <li class="<?php echo owner_active('users', $activePage); ?>">
                <a href="users.php">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="<?php echo owner_active('settings', $activePage); ?>">
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="<?php echo owner_active('stock_audit', $activePage); ?>">
                <a href="stock_audit.php">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Stock Audit</span>
                </a>
            </li>
            <li class="<?php echo owner_active('cash_float', $activePage); ?>">
                <a href="cash_float.php">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Cash Float</span>
                </a>
            </li>
            <li class="<?php echo owner_active('advanced_reports', $activePage); ?>">
                <a href="advanced_reports.php">
                    <i class="fas fa-chart-pie"></i>
                    <span>Advanced Reports</span>
                </a>
            </li>
            <li class="<?php echo owner_active('backup_export', $activePage); ?>">
                <a href="backup_export.php">
                    <i class="fas fa-database"></i>
                    <span>Backup/Export</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Fixed Footer with Clock and Logout -->
    <div class="sidebar-footer">
        <!-- Real-time Clock Widget -->
        <div class="clock-widget">
            <div class="clock-time" id="liveClock">--:--:-- --</div>
            <div class="clock-date" id="liveDate">--</div>
        </div>
        
        <!-- Logout Button -->
        <a href="../index.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

<script>
(function() {
    // ============================================
    // REAL-TIME CLOCK FUNCTIONALITY
    // ============================================
    function updateClock() {
        const now = new Date();
        
        // Format time
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
        
        // Format date
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = now.toLocaleDateString('en-US', options);
        
        const clockElement = document.getElementById('liveClock');
        const dateElement = document.getElementById('liveDate');
        
        if (clockElement) clockElement.textContent = timeString;
        if (dateElement) dateElement.textContent = dateString;
    }
    
    updateClock();
    setInterval(updateClock, 1000);
    
    // ============================================
    // MOBILE SIDEBAR TOGGLE FUNCTIONALITY
    // ============================================
    const sidebar = document.getElementById('adminSidebar');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    function isMobile() {
        return window.innerWidth <= 992;
    }
    
    function closeMobileSidebar() {
        if (sidebar && isMobile()) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    function openMobileSidebar() {
        if (sidebar && isMobile()) {
            sidebar.classList.add('mobile-open');
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }
    
    // Close sidebar when clicking a nav link on mobile
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile()) {
                setTimeout(closeMobileSidebar, 150);
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
})();
</script>