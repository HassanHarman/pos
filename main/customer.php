<?php
// ============================================================
// CUSTOMER MANAGEMENT PAGE - WITH STYLED ADD CUSTOMER MODAL
// Fully responsive with modern Facebox styling
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
$finalcode = 'RS-' . createRandomPassword();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Customers | POS System</title>
    
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
        .customer-count-badge {
            background: rgba(165, 180, 252, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .customer-count-badge i {
            margin-right: 0.5rem;
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
        .stat-card-mini {
            background: white;
            border-radius: 16px;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .stat-icon {
            width: 42px;
            height: 42px;
            background: #eef2ff;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 1.2rem;
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

        /* Search and Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .search-wrapper {
            flex: 1;
            max-width: 320px;
            position: relative;
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
            padding: 0.6rem 1rem 0.6rem 2.3rem;
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
        .btn-add-modern {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.6rem 1.2rem;
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

        /* Customers Table Container */
        .customers-table-container {
            background: white;
            border-radius: 20px;
            padding: 0.8rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .customers-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 600px;
        }
        .customers-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .customers-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .customers-table tbody tr:hover {
            background: #fafbff;
        }
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
        .btn-delete {
            background: #fee2e2;
            border: none;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            color: #dc2626;
            font-size: 0.65rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-left: 0.3rem;
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

        /* Alert */
        .alert-modern {
            border-radius: 14px;
            padding: 0.7rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
        }

        /* ============================================
           FACEBOX MODAL CUSTOM STYLES
           Modern, responsive popup for Add/Edit Customer
        ============================================ */
        #facebox .popup {
            border-radius: 24px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
            background: transparent !important;
            max-width: 500px !important;
            width: 90% !important;
        }
        #facebox .content {
            background: white !important;
            border-radius: 24px !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        #facebox .popup .close {
            position: absolute !important;
            top: 16px !important;
            right: 16px !important;
            background: rgba(0,0,0,0.1) !important;
            border-radius: 50% !important;
            width: 32px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.2s !important;
            z-index: 10 !important;
        }
        #facebox .popup .close:hover {
            background: rgba(0,0,0,0.2) !important;
            transform: scale(1.05) !important;
        }
        #facebox .popup .close img {
            display: none !important;
        }
        #facebox .popup .close:after {
            content: '\f00d' !important;
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
            font-size: 1rem !important;
            color: #475569 !important;
        }
        
        /* Modal inner form styling */
        .facebox-modal-form {
            padding: 1.8rem;
        }
        .facebox-modal-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 1.2rem 1.8rem;
            margin: 0;
            color: white;
        }
        .facebox-modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .facebox-modal-header h3 i {
            color: #a5b4fc;
        }
        .facebox-form-group {
            margin-bottom: 1rem;
        }
        .facebox-form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.75rem;
            color: #334155;
            margin-bottom: 0.4rem;
        }
        .facebox-form-group label i {
            margin-right: 0.4rem;
            color: #4f46e5;
            width: 1.2rem;
        }
        .facebox-form-group input,
        .facebox-form-group textarea,
        .facebox-form-group select {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .facebox-form-group input:focus,
        .facebox-form-group textarea:focus,
        .facebox-form-group select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .facebox-form-group textarea {
            resize: vertical;
            min-height: 70px;
        }
        .facebox-btn-submit {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .facebox-btn-submit:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
        }
        .facebox-btn-cancel {
            background: #f1f5f9;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 14px;
            color: #475569;
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .facebox-btn-cancel:hover {
            background: #e2e8f0;
        }
        .facebox-row {
            display: flex;
            gap: 0.8rem;
        }
        .facebox-row .facebox-form-group {
            flex: 1;
        }
        
        /* Responsive modal */
        @media (max-width: 600px) {
            #facebox .popup {
                width: 95% !important;
                margin: 1rem !important;
            }
            .facebox-modal-form {
                padding: 1.2rem;
            }
            .facebox-row {
                flex-direction: column;
                gap: 0;
            }
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
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle, .sidebar-overlay {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
            }
            .action-bar {
                flex-direction: column;
            }
            .search-wrapper {
                max-width: 100%;
                width: 100%;
            }
            .btn-add-modern {
                width: 100%;
                justify-content: center;
            }
            .page-header-modern h2 {
                font-size: 1.1rem;
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
        if ($role === 'owner') {
            $activePage = 'customers';
            include('owner_sidebar.php');
        } else {
            $activePage = 'customers';
            include('manager_sidebar.php');
        }
        ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-users"></i>
                Customer Management
            </h2>
            <div class="customer-count-badge">
                <i class="fas fa-chart-line"></i>
                Total: <strong id="totalCount">0</strong>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Customers</span>
        </div>
        
        <?php
        include('../connect.php');
        
        // Display any messages from session
        if (isset($_SESSION['msg']) && $_SESSION['msg'] != '') {
            echo '<div class="alert-modern" style="background:#d1fae5; color:#065f46;"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['msg']) . '</div>';
            unset($_SESSION['msg']);
        }
        if (isset($_SESSION['err']) && $_SESSION['err'] != '') {
            echo '<div class="alert-modern" style="background:#fee2e2; color:#991b1b;"><i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['err']) . '</div>';
            unset($_SESSION['err']);
        }
        
        // Get total customers
        $totalResult = $db->prepare("SELECT COUNT(*) as total FROM customer");
        $totalResult->execute();
        $totalCustomers = $totalResult->fetch(PDO::FETCH_ASSOC)['total'];
        ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card-mini">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h4 id="totalCustomersCount"><?php echo $totalCustomers; ?></h4>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="stat-card-mini">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h4>Active</h4>
                    <p>Customer Accounts</p>
                </div>
            </div>
            <div class="stat-card-mini">
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                <div class="stat-info">
                    <h4><?php echo date('M Y'); ?></h4>
                    <p>Current Period</p>
                </div>
            </div>
        </div>
        
        <!-- Search and Action Bar -->
        <div class="action-bar" data-aos="fade-up" data-aos-delay="150">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="filter" placeholder="Search by name, contact, product, or note..." autocomplete="off">
            </div>
            <a rel="facebox" href="addcustomer.php" class="btn-add-modern">
                <i class="fas fa-plus-circle"></i> Add New Customer
            </a>
        </div>
        
        <!-- Customers Table -->
        <div class="customers-table-container" data-aos="fade-up" data-aos-delay="200">
            <table class="customers-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-user me-1"></i> Full Name</th>
                        <th><i class="fas fa-map-marker-alt me-1"></i> Address</th>
                        <th><i class="fas fa-phone me-1"></i> Contact</th>
                        <th><i class="fas fa-box me-1"></i> Product</th>
                        <th><i class="fas fa-money-bill me-1"></i> Total</th>
                        <th><i class="fas fa-sticky-note me-1"></i> Note</th>
                        <th><i class="fas fa-calendar-alt me-1"></i> Due Date</th>
                        <th><i class="fas fa-cog me-1"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $db->prepare("SELECT * FROM customer ORDER BY customer_id DESC");
                    $result->execute();
                    while($row = $result->fetch()):
                    ?>
                    <tr class="record">
                        <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['prod_name']); ?></td>
                        <td class="fw-bold" style="color:#4f46e5;">₦<?php echo number_format($row['membership_number'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['note']); ?></td>
                        <td><?php echo $row['expected_date'] ? date('M d, Y', strtotime($row['expected_date'])) : '—'; ?></td>
                        <td>
                            <a rel="facebox" href="editcustomer.php?id=<?php echo $row['customer_id']; ?>" class="btn-edit" title="Edit Customer">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="#" id="<?php echo $row['customer_id']; ?>" class="btn-delete delbutton" title="Delete Customer">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- No Results Message -->
            <div id="noResults" class="no-results" style="display: none;">
                <i class="fas fa-user-slash"></i>
                <p>No customers found matching your search.</p>
            </div>
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
    
    // Facebox initialization with custom styling
    jQuery(document).ready(function($) {
        $('a[rel*=facebox]').facebox({
            loadingImage: 'src/loading.gif',
            closeImage: 'src/closelabel.png'
        });
        
        // Add custom class to facebox content for styling
        $(document).bind('beforeReveal.facebox', function() {
            $('#facebox .content').addClass('facebox-custom-content');
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
            $("#noResults").toggle(!hasResults);
            
            // Update count based on visible rows
            var visibleCount = $("#resultTable tbody tr:visible").length;
            $("#totalCustomersCount").text(visibleCount);
            $(".customer-count-badge strong").text(visibleCount);
        });
    });
    
    // Delete customer with confirmation
    $(".delbutton").click(function() {
        var element = $(this);
        var del_id = element.attr("id");
        var info = 'id=' + del_id;
        
        if(confirm("Are you sure you want to delete this customer? This action cannot be undone!")) {
            $.ajax({
                type: "GET",
                url: "deletecustomer.php",
                data: info,
                success: function() {
                    element.parents(".record").fadeOut('slow', function() {
                        $(this).remove();
                        var newCount = $("#resultTable tbody tr:visible").length;
                        $("#totalCustomersCount").text(newCount);
                        $(".customer-count-badge strong").text(newCount);
                    });
                }
            });
        }
        return false;
    });
    
    // Update total count on page load
    $(document).ready(function() {
        var total = $("#resultTable tbody tr").length;
        $("#totalCustomersCount").text(total);
        $(".customer-count-badge strong").text(total);
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