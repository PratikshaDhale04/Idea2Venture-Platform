<?php
require_once '../config/database.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('../index.php');
}

$page = 'admin';

// Get stats
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$totalIdeas = $conn->query("SELECT COUNT(*) as count FROM ideas")->fetch_assoc()['count'];
$totalRequests = $conn->query("SELECT COUNT(*) as count FROM join_requests WHERE status = 'pending'")->fetch_assoc()['count'];
$totalMessages = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];

// Recent activities
$recentIdeas = $conn->query("SELECT i.*, u.name as owner_name FROM ideas i 
    JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC LIMIT 5");

$recentUsers = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font-bootstrap-icons.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/admin_panel.css">
</head>
<body class="admin-body">
    <div class="d-flex admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Admin Panel</h4>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="sidebar-link active"><i class="bi bi-speedometer2 me-3"></i>Dashboard</a>
                <a href="users.php" class="sidebar-link"><i class="bi bi-people me-3"></i>Users</a>
                <a href="ideas.php" class="sidebar-link"><i class="bi bi-lightbulb me-3"></i>Ideas</a>
                <a href="requests.php" class="sidebar-link"><i class="bi bi-person-plus me-3"></i>Requests</a>
                <a href="chats.php" class="sidebar-link"><i class="bi bi-chat-dots me-3"></i>Chats</a>
                <div class="sidebar-divider"></div>
                <a href="../index.php" class="sidebar-link"><i class="bi bi-house me-3"></i>Back to Site</a>
                <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-right me-3"></i>Logout</a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <h2 class="fw-bold">Dashboard Overview</h2>
                <span class="text-light">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            </div>
            
            <!-- Stats Cards with Animations -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="admin-stat-card fade-in animate-delay-1">
                        <div class="stat-icon stat-users">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $totalUsers; ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-stat-card fade-in animate-delay-2">
                        <div class="stat-icon stat-ideas">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $totalIdeas; ?></div>
                            <div class="stat-label">Total Ideas</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-stat-card fade-in animate-delay-3">
                        <div class="stat-icon stat-requests">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $totalRequests; ?></div>
                            <div class="stat-label">Pending Requests</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-stat-card fade-in animate-delay-4">
                        <div class="stat-icon stat-messages">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $totalMessages; ?></div>
                            <div class="stat-label">Total Messages</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Recent Ideas -->
                <div class="col-lg-6">
                    <div class="admin-card fade-in animate-delay-2">
                        <div class="admin-card-header">
                            <h5 class="fw-bold mb-0"><i class="bi bi-lightbulb me-2"></i>Recent Ideas</h5>
                            <a href="ideas.php" class="btn btn-sm btn-outline-admin">View All</a>
                        </div>
                        
                        <?php if ($recentIdeas->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Owner</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($idea = $recentIdeas->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($idea['title']); ?></td>
                                        <td><?php echo htmlspecialchars($idea['owner_name']); ?></td>
                                        <td><span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center py-3">No ideas yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="col-lg-6">
                    <div class="admin-card fade-in animate-delay-3">
                        <div class="admin-card-header">
                            <h5 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Recent Users</h5>
                            <a href="users.php" class="btn btn-sm btn-outline-admin">View All</a>
                        </div>
                        
                        <?php if ($recentUsers->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $recentUsers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center py-3">No users yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>