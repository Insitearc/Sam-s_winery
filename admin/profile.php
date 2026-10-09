<?php
/**
 * Sam's Fruit Wines - Admin Profile Settings
 * View and update administrator details
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Admin Profile Settings';
$activeNav = 'profile';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name) || empty($username) || empty($email)) {
        $error = "Name, username, and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Check uniqueness for username and email excluding current admin
            $stmtCheck = $pdo->prepare("SELECT id FROM admins WHERE (username = ? OR email = ?) AND id != ?");
            $stmtCheck->execute([$username, $email, $admin['id']]);
            if ($stmtCheck->fetch()) {
                $error = "Username or email is already in use by another administrator.";
            } else {
                $stmtUpdate = $pdo->prepare("
                    UPDATE admins 
                    SET name = ?, username = ?, email = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$name, $username, $email, $admin['id']]);

                $_SESSION['admin_name'] = $name;
                $admin['name'] = $name;
                $admin['username'] = $username;
                $admin['email'] = $email;

                $message = "Your administrator profile has been updated successfully.";
            }
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
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

<div style="max-width: 680px; margin: 0 auto; width: 100%;">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fa-solid fa-user-shield"></i> Administrator Account</h3>
            <span class="badge badge-active"><?php echo htmlspecialchars($admin['role'] ?? 'Super Admin'); ?></span>
        </div>

        <form method="POST" action="profile.php">
            <div style="display:flex; align-items:center; gap:20px; margin-bottom:28px; padding-bottom:24px; border-bottom:1px solid var(--border-subtle);">
                <div class="admin-avatar" style="width:68px; height:68px; font-size:28px;">
                    <?php echo strtoupper(substr($admin['name'] ?? 'A', 0, 1)); ?>
                </div>
                <div>
                    <h4 style="font-size: 1.15rem; color: #fff; margin-bottom: 4px;"><?php echo htmlspecialchars($admin['name']); ?></h4>
                    <p style="font-size: 12.5px; color: var(--gold-light);">@<?php echo htmlspecialchars($admin['username']); ?> &bull; <?php echo htmlspecialchars($admin['email']); ?></p>
                    <small style="color: var(--text-muted); font-size: 11.5px;">Registered: <?php echo date('d M Y', strtotime($admin['created_at'])); ?></small>
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                    Display Name *
                </label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required class="admin-form-input" style="width:100%; padding:10px 14px;">
            </div>

            <div class="admin-grid-2" style="margin-bottom: 18px;">
                <div>
                    <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                        Username *
                    </label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required class="admin-form-input" style="width:100%; padding:10px 14px;">
                </div>
                <div>
                    <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                        Email Address *
                    </label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required class="admin-form-input" style="width:100%; padding:10px 14px;">
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; padding-top:20px; border-top:1px solid var(--border-subtle);">
                <a href="change-password.php" class="btn-outline" style="padding: 10px 16px;">
                    <i class="fa-solid fa-key"></i> Change Password
                </a>
                <button type="submit" class="btn-gold" style="padding: 10px 24px;">
                    Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
