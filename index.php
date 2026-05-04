<?php
require_once 'config/database.php';
$page = 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Idea2Venture - Transform Your Ideas Into Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-content">
                    <h1 class="hero-title mb-4">Transform Your Ideas Into <span class="text-warning">Venture</span></h1>
                    <p class="hero-subtitle">Connect with talented individuals, build your dream team, and turn your startup ideas into reality.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5 shadow">
                            <i class="bi bi-rocket-takeoff me-2"></i>Get Started
                        </a>
                        <a href="ideas.php" class="btn btn-outline-light btn-lg rounded-pill px-5">
                            <i class="bi bi-search me-2"></i>Explore Ideas
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="text-center">
                        <div class="p-5" style="transform: rotate(-5deg); background: rgba(255,255,255,0.2); border-radius: 20px;">
                            <i class="bi bi-lightbulb" style="font-size: 8rem; color: white;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-gradient">Why Choose Idea2Venture?</h2>
                <p class="text-muted">Everything you need to bring your ideas to life</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="bi bi-lightbulb"></i>
                        </div>
                        <h4>Share Ideas</h4>
                        <p class="text-muted">Post your startup ideas and get feedback from the community</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Find Collaborators</h4>
                        <p class="text-muted">Connect with people who have the skills you need</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4>Team Chat</h4>
                        <p class="text-muted">Communicate with your team members in real-time</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5" style="background: var(--gradient-primary);">
        <div class="container py-4">
            <div class="row text-center">
                <?php
                $stats = [
                    ['icon' => 'bi-people', 'number' => '2,500+', 'label' => 'Active Users'],
                    ['icon' => 'bi-lightbulb', 'number' => '1,200+', 'label' => 'Ideas Posted'],
                    ['icon' => 'bi-hand-thumbs-up', 'number' => '8,500+', 'label' => 'Likes'],
                    ['icon' => 'bi-chat', 'number' => '5,000+', 'label' => 'Messages']
                ];
                foreach ($stats as $stat):
                ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="text-white">
                        <i class="<?php echo $stat['icon']; ?>" style="font-size: 3rem; opacity: 0.8;"></i>
                        <h3 class="fw-bold mt-3"><?php echo $stat['number']; ?></h3>
                        <p class="mb-0 opacity-75"><?php echo $stat['label']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Recent Ideas -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold text-gradient">Latest Ideas</h2>
                    <p class="text-muted">Discover trending startup ideas</p>
                </div>
                <a href="ideas.php" class="btn btn-gradient">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            
            <div class="row g-4">
                <?php
                $stmt = $conn->query("SELECT i.*, u.name as owner_name, 
                    (SELECT COUNT(*) FROM likes WHERE idea_id = i.id) as likes_count
                    FROM ideas i 
                    JOIN users u ON i.user_id = u.id 
                    ORDER BY i.created_at DESC LIMIT 6");
                while ($idea = $stmt->fetch_assoc()):
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card idea-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="idea-category"><?php echo htmlspecialchars($idea['category']); ?></span>
                            <span class="idea-status status-<?php echo $idea['status']; ?>"><?php echo ucfirst($idea['status']); ?></span>
                        </div>
                        <h5 class="mb-2"><?php echo htmlspecialchars($idea['title']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo substr($idea['description'], 0, 100); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-person"></i> <?php echo htmlspecialchars($idea['owner_name']); ?></small>
                            <span class="text-muted"><i class="bi bi-heart-fill text-danger"></i> <?php echo $idea['likes_count']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if ($stmt->num_rows == 0): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-lightbulb" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">No ideas yet</h4>
                    <p class="text-muted">Be the first to share your startup idea!</p>
                    <a href="register.php" class="btn btn-gradient mt-3">Get Started</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container py-5">
            <div class="glass-card p-5 text-center" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));">
                <h2 class="text-white fw-bold mb-3">Ready to Start Your Journey?</h2>
                <p class="text-white opacity-75 mb-4">Join thousands of entrepreneurs and innovators</p>
                <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5">
                    <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>