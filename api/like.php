<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$idea_id = (int)$data['idea_id'];

$check = $conn->query("SELECT id FROM likes WHERE user_id = " . $_SESSION['user_id'] . " AND idea_id = $idea_id");

if ($check->num_rows > 0) {
    $conn->query("DELETE FROM likes WHERE user_id = " . $_SESSION['user_id'] . " AND idea_id = $idea_id");
    $message = 'Like removed';
} else {
    $conn->query("INSERT INTO likes (user_id, idea_id) VALUES (" . $_SESSION['user_id'] . ", $idea_id)");
    $message = 'Liked!';
}

$likes = $conn->query("SELECT COUNT(*) as count FROM likes WHERE idea_id = $idea_id")->fetch_assoc()['count'];

echo json_encode(['success' => true, 'message' => $message, 'likes' => $likes]);
?>