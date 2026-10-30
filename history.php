<?php
include 'config.php';

$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $mysqli->prepare("SELECT title FROM pages WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $page_id);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$page) {
    die("Страница не найдена");
}

$stmt = $mysqli->prepare("
    SELECT r.*, u.username
    FROM page_revisions r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.page_id = ?
    ORDER BY r.timestamp DESC
");
$stmt->bind_param('i', $page_id);
$stmt->execute();
$revisions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = "История: {$page['title']}";
include 'header.php';
?>

<h2>История правок: <?php echo htmlspecialchars($page['title']); ?></h2>

<?php if (empty($revisions)): ?>
    <p>Нет сохранённых версий</p>
<?php else: ?>
    <table class="history-table">
        <tr><th>Дата</th><th>Пользователь</th><th>Действие</th></tr>
        <?php foreach ($revisions as $rev): ?>
            <tr>
                <td><?php echo htmlspecialchars($rev['timestamp']); ?></td>
                <td><?php echo htmlspecialchars($rev['username'] ?? 'unknown'); ?></td>
                <td><a href="page.php?id=<?php echo (int)$page_id; ?>&rev=<?php echo (int)$rev['id']; ?>">Просмотреть эту версию</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="page.php?id=<?php echo (int)$page_id; ?>" class="btn">← Вернуться к странице</a>

<?php include 'footer.php'; ?>