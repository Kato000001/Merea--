<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merea - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">

    <div class="login-wrapper">
        <h1 class="login-logo">Merea</h1>

        <!-- actionをlogin.phpに、methodをPOSTに変更 -->
        <form class="login-form" action="php/auth.php" method="POST">
            <input
                type="password"
                name="password"
                placeholder="パスワードを入力"
                autofocus
            >
            <button type="submit">ログイン</button>
        </form>

        <!-- PHPのエラーメッセージを表示 -->
        <?php if (isset($error)) : ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

    </div>

</body>
</html>