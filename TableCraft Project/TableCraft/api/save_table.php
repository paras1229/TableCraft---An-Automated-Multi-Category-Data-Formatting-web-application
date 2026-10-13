<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$userId  = $_SESSION['user_id'];
$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload || empty($payload['table_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing table_id.']);
    exit;
}

$tableId = (int)$payload['table_id'];
$title   = trim($payload['title'] ?? 'Untitled Table');
if ($title === '') $title = 'Untitled Table';
$accentColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $payload['accent_color'] ?? '') ? $payload['accent_color'] : '#1F50FF';
$fontFamily  = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $payload['font_family'] ?? 'Figtree'), 0, 50) ?: 'Figtree';
$columns     = is_array($payload['columns'] ?? null) ? $payload['columns'] : [];
$rows        = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];

if (empty($columns)) {
    echo json_encode(['success' => false, 'message' => 'A table needs at least one column.']);
    exit;
}

// Ownership check
$stmt = mysqli_prepare($conn, 'SELECT id FROM data_tables WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $tableId, $userId);
mysqli_stmt_execute($stmt);
if (mysqli_stmt_get_result($stmt)->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Table not found or access denied.']);
    exit;
}
mysqli_stmt_close($stmt);

mysqli_begin_transaction($conn);
try {
    // 1. Update table meta
    $stmt = mysqli_prepare($conn, 'UPDATE data_tables SET title = ?, accent_color = ?, font_family = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'sssi', $title, $accentColor, $fontFamily, $tableId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 2. Wipe existing columns (cascades cells), then rows (cascades any leftover cells)
    $stmt = mysqli_prepare($conn, 'DELETE FROM table_columns WHERE table_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $tableId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, 'DELETE FROM table_rows WHERE table_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $tableId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 3. Re-insert columns, tracking front-end id -> new DB id
    $columnIdMap = [];
    $newColumns = [];
    $order = 0;
    $colStmt = mysqli_prepare($conn, 'INSERT INTO table_columns (table_id, column_name, column_type, column_order, is_formula) VALUES (?, ?, ?, ?, ?)');
    foreach ($columns as $col) {
        $name = mb_substr(trim($col['column_name'] ?? 'Column'), 0, 100);
        if ($name === '') $name = 'Column';
        $type = in_array($col['column_type'] ?? 'text', ['text', 'number', 'date', 'currency']) ? $col['column_type'] : 'text';
        $isFormula = !empty($col['is_formula']) ? 1 : 0;

        mysqli_stmt_bind_param($colStmt, 'issii', $tableId, $name, $type, $order, $isFormula);
        mysqli_stmt_execute($colStmt);
        $newId = mysqli_insert_id($conn);
        $columnIdMap[$col['id']] = $newId;
        $newColumns[] = ['id' => $newId, 'column_name' => $name, 'column_type' => $type, 'is_formula' => $isFormula];
        $order++;
    }
    mysqli_stmt_close($colStmt);

    // 4. Re-insert rows + cells
    $newRows = [];
    $rowOrder = 0;
    $rowStmt  = mysqli_prepare($conn, 'INSERT INTO table_rows (table_id, row_order) VALUES (?, ?)');
    $cellStmt = mysqli_prepare($conn, 'INSERT INTO table_cells (row_id, column_id, cell_value) VALUES (?, ?, ?)');
    foreach ($rows as $row) {
        mysqli_stmt_bind_param($rowStmt, 'ii', $tableId, $rowOrder);
        mysqli_stmt_execute($rowStmt);
        $newRowId = mysqli_insert_id($conn);

        $newCells = [];
        foreach (($row['cells'] ?? []) as $cell) {
            if (!isset($columnIdMap[$cell['column_id']])) continue;
            $newColId = $columnIdMap[$cell['column_id']];
            $value = (string)($cell['value'] ?? '');
            mysqli_stmt_bind_param($cellStmt, 'iis', $newRowId, $newColId, $value);
            mysqli_stmt_execute($cellStmt);
            $newCells[] = ['column_id' => $newColId, 'value' => $value];
        }
        $newRows[] = ['row_id' => $newRowId, 'cells' => $newCells];
        $rowOrder++;
    }
    mysqli_stmt_close($rowStmt);
    mysqli_stmt_close($cellStmt);

    mysqli_commit($conn);
    echo json_encode([
        'success' => true,
        'table_id' => $tableId,
        'columns' => $newColumns,
        'rows' => $newRows,
    ]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Save failed. Please try again.']);
}
