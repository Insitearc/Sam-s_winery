<?php
/**
 * Sam's Fruit Wines - User Password Change
 * Securely verifies current password and updates hash.
 */

require_once __DIR__ . '/../includes/functions.php';
require_user_login('../login.php');

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$user = get_logged_in_user($pdo);

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pass = (string)($_POST['current_password'] ?? '');
    $new_pass     = (string)($_POST['new_password'] ?? '');
    $confirm_pass = (string)($_POST['confirm_password'] ?? '');

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error_msg = 'Please fill in all password fields.';
    } elseif (strlen($new_pass) < 6) {
        $error_msg = 'New password must be at least 6 characters long.';
    } elseif ($new_pass !== $confirm_pass) {
        $error_msg = 'New password and confirmation do not match.';
    } else {
        // Fetch current hash
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currHash = $stmt->fetchColumn();

        if (!$currHash || !password_verify($current_pass, $currHash)) {
            $error_msg = 'Current password is incorrect.';
        } else {
            $newHash = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->execute([$newHash, $userId]);
            $success_msg = 'Your password has been changed successfully!';
        }
    }
}
$pageTitle = "Change Password";
$activeNav = "password";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="user-style.css">
</head>
<body>

    <!-- ORIGINAL WEBSITE FLOATING NAVBAR -->
    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="user-container">
        <!-- SIDEBAR & MOBILE HAMBURGER NAVIGATION -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="user-content">
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-shield-halved"></i> Change Account Password</h2>
                </div>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert-box alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= htmlspecialchars($success_msg) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert-box alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= htmlspecialchars($error_msg) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="change-password.php" style="max-width: 500px;">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-field-wrap">
                            <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('current_password', this)" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password (Min 6 characters)</label>
                        <div class="password-field-wrap">
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••" minlength="6" required autocomplete="new-password">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('new_password', this)" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-field-wrap">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" minlength="6" required autocomplete="new-password">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div style="margin-top:24px;">
                        <button type="submit" class="btn-gold"><i class="fa-solid fa-lock"></i> Update Password</button>
                    </div>
                </form>
            </div>
        </main>
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
</body>
</html>
