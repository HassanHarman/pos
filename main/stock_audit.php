<?php
// ============================================================
// STOCK AUDIT PAGE - MODERN UI/UX
// Fully responsive with stock movement tracking
// Owner only access
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');

$type = isset($_GET['type']) ? $_GET['type'] : '';
$d1 = isset($_GET['d1']) ? $_GET['d1'] : '';
$d2 = isset($_GET['d2']) ? $_GET['d2'] : '';
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';

function h($v){ return htmlspecialchars((string)$v); }

// Get summary statistics
$summaryQuery = $db->prepare("
    SELECT 
        COUNT(*) as total_movements,
        SUM(CASE WHEN movement_type = 'sale' THEN quantity ELSE 0 END) as total_sales,
        SUM(CASE WHEN movement_type = 'purchase' THEN quantity ELSE 0 END) as total_purchases,
        SUM(CASE WHEN movement_type = 'return' THEN quantity ELSE 0 END) as total_returns,
        SUM(CASE WHEN movement_type = 'adjustment' THEN quantity ELSE 0 END) as total_adjustments
    FROM stock_movements
");
$summaryQuery->execute();
$summary = $summaryQuery->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Stock Audit | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Flatpickr Date Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
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
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
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
        .stat-icon.sale {
            background: #dbeafe;
        }
        .stat-icon.sale i {
            color: #2563eb;
        }
        .stat-icon.purchase {
            background: #d1fae5;
        }
        .stat-icon.purchase i {
            color: #059669;
        }
        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
        }
        .stat-label {
            font-size: 0.65rem;
            color: #64748b;
            margin-top: 0.2rem;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
        }
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        .filter-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.3rem;
        }
        .filter-group label i {
            margin-right: 0.3rem;
            color: #4f46e5;
        }
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        .filter-group select:focus, .filter-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-filter {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-filter:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
        }
        .btn-reset {
            background: #f1f5f9;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-reset:hover {
            background: #e2e8f0;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 1000px;
        }
        .audit-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .audit-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .audit-table tbody tr:hover {
            background: #fafbff;
        }

        /* Type Badges */
        .type-sale {
            background: #dbeafe;
            color: #2563eb;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }
        .type-purchase {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }
        .type-return {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }
        .type-adjustment {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }

        /* Stock Change Indicators */
        .stock-increase {
            color: #059669;
            font-weight: 600;
        }
        .stock-decrease {
            color: #dc2626;
            font-weight: 600;
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
            .filter-form {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .btn-filter, .btn-reset {
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
            .page-header-modern h2 {
                font-size: 1rem;
            }
            .stats-grid {
                gap: 0.8rem;
            }
            .stat-value {
                font-size: 1rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .filter-bar {
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
        <?php $activePage = 'stock_audit'; include('owner_sidebar.php'); ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-clipboard-list"></i>
                Stock Audit Trail
            </h2>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Stock Audit</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($summary['total_movements']); ?></div>
                <div class="stat-label">Total Movements</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sale">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format(abs($summary['total_sales'])); ?></div>
                <div class="stat-label">Sales (Out)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purchase">
                    <i class="fas fa-download"></i>
                </div>
                <div class="stat-value"><?php echo number_format($summary['total_purchases']); ?></div>
                <div class="stat-label">Purchases (In)</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($summary['total_returns']); ?></div>
                <div class="stat-label">Returns</div>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar" data-aos="fade-up" data-aos-delay="150">
            <form action="stock_audit.php" method="get" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Movement Type</label>
                    <select name="type">
                        <option value="" <?php echo ($type==''?'selected':''); ?>>All Types</option>
                        <option value="sale" <?php echo ($type=='sale'?'selected':''); ?>>Sale</option>
                        <option value="purchase" <?php echo ($type=='purchase'?'selected':''); ?>>Purchase</option>
                        <option value="return" <?php echo ($type=='return'?'selected':''); ?>>Return</option>
                        <option value="adjustment" <?php echo ($type=='adjustment'?'selected':''); ?>>Adjustment</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> From Date</label>
                    <input type="date" name="d1" value="<?php echo h($d1); ?>" id="dateFrom" placeholder="Start date">
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> To Date</label>
                    <input type="date" name="d2" value="<?php echo h($d2); ?>" id="dateTo" placeholder="End date">
                </div>
                
                <div class="filter-group">
                    <label><i class="fas fa-box"></i> Product</label>
                    <select name="product_id" class="product-select">
                        <option value="">All Products</option>
                        <?php
                        $p = $db->prepare("SELECT product_id, product_name FROM products ORDER BY product_name ASC");
                        $p->execute();
                        while($pr = $p->fetch()):
                            $sel = ((string)$product_id === (string)$pr['product_id']) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $pr['product_id']; ?>" <?php echo $sel; ?>><?php echo h($pr['product_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Apply Filter
                    </button>
                </div>
                
                <div class="filter-group">
                    <a href="stock_audit.php" class="btn-reset">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Stock Movements Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="audit-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar"></i> Date</th>
                        <th><i class="fas fa-tag"></i> Type</th>
                        <th><i class="fas fa-box"></i> Product</th>
                        <th><i class="fas fa-layer-group"></i> Variant</th>
                        <th><i class="fas fa-calculator"></i> Qty</th>
                        <th><i class="fas fa-chart-line"></i> Previous</th>
                        <th><i class="fas fa-chart-line"></i> New</th>
                        <th><i class="fas fa-hashtag"></i> Reference</th>
                        <th><i class="fas fa-comment"></i> Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where = array();
                    $params = array();

                    if ($type !== '') {
                        $where[] = "m.movement_type = :t";
                        $params[':t'] = $type;
                    }
                    if ($product_id !== '') {
                        $where[] = "m.product_id = :pid";
                        $params[':pid'] = $product_id;
                    }
                    if ($d1 !== '') {
                        $where[] = "DATE(m.created_at) >= :d1";
                        $params[':d1'] = $d1;
                    }
                    if ($d2 !== '') {
                        $where[] = "DATE(m.created_at) <= :d2";
                        $params[':d2'] = $d2;
                    }

                    $sql = "SELECT m.*, p.product_name, v.variant_name
                            FROM stock_movements m
                            LEFT JOIN products p ON p.product_id = m.product_id
                            LEFT JOIN product_variants v ON v.variant_id = m.variant_id";
                    if (count($where) > 0) {
                        $sql .= " WHERE " . implode(" AND ", $where);
                    }
                    $sql .= " ORDER BY m.movement_id DESC LIMIT 500";

                    $q = $db->prepare($sql);
                    $q->execute($params);
                    $hasResults = false;
                    while($row = $q->fetch()):
                        $hasResults = true;
                        $typeClass = '';
                        switch($row['movement_type']) {
                            case 'sale': $typeClass = 'type-sale'; break;
                            case 'purchase': $typeClass = 'type-purchase'; break;
                            case 'return': $typeClass = 'type-return'; break;
                            case 'adjustment': $typeClass = 'type-adjustment'; break;
                        }
                        $stockChange = $row['new_stock'] - $row['previous_stock'];
                        $stockClass = $stockChange > 0 ? 'stock-increase' : ($stockChange < 0 ? 'stock-decrease' : '');
                    ?>
                    <tr class="record">
                        <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                        <td><span class="<?php echo $typeClass; ?>"><?php echo ucfirst($row['movement_type']); ?></span></td>
                        <td><strong><?php echo h($row['product_name']); ?></strong></td>
                        <td><?php echo h($row['variant_name']) ?: '—'; ?></td>
                        <td class="<?php echo $stockClass; ?>"><?php echo $row['quantity']; ?> <?php echo $stockChange > 0 ? '(+)' : ($stockChange < 0 ? '(-)' : ''); ?></td>
                        <td><?php echo $row['previous_stock']; ?></td>
                        <td><?php echo $row['new_stock']; ?></td>
                        <td><?php echo h($row['reference_type']); ?><?php echo $row['reference_id'] ? ' #' . h($row['reference_id']) : ''; ?></td>
                        <td><?php echo h($row['notes']) ?: '—'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr class="no-data">
                        <td colspan="9">
                            <i class="fas fa-chart-line"></i>
                            <p>No stock movements found for the selected criteria.</p>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Initialize Select2 for product dropdown
    $(document).ready(function() {
        $('.product-select').select2({
            placeholder: "Search for a product...",
            allowClear: true,
            width: '100%'
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