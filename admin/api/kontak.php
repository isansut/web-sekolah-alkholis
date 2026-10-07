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

    // Ensure contact_settings table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL,
        description VARCHAR(255),
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Seed default contact settings if empty
    $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM contact_settings")->fetch()['c'];
    if ($count === 0) {
        $defaultSettings = [
            ['company_name', 'SMA Al-Kholis', 'Nama Sekolah'],
            ['address', 'Jl. Contoh No. 123, Kota, Provinsi', 'Alamat Sekolah'],
            ['phone', '021-1234567', 'Nomor Telepon'],
            ['whatsapp', '08123456789', 'Nomor WhatsApp'],
            ['email', 'info@alkholis.sch.id', 'Email Sekolah'],
            ['website', 'www.alkholis.sch.id', 'Website Sekolah'],
            ['facebook', 'https://facebook.com/alkholis', 'Link Facebook'],
            ['instagram', 'https://instagram.com/alkholis', 'Link Instagram'],
            ['youtube', 'https://youtube.com/alkholis', 'Link YouTube'],
            ['office_hours', 'Senin - Jumat: 07:00 - 15:00 WIB', 'Jam Operasional'],
            ['latitude', '-6.1750', 'Latitude (Koordinat Latitude)'],
            ['longitude', '106.8222', 'Longitude (Koordinat Longitude)'],
            ['map_embed', '<iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>', 'Embed Google Maps'],
            ['about_text', 'SMA Al-Kholis adalah sekolah menengah atas yang berkomitmen untuk memberikan pendidikan berkualitas dan membentuk karakter siswa yang unggul dalam prestasi dan berakhlak mulia.', 'Tentang Sekolah'],
            ['vision', 'Menjadi sekolah unggulan yang menghasilkan lulusan berkualitas, berakhlak mulia, dan siap menghadapi tantangan global.', 'Visi Sekolah'],
            ['mission', 'Menyelenggarakan pendidikan berkualitas, mengembangkan potensi siswa secara optimal, dan membentuk karakter yang berlandaskan nilai-nilai Islam.', 'Misi Sekolah']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO contact_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get all contact settings or single setting
            if (isset($_GET['key'])) {
                $stmt = $pdo->prepare("SELECT * FROM contact_settings WHERE setting_key = ?");
                $stmt->execute([$_GET['key']]);
                $setting = $stmt->fetch();
                
                if ($setting) {
                    echo json_encode(['success' => true, 'data' => $setting]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Setting tidak ditemukan']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM contact_settings ORDER BY id");
                $settings = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $settings]);
            }
            break;
            
        case 'POST':
            // Update contact setting
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['key']) || !isset($data['value'])) {
                echo json_encode(['success' => false, 'error' => 'Key dan value harus diisi']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO contact_settings (setting_key, setting_value, description) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                description = VALUES(description),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $result = $stmt->execute([
                $data['key'],
                $data['value'],
                $data['description'] ?? ''
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Setting berhasil diupdate']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal mengupdate setting']);
            }
            break;
            
        case 'PUT':
            // Update multiple settings
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!is_array($data)) {
                echo json_encode(['success' => false, 'error' => 'Data harus berupa array']);
                break;
            }
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO contact_settings (setting_key, setting_value, description) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value),
                    description = VALUES(description),
                    updated_at = CURRENT_TIMESTAMP
                ");
                
                foreach ($data as $setting) {
                    if (isset($setting['key']) && isset($setting['value'])) {
                        $stmt->execute([
                            $setting['key'],
                            $setting['value'],
                            $setting['description'] ?? ''
                        ]);
                    }
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Semua setting berhasil diupdate']);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
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