<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$ideas = $conn->query("SELECT * FROM ideas WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ideas - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="fw-bold text-gradient"><i class="bi bi-lightbulb me-2"></i>My Ideas</h2>
            <a href="add_idea.php" class="btn btn-gradient"><i class="bi bi-plus-lg me-2"></i>New Idea</a>
        </div>
        
        <div class="row g-4">
            <?php if ($ideas->num_rows > 0): ?>
                <?php while ($idea = $ideas->fetch_assoc()): 
                    $likes = $conn->query("SELECT COUNT(*) as count FROM likes WHERE idea_id = " . $idea['id'])->fetch_assoc()['count'];
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card idea-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="idea-category"><?php echo htmlspecialchars($idea['category']); ?></span>
                            <span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span>
                        </div>
                        
                        <h5 class="mb-2"><?php echo htmlspecialchars($idea['title']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo substr($idea['description'], 0, 100); ?>...</p>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="text-muted"><i class="bi bi-heart-fill text-danger"></i> <?php echo $likes; ?></span>
                            <div>
                                <a href="edit_idea.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <a href="idea_detail.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-gradient">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-lightbulb" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">No ideas yet</h4>
                    <p class="text-muted">Create your first startup idea!</p>
                    <a href="add_idea.php" class="btn btn-gradient mt-3">Create Idea</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>