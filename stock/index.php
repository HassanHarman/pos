<?php
require_once('../main/auth.php');
require_role(array('stock_manager','manager','owner'));
include('../connect.php');

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

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

$totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalSuppliers = (int)$db->query("SELECT COUNT(*) FROM supliers")->fetchColumn();
$totalCategories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalPurchaseOrders = (int)$db->query("SELECT COUNT(*) FROM purchase_orders")->fetchColumn();

$lowStockStmt = $db->prepare("SELECT COUNT(*) FROM ($stockBaseSql) AS stock_view WHERE qty_left <= min_level");
$lowStockStmt->execute();
$lowStockCount = (int)$lowStockStmt->fetchColumn();

$outStockStmt = $db->prepare("SELECT COUNT(*) FROM ($stockBaseSql) AS stock_view WHERE qty_left <= 0");
$outStockStmt->execute();
$outOfStockCount = (int)$outStockStmt->fetchColumn();

$inventoryStmt = $db->prepare("SELECT COALESCE(SUM(price * qty_left), 0) FROM ($stockBaseSql) AS stock_view");
$inventoryStmt->execute();
$inventoryValue = (float)$inventoryStmt->fetchColumn();

$pendingPoStmt = $db->prepare("SELECT COUNT(*) FROM purchase_orders WHERE status <> 'received'");
$pendingPoStmt->execute();
$pendingPurchaseOrders = (int)$pendingPoStmt->fetchColumn();

$activePage = 'dashboard';
function stock_active($key, $activePage) {
    return $key === $activePage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Stock Manager | POS System</title>

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
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            overflow-x: hidden;
        }

        a,
        a:hover,
        a:focus {
            text-decoration: none;
        }

        .stock-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar-brand h3 {
            font-size: 1.2rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(120deg, #fff, #a5b4fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sidebar-brand small {
            font-size: 0.65rem;
            color: #64748b;
            display: block;
            margin-top: 0.35rem;
        }

        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.75rem 0;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 0.125rem;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.2rem;
            color: #94a3b8;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 0.85rem;
            border-left: 3px solid transparent;
        }

        .sidebar-nav li a i {
            width: 1.6rem;
            font-size: 1.1rem;
            text-align: center;
            transition: transform 0.2s;
        }

        .sidebar-nav li a:hover {
            background: rgba(165, 180, 252, 0.1);
            color: white;
            border-left-color: #a5b4fc;
        }

        .sidebar-nav li a:hover i {
            transform: translateX(3px);
        }

        .sidebar-nav li.active a {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.2), transparent);
            color: white;
            border-left-color: #4f46e5;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem;
            background: rgba(15, 23, 42, 0.95);
        }

        .clock-widget {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 0.8rem;
            text-align: center;
            margin-bottom: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .clock-time {
            font-size: 1.15rem;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            color: #a5b4fc;
        }

        .clock-date {
            font-size: 0.65rem;
            color: #64748b;
            margin-top: 0.2rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 18px -12px rgba(220, 38, 38, 0.6);
        }

        .mobile-menu-toggle {
            position: fixed;
            top: 16px;
            left: 16px;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            border: none;
            background: #4f46e5;
            color: white;
            z-index: 1101;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .admin-main-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .app-content {
            min-width: 0;
            padding: 1rem 1.5rem;
        }

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

        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: flex;
            }
            .stock-sidebar {
                transform: translateX(-100%);
            }
            .stock-sidebar.mobile-open {
                transform: translateX(0);
            }
            .admin-main-wrapper {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .app-content {
                padding: 1rem;
                padding-top: 4rem;
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
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<?php include(__DIR__ . '/stock_sidebar.php'); ?>

<div class="admin-main-wrapper">
    <div class="app-content">
        <div class="welcome-header" data-aos="fade-down">
            <h1><i class="fas fa-boxes-stacked"></i> Stock Dashboard</h1>
            <div class="store-name"><i class="fas fa-store me-1"></i> Inventory Operations</div>
        </div>

        <div class="stats-grid" data-aos="fade-up" data-aos-delay="60">
            <div class="stat-card">
                <div class="stat-icon" style="background:#4f46e5;"><i class="fas fa-box"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Total Products</span>
                    <span class="stat-value"><?php echo $totalProducts; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#059669;"><i class="fas fa-coins"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Inventory Value</span>
                    <span class="stat-value"><?php echo formatUGX($inventoryValue); ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#f59e0b;"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Low Stock Items</span>
                    <span class="stat-value"><?php echo $lowStockCount; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#ef4444;"><i class="fas fa-ban"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Out of Stock</span>
                    <span class="stat-value"><?php echo $outOfStockCount; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#0ea5e9;"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Purchase Orders</span>
                    <span class="stat-value"><?php echo $totalPurchaseOrders; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#6366f1;"><i class="fas fa-clock"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Pending Orders</span>
                    <span class="stat-value"><?php echo $pendingPurchaseOrders; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#22c55e;"><i class="fas fa-truck"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Suppliers</span>
                    <span class="stat-value"><?php echo $totalSuppliers; ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#8b5cf6;"><i class="fas fa-tags"></i></div>
                <div class="stat-meta">
                    <span class="stat-label">Categories</span>
                    <span class="stat-value"><?php echo $totalCategories; ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-grid" data-aos="fade-up" data-aos-delay="120">
            <a class="dashboard-card" href="products.php">
                <i class="fas fa-boxes" style="color:#4f46e5;"></i>
                <span>Products</span>
            </a>
            <a class="dashboard-card" href="lowstock.php">
                <i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i>
                <span>Low Stock</span>
            </a>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 300, once: true });

    // Sidebar and clock are handled in stock_sidebar.php
</script>
</body>
</html>
