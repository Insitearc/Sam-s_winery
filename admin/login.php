<?php
/**
 * Sam's Fruit Wines - Admin Login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$pdo = getDbConnection();
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['username'] ?? '');
    $password   = (string)($_POST['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['status'] !== 'active') {
                $error = 'This admin account is disabled. Contact system administrator.';
            } else {
                $_SESSION['admin_id']       = (int)$admin['id'];
                $_SESSION['admin_name']     = $admin['name'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role']     = $admin['role'];

                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error = 'Invalid administrator credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --dark-bg: #070707;
            --card-bg: #111111;
            --gold: #c6a15b;
            --gold-light: #d4af37;
            --gold-border: rgba(198, 161, 91, 0.35);
            --gold-glow: rgba(198, 161, 91, 0.15);
            --font-sans: 'Outfit', sans-serif;
            --font-serif: 'Cormorant Garamond', Georgia, serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-sans);
            background: radial-gradient(circle at 50% 20%, rgba(198, 161, 91, 0.1) 0%, transparent 70%), linear-gradient(180deg, #0a0a0a 0%, #030303 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            border: 1px solid var(--gold-border);
            border-radius: 16px;
            padding: 42px 36px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.9), 0 0 50px var(--gold-glow);
            text-align: center;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold-light), transparent);
        }

        .brand-logo {
            margin-bottom: 24px;
        }

        .brand-logo img {
            height: 60px;
            width: auto;
            object-fit: contain;
        }

        .login-card h1 {
            font-family: var(--font-serif);
            font-size: 2rem;
            color: #fff;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }

        .admin-badge {
            display: inline-block;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid var(--gold-border);
            color: var(--gold-light);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 22px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap > i:first-child {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.35);
            pointer-events: none;
            z-index: 1;
        }

        .input-wrap input {
            width: 100%;
            background: #181818;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 8px;
            padding: 13px 14px 13px 42px;
            color: #fff;
            font-family: var(--font-sans);
            font-size: 14px;
            outline: none;
            transition: all 0.25s;
        }

        .input-wrap input[type="password"] {
            padding-right: 44px !important;
        }

        .input-wrap input:focus {
            border-color: var(--gold-light);
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.25);
            background: #1c1c1c;
        }

        .btn-toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--gold, #c6a15b);
            opacity: 0.8;
            cursor: pointer;
            padding: 6px;
            width: 32px;
            height: 32px;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s, color 0.2s;
            z-index: 5;
        }

        .btn-toggle-password:hover {
            opacity: 1;
            color: #fff;
        }

        .btn-toggle-password i {
            position: static !important;
            left: auto !important;
            top: auto !important;
            transform: none !important;
            font-size: 15px !important;
            color: inherit !important;
            pointer-events: none !important;
        }

        .btn-submit {
            width: 100%;
            background: var(--gold-light);
            color: #070707;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #fff;
            color: #000;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.35);
        }

        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--gold-light);
        }

        @media (max-width: 480px) {
            body {
                padding: 16px 10px;
            }
            .login-card {
                padding: 26px 18px;
            }
            .login-card h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo">
            <img src="../images/samlogovj.png" alt="Sam's Fruit Wines Logo">
        </div>

        <h1>Estate Admin Portal</h1>
        <span class="admin-badge"><i class="fa-solid fa-lock"></i> Authorized Personnel Only</span>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="admin-user">Username or Email</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-shield"></i>
                    <input type="text" id="admin-user" name="username" placeholder="admin" required autocomplete="username" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="admin-pass">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-key"></i>
                    <input type="password" id="admin-pass" name="password" placeholder="••••••••" required autocomplete="current-password" style="padding-right: 42px;">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('admin-pass', this)" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-right-to-bracket"></i> Secure Login
            </button>
        </form>

        <a href="../index.html" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Return to Sam's Winery Main Site
        </a>
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
