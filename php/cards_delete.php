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

$input = json_decode(file_get_contents('php://input'), true);
$cardId = $input['card_id'] ?? null;

if (!$cardId) {
    http_response_code(400);
    echo json_encode(['error' => 'card_idが指定されていません']);
    exit;
}

// card_imagesからファイルパスを取得してから削除
$stmt = $pdo->prepare('SELECT file_path FROM card_images WHERE card_id = ?');
$stmt->execute([$cardId]);
$image = $stmt->fetch();

if ($image) {
    // サーバー上のファイルを削除
    $filePath = __DIR__ . '/../' . $image['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    // DBから削除
    $stmt = $pdo->prepare('DELETE FROM card_images WHERE card_id = ?');
    $stmt->execute([$cardId]);
}

// cardsを削除
$stmt = $pdo->prepare('DELETE FROM cards WHERE id = ?');
$stmt->execute([$cardId]);

echo json_encode(['success' => true]);