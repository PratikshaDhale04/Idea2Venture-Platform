<?php
require_once 'config/database.php';

$user_id = (int)$_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    redirect('ideas.php');
}

$my_ideas = $conn->query("SELECT * FROM ideas WHERE user_id = $user_id ORDER BY created_at DESC");
$joined_ideas = $conn->query("SELECT i.* FROM ideas i 
    JOIN join_requests jr ON i.id = jr.idea_id 
    WHERE jr.user_id = $user_id AND jr.status = 'accepted'");

$is_owner = isLoggedIn() && $_SESSION['user_id'] == $user_id;

// Handle profile update
if ($is_owner && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $skills = sanitize($_POST['skills']);
    $bio = sanitize($_POST['bio']);
    
    $stmt = $conn->prepare("UPDATE users SET name = ?, skills = ?, bio = ? WHERE id = ?");
    $stmt->bind_param('sssi', $name, $skills, $bio, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['name'] = $name;
        $user['name'] = $name;
        $user['skills'] = $skills;
        $user['bio'] = $bio;
        $success = 'Profile updated successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['name']); ?> - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-end" style="padding-bottom: 20px;">
                <div class="col-md-3 text-center">
                    <div class="profile-avatar">
                        <?php echo strtoupper($user['name'][0]); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-white opacity-75 mb-0"><?php echo htmlspecialchars($user['bio'] ?: 'No bio yet'); ?></p>
                </div>
                <div class="col-md-3 text-md-end">
                    <?php if ($is_owner): ?>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil me-2"></i>Edit Profile
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-5">
        <!-- Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-card p-4 text-center">
                    <h3 class="fw-bold text-gradient"><?php echo $my_ideas->num_rows; ?></h3>
                    <p class="text-muted mb-0">Ideas Posted</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center">
                    <h3 class="fw-bold text-gradient"><?php echo $joined_ideas->num_rows; ?></h3>
                    <p class="text-muted mb-0">Teams Joined</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center">
                    <h3 class="fw-bold text-gradient"><?php echo htmlspecialchars($user['skills'] ?: 'N/A'); ?></h3>
                    <p class="text-muted mb-0">Skills</p>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <ul class="nav nav-tabs-custom mb-4">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#my-ideas">
                    <i class="bi bi-lightbulb me-2"></i>My Ideas
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#joined">
                    <i class="bi bi-people me-2"></i>Joined Teams
                </button>
            </li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane fade show active" id="my-ideas">
                <div class="row g-4">
                    <?php if ($my_ideas->num_rows > 0): ?>
                        <?php while ($idea = $my_ideas->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="glass-card idea-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="idea-category"><?php echo htmlspecialchars($idea['category']); ?></span>
                                    <span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span>
                                </div>
                                <h5 class="mb-2"><?php echo htmlspecialchars($idea['title']); ?></h5>
                                <p class="text-muted small"><?php echo substr($idea['description'], 0, 80); ?>...</p>
                                <a href="idea_detail.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-gradient">View</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-lightbulb" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">No ideas posted yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tab-pane fade" id="joined">
                <div class="row g-4">
                    <?php if ($joined_ideas->num_rows > 0): ?>
                        <?php while ($idea = $joined_ideas->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="glass-card idea-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="idea-category"><?php echo htmlspecialchars($idea['category']); ?></span>
                                    <span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span>
                                </div>
                                <h5 class="mb-2"><?php echo htmlspecialchars($idea['title']); ?></h5>
                                <p class="text-muted small"><?php echo substr($idea['description'], 0, 80); ?>...</p>
                                <a href="idea_detail.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-gradient">View</a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">Not joined any team yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <?php if ($is_owner): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Skills</label>
                            <input type="text" class="form-control" name="skills" value="<?php echo htmlspecialchars($user['skills']); ?>" placeholder="e.g., Python, Marketing">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-gradient">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>