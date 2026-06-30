<?php
// データベース接続設定
define('DB_HOST', 'localhost');
define('DB_NAME', 'clip');       // 作成したDBの名前
define('DB_USER', 'root');       // Laragonのデフォルト
define('DB_PASS', '');           // Laragonのデフォルトは空

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('DB接続エラー: ' . $e->getMessage());
}