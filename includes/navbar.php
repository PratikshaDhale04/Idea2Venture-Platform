<?php
$current_page = $page ?? '';
?>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-lightbulb-fill me-2"></i>Idea2Venture
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'home' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'ideas' ? 'active' : ''; ?>" href="ideas.php">
                        <i class="bi bi-lightbulb me-1"></i> Ideas
                    </a>
                </li>
                
                <?php 
$roleBadges = [
    'user' => '<span class="badge bg-secondary ms-2">User</span>',
    'idea_owner' => '<span class="badge bg-warning text-dark ms-2">Owner</span>',
    'admin' => '<span class="badge bg-danger ms-2">Admin</span>'
];
?>
<?php if (isLoggedIn() && in_array($_SESSION['role'], ['user', 'idea_owner'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-grid me-1"></i> Dashboard
                    </a>
                </li>
                
                <?php if (isIdeaOwner()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'add_idea' ? 'active' : ''; ?>" href="add_idea.php">
                        <i class="bi bi-plus-circle me-1"></i> Add Idea
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                            <i class="bi bi-person me-2"></i> My Profile
                        </a></li>
                        <li><a class="dropdown-item" href="chat.php">
                            <i class="bi bi-chat-dots me-2"></i> Messages
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a></li>
                    </ul>
                </li>
                
                <?php elseif (isLoggedIn() && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-grid me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="admin/index.php">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">
                            <i class="bi bi-person me-2"></i> My Profile
                        </a></li>
                        <li><a class="dropdown-item" href="chat.php">
                            <i class="bi bi-chat-dots me-2"></i> Messages
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a></li>
                    </ul>
                </li>
                
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a href="register.php" class="btn btn-gradient btn-sm ms-2">Sign Up</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="toast-container"></div>