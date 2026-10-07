<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get single jadwal
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM jadwal WHERE id = ?");
                $stmt->execute([$id]);
                $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($jadwal) {
                    echo json_encode(['success' => true, 'data' => $jadwal]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Jadwal tidak ditemukan']);
                }
            } else {
                // Get all jadwal
                $stmt = $pdo->query("SELECT * FROM jadwal ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_mulai");
                $jadwal_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $jadwal_list]);
            }
            break;
            
        case 'POST':
            // Create new jadwal
            $hari = $_POST['hari'];
            $jam_mulai = $_POST['jam_mulai'];
            $jam_selesai = $_POST['jam_selesai'];
            $kelas = $_POST['kelas'];
            $mata_pelajaran = $_POST['mata_pelajaran'];
            $guru_id = (int)$_POST['guru_id'];
            $ruangan = $_POST['ruangan'] ?: null;
            $semester = $_POST['semester'];
            $tahun_ajaran = $_POST['tahun_ajaran'];
            
            // Validate time conflict
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jadwal WHERE hari = ? AND kelas = ? AND semester = ? AND tahun_ajaran = ? AND ((jam_mulai BETWEEN ? AND ?) OR (jam_selesai BETWEEN ? AND ?) OR (? BETWEEN jam_mulai AND jam_selesai))");
            $stmt->execute([$hari, $kelas, $semester, $tahun_ajaran, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai, $jam_mulai]);
            $conflicts = $stmt->fetchColumn();
            
            if ($conflicts > 0) {
                echo json_encode(['success' => false, 'message' => 'Konflik jadwal: Sudah ada pelajaran di waktu yang sama']);
                exit();
            }
            
            $stmt = $pdo->prepare("INSERT INTO jadwal (hari, jam_mulai, jam_selesai, kelas, mata_pelajaran, guru_id, ruangan, semester, tahun_ajaran, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $result = $stmt->execute([$hari, $jam_mulai, $jam_selesai, $kelas, $mata_pelajaran, $guru_id, $ruangan, $semester, $tahun_ajaran]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil ditambahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan jadwal']);
            }
            break;
            
        case 'PUT':
            // Update jadwal
            $id = (int)$_POST['id'];
            $hari = $_POST['hari'];
            $jam_mulai = $_POST['jam_mulai'];
            $jam_selesai = $_POST['jam_selesai'];
            $kelas = $_POST['kelas'];
            $mata_pelajaran = $_POST['mata_pelajaran'];
            $guru_id = (int)$_POST['guru_id'];
            $ruangan = $_POST['ruangan'] ?: null;
            $semester = $_POST['semester'];
            $tahun_ajaran = $_POST['tahun_ajaran'];
            
            // Validate time conflict (excluding current record)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jadwal WHERE hari = ? AND kelas = ? AND semester = ? AND tahun_ajaran = ? AND id != ? AND ((jam_mulai BETWEEN ? AND ?) OR (jam_selesai BETWEEN ? AND ?) OR (? BETWEEN jam_mulai AND jam_selesai))");
            $stmt->execute([$hari, $kelas, $semester, $tahun_ajaran, $id, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai, $jam_mulai]);
            $conflicts = $stmt->fetchColumn();
            
            if ($conflicts > 0) {
                echo json_encode(['success' => false, 'message' => 'Konflik jadwal: Sudah ada pelajaran di waktu yang sama']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE jadwal SET hari = ?, jam_mulai = ?, jam_selesai = ?, kelas = ?, mata_pelajaran = ?, guru_id = ?, ruangan = ?, semester = ?, tahun_ajaran = ? WHERE id = ?");
            $result = $stmt->execute([$hari, $jam_mulai, $jam_selesai, $kelas, $mata_pelajaran, $guru_id, $ruangan, $semester, $tahun_ajaran, $id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal mengupdate jadwal']);
            }
            break;
            
        case 'DELETE':
            // Delete jadwal
            $id = (int)$_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus jadwal']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Jadwal API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?> 