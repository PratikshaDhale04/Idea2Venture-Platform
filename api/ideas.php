<?php
require_once '../config/database.php';

$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$where = "1=1";
if ($search) $where .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%')";
if ($category) $where .= " AND i.category = '$category'";

$ideas = $conn->query("SELECT i.*, u.name as owner_name,
    (SELECT COUNT(*) FROM likes WHERE idea_id = i.id) as likes_count
    FROM ideas i 
    JOIN users u ON i.user_id = u.id 
    WHERE $where
    ORDER BY i.created_at DESC");

$ideas_array = [];
while ($idea = $ideas->fetch_assoc()) {
    $liked = false;
    if (isLoggedIn()) {
        $checkLike = $conn->query("SELECT id FROM likes WHERE user_id = " . $_SESSION['user_id'] . " AND idea_id = " . $idea['id']);
        $liked = $checkLike->num_rows > 0;
    }
    
    $ideas_array[] = [
        'id' => $idea['id'],
        'title' => $idea['title'],
        'description' => $idea['description'],
        'category' => $idea['category'],
        'skills_required' => $idea['skills_required'],
        'status' => $idea['status'],
        'owner_name' => $idea['owner_name'],
        'likes_count' => $idea['likes_count'],
        'liked' => $liked
    ];
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'ideas' => $ideas_array]);
?>