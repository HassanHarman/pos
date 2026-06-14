<?php
// ============================================================
// SALES HISTORY - MODERN UI/UX
// PRESERVES ALL ORIGINAL PHP FUNCTIONALITY
// CURRENCY: UGX (Ugandan Shillings)
// ============================================================

require_once('../main/auth.php');
require_role(array('cashier'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

function formatMoney($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

// Get search parameter if any
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFilter = isset($_GET['date']) ? trim($_GET['date']) : '';

// Build query with optional filters
$sql = "SELECT transaction_id, invoice_number, sale_type, total_amount, created_at, customer_name FROM sales WHERE user_id = :uid";
$params = array(':uid' => $userId);

if ($search !== '') {
    $sql .= " AND (invoice_number LIKE :search OR customer_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($dateFilter !== '') {
    $sql .= " AND DATE(created_at) = :date";
    $params[':date'] = $dateFilter;
}

$sql .= " ORDER BY transaction_id DESC LIMIT 200";
$q = $db->prepare($sql);
$q->execute($params);

// Get today's stats
$sum = $db->prepare("SELECT COALESCE(SUM(total_amount),0) AS t, COUNT(*) AS c FROM sales WHERE user_id = :uid AND DATE(created_at) = :d");
$sum->execute(array(':uid' => $userId, ':d' => date('Y-m-d')));
$sr = $sum->fetch(PDO::FETCH_ASSOC);
$todayTotal = $sr && isset($sr['t']) ? (float)$sr['t'] : 0;
$todayCount = $sr && isset($sr['c']) ? (int)$sr['c'] : 0;

// Get monthly stats
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$monthSum = $db->prepare("SELECT COALESCE(SUM(total_amount),0) AS t, COUNT(*) AS c FROM sales WHERE user_id = :uid AND DATE(created_at) BETWEEN :start AND :end");
$monthSum->execute(array(':uid' => $userId, ':start' => $monthStart, ':end' => $monthEnd));
$mr = $monthSum->fetch(PDO::FETCH_ASSOC);
$monthTotal = $mr && isset($mr['t']) ? (float)$mr['t'] : 0;
$monthCount = $mr && isset($mr['c']) ? (int)$mr['c'] : 0;

// Get all time total
$allTimeSum = $db->prepare("SELECT COALESCE(SUM(total_amount),0) AS t, COUNT(*) AS c FROM sales WHERE user_id = :uid");
$allTimeSum->execute(array(':uid' => $userId));
$atr = $allTimeSum->fetch(PDO::FETCH_ASSOC);
$allTimeTotal = $atr && isset($atr['t']) ? (float)$atr['t'] : 0;
$allTimeCount = $atr && isset($atr['c']) ? (int)$atr['c'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Sales History | POS System - UGX</title>
    
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
            margin: 0 !important;
            padding: 0 !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            overflow-y: auto;
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

        /* Main content area */
        .main-content-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            padding: 0 !important;
            margin-top: 0 !important;
        }

        /* Dashboard Content */
        .sales-content {
            padding: 1.5rem 2rem;
            max-width: 100%;
            margin: 0 !important;
            padding-top: 1.5rem;
        }

        /* Header Section */
        .content-header-modern {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 24px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            margin-top: 0 !important;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .content-header-modern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(165,180,252,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .content-header-modern h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .content-header-modern h2 i {
            color: #a5b4fc;
            font-size: 1.8rem;
        }
        .content-header-modern .date-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.1);
            padding: 0.3rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
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
            padding: 1.2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .stat-card .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .stat-card .stat-icon i {
            font-size: 1.3rem;
            color: #4f46e5;
        }
        .stat-card .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .stat-card .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
        }
        .stat-card .stat-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        /* Search and Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
        }
        .search-box {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 40px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .search-box input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .date-filter {
            min-width: 180px;
        }
        .date-filter input {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 40px;
            font-size: 0.85rem;
        }
        .reset-btn {
            background: #f1f5f9;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 40px;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .reset-btn:hover {
            background: #e2e8f0;
        }

        /* Modern Table */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .sales-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }
        .sales-table thead th {
            text-align: left;
            padding: 1rem;
            background: #f8fafc;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .sales-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }
        .sales-table tbody tr:hover {
            background: #f8fafc;
        }
        .invoice-link {
            font-weight: 600;
            color: #4f46e5;
            text-decoration: none;
            font-family: monospace;
        }
        .invoice-link:hover {
            text-decoration: underline;
        }
        .badge-sale-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-counter {
            background: #d1fae5;
            color: #059669;
        }
        .badge-delivery {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-view {
            background: #eef2ff;
            border: none;
            padding: 0.35rem 1rem;
            border-radius: 30px;
            color: #4f46e5;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }
        .btn-view:hover {
            background: #4f46e5;
            color: white;
        }
        .btn-back {
            background: #f1f5f9;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #475569;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Currency badge */
        .currency-badge {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
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
            .sales-content {
                padding: 1rem;
                padding-top: 70px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .filter-bar {
                flex-direction: column;
            }
            .search-box, .date-filter {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Include Sidebar -->
<?php 
$activePage = 'sales_history'; 
include('owner_sidebar.php'); 

?>

<!-- Main Content -->
<div class="main-content-wrapper">
    <div class="sales-content">
        
        <!-- Header -->
        <div class="content-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-list"></i>
                Sales History
            </h2>
            <div class="date-badge">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('l, F d, Y'); ?>
                <span class="currency-badge ms-2"><i class="fas fa-money-bill-wave"></i> UGX</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="50">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value"><?php echo formatMoney($todayTotal); ?></div>
                <div class="stat-sub"><?php echo $todayCount; ?> transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-label">This Month</div>
                <div class="stat-value"><?php echo formatMoney($monthTotal); ?></div>
                <div class="stat-sub"><?php echo $monthCount; ?> transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-label">All Time Total</div>
                <div class="stat-value"><?php echo formatMoney($allTimeTotal); ?></div>
                <div class="stat-sub"><?php echo $allTimeCount; ?> total sales</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-label">Records Shown</div>
                <div class="stat-value"><?php echo number_format($q->rowCount()); ?></div>
                <div class="stat-sub">Last 200 transactions</div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="filter-bar" data-aos="fade-up" data-aos-delay="100">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by invoice or customer..." value="<?php echo h($search); ?>">
            </div>
            <div class="date-filter">
                <input type="date" id="dateFilter" value="<?php echo h($dateFilter); ?>">
            </div>
            <button class="reset-btn" id="resetBtn">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
        </div>

        <!-- Sales Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="150">
            <table class="sales-table" id="salesTable">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Total (UGX)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $hasResults = false;
                    while($row = $q->fetch(PDO::FETCH_ASSOC)) { 
                        $hasResults = true;
                        $badgeClass = $row['sale_type'] == 'counter' ? 'badge-counter' : 'badge-delivery';
                        $badgeIcon = $row['sale_type'] == 'counter' ? 'fa-store' : 'fa-truck';
                    ?>
                    <tr>
                        <td><a href="../main/preview.php?invoice=<?php echo urlencode($row['invoice_number']); ?>" class="invoice-link"><i class="fas fa-receipt"></i> <?php echo h($row['invoice_number']); ?></a></td>
                        <td><i class="far fa-calendar-alt me-1" style="color:#94a3b8;"></i> <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                        <td><?php echo !empty($row['customer_name']) ? h($row['customer_name']) : '<span style="color:#cbd5e1;">—</span>'; ?></td>
                        <td><span class="badge-sale-type <?php echo $badgeClass; ?>"><i class="fas <?php echo $badgeIcon; ?> me-1"></i> <?php echo ucfirst(h($row['sale_type'])); ?></span></td>
                        <td style="font-weight: 700; color: #059669;"><?php echo formatMoney($row['total_amount']); ?></td>
                        <td>
                            <a href="../main/preview.php?invoice=<?php echo urlencode($row['invoice_number']); ?>" class="btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (!$hasResults): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>No sales records found</p>
                                <small>Start a new sale to see transactions here</small>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Back Button -->
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });

    // Search and Filter functionality
    const searchInput = document.getElementById('searchInput');
    const dateFilter = document.getElementById('dateFilter');
    const resetBtn = document.getElementById('resetBtn');

    function applyFilters() {
        let url = window.location.pathname;
        let params = [];
        
        if (searchInput.value.trim() !== '') {
            params.push('search=' + encodeURIComponent(searchInput.value.trim()));
        }
        if (dateFilter.value !== '') {
            params.push('date=' + encodeURIComponent(dateFilter.value));
        }
        
        if (params.length > 0) {
            window.location.href = url + '?' + params.join('&');
        } else {
            window.location.href = url;
        }
    }

    // Debounce search input
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 500);
    });

    dateFilter.addEventListener('change', applyFilters);
    
    resetBtn.addEventListener('click', function() {
        window.location.href = window.location.pathname;
    });

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
    
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-modern a');
    sidebarLinks.forEach(link => {
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

    // Force page to top
    window.scrollTo(0, 0);
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
</script>

</body>
</html>