<?php require 'db.php';
$user = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT v.*, u.username,
           (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id AND l.is_like = 1) AS likes_count,
           (SELECT COUNT(*) FROM likes l WHERE l.video_id = v.id AND l.is_like = 0) AS dislikes_count
    FROM videos v JOIN users u ON u.id = v.user_id
    WHERE v.id = ?
");
$stmt->execute([$id]);
$video = $stmt->fetch();

if (!$video) { http_response_code(404); echo 'Видео не найдено'; exit; }

// Ограничения видимости
if ($video['restriction'] === 'ban' && (!$user || $user['role'] !== 'admin')) {
    echo 'Видео недоступно'; exit;
}
if ($video['restriction'] === 'violation' && (!$user || $user['id'] != $video['user_id'])) {
    echo 'Видео недоступно'; exit;
}

// Лайк / дизлайк
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like']) && $user) {
    $isLike = $_POST['like'] === '1' ? 1 : 0;
    $stmt = $db->prepare("INSERT INTO likes (video_id, user_id, is_like) VALUES (?, ?, ?)
        ON CONFLICT(video_id, user_id) DO UPDATE SET is_like = excluded.is_like");
    $stmt->execute([$id, $user['id'], $isLike]);
    header('Location: video.php?id=' . $id); exit;
}

// Комментарий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $user) {
    $text = trim($_POST['comment']);
    if ($text !== '') {
        $stmt = $db->prepare("INSERT INTO comments (video_id, user_id, text) VALUES (?, ?, ?)");
        $stmt->execute([$id, $user['id'], $text]);
    }
    header('Location: video.php?id=' . $id); exit;
}

$comments = $db->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON u.id = c.user_id WHERE c.video_id = ? ORDER BY c.created_at DESC");
$comments->execute([$id]);
$comments = $comments->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title><?= e($video['title']) ?></title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="logo">Видеохостинг</div><nav><a href="index.php">Главная</a><?php if ($user): ?><a href="my_videos.php">Мои видео</a><a href="logout.php">Выход</a><?php else: ?><a href="login.php">Вход</a><?php endif; ?></nav></header>
<div class="container">
    <div class="card">
        <h1><?= e($video['title']) ?></h1>
        <div class="meta">Автор: <?= e($video['username']) ?> · <?= e($video['created_at']) ?></div>
        <video controls src="<?= e($video['file_path']) ?>"></video>
        <p><?= nl2br(e($video['description'])) ?></p>
        <div class="meta">Лайки: <?= $video['likes_count'] ?> · Дизлайки: <?= $video['dislikes_count'] ?></div>
        <?php if ($user): ?>
            <form method="post" class="inline-form">
                <button name="like" value="1">Лайк</button>
            </form>
            <form method="post" class="inline-form">
                <button name="like" value="0" class="btn-secondary">Дизлайк</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Комментарии</h2>
        <?php if ($user): ?>
            <form method="post">
                <textarea name="comment" rows="2" placeholder="Ваш комментарий..."></textarea>
                <button type="submit">Отправить</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Войдите</a>, чтобы оставить комментарий.</p>
        <?php endif; ?>
        <?php foreach ($comments as $c): ?>
            <div class="comment">
                <div class="author"><?= e($c['username']) ?> <span class="meta"><?= e($c['created_at']) ?></span></div>
                <div><?= nl2br(e($c['text'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>