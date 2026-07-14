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
$name = trim($input['name'] ?? '');
$color = $input['color'] ?? null;

if (!$tagId) {
    http_response_code(400);
    echo json_encode(['error' => 'タグIDが指定されていません']);
    exit;
}

if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'タグ名を入力してください']);
    exit;
}

$stmt = $pdo->prepare('UPDATE tags SET name = ?, color = ? WHERE id = ? AND user_id = ?');
$stmt->execute([$name, $color, $tagId, $userId]);

echo json_encode(['success' => true]);