<?php require 'db.php';
require_login();
$user = current_user();

$stmt = $db->prepare("
    SELECT v.*, 
           (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id AND l.is_like = 1) AS likes_count,
           (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id AND l.is_like = 0) AS dislikes_count
    FROM videos v
    WHERE v.user_id = ?
    ORDER BY (likes_count + dislikes_count) DESC, v.created_at DESC
");
$stmt->execute([$user['id']]);
$videos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Мои видео</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="logo">Видеохостинг</div><nav><a href="index.php">Главная</a><a href="upload.php">Загрузить</a><a href="logout.php">Выход</a></nav></header>
<div class="container">
    <h1>Мои видео</h1>
    <?php if (!$videos): ?>
        <p>Вы ещё не загрузили ни одного видео.</p>
    <?php else: ?>
        <div class="video-grid">
        <?php foreach ($videos as $v): ?>
            <div class="video-item">
                <h3><a href="video.php?id=<?= $v['id'] ?>"><?= e($v['title']) ?></a></h3>
                <div class="meta"><?= e($v['created_at']) ?></div>
                <div class="meta">Лайки: <?= $v['likes_count'] ?> · Дизлайки: <?= $v['dislikes_count'] ?></div>
                <div class="meta">Статус: <span class="status status-<?= e($v['restriction']) ?>"><?= e($v['restriction']) ?></span></div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>