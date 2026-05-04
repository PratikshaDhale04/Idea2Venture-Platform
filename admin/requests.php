<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../index.php');
}

$requests = $conn->query("SELECT jr.*, i.title as idea_title, u.name as user_name 
    FROM join_requests jr 
    JOIN ideas i ON jr.idea_id = i.id 
    JOIN users u ON jr.user_id = u.id 
    ORDER BY jr.created_at DESC");

if (isset($_GET['accept'])) {
    $id = (int)$_GET['accept'];
    $conn->query("UPDATE join_requests SET status = 'accepted' WHERE id = $id");
    header("Location: requests.php");
}
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $conn->query("UPDATE join_requests SET status = 'rejected' WHERE id = $id");
    header("Location: requests.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Idea2Venture Admin</title>
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
                <a href="ideas.php" class="sidebar-link"><i class="bi bi-lightbulb me-3"></i>Ideas</a>
                <a href="requests.php" class="sidebar-link active"><i class="bi bi-person-plus me-3"></i>Requests</a>
                <a href="chats.php" class="sidebar-link"><i class="bi bi-chat-dots me-3"></i>Chats</a>
                <div class="sidebar-divider"></div>
                <a href="../index.php" class="sidebar-link"><i class="bi bi-house me-3"></i>Back to Site</a>
                <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
            </nav>
        </div>
        
        <div class="admin-main">
            <h2 class="fw-bold mb-4" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><i class="bi bi-person-plus me-2"></i>Join Requests</h2>
            
            <div class="admin-card fade-in animate-delay-1">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Idea</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $req['id']; ?></td>
                                <td><?php echo htmlspecialchars($req['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($req['idea_title']); ?></td>
                                <td><span class="badge-admin badge-admin-<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                <td>
                                    <a href="?accept=<?php echo $req['id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-check"></i></a>
                                    <a href="?reject=<?php echo $req['id']; ?>" class="btn btn-sm btn-danger"><i class="bi bi-x"></i></a>
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