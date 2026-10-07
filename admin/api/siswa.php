<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();

    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS siswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(150) NOT NULL,
        nisn VARCHAR(20) UNIQUE,
        kelas VARCHAR(20),
        jurusan VARCHAR(50),
        email VARCHAR(100),
        phone VARCHAR(20),
        alamat TEXT,
        photo VARCHAR(500),
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Helper: normalize empty strings to NULL
    $toNullable = function ($value) {
        if (!isset($value)) return null;
        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }
        return $value;
    };
    $normalizeNisn = function ($value) use ($toNullable) {
        $v = $toNullable($value);
        if (is_string($v) && $v === '-') return null;
        return $v;
    };

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Filters: kelas, jurusan, status, search
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare('SELECT * FROM siswa WHERE id = ?');
                $stmt->execute([$_GET['id']]);
                $row = $stmt->fetch();
                echo json_encode(['success' => (bool)$row, 'data' => $row, 'error' => $row ? null : 'Siswa tidak ditemukan']);
                break;
            }
            $where = [];
            $params = [];
            if (!empty($_GET['kelas'])) { $where[] = 'kelas = ?'; $params[] = $_GET['kelas']; }
            if (!empty($_GET['jurusan'])) { $where[] = 'jurusan = ?'; $params[] = $_GET['jurusan']; }
            if (!empty($_GET['status'])) { $where[] = 'status = ?'; $params[] = $_GET['status']; }
            if (!empty($_GET['q'])) { $where[] = '(nama LIKE ? OR nisn LIKE ?)'; $params[] = '%' . $_GET['q'] . '%'; $params[] = '%' . $_GET['q'] . '%'; }
            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
            $stmt = $pdo->prepare("SELECT * FROM siswa $whereSql ORDER BY kelas, nama");
            $stmt->execute($params);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'POST':
            // Create - support JSON/FormData
            $isJson = isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
            $data = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
            if (empty($data['nama'])) {
                echo json_encode(['success' => false, 'error' => 'Nama wajib diisi']);
                break;
            }
            $nisn = $normalizeNisn($data['nisn'] ?? null);
            $kelas = $toNullable($data['kelas'] ?? null);
            $jurusan = $toNullable($data['jurusan'] ?? null);
            $alamat = $toNullable($data['alamat'] ?? null);
            $photoUrl = $toNullable($data['photo'] ?? null);
            $status = $toNullable($data['status'] ?? 'active') ?? 'active';

            try {
                $stmt = $pdo->prepare("INSERT INTO siswa (nama, nisn, kelas, jurusan, alamat, photo, status) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([
                    trim($data['nama']),
                    $nisn,
                    $kelas,
                    $jurusan,
                    $alamat,
                    $photoUrl,
                    $status
                ]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    echo json_encode(['success' => false, 'error' => 'NISN sudah digunakan']);
                    break;
                }
                throw $e;
            }

            $id = $pdo->lastInsertId();
            $log = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details) VALUES (?,?,?)');
            $log->execute([$_SESSION['admin_id'] ?? 1, 'create_siswa', 'Created siswa: ' . $data['nama']]);
            echo json_encode(['success' => true, 'message' => 'Siswa berhasil ditambahkan', 'id' => $id]);
            break;

        case 'PUT':
            // Update - support JSON/x-www-form-urlencoded
            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                parse_str(file_get_contents('php://input'), $data);
            }
            if (empty($data['id']) || empty($data['nama'])) {
                echo json_encode(['success' => false, 'error' => 'ID dan nama wajib diisi']);
                break;
            }
            $nisn = $normalizeNisn($data['nisn'] ?? null);
            $kelas = $toNullable($data['kelas'] ?? null);
            $jurusan = $toNullable($data['jurusan'] ?? null);
            $alamat = $toNullable($data['alamat'] ?? null);
            $photoUrl = $toNullable($data['photo'] ?? null);
            $status = $toNullable($data['status'] ?? 'active') ?? 'active';

            try {
                $stmt = $pdo->prepare("UPDATE siswa SET nama=?, nisn=?, kelas=?, jurusan=?, alamat=?, photo=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id = ?");
                $ok = $stmt->execute([
                    trim($data['nama']),
                    $nisn,
                    $kelas,
                    $jurusan,
                    $alamat,
                    $photoUrl,
                    $status,
                    $data['id']
                ]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    echo json_encode(['success' => false, 'error' => 'NISN sudah digunakan']);
                    break;
                }
                throw $e;
            }

            if ($ok) {
                $log = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details) VALUES (?,?,?)');
                $log->execute([$_SESSION['admin_id'] ?? 1, 'update_siswa', 'Updated siswa: ' . $data['nama']]);
                echo json_encode(['success' => true, 'message' => 'Siswa berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate siswa']);
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'ID siswa harus diisi']);
                break;
            }
            $get = $pdo->prepare('SELECT nama FROM siswa WHERE id = ?');
            $get->execute([$_GET['id']]);
            $row = $get->fetch();
            $stmt = $pdo->prepare('DELETE FROM siswa WHERE id = ?');
            $ok = $stmt->execute([$_GET['id']]);
            if ($ok) {
                if ($row) {
                    $log = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details) VALUES (?,?,?)');
                    $log->execute([$_SESSION['admin_id'] ?? 1, 'delete_siswa', 'Deleted siswa: ' . $row['nama']]);
                }
                echo json_encode(['success' => true, 'message' => 'Siswa berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus siswa']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} 