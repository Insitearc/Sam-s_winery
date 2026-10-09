<?php
/**
 * Sam's Fruit Wines - Admin Logout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_username'], $_SESSION['admin_role']);

header("Location: login.php");
exit;
