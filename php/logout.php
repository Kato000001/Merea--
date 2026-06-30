<?php
session_start();

// セッションを完全に破棄する
$_SESSION = [];
session_destroy();

// ログイン画面へリダイレクト
header('Location: ../login.php');
exit;