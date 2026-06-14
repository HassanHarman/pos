<?php
// ============================================================
// ZAKAT CALCULATOR PAGE - BUSINESS ZAKAT (2.5%)
// Automated annual calculation (1st Ramadan) with manual run
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');
/** @var PDO $db */
require_once(__DIR__ . '/zakat_service.php');

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

$activePage = 'zakat';
$message = '';
$autoMessage = '';

if (zakat_should_run_today($db)) {
    $autoResult = calculateBusinessZakat($db);
    zakat_record_run($db, $autoResult, 'Automatic run on zakat anniversary date.');
    $autoMessage = 'Automatic Zakat calculation executed for today.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'save_settings') {
        $goldPrice = isset($_POST['gold_price_per_gram_24k']) ? trim($_POST['gold_price_per_gram_24k']) : '0';
        $anniversaryDate = isset($_POST['zakat_anniversary_date']) ? trim($_POST['zakat_anniversary_date']) : '';
        $agencyExcluded = isset($_POST['agency_customer_funds_excluded']) ? trim($_POST['agency_customer_funds_excluded']) : '0';

        zakat_upsert_setting($db, 'gold_price_per_gram_24k', $goldPrice);
        zakat_upsert_setting($db, 'zakat_anniversary_date', $anniversaryDate);
        zakat_upsert_setting($db, 'agency_customer_funds_excluded', $agencyExcluded);

        $message = 'Zakat settings updated successfully.';
    }

    if ($action === 'run_calculation') {
        $manualResult = calculateBusinessZakat($db);
        zakat_record_run($db, $manualResult, 'Manual run from Zakat page.');
        $message = 'Zakat calculation executed and saved.';
    }
}

$goldPrice = zakat_get_setting($db, 'gold_price_per_gram_24k', '0');
$anniversaryDate = zakat_get_setting($db, 'zakat_anniversary_date', '');
$agencyExcluded = zakat_get_setting($db, 'agency_customer_funds_excluded', '0');

$calculation = calculateBusinessZakat($db);

