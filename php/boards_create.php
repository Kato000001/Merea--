<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// ログインしていなければエラーを返す
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ログインしていません']);
    exit;
}

// POST以外のリクエストは拒否
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => '不正なリクエストです']);
    exit;
}

$userId = $_SESSION['user_id'];

// JavaScriptから送られてきたJSONデータを取得
$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');

// 名前が空ならエラー
if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'ボード名を入力してください']);
    exit;
}

// 同名チェック
$stmt = $pdo->prepare('SELECT id FROM boards WHERE user_id = ? AND name = ?');
$stmt->execute([$userId, $name]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['error' => '同じ名前のボードが既に存在します']);
    exit;
}

// boardsテーブルに新規登録
$stmt = $pdo->prepare('INSERT INTO boards (user_id, name) VALUES (?, ?)');
$stmt->execute([$userId, $name]);

$newBoardId = $pdo->lastInsertId();

echo json_encode([
    'id' => $newBoardId,
    'name' => $name
]);