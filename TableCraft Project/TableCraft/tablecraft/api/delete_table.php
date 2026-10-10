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
$tableId = (int)($payload['table_id'] ?? 0);

if ($tableId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid table id.']);
    exit;
}

$stmt = mysqli_prepare($conn, 'DELETE FROM data_tables WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $tableId, $userId);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Table not found or already deleted.']);
}
mysqli_stmt_close($stmt);
