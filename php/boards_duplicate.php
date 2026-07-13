<?php
session_start();
require_once 'db.php';

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
$boardId = $input['id'] ?? null;

if (!$boardId) {
    http_response_code(400);
    echo json_encode(['error' => 'ボードIDが指定されていません']);
    exit;
}

// ① 元のボードを取得
$stmt = $pdo->prepare('SELECT name FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, $userId]);
$board = $stmt->fetch();

// ここに追加
if (!$board) {
    http_response_code(404);
    echo json_encode(['error' => 'ボードが見つかりません']);
    exit;
}

// ② 「のコピー」を付けてINSERT
$newName = $board['name'] . 'のコピー';
$stmt = $pdo->prepare('INSERT INTO boards (user_id, name) VALUES (?, ?)');
$stmt->execute([$userId, $newName]);
$newId = $pdo->lastInsertId();

echo json_encode([
    'success' => true,
    'id' => $newId,
    'name' => $newName
]);