<?php
/**
 * Sam's Fruit Wines - User Profile
 * Displays and allows updating of Full Name, Mobile, and Date of Birth.
 */

require_once __DIR__ . '/../includes/functions.php';
require_user_login('../login.php');

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$user = get_logged_in_user($pdo);

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $mobile    = trim($_POST['mobile'] ?? '');
    $dob       = trim($_POST['dob'] ?? '');

    if (empty($full_name) || empty($mobile) || empty($dob)) {
        $error_msg = 'All profile fields are required.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $error_msg = 'Please enter a valid 10-digit Indian mobile number.';
    } else {
        // Check if mobile changed and taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? AND id != ? LIMIT 1");
        $stmt->execute([$mobile, $userId]);
        if ($stmt->fetch()) {
            $error_msg = 'This mobile number is already registered with another account.';
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, mobile = ?, dob = ? WHERE id = ?");
            $updateStmt->execute([$full_name, $mobile, $dob, $userId]);
            $_SESSION['user_name'] = $full_name;
            $success_msg = 'Profile updated successfully!';
            $user = get_logged_in_user($pdo);
        }
    }
}
$pageTitle = "My Profile";
$activeNav = "profile";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Sam's Fruit Wines</title>
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
                    <h2><i class="fa-solid fa-user-gear"></i> My Profile Information</h2>
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

                <form method="POST" action="profile.php">
                    <div class="form-group">
                        <label for="prof-name">Full Name</label>
                        <input type="text" id="prof-name" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prof-email">Email Address (Registered)</label>
                            <input type="email" id="prof-email" class="form-control" value="<?= e($user['email']) ?>" readonly style="opacity:0.6; cursor:not-allowed;">
                            <small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Email address cannot be changed as it is your unique account identifier.</small>
                        </div>

                        <div class="form-group">
                            <label for="prof-mobile">Mobile Number</label>
                            <input type="tel" id="prof-mobile" name="mobile" class="form-control" value="<?= e($user['mobile']) ?>" maxlength="10" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prof-dob">Date of Birth</label>
                        <input type="date" id="prof-dob" name="dob" class="form-control" value="<?= e($user['dob']) ?>" required>
                        <small style="color:var(--text-muted);font-size:11px;margin-top:4px;display:block;">Used to verify legal drinking age and dispatch birthday gifts.</small>
                    </div>

                    <div style="margin-top:25px;">
                        <button type="submit" class="btn-gold"><i class="fa-solid fa-floppy-disk"></i> Save Profile Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
