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

// 自分の持ち物のボードだけ削除できるようにする（user_idも条件に入れる）
$stmt = $pdo->prepare('DELETE FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, $userId]);

echo json_encode(['success' => true]);