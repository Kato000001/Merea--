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
$boardId = $input['id'] ?? null;
$newTitle = $input['name'] ?? null;

if (!$boardId) {
    http_response_code(400);
    echo json_encode(['error' => 'ボードIDが指定されていません']);
    exit;
}

if (!$newTitle) {
    http_response_code(400);
    echo json_encode(['error' => '新しいボード名が指定されていません']);
    exit;
}

// 同名チェック（自分の別のボードと同じ名前でないか）
$stmt = $pdo->prepare('SELECT id FROM boards WHERE user_id = ? AND name = ? AND id != ?');
$stmt->execute([$userId, $newTitle, $boardId]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['error' => '同じ名前のボードが既に存在します']);
    exit;
}

// 自分の持ち物のボードだけ削除できるようにする（user_idも条件に入れる）
$stmt = $pdo->prepare('UPDATE boards SET name = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$newTitle, $boardId, $userId]);

echo json_encode(['success' => true]);

