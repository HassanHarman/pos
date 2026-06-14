<?php
// ============================================================
// SALES PAGE - MODERN UI/UX
// ALL PHP CODE MUST COME FIRST - NO OUTPUT BEFORE THIS!
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('cashier','manager','owner'));

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

function formatUGX($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

$finalcode = 'Ugx-' . createRandomPassword();
$position = function_exists('current_role') ? current_role() : strtolower(trim((string)$_SESSION['SESS_LAST_NAME']));

// Cashier float check
if ($position === 'cashier') {
    include('../connect.php');
    $uid = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;
    $today = date('Y-m-d');
    $cf = $db->prepare("SELECT float_id FROM cash_float WHERE user_id = :uid AND date = :d LIMIT 1");
    $cf->execute(array(':uid' => $uid, ':d' => $today));
    if (!$cf->fetch(PDO::FETCH_ASSOC)) {
        header('location: ../cashier/cash_float.php?err=' . urlencode('Start day cash float first'));
        exit();
    }
}

// Get cart totals for display
$invoiceId = isset($_GET['invoice']) ? $_GET['invoice'] : '';
$totalAmount = 0;
$totalProfit = 0;
if ($invoiceId) {
    include('../connect.php');
    $resultas = $db->prepare("SELECT COALESCE(SUM(amount),0) as total FROM sales_order WHERE invoice = :a");
    $resultas->execute(array(':a' => $invoiceId));
    $rowas = $resultas->fetch();
    $totalAmount = $rowas['total'];
    
    $resulta = $db->prepare("SELECT COALESCE(SUM(profit),0) as total FROM sales_order WHERE invoice = :b");
    $resulta->execute(array(':b' => $invoiceId));
    $qwe = $resulta->fetch();
    $totalProfit = $qwe['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Sales | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Select2 for better dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
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
        .invoice-badge {
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

        /* Add Product Form */
        .add-product-card {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .add-product-form {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.8rem;
        }
        .form-group {
            flex: 2;
            min-width: 200px;
        }
        .form-group.small {
            flex: 1;
            min-width: 100px;
        }
        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.3rem;
        }
        .form-group label i {
            margin-right: 0.3rem;
            color: #4f46e5;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        .form-group select:focus, .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-add-product {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-add-product:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
        }

        /* Cart Table */
        .cart-container {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 800px;
        }
        .cart-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .cart-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .cart-table tbody tr:hover {
            background: #fafbff;
        }
        .cart-table tfoot td {
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #e2e8f0;
        }
        .btn-remove {
            background: #fee2e2;
            border: none;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            color: #dc2626;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-remove:hover {
            background: #fecaca;
        }
        .btn-save {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            padding: 0.8rem;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-save:hover {
            background: linear-gradient(135deg, #047857, #059669);
            transform: translateY(-2px);
        }

        /* Alert */
        .alert-modern {
            border-radius: 14px;
            padding: 0.8rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.85rem;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
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
            .add-product-form {
                flex-direction: column;
                align-items: stretch;
            }
            .form-group, .form-group.small {
                width: 100%;
            }
            .btn-add-product {
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
        if($position=='owner' || $position=='manager' || $position=='admin') {
            $activePage = 'sales';
            include('owner_sidebar.php');
        } else {
            $activePage = 'new_sale';
            include('../cashier/cashier_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-shopping-cart"></i>
                Point of Sale
            </h2>
            <div class="invoice-badge">
                <i class="fas fa-receipt"></i>
                Invoice: <strong><?php echo htmlspecialchars($_GET['invoice']); ?></strong>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Sales</span>
        </div>
        
        <!-- Alert Messages -->
        <?php if(isset($_GET['err']) && $_GET['err']!=''): ?>
        <div class="alert-modern alert-danger" data-aos="fade">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($_GET['err']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Add Product Form -->
        <div class="add-product-card" data-aos="fade-up" data-aos-delay="100">
            <form action="incoming.php" method="post" class="add-product-form">
                <input type="hidden" name="pt" value="<?php echo htmlspecialchars($_GET['id']); ?>" />
                <input type="hidden" name="invoice" value="<?php echo htmlspecialchars($_GET['invoice']); ?>" />
                <input type="hidden" name="date" value="<?php echo date("m/d/y"); ?>" />
                <input type="hidden" name="discount" value="" />
                
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Product</label>
                    <select name="product" class="product-select" required>
                        <option value="">-- Select Product --</option>
                        <?php
                        include('../connect.php');
                        $result = $db->prepare("SELECT * FROM products");
                        $result->execute();
                        while($row = $result->fetch()):
                        ?>
                        <option value="<?php echo $row['product_id']; ?>">
                            <?php echo htmlspecialchars($row['product_code']); ?> - <?php echo htmlspecialchars($row['gen_name']); ?> - <?php echo htmlspecialchars($row['product_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group small">
                    <label><i class="fas fa-layer-group"></i> Variant</label>
                    <select name="variant_id" id="variant_id">
                        <option value="">-- Select --</option>
                    </select>
                </div>
                
                <div class="form-group small">
                    <label><i class="fas fa-calculator"></i> Quantity</label>
                    <input type="number" name="qty" value="1" min="1" placeholder="Qty" required />
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-add-product">
                        <i class="fas fa-plus-circle"></i> Add to Cart
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Cart Table -->
        <div class="cart-container" data-aos="fade-up" data-aos-delay="200">
            <table class="cart-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Product Name</th>
                        <th><i class="fas fa-capsules"></i> Generic Name</th>
                        <th><i class="fas fa-folder"></i> Category</th>
                        <th><i class="fas fa-money-bill-wave"></i> Price (UGX)</th>
                        <th><i class="fas fa-calculator"></i> Qty</th>
                        <th><i class="fas fa-chart-line"></i> Amount (UGX)</th>
                        <th><i class="fas fa-chart-simple"></i> Profit (UGX)</th>
                        <th><i class="fas fa-cog"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $id = $_GET['invoice'];
                    include('../connect.php');
                    $result = $db->prepare("SELECT * FROM sales_order WHERE invoice = :userid");
                    $result->execute(array(':userid' => $id));
                    $cartItems = 0;
                    while($row = $result->fetch()):
                        $cartItems++;
                    ?>
                    <tr class="record">
                        <td hidden><?php echo $row['product']; ?></td>
                        <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['gen_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo formatUGX($row['price']); ?></td>
                        <td><?php echo $row['qty']; ?></td>
                        <td><?php echo formatUGX($row['amount']); ?></td>
                        <td><?php echo formatUGX($row['profit']); ?></td>
                        <td>
                            <a href="delete.php?id=<?php echo $row['transaction_id']; ?>&invoice=<?php echo $_GET['invoice']; ?>&dle=<?php echo $_GET['id']; ?>&qty=<?php echo $row['qty']; ?>&product_id=<?php echo $row['product_id']; ?>&variant_id=<?php echo $row['variant_id']; ?>" class="btn-remove" onclick="return confirm('Remove this item from cart?')">
                                <i class="fas fa-trash-alt"></i> Remove
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if($cartItems == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            <i class="fas fa-shopping-cart" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            Cart is empty. Add products to continue.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if($cartItems > 0): ?>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total Amount:</strong></td>
                        <td colspan="2">
                            <strong><?php echo formatUGX($totalAmount); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;"><strong>Total Profit:</strong></td>
                        <td colspan="2">
                            <strong style="color: #059669;"><?php echo formatUGX($totalProfit); ?></strong>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Action Buttons -->
        <?php if($cartItems > 0): ?>
        <div data-aos="fade-up" data-aos-delay="300">
            <a rel="facebox" href="checkout.php?pt=<?php echo $_GET['id']; ?>&invoice=<?php echo $_GET['invoice']; ?>&total=<?php echo $totalAmount; ?>&totalprof=<?php echo $totalProfit; ?>&cashier=<?php echo $_SESSION['SESS_FIRST_NAME']; ?>" class="btn-save">
                <i class="fas fa-check-circle"></i> Complete Sale & Checkout
            </a>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="src/facebox.js" type="text/javascript"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Initialize Select2 for product dropdown
    $(document).ready(function() {
        $('.product-select').select2({
            placeholder: "Search for a product...",
            allowClear: true,
            width: '100%'
        });
    });
    
    // Facebox initialization
    jQuery(document).ready(function($) {
        $('a[rel*=facebox]').facebox({
            loadingImage: 'src/loading.gif',
            closeImage: 'src/closelabel.png'
        });
    });
    
    // Load variants based on selected product
    $(function(){
        function loadVariants() {
            var pid = $('select[name="product"]').val();
            if (!pid) {
                $('#variant_id').html('<option value="">-- Select --</option>');
                return;
            }
            $('#variant_id').load('get_variants.php?product_id=' + encodeURIComponent(pid));
        }
        $('select[name="product"]').on('change', loadVariants);
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