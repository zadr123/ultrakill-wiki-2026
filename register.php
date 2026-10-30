<?php
include 'config.php';
$page_title = 'Регистрация';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username'] ?? '');
    $password         = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $email            = trim($_POST['email'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Заполните обязательные поля (логин и пароль)';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Логин: 3–30 символов, латиница, цифры и знак _';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email';
    } elseif (strlen($password) < 4) {
        $error = 'Пароль должен быть не менее 4 символов';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR (email != '' AND email = ?) LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Пользователь с таким логином или email уже существует';
        }
        $stmt->close();

        if (empty($error)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $mysqli->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, 'reader')");
            $stmt->bind_param('sss', $username, $password_hash, $email);

            if ($stmt->execute()) {
                $success = 'Регистрация успешна! <a href="login.php">Войти</a>';
            } else {
                $error = 'Ошибка регистрации';
            }
            $stmt->close();
        }
    }
}

include 'header.php';
?>

<h2>Регистрация</h2>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" class="wiki-form">
    <div class="form-group">
        <label for="username">Логин *</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
        <label for="email">Email (необязательно)</label>
        <input type="email" id="email" name="email">
    </div>

    <div class="form-group">
        <label for="password">Пароль *</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
        <label for="confirm_password">Подтвердите пароль *</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>

    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    <a href="login.php" class="btn">Уже есть аккаунт? Войти</a>
</form>

<?php include 'footer.php'; ?>