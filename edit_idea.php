<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$id = (int)$_GET['id'];
$idea = $conn->query("SELECT * FROM ideas WHERE id = $id")->fetch_assoc();

if (!$idea) {
    redirect('dashboard.php');
}

if (!canEditIdea($idea['user_id'])) {
    $_SESSION['error'] = 'You can only edit your own ideas.';
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
    
    $stmt = $conn->prepare("UPDATE ideas SET title = ?, description = ?, category = ?, skills_required = ?, status = ? WHERE id = ?");
    $stmt->bind_param('sssssi', $title, $description, $category, $skills_required, $status, $id);
    
    if ($stmt->execute()) {
        $success = 'Idea updated successfully!';
        $idea = $conn->query("SELECT * FROM ideas WHERE id = $id")->fetch_assoc();
    } else {
        $error = 'Failed to update idea.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Idea - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>body { background: var(--gradient-primary); min-height: 100vh; }</style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="glass-card p-5 fade-in">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-gradient"><i class="bi bi-pencil"></i> Edit Idea</h2>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger-custom alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success-custom alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Idea Title</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($idea['title']); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="6" required><?php echo htmlspecialchars($idea['description']); ?></textarea>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" required>
                                    <?php $categories = ['Technology', 'Healthcare', 'Education', 'Finance', 'E-commerce', 'Social', 'Other']; ?>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo $idea['category'] == $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="open" <?php echo $idea['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="in_progress" <?php echo $idea['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $idea['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Required Skills</label>
                            <input type="text" class="form-control" name="skills_required" value="<?php echo htmlspecialchars($idea['skills_required']); ?>">
                        </div>
                        
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-gradient flex-grow-1">Update Idea</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
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