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

$boardId = $_POST['board_id'] ?? null;
if (!$boardId) {
    http_response_code(400);
    echo json_encode(['error' => 'board_idが必要です']);
    exit;
}

$file = $_FILES['image'] ?? null;
if (!$file) {
    http_response_code(400);
    echo json_encode(['error' => '画像がありません']);
    exit;
}

// uploadsフォルダに保存
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $ext;
$savePath = __DIR__ . '/../uploads/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    http_response_code(500);
    echo json_encode(['error' => '保存に失敗しました']);
    exit;
}

// cardsテーブルに登録
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare('INSERT INTO cards (board_id, type, pos_x, pos_y) VALUES (?, "image", 0, 0)');
$stmt->execute([$boardId]);
$cardId = $pdo->lastInsertId();

// card_imagesテーブルに登録
$stmt = $pdo->prepare('INSERT INTO card_images (card_id, file_path) VALUES (?, ?)');
$stmt->execute([$cardId, 'uploads/' . $filename]);

echo json_encode([
    'success' => true,
    'card_id' => $cardId,
    'file_path' => 'uploads/' . $filename
]);