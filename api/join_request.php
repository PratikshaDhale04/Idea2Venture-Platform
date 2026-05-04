<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$idea_id = (int)$data['idea_id'];

// Check if already requested
$check = $conn->query("SELECT id, status FROM join_requests WHERE idea_id = $idea_id AND user_id = " . $_SESSION['user_id']);

if ($check->num_rows > 0) {
    $existing = $check->fetch_assoc();
    echo json_encode(['success' => false, 'message' => 'You already have a ' . $existing['status'] . ' request']);
    exit;
}

// Can't join own idea
$idea = $conn->query("SELECT user_id FROM ideas WHERE id = $idea_id")->fetch_assoc();
if ($idea['user_id'] == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot join your own idea']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO join_requests (idea_id, user_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param('ii', $idea_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request sent! The owner will review your request.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send request']);
}
?>