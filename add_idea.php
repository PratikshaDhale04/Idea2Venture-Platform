<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!canCreateIdea()) {
    $_SESSION['error'] = 'Only Idea Owners can create ideas. Upgrade your account.';
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $skills_required = sanitize($_POST['skills_required']);
    $status = sanitize($_POST['status']);
    
    if (empty($title) || empty($description)) {
        $error = 'Title and description are required!';
    } else {
        $status = $status ?: 'open';
        $stmt = $conn->prepare("INSERT INTO ideas (user_id, title, description, category, skills_required, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssss', $_SESSION['user_id'], $title, $description, $category, $skills_required, $status);
        
        if ($stmt->execute()) {
            $success = 'Idea created successfully!';
            header("refresh:2;url=dashboard.php");
        } else {
            $error = 'Failed to create idea. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Idea - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="glass-card p-5 fade-in">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-gradient"><i class="bi bi-lightbulb"></i> Share Your Idea</h2>
                        <p class="text-muted">Transform your idea into reality</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger-custom alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success-custom alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label"><i class="bi bi-lightbulb me-2"></i>Idea Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="Give your idea a catchy title">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label"><i class="bi bi-text-paragraph me-2"></i>Description</label>
                            <textarea class="form-control" name="description" rows="6" required placeholder="Describe your idea in detail..."></textarea>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-folder me-2"></i>Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Healthcare">Healthcare</option>
                                    <option value="Education">Education</option>
                                    <option value="Finance">Finance</option>
                                    <option value="E-commerce">E-commerce</option>
                                    <option value="Social">Social</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-toggle-on me-2"></i>Status</label>
                                <select class="form-select" name="status">
                                    <option value="open">Open for Collaboration</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label"><i class="bi bi-code-slash me-2"></i>Required Skills</label>
                            <input type="text" class="form-control" name="skills_required" placeholder="e.g., Python, React, Marketing, Design">
                            <small class="text-muted">Separate skills with commas</small>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-gradient flex-grow-1">
                                <i class="bi bi-check2-circle me-2"></i> Create Idea
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary flex-grow-1">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>