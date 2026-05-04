<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields!';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Invalid password!';
            }
        } else {
            $error = 'Email not registered!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: var(--gradient-primary); min-height: 100vh; }
        .auth-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 30px; padding: 50px; max-width: 500px; margin: 50px auto; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="auth-card fade-in">
            <div class="text-center mb-5">
                <h1 class="fw-bold text-gradient"><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
                <p class="text-muted">Login to continue your journey</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" name="email" required placeholder="your@email.com">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" name="password" required placeholder="Enter password">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="text-gradient">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-gradient btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                </button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">Don't have an account? <a href="register.php" class="text-gradient fw-bold">Register here</a></p>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <p class="text-muted small mb-2"><strong>Demo Credentials:</strong></p>
                <p class="small mb-1">User: user@example.com / User@123</p>
                <p class="small mb-0"><a href="admin_login.php" class="text-gradient">Admin Login</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        function togglePassword() {
            const password = document.querySelector('input[name="password"]');
            const icon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>