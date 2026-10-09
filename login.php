<?php
/**
 * Sam's Fruit Wines - User Login & Registration
 * Secure session-based authentication with password hashing.
 */

require_once __DIR__ . '/includes/functions.php';

$pdo = getDbConnection();
$redirect = trim($_GET['redirect'] ?? '');
if (empty($redirect)) {
    $redirect = 'user/dashboard.php';
}

// If already logged in, redirect directly
if (is_user_logged_in()) {
    header("Location: " . $redirect);
    exit;
}

$error_login = '';
$error_reg = '';
$success_reg = '';
$active_tab = 'login';

// Process standard POST fallback if JS disabled
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? 'login';

    if ($form_type === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = (string)($_POST['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            $error_login = 'Please enter your email/mobile and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR mobile = ?) LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error_login = 'Account is suspended. Please contact customer care.';
                } else {
                    $_SESSION['user_id']    = (int)$user['id'];
                    $_SESSION['user_name']  = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    header("Location: " . $redirect);
                    exit;
                }
            } else {
                $error_login = 'Invalid email/mobile or password.';
            }
        }
    } elseif ($form_type === 'register') {
        $active_tab       = 'register';
        $full_name        = trim($_POST['full_name'] ?? '');
        $mobile           = trim($_POST['mobile'] ?? '');
        $email            = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password         = (string)($_POST['password'] ?? '');
        $confirm_password = (string)($_POST['confirm_password'] ?? '');
        $dob              = trim($_POST['dob'] ?? '');
        $address          = trim($_POST['address'] ?? '');
        $city             = trim($_POST['city'] ?? '');
        $state            = trim($_POST['state'] ?? 'Maharashtra');
        $pincode          = trim($_POST['pincode'] ?? '');

        if (empty($full_name) || empty($mobile) || empty($email) || empty($password) || empty($dob) || empty($address) || empty($city) || empty($pincode)) {
            $error_reg = 'All registration fields are required.';
        } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
            $error_reg = 'Please enter a valid 10-digit Indian mobile number.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_reg = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error_reg = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $error_reg = 'Passwords do not match.';
        } else {
            // Check uniqueness
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1");
            $chk->execute([$email, $mobile]);
            if ($chk->fetch()) {
                $error_reg = 'An account with this email or mobile number already exists.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $pdo->prepare("INSERT INTO users (full_name, email, mobile, password, dob, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    $ins->execute([$full_name, $email, $mobile, $hash, $dob]);
                    $newId = (int)$pdo->lastInsertId();

                    $insAddr = $pdo->prepare("INSERT INTO addresses (user_id, address, city, state, pincode, is_default) VALUES (?, ?, ?, ?, ?, 1)");
                    $insAddr->execute([$newId, $address, $city, $state, $pincode]);

                    $pdo->commit();

                    $_SESSION['user_id']    = $newId;
                    $_SESSION['user_name']  = $full_name;
                    $_SESSION['user_email'] = $email;

                    header("Location: " . $redirect);
                    exit;
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error_reg = 'Error creating account. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login & Registration | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --dark: #070707;
            --dark-card: #121212;
            --dark-input: #181818;
            --gold: #c6a15b;
            --gold-light: #d4af37;
            --gold-border: rgba(198, 161, 91, 0.35);
            --gold-glow: rgba(198, 161, 91, 0.15);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.65);
            --font-sans: 'Outfit', sans-serif;
            --font-serif: 'Cormorant Garamond', Georgia, serif;
            --error: #ef4444;
            --success: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background: radial-gradient(circle at 50% 10%, rgba(198, 161, 91, 0.08) 0%, transparent 60%), linear-gradient(180deg, #0a0a0a 0%, #030303 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-brand a {
            display: inline-block;
            text-decoration: none;
        }

        .auth-brand img {
            height: 64px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
            transition: transform 0.3s ease;
        }

        .auth-brand img:hover {
            transform: scale(1.04);
        }

        .auth-container {
            width: 100%;
            max-width: 540px;
            background: var(--dark-card);
            border: 1px solid var(--gold-border);
            border-radius: 16px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.85), 0 0 40px var(--gold-glow);
            padding: 38px 34px;
            position: relative;
            overflow: hidden;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold-light), transparent);
        }

        .auth-tabs {
            display: flex;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
            gap: 10px;
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            padding: 12px 16px;
            color: var(--text-muted);
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .tab-btn.active {
            color: var(--gold-light);
            font-weight: 600;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gold-light);
            box-shadow: 0 0 10px var(--gold-light);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-header h1 {
            font-family: var(--font-serif);
            font-size: 2.1rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .auth-header p {
            font-size: 0.92rem;
            color: var(--text-muted);
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.35);
            color: #86efac;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
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
            color: rgba(255, 255, 255, 0.4);
            font-size: 14px;
            pointer-events: none;
            z-index: 1;
        }

        .input-wrap input,
        .input-wrap textarea,
        .input-wrap select {
            width: 100%;
            background: var(--dark-input);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 8px;
            padding: 12px 14px 12px 40px;
            color: #fff;
            font-family: var(--font-sans);
            font-size: 14px;
            outline: none;
            transition: all 0.25s ease;
        }

        .input-wrap textarea {
            min-height: 70px;
            resize: vertical;
            padding-top: 10px;
        }

        .input-wrap textarea + i {
            top: 20px;
        }

        .input-wrap input:focus,
        .input-wrap textarea:focus,
        .input-wrap select:focus {
            border-color: var(--gold-light);
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.25);
            background: #1c1c1c;
        }

        .input-wrap input[type="password"] {
            padding-right: 44px !important;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s, color 0.2s;
            z-index: 5;
        }

        .btn-toggle-password:hover {
            opacity: 1;
            color: #ffffff;
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
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background: #ffffff;
            color: #000000;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.35);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .auth-footer a {
            color: var(--gold);
            text-decoration: none;
            transition: color 0.2s;
        }

        .auth-footer a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }

        .back-home:hover {
            color: var(--gold);
        }

        @media (max-width: 580px) {
            body {
                padding: 24px 14px;
            }
            .auth-container {
                padding: 24px 16px;
                border-radius: 12px;
            }
            .auth-header h1 {
                font-size: 1.65rem;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .tab-btn {
                font-size: 13px;
                padding: 10px 8px;
                letter-spacing: 1px;
            }
        }

        @media (max-width: 380px) {
            body {
                padding: 16px 10px;
            }
            .auth-container {
                padding: 20px 12px;
            }
            .auth-header h1 {
                font-size: 1.45rem;
            }
            .btn-submit {
                padding: 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

    <div class="auth-brand">
        <a href="index.html" aria-label="Sam's Fruit Wines Home">
            <img src="images/samlogovj.png" alt="Sam's Fruit Wines Logo">
        </a>
    </div>

    <div class="auth-container">
        <!-- Tabs -->
        <div class="auth-tabs">
            <button type="button" class="tab-btn <?= $active_tab === 'login' ? 'active' : '' ?>" id="tab-login-btn">Sign In</button>
            <button type="button" class="tab-btn <?= $active_tab === 'register' ? 'active' : '' ?>" id="tab-register-btn">Create Account</button>
        </div>

        <!-- LOGIN FORM -->
        <div id="login-section" style="<?= $active_tab === 'login' ? '' : 'display:none;' ?>">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Access your cellar orders and exclusive club benefits</p>
            </div>

            <?php if (!empty($error_login)): ?>
                <div class="alert-box alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error_login) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" id="loginForm">
                <input type="hidden" name="form_type" value="login">

                <div class="form-group">
                    <label for="login-id">Email Address or Mobile</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="login-id" name="identifier" placeholder="e.g. name@example.com or 9876543210" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="login-pass">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="login-pass" name="password" placeholder="••••••••" required autocomplete="current-password" style="padding-right: 42px;">
                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('login-pass', this)" aria-label="Toggle password visibility">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In to Account
                </button>
            </form>

            <div class="auth-footer">
                Don't have a Sam's account? <a href="#" id="switch-to-register">Register for free</a>
            </div>
        </div>

        <!-- REGISTRATION FORM -->
        <div id="register-section" style="<?= $active_tab === 'register' ? '' : 'display:none;' ?>">
            <div class="auth-header">
                <h1>Join the Cellar Club</h1>
                <p>Register for seamless orders, birthday gifts, and exclusive tastings</p>
            </div>

            <?php if (!empty($error_reg)): ?>
                <div class="alert-box alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error_reg) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php<?= !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" id="registerForm">
                <input type="hidden" name="form_type" value="register">

                <div class="form-group">
                    <label for="reg-name">Full Name</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user-tag"></i>
                        <input type="text" id="reg-name" name="full_name" placeholder="First & Last Name" required autocomplete="name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-mobile">Mobile Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" id="reg-mobile" name="mobile" placeholder="10-digit mobile" maxlength="10" pattern="[0-9]{10}" required autocomplete="tel">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Email Address</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="reg-email" name="email" placeholder="you@domain.com" required autocomplete="email">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-dob">Date of Birth</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-calendar"></i>
                        <input type="date" id="reg-dob" name="dob" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-pass">Password (Min 6 chars)</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="reg-pass" name="password" placeholder="••••••••" minlength="6" required autocomplete="new-password" style="padding-right: 42px;">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('reg-pass', this)" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-cpass">Confirm Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-shield-halved"></i>
                            <input type="password" id="reg-cpass" name="confirm_password" placeholder="••••••••" minlength="6" required autocomplete="new-password" style="padding-right: 42px;">
                            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('reg-cpass', this)" aria-label="Toggle password visibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-address">Delivery Address</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-location-dot"></i>
                        <textarea id="reg-address" name="address" placeholder="Flat/House No, Building, Landmark, Street" required autocomplete="street-address"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-city">City</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-city"></i>
                            <input type="text" id="reg-city" name="city" placeholder="e.g. Nashik / Pune" required autocomplete="address-level2">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-state">State</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-map"></i>
                            <input type="text" id="reg-state" name="state" value="Maharashtra" required autocomplete="address-level1">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg-pincode">Pincode</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-envelopes-bulk"></i>
                        <input type="text" id="reg-pincode" name="pincode" placeholder="6-digit PIN" maxlength="6" pattern="[0-9]{6}" required autocomplete="postal-code">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i> Create Account & Continue
                </button>
            </form>

            <div class="auth-footer">
                Already registered? <a href="#" id="switch-to-login">Sign in here</a>
            </div>
        </div>

    </div>

    <a href="index.html" class="back-home">
        <i class="fa-solid fa-arrow-left"></i> Return to Sam's Winery Homepage
    </a>

    <script>
        const tabLoginBtn = document.getElementById('tab-login-btn');
        const tabRegBtn = document.getElementById('tab-register-btn');
        const loginSection = document.getElementById('login-section');
        const regSection = document.getElementById('register-section');

        const switchToReg = document.getElementById('switch-to-register');
        const switchToLogin = document.getElementById('switch-to-login');

        function showLogin() {
            tabLoginBtn.classList.add('active');
            tabRegBtn.classList.remove('active');
            loginSection.style.display = 'block';
            regSection.style.display = 'none';
        }

        function showRegister() {
            tabRegBtn.classList.add('active');
            tabLoginBtn.classList.remove('active');
            regSection.style.display = 'block';
            loginSection.style.display = 'none';
        }

        tabLoginBtn.addEventListener('click', showLogin);
        tabRegBtn.addEventListener('click', showRegister);
        if (switchToReg) switchToReg.addEventListener('click', (e) => { e.preventDefault(); showRegister(); });
        if (switchToLogin) switchToLogin.addEventListener('click', (e) => { e.preventDefault(); showLogin(); });

        // Restrict mobile to numbers only
        const mobileInput = document.getElementById('reg-mobile');
        if (mobileInput) {
            mobileInput.addEventListener('input', () => {
                mobileInput.value = mobileInput.value.replace(/\D/g, '').slice(0, 10);
            });
        }

        // Restrict pincode to numbers only
        const pinInput = document.getElementById('reg-pincode');
        if (pinInput) {
            pinInput.addEventListener('input', () => {
                pinInput.value = pinInput.value.replace(/\D/g, '').slice(0, 6);
            });
        }

        // Toggle password visibility
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
