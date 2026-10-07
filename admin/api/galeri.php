<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all galeri or single galeri
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM galeri WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $galeri = $stmt->fetch();
                
                if ($galeri) {
                    echo json_encode(['success' => true, 'data' => $galeri]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Foto tidak ditemukan']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM galeri ORDER BY created_at DESC");
                $galeri = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $galeri]);
            }
            break;
            
        case 'POST':
            // Create new galeri
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $_POST['image'] ?? '';
            $category = $_POST['category'] ?? 'kegiatan';
            $status = $_POST['status'] ?? 'active';
            
            if (empty($title) || empty($image)) {
                echo json_encode(['success' => false, 'error' => 'Title dan image harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO galeri (title, description, image, category, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $title,
                $description,
                $image,
                $category,
                $status
            ]);
            
            if ($result) {
                $galeriId = $pdo->lastInsertId();
                
                // Log activity if table exists
                try {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'] ?? 1, 'create_galeri', "Created galeri: {$title}"]);
                } catch (Exception $e) {
                    // Activity logs table might not exist, ignore error
                }
                
                echo json_encode(['success' => true, 'message' => 'Foto berhasil ditambahkan', 'id' => $galeriId]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menambahkan foto']);
            }
            break;
            
        case 'PUT':
            // Update galeri
            parse_str(file_get_contents("php://input"), $putData);
            
            $id = $putData['id'] ?? '';
            $title = $putData['title'] ?? '';
            $description = $putData['description'] ?? '';
            $image = $putData['image'] ?? '';
            $category = $putData['category'] ?? 'kegiatan';
            $status = $putData['status'] ?? 'active';
            
            if (empty($id) || empty($title) || empty($image)) {
                echo json_encode(['success' => false, 'error' => 'ID, title, dan image harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE galeri 
                SET title = ?, description = ?, image = ?, category = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $title,
                $description,
                $image,
                $category,
                $status,
                $id
            ]);
            
            if ($result) {
                // Log activity if table exists
                try {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'] ?? 1, 'update_galeri', "Updated galeri: {$title}"]);
                } catch (Exception $e) {
                    // Activity logs table might not exist, ignore error
                }
                
                echo json_encode(['success' => true, 'message' => 'Foto berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate foto']);
            }
            break;
            
        case 'DELETE':
            // Delete galeri
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID foto harus diisi']);
                break;
            }
            
            // Get galeri title for logging
            $stmt = $pdo->prepare("SELECT title FROM galeri WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $galeri = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM galeri WHERE id = ?");
            $result = $stmt->execute([$_GET['id']]);
            
            if ($result) {
                // Log activity if table exists
                if ($galeri) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['admin_id'] ?? 1, 'delete_galeri', "Deleted galeri: {$galeri['title']}"]);
                    } catch (Exception $e) {
                        // Activity logs table might not exist, ignore error
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Foto berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus foto']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 