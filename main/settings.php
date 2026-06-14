<?php
// ============================================================
// SYSTEM SETTINGS PAGE - MODERN UI/UX
// Fully responsive with settings management
// Owner only access
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');

function get_setting($db, $key, $default = '') {
    $q = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = :k");
    $q->execute(array(':k' => $key));
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['setting_value'])) {
        return $row['setting_value'];
    }
    return $default;
}

$store_name = get_setting($db, 'store_name', 'POS');
$receipt_line1 = get_setting($db, 'receipt_line1', '');
$receipt_line2 = get_setting($db, 'receipt_line2', '');
$vat_rate = get_setting($db, 'vat_rate', '0.18');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Settings | POS System</title>
    
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

        /* Settings Form Container */
        .settings-container {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 800px;
        }
        .settings-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .settings-header i {
            font-size: 1.5rem;
            color: #4f46e5;
        }
        .settings-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .settings-header p {
            font-size: 0.7rem;
            color: #64748b;
            margin: 0;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 1.5rem;
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
            width: 1.3rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
            background: white;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .form-group input[type="number"] {
            max-width: 200px;
        }
        .form-hint {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }
        .form-hint i {
            margin-right: 0.3rem;
        }

        /* Preview Section */
        .preview-section {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .preview-title {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .receipt-preview {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            font-family: monospace;
            font-size: 0.7rem;
            color: #1e293b;
            border: 1px solid #e2e8f0;
        }
        .receipt-preview .store-name {
            font-weight: 800;
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
        }
        .receipt-preview .line {
            margin: 0.1rem 0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        .btn-save {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-save:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79,70,229,0.3);
        }
        .btn-back {
            background: #f1f5f9;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 16px;
            color: #475569;
            font-weight: 600;
            font-size: 0.9rem;
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

        /* VAT Info Card */
        .vat-info {
            background: #eef2ff;
            border-radius: 16px;
            padding: 0.8rem 1rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .vat-info span {
            font-size: 0.75rem;
            color: #4338ca;
        }
        .vat-info strong {
            font-size: 1rem;
            color: #4f46e5;
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
            .settings-container {
                max-width: 100%;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-save, .btn-back {
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
            .settings-container {
                padding: 1.2rem;
            }
            .form-group input[type="number"] {
                max-width: 100%;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .breadcrumb-modern, .action-buttons {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .settings-container {
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
        <?php $activePage = 'settings'; include('owner_sidebar.php'); ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-cog"></i>
                System Settings
            </h2>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Settings</span>
        </div>
        
        <!-- Alert Messages -->
        <?php if(isset($_GET['msg']) && $_GET['msg']!=''): ?>
        <div class="alert-modern alert-success" data-aos="fade">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Settings Form -->
        <div class="settings-container" data-aos="fade-up" data-aos-delay="100">
            <div class="settings-header">
                <i class="fas fa-sliders-h"></i>
                <div>
                    <h3>Configure System Preferences</h3>
                    <p>Update your store information and system settings</p>
                </div>
            </div>
            
            <form action="save_settings.php" method="post" id="settingsForm">
                <!-- Store Name -->
                <div class="form-group">
                    <label><i class="fas fa-store"></i> Store Name</label>
                    <input type="text" name="store_name" value="<?php echo htmlspecialchars($store_name); ?>" placeholder="Enter your store name" />
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i> This name appears on receipts and invoices
                    </div>
                </div>
                
                <!-- Receipt Line 1 -->
                <div class="form-group">
                    <label><i class="fas fa-receipt"></i> Receipt Line 1</label>
                    <input type="text" name="receipt_line1" value="<?php echo htmlspecialchars($receipt_line1); ?>" placeholder="e.g., Thank you for shopping!" />
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i> Appears at the top of receipts
                    </div>
                </div>
                
                <!-- Receipt Line 2 -->
                <div class="form-group">
                    <label><i class="fas fa-receipt"></i> Receipt Line 2</label>
                    <input type="text" name="receipt_line2" value="<?php echo htmlspecialchars($receipt_line2); ?>" placeholder="e.g., Visit us again!" />
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i> Appears at the bottom of receipts
                    </div>
                </div>
                
                <!-- VAT Rate -->
                <div class="form-group">
                    <label><i class="fas fa-percent"></i> VAT Rate</label>
                    <input type="number" step="0.01" min="0" max="1" name="vat_rate" value="<?php echo htmlspecialchars($vat_rate); ?>" required />
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i> Enter as decimal (e.g., 0.18 for 18%, 0.00 for no VAT)
                    </div>
                </div>
                
                <!-- VAT Info Card -->
                <div class="vat-info">
                    <span><i class="fas fa-calculator"></i> Current VAT Rate:</span>
                    <strong><?php echo (float)$vat_rate * 100; ?>%</strong>
                </div>
                
                <!-- Receipt Preview -->
                <div class="preview-section">
                    <div class="preview-title">
                        <i class="fas fa-eye"></i>
                        Receipt Preview
                    </div>
                    <div class="receipt-preview">
                        <div class="store-name"><?php echo htmlspecialchars($store_name); ?></div>
                        <div class="line"><?php echo htmlspecialchars($receipt_line1); ?></div>
                        <div class="line">--------------------------------</div>
                        <div class="line">Item            Qty     Price</div>
                        <div class="line">Sample Product   1    UGX 1,000</div>
                        <div class="line">--------------------------------</div>
                        <div class="line">Subtotal: UGX 1,000</div>
                        <div class="line">VAT (<?php echo (float)$vat_rate * 100; ?>%): UGX <?php echo number_format(1000 * (float)$vat_rate, 2); ?></div>
                        <div class="line">Total: UGX <?php echo number_format(1000 * (1 + (float)$vat_rate), 2); ?></div>
                        <div class="line">--------------------------------</div>
                        <div class="line"><?php echo htmlspecialchars($receipt_line2); ?></div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn-save" id="submitBtn">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <a href="index.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Real-time receipt preview update
    const storeNameInput = document.querySelector('input[name="store_name"]');
    const receiptLine1Input = document.querySelector('input[name="receipt_line1"]');
    const receiptLine2Input = document.querySelector('input[name="receipt_line2"]');
    const vatRateInput = document.querySelector('input[name="vat_rate"]');
    
    const previewStoreName = document.querySelector('.receipt-preview .store-name');
    const previewLine1 = document.querySelector('.receipt-preview .line:first-of-type');
    const previewLine2 = document.querySelector('.receipt-preview .line:last-of-type');
    const previewVatRate = document.querySelector('.receipt-preview .line:contains("VAT")');
    
    function updateReceiptPreview() {
        if (previewStoreName) {
            previewStoreName.textContent = storeNameInput.value || 'POS Store';
        }
        if (previewLine1 && receiptLine1Input) {
            const lines = document.querySelectorAll('.receipt-preview .line');
            if (lines[0]) lines[0].textContent = receiptLine1Input.value || '';
        }
        if (previewLine2 && receiptLine2Input) {
            const lines = document.querySelectorAll('.receipt-preview .line');
            const lastLine = lines[lines.length - 2];
            if (lastLine) lastLine.textContent = receiptLine2Input.value || '';
        }
        if (vatRateInput) {
            const vatPercent = (parseFloat(vatRateInput.value) || 0) * 100;
            const vatAmount = 1000 * (parseFloat(vatRateInput.value) || 0);
            const totalAmount = 1000 * (1 + (parseFloat(vatRateInput.value) || 0));
            
            const lines = document.querySelectorAll('.receipt-preview .line');
            for (let i = 0; i < lines.length; i++) {
                if (lines[i].textContent.includes('VAT (')) {
                    lines[i].textContent = `VAT (${vatPercent.toFixed(0)}%): UGX ${vatAmount.toFixed(2)}`;
                }
                if (lines[i].textContent.includes('Total:')) {
                    lines[i].textContent = `Total: UGX ${totalAmount.toFixed(2)}`;
                }
            }
            
            // Update VAT info card
            const vatInfoStrong = document.querySelector('.vat-info strong');
            if (vatInfoStrong) {
                vatInfoStrong.textContent = `${vatPercent.toFixed(0)}%`;
            }
        }
    }
    
    if (storeNameInput) storeNameInput.addEventListener('input', updateReceiptPreview);
    if (receiptLine1Input) receiptLine1Input.addEventListener('input', updateReceiptPreview);
    if (receiptLine2Input) receiptLine2Input.addEventListener('input', updateReceiptPreview);
    if (vatRateInput) vatRateInput.addEventListener('input', updateReceiptPreview);
    
    // Form submission with loading state
    $('#settingsForm').on('submit', function() {
        $('#submitBtn').html('<i class="fas fa-spinner fa-pulse"></i> Saving...').prop('disabled', true);
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