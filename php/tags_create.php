<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ログインしていません']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => '不正なリクエストです']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$color = $input['color'] ?? '#888888';

if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'タグ名を入力してください']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO tags (user_id, name, color) VALUES (?, ?, ?)');
$stmt->execute([$userId, $name, $color]);
$tagId = $pdo->lastInsertId();

echo json_encode(['success' => true, 'id' => $tagId, 'name' => $name, 'color' => $color]);