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
            // Get all jadwal or single jadwal
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("
                    SELECT j.*, g.nama as guru_nama 
                    FROM jadwal j 
                    LEFT JOIN guru g ON j.guru_id = g.id 
                    WHERE j.id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $jadwal = $stmt->fetch();
                
                if ($jadwal) {
                    echo json_encode(['success' => true, 'data' => $jadwal]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Jadwal tidak ditemukan']);
                }
            } else {
                // Get jadwal with filters
                $where = "WHERE 1=1";
                $params = [];
                
                if (isset($_GET['kelas']) && !empty($_GET['kelas'])) {
                    $where .= " AND j.kelas = ?";
                    $params[] = $_GET['kelas'];
                }
                
                if (isset($_GET['hari']) && !empty($_GET['hari'])) {
                    $where .= " AND j.hari = ?";
                    $params[] = $_GET['hari'];
                }
                
                if (isset($_GET['semester']) && !empty($_GET['semester'])) {
                    $where .= " AND j.semester = ?";
                    $params[] = $_GET['semester'];
                }
                
                $stmt = $pdo->prepare("
                    SELECT j.*, g.nama as guru_nama 
                    FROM jadwal j 
                    LEFT JOIN guru g ON j.guru_id = g.id 
                    {$where}
                    ORDER BY j.kelas, FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai
                ");
                $stmt->execute($params);
                $jadwal = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $jadwal]);
            }
            break;
            
        case 'POST':
            // Create new jadwal
            $data = [];
            if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                $data = $_POST;
            }
            
            // Validate required fields
            if (empty($data['kelas']) || empty($data['hari']) || empty($data['jam_mulai']) || 
                empty($data['jam_selesai']) || empty($data['mata_pelajaran'])) {
                echo json_encode(['success' => false, 'error' => 'Semua field wajib diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO jadwal (kelas, hari, jam_mulai, jam_selesai, mata_pelajaran, guru_id, ruangan, semester, tahun_ajaran, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $guruId = $data['guru_id'] ?? null;
            $ruangan = $data['ruangan'] ?? null;
            $semester = $data['semester'] ?? 'Ganjil';
            $tahunAjaran = $data['tahun_ajaran'] ?? '2024/2025';
            $status = $data['status'] ?? 'active';
            
            $stmt->execute([
                $data['kelas'],
                $data['hari'],
                $data['jam_mulai'],
                $data['jam_selesai'],
                $data['mata_pelajaran'],
                $guruId,
                $ruangan,
                $semester,
                $tahunAjaran,
                $status
            ]);
            
            $jadwalId = $pdo->lastInsertId();
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['admin_id'] ?? 1, 'create_jadwal', "Created jadwal: {$data['kelas']} - {$data['mata_pelajaran']}"]);
            
            echo json_encode(['success' => true, 'message' => 'Jadwal berhasil ditambahkan', 'id' => $jadwalId]);
            break;
            
        case 'PUT':
            // Update jadwal
            $data = [];
            if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
            } else {
                parse_str(file_get_contents('php://input'), $data);
            }
            
            // Validate required fields
            if (empty($data['id']) || empty($data['kelas']) || empty($data['hari']) || 
                empty($data['jam_mulai']) || empty($data['jam_selesai']) || empty($data['mata_pelajaran'])) {
                echo json_encode(['success' => false, 'error' => 'Semua field wajib diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE jadwal 
                SET kelas = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, mata_pelajaran = ?, 
                    guru_id = ?, ruangan = ?, semester = ?, tahun_ajaran = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $guruId = $data['guru_id'] ?? null;
            $ruangan = $data['ruangan'] ?? null;
            $semester = $data['semester'] ?? 'Ganjil';
            $tahunAjaran = $data['tahun_ajaran'] ?? '2024/2025';
            $status = $data['status'] ?? 'active';
            
            $result = $stmt->execute([
                $data['kelas'],
                $data['hari'],
                $data['jam_mulai'],
                $data['jam_selesai'],
                $data['mata_pelajaran'],
                $guruId,
                $ruangan,
                $semester,
                $tahunAjaran,
                $status,
                $data['id']
            ]);
            
            if ($result) {
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['admin_id'] ?? 1, 'update_jadwal', "Updated jadwal: {$data['kelas']} - {$data['mata_pelajaran']}"]);
                
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate jadwal']);
            }
            break;
            
        case 'DELETE':
            // Delete jadwal
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID jadwal harus diisi']);
                break;
            }
            
            // Get jadwal info for logging
            $stmt = $pdo->prepare("SELECT kelas, mata_pelajaran FROM jadwal WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $jadwal = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?");
            $result = $stmt->execute([$_GET['id']]);
            
            if ($result) {
                // Log activity
                if ($jadwal) {
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'] ?? 1, 'delete_jadwal', "Deleted jadwal: {$jadwal['kelas']} - {$jadwal['mata_pelajaran']}"]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus jadwal']);
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