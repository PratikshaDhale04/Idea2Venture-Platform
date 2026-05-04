<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$receiver_id = (int)$_GET['receiver_id'];
$user_id = $_SESSION['user_id'];

$messages = $conn->query("SELECT * FROM messages 
    WHERE (sender_id = $user_id AND receiver_id = $receiver_id) 
       OR (sender_id = $receiver_id AND receiver_id = $user_id)
    ORDER BY created_at ASC");

$messages_array = [];
while ($msg = $messages->fetch_assoc()) {
    $messages_array[] = [
        'id' => $msg['id'],
        'sender_id' => $msg['sender_id'],
        'receiver_id' => $msg['receiver_id'],
        'message' => $msg['message'],
        'created_at' => $msg['created_at'],
        'time_ago' => timeAgo($msg['created_at'])
    ];
}

echo json_encode(['success' => true, 'messages' => $messages_array]);

function timeAgo($timestamp) {
    if (!$timestamp) return '';
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
?>