<?php
/**
 * Sam's Fruit Wines - Authentication API
 * Handles Login, Registration, Session Checks, and Checkout profile pre-fill.
 */

header('Content-Type: application/json; charset=UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = getDbConnection();

// Read JSON input if sent as application/json
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?: [];

// Merge JSON with POST
$input = array_merge($_POST, $jsonBody);

switch ($action) {

    // ----------------------------------------------------
    // CHECK AUTH STATUS
    // ----------------------------------------------------
    case 'check':
        if (is_user_logged_in()) {
            $stmt = $pdo->prepare("
                SELECT u.id, u.full_name, u.email, u.mobile, u.dob,
                       a.address, a.city, a.state, a.pincode
                FROM users u
                LEFT JOIN addresses a ON a.user_id = u.id AND a.is_default = 1
                WHERE u.id = ?
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                echo json_encode([
                    'loggedIn' => true,
                    'user'     => $user
                ]);
                exit;
            }
        }

        echo json_encode([
            'loggedIn' => false,
            'user'     => null
        ]);
        exit;

    // ----------------------------------------------------
    // USER LOGIN
    // ----------------------------------------------------
    case 'login':
        $identifier = trim($input['identifier'] ?? $input['email_or_mobile'] ?? $input['email'] ?? '');
        $password   = (string)($input['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            http_response_code(422);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Please provide both email/mobile and password.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT u.*, a.address, a.city, a.state, a.pincode
            FROM users u
            LEFT JOIN addresses a ON a.user_id = u.id AND a.is_default = 1
            WHERE (u.email = ? OR u.mobile = ?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Invalid email/mobile or password.'
            ]);
            exit;
        }

        if ($user['status'] !== 'active') {
            http_response_code(403);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Your account is suspended. Please contact customer support.'
            ]);
            exit;
        }

        // Set session
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];

        // Remove password before sending
        unset($user['password']);

        echo json_encode([
            'status'   => 'success',
            'message'  => 'Login successful. Welcome back, ' . $user['full_name'] . '!',
            'loggedIn' => true,
            'user'     => $user
        ]);
        exit;

    // ----------------------------------------------------
    // USER REGISTRATION
    // ----------------------------------------------------
    case 'register':
        $full_name        = trim($input['full_name'] ?? '');
        $mobile           = trim($input['mobile'] ?? '');
        $email            = trim(filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password         = (string)($input['password'] ?? '');
        $confirm_password = (string)($input['confirm_password'] ?? '');
        $dob              = trim($input['dob'] ?? '');
        $address          = trim($input['address'] ?? '');
        $city             = trim($input['city'] ?? '');
        $state            = trim($input['state'] ?? 'Maharashtra');
        $pincode          = trim($input['pincode'] ?? '');

        // Validations
        if (empty($full_name) || empty($mobile) || empty($email) || empty($password) || empty($dob) || empty($address) || empty($city) || empty($pincode)) {
            http_response_code(422);
            echo json_encode([
                'status'  => 'error',
                'message' => 'All registration fields are required.'
            ]);
            exit;
        }

        if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
            http_response_code(422);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Please enter a valid 10-digit Indian mobile number.'
            ]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Please provide a valid email address.'
            ]);
            exit;
        }

        if (strlen($password) < 6) {
            http_response_code(422);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Password must be at least 6 characters long.'
            ]);
            exit;
        }

        if ($password !== $confirm_password) {
            http_response_code(422);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Passwords do not match.'
            ]);
            exit;
        }

        // Check if email or mobile already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'status'  => 'error',
                'message' => 'An account with this email already exists. Please login.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? LIMIT 1");
        $stmt->execute([$mobile]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'status'  => 'error',
                'message' => 'An account with this mobile number already exists. Please login.'
            ]);
            exit;
        }

        // Insert user and address in a transaction
        try {
            $pdo->beginTransaction();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, mobile, password, dob, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$full_name, $email, $mobile, $password_hash, $dob]);
            $userId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO addresses (user_id, address, city, state, pincode, is_default)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$userId, $address, $city, $state, $pincode]);

            $pdo->commit();

            // Set session
            $_SESSION['user_id']    = $userId;
            $_SESSION['user_name']  = $full_name;
            $_SESSION['user_email'] = $email;

            echo json_encode([
                'status'   => 'success',
                'message'  => 'Account registered successfully! Welcome to Sam\'s Fruit Wines.',
                'loggedIn' => true,
                'user'     => [
                    'id'        => $userId,
                    'full_name' => $full_name,
                    'email'     => $email,
                    'mobile'    => $mobile,
                    'dob'       => $dob,
                    'address'   => $address,
                    'city'      => $city,
                    'state'     => $state,
                    'pincode'   => $pincode
                ]
            ]);
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Registration error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Could not complete registration. Please try again.'
            ]);
            exit;
        }

    // ----------------------------------------------------
    // USER LOGOUT
    // ----------------------------------------------------
    case 'logout':
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
        echo json_encode([
            'status'  => 'success',
            'message' => 'Logged out successfully.'
        ]);
        exit;

    default:
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid action.'
        ]);
        exit;
}
