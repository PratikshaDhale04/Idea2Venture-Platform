<?php
require_once 'config/database.php';

if (isLoggedIn() && $_SESSION['role'] === 'admin') {
    redirect('admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields!';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'admin'");
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
                
                redirect('admin/index.php');
            } else {
                $error = 'Invalid password!';
            }
        } else {
            $error = 'Admin account not found!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Idea2Venture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font-bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <link rel="stylesheet" href="css/admin_panel.css">
    <style>
        .admin-login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .admin-login-card {
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            animation: fadeIn 0.6s ease-out;
        }
        .admin-logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: float 3s ease-in-out infinite;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 14px 18px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 12px 0 0 12px;
            border-right: none;
        }
        .input-group .form-control {
            border-radius: 0 12px 12px 0;
            border-left: none;
        }
        .input-group-text i {
            color: #667eea;
        }
        .btn-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .text-muted-custom {
            color: #888;
        }
    </style>
</head>
<body class="admin-login-page">
    <div class="admin-login-card">
        <div class="text-center mb-4">
            <div class="admin-logo mb-3">
                <i class="bi bi-shield-fill-check"></i>
            </div>
            <h2 class="fw-bold" style="background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Admin Panel</h2>
            <p class="text-muted-custom">Idea2Venture Management</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger-custom alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Admin Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" name="email" required placeholder="admin@idea2venture.com">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" name="password" required placeholder="Enter password">
                </div>
            </div>
            
            <button type="submit" class="btn btn-admin btn-lg w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i> Login to Admin Panel
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="login.php" class="text-muted-custom"><i class="bi bi-arrow-left me-1"></i> Back to User Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>