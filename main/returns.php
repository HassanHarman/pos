<?php
// ============================================================
// RETURNS MANAGEMENT PAGE - MODERN UI/UX
// Fully responsive with Customer & Supplier Returns
// Currency: UGX (Ugandan Shilling)
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

// Get return statistics
$customerStats = $db->prepare("SELECT COUNT(*) as count, COALESCE(SUM(refund_amount), 0) as total FROM returns");
$customerStats->execute();
$customerData = $customerStats->fetch(PDO::FETCH_ASSOC);

$supplierStats = $db->prepare("SELECT COUNT(*) as count FROM supplier_returns");
$supplierStats->execute();
$supplierCount = $supplierStats->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Returns | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Facebox -->
    <link href="src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
    
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            background: #eef2ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon i {
            font-size: 1.5rem;
            color: #4f46e5;
        }
        .stat-icon.customer {
            background: #dbeafe;
        }
        .stat-icon.customer i {
            color: #2563eb;
        }
        .stat-icon.supplier {
            background: #fef3c7;
        }
        .stat-icon.supplier i {
            color: #d97706;
        }
        .stat-icon.refund {
            background: #d1fae5;
        }
        .stat-icon.refund i {
            color: #059669;
        }
        .stat-info h4 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
            color: #1e293b;
        }
        .stat-info p {
            margin: 0;
            font-size: 0.65rem;
            color: #64748b;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .btn-customer-return {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-customer-return:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
            transform: translateY(-2px);
            color: white;
        }
        .btn-supplier-return {
            background: linear-gradient(135deg, #d97706, #b45309);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-supplier-return:hover {
            background: linear-gradient(135deg, #b45309, #92400e);
            transform: translateY(-2px);
            color: white;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .section-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .section-header i {
            color: #4f46e5;
            font-size: 1.2rem;
        }

        /* Returns Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .returns-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 700px;
        }
        .returns-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .returns-table tbody td {
            padding: 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .returns-table tbody tr:hover {
            background: #fafbff;
        }

        /* Alert Messages */
        .alert-modern {
            border-radius: 14px;
            padding: 0.8rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.85rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #94a3b8;
        }
        .no-data i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
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
                grid-template-columns: repeat(2, 1fr);
            }
            .action-bar {
                flex-direction: column;
            }
            .btn-customer-return, .btn-supplier-return {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle, .sidebar-overlay {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                gap: 0.8rem;
            }
            .stat-card {
                padding: 0.8rem;
            }
            .stat-info h4 {
                font-size: 1rem;
            }
            .page-header-modern h2 {
                font-size: 1rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .action-bar {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .table-container {
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
            $activePage = 'returns';
            include('owner_sidebar.php');
        } else {
            $activePage = 'returns';
            include('manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-undo-alt"></i>
                Returns Management
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
            <span class="text-dark fw-semibold">Returns</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon customer">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $customerData['count']; ?></h4>
                    <p>Customer Returns</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon supplier">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $supplierCount; ?></h4>
                    <p>Supplier Returns</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon refund">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo formatUGX($customerData['total']); ?></h4>
                    <p>Total Refunds</p>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-bar" data-aos="fade-up" data-aos-delay="150">
            <a rel="facebox" href="return_customer_add.php" class="btn-customer-return">
                <i class="fas fa-user-plus"></i> Customer Return
            </a>
            <a rel="facebox" href="return_supplier_add.php" class="btn-supplier-return">
                <i class="fas fa-truck-plus"></i> Supplier Return
            </a>
        </div>
        
        <!-- Alert Messages -->
        <?php if(isset($_GET['msg']) && $_GET['msg']!=''): ?>
        <div class="alert-modern alert-success" data-aos="fade">
            <i class="fas fa-check-circle"></i>
            <?php echo h($_GET['msg']); ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['err']) && $_GET['err']!=''): ?>
        <div class="alert-modern alert-danger" data-aos="fade">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo h($_GET['err']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Customer Returns Section -->
        <div class="section-header" data-aos="fade-right">
            <i class="fas fa-user"></i>
            <h3>Customer Returns</h3>
        </div>
        
        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="returns-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar me-1"></i> Date</th>
                        <th><i class="fas fa-file-invoice me-1"></i> Invoice</th>
                        <th><i class="fas fa-box me-1"></i> Item</th>
                        <th><i class="fas fa-calculator me-1"></i> Qty</th>
                        <th><i class="fas fa-money-bill-wave me-1"></i> Refund (UGX)</th>
                        <th><i class="fas fa-comment me-1"></i> Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = $db->prepare("SELECT r.return_date, r.invoice_number, r.quantity, r.refund_amount, r.reason, p.product_name, v.variant_name
                            FROM returns r
                            LEFT JOIN products p ON p.product_id = r.product_id
                            LEFT JOIN product_variants v ON v.variant_id = r.variant_id
                            ORDER BY r.return_id DESC LIMIT 200");
                    $q->execute();
                    $hasCustomerReturns = false;
                    while($row = $q->fetch(PDO::FETCH_ASSOC)):
                        $hasCustomerReturns = true;
                    ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['return_date'])); ?></td>
                        <td><strong><?php echo h($row['invoice_number']); ?></strong></td>
                        <td><?php echo h($row['product_name']); ?><?php echo $row['variant_name'] ? ' - ' . h($row['variant_name']) : ''; ?></td>
                        <td><?php echo h($row['quantity']); ?></td>
                        <td class="fw-bold" style="color:#4f46e5;"><?php echo formatUGX($row['refund_amount']); ?></td>
                        <td><?php echo h($row['reason']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasCustomerReturns): ?>
                    <tr class="no-data">
                        <td colspan="6">
                            <i class="fas fa-user"></i>
                            <p>No customer returns recorded yet.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Supplier Returns Section -->
        <div class="section-header" data-aos="fade-right">
            <i class="fas fa-truck"></i>
            <h3>Supplier Returns</h3>
        </div>
        
        <div class="table-container" data-aos="fade-up" data-aos-delay="250">
            <table class="returns-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar me-1"></i> Date</th>
                        <th><i class="fas fa-file-invoice me-1"></i> PO Number</th>
                        <th><i class="fas fa-building me-1"></i> Supplier</th>
                        <th><i class="fas fa-box me-1"></i> Item</th>
                        <th><i class="fas fa-calculator me-1"></i> Qty</th>
                        <th><i class="fas fa-comment me-1"></i> Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = $db->prepare("SELECT r.return_date, r.po_id, s.suplier_name, r.quantity, r.reason, p.product_name, v.variant_name
                            FROM supplier_returns r
                            LEFT JOIN supliers s ON s.suplier_id = r.supplier_id
                            LEFT JOIN products p ON p.product_id = r.product_id
                            LEFT JOIN product_variants v ON v.variant_id = r.variant_id
                            ORDER BY r.return_id DESC LIMIT 200");
                    $q->execute();
                    $hasSupplierReturns = false;
                    while($row = $q->fetch(PDO::FETCH_ASSOC)):
                        $hasSupplierReturns = true;
                    ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['return_date'])); ?></td>
                        <td><strong><?php echo h($row['po_id']); ?></strong></td>
                        <td><?php echo h($row['suplier_name']); ?></td>
                        <td><?php echo h($row['product_name']); ?><?php echo $row['variant_name'] ? ' - ' . h($row['variant_name']) : ''; ?></td>
                        <td><?php echo h($row['quantity']); ?></td>
                        <td><?php echo h($row['reason']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasSupplierReturns): ?>
                    <tr class="no-data">
                        <td colspan="6">
                            <i class="fas fa-truck"></i>
                            <p>No supplier returns recorded yet.</p>
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
<script src="src/facebox.js" type="text/javascript"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Facebox initialization
    jQuery(document).ready(function($) {
        $('a[rel*=facebox]').facebox({
            loadingImage: 'src/loading.gif',
            closeImage: 'src/closelabel.png'
        });
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
</script>

</body>
</html>