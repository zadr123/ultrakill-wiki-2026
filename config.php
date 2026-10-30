<?php
session_start();

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'ultrakill_wiki';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    die('Ошибка подключения к базе данных: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

function getCategories() {
    global $mysqli;
    $result = $mysqli->query("SELECT * FROM categories ORDER BY name");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCategoryName($category_id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? $row['name'] : 'unknown';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function canEdit() {
    return isLoggedIn();
}

function canDelete() {
    return isAdmin();
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['role']
        ];
    }
    return null;
}