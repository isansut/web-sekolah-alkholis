<?php
/**
 * UPLOAD API ENDPOINT FOR ADMIN PANEL
 * Handles file uploads for various content types
 */

// Include necessary files
require_once '../../includes/auth_helper.php';
require_once '../../includes/upload_handler.php';
require_once '../../includes/error_handler.php';

// Set headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get upload parameters
    $contentType = $_POST['content_type'] ?? '';
    $customName = $_POST['custom_name'] ?? '';
    $action = $_POST['action'] ?? 'upload';
    
    // Validate content type
    $allowedTypes = ['berita', 'guru', 'karya-siswa', 'galeri', 'pengumuman'];
    if (!in_array($contentType, $allowedTypes)) {
        throw new Exception('Tipe konten tidak valid');
    }
    
    switch ($action) {
        case 'upload':
            // Handle single file upload
            if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Tidak ada file yang diupload');
            }
            
            $result = uploadImage($_FILES['file'], $contentType, $customName);
            break;
            
        case 'upload_multiple':
            // Handle multiple file upload
            if (!isset($_FILES['files'])) {
                throw new Exception('Tidak ada file yang diupload');
            }
            
            $handler = new UploadHandler('image', UploadConfig::SUBDIRS[$contentType]);
            $result = $handler->uploadMultipleFiles($_FILES['files']);
            
            // Format result for multiple files
            $successCount = 0;
            $failedCount = 0;
            $uploadedFiles = [];
            
            foreach ($result as $fileResult) {
                if ($fileResult['success']) {
                    $successCount++;
                    $uploadedFiles[] = $fileResult;
                } else {
                    $failedCount++;
                }
            }
            
            $result = [
                'success' => $successCount > 0,
                'message' => "Berhasil upload {$successCount} file" . ($failedCount > 0 ? ", gagal {$failedCount} file" : ''),
                'uploaded_files' => $uploadedFiles,
                'total_files' => count($result),
                'success_count' => $successCount,
                'failed_count' => $failedCount
            ];
            break;
            
        case 'delete':
            // Handle file deletion
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                throw new Exception('Nama file diperlukan');
            }
            
            $result = deleteUploadedFile($filename, $contentType);
            $result = [
                'success' => $result,
                'message' => $result ? 'File berhasil dihapus' : 'Gagal menghapus file'
            ];
            break;
            
        case 'list':
            // List uploaded files
            $handler = new UploadHandler('image', UploadConfig::SUBDIRS[$contentType]);
            $files = $handler->listFiles();
            
            $result = [
                'success' => true,
                'files' => $files,
                'count' => count($files)
            ];
            break;
            
        case 'validate':
            // Validate file without uploading
            if (!isset($_FILES['file'])) {
                throw new Exception('Tidak ada file yang diupload');
            }
            
            $validation = validateFileUpload($_FILES['file'], 'image');
            $result = [
                'success' => $validation['valid'],
                'message' => $validation['message']
            ];
            break;
            
        default:
            throw new Exception('Aksi tidak valid');
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    logError("Upload API error: " . $e->getMessage(), E_USER_ERROR);
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 