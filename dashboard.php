<?php
require_once 'config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page = 'dashboard';
$user_id = $_SESSION['user_id'];

// Get user stats
$myIdeas = $conn->query("SELECT COUNT(*) as count FROM ideas WHERE user_id = $user_id")->fetch_assoc()['count'];
$joinedIdeas = $conn->query("SELECT COUNT(*) as count FROM join_requests WHERE user_id = $user_id AND status = 'accepted'")->fetch_assoc()['count'];
$pendingRequests = $conn->query("SELECT COUNT(*) as count FROM join_requests jr 
    JOIN ideas i ON jr.idea_id = i.id 
    WHERE i.user_id = $user_id AND jr.status = 'pending'")->fetch_assoc()['count'];

// Get my ideas
$stmt = $conn->query("SELECT * FROM ideas WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Get pending join requests for my ideas
$requests = $conn->query("SELECT jr.*, i.title as idea_title, u.name as user_name, u.skills as user_skills 
    FROM join_requests jr 
    JOIN ideas i ON jr.idea_id = i.id 
    JOIN users u ON jr.user_id = u.id 
    WHERE i.user_id = $user_id AND jr.status = 'pending' 
    ORDER BY jr.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="glass-card p-4 mb-5 fade-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-gradient mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                    <p class="text-muted mb-0">Here's what's happening with your ideas</p>
                </div>
                <a href="add_idea.php" class="btn btn-gradient">
                    <i class="bi bi-plus-lg me-2"></i> New Idea
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card fade-in animate-delay-1">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="bi bi-lightbulb text-white"></i>
                    </div>
                    <div class="stat-number"><?php echo $myIdeas; ?></div>
                    <div class="stat-label">My Ideas</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card fade-in animate-delay-2">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="bi bi-people text-white"></i>
                    </div>
                    <div class="stat-number"><?php echo $joinedIdeas; ?></div>
                    <div class="stat-label">Joined Teams</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card fade-in animate-delay-3">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="bi bi-person-plus text-white"></i>
                    </div>
                    <div class="stat-number"><?php echo $pendingRequests; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- My Ideas -->
            <div class="col-lg-8">
                <div class="glass-card p-4 fade-in animate-delay-2">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0"><i class="bi bi-lightbulb me-2 text-warning"></i>My Ideas</h4>
                        <a href="my_ideas.php" class="btn btn-sm btn-outline-gradient">View All</a>
                    </div>
                    
                    <?php if ($stmt->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($idea = $stmt->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="idea_detail.php?id=<?php echo $idea['id']; ?>" class="text-decoration-none fw-semibold">
                                            <?php echo htmlspecialchars($idea['title']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($idea['category']); ?></span></td>
                                    <td>
                                        <span class="idea-status status-<?php echo $idea['status']; ?>">
                                            <?php echo ucfirst($idea['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_idea.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button onclick="confirmDelete('idea', <?php echo $idea['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-lightbulb" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">You haven't posted any ideas yet</p>
                        <a href="add_idea.php" class="btn btn-gradient btn-sm">Create Your First Idea</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pending Requests -->
            <div class="col-lg-4">
                <div class="glass-card p-4 fade-in animate-delay-3">
                    <h4 class="fw-bold mb-4"><i class="bi bi-person-plus me-2 text-success"></i>Join Requests</h4>
                    
                    <?php if ($requests->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($req = $requests->fetch_assoc()): ?>
                        <div class="p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($req['user_name']); ?></strong>
                                    <p class="mb-0 small text-muted">wants to join</p>
                                </div>
                                <span class="badge-status badge-pending">Pending</span>
                            </div>
                            <p class="mb-2 small"><strong>Idea:</strong> <?php echo htmlspecialchars($req['idea_title']); ?></p>
                            <p class="mb-3 small"><strong>Skills:</strong> <?php echo htmlspecialchars($req['user_skills']); ?></p>
                            <div class="d-flex gap-2">
                                <button onclick="handleRequest(<?php echo $req['id']; ?>, 'accepted')" class="btn btn-sm btn-success flex-grow-1">
                                    <i class="bi bi-check"></i> Accept
                                </button>
                                <button onclick="handleRequest(<?php echo $req['id']; ?>, 'rejected')" class="btn btn-sm btn-danger flex-grow-1">
                                    <i class="bi bi-x"></i> Reject
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">No pending requests</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="glass-card p-4 fade-in animate-delay-4">
                    <h4 class="fw-bold mb-4"><i class="bi bi-lightning me-2"></i>Quick Actions</h4>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="add_idea.php" class="text-decoration-none">
                                <div class="p-4 bg-light rounded text-center hover-scale">
                                    <i class="bi bi-plus-circle fs-2 text-primary"></i>
                                    <p class="mb-0 mt-2 fw-semibold">New Idea</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="ideas.php" class="text-decoration-none">
                                <div class="p-4 bg-light rounded text-center hover-scale">
                                    <i class="bi bi-search fs-2 text-success"></i>
                                    <p class="mb-0 mt-2 fw-semibold">Browse Ideas</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="chat.php" class="text-decoration-none">
                                <div class="p-4 bg-light rounded text-center hover-scale">
                                    <i class="bi bi-chat-dots fs-2 text-info"></i>
                                    <p class="mb-0 mt-2 fw-semibold">Messages</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="profile.php?id=<?php echo $user_id; ?>" class="text-decoration-none">
                                <div class="p-4 bg-light rounded text-center hover-scale">
                                    <i class="bi bi-person fs-2 text-warning"></i>
                                    <p class="mb-0 mt-2 fw-semibold">My Profile</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        async function handleRequest(requestId, action) {
            try {
                const response = await fetch('api/handle_request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ request_id: requestId, action: action })
                });
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Error processing request', 'error');
            }
        }
    </script>
</body>
</html>