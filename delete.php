<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$type = $_GET['type'] ?? '';
$id = (int)$_GET['id'];

switch ($type) {
    case 'idea':
        $idea = $conn->query("SELECT user_id FROM ideas WHERE id = $id")->fetch_assoc();
        if ($idea && ($idea['user_id'] == $_SESSION['user_id'] || isAdmin())) {
            $conn->query("DELETE FROM ideas WHERE id = $id");
        }
        redirect('dashboard.php');
        break;
        
    case 'comment':
        $comment = $conn->query("SELECT user_id, idea_id FROM comments WHERE id = $id")->fetch_assoc();
        if ($comment && ($comment['user_id'] == $_SESSION['user_id'] || isAdmin())) {
            $conn->query("DELETE FROM comments WHERE id = $id");
        }
        $back = $_GET['back'] ?? 'ideas.php';
        redirect($back);
        break;
        
    case 'user':
        if (isAdmin() && $id != $_SESSION['user_id']) {
            $conn->query("DELETE FROM users WHERE id = $id");
            redirect('admin/users.php');
        }
        redirect('index.php');
        break;
        
    default:
        redirect('index.php');
}
?>