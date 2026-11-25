<?php require 'db.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') { http_response_code(403); echo 'Доступ запрещён'; exit; }

// Смена статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id'], $_POST['restriction'])) {
    $allowed = ['none', 'violation', 'shadow', 'ban'];
    if (in_array($_POST['restriction'], $allowed, true)) {
        $stmt = $db->prepare("UPDATE videos SET restriction = ? WHERE id = ?");
        $stmt->execute([$_POST['restriction'], (int)$_POST['video_id']]);
    }
    header('Location: admin.php'); exit;
}

$videos = $db->query("
    SELECT v.*, u.username FROM videos v JOIN users u ON u.id = v.user_id
    ORDER BY v.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Админ-панель</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="logo">Видеохостинг</div><nav><a href="index.php">Главная</a><a href="logout.php">Выход</a></nav></header>
<div class="container">
    <h1>Админ-панель</h1>
    <div class="card">
        <table style="width:100%;border-collapse:collapse;">
            <tr><th align="left">ID</th><th align="left">Название</th><th align="left">Автор</th><th align="left">Статус</th><th align="left">Действие</th></tr>
            <?php foreach ($videos as $v): ?>
            <tr>
                <td><?= $v['id'] ?></td>
                <td><a href="video.php?id=<?= $v['id'] ?>"><?= e($v['title']) ?></a></td>
                <td><?= e($v['username']) ?></td>
                <td><span class="status status-<?= e($v['restriction']) ?>"><?= e($v['restriction']) ?></span></td>
                <td>
                    <form method="post" style="display:flex;gap:6px;">
                        <input type="hidden" name="video_id" value="<?= $v['id'] ?>">
                        <select name="restriction">
                            <option value="none" <?= $v['restriction']=='none'?'selected':'' ?>>Нет ограничений</option>
                            <option value="violation" <?= $v['restriction']=='violation'?'selected':'' ?>>Нарушение</option>
                            <option value="shadow" <?= $v['restriction']=='shadow'?'selected':'' ?>>Теневой бан</option>
                            <option value="ban" <?= $v['restriction']=='ban'?'selected':'' ?>>Бан</option>
                        </select>
                        <button type="submit">Применить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>