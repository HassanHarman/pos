<?php
// ============================================================
// PURCHASE ORDERS PAGE - MODERN UI/UX
// Fully responsive with PO management
// Preserves ALL original functionality
// Currency: UGX (Ugandan Shilling)
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));
include('../connect.php');

// Get purchase order statistics
$statsQuery = $db->prepare("
    SELECT 
        COUNT(*) as total_pos,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received_count,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        COALESCE(SUM(total_amount), 0) as total_value
    FROM purchase_orders
");
$statsQuery->execute();
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Purchase Orders | POS System</title>
    
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
        .stat-icon.pending {
            background: #fef3c7;
        }
        .stat-icon.pending i {
            color: #d97706;
        }
        .stat-icon.received {
            background: #d1fae5;
        }
        .stat-icon.received i {
            color: #059669;
        }
        .stat-icon.cancelled {
            background: #fee2e2;
        }
        .stat-icon.cancelled i {
            color: #dc2626;
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
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .btn-add-modern {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
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
        .btn-add-modern:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            color: white;
        }

        /* Purchase Orders Table Container */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .po-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 800px;
        }
        .po-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .po-table tbody td {
            padding: 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .po-table tbody tr:hover {
            background: #fafbff;
        }

        /* Status Badges */
        .status-pending {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .status-received {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Action Buttons */
        .btn-view {
            background: #fef3c7;
            border: none;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            color: #d97706;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-view:hover {
            background: #fde68a;
        }
        .btn-receive {
            background: #d1fae5;
            border: none;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            color: #059669;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-receive:hover {
            background: #a7f3d0;
        }
        .btn-cancel {
            background: #fee2e2;
            border: none;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            color: #dc2626;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-cancel:hover {
            background: #fecaca;
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
                grid-template-columns: repeat(2, 1fr);
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
            .action-bar {
                justify-content: center;
            }
            .btn-add-modern {
                width: 100%;
                justify-content: center;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .action-bar, .btn-view, .btn-receive, .btn-cancel {
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
        if (isset($portal) && $portal === 'stock') {
            if (!isset($activePage)) {
                $activePage = 'purchase_orders';
            }
            include(__DIR__ . '/../stock/stock_sidebar.php');
        } elseif ($role === 'owner') {
            $activePage = 'purchase_orders';
            include(__DIR__ . '/owner_sidebar.php');
        } else {
            $activePage = 'purchase_orders';
            include(__DIR__ . '/manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-file-invoice"></i>
                Purchase Orders
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
            <span class="text-dark fw-semibold">Purchase Orders</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['total_pos']; ?></h4>
                    <p>Total POs</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['pending_count']; ?></h4>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon received">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['received_count']; ?></h4>
                    <p>Received</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cancelled">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['cancelled_count']; ?></h4>
                    <p>Cancelled</p>
                </div>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div class="action-bar" data-aos="fade-up" data-aos-delay="150">
            <a rel="facebox" href="add_purchase_order.php" class="btn-add-modern">
                <i class="fas fa-plus-circle"></i> Create Purchase Order
            </a>
        </div>
        
        <!-- Purchase Orders Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="po-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-1"></i> PO Number</th>
                        <th><i class="fas fa-truck me-1"></i> Supplier</th>
                        <th><i class="fas fa-calendar me-1"></i> Order Date</th>
                        <th><i class="fas fa-info-circle me-1"></i> Status</th>
                        <th><i class="fas fa-money-bill-wave me-1"></i> Total (UGX)</th>
                        <th><i class="fas fa-cog me-1"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT po.po_id, po.po_number, po.order_date, po.status, po.total_amount, s.suplier_name
                            FROM purchase_orders po
                            LEFT JOIN supliers s ON s.suplier_id = po.supplier_id
                            ORDER BY po.po_id DESC";
                    $r = $db->prepare($sql);
                    $r->execute();
                    $hasResults = false;
                    while($row = $r->fetch()):
                        $hasResults = true;
                        $statusClass = '';
                        $statusIcon = '';
                        switch($row['status']) {
                            case 'pending':
                                $statusClass = 'status-pending';
                                $statusIcon = 'fa-clock';
                                break;
                            case 'received':
                                $statusClass = 'status-received';
                                $statusIcon = 'fa-check-circle';
                                break;
                            case 'cancelled':
                                $statusClass = 'status-cancelled';
                                $statusIcon = 'fa-times-circle';
                                break;
                            default:
                                $statusClass = 'status-pending';
                                $statusIcon = 'fa-clock';
                        }
                    ?>
                    <tr class="record">
                        <td><strong><?php echo htmlspecialchars($row['po_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['suplier_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                        <td><span class="<?php echo $statusClass; ?>">
                            <i class="fas <?php echo $statusIcon; ?>"></i> <?php echo ucfirst($row['status']); ?>
                        </span></td>
                        <td class="fw-bold" style="color:#4f46e5;"><?php echo $row['total_amount'] !== null ? formatUGX($row['total_amount']) : 'UGX 0.00'; ?></td>
                        <td>
                            <a href="po_view.php?po_id=<?php echo $row['po_id']; ?>" class="btn-view" title="View PO Details">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php if($row['status'] == 'pending'): ?>
                            <a href="po_receive.php?po_id=<?php echo $row['po_id']; ?>" class="btn-receive" title="Receive Stock" onclick="return confirm('Mark this PO as received? This will update inventory.');">
                                <i class="fas fa-check"></i> Receive
                            </a>
                            <a href="po_cancel.php?po_id=<?php echo $row['po_id']; ?>" class="btn-cancel" title="Cancel PO" onclick="return confirm('Cancel this Purchase Order? This action cannot be undone.');">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr class="no-data">
                        <td colspan="6">
                            <i class="fas fa-file-invoice"></i>
                            <p>No purchase orders found. Click "Create Purchase Order" to get started.</p>
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