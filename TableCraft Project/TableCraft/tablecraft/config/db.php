<?php
/**
 * TableCraft - Database Connection
 * WampServer default credentials are used below.
 * Change DB_USER / DB_PASS only if you customised your MySQL root account.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tablecraft');

// Base URL of the project as seen in the browser, e.g. http://localhost/tablecraft
// Used to build absolute links in emails / redirects if ever needed.
define('BASE_URL', '/tablecraft');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:Arial;padding:40px;color:#080E53">
            <h2>Database connection failed</h2>
            <p>' . htmlspecialchars(mysqli_connect_error()) . '</p>
            <p>Make sure WampServer is running (icon is green) and that you have
            imported <code>database/tablecraft.sql</code> in phpMyAdmin.</p>
         </div>');
}

mysqli_set_charset($conn, 'utf8mb4');

// Start session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
