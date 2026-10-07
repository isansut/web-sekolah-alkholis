<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get HTTP method
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get all settings or single setting
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM settings WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $setting = $stmt->fetch();
                
                if ($setting) {
                    echo json_encode(['success' => true, 'data' => $setting]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Setting tidak ditemukan']);
                }
            } else {
                // Get all settings
                $stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_key");
                $settings = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $settings]);
            }
            break;
            
        case 'POST':
            // Create new setting
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['setting_key']) || !isset($data['setting_value'])) {
                echo json_encode(['success' => false, 'error' => 'setting_key dan setting_value harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, description) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['setting_key'],
                $data['setting_value'],
                $data['description'] ?? null
            ]);
            
            // Log activity
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, details) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['admin_id'] ?? null,
                'create_setting',
                "Created setting: {$data['setting_key']}"
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Setting berhasil ditambahkan']);
            break;
            
        case 'PUT':
            // Update setting
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID setting harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE settings 
                SET setting_key = ?, setting_value = ?, description = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['setting_key'],
                $data['setting_value'],
                $data['description'] ?? null,
                $data['id']
            ]);
            
            // Log activity
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, action, details) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['admin_id'] ?? null,
                'update_setting',
                "Updated setting: {$data['setting_key']}"
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Setting berhasil diupdate']);
            break;
            
        case 'DELETE':
            // Delete setting
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID setting harus diisi']);
                break;
            }
            
            // Get setting info before delete for logging
            $stmt = $pdo->prepare("SELECT setting_key FROM settings WHERE id = ?");
            $stmt->execute([$data['id']]);
            $setting = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM settings WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            if ($stmt->rowCount() > 0) {
                // Log activity
                $stmt = $pdo->prepare("
                    INSERT INTO activity_logs (user_id, action, details) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['admin_id'] ?? null,
                    'delete_setting',
                    "Deleted setting: " . ($setting['setting_key'] ?? 'Unknown')
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Setting berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Setting tidak ditemukan']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 