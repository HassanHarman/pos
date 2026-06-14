<?php
// ============================================================
// ADVANCED REPORTS PAGE - MODERN UI/UX
// Fully responsive with profit analysis by product and variant
// Owner only access
// Currency: UGX (Ugandan Shilling)
// Preserves ALL original functionality - NO CHART LIBRARIES
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

$d1 = isset($_GET['d1']) ? $_GET['d1'] : '';
$d2 = isset($_GET['d2']) ? $_GET['d2'] : '';

// default: last 7 days
if ($d1 === '' && $d2 === '') {
    $d2 = date('Y-m-d');
    $d1 = date('Y-m-d', strtotime('-6 days'));
}

$where = array();
$params = array();
if ($d1 !== '') {
    $where[] = "DATE(s.created_at) >= :d1";
    $params[':d1'] = $d1;
}
if ($d2 !== '') {
    $where[] = "DATE(s.created_at) <= :d2";
    $params[':d2'] = $d2;
}

$filterSql = '';
if (count($where) > 0) {
    $filterSql = ' WHERE ' . implode(' AND ', $where);
}

// Profit by product (includes variant lines but grouped by product only)
$sqlProduct = "SELECT so.product_id, p.product_name,
                    COALESCE(SUM(so.qty),0) qty_sold,
                    COALESCE(SUM(so.amount),0) revenue,
                    COALESCE(SUM(so.profit),0) profit
                FROM sales_order so
                INNER JOIN sales s ON s.invoice_number = so.invoice
                LEFT JOIN products p ON p.product_id = so.product_id" .
                $filterSql .
                " GROUP BY so.product_id, p.product_name
                ORDER BY profit DESC";
$stmtProduct = $db->prepare($sqlProduct);
$stmtProduct->execute($params);

// Profit by variant
$sqlVariant = "SELECT so.product_id, p.product_name, so.variant_id, v.variant_name,
                    COALESCE(SUM(so.qty),0) qty_sold,
                    COALESCE(SUM(so.amount),0) revenue,
                    COALESCE(SUM(so.profit),0) profit
                FROM sales_order so
                INNER JOIN sales s ON s.invoice_number = so.invoice
                LEFT JOIN products p ON p.product_id = so.product_id
                LEFT JOIN product_variants v ON v.variant_id = so.variant_id" .
                $filterSql .
                " GROUP BY so.product_id, p.product_name, so.variant_id, v.variant_name
                ORDER BY profit DESC";
$stmtVariant = $db->prepare($sqlVariant);
$stmtVariant->execute($params);

function money($v){ return number_format((float)$v, 2); }

// Calculate totals for summary
$totalRevenue = 0;
$totalProfit = 0;
$totalQty = 0;
$productCount = 0;
$stmtProductTemp = $db->prepare($sqlProduct);
$stmtProductTemp->execute($params);
while ($row = $stmtProductTemp->fetch(PDO::FETCH_ASSOC)) {
    $totalRevenue += (float)$row['revenue'];
    $totalProfit += (float)$row['profit'];
    $totalQty += (float)$row['qty_sold'];
    $productCount++;
}
$profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Advanced Reports | POS System</title>
    
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
            width: 50px;
            height: 50px;
            background: #eef2ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
        }
        .stat-icon i {
            font-size: 1.5rem;
            color: #4f46e5;
        }
        .stat-icon.profit {
            background: #d1fae5;
        }
        .stat-icon.profit i {
            color: #059669;
        }
        .stat-value {
            font-size: 1.2rem;
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
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.8rem;
        }
        .filter-group {
            flex: 1;
            min-width: 160px;
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
        .filter-group input {
            width: 100%;
            padding: 0.55rem 0.8rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        .filter-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-filter {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.55rem 1.2rem;
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

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            overflow-x: auto;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .section-header h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .section-header i {
            color: #4f46e5;
            font-size: 1.1rem;
        }
        .reports-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 500px;
        }
        .reports-table thead th {
            text-align: left;
            padding: 0.7rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .reports-table tbody td {
            padding: 0.6rem 0.7rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .reports-table tbody tr:hover {
            background: #fafbff;
        }
        .reports-table tfoot th {
            padding: 0.7rem;
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #e2e8f0;
        }
        .profit-positive {
            color: #059669;
            font-weight: 600;
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
            .btn-filter {
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
        <?php $activePage = 'advanced_reports'; include('owner_sidebar.php'); ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-chart-pie"></i>
                Advanced Reports
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
            <span class="text-dark fw-semibold">Advanced Reports</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?php echo formatUGX($totalRevenue); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon profit"><i class="fas fa-chart-simple"></i></div>
                <div class="stat-value"><?php echo formatUGX($totalProfit); ?></div>
                <div class="stat-label">Total Profit</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-percent"></i></div>
                <div class="stat-value"><?php echo number_format($profitMargin, 2); ?>%</div>
                <div class="stat-label">Profit Margin</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                <div class="stat-value"><?php echo number_format($totalQty); ?></div>
                <div class="stat-label">Units Sold</div>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar" data-aos="fade-up" data-aos-delay="150">
            <form action="advanced_reports.php" method="get" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> From Date</label>
                    <input type="date" name="d1" value="<?php echo h($d1); ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> To Date</label>
                    <input type="date" name="d2" value="<?php echo h($d2); ?>">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Profit by Product Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <div class="section-header">
                <i class="fas fa-box"></i>
                <h4>Profit by Product</h4>
            </div>
            <table class="reports-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Product</th>
                        <th><i class="fas fa-calculator"></i> Qty Sold</th>
                        <th><i class="fas fa-money-bill-wave"></i> Revenue (UGX)</th>
                        <th><i class="fas fa-chart-line"></i> Profit (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmtProduct->execute($params);
                    $displayRevenue = 0;
                    $displayProfit = 0;
                    while ($row = $stmtProduct->fetch(PDO::FETCH_ASSOC)):
                        $displayRevenue += (float)$row['revenue'];
                        $displayProfit += (float)$row['profit'];
                    ?>
                    <tr>
                        <td><strong><?php echo h($row['product_name']); ?></strong></td>
                        <td><?php echo number_format($row['qty_sold']); ?></td>
                        <td><?php echo formatUGX($row['revenue']); ?></td>
                        <td class="profit-positive"><?php echo formatUGX($row['profit']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" style="text-align: right;">Totals:</th>
                        <th><?php echo formatUGX($displayRevenue); ?></th>
                        <th><?php echo formatUGX($displayProfit); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Profit by Variant Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="250">
            <div class="section-header">
                <i class="fas fa-layer-group"></i>
                <h4>Profit by Variant</h4>
            </div>
            <table class="reports-table" id="resultTable2">
                <thead>
                    <tr>
                        <th><i class="fas fa-box"></i> Product</th>
                        <th><i class="fas fa-tag"></i> Variant</th>
                        <th><i class="fas fa-calculator"></i> Qty Sold</th>
                        <th><i class="fas fa-money-bill-wave"></i> Revenue (UGX)</th>
                        <th><i class="fas fa-chart-line"></i> Profit (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmtVariant->execute($params);
                    while ($row = $stmtVariant->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?php echo h($row['product_name']); ?></td>
                        <td><?php echo $row['variant_name'] ? h($row['variant_name']) : '—'; ?></td>
                        <td><?php echo number_format($row['qty_sold']); ?></td>
                        <td><?php echo formatUGX($row['revenue']); ?></td>
                        <td class="profit-positive"><?php echo formatUGX($row['profit']); ?></td>
                    </tr>
                    <?php endwhile; ?>
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
</script>

</body>
</html>