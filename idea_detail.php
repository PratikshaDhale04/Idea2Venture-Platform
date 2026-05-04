<?php
require_once 'config/database.php';

$id = (int)$_GET['id'];
$idea = $conn->query("SELECT i.*, u.name as owner_name, u.id as owner_id, u.skills as owner_skills, u.bio as owner_bio,
    (SELECT COUNT(*) FROM likes WHERE idea_id = i.id) as likes_count
    FROM ideas i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.id = $id")->fetch_assoc();

if (!$idea) {
    redirect('ideas.php');
}

$liked = false;
$has_request = false;
if (isLoggedIn()) {
    $checkLike = $conn->query("SELECT id FROM likes WHERE user_id = " . $_SESSION['user_id'] . " AND idea_id = $id");
    $liked = $checkLike->num_rows > 0;
    
    $checkRequest = $conn->query("SELECT id, status FROM join_requests WHERE idea_id = $id AND user_id = " . $_SESSION['user_id']);
    if ($checkRequest->num_rows > 0) {
        $has_request = $checkRequest->fetch_assoc();
    }
}

// Get team members
$team = $conn->query("SELECT u.id, u.name, u.skills FROM join_requests jr 
    JOIN users u ON jr.user_id = u.id 
    WHERE jr.idea_id = $id AND jr.status = 'accepted'");

// Get comments
$comments = $conn->query("SELECT c.*, u.name as user_name FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.idea_id = $id ORDER BY c.created_at DESC");

// Add comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment']) && isLoggedIn()) {
    $comment = sanitize($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, idea_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $_SESSION['user_id'], $id, $comment);
        $stmt->execute();
        header("Location: idea_detail.php?id=$id");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($idea['title']); ?> - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="glass-card p-4 mb-4 fade-in">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <span class="idea-category"><?php echo htmlspecialchars($idea['category']); ?></span>
                            <span class="idea-status status-<?php echo $idea['status']; ?> ms-2"><?php echo ucfirst($idea['status']); ?></span>
                        </div>
                        
                        <?php if (isLoggedIn() && ($_SESSION['user_id'] == $idea['owner_id'] || isAdmin())): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="edit_idea.php?id=<?php echo $id; ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                <li><button class="dropdown-item text-danger" onclick="confirmDelete('idea', <?php echo $id; ?>)"><i class="bi bi-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <h2 class="fw-bold mb-4"><?php echo htmlspecialchars($idea['title']); ?></h2>
                    
                    <div class="mb-4">
                        <h5 class="fw-bold">Description</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($idea['description'])); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="fw-bold">Required Skills</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($idea['skills_required'] ?: 'No specific skills required'); ?></p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3 border-top pt-4">
                        <button class="like-btn <?php echo $liked ? 'liked' : ''; ?>" data-idea-id="<?php echo $id; ?>" <?php echo isLoggedIn() ? '' : 'onclick="showToast(\'Please login to like\', \'error\')"'; ?>>
                            <i class="bi <?php echo $liked ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                        </button>
                        <span class="text-muted"><?php echo $idea['likes_count']; ?> likes</span>
                        
                        <?php if (isLoggedIn() && $_SESSION['user_id'] != $idea['owner_id']): ?>
                            <?php if ($has_request): ?>
                                <span class="badge-status badge-<?php echo $has_request['status']; ?> ms-auto">
                                    Request <?php echo ucfirst($has_request['status']); ?>
                                </span>
                            <?php else: ?>
                                <button onclick="sendJoinRequest(<?php echo $id; ?>)" class="btn btn-gradient btn-sm ms-auto join-request-btn" data-idea-id="<?php echo $id; ?>">
                                    <i class="bi bi-person-plus me-2"></i>Request to Join
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="glass-card p-4 fade-in">
                    <h5 class="fw-bold mb-4"><i class="bi bi-chat me-2"></i>Comments</h5>
                    
                    <?php if (isLoggedIn()): ?>
                    <form method="POST" class="mb-4">
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" name="comment" placeholder="Write a comment..." required>
                            <button type="submit" name="add_comment" class="btn btn-gradient">Post</button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <div class="comments-list">
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="comment-item">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="comment-avatar" style="width: 35px; height: 35px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        <?php echo strtoupper($comment['user_name'][0]); ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                    <small class="text-muted"><?php echo timeAgo($comment['created_at']); ?></small>
                                </div>
                                <p class="mb-0"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                
                                <?php if (isLoggedIn() && ($_SESSION['user_id'] == $comment['user_id'] || isAdmin())): ?>
                                <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="btn btn-sm btn-link text-danger">Delete</button>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Owner Info -->
                <div class="glass-card p-4 mb-4 fade-in">
                    <h5 class="fw-bold mb-4"><i class="bi bi-person me-2"></i>Idea Owner</h5>
                    <a href="profile.php?id=<?php echo $idea['owner_id']; ?>" class="text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="profile-avatar-sm" style="width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 700;">
                                <?php echo strtoupper($idea['owner_name'][0]); ?>
                            </div>
                            <div>
                                <h6 class="mb-0 text-dark"><?php echo htmlspecialchars($idea['owner_name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($idea['owner_skills'] ?: 'No skills listed'); ?></small>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Team Members -->
                <div class="glass-card p-4 fade-in">
                    <h5 class="fw-bold mb-4"><i class="bi bi-people me-2"></i>Team Members</h5>
                    
                    <?php if ($team->num_rows > 0): ?>
                        <?php while ($member = $team->fetch_assoc()): ?>
                        <a href="profile.php?id=<?php echo $member['id']; ?>" class="text-decoration-none">
                            <div class="team-member">
                                <div class="team-avatar"><?php echo strtoupper($member['name'][0]); ?></div>
                                <div>
                                    <strong class="text-dark"><?php echo htmlspecialchars($member['name']); ?></strong>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($member['skills']); ?></small>
                                </div>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No team members yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>

<?php
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}
?>