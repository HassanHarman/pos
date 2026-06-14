<?php
// ============================================================
// BACKUP & EXPORT PAGE - MODERN UI/UX
// Fully responsive with CSV exports and backup logging
// Owner only access
// Preserves ALL original functionality
// ============================================================

require_once('auth.php');
require_role(array('owner'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

$logs = $db->prepare("SELECT * FROM backup_logs ORDER BY backup_id DESC LIMIT 50");
$logs->execute();

// Get backup statistics
$logCount = $db->prepare("SELECT COUNT(*) as count FROM backup_logs");
$logCount->execute();
$totalBackups = $logCount->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Backup & Export | POS System</title>
    
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
        .stat-icon.backup {
            background: #d1fae5;
        }
        .stat-icon.backup i {
            color: #059669;
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

        /* Section Styles */
        .section-card {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .section-header i {
            font-size: 1.2rem;
            color: #4f46e5;
        }
        .section-header h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        /* Export Buttons Grid */
        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.8rem;
        }
        .export-btn {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.8rem;
            border-radius: 16px;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: center;
        }
        .export-btn:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            color: white;
        }

        /* Form Styles */
        .backup-form {
            margin-top: 0.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.75rem;
            color: #334155;
            margin-bottom: 0.4rem;
        }
        .form-group label i {
            margin-right: 0.4rem;
            color: #4f46e5;
            width: 1.2rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-submit {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #047857, #059669);
            transform: translateY(-2px);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        .backup-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 600px;
        }
        .backup-table thead th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .backup-table tbody td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .backup-table tbody tr:hover {
            background: #fafbff;
        }

        /* Status Badges */
        .status-success {
            background: #d1fae5;
            color: #059669;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-block;
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
            .export-grid {
                grid-template-columns: repeat(2, 1fr);
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
            .export-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Print Styles */
        @media print {
            .app-sidebar, .mobile-menu-toggle, .sidebar-overlay, .stats-grid, .breadcrumb-modern, .export-grid, .btn-submit {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .section-card {
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
        <?php $activePage = 'backup_export'; include('owner_sidebar.php'); ?>
    </div>
    
    <!-- Main Content -->
    <div class="app-content">
        
        <!-- Page Header -->
        <div class="page-header-modern" data-aos="fade-down">
            <h2>
                <i class="fas fa-database"></i>
                Backup & Export
            </h2>
        </div>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-modern">
            <a href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-dark fw-semibold">Backup & Export</span>
        </div>
        
        <!-- Alert Messages -->
        <?php if(isset($_GET['msg']) && $_GET['msg']!=''): ?>
        <div class="alert-modern alert-success" data-aos="fade">
            <i class="fas fa-check-circle"></i>
            <?php echo h($_GET['msg']); ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['err']) && $_GET['err']!=''): ?>
        <div class="alert-modern alert-danger" data-aos="fade">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo h($_GET['err']); ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-csv"></i></div>
                <div class="stat-info">
                    <h4>5</h4>
                    <p>Export Types</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon backup"><i class="fas fa-archive"></i></div>
                <div class="stat-info">
                    <h4><?php echo $totalBackups; ?></h4>
                    <p>Backup Records</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h4>Manual</h4>
                    <p>Backup Type</p>
                </div>
            </div>
        </div>
        
        <!-- CSV Exports Section -->
        <div class="section-card" data-aos="fade-up" data-aos-delay="150">
            <div class="section-header">
                <i class="fas fa-file-csv"></i>
                <h3>CSV Exports</h3>
            </div>
            <div class="export-grid">
                <a href="export_csv.php?type=sales" target="_blank" class="export-btn">
                    <i class="fas fa-chart-line"></i> Sales CSV
                </a>
                <a href="export_csv.php?type=sales_lines" target="_blank" class="export-btn">
                    <i class="fas fa-list"></i> Sales Lines CSV
                </a>
                <a href="export_csv.php?type=products" target="_blank" class="export-btn">
                    <i class="fas fa-box"></i> Products CSV
                </a>
                <a href="export_csv.php?type=variants" target="_blank" class="export-btn">
                    <i class="fas fa-layer-group"></i> Variants CSV
                </a>
                <a href="export_csv.php?type=stock_movements" target="_blank" class="export-btn">
                    <i class="fas fa-exchange-alt"></i> Stock Movements CSV
                </a>
            </div>
        </div>
        
        <!-- Manual Backup Log Section -->
        <div class="section-card" data-aos="fade-up" data-aos-delay="200">
            <div class="section-header">
                <i class="fas fa-plus-circle"></i>
                <h3>Add Manual Backup Log</h3>
            </div>
            <form action="save_backup_log.php" method="post" class="backup-form">
                <div class="form-group">
                    <label><i class="fas fa-file"></i> Backup File (Optional)</label>
                    <input type="text" name="backup_file" placeholder="e.g., sales_backup_2026-04-05.sql">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Notes</label>
                    <input type="text" name="notes" placeholder="e.g., Exported from phpMyAdmin, manual backup before update">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Add Backup Log
                </button>
            </form>
        </div>
        
        <!-- Recent Backups Section -->
        <div class="section-card" data-aos="fade-up" data-aos-delay="250">
            <div class="section-header">
                <i class="fas fa-history"></i>
                <h3>Recent Backups</h3>
            </div>
            <div class="table-container">
                <table class="backup-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-tag"></i> Type</th>
                            <th><i class="fas fa-file"></i> File</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                            <th><i class="fas fa-comment"></i> Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasResults = false;
                        while($row = $logs->fetch(PDO::FETCH_ASSOC)): 
                            $hasResults = true;
                        ?>
                        <tr class="record">
                            <td><?php echo date('M d, Y H:i', strtotime($row['backup_date'])); ?></td>
                            <td><?php echo ucfirst(h($row['backup_type'])); ?></td>
                            <td><?php echo h($row['backup_file']) ?: '—'; ?></td>
                            <td><span class="status-success"><i class="fas fa-check-circle"></i> <?php echo ucfirst(h($row['status'])); ?></span></td>
                            <td><?php echo h($row['notes']) ?: '—'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(!$hasResults): ?>
                        <tr class="no-data">
                            <td colspan="5">
                                <i class="fas fa-archive"></i>
                                <p>No backup records found. Add your first backup log above.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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