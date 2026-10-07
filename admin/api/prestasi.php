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
            // Get single prestasi or list all
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM prestasi WHERE id = ?");
                $stmt->execute([$id]);
                $prestasi = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($prestasi) {
                    echo json_encode(['success' => true, 'data' => $prestasi]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Prestasi tidak ditemukan']);
                }
            } else {
                // Get all prestasi
                $stmt = $pdo->query("SELECT * FROM prestasi ORDER BY tahun DESC, tingkat DESC, id DESC");
                $prestasi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $prestasi_list]);
            }
            break;
            
        case 'POST':
            // Create new prestasi
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Invalid input data']);
                break;
            }
            
            $required_fields = ['nama_prestasi', 'kategori', 'tingkat', 'tahun', 'siswa_id'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    echo json_encode(['success' => false, 'message' => "Field {$field} is required"]);
                    exit();
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO prestasi (nama_prestasi, kategori, tingkat, peringkat, tahun, deskripsi, siswa_id, guru_pembimbing_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['nama_prestasi'],
                $input['kategori'],
                $input['tingkat'],
                $input['peringkat'] ?? '',
                $input['tahun'],
                $input['deskripsi'] ?? '',
                $input['siswa_id'],
                $input['guru_pembimbing_id'] ?? null,
                $input['status'] ?? 'active'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Prestasi berhasil ditambahkan']);
            break;
            
        case 'PUT':
            // Update prestasi
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || empty($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID is required for update']);
                break;
            }
            
            $stmt = $pdo->prepare("UPDATE prestasi SET nama_prestasi = ?, kategori = ?, tingkat = ?, peringkat = ?, tahun = ?, deskripsi = ?, siswa_id = ?, guru_pembimbing_id = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $input['nama_prestasi'],
                $input['kategori'],
                $input['tingkat'],
                $input['peringkat'] ?? '',
                $input['tahun'],
                $input['deskripsi'] ?? '',
                $input['siswa_id'],
                $input['guru_pembimbing_id'] ?? null,
                $input['status'] ?? 'active',
                $input['id']
            ]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Prestasi berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
            }
            break;
            
        case 'DELETE':
            // Delete prestasi
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID is required for deletion']);
                break;
            }
            
            $id = (int)$_GET['id'];
            
            $stmt = $pdo->prepare("DELETE FROM prestasi WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Prestasi berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Prestasi tidak ditemukan']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Prestasi API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
