<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $skills = sanitize($_POST['skills']);
    $role = 'user';
    $bio = sanitize($_POST['bio']);
    
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*]/', $password)) {
        $error = 'Password must be at least 8 characters with a number and symbol!';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email already registered!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, skills, role, bio) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $name, $email, $hashed_password, $skills, $role, $bio);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Redirecting to login...';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: var(--gradient-primary); min-height: 100vh; }
        .auth-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 30px; padding: 50px; max-width: 600px; margin: 50px auto; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="auth-card fade-in">
            <div class="text-center mb-5">
                <h1 class="fw-bold text-gradient"><i class="bi bi-rocket-takeoff"></i> Join Idea2Venture</h1>
                <p class="text-muted">Create your account and start transforming ideas</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success-custom alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" name="name" required placeholder="Enter your name">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="email" required placeholder="your@email.com">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="password" required placeholder="Min 8 chars, number, symbol">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm password">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Your Skills</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-code-slash"></i></span>
                            <input type="text" class="form-control" name="skills" placeholder="e.g., Python, Marketing, Design">
                        </div>
                    </div>
                    
                    <input type="hidden" name="role" value="user">
                    
                    <div class="col-12">
                        <label class="form-label">Bio</label>
                        <textarea class="form-control" name="bio" rows="3" placeholder="Tell us about yourself..."></textarea>
                    </div>
                </div>
                
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" required id="terms">
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-gradient btn-lg w-100 mt-4">
                    <i class="bi bi-person-plus me-2"></i> Create Account
                </button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">Already have an account? <a href="login.php" class="text-gradient fw-bold">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = this.password.value;
            if (password.length < 8 || !/\d/.test(password) || !/[!@#$%^&*]/.test(password)) {
                e.preventDefault();
                alert('Password must be at least 8 characters with a number and symbol!');
            }
        });
    </script>
</body>
</html>