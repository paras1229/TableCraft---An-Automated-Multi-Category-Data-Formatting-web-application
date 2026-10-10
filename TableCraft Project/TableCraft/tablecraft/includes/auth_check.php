<?php
/**
 * Include this AFTER config/db.php on every protected page.
 * Redirects to the login page if no user is logged in.
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/categories/') !== false || strpos($_SERVER['PHP_SELF'], '/api/') !== false ? '../auth/login.php' : 'auth/login.php'));
    exit;
}
