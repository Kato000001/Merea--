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

// ① 元のボードを取得
$stmt = $pdo->prepare('SELECT name FROM boards WHERE id = ? AND user_id = ?');
$stmt->execute([$boardId, $userId]);
$board = $stmt->fetch();

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

// ③ 元のボードのカードを全て取得してコピー
$stmt = $pdo->prepare('SELECT * FROM cards WHERE board_id = ?');
$stmt->execute([$boardId]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cards as $card) {
    $stmt = $pdo->prepare('INSERT INTO cards (board_id, type, content, pos_x, pos_y) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$newId, $card['type'], $card['content'], $card['pos_x'], $card['pos_y']]);
    $newCardId = $pdo->lastInsertId();

    if ($card['type'] === 'image') {
        $stmt = $pdo->prepare('SELECT file_path FROM card_images WHERE card_id = ?');
        $stmt->execute([$card['id']]);
        $image = $stmt->fetch();

        if ($image) {
            $srcPath = __DIR__ . '/../' . $image['file_path'];
            $ext = pathinfo($image['file_path'], PATHINFO_EXTENSION);
            $newFilename = uniqid() . '.' . $ext;
            $destPath = __DIR__ . '/../uploads/' . $newFilename;
            copy($srcPath, $destPath);

            $stmt = $pdo->prepare('INSERT INTO card_images (card_id, file_path) VALUES (?, ?)');
            $stmt->execute([$newCardId, 'uploads/' . $newFilename]);
        }
    }
}

echo json_encode([
    'success' => true,
    'id' => $newId,
    'name' => $newName
]);