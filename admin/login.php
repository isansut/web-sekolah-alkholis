<?php
session_start();
require_once '../config/database.php';

// If already logged in, redirect to admin panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $pdo = getDBConnection();
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        $tableExists = $stmt->rowCount() > 0;
        
        if (!$tableExists) {
            $error = 'Tabel users tidak ditemukan. Silakan import database terlebih dahulu.';
            $debug_info .= "Tabel users: Tidak ada\n";
        } else {
            // Get user with plain text password (no hashing)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'admin'");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                
                // Log login activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], 'login', "Admin login: {$user['username']}"]);
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Username atau password salah!';
                
                // Debug info
                $stmt = $pdo->query("SELECT username, password, role FROM users");
                $allUsers = $stmt->fetchAll();
                $debug_info .= "Total users: " . count($allUsers) . "\n";
                foreach ($allUsers as $u) {
                    $debug_info .= "User: {$u['username']}, Password: {$u['password']}, Role: {$u['role']}\n";
                }
            }
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
        $debug_info .= "Error: " . $e->getMessage() . "\n";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SMA Al-Kholis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            margin-top: 1rem;
            font-family: monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-school fa-3x mb-3"></i>
            <h4>SMA Al-Kholis</h4>
            <p class="mb-0">Admin Panel Login</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <strong>Default Credentials:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </small>
            </div>
            
            <?php if ($debug_info): ?>
                <details class="mt-3">
                    <summary class="text-muted">Debug Info</summary>
                    <div class="debug-info"><?php echo htmlspecialchars($debug_info); ?></div>
                </details>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 