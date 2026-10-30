<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultrakill Wiki<?php echo isset($page_title) ? " - $page_title" : ''; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="index.php">ULTRAKILL WIKI</a></h1>
            <nav>
                <a href="index.php">Главная</a>
                <a href="category.php?type=weapon">Оружие</a>
                <a href="category.php?type=enemy">Враги</a>
                <a href="category.php?type=level">Уровни</a>
                <a href="category.php?type=lore">История</a>
                <a href="create.php" class="create-btn">+ Создать статью</a>
                
                <?php if (isLoggedIn()): ?>
                    <span class="user-info">вы вошли как <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <?php if (isAdmin()): ?>
                        <span class="admin-badge">(ADMIN)</span>
                    <?php endif; ?>
                    <a href="logout.php">Выйти</a>
                <?php else: ?>
                    <a href="login.php">Войти</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">