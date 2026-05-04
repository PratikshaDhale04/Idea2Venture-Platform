<?php
require_once 'config/database.php';

$page = 'ideas';
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$where = "1=1";
if ($search) $where .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%')";
if ($category) $where .= " AND i.category = '$category'";

$ideas = $conn->query("SELECT i.*, u.name as owner_name, u.id as owner_id,
    (SELECT COUNT(*) FROM likes WHERE idea_id = i.id) as likes_count,
    (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) as comments_count
    FROM ideas i 
    JOIN users u ON i.user_id = u.id 
    WHERE $where
    ORDER BY i.created_at DESC");

$categories = ['Technology', 'Healthcare', 'Education', 'Finance', 'E-commerce', 'Social', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Ideas - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-5">
        <!-- Header -->
        <div class="glass-card p-4 mb-5 fade-in">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold text-gradient mb-1">Explore Ideas</h2>
                    <p class="text-muted mb-0">Discover innovative startup ideas</p>
                </div>
                <div class="col-lg-6">
                    <form method="GET" class="d-flex gap-2">
                        <div class="search-box flex-grow-1">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" placeholder="Search ideas..." value="<?php echo $search; ?>">
                        </div>
                        <select name="category" class="form-select" style="width: 150px;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-gradient"><i class="bi bi-filter"></i></button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Ideas Grid -->
        <div class="row g-4" id="ideasGrid">
            <?php if ($ideas->num_rows > 0): ?>
                <?php while ($idea = $ideas->fetch_assoc()): 
                    $liked = false;
                    if (isLoggedIn()) {
                        $checkLike = $conn->query("SELECT id FROM likes WHERE user_id = " . $_SESSION['user_id'] . " AND idea_id = " . $idea['id']);
                        $liked = $checkLike->num_rows > 0;
                    }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card idea-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="idea-category"><?php echo htmlspecialchars($idea['category']); ?></span>
                            <span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span>
                        </div>
                        
                        <h5 class="mb-2"><?php echo htmlspecialchars($idea['title']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo substr($idea['description'], 0, 100); ?>...</p>
                        
                        <div class="mb-3">
                            <small class="text-muted"><i class="bi bi-tag me-1"></i> <?php echo htmlspecialchars($idea['skills_required'] ?: 'No skills specified'); ?></small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div>
                                <button class="like-btn <?php echo $liked ? 'liked' : ''; ?>" data-idea-id="<?php echo $idea['id']; ?>" <?php echo isLoggedIn() ? '' : 'onclick="showToast(\'Please login to like\', \'error\')"'; ?>>
                                    <i class="bi <?php echo $liked ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                </button>
                                <span class="text-muted small"><?php echo $idea['likes_count']; ?></span>
                            </div>
                            <div>
                                <span class="text-muted small me-3"><i class="bi bi-chat"></i> <?php echo $idea['comments_count']; ?></span>
                                <a href="idea_detail.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-gradient">View</a>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-2 border-top">
                            <small class="text-muted"><i class="bi bi-person me-1"></i> by <?php echo htmlspecialchars($idea['owner_name']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">No ideas found</h4>
                    <p class="text-muted">Try adjusting your search or filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>