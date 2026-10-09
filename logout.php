<?php
/**
 * Sam's Fruit Wines - Customer Logout
 * Destroys user session and redirects to homepage.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

// If no other session data, destroy
if (empty($_SESSION['admin_id'])) {
    session_destroy();
}

header("Location: index.html");
exit;
