<?php
/**
 * Sam's Fruit Wines - Common Helper Functions
 * Includes session management, auth checks, tokens, and sanitization.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Check if a regular user is logged in.
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user record.
 */
function get_logged_in_user($pdo = null) {
    if (!is_user_logged_in()) {
        return null;
    }
    if ($pdo === null) {
        $pdo = getDbConnection();
    }
    $stmt = $pdo->prepare("SELECT id, full_name, email, mobile, dob, status, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Require a user to be logged in; if not, redirect to login page.
 */
function require_user_login($redirect_to = null) {
    if (!is_user_logged_in()) {
        $target = $redirect_to ? '?redirect=' . urlencode($redirect_to) : '';
        header("Location: ../login.php" . $target);
        exit;
    }
}

/**
 * Check if an admin is logged in.
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Get current admin record.
 */
function get_logged_in_admin($pdo = null) {
    if (!is_admin_logged_in()) {
        return null;
    }
    if ($pdo === null) {
        $pdo = getDbConnection();
    }
    $stmt = $pdo->prepare("SELECT id, name, username, email, role, status, created_at FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Require admin to be logged in; if not, redirect to admin login page.
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Generate a unique Free Gift Token (e.g. SAM-8K4P-X92L).
 */
function generate_unique_gift_token($pdo) {
    $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $maxAttempts = 20;

    for ($i = 0; $i < $maxAttempts; $i++) {
        $part1 = substr(str_shuffle($chars), 0, 4);
        $part2 = substr(str_shuffle($chars), 0, 4);
        $token = "SAM-{$part1}-{$part2}";

        $stmt = $pdo->prepare("SELECT id FROM gift_tokens WHERE token = ?");
        $stmt->execute([$token]);
        if (!$stmt->fetch()) {
            return $token;
        }
    }

    // Fallback using timestamp
    return "SAM-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

function generate_unique_token($pdo) {
    return generate_unique_gift_token($pdo);
}

/**
 * Generate a unique Order Number (e.g. SAM-ORD-1029384).
 */
function generate_order_number($pdo) {
    return "SAM-ORD-" . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Escape output for safe HTML display.
 */
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency in Indian Rupees.
 */
function format_price($amount) {
    return '₹' . number_format((float)$amount, 2);
}

/**
 * Generate and return CSRF token.
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token.
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}
