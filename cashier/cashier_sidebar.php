<?php
// ============================================================
// CASHIER SIDEBAR - MODERN RESPONSIVE DESIGN
// Docks to left side, content flows to right
// Maintains ALL original PHP functionality
// ============================================================

if (!function_exists('cashier_active')) {
    function cashier_active($key, $activePage) {
        return $key === $activePage ? 'active' : '';
    }
}

$activePage = isset($activePage) ? $activePage : '';

$chars = '003232303232023232023456789';
$pass = '';
for ($i = 0; $i < 8; $i++) {
    $pass .= substr($chars, random_int(0, strlen($chars) - 1), 1);
}
$finalcode = 'RS-' . $pass;
?>

<!-- 
    ============================================================
    MODERN RESPONSIVE SIDEBAR
    - Docks to left side on desktop (fixed position)
    - Collapses to off-canvas/hamburger on mobile/tablet
    - Content flows responsively to the right
    - Maintains all original menu items and functionality
    ============================================================
-->

<style>
    /* ========== SIDEBAR MODERN STYLES ========== */
    :root {
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
        --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
        --sidebar-hover: rgba(165, 180, 252, 0.15);
        --sidebar-active: linear-gradient(90deg, #4f46e5, #6366f1);
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
    }

    /* Sidebar Container - Fixed on desktop */
    .modern-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width);
        background: var(--sidebar-bg);
        color: var(--text-primary);
        z-index: 1030;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        overflow-x: hidden;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        font-family: 'Inter', sans-serif;
    }

    /* Custom scrollbar for sidebar */
    .modern-sidebar::-webkit-scrollbar {
        width: 5px;
    }
    .modern-sidebar::-webkit-scrollbar-track {
        background: #1e293b;
    }
    .modern-sidebar::-webkit-scrollbar-thumb {
        background: #4f46e5;
        border-radius: 10px;
    }

    /* Sidebar Header / Brand */
    .sidebar-header {
        padding: 1.5rem 1.2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        margin-bottom: 1rem;
    }
    .sidebar-header h4 {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(120deg, #fff, #a5b4fc);
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        letter-spacing: -0.3px;
    }
    .sidebar-header small {
        font-size: 0.7rem;
        color: #64748b;
        display: block;
        margin-top: 0.25rem;
    }

    /* Navigation Menu */
    .sidebar-nav-modern {
        padding: 0 0.75rem 2rem 0.75rem;
    }
    .sidebar-nav-modern ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar-nav-modern li {
        margin-bottom: 0.25rem;
    }
    .sidebar-nav-modern li a {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.85rem 1rem;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 14px;
        transition: all 0.25s ease;
        font-weight: 500;
        font-size: 0.9rem;
    }
    .sidebar-nav-modern li a i {
        width: 1.6rem;
        font-size: 1.2rem;
        text-align: center;
        transition: transform 0.2s;
    }
    .sidebar-nav-modern li a:hover {
        background: var(--sidebar-hover);
        color: white;
        transform: translateX(4px);
    }
    .sidebar-nav-modern li a:hover i {
        transform: scale(1.05);
    }
    /* Active menu item */
    .sidebar-nav-modern li.active a {
        background: var(--sidebar-active);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
    .sidebar-nav-modern li.active a i {
        color: white;
    }
    /* Logout special styling */
    .sidebar-nav-modern li:last-of-type a {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        margin-top: 1rem;
        padding-top: 1rem;
    }
    .sidebar-nav-modern li:last-of-type a i {
        color: #f87171;
    }

    /* Clock Widget */
    .clock-widget {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 1rem;
        margin-top: 1.5rem;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .clock-widget .clock-time {
        font-size: 1.4rem;
        font-weight: 700;
        font-family: monospace;
        letter-spacing: 2px;
        color: #a5b4fc;
    }
    .clock-widget .clock-label {
        font-size: 0.7rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    /* ========== MAIN CONTENT AREA ========== */
    /* Content wrapper - pushed to the right of sidebar */
    .modern-content-wrapper {
        margin-left: var(--sidebar-width);
        transition: margin-left 0.3s ease;
        min-height: 100vh;
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
    }

    /* Mobile Menu Toggle Button (hidden on desktop) */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1040;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        border: none;
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 12px;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: all 0.2s;
    }
    .mobile-menu-toggle:hover {
        transform: scale(1.05);
    }

    /* Overlay for mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1025;
        backdrop-filter: blur(3px);
    }

    /* ========== RESPONSIVE BREAKPOINTS ========== */
    @media (max-width: 992px) {
        .modern-sidebar {
            transform: translateX(-100%);
            width: var(--sidebar-width);
            z-index: 1035;
        }
        .modern-sidebar.mobile-open {
            transform: translateX(0);
        }
        .modern-content-wrapper {
            margin-left: 0 !important;
            padding: 1rem;
        }
        .mobile-menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-overlay.active {
            display: block;
        }
        /* Add padding for fixed header on mobile */
        body {
            padding-top: 0;
        }
    }

    @media (min-width: 993px) and (max-width: 1200px) {
        :root {
            --sidebar-width: 260px;
        }
    }

    /* Collapsed state for desktop (optional - can be toggled) */
    .modern-sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }
    .modern-sidebar.collapsed .sidebar-header h4,
    .modern-sidebar.collapsed .sidebar-header small,
    .modern-sidebar.collapsed .sidebar-nav-modern li a span,
    .modern-sidebar.collapsed .clock-widget .clock-label {
        display: none;
    }
    .modern-sidebar.collapsed .sidebar-nav-modern li a {
        justify-content: center;
        padding: 0.85rem 0;
    }
    .modern-sidebar.collapsed .sidebar-nav-modern li a i {
        margin: 0;
        font-size: 1.3rem;
    }
    .modern-sidebar.collapsed .clock-widget .clock-time {
        font-size: 1rem;
    }
    .collapsed + .modern-content-wrapper {
        margin-left: var(--sidebar-collapsed-width);
    }
    /* Toggle collapse button */
    .sidebar-collapse-btn {
        position: absolute;
        bottom: 20px;
        right: -12px;
        background: #4f46e5;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: white;
        font-size: 0.7rem;
        z-index: 10;
    }
    @media (max-width: 992px) {
        .sidebar-collapse-btn {
            display: none;
        }
    }
</style>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modern Sidebar -->
<div class="modern-sidebar" id="modernSidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-cash-register me-2"></i> POS Cashier</h4>
        <small>Point of Sale System</small>
    </div>
    
    <div class="sidebar-nav-modern">
        <ul>
            <li class="<?php echo cashier_active('dashboard', $activePage); ?>">
                <a href="index.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo cashier_active('new_sale', $activePage); ?>">
                <a href="pos.php?invoice=<?php echo $finalcode; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>New Sale</span>
                </a>
            </li>
            <li class="<?php echo cashier_active('sales_history', $activePage); ?>">
                <a href="sales_history.php">
                    <i class="fas fa-history"></i>
                    <span>Sales History</span>
                </a>
            </li>
            <li class="<?php echo cashier_active('cash_float', $activePage); ?>">
                <a href="cash_float.php">
                    <i class="fas fa-coins"></i>
                    <span>Cash Float</span>
                </a>
            </li>
            <li class="<?php echo cashier_active('profile', $activePage); ?>">
                <a href="profile.php">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li>
                <a href="../index.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span style="color: #f87171;">Logout</span>
                </a>
            </li>
        </ul>
        
        <!-- Clock Widget - Preserving original functionality -->
        <div class="clock-widget">
            <div class="clock-time" id="liveClock">--:--:--</div>
            <div class="clock-label">
                <i class="far fa-clock me-1"></i> Current Time
            </div>
        </div>
    </div>
    
    <!-- Optional: Collapse/Expand button (desktop only) -->
    <div class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Collapse/Expand">
        <i class="fas fa-chevron-left"></i>
    </div>
</div>

<script>
// ============================================================
// SIDEBAR RESPONSIVE FUNCTIONALITY
// Preserves all original behavior while adding modern UX
// ============================================================
(function() {
    const sidebar = document.getElementById('modernSidebar');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const overlay = document.getElementById('sidebarOverlay');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const contentWrapper = document.getElementById('mainContentWrapper');
    
    // Check if we're on mobile
    function isMobile() {
        return window.innerWidth <= 992;
    }
    
    // Close mobile sidebar
    function closeMobileSidebar() {
        if (sidebar && isMobile()) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
        }
    }
    
    // Open mobile sidebar
    function openMobileSidebar() {
        if (sidebar && isMobile()) {
            sidebar.classList.add('mobile-open');
            if (overlay) overlay.classList.add('active');
        }
    }
    
    // Toggle mobile sidebar
    function toggleMobileSidebar() {
        if (sidebar.classList.contains('mobile-open')) {
            closeMobileSidebar();
        } else {
            openMobileSidebar();
        }
    }
    
    // Toggle desktop collapse
    let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    function toggleDesktopCollapse() {
        if (!isMobile()) {
            isCollapsed = !isCollapsed;
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                if (collapseBtn) collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                if (contentWrapper) contentWrapper.style.marginLeft = '80px';
            } else {
                sidebar.classList.remove('collapsed');
                if (collapseBtn) collapseBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                if (contentWrapper) contentWrapper.style.marginLeft = '280px';
            }
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
    }
    
    // Initialize collapse state
    if (collapseBtn) {
        collapseBtn.addEventListener('click', toggleDesktopCollapse);
        if (isCollapsed && !isMobile()) {
            sidebar.classList.add('collapsed');
            collapseBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            if (contentWrapper) contentWrapper.style.marginLeft = '80px';
        }
    }
    
    // Mobile toggle event
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleMobileSidebar);
    }
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }
    
    // Close sidebar on window resize if switching to desktop
    window.addEventListener('resize', function() {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            // Reset margin based on collapse state
            if (isCollapsed) {
                if (contentWrapper) contentWrapper.style.marginLeft = '80px';
            } else {
                if (contentWrapper) contentWrapper.style.marginLeft = '280px';
            }
        } else if (isMobile()) {
            if (contentWrapper) contentWrapper.style.marginLeft = '0';
            sidebar.classList.remove('collapsed');
        }
    });
    
    // ========== LIVE CLOCK (enhanced from original) ==========
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')} ${ampm}`;
        const clockElement = document.getElementById('liveClock');
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }
    updateClock();
    setInterval(updateClock, 1000);
    
    // Close sidebar when clicking a link on mobile (good UX)
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-modern a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (isMobile()) {
                // Don't close immediately to allow navigation, but close after short delay
                setTimeout(() => {
                    closeMobileSidebar();
                }, 150);
            }
        });
    });
    
    // Prevent body scroll when sidebar is open on mobile
    function preventBodyScroll(prevent) {
        if (prevent) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    // Observe sidebar open/close on mobile
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                if (sidebar.classList.contains('mobile-open')) {
                    preventBodyScroll(true);
                } else {
                    preventBodyScroll(false);
                }
            }
        });
    });
    observer.observe(sidebar, { attributes: true });
})();
</script>

<!-- 
    ============================================================
    IMPORTANT NOTES:
    - This sidebar maintains ALL original PHP functionality
    - All menu links preserve original href paths
    - The clock widget is enhanced but preserves the original concept
    - Original $activePage and $finalcode variables work exactly as before
    - For use in your POS system, include this file and wrap your 
      page content in a div with class="page-content-inside" or simply
      ensure your existing content is placed after this sidebar include
    - The main content area is now the .modern-content-wrapper div
    ============================================================
-->

<!-- 
    INSTRUCTIONS FOR INTEGRATION:
    1. Replace your existing sidebar.php with this file
    2. Make sure your main content area is wrapped appropriately
    3. The sidebar will automatically dock left and content flows right
    4. Fully responsive: collapses to hamburger menu on mobile
-->