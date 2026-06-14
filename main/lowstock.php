<?php
// ============================================================
// LOW STOCK ALERT PAGE - MODERN UI/UX
// Fully responsive with stock monitoring
// Displays products with stock <= min level
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));
include('../connect.php');
$assetPrefix = (isset($portal) && $portal === 'stock') ? '../main/' : '';
$isStockPortal = (isset($portal) && $portal === 'stock');

// Get total number of low stock items for stats
$countSql = "SELECT COUNT(*) as total FROM (
    SELECT p.product_id, NULL AS variant_id FROM products p
    WHERE p.is_active = 1 AND p.qty <= p.min_stock_level
    UNION ALL
    SELECT p.product_id, v.variant_id FROM product_variants v
    INNER JOIN products p ON p.product_id = v.product_id
    WHERE v.is_active = 1 AND p.is_active = 1 AND v.current_stock <= v.min_stock_level
) x";
$countResult = $db->prepare($countSql);
$countResult->execute();
$totalLowStock = $countResult->fetch(PDO::FETCH_ASSOC)['total'];

// Get critical stock (stock = 0)
$criticalSql = "SELECT COUNT(*) as total FROM (
    SELECT p.product_id FROM products p
    WHERE p.is_active = 1 AND p.qty = 0
    UNION ALL
    SELECT p.product_id FROM product_variants v
    INNER JOIN products p ON p.product_id = v.product_id
    WHERE v.is_active = 1 AND p.is_active = 1 AND v.current_stock = 0
) x";
$criticalResult = $db->prepare($criticalSql);
$criticalResult->execute();
$criticalCount = $criticalResult->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Low Stock Alert | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Facebox -->
    <link href="<?php echo $assetPrefix; ?>src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
    
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
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
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
            font-size: 1.5rem;
        }
        .alert-badge {
            background: rgba(255,255,255,0.2);
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
        .stat-icon.warning i {
            color: #d97706;
        }
        .stat-icon.danger i {
            color: #dc2626;
        }
        .stat-icon.warning-bg {
            background: #fef3c7;
        }
        .stat-icon.danger-bg {
            background: #fee2e2;
        }
        .stat-info h4 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            color: #1e293b;
        }
        .stat-info p {
            margin: 0;
            font-size: 0.7rem;
            color: #64748b;
        }

        /* Low Stock Table Container */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .lowstock-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 800px;
        }
        .lowstock-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .lowstock-table tbody td {
            padding: 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .lowstock-table tbody tr:hover {
            background: #fafbff;
        }

        /* Stock Level Indicators */
        .stock-critical {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-block;
        }
        .stock-low {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-block;
        }
        .stock-warning {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-block;
        }

        /* Action Buttons */
        .btn-edit {
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
        .btn-edit:hover {
            background: #fde68a;
        }
        .btn-variants {
            background: #dbeafe;
            border: none;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            color: #2563eb;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-left: 0.3rem;
        }
        .btn-variants:hover {
            background: #bfdbfe;
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
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 0.7rem 1rem;
            border-radius: 14px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
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
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }
            .stat-card {
                padding: 0.8rem;
            }
            .stat-info h4 {
                font-size: 1.2rem;
            }
            .page-header-modern h2 {
                font-size: 1rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .btn-edit, .btn-variants {
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
                $activePage = 'lowstock';
            }
            include(__DIR__ . '/../stock/stock_sidebar.php');
        } elseif ($role === 'owner') {
            $activePage = 'lowstock';
            include(__DIR__ . '/owner_sidebar.php');
        } else {
            $activePage = 'lowstock';
            include(__DIR__ . '/manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-exclamation-triangle"></i>
                Low Stock Alert
            </h2>
            <div class="alert-badge">
                <i class="fas fa-bell"></i>
                Products at or below minimum level
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Low Stock</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon warning-bg">
                    <i class="fas fa-exclamation-triangle" style="color: #d97706;"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $totalLowStock; ?></h4>
                    <p>Products Low in Stock</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger-bg">
                    <i class="fas fa-times-circle" style="color: #dc2626;"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $criticalCount; ?></h4>
                    <p>Out of Stock / Critical</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <h4>Alert</h4>
                    <p>Restock immediately</p>
                </div>
            </div>
        </div>
        
        <!-- Low Stock Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="lowstock-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-barcode me-1"></i> Product Code</th>
                        <th><i class="fas fa-tag me-1"></i> Product Name</th>
                        <th><i class="fas fa-folder me-1"></i> Category</th>
                        <th><i class="fas fa-ruler me-1"></i> Unit</th>
                        <th><i class="fas fa-boxes me-1"></i> Current Stock</th>
                        <th><i class="fas fa-flag me-1"></i> Min Level</th>
                        <?php if (!$isStockPortal): ?>
                        <th><i class="fas fa-cog me-1"></i> Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM (
                        SELECT p.product_id, NULL AS variant_id, p.product_code, p.product_name, c.category_name, p.unit_type, p.qty AS stock_qty, p.min_stock_level AS min_level
                        FROM products p
                        LEFT JOIN categories c ON c.category_id = p.category_id
                        WHERE p.is_active = 1 AND p.qty <= p.min_stock_level
                        UNION ALL
                        SELECT p.product_id, v.variant_id, p.product_code, CONCAT(p.product_name,' - ',v.variant_name) AS product_name, c.category_name, p.unit_type, v.current_stock AS stock_qty, v.min_stock_level AS min_level
                        FROM product_variants v
                        INNER JOIN products p ON p.product_id = v.product_id
                        LEFT JOIN categories c ON c.category_id = p.category_id
                        WHERE v.is_active = 1 AND p.is_active = 1 AND v.current_stock <= v.min_stock_level
                    ) x
                    ORDER BY x.stock_qty ASC, x.product_name ASC";
                    $result = $db->prepare($sql);
                    $result->execute();
                    $hasResults = false;
                    while($row = $result->fetch()):
                        $hasResults = true;
                        $stockClass = '';
                        $stockText = '';
                        if($row['stock_qty'] <= 0) {
                            $stockClass = 'stock-critical';
                            $stockText = 'Out of Stock!';
                        } elseif($row['stock_qty'] <= $row['min_level'] / 2) {
                            $stockClass = 'stock-critical';
                            $stockText = 'Critical';
                        } else {
                            $stockClass = 'stock-warning';
                            $stockText = 'Low';
                        }
                    ?>
                    <tr class="record">
                        <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['unit_type']); ?></td>
                        <td><span class="<?php echo $stockClass; ?>">
                            <i class="fas fa-<?php echo ($row['stock_qty'] <= 0) ? 'ban' : 'exclamation-circle'; ?> me-1"></i>
                            <?php echo $row['stock_qty']; ?> <?php echo $stockText ? '(' . $stockText . ')' : ''; ?>
                        </span></td>
                        <td><?php echo $row['min_level']; ?></td>
                        <?php if (!$isStockPortal): ?>
                        <td>
                            <a rel="facebox" href="editproduct.php?id=<?php echo $row['product_id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($row['variant_id'] !== null): ?>
                            <a href="variants.php?product_id=<?php echo $row['product_id']; ?>" class="btn-variants">
                                <i class="fas fa-th-list"></i> Variants
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr>
                        <td colspan="<?php echo $isStockPortal ? 6 : 7; ?>" class="no-data">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i>
                            <p>Great news! No products are low in stock at the moment.</p>
                            <p style="font-size: 0.75rem; margin-top: 0.5rem;">All inventory levels are healthy.</p>
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
<script src="<?php echo $assetPrefix; ?>src/facebox.js" type="text/javascript"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Facebox initialization
    jQuery(document).ready(function($) {
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo $assetPrefix; ?>src/loading.gif',
            closeImage: '<?php echo $assetPrefix; ?>src/closelabel.png'
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