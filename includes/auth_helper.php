<?php
/**
 * AUTHENTICATION HELPER FOR SMA AL-KHOLIS WEBSITE
 * Centralized authentication and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in as admin
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin authentication
 * Redirects to login if not authenticated
 */
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current admin user data
 * @return array|null
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting current admin: " . $e->getMessage());
        return null;
    }
}

/**
 * Authenticate user login
 * @param string $username
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function authenticateUser($username, $password) {
    try {
        $pdo = getDBConnection();
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            return [
                'success' => false,
                'message' => 'Tabel users tidak ditemukan. Silakan import database terlebih dahulu.',
                'user' => null
            ];
        }
        
        // Get user with plain text password (no hashing for now)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'admin'");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Set session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            // Log login activity
            logActivity($user['id'], 'login', "Admin login: {$user['username']}");
            
            return [
                'success' => true,
                'message' => 'Login berhasil!',
                'user' => $user
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Username atau password salah!',
                'user' => null
            ];
        }
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'user' => null
        ];
    }
}

/**
 * Logout current user
 */
function logoutUser() {
    if (isAdminLoggedIn()) {
        // Log logout activity
        logActivity($_SESSION['admin_id'], 'logout', "Admin logout: {$_SESSION['admin_username']}");
    }
    
    // Clear session
    session_unset();
    session_destroy();
    
    // Start new session for flash messages
    session_start();
}

/**
 * Log user activity
 * @param int $userId
 * @param string $action
 * @param string $details
 */
function logActivity($userId, $action, $details = '') {
    try {
        $pdo = getDBConnection();
        
        // Check if activity_logs table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require CSRF token for POST requests
 */
function requireCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCSRFToken($token)) {
            http_response_code(403);
            die('CSRF token verification failed');
        }
    }
}

/**
 * Hash password using secure method
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Update user password
 * @param int $userId
 * @param string $newPassword
 * @return bool
 */
function updateUserPassword($userId, $newPassword) {
    try {
        $pdo = getDBConnection();
        $hashedPassword = hashPassword($newPassword);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error updating password: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user has permission for specific action
 * @param string $action
 * @return bool
 */
function hasPermission($action) {
    if (!isAdminLoggedIn()) {
        return false;
    }
    
    // For now, all admin users have all permissions
    // This can be extended with role-based permissions later
    return true;
}

/**
 * Get user permissions
 * @param int $userId
 * @return array
 */
function getUserPermissions($userId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            switch ($user['role']) {
                case 'admin':
                    return ['read', 'write', 'delete', 'manage_users', 'manage_settings'];
                case 'guru':
                    return ['read', 'write'];
                case 'siswa':
                    return ['read'];
                default:
                    return [];
            }
        }
        
        return [];
    } catch (Exception $e) {
        error_log("Error getting user permissions: " . $e->getMessage());
        return [];
    }
}

/**
 * Set flash message
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message if exists
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($message);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
    }
}

/**
 * Sanitize input for security
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $string;
}

/**
 * Get user by ID
 * @param int $userId
 * @return array|null
 */
function getUserById($userId) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting user by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all users
 * @param string $role (optional filter)
 * @return array
 */
function getAllUsers($role = null) {
    try {
        $pdo = getDBConnection();
        
        if ($role) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        }
        
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting all users: " . $e->getMessage());
        return [];
    }
}

/**
 * Create new user
 * @param array $userData
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function createUser($userData) {
    try {
        $pdo = getDBConnection();
        
        // Validate required fields
        $required = ['username', 'password', 'email', 'role'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                return [
                    'success' => false,
                    'message' => "Field {$field} is required",
                    'user_id' => null
                ];
            }
        }
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$userData['username'], $userData['email']]);
        if ($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Username atau email sudah digunakan',
                'user_id' => null
            ];
        }
        
        // Hash password
        $hashedPassword = hashPassword($userData['password']);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $userData['username'],
            $hashedPassword,
            $userData['email'],
            $userData['role']
        ]);
        
        $userId = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'message' => 'User berhasil dibuat',
            'user_id' => $userId
        ];
    } catch (Exception $e) {
        error_log("Error creating user: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error creating user: ' . $e->getMessage(),
            'user_id' => null
        ];
    }
}

/**
 * Update user
 * @param int $userId
 * @param array $userData
 * @return array ['success' => bool, 'message' => string]
 */
function updateUser($userId, $userData) {
    try {
        $pdo = getDBConnection();
        
        $updates = [];
        $params = [];
        
        // Build update query
        if (isset($userData['username'])) {
            $updates[] = "username = ?";
            $params[] = $userData['username'];
        }
        
        if (isset($userData['email'])) {
            $updates[] = "email = ?";
            $params[] = $userData['email'];
        }
        
        if (isset($userData['role'])) {
            $updates[] = "role = ?";
            $params[] = $userData['role'];
        }
        
        if (isset($userData['password']) && !empty($userData['password'])) {
            $updates[] = "password = ?";
            $params[] = hashPassword($userData['password']);
        }
        
        if (empty($updates)) {
            return [
                'success' => false,
                'message' => 'No fields to update'
            ];
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return [
            'success' => true,
            'message' => 'User berhasil diupdate'
        ];
    } catch (Exception $e) {
        error_log("Error updating user: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error updating user: ' . $e->getMessage()
        ];
    }
}

/**
 * Delete user
 * @param int $userId
 * @return array ['success' => bool, 'message' => string]
 */
function deleteUser($userId) {
    try {
        $pdo = getDBConnection();
        
        // Don't allow deleting self
        if (isAdminLoggedIn() && $_SESSION['admin_id'] == $userId) {
            return [
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ];
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            return [
                'success' => true,
                'message' => 'User berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan'
            ];
        }
    } catch (Exception $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error deleting user: ' . $e->getMessage()
        ];
    }
}
?> 