        </div> <!-- .admin-body -->
    </div> <!-- .admin-main -->
</div> <!-- .admin-shell -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Optional notification auto-dismiss
    const alerts = document.querySelectorAll('.admin-alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(el => {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    }

    // Responsive Admin Mobile Sidebar Drawer
    const hamburger = document.getElementById('adminHamburgerBtn');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const closeBtn = document.getElementById('adminSidebarClose');

    function openAdminSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.classList.add('admin-sidebar-open');
    }

    function closeAdminSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.classList.remove('admin-sidebar-open');
    }

    if (hamburger) hamburger.addEventListener('click', openAdminSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeAdminSidebar);
    if (backdrop) backdrop.addEventListener('click', closeAdminSidebar);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAdminSidebar();
        }
    });
});
</script>
</body>
</html>

