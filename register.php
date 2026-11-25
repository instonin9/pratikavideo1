<?php require 'db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '') $errors[] = 'Укажите никнейм.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email.';
    if ($password === '') $errors[] = 'Укажите пароль.';
    if ($password !== $password2) $errors[] = 'Пароли не совпадают.';

    if (!$errors) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким никнеймом или email уже существует.';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Регистрация</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="logo">Видеохостинг</div><nav><a href="index.php">Главная</a><a href="login.php">Вход</a></nav></header>
<div class="container">
    <div class="card" style="max-width:480px;margin:0 auto;">
        <h1>Регистрация</h1>
        <?php foreach ($errors as $e): ?><div class="error"><?= e($e) ?></div><?php endforeach; ?>
        <form method="post">
            <label>Никнейм<input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>"></label>
            <label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>"></label>
            <label>Пароль<input type="password" name="password"></label>
            <label>Повтор пароля<input type="password" name="password2"></label>
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</div>
</body>
</html>