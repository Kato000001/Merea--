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
$url = $input['url'] ?? null;

if (!$boardId || !$url) {
    http_response_code(400);
    echo json_encode(['error' => 'パラメータが不足しています']);
    exit;
}

// OGP情報を取得
$html = @file_get_contents($url);
$title = $url;
$thumbnail = null;

if ($html) {
    // タイトル取得
    if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\'](.*?)["\']/', $html, $m)) {
        $title = $m[1];
    } elseif (preg_match('/<title>(.*?)<\/title>/i', $html, $m)) {
        $title = $m[1];
    }

    // サムネイル取得
    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](.*?)["\']/', $html, $m)) {
        $thumbnail = $m[1];
    }
}

// cardsテーブルに登録
$stmt = $pdo->prepare('INSERT INTO cards (board_id, type, pos_x, pos_y) VALUES (?, "url", 0, 0)');
$stmt->execute([$boardId]);
$cardId = $pdo->lastInsertId();

// card_urlsテーブルに登録
$stmt = $pdo->prepare('INSERT INTO card_urls (card_id, url, title, thumbnail_url) VALUES (?, ?, ?, ?)');
$stmt->execute([$cardId, $url, $title, $thumbnail]);

echo json_encode([
    'success' => true,
    'card_id' => $cardId,
    'url' => $url,
    'title' => $title,
    'thumbnail' => $thumbnail
]);