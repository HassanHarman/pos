<?php
// ============================================================
// CASH FLOAT - MODERN UI/UX
// PRESERVES ALL ORIGINAL PHP FUNCTIONALITY
// CURRENCY: UGX (Ugandan Shillings)
// ============================================================

require_once('../main/auth.php');
require_role(array('cashier'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

function formatMoney($amount) {
    if ($amount === null || $amount === '') return '-';
    return 'UGX ' . number_format((float)$amount, 2);
}

function formatMoneyRaw($amount) {
    return number_format((float)$amount, 2);
}

$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;
$today = date('Y-m-d');

$err = isset($_GET['err']) ? $_GET['err'] : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$float = null;
$q = $db->prepare("SELECT * FROM cash_float WHERE user_id = :uid AND date = :d LIMIT 1");
$q->execute(array(':uid' => $userId, ':d' => $today));
$float = $q->fetch(PDO::FETCH_ASSOC);

$totalSales = 0;
$s = $db->prepare("SELECT COALESCE(SUM(total_amount),0) AS t FROM sales WHERE user_id = :uid AND DATE(created_at) = :d");
$s->execute(array(':uid' => $userId, ':d' => $today));
$r = $s->fetch(PDO::FETCH_ASSOC);
if ($r && isset($r['t'])) {
    $totalSales = (float)$r['t'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Cash Float | POS System - UGX</title>
    
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

        /* Cash Float Content */
        .cashfloat-content {
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

        /* Status Cards */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.2s;
        }
        .status-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .status-card .status-icon {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .status-card .status-icon i {
            font-size: 1.3rem;
        }
        .status-card .status-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .status-card .status-value {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1e293b;
        }
        .status-card .status-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
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
        .status-notstarted {
            background: #fef3c7;
            color: #d97706;
        }

        /* Form Section */
        .form-container {
            background: white;
            border-radius: 24px;
            padding: 1.8rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-top: 1rem;
        }
        .form-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            color: #334155;
            margin-bottom: 0.4rem;
        }
        .form-label i {
            margin-right: 0.4rem;
            color: #4f46e5;
        }
        .form-input {
            width: 100%;
            max-width: 350px;
            padding: 0.7rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .form-textarea {
            width: 100%;
            max-width: 500px;
            padding: 0.7rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            resize: vertical;
        }
        .btn-primary {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(5, 150, 105, 0.3);
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.3);
        }
        .btn-secondary {
            background: #f1f5f9;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 40px;
            color: #64748b;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            color: #475569;
        }
        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        /* Info Cards for Closed State */
        .info-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1rem;
            margin-top: 1rem;
            text-align: center;
        }
        .info-card i {
            font-size: 2rem;
            color: #10b981;
            margin-bottom: 0.5rem;
        }

        /* Alerts */
        .alert-modern {
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #059669;
            border-left: 4px solid #059669;
        }
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
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
            .cashfloat-content {
                padding: 1rem;
                padding-top: 70px;
            }
        }

        @media (max-width: 576px) {
            .form-input, .form-textarea {
                max-width: 100%;
            }
            .button-group {
                flex-direction: column;
            }
            .btn-primary, .btn-danger, .btn-secondary {
                width: 100%;
                justify-content: center;
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
$activePage = 'cash_float'; 
include('cashier_sidebar.php'); 
?>

<!-- Main Content -->
<div class="main-content-wrapper">
    <div class="cashfloat-content">
        
        <!-- Header -->
        <div class="content-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-money-bill-wave"></i>
                Cash Float Management
            </h2>
            <div class="date-badge">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('l, F d, Y'); ?>
                <span class="currency-badge ms-2"><i class="fas fa-coins"></i> UGX</span>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if($msg != ''): ?>
        <div class="alert-modern alert-success" data-aos="fade-up">
            <i class="fas fa-check-circle"></i>
            <?php echo h($msg); ?>
        </div>
        <?php endif; ?>
        
        <?php if($err != ''): ?>
        <div class="alert-modern alert-danger" data-aos="fade-up">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo h($err); ?>
        </div>
        <?php endif; ?>

        <!-- Status Cards -->
        <div class="status-grid" data-aos="fade-up" data-aos-delay="50">
            <div class="status-card">
                <div class="status-icon" style="background: #eef2ff;">
                    <i class="fas fa-calendar-day" style="color: #4f46e5;"></i>
                </div>
                <div class="status-label">Date</div>
                <div class="status-value"><?php echo date('d M Y', strtotime($today)); ?></div>
                <div class="status-sub">Today's date</div>
            </div>
            <div class="status-card">
                <div class="status-icon" style="background: #d1fae5;">
                    <i class="fas fa-user" style="color: #059669;"></i>
                </div>
                <div class="status-label">Cashier</div>
                <div class="status-value"><?php echo h(isset($_SESSION['SESS_FIRST_NAME']) ? $_SESSION['SESS_FIRST_NAME'] : 'Cashier'); ?></div>
                <div class="status-sub"><?php echo h(isset($_SESSION['SESS_LAST_NAME']) ? $_SESSION['SESS_LAST_NAME'] : ''); ?></div>
            </div>
            <div class="status-card">
                <div class="status-icon" style="background: #fef3c7;">
                    <i class="fas fa-chart-line" style="color: #d97706;"></i>
                </div>
                <div class="status-label">Today's Sales</div>
                <div class="status-value"><?php echo formatMoney($totalSales); ?></div>
                <div class="status-sub">Total revenue today</div>
            </div>
            <div class="status-card">
                <div class="status-icon" style="background: <?php echo $float ? ($float['closing_balance']===null ? '#d1fae5' : '#fee2e2') : '#fef3c7'; ?>;">
                    <i class="fas <?php echo $float ? ($float['closing_balance']===null ? 'fa-unlock-alt' : 'fa-lock') : 'fa-clock'; ?>" style="color: <?php echo $float ? ($float['closing_balance']===null ? '#059669' : '#dc2626') : '#d97706'; ?>;"></i>
                </div>
                <div class="status-label">Status</div>
                <div class="status-value">
                    <span class="status-badge <?php echo $float ? ($float['closing_balance']===null ? 'status-open' : 'status-closed') : 'status-notstarted'; ?>">
                        <i class="fas <?php echo $float ? ($float['closing_balance']===null ? 'fa-unlock-alt' : 'fa-lock') : 'fa-hourglass-half'; ?>"></i>
                        <?php echo $float ? ($float['closing_balance']===null ? 'OPEN' : 'CLOSED') : 'NOT STARTED'; ?>
                    </span>
                </div>
                <div class="status-sub">Current cash float status</div>
            </div>
        </div>

        <!-- Main Form Section -->
        <div class="form-container" data-aos="fade-up" data-aos-delay="100">
            
            <?php if(!$float): ?>
                <!-- START DAY FORM -->
                <div class="form-title">
                    <i class="fas fa-play-circle"></i>
                    Start New Day
                </div>
                <form action="cash_float_open.php" method="post">
                    <input type="hidden" name="date" value="<?php echo h($today); ?>" />
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-money-bill"></i> Opening Balance (UGX)
                        </label>
                        <input type="number" step="0.01" min="0" name="opening_balance" class="form-input" placeholder="0.00" required />
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i> Notes (Optional)
                        </label>
                        <input type="text" name="notes" class="form-input" placeholder="Any notes about opening balance..." />
                    </div>
                    
                    <div class="button-group">
                        <button class="btn-primary" type="submit">
                            <i class="fas fa-play"></i> Start Day
                        </button>
                        <a href="index.php" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
                
            <?php elseif($float['closing_balance'] === null): ?>
                <!-- END DAY FORM -->
                <?php
                    $opening = (float)$float['opening_balance'];
                    $expected = $opening + $totalSales;
                ?>
                
                <div class="form-title">
                    <i class="fas fa-stop-circle"></i>
                    End Day - Cash Reconciliation
                </div>
                
                <!-- Summary Cards -->
                <div class="status-grid" style="margin-bottom: 1.5rem;">
                    <div class="status-card">
                        <div class="status-label">Opening Balance</div>
                        <div class="status-value"><?php echo formatMoney($opening); ?></div>
                    </div>
                    <div class="status-card">
                        <div class="status-label">Today's Sales</div>
                        <div class="status-value"><?php echo formatMoney($totalSales); ?></div>
                    </div>
                    <div class="status-card">
                        <div class="status-label">Expected Cash</div>
                        <div class="status-value"><?php echo formatMoney($expected); ?></div>
                    </div>
                </div>
                
                <form action="cash_float_close.php" method="post">
                    <input type="hidden" name="float_id" value="<?php echo h($float['float_id']); ?>" />
                    <input type="hidden" name="date" value="<?php echo h($today); ?>" />
                    <input type="hidden" name="total_sales" value="<?php echo h($totalSales); ?>" />
                    <input type="hidden" name="expected_cash" value="<?php echo h($expected); ?>" />
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calculator"></i> Actual Cash Counted (UGX)
                        </label>
                        <input type="number" step="0.01" min="0" name="actual_cash" class="form-input" id="actualCash" placeholder="0.00" required />
                        <small style="font-size: 0.7rem; color: #64748b;">Count all cash in drawer and enter the total</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i> Notes (Optional)
                        </label>
                        <input type="text" name="notes" class="form-input" placeholder="Any notes about closing balance..." />
                    </div>
                    
                    <div id="differenceDisplay" class="info-card" style="background: #eef2ff; margin-bottom: 1rem;">
                        <i class="fas fa-exchange-alt"></i>
                        <div style="font-size: 0.85rem; color: #475569;">Difference: <strong id="differenceAmount">UGX 0.00</strong></div>
                        <div style="font-size: 0.7rem; color: #64748b;">Expected - Actual (positive means overage, negative means shortage)</div>
                    </div>
                    
                    <div class="button-group">
                        <button class="btn-danger" type="submit" onclick="return confirm('WARNING: Closing the day will lock this cash float. Are you sure?');">
                            <i class="fas fa-lock"></i> End Day & Close
                        </button>
                        <a href="index.php" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
                
            <?php else: ?>
                <!-- DAY CLOSED - SHOW SUMMARY -->
                <div class="form-title">
                    <i class="fas fa-check-circle"></i>
                    Day Closed - Summary
                </div>
                
                <?php
                    $opening = (float)$float['opening_balance'];
                    $expected = $opening + $totalSales;
                    $actual = (float)$float['actual_cash'];
                    $difference = (float)$float['difference'];
                ?>
                
                <div class="status-grid" style="margin-bottom: 1rem;">
                    <div class="status-card">
                        <div class="status-label">Opening Balance</div>
                        <div class="status-value"><?php echo formatMoney($opening); ?></div>
                    </div>
                    <div class="status-card">
                        <div class="status-label">Today's Sales</div>
                        <div class="status-value"><?php echo formatMoney($totalSales); ?></div>
                    </div>
                    <div class="status-card">
                        <div class="status-label">Expected Cash</div>
                        <div class="status-value"><?php echo formatMoney($expected); ?></div>
                    </div>
                    <div class="status-card">
                        <div class="status-label">Actual Cash</div>
                        <div class="status-value"><?php echo formatMoney($actual); ?></div>
                    </div>
                    <div class="status-card">
                        <div class="status-label">Difference</div>
                        <div class="status-value" style="color: <?php echo $difference >= 0 ? '#059669' : '#dc2626'; ?>;">
                            <?php echo formatMoney($difference); ?>
                        </div>
                    </div>
                </div>
                
                <?php if(!empty($float['notes'])): ?>
                <div class="info-card" style="text-align: left;">
                    <i class="fas fa-sticky-note"></i>
                    <strong>Notes:</strong> <?php echo h($float['notes']); ?>
                </div>
                <?php endif; ?>
                
                <div class="info-card" style="background: #d1fae5; margin-top: 1rem;">
                    <i class="fas fa-check-circle"></i>
                    <p style="margin: 0;">Day successfully closed. No further transactions can be recorded for today.</p>
                </div>
                
                <div class="button-group" style="margin-top: 1.5rem;">
                    <a href="index.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
            <?php endif; ?>
            
        </div>
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

    // Live difference calculation for closing form
    const actualCashInput = document.getElementById('actualCash');
    const differenceAmount = document.getElementById('differenceAmount');
    
    <?php if($float && $float['closing_balance'] === null): ?>
    const expectedCash = <?php echo isset($expected) ? (float)$expected : 0; ?>;
    
    function calculateDifference() {
        let actual = parseFloat(actualCashInput.value) || 0;
        let diff = expectedCash - actual;
        let diffFormatted = Math.abs(diff).toFixed(2);
        let sign = diff >= 0 ? 'UGX ' : '-UGX ';
        
        if (differenceAmount) {
            differenceAmount.innerHTML = sign + diffFormatted;
            differenceAmount.style.color = diff >= 0 ? '#059669' : '#dc2626';
        }
    }
    
    if (actualCashInput) {
        actualCashInput.addEventListener('input', calculateDifference);
        calculateDifference();
    }
    <?php endif; ?>

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