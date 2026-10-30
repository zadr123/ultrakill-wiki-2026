<?php
include 'config.php';
$page_title = 'Главная';

$deleted = isset($_GET['deleted']);

include 'header.php';

$result = $mysqli->query("
    SELECT p.*, c.name as category_name
    FROM pages p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$pages = $result->fetch_all(MYSQLI_ASSOC);

$categories_list = getCategories();
$pages_by_category = [];
foreach ($pages as $page) {
    $pages_by_category[$page['category_name']][] = $page;
}
?>

<h2>Все статьи</h2>

<?php if ($deleted): ?>
    <div class="success-delete">Статья успешно удалена</div>
<?php endif; ?>

<?php foreach ($categories_list as $cat): ?>
    <div class="category-section">
        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
        <?php if (isset($pages_by_category[$cat['name']])): ?>
            <ul class="page-list">
                <?php foreach ($pages_by_category[$cat['name']] as $page): ?>
                    <li>
                        <a href="page.php?id=<?php echo (int)$page['id']; ?>">
                            <?php echo htmlspecialchars($page['title']); ?>
                        </a>
                        <span class="views"> <?php echo (int)$page['views']; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">Нет статей в этой категории</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>