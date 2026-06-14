<?php
// ============================================================
// CASHIER DASHBOARD - MODERN UI/UX
// PRESERVES ALL ORIGINAL PHP FUNCTIONALITY
// FORCED CONTENT TO START AT VERY TOP OF PAGE
// ============================================================

require_once('../main/auth.php');
require_role(array('cashier'));

// Generate invoice code for new sale link (preserving original logic)
$chars = '003232303232023232023456789';
$pass = '';
for ($i = 0; $i < 8; $i++) {
    $pass .= substr($chars, random_int(0, strlen($chars) - 1), 1);
}
$finalcode = 'RS-' . $pass;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Cashier Dashboard | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* RESET ALL MARGINS AND PADDINGS - FORCE TOP POSITION */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
            position: relative;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            overflow-y: auto;
            position: relative;
            top: 0;
            left: 0;
        }

        /* Remove any possible spacing from PHP includes */
        .container-fluid, .row-fluid, [class*="span"] {
            margin: 0;
            padding: 0;
        }

        /* ============================================
           FIXED SIDEBAR - DOCKS TO LEFT
        ============================================ */
        
        .modern-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        }

        /* Main content area - starts at VERY TOP */
        .main-content-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            padding: 0 !important;
            margin-top: 0 !important;
            position: relative;
            top: 0;
        }

        /* Dashboard Content - NO top padding/margin */
        .dashboard-content {
            padding: 1.5rem 2rem;
            max-width: 100%;
            margin: 0 !important;
            padding-top: 1.5rem;
        }

        /* Welcome Header - NO margin top */
        .welcome-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 24px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            margin-top: 0 !important;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(165,180,252,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .welcome-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .welcome-header h1 i {
            color: #a5b4fc;
            font-size: 2rem;
        }
        .welcome-header .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-size: 0.85rem;
            backdrop-filter: blur(10px);
        }

        /* Section Title */
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-title i {
            color: #4f46e5;
            font-size: 1.3rem;
        }
        
        /* Modern Grid for Menu Items */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            margin-top: 0;
        }
        
        .action-card {
            background: white;
            border-radius: 24px;
            padding: 2rem 1rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.15);
            border-color: #c7d2fe;
        }
        .action-card i {
            font-size: 2.8rem;
            transition: transform 0.2s;
        }
        .action-card:hover i {
            transform: scale(1.1);
        }
        .action-card span {
            font-weight: 600;
            font-size: 1rem;
            color: #1e293b;
        }
        .action-card small {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Info Banner */
        .info-banner {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            border-radius: 20px;
            padding: 1rem 1.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .info-banner p {
            margin: 0;
            font-size: 0.85rem;
            color: #4338ca;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-banner a {
            color: #4f46e5;
            font-weight: 600;
            text-decoration: none;
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

        /* Clearfix */
        .clearfix {
            clear: both;
        }

        /* Force any hidden elements from navfixed to not create space */
        .navbar-fixed-top, .navbar, .navfixed {
            position: relative;
            margin: 0;
            padding: 0;
        }

        /* ============================================
           RESPONSIVE BREAKPOINTS
        ============================================ */
        @media (max-width: 992px) {
            .modern-sidebar {
                transform: translateX(-100%);
                z-index: 1050;
            }
            .modern-sidebar.mobile-open {
                transform: translateX(0);
            }
            .main-content-wrapper {
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
            .dashboard-content {
                padding: 1rem;
                padding-top: 70px;
            }
            .welcome-header h1 {
                font-size: 1.3rem;
            }
            .welcome-header {
                padding: 1.2rem 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-content {
                padding: 1rem;
                padding-top: 70px;
            }
            .actions-grid {
                gap: 1rem;
            }
            .action-card {
                padding: 1.2rem 0.5rem;
            }
            .action-card i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Include the modern sidebar (this will be fixed on the left) -->
<?php 
$activePage = 'dashboard'; 
include('cashier_sidebar.php'); 
?>

<!-- Main Content Wrapper - Pushed to the right of sidebar -->
<div class="main-content-wrapper">
    <div class="dashboard-content">
        
        <!-- Welcome Header - starts at top -->
        <div class="welcome-header" data-aos="fade-down">
            <h1>
                <i class="fas fa-chart-line"></i>
                Cashier Dashboard
            </h1>
            <div class="date-badge">
                <i class="fas fa-calendar-alt"></i>
                <?php
                // Preserving original date format
                $Today = date('y:m:d');
                $new = date('l, F d, Y', strtotime($Today));
                echo $new;
                ?>
                <i class="fas fa-clock ms-2"></i>
                <span id="liveTime">--:-- --</span>
            </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div class="section-title" data-aos="fade-right">
            <i class="fas fa-bolt"></i>
            <span>Quick Actions</span>
        </div>
        
        <div class="actions-grid" data-aos="fade-up" data-aos-delay="100">
            <!-- New Sale -->
            <a href="pos.php?invoice=<?php echo $finalcode; ?>" class="action-card">
                <i class="fas fa-shopping-cart" style="color: #4f46e5;"></i>
                <span>New Sale</span>
                <small>Start a new transaction</small>
            </a>
            
            <!-- Sales History -->
            <a href="sales_history.php" class="action-card">
                <i class="fas fa-list" style="color: #059669;"></i>
                <span>Sales History</span>
                <small>View past transactions</small>
            </a>
            
            <!-- Cash Float -->
            <a href="cash_float.php" class="action-card">
                <i class="fas fa-money-bill-wave" style="color: #d97706;"></i>
                <span>Cash Float</span>
                <small>Manage cash drawer</small>
            </a>
            
            <!-- My Profile -->
            <a href="profile.php" class="action-card">
                <i class="fas fa-user" style="color: #8b5cf6;"></i>
                <span>My Profile</span>
                <small>View account settings</small>
            </a>
            
            <!-- Logout -->
            <a href="../index.php" class="action-card" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt" style="color: #ef4444;"></i>
                <span>Logout</span>
                <small>Exit the system</small>
            </a>
        </div>
        
        <!-- Info Banner -->
        <div class="info-banner" data-aos="fade-up" data-aos-delay="150">
            <p>
                <i class="fas fa-lightbulb"></i>
                <strong>Quick Tip:</strong> Start a new sale by clicking the New Sale button above. Always verify cash float before starting your shift.
            </p>
            <a href="cash_float.php">
                Check Cash Float <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="clearfix"></div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS animations
    AOS.init({
        duration: 500,
        once: true,
        offset: 10
    });
    
    // Live clock update
    function updateLiveTime() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const timeString = `${hours}:${minutes} ${ampm}`;
        const timeElement = document.getElementById('liveTime');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }
    updateLiveTime();
    setInterval(updateLiveTime, 60000);
    
    // Mobile sidebar functionality
    const sidebar = document.getElementById('modernSidebar');
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
        mobileToggle.addEventListener('click', function() {
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
    
    // Close sidebar when clicking a link on mobile
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-modern a');
    sidebarLinks.forEach(link => {
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
    
    // Hover animations
    document.querySelectorAll('.action-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // FORCE PAGE TO START AT VERY TOP - MULTIPLE METHODS
    // Method 1: Scroll to top immediately
    window.scrollTo(0, 0);
    
    // Method 2: Disable scroll restoration
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    
    // Method 3: Force scroll to top after all content loads
    window.addEventListener('load', function() {
        window.scrollTo(0, 0);
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    });
    
    // Method 4: Force scroll to top after any delay
    setTimeout(function() {
        window.scrollTo(0, 0);
    }, 10);
    
    // Method 5: Focus on body to ensure top position
    document.body.focus();
</script>

</body>
</html>