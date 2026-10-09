<?php

/**
 * Sam's Fruit Wines - Admin Change Password
 * Secure password modification with password_verify and password_hash
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Change Password';
$activeNav = 'password';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "All password fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation password do not match.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$admin['id']]);
        $currentHash = $stmt->fetchColumn();

        if (!$currentHash || !password_verify($currentPassword, $currentHash)) {
            $error = "Current password entered is incorrect.";
        } else {
            // Update password
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmtUpdate = $pdo->prepare("UPDATE admins SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmtUpdate->execute([$newHash, $admin['id']]);

            $message = "Your password has been changed successfully!";
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<?php if ($message): ?>
    <div class="admin-alert admin-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span><?php echo $message; ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-alert admin-alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<div style="max-width: 580px; margin: 0 auto; width: 100%;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fa-solid fa-shield-halved"></i> Update Admin Password</h3>
        </div>

        <form method="POST" action="change-password.php">
            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                    Current Password *
                </label>
                <div class="password-field-wrap">
                    <input type="password" id="cur_admin_pass" name="current_password" required class="admin-form-input" style="width:100%; padding:10px 14px;" placeholder="••••••••">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('cur_admin_pass', this)" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                    New Password (min 6 characters) *
                </label>
                <div class="password-field-wrap">
                    <input type="password" id="new_admin_pass" name="new_password" required minlength="6" class="admin-form-input" style="width:100%; padding:10px 14px;" placeholder="••••••••">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('new_admin_pass', this)" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 28px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                    Confirm New Password *
                </label>
                <div class="password-field-wrap">
                    <input type="password" id="conf_admin_pass" name="confirm_password" required minlength="6" class="admin-form-input" style="width:100%; padding:10px 14px;" placeholder="••••••••">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('conf_admin_pass', this)" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; padding-top:18px; border-top:1px solid var(--border-subtle);">
                <a href="dashboard.php" class="btn-outline" style="padding: 10px 18px;">Cancel</a>
                <button type="submit" class="btn-gold" style="padding: 10px 24px;">Change Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
