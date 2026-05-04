<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../index.php');
}

$ideas = $conn->query("SELECT i.*, u.name as owner_name FROM ideas i 
    JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM ideas WHERE id = $id");
    header("Location: ideas.php");
}

if (isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = sanitize($_GET['status']);
    $conn->query("UPDATE ideas SET status = '$status' WHERE id = $id");
    header("Location: ideas.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ideas - Idea2Venture Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font-bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/admin_panel.css">
</head>
<body class="admin-body">
    <div class="d-flex admin-layout">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Admin Panel</h4>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-link"><i class="bi bi-speedometer2 me-3"></i>Dashboard</a>
                <a href="users.php" class="sidebar-link"><i class="bi bi-people me-3"></i>Users</a>
                <a href="ideas.php" class="sidebar-link active"><i class="bi bi-lightbulb me-3"></i>Ideas</a>
                <a href="requests.php" class="sidebar-link"><i class="bi bi-person-plus me-3"></i>Requests</a>
                <a href="chats.php" class="sidebar-link"><i class="bi bi-chat-dots me-3"></i>Chats</a>
                <div class="sidebar-divider"></div>
                <a href="../index.php" class="sidebar-link"><i class="bi bi-house me-3"></i>Back to Site</a>
                <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
            </nav>
        </div>
        
        <div class="admin-main">
            <h2 class="fw-bold mb-4" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="bi bi-lightbulb me-2"></i>Manage Ideas</h2>
            
            <div class="admin-card fade-in animate-delay-1">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Owner</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($idea = $ideas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $idea['id']; ?></td>
                                <td><?php echo htmlspecialchars($idea['title']); ?></td>
                                <td><?php echo htmlspecialchars($idea['owner_name']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($idea['category']); ?></span></td>
                                <td><span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($idea['created_at'])); ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="../idea_detail.php?id=<?php echo $idea['id']; ?>" target="_blank"><i class="bi bi-eye me-2"></i>View</a></li>
                                            <li><a class="dropdown-item" href="?status=open&id=<?php echo $idea['id']; ?>">Set Open</a></li>
                                            <li><a class="dropdown-item" href="?status=in_progress&id=<?php echo $idea['id']; ?>">Set In Progress</a></li>
                                            <li><a class="dropdown-item" href="?status=completed&id=<?php echo $idea['id']; ?>">Set Completed</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="?delete=<?php echo $idea['id']; ?>" onclick="return confirm('Delete this idea?')"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>