<?php
// ============================================================
// CASH FLOAT MANAGEMENT PAGE - MODERN UI/UX
// Fully responsive with staff selection and float tracking
// Owner only access
// Currency: UGX (Ugandan Shilling)
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

$today = date('Y-m-d');

$activeUserId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
if ($activeUserId === '') {
    $activeUserId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : '';
}

$users = $db->prepare("SELECT id, name, position, is_active FROM user ORDER BY name ASC");
$users->execute();

$float = null;
if ($activeUserId !== '') {
    $q = $db->prepare("SELECT * FROM cash_float WHERE user_id = :uid AND date = :d LIMIT 1");
    $q->execute(array(':uid' => $activeUserId, ':d' => $today));
    $float = $q->fetch(PDO::FETCH_ASSOC);
}

$totalSales = 0;
if ($activeUserId !== '') {
    $s = $db->prepare("SELECT COALESCE(SUM(total_amount),0) AS t FROM sales WHERE user_id = :uid AND DATE(created_at) = :d");
    $s->execute(array(':uid' => $activeUserId, ':d' => $today));
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if ($r && isset($r['t'])) {
        $totalSales = (float)$r['t'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Cash Float | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
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
        .date-badge {
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

        /* Staff Selection Card */
        .staff-selector {
            background: white;
            border-radius: 20px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .staff-selector label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .staff-selector label i {
            color: #4f46e5;
        }
        .staff-selector select {
            flex: 1;
            min-width: 250px;
            padding: 0.65rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            background: white;
        }
        .staff-selector select:focus {
            outline: none;
            border-color: #4f46e5;
        }

        /* Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .info-icon {
            width: 45px;
            height: 45px;
            background: #eef2ff;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        .info-icon i {
            font-size: 1.3rem;
            color: #4f46e5;
        }
        .info-label {
            font-size: 0.65rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1e293b;
            margin-top: 0.2rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .status-open {
            background: #d1fae5;
            color: #059669;
        }
        .status-closed {
            background: #fee2e2;
            color: #dc2626;
        }
        .status-not-opened {
            background: #fef3c7;
            color: #d97706;
        }

        /* Details Table */
        .details-table {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .details-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th {
            text-align: left;
            padding: 0.8rem 1rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            font-size: 0.75rem;
            width: 200px;
        }
        .details-table td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .form-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-title i {
            color: #4f46e5;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .form-group label i {
            margin-right: 0.5rem;
            color: #4f46e5;
        }
        .form-group input {
            width: 100%;
            max-width: 300px;
            padding: 0.7rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.7rem 1.5rem;
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
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
            padding: 0.7rem 1.5rem;
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
        .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
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
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
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
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
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
            .info-grid {
                gap: 0.8rem;
            }
            .info-value {
                font-size: 1rem;
            }
            .staff-selector {
                flex-direction: column;
                align-items: stretch;
            }
            .staff-selector select {
                width: 100%;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .breadcrumb-modern, .staff-selector, .btn-primary, .btn-danger {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
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
        <?php $activePage = 'cash_float'; include('owner_sidebar.php'); ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-money-bill-wave"></i>
                Cash Float Management
            </h2>
            <div class="date-badge">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('l, F d, Y'); ?>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Cash Float</span>
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
        
        <!-- Staff Selector -->
        <div class="staff-selector" data-aos="fade-up" data-aos-delay="100">
            <label><i class="fas fa-user-circle"></i> Select Staff Member:</label>
            <form method="get" action="cash_float.php" style="flex:1;">
                <select name="user_id" onchange="this.form.submit()">
                    <option value="">-- Select Staff --</option>
                    <?php 
                    $users->execute();
                    while($u = $users->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                    <option value="<?php echo $u['id']; ?>" <?php echo ((string)$activeUserId===(string)$u['id'])?'selected':''; ?>>
                        <?php echo h($u['name']); ?> (<?php echo ucfirst(h($u['position'])); ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
        
        <?php if($activeUserId == ''): ?>
        <div class="alert-modern alert-info" data-aos="fade-up">
            <i class="fas fa-info-circle"></i>
            Please select a staff member to manage their daily cash float.
        </div>
        <?php else: ?>
        
        <!-- Info Cards -->
        <div class="info-grid" data-aos="fade-up" data-aos-delay="150">
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="info-label">Today's Date</div>
                <div class="info-value"><?php echo date('M d, Y'); ?></div>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-chart-line"></i></div>
                <div class="info-label">Total Sales Today</div>
                <div class="info-value"><?php echo formatUGX($totalSales); ?></div>
            </div>
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-flag-checkered"></i></div>
                <div class="info-label">Float Status</div>
                <div class="info-value">
                    <span class="status-badge <?php echo !$float ? 'status-not-opened' : ($float['closing_balance']===null ? 'status-open' : 'status-closed'); ?>">
                        <?php echo !$float ? 'NOT OPENED' : ($float['closing_balance']===null ? 'OPEN' : 'CLOSED'); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Float Details Table -->
        <?php if($float): ?>
        <div class="details-table" data-aos="fade-up" data-aos-delay="200">
            <table>
                <tr><th>Opening Balance</th><td><?php echo formatUGX($float['opening_balance']); ?></td></tr>
                <tr><th>Expected Cash (Opening + Sales)</th><td><?php echo formatUGX((float)$float['opening_balance'] + $totalSales); ?></td></tr>
                <?php if($float['closing_balance'] !== null): ?>
                <tr><th>Closing Balance</th><td><?php echo formatUGX($float['closing_balance']); ?></td></tr>
                <tr><th>Actual Cash Counted</th><td><?php echo $float['actual_cash'] !== null ? formatUGX($float['actual_cash']) : '-'; ?></td></tr>
                <tr><th>Difference</th><td class="<?php echo (float)$float['difference'] >= 0 ? 'stock-increase' : 'stock-decrease'; ?>"><?php echo $float['difference'] !== null ? formatUGX($float['difference']) : '-'; ?></td></tr>
                <?php if($float['notes']): ?>
                <tr><th>Notes</th><td><?php echo h($float['notes']); ?></td></tr>
                <?php endif; ?>
                <?php else: ?>
                <tr><th>Current Status</th><td><span class="status-badge status-open">Day is OPEN - Ready to Close</span></td></tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Action Forms -->
        <div class="form-container" data-aos="fade-up" data-aos-delay="250">
            <?php if(!$float): ?>
            <!-- Open Day Form -->
            <div class="form-title">
                <i class="fas fa-play-circle"></i>
                Open Cash Float
            </div>
            <form action="cash_float_open.php" method="post">
                <input type="hidden" name="user_id" value="<?php echo h($activeUserId); ?>" />
                <input type="hidden" name="date" value="<?php echo h($today); ?>" />
                <div class="form-group">
                    <label><i class="fas fa-money-bill"></i> Opening Balance</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" placeholder="Enter opening cash amount" required />
                </div>
                <button class="btn-primary" type="submit">
                    <i class="fas fa-play"></i> Open Day
                </button>
            </form>
            
            <?php elseif($float['closing_balance'] === null): ?>
            <!-- Close Day Form -->
            <div class="form-title">
                <i class="fas fa-stop-circle"></i>
                Close Cash Float
            </div>
            <form action="cash_float_close.php" method="post">
                <input type="hidden" name="float_id" value="<?php echo h($float['float_id']); ?>" />
                <input type="hidden" name="user_id" value="<?php echo h($activeUserId); ?>" />
                <input type="hidden" name="date" value="<?php echo h($today); ?>" />
                <input type="hidden" name="total_sales" value="<?php echo h($totalSales); ?>" />
                <input type="hidden" name="expected_cash" value="<?php echo h((float)$float['opening_balance'] + $totalSales); ?>" />
                
                <div class="form-group">
                    <label><i class="fas fa-calculator"></i> Actual Cash Counted</label>
                    <input type="number" step="0.01" min="0" name="actual_cash" placeholder="Enter actual cash counted" required />
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Notes (Optional)</label>
                    <input type="text" name="notes" placeholder="Add any notes about discrepancies" />
                </div>
                <button class="btn-danger" type="submit">
                    <i class="fas fa-lock"></i> Close Day & Lock Float
                </button>
            </form>
            
            <?php else: ?>
            <div class="alert-modern alert-success">
                <i class="fas fa-check-circle"></i>
                Day is already closed for this staff member. A new day can be opened tomorrow.
            </div>
            <?php endif; ?>
        </div>
        
        <?php endif; ?>
        
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
    
    // Initialize Select2 for better dropdown
    $(document).ready(function() {
        $('select[name="user_id"]').select2({
            placeholder: "-- Select Staff --",
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