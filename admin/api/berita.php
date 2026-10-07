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
            // Get all berita or single berita
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $berita = $stmt->fetch();
                
                if ($berita) {
                    echo json_encode(['success' => true, 'data' => $berita]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Berita tidak ditemukan']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM berita ORDER BY created_at DESC");
                $berita = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $berita]);
            }
            break;
            
        case 'POST':
            // Create new berita - support JSON and FormData
            $isJson = isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
            $data = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
            
            if (empty($data['title']) || empty($data['content'])) {
                echo json_encode(['success' => false, 'error' => 'Title dan content harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO berita (title, content, excerpt, author, status, image) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $excerpt = substr(strip_tags($data['content']), 0, 200) . '...';
            $author = $data['author'] ?? 'Admin';
            $status = $data['status'] ?? 'draft';
            $image = $data['image'] ?? null;
            
            $stmt->execute([
                $data['title'],
                $data['content'],
                $excerpt,
                $author,
                $status,
                $image
            ]);
            
            $beritaId = $pdo->lastInsertId();
            
            // Log activity
            $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
            $log->execute([$_SESSION['admin_id'] ?? 1, 'create_berita', "Created berita: {$data['title']}"]);
            
            echo json_encode(['success' => true, 'message' => 'Berita berhasil ditambahkan', 'id' => $beritaId]);
            break;
            
        case 'PUT':
            // Update berita - support JSON and x-www-form-urlencoded
            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                parse_str(file_get_contents('php://input'), $data);
            }
            
            if (empty($data['id']) || empty($data['title']) || empty($data['content'])) {
                echo json_encode(['success' => false, 'error' => 'ID, title, dan content harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE berita 
                SET title = ?, content = ?, excerpt = ?, author = ?, status = ?, image = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $excerpt = substr(strip_tags($data['content']), 0, 200) . '...';
            $author = $data['author'] ?? 'Admin';
            $status = $data['status'] ?? 'draft';
            $image = $data['image'] ?? null;
            
            $result = $stmt->execute([
                $data['title'],
                $data['content'],
                $excerpt,
                $author,
                $status,
                $image,
                $data['id']
            ]);
            
            if ($result) {
                $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $log->execute([$_SESSION['admin_id'] ?? 1, 'update_berita', "Updated berita: {$data['title']}"]);
                echo json_encode(['success' => true, 'message' => 'Berita berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate berita']);
            }
            break;
            
        case 'DELETE':
            // Delete berita
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID berita harus diisi']);
                break;
            }
            
            // Get berita title for logging
            $stmt = $pdo->prepare("SELECT title FROM berita WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $berita = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM berita WHERE id = ?");
            $result = $stmt->execute([$_GET['id']]);
            
            if ($result) {
                if ($berita) {
                    $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $log->execute([$_SESSION['admin_id'] ?? 1, 'delete_berita', "Deleted berita: {$berita['title']}"]);
                }
                echo json_encode(['success' => true, 'message' => 'Berita berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus berita']);
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