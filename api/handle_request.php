<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$request_id = (int)$data['request_id'];
$action = $data['action'];

// Get request info
$request = $conn->query("SELECT jr.*, i.user_id as owner_id FROM join_requests jr 
    JOIN ideas i ON jr.idea_id = i.id 
    WHERE jr.id = $request_id")->fetch_assoc();

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

// Only owner or admin can handle request
if ($request['owner_id'] != $_SESSION['user_id'] && !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$status = $action === 'accepted' ? 'accepted' : 'rejected';
$stmt = $conn->prepare("UPDATE join_requests SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $request_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request ' . $status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update request']);
}
?>