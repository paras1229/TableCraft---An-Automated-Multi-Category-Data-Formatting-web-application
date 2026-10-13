<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

if (($payload['action'] ?? '') === 'theme') {
    $theme = ($payload['theme'] ?? '') === 'dark' ? 'dark' : 'bright';
    $_SESSION['theme_pref'] = $theme; // best-effort server-side memory of preference
    echo json_encode(['success' => true, 'theme' => $theme]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
