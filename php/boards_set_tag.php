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
$boardId = $input['board_id'] ?? null;
$tagId = $input['tag_id'] ?? null;
$action = $input['action'] ?? null; // 'add' or 'remove'

if (!$boardId || !$tagId || !$action) {
    http_response_code(400);
    echo json_encode(['error' => 'パラメータが不足しています']);
    exit;
}

if ($action === 'add') {
    // 既に3つついている場合は追加不可
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM board_tags WHERE board_id = ?');
    $stmt->execute([$boardId]);
    $count = $stmt->fetchColumn();
    
    if ($count >= 3) {
        http_response_code(400);
        echo json_encode(['error' => 'タグは最大3つまでです']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT IGNORE INTO board_tags (board_id, tag_id) VALUES (?, ?)');
    $stmt->execute([$boardId, $tagId]);
} else if ($action === 'remove') {
    $stmt = $pdo->prepare('DELETE FROM board_tags WHERE board_id = ? AND tag_id = ?');
    $stmt->execute([$boardId, $tagId]);
}

echo json_encode(['success' => true]);