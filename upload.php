<?php require 'db.php';
require_login();
$user = current_user();
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);

    if ($title === '') $errors[] = 'Укажите название.';
    if ($categoryId <= 0) $errors[] = 'Выберите категорию.';
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Загрузите видеофайл.';
    } else {
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'mp4') $errors[] = 'Допустим только формат mp4.';
    }

    if (!$errors) {
        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.mp4';
        $target = __DIR__ . '/uploads/' . $filename;
        move_uploaded_file($_FILES['video']['tmp_name'], $target);
        $stmt = $db->prepare("INSERT INTO videos (user_id, category_id, title, description, file_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $categoryId, $title, $description, 'uploads/' . $filename]);
        header('Location: my_videos.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"><title>Загрузка видео</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="logo">Видеохостинг</div><nav><a href="index.php">Главная</a><a href="my_videos.php">Мои видео</a><a href="logout.php">Выход</a></nav></header>
<div class="container">
    <div class="card" style="max-width:600px;margin:0 auto;">
        <h1>Загрузить видео</h1>
        <?php foreach ($errors as $e): ?><div class="error"><?= e($e) ?></div><?php endforeach; ?>
        <form method="post" enctype="multipart/form-data">
            <label>Название<input type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>"></label>
            <label>Описание<textarea name="description" rows="3"><?= e($_POST['description'] ?? '') ?></textarea></label>
            <label>Категория
                <select name="category_id">
                    <option value="">-- выберите --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Видеофайл (.mp4)<input type="file" name="video" accept="video/mp4"></label>
            <button type="submit">Загрузить</button>
        </form>
    </div>
</div>
</body>
</html>