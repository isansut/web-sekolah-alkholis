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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Tidak ada file yang diupload atau terjadi error');
        }

        $file = $_FILES['image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Hanya file JPG/PNG yang diizinkan');
        }
        
        // Validate file size (5MB max)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('Ukuran file maksimal 5MB');
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = '../../uploads/galeri/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'galeri_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Gagal menyimpan file');
        }
        
        // Return success response
        $imageUrl = 'uploads/galeri/' . $filename;
        
        echo json_encode([
            'success' => true,
            'message' => 'File berhasil diupload',
            'url' => $imageUrl,
            'filename' => $filename
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

// Handle file deletion
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $filename = $data['filename'] ?? '';
        
        if (empty($filename)) {
            throw new Exception('Filename tidak boleh kosong');
        }
        
        $filepath = '../../uploads/galeri/' . $filename;
        
        if (file_exists($filepath)) {
            if (unlink($filepath)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'File berhasil dihapus'
                ]);
            } else {
                throw new Exception('Gagal menghapus file');
            }
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'File tidak ditemukan'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
?> 