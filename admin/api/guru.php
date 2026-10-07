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
            // Get all guru or single guru
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM guru WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $guru = $stmt->fetch();
                
                if ($guru) {
                    echo json_encode(['success' => true, 'data' => $guru]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Guru tidak ditemukan']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM guru ORDER BY nama ASC");
                $guru = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $guru]);
            }
            break;
            
        case 'POST':
            // Create new guru
            // Handle both FormData and JSON
            $data = [];
            if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                // Handle FormData
                $data = $_POST;
            }
            
            // Validate required fields
            if (empty($data['nama']) || empty($data['mata_pelajaran'])) {
                echo json_encode(['success' => false, 'error' => 'Nama dan mata pelajaran harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO guru (nama, nip, jabatan, mata_pelajaran, email, phone, image, bio, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $nip = $data['nip'] ?? null;
            $jabatan = $data['jabatan'] ?? 'Guru';
            $email = $data['email'] ?? null;
            $phone = $data['phone'] ?? null;
            $image = $data['photo'] ?? null; // Keep 'photo' from form, but use 'image' in DB
            $bio = $data['bio'] ?? null;
            $status = $data['status'] ?? 'active';
            
            $stmt->execute([
                $data['nama'],
                $nip,
                $jabatan,
                $data['mata_pelajaran'],
                $email,
                $phone,
                $image,
                $bio,
                $status
            ]);
            
            $guruId = $pdo->lastInsertId();
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['admin_id'] ?? 1, 'create_guru', "Created guru: {$data['nama']}"]);
            
            echo json_encode(['success' => true, 'message' => 'Guru berhasil ditambahkan', 'id' => $guruId]);
            break;
            
        case 'PUT':
            // Update guru
            // Handle both FormData and JSON
            $data = [];
            if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                // Handle FormData - for PUT requests, we need to parse it manually
                parse_str(file_get_contents('php://input'), $data);
            }
            
            // Validate required fields
            if (empty($data['id']) || empty($data['nama']) || empty($data['mata_pelajaran'])) {
                echo json_encode(['success' => false, 'error' => 'ID, nama, dan mata pelajaran harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE guru 
                SET nama = ?, nip = ?, jabatan = ?, mata_pelajaran = ?, email = ?, phone = ?, image = ?, bio = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $nip = $data['nip'] ?? null;
            $jabatan = $data['jabatan'] ?? 'Guru';
            $email = $data['email'] ?? null;
            $phone = $data['phone'] ?? null;
            $image = $data['photo'] ?? null; // Keep 'photo' from form, but use 'image' in DB
            $bio = $data['bio'] ?? null;
            $status = $data['status'] ?? 'active';
            
            $result = $stmt->execute([
                $data['nama'],
                $nip,
                $jabatan,
                $data['mata_pelajaran'],
                $email,
                $phone,
                $image,
                $bio,
                $status,
                $data['id']
            ]);
            
            if ($result) {
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['admin_id'] ?? 1, 'update_guru', "Updated guru: {$data['nama']}"]);
                
                echo json_encode(['success' => true, 'message' => 'Guru berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate guru']);
            }
            break;
            
        case 'DELETE':
            // Delete guru
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID guru harus diisi']);
                break;
            }
            
            // Get guru name for logging
            $stmt = $pdo->prepare("SELECT nama FROM guru WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $guru = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM guru WHERE id = ?");
            $result = $stmt->execute([$_GET['id']]);
            
            if ($result) {
                // Log activity
                if ($guru) {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'] ?? 1, 'delete_guru', "Deleted guru: {$guru['nama']}"]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Guru berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus guru']);
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