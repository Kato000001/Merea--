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

if (!$boardId) {
    http_response_code(400);
    echo json_encode(['error' => 'ボードIDが指定されていません']);
    exit;
}

// ① ボード内の画像カードのファイルを削除
$stmt = $pdo->prepare('
    SELECT ci.file_path FROM card_images ci
    INNER JOIN cards c ON ci.card_id = c.id
    WHERE c.board_id = ?
');
$stmt->execute([$boardId]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($images as $image) {
    $filePath = __DIR__ . '/../' . $image['file_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// ② card_images、cards、boardsの順に削除
$stmt = $pdo->prepare('
    DELETE ci FROM card_images ci
    INNER JOIN cards c ON ci.card_id = c.id
    WHERE c.board_id = ?
');
$stmt->execute([$boardId]);

$stmt = $pdo->prepare('DELETE FROM cards WHERE board_id = ?');
$stmt->execute([$boardId]);

$stmt = $pdo->prepare('DELETE FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, $userId]);

echo json_encode(['success' => true]);