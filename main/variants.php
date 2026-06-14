<?php
// ============================================================
// PRODUCT VARIANTS PAGE - MODERN UI/UX
// Fully responsive with variant management
// Preserves ALL original functionality
// Currency: UGX (Ugandan Shilling)
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));
include('../connect.php');

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
$p = $db->prepare("SELECT product_id, product_name, product_code FROM products WHERE product_id = :id");
$p->execute(array(':id' => $product_id));
$product = $p->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    echo 'Product not found';
    exit();
}

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

// Get variant statistics
$statsQuery = $db->prepare("
    SELECT 
        COUNT(*) as total_variants,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_variants,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_variants,
        COALESCE(SUM(current_stock), 0) as total_stock
    FROM product_variants 
    WHERE product_id = :pid
");
$statsQuery->execute(array(':pid' => $product_id));
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Variants | POS System</title>
    
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
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .page-header-modern h2 i {
            color: #a5b4fc;
            font-size: 1.3rem;
        }
        .product-badge {
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
            display: flex;
            align-items: center;
            gap: 1rem;
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
        }
        .stat-icon i {
            font-size: 1.3rem;
            color: #4f46e5;
        }
        .stat-icon.stock {
            background: #d1fae5;
        }
        .stat-icon.stock i {
            color: #059669;
        }
        .stat-info h4 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
            color: #1e293b;
        }
        .stat-info p {
            margin: 0;
            font-size: 0.65rem;
            color: #64748b;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn-back {
            background: #f1f5f9;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 40px;
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
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .btn-add-modern {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-add-modern:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            color: white;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .variants-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 800px;
        }
        .variants-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .variants-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .variants-table tbody tr:hover {
            background: #fafbff;
        }

        /* Status Badges */
        .status-active {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
        }
        .stock-low {
            color: #d97706;
            font-weight: 600;
        }
        .stock-critical {
            color: #dc2626;
            font-weight: 600;
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
        .btn-toggle {
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
        .btn-toggle:hover {
            background: #bfdbfe;
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
            .action-bar {
                flex-direction: column;
            }
            .btn-back, .btn-add-modern {
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
                font-size: 0.9rem;
            }
            .stats-grid {
                gap: 0.8rem;
            }
            .stat-info h4 {
                font-size: 1rem;
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
                $activePage = 'products';
            }
            include(__DIR__ . '/../stock/stock_sidebar.php');
        } elseif ($role === 'owner') {
            $activePage = 'products';
            include(__DIR__ . '/owner_sidebar.php');
        } else {
            $activePage = 'products';
            include(__DIR__ . '/manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-th-list"></i>
                Product Variants: <?php echo htmlspecialchars($product['product_code']); ?> - <?php echo htmlspecialchars($product['product_name']); ?>
            </h2>
            <div class="product-badge">
                <i class="fas fa-box"></i>
                Product ID: <?php echo htmlspecialchars($product_id); ?>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <a href="products.php">Products</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Variants</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <h4><?php echo $stats['total_variants']; ?></h4>
                    <p>Total Variants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h4><?php echo $stats['active_variants']; ?></h4>
                    <p>Active Variants</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stock"><i class="fas fa-boxes"></i></div>
                <div class="stat-info">
                    <h4><?php echo number_format($stats['total_stock']); ?></h4>
                    <p>Total Stock</p>
                </div>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div class="action-bar" data-aos="fade-up" data-aos-delay="150">
            <a href="products.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
            <a rel="facebox" href="addvariant.php?product_id=<?php echo $product['product_id']; ?>" class="btn-add-modern">
                <i class="fas fa-plus-circle"></i> Add New Variant
            </a>
        </div>
        
        <!-- Variants Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="variants-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Variant</th>
                        <th><i class="fas fa-chart-line"></i> Cost (UGX)</th>
                        <th><i class="fas fa-tag"></i> Price (UGX)</th>
                        <th><i class="fas fa-boxes"></i> Stock</th>
                        <th><i class="fas fa-flag"></i> Min Level</th>
                        <th><i class="fas fa-power-off"></i> Status</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $db->prepare("SELECT variant_id, variant_name, cost, price, current_stock, min_stock_level, is_active FROM product_variants WHERE product_id = :pid ORDER BY variant_name ASC");
                    $result->execute(array(':pid' => $product_id));
                    $hasResults = false;
                    while($row = $result->fetch()):
                        $hasResults = true;
                        $isActive = (int)$row['is_active'] === 1;
                        $stockClass = '';
                        if($row['current_stock'] <= 0) {
                            $stockClass = 'stock-critical';
                        } elseif($row['current_stock'] <= $row['min_stock_level']) {
                            $stockClass = 'stock-low';
                        }
                    ?>
                    <tr class="record">
                        <td><strong><?php echo htmlspecialchars($row['variant_name']); ?></strong></td>
                        <td><?php echo formatUGX($row['cost']); ?></td>
                        <td class="fw-bold" style="color:#4f46e5;"><?php echo formatUGX($row['price']); ?></td>
                        <td><span class="<?php echo $stockClass; ?>"><?php echo number_format($row['current_stock']); ?></span></td>
                        <td><?php echo $row['min_stock_level']; ?></td>
                        <td>
                            <?php if($isActive): ?>
                                <span class="status-active"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> Active</span>
                            <?php else: ?>
                                <span class="status-inactive"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a rel="facebox" href="editvariant.php?id=<?php echo $row['variant_id']; ?>&product_id=<?php echo $product_id; ?>" class="btn-edit" title="Edit Variant">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="togglevariant.php?id=<?php echo $row['variant_id']; ?>&product_id=<?php echo $product_id; ?>" class="btn-toggle" title="Toggle Status" onclick="return confirm('Change variant active status?')">
                                <i class="fas fa-sync-alt"></i> Toggle
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr class="no-data">
                        <td colspan="7">
                            <i class="fas fa-layer-group"></i>
                            <p>No variants found for this product. Click "Add New Variant" to create one.</p>
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