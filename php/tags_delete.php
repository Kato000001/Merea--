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
$tagId = $input['id'] ?? null;

if (!$tagId) {
    http_response_code(400);
    echo json_encode(['error' => 'タグIDが指定されていません']);
    exit;
}

// board_tagsから先に削除
$stmt = $pdo->prepare('DELETE FROM board_tags WHERE tag_id = ?');
$stmt->execute([$tagId]);

// tagsから削除
$stmt = $pdo->prepare('DELETE FROM tags WHERE id = ? AND user_id = ?');
$stmt->execute([$tagId, $userId]);

echo json_encode(['success' => true]);