<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all pengumuman or single pengumuman
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $pengumuman = $stmt->fetch();
                
                if ($pengumuman) {
                    echo json_encode(['success' => true, 'data' => $pengumuman]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Pengumuman tidak ditemukan']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM pengumuman ORDER BY created_at DESC");
                $pengumuman = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $pengumuman]);
            }
            break;
            
        case 'POST':
            // Create new pengumuman - support JSON/FormData
            $isJson = isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
            $data = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
            
            if (empty($data['title']) || empty($data['content'])) {
                echo json_encode(['success' => false, 'error' => 'Title dan content harus diisi']);
                break;
            }
            
            // Handle empty expiry_date properly
            $expiryDate = null;
            if (!empty($data['expiry_date']) && $data['expiry_date'] !== '') {
                $expiryDate = $data['expiry_date'];
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO pengumuman (title, content, priority, status, target_audience, expiry_date) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $priority = $data['priority'] ?? 'medium';
            $status = $data['status'] ?? 'draft';
            $targetAudience = $data['target_audience'] ?? 'umum';
            
            $result = $stmt->execute([
                $data['title'],
                $data['content'],
                $priority,
                $status,
                $targetAudience,
                $expiryDate
            ]);
            
            if ($result) {
                $pengumumanId = $pdo->lastInsertId();
                
                // Log activity
                $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $log->execute([$_SESSION['admin_id'] ?? 1, 'create_pengumuman', "Created pengumuman: {$data['title']}"]);
                
                echo json_encode(['success' => true, 'message' => 'Pengumuman berhasil ditambahkan', 'id' => $pengumumanId]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menambahkan pengumuman']);
            }
            break;
            
        case 'PUT':
            // Update pengumuman - support JSON/x-www-form-urlencoded
            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                parse_str(file_get_contents('php://input'), $data);
            }
            
            if (empty($data['id']) || empty($data['title']) || empty($data['content'])) {
                echo json_encode(['success' => false, 'error' => 'ID, title, dan content harus diisi']);
                break;
            }
            
            // Handle empty expiry_date properly
            $expiryDate = null;
            if (!empty($data['expiry_date']) && $data['expiry_date'] !== '') {
                $expiryDate = $data['expiry_date'];
            }
            
            $stmt = $pdo->prepare("
                UPDATE pengumuman 
                SET title = ?, content = ?, priority = ?, status = ?, target_audience = ?, expiry_date = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $priority = $data['priority'] ?? 'medium';
            $status = $data['status'] ?? 'draft';
            $targetAudience = $data['target_audience'] ?? 'umum';
            
            $result = $stmt->execute([
                $data['title'],
                $data['content'],
                $priority,
                $status,
                $targetAudience,
                $expiryDate,
                $data['id']
            ]);
            
            if ($result) {
                $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $log->execute([$_SESSION['admin_id'] ?? 1, 'update_pengumuman', "Updated pengumuman: {$data['title']}"]);
                echo json_encode(['success' => true, 'message' => 'Pengumuman berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal memperbarui pengumuman']);
            }
            break;
            
        case 'DELETE':
            // Delete pengumuman
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID pengumuman diperlukan']);
                break;
            }
            
            // Get pengumuman title for logging
            $stmt = $pdo->prepare("SELECT title FROM pengumuman WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $pengumuman = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE id = ?");
            $result = $stmt->execute([$_GET['id']]);
            
            if ($result) {
                if ($pengumuman) {
                    $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $log->execute([$_SESSION['admin_id'] ?? 1, 'delete_pengumuman', "Deleted pengumuman: {$pengumuman['title']}"]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Pengumuman berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus pengumuman']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Pengumuman API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
?> 