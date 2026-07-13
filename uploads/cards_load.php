<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ログインしていません']);
    exit;
}

$boardId = $_GET['board_id'] ?? null;
if (!$boardId) {
    http_response_code(400);
    echo json_encode(['error' => 'board_idが必要です']);
    exit;
}

// cardsとcard_imagesをJOINして取得
$stmt = $pdo->prepare('
    SELECT c.id, c.type, c.pos_x, c.pos_y, ci.file_path
    FROM cards c
    LEFT JOIN card_images ci ON c.id = ci.card_id
    WHERE c.board_id = ?
    ORDER BY c.created_at ASC
');
$stmt->execute([$boardId]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cards);