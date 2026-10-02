<?php
/**
 * Login Page
 * IMSTS - Integrated Market Surveillance Tracking System
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $sql = "SELECT id, full_name, username, email, password, role, status 
                FROM users 
                WHERE username = ? OR email = ?";
        $user = fetchOne($sql, [$username, $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Your account is inactive. Please contact the administrator.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                try {
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    $logSql = "INSERT INTO audit_log (user_id, action, description, ip_address) 
                              VALUES (?, 'LOGIN', 'User logged in', ?)";
                    executeQuery($logSql, [$user['id'], $ip]);
                } catch (Exception $e) {
                    error_log("Audit log failed: " . $e->getMessage());
                }
                
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IMSTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        .login-icon {
            font-size: 3rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="bi bi-shield-check login-icon"></i>
            <h2>IMSTS</h2>
            <p class="text-muted">Integrated Market Surveillance Tracking System</p>
            <hr>
            <h4 class="mb-4">Login</h4>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" class="form-control" name="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-muted small">Default Admin: <strong>admin</strong> / <strong>admin123</strong></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
