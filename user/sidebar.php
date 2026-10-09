<?php
/**
 * Sam's Fruit Wines - User Dashboard Shared Sidebar Component
 * Supports sticky desktop display and smooth off-canvas mobile drawer with hamburger toggle.
 */

$activeNav = $activeNav ?? 'dashboard';
$userInitials = strtoupper(substr($user['full_name'] ?? 'U', 0, 1));
$userName = htmlspecialchars($user['full_name'] ?? 'Member', ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
$displayPageTitle = htmlspecialchars($pageTitle ?? 'Cellar Portal', ENT_QUOTES, 'UTF-8');
?>

<!-- BACKDROP FOR MOBILE SIDEBAR -->
<div class="user-sidebar-backdrop" id="userSidebarBackdrop"></div>

<!-- MOBILE DASHBOARD BAR (Visible only on mobile/tablet <= 960px) -->
<div class="user-mobile-bar">
    <button type="button" class="btn-user-sidebar-toggle" id="btnUserSidebarToggle" aria-label="Open Dashboard Menu">
        <i class="fa-solid fa-bars-staggered"></i>
        <span>Account Menu</span>
    </button>
    <span class="user-mobile-title"><?= $displayPageTitle ?></span>
</div>

<!-- SIDEBAR -->
<aside class="user-sidebar" id="userSidebar">
    <!-- Close button for mobile -->
    <button type="button" class="user-sidebar-close" id="btnUserSidebarClose" aria-label="Close Account Menu">
        <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="user-sidebar-profile">
        <div class="profile-avatar"><?= $userInitials ?></div>
        <h3 class="profile-name"><?= $userName ?></h3>
        <p class="profile-email"><?= $userEmail ?></p>
        <span class="member-tag"><i class="fa-solid fa-award"></i> Wine Club Member</span>
    </div>

    <nav class="user-nav">
        <a href="dashboard.php" class="user-nav-link <?= ($activeNav === 'dashboard') ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
        </a>
        <a href="profile.php" class="user-nav-link <?= ($activeNav === 'profile') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-circle"></i> <span>My Profile</span>
        </a>
        <a href="orders.php" class="user-nav-link <?= ($activeNav === 'orders') ? 'active' : '' ?>">
            <i class="fa-solid fa-box-open"></i> <span>My Orders</span>
        </a>
        <a href="free-gift.php" class="user-nav-link <?= ($activeNav === 'free-gift') ? 'active' : '' ?>">
            <i class="fa-solid fa-gift"></i> <span>My Free Gift</span>
        </a>
        <a href="address.php" class="user-nav-link <?= ($activeNav === 'address') ? 'active' : '' ?>">
            <i class="fa-solid fa-location-dot"></i> <span>My Address</span>
        </a>
        <a href="change-password.php" class="user-nav-link <?= ($activeNav === 'password') ? 'active' : '' ?>">
            <i class="fa-solid fa-key"></i> <span>Change Password</span>
        </a>
        <a href="../logout.php" class="user-nav-link logout-link">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Logout</span>
        </a>
    </nav>
</aside>

<script>
(function() {
    const toggleBtn = document.getElementById('btnUserSidebarToggle');
    const closeBtn = document.getElementById('btnUserSidebarClose');
    const sidebar = document.getElementById('userSidebar');
    const backdrop = document.getElementById('userSidebarBackdrop');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.classList.add('user-sidebar-locked');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.classList.remove('user-sidebar-locked');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
})();
</script>
