<?php
include 'config.php';

$category_type = isset($_GET['type']) ? $_GET['type'] : '';

$category_map = [
    'weapon' => 'weapon',
    'enemy'  => 'enemy',
    'level'  => 'level',
    'lore'   => 'lore'
];

$category_titles = [
    'weapon' => 'Оружие',
    'enemy'  => 'Враги',
    'level'  => 'Уровни',
    'lore'   => 'История'
];

if (!isset($category_map[$category_type])) {
    die("Категория не найдена");
}

$category_name = $category_map[$category_type];
$page_title    = $category_titles[$category_type];
include 'header.php';

$stmt = $mysqli->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
$stmt->bind_param('s', $category_name);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$category) {
    echo "<p>Категория не найдена</p>";
    include 'footer.php';
    exit;
}

$category_id = (int)$category['id'];

$stmt = $mysqli->prepare("
    SELECT p.*, u.username as author_name
    FROM pages p
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.category_id = ?
    ORDER BY p.title ASC
");
$stmt->bind_param('i', $category_id);
$stmt->execute();
$pages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<h2><?php echo htmlspecialchars($page_title); ?></h2>

<?php if (empty($pages)): ?>
    <div class="empty-category">
        <p>В этой категории пока нет статей.</p>
        <a href="create.php" class="btn">Создать первую статью</a>
    </div>
<?php else: ?>
    <div class="category-page-list">
        <?php foreach ($pages as $page): ?>
            <div class="category-item">
                <?php if ($page['image'] && file_exists($page['image'])): ?>
                    <div class="category-item-image">
                        <img src="<?php echo htmlspecialchars($page['image']); ?>" alt="<?php echo htmlspecialchars($page['title']); ?>">
                    </div>
                <?php endif; ?>
                <div class="category-item-content">
                    <h3>
                        <a href="page.php?id=<?php echo (int)$page['id']; ?>">
                            <?php echo htmlspecialchars($page['title']); ?>
                        </a>
                    </h3>
                    <?php if ($page['author_name']): ?>
                        <div class="item-meta">Автор: <?php echo htmlspecialchars($page['author_name']); ?></div>
                    <?php endif; ?>
                    <div class="item-excerpt">
                        <?php
                        $excerpt = strip_tags($page['content']);
                        $excerpt = mb_substr($excerpt, 0, 150);
                        echo htmlspecialchars($excerpt) . '...';
                        ?>
                    </div>
                    <div class="item-footer">
                        <span class="views"><?php echo (int)$page['views']; ?> просмотров</span>
                        <a href="page.php?id=<?php echo (int)$page['id']; ?>" class="read-more">Читать →</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>