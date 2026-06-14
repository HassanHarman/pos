<?php
// ============================================================
// RECEIVING (STOCK IN) PAGE - MODERN UI/UX
// Fully responsive for receiving stock from suppliers
// PRESERVES ALL ORIGINAL FUNCTIONALITY - NO EXTRA QUERIES
// ============================================================

require_once('auth.php');
require_role(array('owner','manager'));
include('../connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Receiving | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Select2 for better dropdowns -->
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

        /* Receiving Form Container */
        .form-container {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 800px;
        }
        .form-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.2rem;
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
            width: 1.2rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .btn-save {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .btn-save:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79,70,229,0.3);
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
            .form-container {
                max-width: 100%;
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
            .form-container {
                padding: 1.2rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .breadcrumb-modern, .btn-save {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .form-container {
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
                $activePage = 'receiving';
            }
            include(__DIR__ . '/../stock/stock_sidebar.php');
        } elseif ($role === 'owner') {
            $activePage = 'receiving';
            include(__DIR__ . '/owner_sidebar.php');
        } else {
            $activePage = 'receiving';
            include(__DIR__ . '/manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-download"></i>
                Stock Receiving
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
            <span class="text-dark fw-semibold">Receiving</span>
        </div>
        
        <!-- Alert Messages -->
        <?php if(isset($_GET['msg']) && $_GET['msg']!=''): ?>
        <div class="alert-modern alert-success" data-aos="fade">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['err']) && $_GET['err']!=''): ?>
        <div class="alert-modern alert-danger" data-aos="fade">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($_GET['err']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Receiving Form - PRESERVED ORIGINAL STRUCTURE -->
        <div class="form-container" data-aos="fade-up" data-aos-delay="200">
            <div class="form-title">
                <i class="fas fa-clipboard-list"></i>
                Receive Stock from Supplier
            </div>
            
            <form action="savereceiving.php" method="post" id="receivingForm">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Product</label>
                    <select name="product_id" id="product_id" class="product-select" style="width:100%;" required>
                        <option value="">-- select product --</option>
                        <?php
                        $p = $db->prepare("SELECT product_id, product_code, product_name FROM products WHERE is_active = 1 ORDER BY product_name ASC");
                        $p->execute();
                        while($row = $p->fetch()):
                        ?>
                        <option value="<?php echo $row['product_id']; ?>">
                            <?php echo htmlspecialchars($row['product_code']); ?> - <?php echo htmlspecialchars($row['product_name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Variant (optional)</label>
                    <select name="variant_id" id="variant_id" style="width:100%;">
                        <option value="">-- base product stock --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calculator"></i> Quantity Received</label>
                    <input type="number" step="0.01" min="0" name="qty" id="qty" placeholder="Enter quantity received" required />
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Notes (optional)</label>
                    <input type="text" name="notes" placeholder="Additional notes about this receiving" />
                </div>
                
                <button class="btn-save" type="submit" id="submitBtn">
                    <i class="fas fa-save"></i> Save Receiving
                </button>
            </form>
        </div>
        
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
    
    // Initialize Select2 for better dropdowns (optional - doesn't affect functionality)
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.product-select').select2({
                placeholder: "Search for a product...",
                allowClear: true,
                width: '100%'
            });
        }
    });
    
    // Load variants based on selected product - PRESERVED ORIGINAL FUNCTIONALITY
    $(function(){
        $('#product_id').on('change', function(){
            var pid = $(this).val();
            if (!pid) {
                $('#variant_id').html('<option value="">-- base product stock --</option>');
                return;
            }
            $('#variant_id').load('get_variants.php?product_id=' + encodeURIComponent(pid), function(){
                $('#variant_id').prepend('<option value="">-- base product stock --</option>');
            });
        });
    });
    
    // Form submission with loading state
    $('#receivingForm').on('submit', function() {
        var product = $('#product_id').val();
        var qty = $('#qty').val();
        
        if (!product) {
            alert('Please select a product');
            return false;
        }
        if (!qty || qty <= 0) {
            alert('Please enter a valid quantity');
            return false;
        }
        
        $('#submitBtn').html('<i class="fas fa-spinner fa-pulse"></i> Processing...').prop('disabled', true);
        return true;
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