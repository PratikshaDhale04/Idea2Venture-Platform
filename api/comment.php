<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$idea_id = (int)$data['idea_id'];
$comment = sanitize($data['comment']);

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO comments (user_id, idea_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $_SESSION['user_id'], $idea_id, $comment);

if ($stmt->execute()) {
    $comment_id = $stmt->insert_id;
    $comment_data = $conn->query("SELECT c.*, u.name as user_name FROM comments c 
        JOIN users u ON c.user_id = u.id WHERE c.id = $comment_id")->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment added!',
        'comment' => [
            'id' => $comment_data['id'],
            'user_name' => $comment_data['user_name'],
            'comment' => $comment_data['comment'],
            'time_ago' => timeAgo($comment_data['created_at'])
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
}

function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}
?>