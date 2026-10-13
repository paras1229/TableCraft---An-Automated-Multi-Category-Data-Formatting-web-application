<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/category_defaults.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$category = $_POST['category'] ?? '';
$title    = trim($_POST['title'] ?? '');
$map      = tc_category_map();

if (!isset($map[$category])) { header('Location: dashboard.php'); exit; }
if ($title === '') { $title = $map[$category]['label'] . ' - ' . date('d M Y'); }

$userId       = $_SESSION['user_id'];
$isCommercial = $map[$category]['is_commercial'];

mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, 'INSERT INTO data_tables (user_id, category, title, is_commercial) VALUES (?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'issi', $userId, $category, $title, $isCommercial);
    mysqli_stmt_execute($stmt);
    $tableId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $order = 0;
    $colStmt = mysqli_prepare($conn, 'INSERT INTO table_columns (table_id, column_name, column_type, column_order, is_formula) VALUES (?, ?, ?, ?, ?)');
    foreach ($map[$category]['columns'] as $col) {
        $formula = $col['formula'] ?? 0;
        mysqli_stmt_bind_param($colStmt, 'issii', $tableId, $col['name'], $col['type'], $order, $formula);
        mysqli_stmt_execute($colStmt);
        $order++;
    }
    mysqli_stmt_close($colStmt);

    // Start with 3 blank rows so the table isn't empty on first open
    $rowStmt = mysqli_prepare($conn, 'INSERT INTO table_rows (table_id, row_order) VALUES (?, ?)');
    for ($i = 0; $i < 3; $i++) {
        mysqli_stmt_bind_param($rowStmt, 'ii', $tableId, $i);
        mysqli_stmt_execute($rowStmt);
    }
    mysqli_stmt_close($rowStmt);

    mysqli_commit($conn);
    header('Location: table_view.php?id=' . $tableId);
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    header('Location: dashboard.php?error=create_failed');
    exit;
}
