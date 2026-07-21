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
$x = $input['x'] ?? null;
$y = $input['y'] ?? null;
$z = $input['z'] ?? 0;

if (!$cardId || $x === null || $y === null || $z === null) {
    http_response_code(400);
    echo json_encode(['error' => 'パラメータが不足しています']);
    exit;
}

$stmt = $pdo->prepare('UPDATE cards SET pos_x = ?, pos_y = ?, z_index = ? WHERE id = ?');
$stmt->execute([$x, $y, $z, $cardId]);

echo json_encode(['success' => true]);