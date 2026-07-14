<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ログインしていません']);
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id, name, color FROM tags WHERE user_id = ? ORDER BY created_at ASC');
$stmt->execute([$userId]);
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tags);