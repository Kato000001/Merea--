<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ログインしていません']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('
    SELECT b.id, b.name,
           GROUP_CONCAT(t.id ORDER BY t.id SEPARATOR ",") AS tag_ids,
           GROUP_CONCAT(t.name ORDER BY t.id SEPARATOR ",") AS tag_names,
           GROUP_CONCAT(t.color ORDER BY t.id SEPARATOR ",") AS tag_colors
    FROM boards b
    LEFT JOIN board_tags bt ON b.id = bt.board_id
    LEFT JOIN tags t ON bt.tag_id = t.id
    WHERE b.user_id = ?
    GROUP BY b.id
    ORDER BY b.id ASC
');
$stmt->execute([$userId]);
$boards = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($boards);