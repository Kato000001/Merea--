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
$content = $input['content'] ?? null;

if (!$cardId || $content === null) {
    http_response_code(400);
    echo json_encode(['error' => 'パラメータが不足しています']);
    exit;
}

$stmt = $pdo->prepare('UPDATE cards SET content = ? WHERE id = ?');
$stmt->execute([$content, $cardId]);

echo json_encode(['success' => true]);