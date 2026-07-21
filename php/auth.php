<?php
session_start();

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    // usersテーブルからid:1のユーザーを取得
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = 1');
    $stmt->execute();
    $user = $stmt->fetch();

    // パスワードを照合
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: ../home.php');
        exit;
    } else {
        $error = 'パスワードが正しくありません';
    }
}
?>