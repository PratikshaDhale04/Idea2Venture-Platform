<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'idea2venture';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isIdeaOwner() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['idea_owner', 'admin']);
}

function hasRole($required_role) {
    if (!isset($_SESSION['role'])) return false;
    if ($_SESSION['role'] === 'admin') return true;
    if ($_SESSION['role'] === 'idea_owner') {
        return in_array($required_role, ['idea_owner', 'user']);
    }
    return $_SESSION['role'] === $required_role;
}

function requireRole($role) {
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Access denied';
        redirect('index.php');
    }
}

function canCreateIdea() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['idea_owner', 'admin', 'user']);
}

function canEditIdea($idea_owner_id) {
    return hasRole('admin') || ($_SESSION['user_id'] == $idea_owner_id && hasRole('idea_owner'));
}

function canDeleteIdea($idea_owner_id) {
    return hasRole('admin');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    global $conn;
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>