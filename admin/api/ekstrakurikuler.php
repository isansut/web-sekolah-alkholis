<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/auth_helper.php';

// Check if user is logged in as admin
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get single ekstrakurikuler or list all
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM ekstrakurikuler WHERE id = ?");
                $stmt->execute([$id]);
                $ekstrakurikuler = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ekstrakurikuler) {
                    echo json_encode(['success' => true, 'data' => $ekstrakurikuler]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Ekstrakurikuler tidak ditemukan']);
                }
            } else {
                // Get all ekstrakurikuler
                $stmt = $pdo->query("SELECT * FROM ekstrakurikuler ORDER BY nama");
                $ekstrakurikuler_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $ekstrakurikuler_list]);
            }
            break;
            
        case 'POST':
            // Create new ekstrakurikuler
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Invalid input data']);
                break;
            }
            
            $required_fields = ['nama'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    echo json_encode(['success' => false, 'message' => "Field {$field} is required"]);
                    exit();
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO ekstrakurikuler (nama, deskripsi, pembina_id, kategori, jadwal, tempat, kuota, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['nama'],
                $input['deskripsi'] ?? '',
                $input['pembina_id'] ?? null,
                $input['kategori'] ?? '',
                $input['jadwal'] ?? '',
                $input['tempat'] ?? '',
                $input['kuota'] ?? 30,
                $input['status'] ?? 'active'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Ekstrakurikuler berhasil ditambahkan']);
            break;
            
        case 'PUT':
            // Update ekstrakurikuler
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || empty($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID is required for update']);
                break;
            }
            
            $stmt = $pdo->prepare("UPDATE ekstrakurikuler SET nama = ?, deskripsi = ?, pembina_id = ?, kategori = ?, jadwal = ?, tempat = ?, kuota = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $input['nama'],
                $input['deskripsi'] ?? '',
                $input['pembina_id'] ?? null,
                $input['kategori'] ?? '',
                $input['jadwal'] ?? '',
                $input['tempat'] ?? '',
                $input['kuota'] ?? 30,
                $input['status'] ?? 'active',
                $input['id']
            ]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Ekstrakurikuler berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
            }
            break;
            
        case 'DELETE':
            // Delete ekstrakurikuler
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID is required for deletion']);
                break;
            }
            
            $id = (int)$_GET['id'];
            
            // Check if ekstrakurikuler has active members
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa_ekstrakurikuler WHERE ekstrakurikuler_id = ? AND status = 'active'");
            $stmt->execute([$id]);
            $memberCount = $stmt->fetchColumn();
            
            if ($memberCount > 0) {
                echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus ekstrakurikuler yang masih memiliki anggota aktif']);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM ekstrakurikuler WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Ekstrakurikuler berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ekstrakurikuler tidak ditemukan']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Ekstrakurikuler API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
