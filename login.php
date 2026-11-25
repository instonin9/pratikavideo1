<?php require 'db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный email или пароль.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Вход</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="logo">Видеохостинг</div><nav><a href="index.php">Главная</a><a href="register.php">Регистрация</a></nav></header>
<div class="container">
    <div class="card" style="max-width:480px;margin:0 auto;">
        <h1>Вход</h1>
        <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>"></label>
            <label>Пароль<input type="password" name="password"></label>
            <button type="submit">Войти</button>
        </form>
    </div>
</div>
</body>
</html>