<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$receiver_id = (int)$data['receiver_id'];
$message = sanitize($data['message']);

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $_SESSION['user_id'], $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>