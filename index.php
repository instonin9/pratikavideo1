<?php require 'db.php';
$user = current_user();

// последние 10 без ограничений
$videos = $db->query("
    SELECT v.*, u.username,
           (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id AND l.is_like = 1) AS likes_count,
           (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id AND l.is_like = 0) AS dislikes_count
    FROM videos v
    JOIN users u ON u.id = v.user_id
    WHERE v.restriction = 'none'
    ORDER BY v.created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Видеохостинг — главная</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="logo">Видеохостинг</div>
    <nav>
        <a href="index.php">Главная</a>
        <?php if ($user): ?>
            <a href="my_videos.php">Мои видео</a>
            <a href="upload.php">Загрузить</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="admin.php">Админ-панель</a>
            <?php endif; ?>
            <a href="logout.php">Выход (<?= e($user['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container">
    <h1>Последние видео</h1>
    <?php if (!$videos): ?>
        <p>Пока нет видео.</p>
    <?php else: ?>
        <div class="video-grid">
        <?php foreach ($videos as $v): ?>
            <div class="video-item">
                <h3><a href="video.php?id=<?= $v['id'] ?>"><?= e($v['title']) ?></a></h3>
                <div class="meta">Автор: <?= e($v['username']) ?> · <?= e($v['created_at']) ?></div>
                <div class="meta">Лайки: <?= $v['likes_count'] ?> · Дизлайки: <?= $v['dislikes_count'] ?></div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>