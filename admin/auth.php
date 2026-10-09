<?php
/**
 * Sam's Fruit Wines - Admin Authentication Guard
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

$pdo = getDbConnection();
$admin = get_logged_in_admin($pdo);

if (!$admin || $admin['status'] !== 'active') {
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    header("Location: login.php?error=" . urlencode("Account inactive or invalid."));
    exit;
}
