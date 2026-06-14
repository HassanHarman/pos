<?php
// ============================================================
// POS - POINT OF SALE (CASHIER DESK)
// HIGHLY RESPONSIVE & FAST CHECKOUT UI/UX
// PRESERVES ALL ORIGINAL PHP FUNCTIONALITY
// VAT CALCULATION FIXED - v2.0
// ============================================================

require_once('../main/auth.php');
require_role(array('cashier'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

function formatMoney($amount) {
    return 'UGX ' . number_format((float)$amount, 2);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'products') {
    $invoiceAjax = isset($_GET['invoice']) ? trim((string)$_GET['invoice']) : '';
    $searchAjax = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

    $paramsAjax = array();
    $sqlProductsAjax = "SELECT product_id, product_code, product_name, price, qty FROM products WHERE is_active = 1";
    if ($searchAjax !== '') {
        $sqlProductsAjax .= " AND (product_name LIKE :q OR product_code LIKE :q OR gen_name LIKE :q)";
        $paramsAjax[':q'] = '%' . $searchAjax . '%';
    }
    $sqlProductsAjax .= " ORDER BY product_name ASC LIMIT 80";
    $pqAjax = $db->prepare($sqlProductsAjax);
    $pqAjax->execute($paramsAjax);

    header('Content-Type: text/html; charset=utf-8');
    while ($p = $pqAjax->fetch(PDO::FETCH_ASSOC)) {
        $href = 'add_to_cart.php?invoice=' . urlencode($invoiceAjax) . '&product_id=' . urlencode($p['product_id']);
        echo '<div class="product-card" data-product-id="' . h($p['product_id']) . '" data-price="' . h($p['price']) . '" data-name="' . h($p['product_name']) . '">';
        echo '<div class="product-name">' . h($p['product_name']) . '</div>';
        echo '<div class="product-code"><i class="fas fa-barcode"></i> ' . h($p['product_code']) . '</div>';
        echo '<div class="stock-badge"><i class="fas fa-boxes"></i> Stock: ' . h($p['qty']) . '</div>';
        echo '<div class="price">' . h(formatMoney($p['price'])) . '</div>';
        echo '<a rel="facebox" href="' . h($href) . '" class="add-btn"><i class="fas fa-plus"></i></a>';
        echo '</div>';
    }
    exit();
}

$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;
$today = date('Y-m-d');
$cf = $db->prepare("SELECT float_id, closing_balance FROM cash_float WHERE user_id = :uid AND date = :d LIMIT 1");
$cf->execute(array(':uid' => $userId, ':d' => $today));
$cfRow = $cf->fetch(PDO::FETCH_ASSOC);
if (!$cfRow) {
    header('location: cash_float.php?err=' . urlencode('Start day cash float first'));
    exit();
}
if (isset($cfRow['closing_balance']) && $cfRow['closing_balance'] !== null) {
    header('location: cash_float.php?err=' . urlencode('Day is already closed'));
    exit();
}

$invoice = isset($_GET['invoice']) ? trim((string)$_GET['invoice']) : '';
if ($invoice === '') {
    $chars = '003232303232023232023456789';
    $pass = '';
    for ($i = 0; $i < 8; $i++) {
        $pass .= substr($chars, random_int(0, strlen($chars) - 1), 1);
    }
    $invoice = 'RS-' . $pass;
    header('location: pos.php?invoice=' . urlencode($invoice));
    exit();
}

$search = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$err = isset($_GET['err']) ? (string)$_GET['err'] : '';
$msg = isset($_GET['msg']) ? (string)$_GET['msg'] : '';

// ===== FIXED VAT CALCULATION =====
$vat_rate = 0.18; // Default 18% in decimal form
try {
    $qv = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'vat_rate'");
    $qv->execute();
    $rv = $qv->fetch(PDO::FETCH_ASSOC);
    if ($rv && isset($rv['setting_value']) && $rv['setting_value'] !== '') {
        $db_value = (float)$rv['setting_value'];
        // Normalize: if value > 1 (e.g., 18), convert to decimal (0.18)
        // If value is already decimal (0.18), keep as is
        $vat_rate = ($db_value > 1) ? ($db_value / 100) : $db_value;
    }
} catch (Exception $e) {
    // Keep default 0.18 if database query fails
}
// ===== END FIXED VAT CALCULATION =====

$params = array();
$sqlProducts = "SELECT product_id, product_code, product_name, price, qty FROM products WHERE is_active = 1";
if ($search !== '') {
    $sqlProducts .= " AND (product_name LIKE :q OR product_code LIKE :q OR gen_name LIKE :q)";
    $params[':q'] = '%' . $search . '%';
}
$sqlProducts .= " ORDER BY product_name ASC LIMIT 80";
$pq = $db->prepare($sqlProducts);
$pq->execute($params);

$cq = $db->prepare("SELECT transaction_id, product_id, variant_id, name, qty, unit_price, amount FROM sales_order WHERE invoice = :inv ORDER BY transaction_id DESC");
$cq->execute(array(':inv' => $invoice));

$tot = $db->prepare("SELECT COALESCE(SUM(amount),0) AS subtotal, COALESCE(SUM(profit),0) AS profit FROM sales_order WHERE invoice = :inv");
$tot->execute(array(':inv' => $invoice));
$tr = $tot->fetch(PDO::FETCH_ASSOC);
$subtotal = $tr && isset($tr['subtotal']) ? (float)$tr['subtotal'] : 0;
$totalProfit = $tr && isset($tr['profit']) ? (float)$tr['profit'] : 0;

// ===== FIXED VAT AMOUNT & TOTAL =====
$vat_amount = round($subtotal * $vat_rate, 2);
// Don't double-round the total - just add to avoid rounding discrepancies
$total_amount = $subtotal + $vat_amount;
// ===== END FIXED VAT AMOUNT & TOTAL =====
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>POS - Point of Sale | Cashier Desk</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Facebox (preserved) -->
    <link href="../main/src/facebox.css" media="screen" rel="stylesheet" type="text/css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            overflow-y: auto;
        }

        a,
        a:hover,
        a:focus {
            text-decoration: none;
        }

        /* Main content wrapper */
        .main-content-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            padding: 0 !important;
        }

        /* POS Content */
        .pos-content {
            padding: 1rem 1.5rem;
        }

        /* Header */
        .pos-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .pos-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
        }
        .pos-header h2 i {
            color: #a5b4fc;
        }
        .invoice-badge {
            background: rgba(165,180,252,0.2);
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-size: 0.85rem;
            color: white;
        }

        /* Search Bar */
        .search-container {
            background: white;
            border-radius: 56px;
            padding: 0.3rem;
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .search-container input {
            flex: 1;
            border: none;
            padding: 0.8rem 1.2rem;
            font-size: 1rem;
            border-radius: 56px;
            outline: none;
            font-family: 'Inter', sans-serif;
            direction: ltr;
            text-align: left;
            unicode-bidi: isolate;
        }
        .search-container button {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0 1.8rem;
            border-radius: 56px;
            color: white;
            font-weight: 600;
            transition: all 0.2s;
        }
        .search-container button:hover {
            transform: scale(1.02);
            background: linear-gradient(135deg, #4338ca, #4f46e5);
        }

        /* Product Grid - FAST SCAN */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            max-height: 500px;
            overflow-y: auto;
            padding: 0.25rem;
        }
        .product-card {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px -12px rgba(0,0,0,0.15);
            border-color: #c7d2fe;
        }
        .product-card .product-name {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        .product-card .product-code {
            font-size: 0.65rem;
            color: #64748b;
            font-family: monospace;
        }
        .product-card .stock-badge {
            font-size: 0.65rem;
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            display: inline-block;
            margin: 0.5rem 0;
        }
        .product-card .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #4f46e5;
        }
        .product-card .add-btn {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            background: #4f46e5;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            text-decoration: none;
        }
        .product-card .add-btn:hover {
            background: #4338ca;
            transform: scale(1.1);
        }

        /* Cart Section */
        .cart-section {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            position: sticky;
            top: 1rem;
        }
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
        }
        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-name {
            font-weight: 600;
            font-size: 0.85rem;
        }
        .cart-item-price {
            font-size: 0.7rem;
            color: #64748b;
        }
        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .cart-item-qty span {
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        .qty-btn {
            background: #f1f5f9;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .qty-btn:hover {
            background: #e2e8f0;
        }
        .remove-item {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 0.5rem;
            text-decoration: none;
        }
        .totals {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        .grand-total {
            font-size: 1.2rem;
            font-weight: 800;
            color: #4f46e5;
            border-top: 2px solid #e2e8f0;
            padding-top: 0.75rem;
            margin-top: 0.5rem;
        }
        .change-amount {
            font-size: 1.3rem;
            font-weight: 700;
            color: #059669;
            background: #d1fae5;
            padding: 0.25rem 0.75rem;
            border-radius: 40px;
            display: inline-block;
        }
        .btn-complete {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            padding: 0.8rem;
            border-radius: 16px;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.2s;
        }
        .btn-complete:hover:not(:disabled) {
            transform: translateY(-2px);
        }
        .btn-clear {
            background: #f1f5f9;
            border: none;
            padding: 0.8rem;
            border-radius: 16px;
            font-weight: 500;
            width: 100%;
            margin-top: 0.5rem;
        }

        /* Form inputs */
        .form-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.6rem 1rem;
            width: 100%;
            font-size: 0.85rem;
        }
        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
        }

        /* Alert */
        .alert-modern {
            border-radius: 16px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content-wrapper {
                margin-left: 0;
            }
            .pos-content {
                padding: 1rem;
                padding-top: 70px;
            }
        }
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
        }
    </style>
