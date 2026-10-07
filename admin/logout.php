<?php
session_start();

// Log the logout activity if user was logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    try {
        require_once '../config/database.php';
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['admin_id'] ?? 0, 'logout', 'Admin logout']);
    } catch (Exception $e) {
        // Logging failed, but logout continues
    }
}

// Clear all session data
$_SESSION = array();

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?> 
?> 