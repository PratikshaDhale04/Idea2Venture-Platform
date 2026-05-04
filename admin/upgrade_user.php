<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$id = (int)$_GET['id'];
$action = $_GET['action'] ?? '';

if ($action === 'upgrade') {
    $new_role = $_GET['role'] ?? 'idea_owner';
    if (in_array($new_role, ['idea_owner', 'admin'])) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param('si', $new_role, $id);
        $stmt->execute();
        $_SESSION['role'] = $new_role;
    }
    redirect('../dashboard.php');
}

if ($action === 'downgrade') {
    $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $_SESSION['role'] = 'user';
    redirect('../dashboard.php');
}

redirect('../index.php');
?>