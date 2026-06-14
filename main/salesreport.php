<?php
// ============================================================
// SALES REPORT PAGE - MODERN UI/UX WITH UGANDAN CURRENCY
// Fully responsive with date range picker
// Preserves ALL original functionality
// Currency: UGX (Ugandan Shilling)
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));

function createRandomPassword() {
    $chars = "003232303232023232023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '';
    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}
$finalcode = 'RS-' . createRandomPassword();

// Set default date range (last 30 days)
$d1 = isset($_GET['d1']) && $_GET['d1'] != '' ? $_GET['d1'] : date('Y-m-d', strtotime('-30 days'));
$d2 = isset($_GET['d2']) && $_GET['d2'] != '' ? $_GET['d2'] : date('Y-m-d');

function formatMoney($number, $fractional=false) {
    if ($fractional) {
        $number = sprintf('%.2f', $number);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
        if ($replaced != $number) {
            $number = $replaced;
        } else {
            break;
        }
    }
    return $number;
}

// Format currency in UGX
function formatUGX($amount) {
    return 'UGX ' . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Sales Report | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Flatpickr Date Picker (modern replacement for tcal) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
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
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1rem 1.2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-card .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 0.3rem;
        }
        .stat-card .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
        }
        .stat-card .stat-sub {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }
        .stat-card .currency-badge {
            font-size: 0.6rem;
            background: #eef2ff;
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            color: #4f46e5;
            margin-left: 0.5rem;
        }

        /* Date Range Filter Bar */
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
            justify-content: center;
        }
        .filter-group {
            flex: 1;
            min-width: 180px;
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
            padding: 0.65rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        .filter-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-search {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-search:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
        }
        .btn-print {
            background: #f1f5f9;
            border: none;
            padding: 0.65rem 1.2rem;
            border-radius: 14px;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-print:hover {
            background: #e2e8f0;
        }

        /* Report Container */
        .report-container {
            background: white;
            border-radius: 24px;
            padding: 1.2rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .report-title {
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .report-title i {
            color: #4f46e5;
            margin-right: 0.5rem;
        }

        /* Sales Table */
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 700px;
        }
        .sales-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .sales-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .sales-table tbody tr:hover {
            background: #fafbff;
        }
        .sales-table tfoot th {
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #e2e8f0;
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

        /* Currency Highlight */
        .currency-amount {
            font-weight: 600;
            color: #4f46e5;
        }
        .currency-profit {
            font-weight: 600;
            color: #059669;
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
            .filter-form {
                flex-direction: column;
            }
            .filter-group {
                width: 100%;
            }
            .btn-search, .btn-print {
                width: 100%;
                justify-content: center;
            }
            .stats-grid {
                gap: 0.8rem;
            }
            .stat-card .stat-value {
                font-size: 1rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .filter-bar, .breadcrumb-modern, .stats-grid, .page-header-modern .supplier-count-badge, .btn-search, .btn-print {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .report-container {
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
            $activePage = 'salesreport';
            include('owner_sidebar.php');
        } else {
            $activePage = 'salesreport';
            include('manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-chart-bar"></i>
                Sales Report
            </h2>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-coins"></i>
                <span style="font-size: 0.8rem;">Currency: Ugandan Shilling (UGX)</span>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Sales Report</span>
        </div>
        
        <?php
        include('../connect.php');
        
        // Calculate totals for stats cards
        $totalQuery = $db->prepare("SELECT COALESCE(SUM(amount),0) as total_amount, COALESCE(SUM(profit),0) as total_profit, COUNT(*) as transaction_count FROM sales WHERE date BETWEEN :a AND :b");
        $totalQuery->execute(array(':a' => $d1, ':b' => $d2));
        $totals = $totalQuery->fetch(PDO::FETCH_ASSOC);
        $totalSales = $totals['total_amount'];
        $totalProfit = $totals['total_profit'];
        $transactionCount = $totals['transaction_count'];
        
        // Calculate average transaction value
        $avgTransaction = $transactionCount > 0 ? $totalSales / $transactionCount : 0;
        ?>
        
        <!-- Stats Cards with UGX Currency -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-chart-line"></i> TOTAL SALES</div>
                <div class="stat-value"><?php echo formatUGX($totalSales); ?></div>
                <div class="stat-sub">For selected period</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-chart-simple"></i> TOTAL PROFIT</div>
                <div class="stat-value" style="color: #059669;"><?php echo formatUGX($totalProfit); ?></div>
                <div class="stat-sub">Net profit margin</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-receipt"></i> TRANSACTIONS</div>
                <div class="stat-value"><?php echo number_format($transactionCount); ?></div>
                <div class="stat-sub">Completed sales</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-chart-line"></i> AVG. TRANSACTION</div>
                <div class="stat-value"><?php echo formatUGX($avgTransaction); ?></div>
                <div class="stat-sub">Per sale average</div>
            </div>
        </div>
        
        <!-- Date Range Filter -->
        <div class="filter-bar" data-aos="fade-up" data-aos-delay="150">
            <form action="salesreport.php" method="get" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> From Date</label>
                    <input type="text" name="d1" id="dateFrom" value="<?php echo htmlspecialchars($d1); ?>" placeholder="Select start date" autocomplete="off">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> To Date</label>
                    <input type="text" name="d2" id="dateTo" value="<?php echo htmlspecialchars($d2); ?>" placeholder="Select end date" autocomplete="off">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
                <div class="filter-group">
                    <button type="button" class="btn-print" onclick="window.print();">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Report Content -->
        <div class="report-container" data-aos="fade-up" data-aos-delay="200">
            <div id="reportContent">
                <div class="report-title">
                    <i class="fas fa-chart-line me-2"></i>
                    Sales Report from <?php echo date('F d, Y', strtotime($d1)); ?> to <?php echo date('F d, Y', strtotime($d2)); ?>
                    <div style="font-size: 0.7rem; font-weight: normal; margin-top: 0.3rem;">
                        <i class="fas fa-coins"></i> All amounts are in Ugandan Shillings (UGX)
                    </div>
                </div>
                
                <table class="sales-table" id="resultTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-1"></i> Transaction ID</th>
                            <th><i class="fas fa-calendar-day me-1"></i> Transaction Date</th>
                            <th><i class="fas fa-user me-1"></i> Customer Name</th>
                            <th><i class="fas fa-file-invoice me-1"></i> Invoice Number</th>
                            <th><i class="fas fa-money-bill-wave me-1"></i> Amount (UGX)</th>
                            <th><i class="fas fa-chart-line me-1"></i> Profit (UGX)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $db->prepare("SELECT * FROM sales WHERE date BETWEEN :a AND :b ORDER BY transaction_id DESC");
                        $result->execute(array(':a' => $d1, ':b' => $d2));
                        $hasResults = false;
                        while($row = $result->fetch()):
                            $hasResults = true;
                        ?>
                        <tr class="record">
                            <td>STI-00<?php echo $row['transaction_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                            <td class="currency-amount"><?php echo formatUGX($row['amount']); ?></td>
                            <td class="currency-profit"><?php echo formatUGX($row['profit']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(!$hasResults): ?>
                        <tr>
                            <td colspan="6" class="no-data">
                                <i class="fas fa-chart-line"></i>
                                <p>No sales data found for the selected period.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if($hasResults): ?>
                    <tfoot>
                        <tr>
                            <th colspan="4" style="text-align: right;">Total (UGX):</th>
                            <th class="currency-amount"><?php echo formatUGX($totalSales); ?></th>
                            <th class="currency-profit"><?php echo formatUGX($totalProfit); ?></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
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
    
    // Initialize Flatpickr date pickers
    flatpickr("#dateFrom", {
        dateFormat: "Y-m-d",
        maxDate: new Date(),
        allowInput: true
    });
    
    flatpickr("#dateTo", {
        dateFormat: "Y-m-d",
        maxDate: new Date(),
        allowInput: true
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