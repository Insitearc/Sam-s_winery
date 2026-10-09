<?php
/**
 * Sam's Fruit Wines - Admin Shell Header & Navigation
 */
if (!defined('ADMIN_PANEL')) {
    define('ADMIN_PANEL', true);
}
require_once __DIR__ . '/auth.php';

if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
if (!isset($activeNav)) {
    $activeNav = 'dashboard';
}

$adminName = htmlspecialchars($admin['name'] ?? $admin['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Sam's Fruit Wines Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin Stylesheet -->
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
<div class="admin-shell">
    <!-- MOBILE SIDEBAR BACKDROP -->
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

    <!-- STICKY & SCROLLABLE SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                <img src="../assets/images/logo.png" alt="Sam's Fruit Wines" onerror="this.style.display='none'">
                <div class="sidebar-brand-text">
                    <h2>Sam's Fruit Wines</h2>
                    <span>Executive Admin</span>
                </div>
            </div>
            <button type="button" class="admin-sidebar-close" id="adminSidebarClose" aria-label="Close menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="sidebar-menu">
            <div class="sidebar-heading">Navigation</div>
            <a href="dashboard.php" class="sidebar-link <?php echo ($activeNav === 'dashboard') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
            <a href="orders.php" class="sidebar-link <?php echo ($activeNav === 'orders') ? 'active' : ''; ?>">
                <i class="fa-solid fa-box"></i>
                <span>Orders</span>
            </a>
            <a href="free-wine.php" class="sidebar-link <?php echo ($activeNav === 'free-wine') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gift"></i>
                <span>Free Wine / Gift</span>
            </a>
            <a href="users.php" class="sidebar-link <?php echo ($activeNav === 'users') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>
            <a href="products.php" class="sidebar-link <?php echo ($activeNav === 'products') ? 'active' : ''; ?>">
                <i class="fa-solid fa-wine-bottle"></i>
                <span>Products</span>
            </a>
            <a href="shops.php" class="sidebar-link <?php echo ($activeNav === 'shops') ? 'active' : ''; ?>">
                <i class="fa-solid fa-store"></i>
                <span>Shops</span>
            </a>

            <div class="sidebar-heading">Administration</div>
            <a href="profile.php" class="sidebar-link <?php echo ($activeNav === 'profile') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-gear"></i>
                <span>Admin Profile</span>
            </a>
            <a href="change-password.php" class="sidebar-link <?php echo ($activeNav === 'password') ? 'active' : ''; ?>">
                <i class="fa-solid fa-key"></i>
                <span>Change Password</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-logout" onclick="return confirm('Are you sure you want to log out of the Admin Panel?');">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="admin-main">
        <!-- STICKY HEADER -->
        <header class="admin-header">
            <div class="admin-header-left">
                <button type="button" class="admin-hamburger-btn" id="adminHamburgerBtn" aria-label="Open menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="admin-header-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
            </div>

            <div class="admin-header-right">
                <a href="../index.html" target="_blank" class="admin-pill-link" title="Open storefront in new tab">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>View Storefront</span>
                </a>

                <div class="admin-avatar-wrap">
                    <div class="admin-avatar"><?php echo $adminInitial; ?></div>
                    <span class="admin-meta"><?php echo $adminName; ?></span>
                </div>

                <a href="logout.php" class="btn-outline" style="padding: 6px 10px;" title="Logout">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </header>

        <!-- SCROLLABLE BODY STARTS -->
        <div class="admin-body">
