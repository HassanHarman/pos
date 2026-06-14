<?php
// ============================================================
// PRODUCTS MANAGEMENT PAGE - MODERN UI/UX
// Fully responsive with product listing, search, and management
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
$finalcode = 'Ugx-' . createRandomPassword();

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

$assetPrefix = (isset($portal) && $portal === 'stock') ? '../main/' : '';
$isStockPortal = (isset($portal) && $portal === 'stock');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Products | POS System</title>
    
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
        .stat-icon.warning {
            background: #fee2e2;
        }
        .stat-icon.warning i {
            color: #dc2626;
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

        /* Search Bar */
        .search-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .search-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .search-wrapper input {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 40px;
            font-size: 0.85rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .search-wrapper input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        /* Products Table Container */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 1200px;
        }
        .products-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .products-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .products-table tbody tr:hover {
            background: #fafbff;
        }
        .products-table tbody tr.low-stock {
            background: #fef3c7;
        }
        .products-table tbody tr.low-stock:hover {
            background: #fde68a;
        }
        .products-table tbody tr.critical-stock {
            background: #fee2e2;
        }
        .products-table tbody tr.critical-stock:hover {
            background: #fecaca;
        }

        /* Stock Indicators */
        .stock-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
        }
        .stock-critical {
            background: #fee2e2;
            color: #dc2626;
        }
        .stock-low {
            background: #fef3c7;
            color: #d97706;
        }
        .stock-good {
            background: #d1fae5;
            color: #059669;
        }

        /* Action Buttons */
        .btn-edit {
            background: #fef3c7;
            border: none;
            padding: 0.25rem 0.6rem;
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
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            color: #2563eb;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-variants:hover {
            background: #bfdbfe;
        }
        .btn-delete {
            background: #fee2e2;
            border: none;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            color: #dc2626;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-delete:hover {
            background: #fecaca;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 2rem;
            color: #94a3b8;
        }
        .no-results i {
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
                font-size: 1rem;
            }
            .stats-grid {
                gap: 0.8rem;
            }
            .stat-info h4 {
                font-size: 1.2rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .action-bar, .search-wrapper, .btn-edit, .btn-variants, .btn-delete {
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
                <i class="fas fa-boxes"></i>
                Product Management
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
            <span class="text-dark fw-semibold">Products</span>
        </div>
        
        <?php
        include('../connect.php');
        $result = $db->prepare("SELECT * FROM products ORDER BY qty_sold DESC");
        $result->execute();
        $rowcount = $result->rowCount();
        
        $resultLow = $db->prepare("SELECT COUNT(*) AS low_count FROM (
            SELECT products.product_id, COALESCE(variant_stock.total_stock, products.qty) AS qty_left
            FROM products
            LEFT JOIN (
                SELECT product_id, SUM(current_stock) AS total_stock
                FROM product_variants
                WHERE is_active = 1
                GROUP BY product_id
            ) variant_stock ON variant_stock.product_id = products.product_id
        ) AS stock_view WHERE qty_left < 10");
        $resultLow->execute();
        $lowRow = $resultLow->fetch(PDO::FETCH_ASSOC);
        $lowStockCount = (is_array($lowRow) && isset($lowRow['low_count'])) ? (int)$lowRow['low_count'] : 0;
        ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                <div class="stat-info">
                    <h4><?php echo $rowcount; ?></h4>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <h4><?php echo $lowStockCount; ?></h4>
                    <p>Low Stock Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h4>Active</h4>
                    <p>Inventory Status</p>
                </div>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div class="action-bar" data-aos="fade-up" data-aos-delay="150">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <?php if (!$isStockPortal): ?>
            <a rel="facebox" href="addproduct.php" class="btn-add-modern">
                <i class="fas fa-plus-circle"></i> Add New Product
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Search Bar -->
        <div class="search-wrapper" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-search"></i>
            <input type="text" id="filter" placeholder="Search by brand name, generic name, category, or supplier..." autocomplete="off">
        </div>
        
        <!-- Products Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="250">
            <table class="products-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Brand Name</th>
                        <th><i class="fas fa-capsules"></i> Generic Name</th>
                        <th><i class="fas fa-folder"></i> Category</th>
                        <th><i class="fas fa-truck"></i> Supplier</th>
                        <th><i class="fas fa-calendar"></i> Date Received</th>
                        <th><i class="fas fa-calendar-times"></i> Expiry Date</th>
                        <th><i class="fas fa-chart-line"></i> Original Price</th>
                        <th><i class="fas fa-tag"></i> Selling Price</th>
                        <th><i class="fas fa-boxes"></i> Stock Status</th>
                        <th><i class="fas fa-chart-simple"></i> Qty Left</th>
                        <th><i class="fas fa-money-bill-wave"></i> Total Value</th>
                        <?php if (!$isStockPortal): ?>
                        <th><i class="fas fa-cog"></i> Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
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
                    
                    $result = $db->prepare("SELECT products.*, categories.category_name,
                        COALESCE(variant_stock.total_stock, products.qty) AS qty_left,
                        products.price * COALESCE(variant_stock.total_stock, products.qty) AS total
                        FROM products
                        LEFT JOIN categories ON products.category_id = categories.category_id
                        LEFT JOIN (
                            SELECT product_id, SUM(current_stock) AS total_stock
                            FROM product_variants
                            WHERE is_active = 1
                            GROUP BY product_id
                        ) variant_stock ON variant_stock.product_id = products.product_id
                        ORDER BY products.product_id DESC");
                    $result->execute();
                    $hasResults = false;
                    while(($row = $result->fetch(PDO::FETCH_ASSOC)) !== false):
                        if (!is_array($row)) {
                            continue;
                        }
                        $hasResults = true;
                        $brandName = $row['product_code'] !== '' ? $row['product_code'] : $row['product_name'];
                        $genericName = $row['gen_name'] ?: '—';
                        $categoryName = $row['category_name'] ?: '—';
                        $supplierName = $row['supplier'] ?: '—';
                        $dateArrival = $row['date_arrival'] ? date('M d, Y', strtotime($row['date_arrival'])) : '—';
                        $expiryDate = $row['expiry_date'] ? date('M d, Y', strtotime($row['expiry_date'])) : '—';
                        $total = $row['total'];
                        $availableqty = $row['qty_left'];
                        
                        $stockClass = '';
                        $rowClass = '';
                        if ($availableqty <= 0) {
                            $rowClass = 'critical-stock';
                            $stockClass = 'stock-critical';
                            $stockText = 'Out of Stock';
                        } elseif ($availableqty < 10) {
                            $rowClass = 'low-stock';
                            $stockClass = 'stock-low';
                            $stockText = 'Low Stock';
                        } else {
                            $stockClass = 'stock-good';
                            $stockText = 'Good';
                        }
                    ?>
                    <tr class="record <?php echo $rowClass; ?>">
                        <td><strong><?php echo htmlspecialchars($brandName); ?></strong></td>
                        <td><?php echo htmlspecialchars($genericName); ?></td>
                        <td><?php echo htmlspecialchars($categoryName); ?></td>
                        <td><?php echo htmlspecialchars($supplierName); ?></td>
                        <td><?php echo $dateArrival; ?></td>
                        <td><?php echo $expiryDate; ?></td>
                        
                        <td><?php echo formatUGX($row['o_price']); ?></td>
                        <td class="fw-bold" style="color:#4f46e5;"><?php echo formatUGX($row['price']); ?></td>
                        <td><span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?> (<?php echo $availableqty; ?>)</span></td>
                        <td><?php echo number_format($availableqty); ?></td>
                        <td><?php echo formatUGX($total); ?></td>
                        <?php if (!$isStockPortal): ?>
                        <td>
                            <a rel="facebox" href="editproduct.php?id=<?php echo $row['product_id']; ?>" class="btn-edit" title="Edit Product">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="variants.php?product_id=<?php echo $row['product_id']; ?>" class="btn-variants" title="Manage Variants">
                                <i class="fas fa-th-list"></i> Variants
                            </a>
                            <a href="#" id="<?php echo $row['product_id']; ?>" class="btn-delete delbutton" title="Delete Product">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr class="no-results">
                        <td colspan="<?php echo $isStockPortal ? 11 : 12; ?>">
                            <i class="fas fa-box-open"></i>
                            <p>
                                <?php echo $isStockPortal
                                    ? 'No products found in inventory.'
                                    : 'No products found. Click "Add New Product" to get started.'; ?>
                            </p>
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
    
    // Live search functionality
    $(document).ready(function() {
        $("#filter").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var hasResults = false;
            $("#resultTable tbody tr").filter(function() {
                var matches = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(matches);
                if (matches) hasResults = true;
            });
            if ($("#resultTable tbody tr:visible").length === 0 && !hasResults) {
                if ($("#noResultsMsg").length === 0) {
                    $("#resultTable tbody").append('<tr id="noResultsMsg" class="no-results"><td colspan="12"><i class="fas fa-search"></i><p>No products match your search.</p></td></tr>');
                }
            } else {
                $("#noResultsMsg").remove();
            }
        });
    });
    
    // Delete product with confirmation
    $(".delbutton").click(function() {
        var element = $(this);
        var del_id = element.attr("id");
        var info = 'id=' + del_id;
        
        if(confirm("Are you sure you want to delete this product? This action cannot be undone!")) {
            $.ajax({
                type: "GET",
                url: "deleteproduct.php",
                data: info,
                success: function() {
                    element.parents(".record").fadeOut('slow', function() {
                        $(this).remove();
                    });
                }
            });
        }
        return false;
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