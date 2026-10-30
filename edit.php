<?php
include 'config.php';

if (!canEdit()) {
    header('Location: login.php');
    exit;
}

$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $mysqli->prepare("SELECT * FROM pages WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $page_id);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$page) {
    die("Страница не найдена");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $image_path = $page['image'];

    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        $image_path = null;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if ($image_path && file_exists($image_path)) {
                unlink($image_path);
            }

            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            } else {
                $error = 'Ошибка загрузки файла';
            }
        } else {
            $error = 'Разрешены только JPG, PNG, GIF, WEBP';
        }
    }

    if (empty($title) || empty($content)) {
        $error = 'Заполните все поля';
    } elseif (!$error) {
        $user_id     = (int)$_SESSION['user_id'];
        $old_content = $page['content'];

        $stmt = $mysqli->prepare("INSERT INTO page_revisions (page_id, content, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param('isi', $page_id, $old_content, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE pages SET title = ?, content = ?, image = ? WHERE id = ?");
        $stmt->bind_param('sssi', $title, $content, $image_path, $page_id);
        $stmt->execute();
        $stmt->close();

        $success = "Страница обновлена! <a href='page.php?id=$page_id'>Перейти к странице</a>";

        $page['title']   = $title;
        $page['content'] = $content;
        $page['image']   = $image_path;
    }
}

$page_title = "Редактировать: {$page['title']}";
include 'header.php';
?>

<h2>Редактировать: <?php echo htmlspecialchars($page['title']); ?></h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" class="wiki-form" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Название</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($page['title']); ?>" required>
    </div>

    <div class="form-group">
        <label for="image">Изображение</label>

        <?php if ($page['image'] && file_exists($page['image'])): ?>
            <div class="current-image">
                <p>Текущее изображение:</p>
                <img src="<?php echo htmlspecialchars($page['image']); ?>" style="max-width: 150px; border-radius: 8px;">
                <label>
                    <input type="checkbox" name="delete_image" value="1"> Удалить изображение
                </label>
            </div>
        <?php endif; ?>

        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        <small>Оставьте пустым, чтобы сохранить текущее изображение</small>
    </div>

    <div class="form-group">
        <label for="content">Содержание</label>
        <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($page['content']); ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    <a href="page.php?id=<?php echo (int)$page_id; ?>" class="btn">Отмена</a>
</form>

<?php include 'footer.php'; ?>