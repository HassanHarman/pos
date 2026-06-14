<?php
// ============================================================
// DASHBOARD PAGE - MODERN UI/UX
// Fully responsive with role-based navigation
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');

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

$role = function_exists('current_role') ? current_role() : strtolower(trim((string)$_SESSION['SESS_LAST_NAME']));

include('../connect.php');

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

function formatUGX($amount) {
    return 'UGX ' . formatMoney((float)$amount, true);
}

$totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCustomers = (int)$db->query("SELECT COUNT(*) FROM customer")->fetchColumn();
$totalSuppliers = (int)$db->query("SELECT COUNT(*) FROM supliers")->fetchColumn();

$stockBaseSql = "SELECT products.product_id,
    COALESCE(variant_stock.total_stock, products.qty) AS qty_left,
    COALESCE(products.min_stock_level, 10) AS min_level,
    products.price
    FROM products
    LEFT JOIN (
        SELECT product_id, SUM(current_stock) AS total_stock
        FROM product_variants
        WHERE is_active = 1
        GROUP BY product_id
    ) variant_stock ON variant_stock.product_id = products.product_id";

$lowStockStmt = $db->prepare("SELECT COUNT(*) FROM ($stockBaseSql) AS stock_view WHERE qty_left <= min_level");
$lowStockStmt->execute();
$lowStockCount = (int)$lowStockStmt->fetchColumn();

$outStockStmt = $db->prepare("SELECT COUNT(*) FROM ($stockBaseSql) AS stock_view WHERE qty_left <= 0");
$outStockStmt->execute();
$outOfStockCount = (int)$outStockStmt->fetchColumn();

$inventoryStmt = $db->prepare("SELECT COALESCE(SUM(price * qty_left), 0) FROM ($stockBaseSql) AS stock_view");
$inventoryStmt->execute();
$inventoryValue = (float)$inventoryStmt->fetchColumn();

$salesDateExpr = "COALESCE(created_at, STR_TO_DATE(date, '%m/%d/%y'))";

$salesTodayStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM sales WHERE DATE($salesDateExpr) = CURDATE()");
$salesTodayStmt->execute();
$salesToday = (float)$salesTodayStmt->fetchColumn();

$transactionsTodayStmt = $db->prepare("SELECT COUNT(*) FROM sales WHERE DATE($salesDateExpr) = CURDATE()");
$transactionsTodayStmt->execute();
$transactionsToday = (int)$transactionsTodayStmt->fetchColumn();

$salesMonthStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM sales WHERE DATE_FORMAT($salesDateExpr, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
$salesMonthStmt->execute();
$salesMonth = (float)$salesMonthStmt->fetchColumn();

$profitMonthStmt = $db->prepare("SELECT COALESCE(SUM(profit),0) FROM sales WHERE DATE_FORMAT($salesDateExpr, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
$profitMonthStmt->execute();
$profitMonth = (float)$profitMonthStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Dashboard | POS System</title>
    
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

        /* Main content takes remaining space */
        .app-content {
            flex: 1;
            min-width: 0;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        /* Welcome Header */
        .welcome-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(165,180,252,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .welcome-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .welcome-header h1 i {
            color: #a5b4fc;
            font-size: 1.8rem;
        }
        .welcome-header .store-name {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 0.5rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.2rem;
        }
        .dashboard-card {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -12px rgba(0, 0, 0, 0.15);
            border-color: #c7d2fe;
        }
        .dashboard-card i {
            font-size: 2.2rem;
            transition: transform 0.2s;
        }
        .dashboard-card:hover i {
            transform: scale(1.1);
        }
        .dashboard-card span {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
        }

        /* Summary Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1rem 1.2rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            gap: 0.8rem;
            align-items: center;
        }
        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
        }
        .stat-meta {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .stat-label {
            font-size: 0.7rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .app-content {
                width: 100%;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                gap: 1rem;
            }
            .dashboard-card {
                padding: 1rem;
            }
            .dashboard-card i {
                font-size: 1.8rem;
            }
            .welcome-header h1 {
                font-size: 1.2rem;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }
            .stat-value {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
<?php $activePage = 'dashboard'; include('owner_sidebar.php'); ?>

<div class="admin-main-wrapper">
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Welcome Header -->
        <div class="welcome-header" data-aos="fade-down">
            <h1>
                <i class="fas fa-chart-line"></i>
                Dashboard
            </h1>
            <div class="store-name">
                <i class="fas fa-store me-1"></i> Real Sisters POS
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-card">
                <div class="stat-icon" style="background:#4f46e5;"><i class="fas fa-cash-register"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Sales Today</span>
                    <span class="stat-value"><?php echo formatUGX($salesToday); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#0ea5e9;"><i class="fas fa-receipt"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Transactions Today</span>
                    <span class="stat-value"><?php echo number_format($transactionsToday); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#059669;"><i class="fas fa-chart-area"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Sales This Month</span>
                    <span class="stat-value"><?php echo formatUGX($salesMonth); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f59e0b;"><i class="fas fa-sack-dollar"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Profit This Month</span>
                    <span class="stat-value"><?php echo formatUGX($profitMonth); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#22c55e;"><i class="fas fa-warehouse"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Inventory Value</span>
                    <span class="stat-value"><?php echo formatUGX($inventoryValue); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#14b8a6;"><i class="fas fa-boxes"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Total Products</span>
                    <span class="stat-value"><?php echo number_format($totalProducts); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#ef4444;"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Low Stock Items</span>
                    <span class="stat-value"><?php echo number_format($lowStockCount); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#be123c;"><i class="fas fa-ban"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Out of Stock</span>
                    <span class="stat-value"><?php echo number_format($outOfStockCount); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#6366f1;"><i class="fas fa-users"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Customers</span>
                    <span class="stat-value"><?php echo number_format($totalCustomers); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#2563eb;"><i class="fas fa-truck"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Suppliers</span>
                    <span class="stat-value"><?php echo number_format($totalSuppliers); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Grid -->
        <div class="dashboard-grid" data-aos="fade-up" data-aos-delay="100">
            <a href="sales.php?id=cash&invoice=<?php echo $finalcode; ?>" class="dashboard-card">
                <i class="fas fa-shopping-cart" style="color: #4f46e5;"></i>
                <span>Sales</span>
            </a>
            <a href="products.php" class="dashboard-card">
                <i class="fas fa-boxes" style="color: #059669;"></i>
                <span>Products</span>
            </a>
            <a href="customer.php" class="dashboard-card">
                <i class="fas fa-users" style="color: #d97706;"></i>
                <span>Customers</span>
            </a>
            <a href="supplier.php" class="dashboard-card">
                <i class="fas fa-truck" style="color: #2563eb;"></i>
                <span>Suppliers</span>
            </a>
            <a href="salesreport.php?d1=0&d2=0" class="dashboard-card">
                <i class="fas fa-chart-bar" style="color: #8b5cf6;"></i>
                <span>Sales Report</span>
            </a>
            <a href="../index.php" class="dashboard-card" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt" style="color: #dc2626;"></i>
                <span>Logout</span>
            </a>
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
    
    // Force scroll to top
    if('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
</script>

</body>
</html>