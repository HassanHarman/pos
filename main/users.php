<?php
// ============================================================
// USERS MANAGEMENT PAGE - MODERN UI/UX
// Fully responsive with user management
// Owner only access
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');

// Get user statistics
$statsQuery = $db->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users,
        SUM(CASE WHEN position = 'owner' THEN 1 ELSE 0 END) as owners,
        SUM(CASE WHEN position = 'manager' THEN 1 ELSE 0 END) as managers,
        SUM(CASE WHEN position = 'cashier' THEN 1 ELSE 0 END) as cashiers
    FROM user
");
$statsQuery->execute();
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Users | POS System</title>
    
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
        .stat-icon.active {
            background: #d1fae5;
        }
        .stat-icon.active i {
            color: #059669;
        }
        .stat-icon.inactive {
            background: #fee2e2;
        }
        .stat-icon.inactive i {
            color: #dc2626;
        }
        .stat-icon.role {
            background: #fef3c7;
        }
        .stat-icon.role i {
            color: #d97706;
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

        /* Search Bar */
        .search-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
            max-width: 350px;
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
            padding: 0.65rem 1rem 0.65rem 2.3rem;
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

        /* Users Table Container */
        .table-container {
            background: white;
            border-radius: 24px;
            padding: 1rem;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            min-width: 600px;
        }
        .users-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.75rem;
        }
        .users-table tbody td {
            padding: 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .users-table tbody tr:hover {
            background: #fafbff;
        }

        /* Role Badges */
        .role-owner {
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .role-manager {
            background: #fef3c7;
            color: #d97706;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        .role-cashier {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }

        /* Status Badges */
        .status-active {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Toggle Button */
        .btn-toggle {
            background: #fef3c7;
            border: none;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            color: #d97706;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-toggle:hover {
            background: #fde68a;
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
            .search-wrapper {
                max-width: 100%;
                width: 100%;
            }
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle, .sidebar-overlay {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                gap: 0.8rem;
            }
            .stat-card {
                padding: 0.8rem;
            }
            .stat-info h4 {
                font-size: 1rem;
            }
            .page-header-modern h2 {
                font-size: 1rem;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .action-bar, .search-wrapper, .btn-toggle {
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
        <?php $activePage = 'users'; include('owner_sidebar.php'); ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-users-cog"></i>
                User Management
            </h2>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Users</span>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['total_users']; ?></h4>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['active_users']; ?></h4>
                    <p>Active</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon inactive">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['inactive_users']; ?></h4>
                    <p>Inactive</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon role">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['owners']; ?> / <?php echo $stats['managers']; ?> / <?php echo $stats['cashiers']; ?></h4>
                    <p>Owner / Manager / Cashier</p>
                </div>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div class="action-bar" data-aos="fade-up" data-aos-delay="150">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a rel="facebox" href="adduser.php" class="btn-add-modern">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>
        
        <!-- Search Bar -->
        <div class="search-wrapper" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-search"></i>
            <input type="text" id="filter" placeholder="Search by username, name, or role..." autocomplete="off">
        </div>
        
        <!-- Users Table -->
        <div class="table-container" data-aos="fade-up" data-aos-delay="250">
            <table class="users-table" id="resultTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-user me-1"></i> Username</th>
                        <th><i class="fas fa-signature me-1"></i> Name</th>
                        <th><i class="fas fa-briefcase me-1"></i> Role</th>
                        <th><i class="fas fa-power-off me-1"></i> Status</th>
                        <th><i class="fas fa-cog me-1"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $db->prepare("SELECT id, username, name, position, is_active FROM user ORDER BY id DESC");
                    $result->execute();
                    $hasResults = false;
                    while($row = $result->fetch()):
                        $hasResults = true;
                        $roleClass = '';
                        $roleIcon = '';
                        switch($row['position']) {
                            case 'owner':
                                $roleClass = 'role-owner';
                                $roleIcon = 'fa-crown';
                                break;
                            case 'manager':
                                $roleClass = 'role-manager';
                                $roleIcon = 'fa-user-tie';
                                break;
                            case 'cashier':
                                $roleClass = 'role-cashier';
                                $roleIcon = 'fa-cash-register';
                                break;
                            default:
                                $roleClass = 'role-owner';
                                $roleIcon = 'fa-user';
                        }
                        $isActive = (int)$row['is_active'] === 1;
                    ?>
                    <tr class="record">
                        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><span class="<?php echo $roleClass; ?>">
                            <i class="fas <?php echo $roleIcon; ?> me-1"></i> <?php echo ucfirst($row['position']); ?>
                        </span></td>
                        <td>
                            <?php if($isActive): ?>
                                <span class="status-active">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Active
                                </span>
                            <?php else: ?>
                                <span class="status-inactive">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="toggleuser.php?id=<?php echo $row['id']; ?>" class="btn-toggle" title="Toggle User Status" onclick="return confirm('Change user active status?')">
                                <i class="fas fa-sync-alt"></i> Toggle
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasResults): ?>
                    <tr class="no-data">
                        <td colspan="5">
                            <i class="fas fa-users"></i>
                            <p>No users found.</p>
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
            
            // Show/hide no results message
            if ($("#resultTable tbody tr:visible").length === 0 && !hasResults) {
                if ($("#noResultsMsg").length === 0) {
                    $("#resultTable tbody").append('<tr id="noResultsMsg" class="no-data"><td colspan="5"><i class="fas fa-search"></i><p>No users match your search.</p></td></tr>');
                }
            } else {
                $("#noResultsMsg").remove();
            }
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