</head>
<body>

<!-- Include sidebar -->
<?php $activePage = 'new_sale'; include('cashier_sidebar.php'); ?>

<div class="main-content-wrapper">
    <div class="pos-content">
        
        <!-- Header -->
        <div class="pos-header">
            <h2><i class="fas fa-cash-register"></i> Point of Sale</h2>
            <div class="invoice-badge"><i class="fas fa-receipt"></i> Invoice: <?php echo h($invoice); ?></div>
        </div>
        
        <!-- Alerts -->
        <?php if($msg): ?>
        <div class="alert-modern" style="background:#d1fae5; color:#065f46;"><i class="fas fa-check-circle"></i> <?php echo h($msg); ?></div>
        <?php endif; ?>
        <?php if($err): ?>
        <div class="alert-modern" style="background:#fee2e2; color:#991b1b;"><i class="fas fa-exclamation-triangle"></i> <?php echo h($err); ?></div>
        <?php endif; ?>
        
        <div class="row g-3">
            <!-- Products Panel -->
            <div class="col-lg-7">
                <div class="search-container">
                    <form method="get" action="pos.php" style="display: flex; flex:1; gap:0.5rem;" id="searchForm">
                        <input type="hidden" name="invoice" value="<?php echo h($invoice); ?>">
                        <input type="text" name="q" value="<?php echo h($search); ?>" placeholder="🔍 Search product by name or barcode..." autofocus id="searchInput">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
                
                <div class="product-grid" id="productGrid">
                    <?php while($p = $pq->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="product-card" data-product-id="<?php echo $p['product_id']; ?>" data-price="<?php echo $p['price']; ?>" data-name="<?php echo h($p['product_name']); ?>">
                        <div class="product-name"><?php echo h($p['product_name']); ?></div>
                        <div class="product-code"><i class="fas fa-barcode"></i> <?php echo h($p['product_code']); ?></div>
                        <div class="stock-badge"><i class="fas fa-boxes"></i> Stock: <?php echo $p['qty']; ?></div>
                        <div class="price"><?php echo h(formatMoney($p['price'])); ?></div>
                        <a rel="facebox" href="add_to_cart.php?invoice=<?php echo urlencode($invoice); ?>&product_id=<?php echo urlencode($p['product_id']); ?>" class="add-btn"><i class="fas fa-plus"></i></a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Cart Panel -->
            <div class="col-lg-5">
                <div class="cart-section">
                    <div class="cart-header">
                        <strong><i class="fas fa-shopping-cart"></i> Current Sale</strong>
                        <a href="clear_cart.php?invoice=<?php echo urlencode($invoice); ?>" class="text-danger small" onclick="return confirm('Clear entire cart?')"><i class="fas fa-trash"></i> Clear</a>
                    </div>
                    
                    <div class="cart-items" id="cartItems">
                        <?php 
                        $cq->execute(array(':inv' => $invoice));
                        while($c = $cq->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?php echo h($c['name']); ?></div>
                                <div class="cart-item-price"><?php echo h(formatMoney($c['unit_price'])); ?></div>
                            </div>
                            <div class="cart-item-qty">
                                <span><?php echo $c['qty']; ?></span>
                                <a href="delete_cart_item.php?id=<?php echo $c['transaction_id']; ?>&invoice=<?php echo urlencode($invoice); ?>" class="remove-item" onclick="return confirm('Remove item?')"><i class="fas fa-trash-alt"></i></a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="totals">
                        <div class="total-row"><span>Subtotal</span><strong><?php echo h(formatMoney($subtotal)); ?></strong></div>
                        <div class="total-row"><span>VAT (<?php echo $vat_rate*100; ?>%)</span><strong><?php echo h(formatMoney($vat_amount)); ?></strong></div>
                        <div class="total-row grand-total"><span>Total</span><strong><?php echo h(formatMoney($total_amount)); ?></strong></div>
                    </div>
                    
                    <form action="../main/savesales.php" method="post" id="saleForm">
                        <input type="hidden" name="date" value="<?php echo date('m/d/y'); ?>">
                        <input type="hidden" name="invoice" value="<?php echo h($invoice); ?>">
                        <input type="hidden" name="ptype" value="cash">
                        <input type="hidden" name="cashier" value="<?php echo isset($_SESSION['SESS_FIRST_NAME']) ? $_SESSION['SESS_FIRST_NAME'] : ''; ?>">
                        <input type="hidden" name="profit" value="<?php echo $totalProfit; ?>">
                        <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
                        <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
                        <input type="hidden" name="vat_amount" value="<?php echo $vat_amount; ?>">
                        <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                        <div class="mt-3">
                            <label class="small fw-bold"><i class="fas fa-user"></i> Customer Name</label>
                            <input type="text" name="cname" class="form-input" placeholder="Optional">
                        </div>
                        <div class="mt-2">
                            <label class="small fw-bold"><i class="fas fa-phone"></i> Customer Phone</label>
                            <input type="text" name="customer_phone" class="form-input" placeholder="Optional">
                        </div>
                        <div class="mt-2">
                            <label class="small fw-bold"><i class="fas fa-truck"></i> Sale Type</label>
                            <select name="sale_type" class="form-input" id="saleType">
                                <option value="counter">🏪 Counter Sale</option>
                                <option value="delivery">🚚 Delivery</option>
                            </select>
                        </div>
                        <div class="mt-2" id="addressField" style="display:none;">
                            <label class="small fw-bold"><i class="fas fa-location-dot"></i> Delivery Address</label>
                            <textarea name="delivery_address" class="form-input" rows="2" placeholder="Enter delivery address"></textarea>
                        </div>
                        <div class="mt-2">
                            <label class="small fw-bold"><i class="fas fa-money-bill-wave"></i> Amount Paid</label>
                            <input type="number" step="0.01" min="0" name="cash" id="amount_paid" class="form-input" required>
                        </div>
                        <div class="text-center mt-2">
                            <span class="change-amount">Change: <span id="change_value">UGX 0.00</span></span>
                        </div>
                        <button class="btn-complete mt-3" type="submit" onclick="return confirm('Complete sale?');" <?php echo $subtotal<=0 ? 'disabled' : ''; ?>><i class="fas fa-check-circle"></i> Complete Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="../main/lib/jquery.js"></script>
<script src="../main/src/facebox.js"></script>

<script>
    AOS.init({ duration: 300, once: true });
    
    // Facebox
    jQuery(document).ready(function($) {
        $('a[rel*=facebox]').facebox({
            loadingImage: '../main/src/loading.gif',
            closeImage: '../main/src/closelabel.png'
        });
    });
    
    // Change calculation
    const paidInput = document.getElementById('amount_paid');
    const changeSpan = document.getElementById('change_value');
    const totalAmount = <?php echo $total_amount; ?>;
    
    function updateChange() {
        let paid = parseFloat(paidInput.value) || 0;
        let change = paid - totalAmount;
        changeSpan.textContent = 'UGX ' + change.toFixed(2);
        changeSpan.style.color = change >= 0 ? '#059669' : '#dc2626';
    }
    if(paidInput) paidInput.addEventListener('input', updateChange);
    updateChange();
    
    // Toggle delivery address
    const saleType = document.getElementById('saleType');
    const addressField = document.getElementById('addressField');
    function toggleAddress() {
        addressField.style.display = saleType.value === 'delivery' ? 'block' : 'none';
    }
    if(saleType) saleType.addEventListener('change', toggleAddress);
    toggleAddress();

    // Quick add product on card click (event delegation)
    (function() {
        const grid = document.getElementById('productGrid');
        if (!grid) return;
        grid.addEventListener('click', function(e) {
            const addBtn = e.target.closest('.add-btn');
            if (addBtn) return;
            const card = e.target.closest('.product-card');
            if (!card) return;
            const cardAdd = card.querySelector('.add-btn');
            if (cardAdd) window.location.href = cardAdd.href;
        });
    })();

    // AJAX live search (no page reload)
    (function() {
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        const grid = document.getElementById('productGrid');
        if (!searchInput || !searchForm || !grid) return;

        let t;
        let controller = null;

        function refreshFacebox() {
            if (window.jQuery && jQuery.fn && jQuery.fn.facebox) {
                jQuery('a[rel*=facebox]').facebox({
                    loadingImage: '../main/src/loading.gif',
                    closeImage: '../main/src/closelabel.png'
                });
            }
        }

        async function doSearch() {
            const q = searchInput.value || '';
            const params = new URLSearchParams();
            params.set('ajax', 'products');
            params.set('invoice', <?php echo json_encode($invoice); ?>);
            params.set('q', q);

            if (controller) controller.abort();
            controller = new AbortController();

            try {
                const res = await fetch('pos.php?' + params.toString(), { signal: controller.signal });
                if (!res.ok) return;
                const html = await res.text();
                grid.innerHTML = html;
                refreshFacebox();
            } catch (e) {
            }
        }

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            doSearch();
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(t);
            t = setTimeout(doSearch, 120);
        });

        refreshFacebox();
    })();
    
    // Force scroll to top
    if('scrollRestoration' in history) history.scrollRestoration = 'manual';
    window.scrollTo(0,0);
</script>

</body>
</html>