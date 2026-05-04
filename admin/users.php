<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../index.php');
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $id");
        header("Location: users.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Idea2Venture Admin</title>
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
                <a href="users.php" class="sidebar-link active"><i class="bi bi-people me-3"></i>Users</a>
                <a href="ideas.php" class="sidebar-link"><i class="bi bi-lightbulb me-3"></i>Ideas</a>
                <a href="requests.php" class="sidebar-link"><i class="bi bi-person-plus me-3"></i>Requests</a>
                <a href="chats.php" class="sidebar-link"><i class="bi bi-chat-dots me-3"></i>Chats</a>
                <div class="sidebar-divider"></div>
                <a href="../index.php" class="sidebar-link"><i class="bi bi-house me-3"></i>Back to Site</a>
                <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
            </nav>
        </div>
        
        <div class="admin-main">
            <h2 class="fw-bold mb-4" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="bi bi-people me-2"></i>Manage Users</h2>
            
            <div class="admin-card fade-in animate-delay-1">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Skills</th>
                                <th>Joined</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['skills'] ?: '-'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'user'): ?>
                                    <span class="badge bg-secondary">User</span>
                                    <?php elseif ($user['role'] === 'idea_owner'): ?>
                                    <span class="badge bg-warning text-dark">Idea Owner</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="../profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <?php if ($user['role'] === 'user'): ?>
                                    <a href="upgrade_user.php?id=<?php echo $user['id']; ?>&action=upgrade&role=idea_owner" class="btn btn-sm btn-outline-warning" title="Upgrade to Idea Owner">
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </a>
                                    <?php elseif ($user['role'] === 'idea_owner'): ?>
                                    <a href="upgrade_user.php?id=<?php echo $user['id']; ?>&action=downgrade" class="btn btn-sm btn-outline-secondary" title="Downgrade to User">
                                        <i class="bi bi-arrow-down-circle"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
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