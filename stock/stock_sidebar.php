<?php
if (!isset($activePage)) {
    $activePage = '';
}

if (!function_exists('stock_active')) {
    function stock_active($key, $activePage) {
        return $key === $activePage ? 'active' : '';
    }
}
?>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

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

    .sidebar-nav-container {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0.75rem 0;
    }

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

    .sidebar-nav li.active a {
        background: linear-gradient(90deg, rgba(79, 70, 229, 0.2), transparent);
        color: white;
        border-left-color: #4f46e5;
    }

    .sidebar-nav li.active a i {
        color: #a5b4fc;
    }

    .sidebar-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding: 1rem;
        background: rgba(15, 23, 42, 0.95);
    }

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
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 18px -12px rgba(220, 38, 38, 0.6);
    }

    .admin-main-wrapper {
        margin-left: 280px;
        min-height: 100vh;
        transition: margin-left 0.3s ease;
    }

    .mobile-menu-toggle {
        position: fixed;
        top: 16px;
        left: 16px;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        border: none;
        background: #4f46e5;
        color: white;
        z-index: 1101;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    @media (max-width: 992px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }
        .admin-sidebar.mobile-open {
            transform: translateX(0);
        }
        .admin-main-wrapper {
            margin-left: 0 !important;
        }
        .mobile-menu-toggle {
            display: flex;
        }
    }
</style>

<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-warehouse"></i> Real Sisters POS</h3>
        <small>Stock Manager Panel</small>
    </div>

    <div class="sidebar-nav-container">
        <ul class="sidebar-nav">
            <li class="<?php echo stock_active('dashboard', $activePage); ?>">
                <a href="index.php"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
            </li>
            <li class="<?php echo stock_active('products', $activePage); ?>">
                <a href="products.php"><i class="fas fa-boxes"></i><span>Products</span></a>
            </li>
            <li class="<?php echo stock_active('lowstock', $activePage); ?>">
                <a href="lowstock.php"><i class="fas fa-triangle-exclamation"></i><span>Low Stock</span></a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="clock-widget">
            <div class="clock-time" id="liveClock">--:--:--</div>
            <div class="clock-date" id="liveDate">--</div>
        </div>
        <a class="logout-btn" href="../index.php" onclick="return confirm('Are you sure you want to logout?');">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
(function() {
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        const timeString = `${hours.toString().padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
        const dateString = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const clockElement = document.getElementById('liveClock');
        const dateElement = document.getElementById('liveDate');
        if (clockElement) clockElement.textContent = timeString;
        if (dateElement) dateElement.textContent = dateString;
    }

    updateClock();
    setInterval(updateClock, 1000);

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

    const navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile()) {
                setTimeout(closeMobileSidebar, 150);
            }
        });
    });

    window.addEventListener('resize', function() {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
})();
</script>
