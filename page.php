<?php
include 'config.php';

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id']) || (int)$_GET['id'] <= 0) {
    $page_title = 'Страница не найдена';
    include 'header.php';
    ?>
    <div class="empty-category">
        <h2>Страница не найдена</h2>
        <a href="index.php" class="btn">Вернуться на главную</a>
    </div>
    <?php
    include 'footer.php';
    exit;
}

$page_id = (int)$_GET['id'];

if (isset($_POST['delete']) && canDelete()) {
    $stmt = $mysqli->prepare("SELECT image FROM pages WHERE id = ?");
    $stmt->bind_param('i', $page_id);
    $stmt->execute();
    $img = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($img && $img['image'] && file_exists($img['image'])) {
        unlink($img['image']);
    }

    $stmt = $mysqli->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->bind_param('i', $page_id);
    $stmt->execute();
    $stmt->close();

    header('Location: index.php?deleted=1');
    exit;
}

$stmt = $mysqli->prepare("
    SELECT p.*, c.name as category_name, u.username as author_name
    FROM pages p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->bind_param('i', $page_id);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$page) {
    $page_title = 'Страница не найдена';
    include 'header.php';
    ?>
    <div class="empty-category">
        <h2>Страница не найдена</h2>
        <a href="index.php" class="btn">Вернуться на главную</a>
    </div>
    <?php
    include 'footer.php';
    exit;
}

$upd = $mysqli->prepare("UPDATE pages SET views = views + 1 WHERE id = ?");
$upd->bind_param('i', $page_id);
$upd->execute();
$upd->close();

$page_title = $page['title'];
include 'header.php';
?>

<article class="wiki-page">
    <h2><?php echo htmlspecialchars($page['title']); ?></h2>

    <div class="page-meta">
        <span class="category"><?php echo htmlspecialchars($page['category_name']); ?></span>
        <?php if ($page['author_name']): ?>
            <span class="author"><?php echo htmlspecialchars($page['author_name']); ?></span>
        <?php endif; ?>
        <span class="views"><?php echo (int)$page['views']; ?> просмотров</span>
    </div>

    <div class="page-with-image">
        <?php if ($page['image'] && file_exists($page['image'])): ?>
            <div class="page-image">
                <img src="<?php echo htmlspecialchars($page['image']); ?>" alt="<?php echo htmlspecialchars($page['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="page-content">
            <?php echo nl2br(htmlspecialchars($page['content'])); ?>
        </div>
    </div>

    <div class="page-actions">
        <?php if (canEdit()): ?>
            <a href="edit.php?id=<?php echo (int)$page['id']; ?>" class="btn">Редактировать</a>
        <?php endif; ?>

        <a href="history.php?id=<?php echo (int)$page['id']; ?>" class="btn">История</a>

        <?php if (canDelete()): ?>
            <form method="POST" style="display: inline;" onsubmit="return confirm('Точно удалить статью? Восстановить будет нельзя');">
                <button type="submit" name="delete" class="btn btn-danger">Удалить</button>
            </form>
        <?php endif; ?>
    </div>
</article>

<?php include 'footer.php'; ?>