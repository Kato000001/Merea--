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

$userId = $_SESSION['user_id'];

// このユーザーのボードを全部取得する
$stmt = $pdo->prepare('SELECT id, name, sort_order, created_at, updated_at FROM boards WHERE user_id = ? ORDER BY sort_order ASC, id ASC');
$stmt->execute([$userId]);
$boards = $stmt->fetchAll();

echo json_encode($boards);