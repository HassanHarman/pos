<?php
// ============================================================
// DELIVERIES MANAGEMENT PAGE - MODERN UI/UX
// Fully responsive with status management
// Currency: UGX (Ugandan Shilling)
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));
include('../connect.php');

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

// Get delivery statistics
$statsQuery = $db->prepare("
    SELECT 
        COUNT(*) as total_deliveries,
        SUM(CASE WHEN delivery_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN delivery_status = 'dispatched' THEN 1 ELSE 0 END) as dispatched_count,
        SUM(CASE WHEN delivery_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
        SUM(CASE WHEN delivery_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        COALESCE(SUM(total_amount), 0) as total_revenue
    FROM sales 
    WHERE sale_type = 'delivery'
");
$statsQuery->execute();
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Deliveries | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            overflow-x: hidden;
        }

        /* Container that holds sidebar and content together */
        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar is fixed width */
        .app-sidebar {
            width: 280px;
            flex-shrink: 0;
            position: relative;
        }

        /* Main content takes remaining space */
        .app-content {
            flex: 1;
            min-width: 0;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        /* Page Header */
        .page-header-modern {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header-modern h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .page-header-modern h2 i {
            color: #a5b4fc;
            font-size: 1.5rem;
        }
        .currency-badge {
            background: rgba(165, 180, 252, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Breadcrumb */
        .breadcrumb-modern {
            background: transparent;
            padding: 0.5rem 0;
            margin-bottom: 1rem;
        }
        .breadcrumb-modern a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .breadcrumb-modern span {
            color: #64748b;
            font-size: 0.85rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.2s;
            text-align: center;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 45px;
            height: 45px;
            background: #eef2ff;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
        }
        .stat-icon i {
            font-size: 1.3rem;
            color: #4f46e5;
        }
        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
        }
        .stat-label {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 0.2rem;
        }
        .stat-label i {
            margin-right: 0.2rem;
        }

        /* Status Colors */
        .status-pending {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-dispatched {
            background: #dbeafe;
            color: #2563eb;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-delivered {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }

        /* Deliveries Table Container */
        .deliveries-table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .deliveries-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 900px;
        }
        .deliveries-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .deliveries-table tbody td {
            padding: 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .deliveries-table tbody tr:hover {
            background: #fafbff;
        }
        .invoice-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
        .invoice-link:hover {
            text-decoration: underline;
        }

        /* Status Form */
        .status-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .status-select {
            padding: 0.4rem 0.6rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.7rem;
            font-family: 'Inter', sans-serif;
            background: white;
            cursor: pointer;
        }
        .status-select:focus {
            outline: none;
            border-color: #4f46e5;
        }
        .btn-save {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.35rem 0.8rem;
            border-radius: 12px;
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-save:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-1px);
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }
        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .app-sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                z-index: 1050;
                transition: left 0.3s ease;
            }
            .app-sidebar.mobile-open {
                left: 0;
            }
            .app-content {
                width: 100%;
                padding: 1rem;
                margin-top: 60px;
            }
            .mobile-menu-toggle {
                display: flex;
                position: fixed;
                top: 12px;
                left: 12px;
                z-index: 1060;
                background: linear-gradient(135deg, #4f46e5, #6366f1);
                border: none;
                color: white;
                width: 42px;
                height: 42px;
                border-radius: 12px;
                font-size: 1.1rem;
                cursor: pointer;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            .sidebar-overlay.active {
                display: block;
            }
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle, .sidebar-overlay {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
            }
            .stat-value {
                font-size: 1rem;
            }
            .status-form {
                flex-direction: column;
                align-items: flex-start;
            }
            .btn-save {
                width: 100%;
                justify-content: center;
            }
            .status-select {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .page-header-modern .currency-badge, .btn-save, .status-select {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .deliveries-table-container {
                box-shadow: none;
                padding: 0;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-container">
    <!-- Sidebar -->
    <div class="app-sidebar" id="appSidebar">
        <?php
        $role = function_exists('current_role') ? current_role() : '';
        if ($role === 'owner') {
            $activePage = 'deliveries';
            include('owner_sidebar.php');
        } else {
            $activePage = 'deliveries';
            include('manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-truck"></i>
                Delivery Management
            </h2>
            <div class="currency-badge">
                <i class="fas fa-coins"></i>
                UGX (Ugandan Shilling)
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Deliveries</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                <div class="stat-value"><?php echo $stats['total_deliveries']; ?></div>
                <div class="stat-label"><i class="fas fa-chart-line"></i> Total Deliveries</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                <div class="stat-label"><i class="fas fa-hourglass-half"></i> Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="stat-value"><?php echo $stats['dispatched_count']; ?></div>
                <div class="stat-label"><i class="fas fa-truck-moving"></i> Dispatched</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?php echo $stats['delivered_count']; ?></div>
                <div class="stat-label"><i class="fas fa-check"></i> Delivered</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-value"><?php echo $stats['cancelled_count']; ?></div>
                <div class="stat-label"><i class="fas fa-ban"></i> Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value"><?php echo formatUGX($stats['total_revenue']); ?></div>
                <div class="stat-label"><i class="fas fa-chart-line"></i> Total Revenue</div>
            </div>
        </div>
        
        <!-- Deliveries Table -->
        <div class="deliveries-table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="deliveries-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar-day me-1"></i> Date</th>
                        <th><i class="fas fa-file-invoice me-1"></i> Invoice</th>
                        <th><i class="fas fa-user me-1"></i> Customer</th>
                        <th><i class="fas fa-phone me-1"></i> Phone</th>
                        <th><i class="fas fa-map-marker-alt me-1"></i> Address</th>
                        <th><i class="fas fa-money-bill-wave me-1"></i> Total (UGX)</th>
                        <th><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th><i class="fas fa-cog me-1"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $db->prepare("SELECT transaction_id, invoice_number, date, name, customer_phone, delivery_address, delivery_status, total_amount, amount FROM sales WHERE sale_type='delivery' ORDER BY transaction_id DESC");
                    $result->execute();
                    $hasResults = false;
                    while($row = $result->fetch()):
                        $hasResults = true;
                        $display_total = $row['total_amount'] !== null ? $row['total_amount'] : $row['amount'];
                        $statusClass = '';
                        switch($row['delivery_status']) {
                            case 'pending': $statusClass = 'status-pending'; break;
                            case 'dispatched': $statusClass = 'status-dispatched'; break;
                            case 'delivered': $statusClass = 'status-delivered'; break;
                            case 'cancelled': $statusClass = 'status-cancelled'; break;
                        }
                    ?>
                    <tr class="record">
                        <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                        <td><a href="preview.php?invoice=<?php echo $row['invoice_number']; ?>" class="invoice-link" target="_blank">
                            <i class="fas fa-receipt"></i> <?php echo $row['invoice_number']; ?>
                        </a></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['delivery_address']); ?></td>
                        <td class="fw-bold" style="color:#4f46e5;"><?php echo formatUGX($display_total); ?></td>
                        <td><span class="<?php echo $statusClass; ?>"><i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> <?php echo ucfirst($row['delivery_status']); ?></span></td>
                        <td>
                            <form action="update_delivery_status.php" method="post" class="status-form">
                                <input type="hidden" name="id" value="<?php echo $row['transaction_id']; ?>" />
                                <select name="delivery_status" class="status-select">
                                    <option value="pending" <?php echo ($row['delivery_status']=='pending') ? 'selected' : ''; ?>>📋 Pending</option>
                                    <option value="dispatched" <?php echo ($row['delivery_status']=='dispatched') ? 'selected' : ''; ?>>🚚 Dispatched</option>
                                    <option value="delivered" <?php echo ($row['delivery_status']=='delivered') ? 'selected' : ''; ?>>✅ Delivered</option>
                                    <option value="cancelled" <?php echo ($row['delivery_status']=='cancelled') ? 'selected' : ''; ?>>❌ Cancelled</option>
                                </select>
                                <button class="btn-save" type="submit">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </form>
                         </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-truck"></i>
                            <p>No delivery orders found.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Mobile sidebar functionality
    const sidebar = document.getElementById('appSidebar');
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
    
    window.addEventListener('resize', function() {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Force scroll to top
    if('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
    
    // Auto-refresh status update (optional - shows success message)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status_updated')) {
        const statusMsg = document.createElement('div');
        statusMsg.className = 'alert-modern';
        statusMsg.style.cssText = 'background:#d1fae5; color:#065f46; padding:0.7rem 1rem; border-radius:14px; margin-bottom:1rem; display:flex; align-items:center; gap:0.6rem;';
        statusMsg.innerHTML = '<i class="fas fa-check-circle"></i> Delivery status updated successfully!';
        const container = document.querySelector('.deliveries-table-container');
        if (container) {
            container.parentNode.insertBefore(statusMsg, container);
            setTimeout(() => { statusMsg.remove(); }, 3000);
        }
        // Remove query parameter
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>

</body>
</html>