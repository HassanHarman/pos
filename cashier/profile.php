<?php
// ============================================================
// MY PROFILE - MODERN UI/UX
// PRESERVES ALL ORIGINAL PHP FUNCTIONALITY
// INTEGRATES WITH RESPONSIVE SIDEBAR
// ============================================================

require_once('../main/auth.php');
require_role(array('cashier'));
include('../connect.php');

function h($v){ return htmlspecialchars((string)$v); }

$userId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$err = isset($_GET['err']) ? $_GET['err'] : '';

$q = $db->prepare("SELECT id, username, name, position FROM user WHERE id = :id LIMIT 1");
$q->execute(array(':id' => $userId));
$u = $q->fetch(PDO::FETCH_ASSOC);
if (!$u) {
    header('location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>My Profile | POS System</title>
    
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
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            overflow-y: auto;
        }

        /* Main content wrapper - starts at top */
        .main-content-wrapper {
            margin-left: 280px;
            min-height: 100vh;
            padding: 0 !important;
            margin-top: 0 !important;
        }

        /* Profile Content */
        .profile-content {
            padding: 1.5rem 2rem;
            max-width: 100%;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 24px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(165,180,252,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .profile-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .profile-header h1 i {
            color: #a5b4fc;
            font-size: 2rem;
        }
        .profile-header .breadcrumb-modern {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        .profile-header .breadcrumb-modern a {
            color: #a5b4fc;
            text-decoration: none;
        }
        .profile-header .breadcrumb-modern span {
            color: #94a3b8;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .profile-card-header {
            background: #f8fafc;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .profile-card-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .profile-card-header h3 i {
            color: #4f46e5;
        }

        .profile-info-table {
            width: 100%;
        }
        .profile-info-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .profile-info-table tr:last-child {
            border-bottom: none;
        }
        .profile-info-table th {
            width: 200px;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #475569;
            background: #fafbfc;
            font-size: 0.85rem;
        }
        .profile-info-table td {
            padding: 1rem 1.5rem;
            color: #1e293b;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Change Password Section */
        .password-section {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .password-form {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .form-group label i {
            margin-right: 0.5rem;
            color: #4f46e5;
            width: 1.2rem;
        }
        .form-group input {
            width: 100%;
            max-width: 350px;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.9rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-update {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 14px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-update:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-back {
            background: #f1f5f9;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 14px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            margin-left: 0.5rem;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* Alert Messages */
        .alert-modern {
            border-radius: 16px;
            border: none;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
            .main-content-wrapper {
                margin-left: 0;
            }
            .profile-content {
                padding: 1rem;
                padding-top: 70px;
            }
            .profile-info-table th {
                width: 120px;
                padding: 0.8rem 1rem;
            }
            .profile-info-table td {
                padding: 0.8rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .profile-header h1 {
                font-size: 1.3rem;
            }
            .profile-info-table th,
            .profile-info-table td {
                display: block;
                width: 100%;
            }
            .profile-info-table th {
                background: transparent;
                padding-bottom: 0;
            }
            .form-group input {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Include the modern sidebar -->
<?php 
$activePage = 'profile'; 
include('cashier_sidebar.php'); 
?>

<!-- Main Content Wrapper -->
<div class="main-content-wrapper">
    <div class="profile-content">
        
        <!-- Profile Header -->
        <div class="profile-header" data-aos="fade-down">
            <h1>
                <i class="fas fa-user-circle"></i>
                My Profile
            </h1>
            <div class="breadcrumb-modern">
                <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                <span class="mx-2">/</span>
                <span style="color: #cbd5e1;">My Profile</span>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <?php if($msg != ''): ?>
        <div class="alert-modern alert-success" data-aos="fade">
            <i class="fas fa-check-circle"></i>
            <?php echo h($msg); ?>
        </div>
        <?php endif; ?>
        
        <?php if($err != ''): ?>
        <div class="alert-modern alert-danger" data-aos="fade">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo h($err); ?>
        </div>
        <?php endif; ?>
        
        <!-- Profile Information Card -->
        <div class="profile-card" data-aos="fade-up" data-aos-delay="100">
            <div class="profile-card-header">
                <h3>
                    <i class="fas fa-id-card"></i>
                    Personal Information
                </h3>
            </div>
            <table class="profile-info-table">
                <tr>
                    <th><i class="fas fa-user me-2"></i> Username</th>
                    <td><?php echo h($u['username']); ?></td>
                </tr>
                <tr>
                    <th><i class="fas fa-signature me-2"></i> Name</th>
                    <td><?php echo h($u['name']); ?></td>
                </tr>
                <tr>
                    <th><i class="fas fa-briefcase me-2"></i> Role</th>
                    <td>
                        <span style="background: #eef2ff; color: #4f46e5; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                            <i class="fas fa-cash-register me-1"></i> <?php echo h($u['position']); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Change Password Section -->
        <div class="password-section" data-aos="fade-up" data-aos-delay="200">
            <div class="profile-card-header">
                <h3>
                    <i class="fas fa-key"></i>
                    Change Password
                </h3>
            </div>
            <div class="password-form">
                <form action="profile_save.php" method="post">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter your current password" required />
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" required />
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required />
                    </div>
                    <div class="form-group">
                        <button class="btn-update" type="submit">
                            <i class="fas fa-save"></i> Update Password
                        </button>
                        <a class="btn-back" href="index.php">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS animations
    AOS.init({
        duration: 500,
        once: true,
        offset: 10
    });
    
    // Mobile sidebar functionality
    const sidebar = document.getElementById('modernSidebar');
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
        mobileToggle.addEventListener('click', function() {
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
    
    // Close sidebar when clicking a link on mobile
    const sidebarLinks = document.querySelectorAll('.sidebar-nav-modern a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile()) {
                setTimeout(closeMobileSidebar, 150);
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Force page to start at top
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
</script>

</body>
</html>