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
$boardId = $input['board_id'] ?? null;

if (!$boardId) {
    http_response_code(400);
    echo json_encode(['error' => 'board_idが必要です']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO cards (board_id, type, content, pos_x, pos_y) VALUES (?, "text", "", 0, 0)');
$stmt->execute([$boardId]);
$cardId = $pdo->lastInsertId();

echo json_encode(['success' => true, 'card_id' => $cardId]);