$runStmt = $db->prepare("SELECT run_date, gold_price_per_gram, nisab_threshold, total_assets, total_liabilities, net_zakat_pool, zakat_due, calculation_notes
    FROM zakat_runs ORDER BY run_date DESC, run_id DESC LIMIT 5");
$runStmt->execute();
$recentRuns = $runStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Zakat Calculator | POS System</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .app-sidebar {
            width: 280px;
            flex-shrink: 0;
            position: relative;
        }

        .app-content {
            flex: 1;
            min-width: 0;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

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
            color: #fcd34d;
            font-size: 1.5rem;
        }

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

        .alert-info {
            background: #e0f2fe;
            color: #075985;
        }

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
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            margin-bottom: 0.4rem;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-sub {
            margin-top: 0.3rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .panel {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .panel h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .panel h3 i {
            color: #4f46e5;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        .form-hint {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79,70,229,0.3);
        }

        .btn-secondary {
            background: #f1f5f9;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 14px;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .breakdown-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.8rem;
        }

        .breakdown-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #1e293b;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 0.4rem;
        }

        .breakdown-list span {
            color: #64748b;
            font-size: 0.75rem;
        }

        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .runs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 700px;
        }

        .runs-table th {
            text-align: left;
            padding: 0.7rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }

        .runs-table td {
            padding: 0.7rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            font-size: 0.75rem;
            color: #475569;
            display: grid;
            gap: 0.5rem;
        }

        .info-card strong {
            color: #1e293b;
        }

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
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle, .sidebar-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-container">
    <div class="app-sidebar" id="appSidebar">
        <?php include(__DIR__ . '/owner_sidebar.php'); ?>
    </div>

    <div class="app-content">
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-mosque"></i>
                Zakat Calculator
            </h2>
        </div>

        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Zakat</span>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert-modern alert-success" data-aos="fade">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($autoMessage !== ''): ?>
        <div class="alert-modern alert-info" data-aos="fade">
            <i class="fas fa-bell"></i>
            <?php echo htmlspecialchars($autoMessage); ?>
        </div>
        <?php endif; ?>

        <div class="stats-grid" data-aos="fade-up">
            <div class="stat-card">
                <div class="stat-label">Total Assets</div>
                <div class="stat-value"><?php echo formatUGX($calculation['total_assets']); ?></div>
                <div class="stat-sub">Cash + Banks + Inventory + Receivables</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Short-Term Liabilities</div>
                <div class="stat-value"><?php echo formatUGX($calculation['liabilities_total']); ?></div>
                <div class="stat-sub">Payables + Current month expenses</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Net Zakat Pool</div>
                <div class="stat-value"><?php echo formatUGX($calculation['net_zakat_pool']); ?></div>
                <div class="stat-sub">After liabilities</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Zakat Due (2.5%)</div>
                <div class="stat-value"><?php echo formatUGX($calculation['zakat_due']); ?></div>
                <div class="stat-sub">Nisab: <?php echo formatUGX($calculation['nisab_threshold']); ?></div>
            </div>
        </div>

        <div class="content-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="panel">
                <h3><i class="fas fa-sliders"></i> Zakat Settings</h3>
                <form method="post">
                    <input type="hidden" name="action" value="save_settings" />
                    <div class="form-group">
                        <label>Gold Price per Gram (24K)</label>
                        <input type="number" step="0.01" min="0" name="gold_price_per_gram_24k" value="<?php echo htmlspecialchars($goldPrice); ?>" required />
                        <div class="form-hint">Used to calculate Nisab threshold (85g).</div>
                    </div>
                    <div class="form-group">
                        <label>Zakat Anniversary (1st Ramadan)</label>
                        <input type="date" name="zakat_anniversary_date" value="<?php echo htmlspecialchars($anniversaryDate); ?>" />
                        <div class="form-hint">Set the Gregorian date corresponding to 1st Ramadan each year.</div>
                    </div>
                    <div class="form-group">
                        <label>Agency Customer Funds (Exclude)</label>
                        <input type="number" step="0.01" min="0" name="agency_customer_funds_excluded" value="<?php echo htmlspecialchars($agencyExcluded); ?>" />
                        <div class="form-hint">Exclude customer funds held temporarily in drawer/wallet.</div>
                    </div>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                </form>
            </div>

            <div class="panel">
                <h3><i class="fas fa-coins"></i> Asset Breakdown</h3>
                <ul class="breakdown-list">
                    <li>
                        Cash Float + Cash Accounts
                        <span><?php echo formatUGX($calculation['cash_total']); ?></span>
                    </li>
                    <li>
                        Bank Balances
                        <span><?php echo formatUGX($calculation['bank_total']); ?></span>
                    </li>
                    <li>
                        Wallet Balances
                        <span><?php echo formatUGX($calculation['wallet_total']); ?></span>
                    </li>
                    <li>
                        Sellable Inventory (Cost)
                        <span><?php echo formatUGX($calculation['inventory_total']); ?></span>
                    </li>
                    <li>
                        Good Receivables
                        <span><?php echo formatUGX($calculation['receivables_total']); ?></span>
                    </li>
                </ul>
                <div class="info-card" style="margin-top:1rem;">
                    <div><strong>Agency exclusion:</strong> <?php echo formatUGX($calculation['agency_excluded']); ?></div>
                    <div><strong>Cash float total:</strong> <?php echo formatUGX($calculation['cash_float_total']); ?></div>
                    <div><strong>Cash accounts total:</strong> <?php echo formatUGX($calculation['cash_accounts_total']); ?></div>
                </div>
            </div>

            <div class="panel">
                <h3><i class="fas fa-file-invoice"></i> Liabilities & Validation</h3>
                <ul class="breakdown-list">
                    <li>
                        Short-Term Liabilities
                        <span><?php echo formatUGX($calculation['liabilities_total']); ?></span>
                    </li>
                    <li>
                        Nisab Threshold
                        <span><?php echo formatUGX($calculation['nisab_threshold']); ?></span>
                    </li>
                    <li>
                        Zakat Rate
                        <span>2.5% (0.025)</span>
                    </li>
                </ul>
                <div class="info-card" style="margin-top:1rem;">
                    <div><strong>Rule:</strong> Zakat is due only if Net Zakat Pool ≥ Nisab.</div>
                    <div><strong>Nisab formula:</strong> 85 × gold_price_per_gram_24k.</div>
                </div>
                <form method="post" style="margin-top:1rem;">
                    <input type="hidden" name="action" value="run_calculation" />
                    <button class="btn-secondary" type="submit"><i class="fas fa-play"></i> Run Calculation Now</button>
                </form>
            </div>
        </div>

        <div class="panel" data-aos="fade-up" data-aos-delay="150">
            <h3><i class="fas fa-database"></i> Data Sources</h3>
            <div class="info-card">
                <div><strong>Cash:</strong> Latest cash float closing balance + active cash accounts.</div>
                <div><strong>Inventory:</strong> Products/variants valued at wholesale cost (dead stock excluded).</div>
                <div><strong>Receivables:</strong> Credit sales marked as good debt (debt_status = 'good').</div>
                <div><strong>Liabilities:</strong> Open short-term liabilities + pending purchase orders due this month.</div>
            </div>
        </div>

        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <h3 style="margin-bottom:0.8rem; font-size:1rem; color:#1e293b; display:flex; gap:0.6rem; align-items:center;">
                <i class="fas fa-history" style="color:#4f46e5;"></i> Recent Zakat Runs
            </h3>
            <table class="runs-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Gold Price</th>
                        <th>Nisab</th>
                        <th>Total Assets</th>
                        <th>Liabilities</th>
                        <th>Net Pool</th>
                        <th>Zakat Due</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentRuns)): ?>
                        <?php foreach ($recentRuns as $run): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($run['run_date']); ?></td>
                            <td><?php echo formatUGX($run['gold_price_per_gram']); ?></td>
                            <td><?php echo formatUGX($run['nisab_threshold']); ?></td>
                            <td><?php echo formatUGX($run['total_assets']); ?></td>
                            <td><?php echo formatUGX($run['total_liabilities']); ?></td>
                            <td><?php echo formatUGX($run['net_zakat_pool']); ?></td>
                            <td><?php echo formatUGX($run['zakat_due']); ?></td>
                            <td><?php echo htmlspecialchars($run['calculation_notes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:1.5rem; color:#94a3b8;">
                                No Zakat runs recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 400, once: true });

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
</script>
</body>
</html>
