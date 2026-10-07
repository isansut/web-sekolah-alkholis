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

    // Quick status change
    if (isset($_GET['action']) && $_GET['action'] === 'status' && isset($_GET['id']) && isset($_GET['status'])) {
        $allowed = ['pending','approved','rejected'];
        $newStatus = strtolower($_GET['status']);
        if (!in_array($newStatus, $allowed, true)) {
            echo json_encode(['success' => false, 'error' => 'Status tidak valid']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE karya_siswa SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $ok = $stmt->execute([$newStatus, $_GET['id']]);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Status berhasil diubah']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Gagal mengubah status']);
        }
        exit;
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM karya_siswa WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $karya = $stmt->fetch();
                echo json_encode(['success' => (bool)$karya, 'data' => $karya, 'error' => $karya ? null : 'Karya tidak ditemukan']);
            } else {
                $stmt = $pdo->query("SELECT * FROM karya_siswa ORDER BY created_at DESC");
                $karya = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $karya]);
            }
            break;

        case 'POST':
            // Create - support FormData and JSON
            $isJson = isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
            $data = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;

            if (empty($data['title']) || empty($data['author']) || empty($data['category']) || empty($data['description'])) {
                echo json_encode(['success' => false, 'error' => 'Title, author, category, dan description harus diisi']);
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO karya_siswa (title, author, category, description, year, achievement, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['title'],
                $data['author'],
                $data['category'],
                $data['description'],
                $data['year'] ?? date('Y'),
                $data['achievement'] ?? null,
                $data['image'] ?? null,
                $data['status'] ?? 'pending'
            ]);

            $karyaId = $pdo->lastInsertId();
            $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
            $log->execute([$_SESSION['admin_id'] ?? 1, 'create_karya', "Created karya: {$data['title']}"]);
            echo json_encode(['success' => true, 'message' => 'Karya berhasil ditambahkan', 'id' => $karyaId]);
            break;

        case 'PUT':
            // Update - support x-www-form-urlencoded and JSON
            if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                parse_str(file_get_contents('php://input'), $data);
            }

            if (empty($data['id']) || empty($data['title']) || empty($data['author']) || empty($data['category']) || empty($data['description'])) {
                echo json_encode(['success' => false, 'error' => 'ID, title, author, category, dan description harus diisi']);
                break;
            }

            $stmt = $pdo->prepare("UPDATE karya_siswa SET title = ?, author = ?, category = ?, description = ?, year = ?, achievement = ?, image = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $ok = $stmt->execute([
                $data['title'],
                $data['author'],
                $data['category'],
                $data['description'],
                $data['year'] ?? date('Y'),
                $data['achievement'] ?? null,
                $data['image'] ?? null,
                $data['status'] ?? 'pending',
                $data['id']
            ]);

            if ($ok) {
                $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $log->execute([$_SESSION['admin_id'] ?? 1, 'update_karya', "Updated karya: {$data['title']}"]);
                echo json_encode(['success' => true, 'message' => 'Karya berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal memperbarui karya']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM karya_siswa WHERE id = ?");
                $ok = $stmt->execute([$_GET['id']]);
                if ($ok) {
                    $log = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $log->execute([$_SESSION['admin_id'] ?? 1, 'delete_karya', "Deleted karya ID: {$_GET['id']}"]);
                    echo json_encode(['success' => true, 'message' => 'Karya berhasil dihapus']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menghapus karya']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'ID karya diperlukan']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
            break;
    }

} catch (Exception $e) {
    error_log("Karya Siswa API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
?> 