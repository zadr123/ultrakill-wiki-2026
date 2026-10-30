<?php
include 'config.php';
$page_title = 'Создать статью';

if (!canEdit()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
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

    if (empty($title) || empty($content) || $category_id <= 0) {
        $error = 'Заполните все обязательные поля';
    } elseif (!$error) {
        $author_id = (int)$_SESSION['user_id'];

        $stmt = $mysqli->prepare("INSERT INTO pages (title, content, category_id, author_id, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiis', $title, $content, $category_id, $author_id, $image_path);

        if ($stmt->execute()) {
            $page_id = $mysqli->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare("INSERT INTO page_revisions (page_id, content, user_id) VALUES (?, ?, ?)");
            $stmt->bind_param('isi', $page_id, $content, $author_id);
            $stmt->execute();
            $stmt->close();

            $success = "Страница создана! <a href='page.php?id=$page_id'>Перейти к странице</a>";
        } else {
            $error = 'Ошибка создания страницы';
        }
    }
}

$categories = getCategories();
include 'header.php';
?>

<h2>Создать новую статью</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" class="wiki-form" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Название статьи</label>
        <input type="text" id="title" name="title" required>
    </div>

    <div class="form-group">
        <label for="category_id">Категория</label>
        <select id="category_id" name="category_id" required>
            <option value="">Выберите...</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Изображение (необязательно)</label>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        <small>Рекомендуемый размер: 300x300px. Будет отображаться справа от текста.</small>
    </div>

    <div class="form-group">
        <label for="content">Содержание</label>
        <textarea id="content" name="content" rows="15" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Сохранить</button>
</form>

<?php include 'footer.php'; ?>