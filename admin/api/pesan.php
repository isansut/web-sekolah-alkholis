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

    // Ensure contact_messages table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        priority ENUM('low','medium','high') DEFAULT 'medium',
        status ENUM('unread','read','replied') DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Seed one sample message if empty
    $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM contact_messages")->fetch()['c'];
    if ($count === 0) {
        $stmtSeed = $pdo->prepare("INSERT INTO contact_messages (name,whatsapp,subject,message,priority,status) VALUES (?,?,?,?,?,?)");
        $stmtSeed->execute([
            'Demo User','08123456789','Contoh Pesan','Ini adalah pesan contoh untuk pengujian modul kontak.','medium','unread'
        ]);
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all pesan or single pesan
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $pesan = $stmt->fetch();
                
                if ($pesan) {
                    echo json_encode(['success' => true, 'data' => $pesan]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Pesan tidak ditemukan']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
                $pesan = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $pesan]);
            }
            break;
            
        case 'POST':
            // Add new pesan from contact form
            try {
                $name = $_POST['name'] ?? '';
                $whatsapp = $_POST['whatsapp'] ?? '';
                $subject = $_POST['subject'] ?? '';
                $message = $_POST['message'] ?? '';
                
                // Validation
                if (empty($name) || empty($whatsapp) || empty($subject) || empty($message)) {
                    echo json_encode(['success' => false, 'error' => 'Semua field harus diisi']);
                    break;
                }
                
                // Clean whatsapp number
                $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);
                if (strlen($whatsapp) < 10 || strlen($whatsapp) > 15) {
                    echo json_encode(['success' => false, 'error' => 'Format nomor WhatsApp tidak valid']);
                    break;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages (name, whatsapp, subject, message, priority, status) 
                    VALUES (?, ?, ?, ?, 'medium', 'unread')
                ");
                
                $result = $stmt->execute([$name, $whatsapp, $subject, $message]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Pesan berhasil ditambahkan']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menambahkan pesan']);
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        case 'PUT':
            // Update pesan status (mark as read, replied, etc.)
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['status'])) {
                echo json_encode(['success' => false, 'error' => 'ID dan status harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE contact_messages 
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['status'],
                $data['id']
            ]);
            
            if ($result) {
                // Log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['admin_id'], 'update_pesan', "Updated pesan status to: {$data['status']}"]);
                
                echo json_encode(['success' => true, 'message' => 'Status pesan berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate status pesan']);
            }
            break;
            
        case 'DELETE':
            // Delete pesan
            if (isset($_GET['id'])) {
                // Delete single pesan
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                $result = $stmt->execute([$_GET['id']]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Pesan berhasil dihapus']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menghapus pesan']);
                }
            } else {
                // Check if it's a bulk delete action
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (isset($data['action']) && $data['action'] === 'delete_all_read') {
                    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE status = 'read'");
                    $result = $stmt->execute();
                    
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Semua pesan yang sudah dibaca berhasil dihapus']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Gagal menghapus pesan yang sudah dibaca']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Action tidak valid']);
                }
            }
            break;
            
        case 'PATCH':
            // Mark all messages as read
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['action']) && $data['action'] === 'mark_all_read') {
                $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read', updated_at = CURRENT_TIMESTAMP WHERE status = 'unread'");
                $result = $stmt->execute();
                
                if ($result) {
                    // Log activity
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'], 'mark_all_read', 'Marked all messages as read']);
                    
                    echo json_encode(['success' => true, 'message' => 'Semua pesan ditandai sebagai sudah dibaca']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menandai semua pesan']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Action tidak valid']);
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