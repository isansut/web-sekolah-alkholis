<?php
/**
 * GURU IMAGE UPLOAD API
 * Handles image uploads for guru with random naming and duplicate prevention
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

// Handle POST request for image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diupload']);
            exit;
        }

        $file = $_FILES['image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Hanya file JPG/PNG yang diizinkan']);
            exit;
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 5MB']);
            exit;
        }

        // Generate random filename with timestamp
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        $filename = "guru_{$timestamp}_{$randomString}.{$extension}";
        
        // Ensure filename is unique
        $uploadDir = '../../uploads/guru/';
        $counter = 1;
        $originalFilename = $filename;
        
        while (file_exists($uploadDir . $filename)) {
            $nameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
            $filename = $nameWithoutExt . '_' . $counter . '.' . $extension;
            $counter++;
        }

        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        $filepath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Optimize image if it's too large
            optimizeImage($filepath);
            
            // Get file info
            $fileInfo = [
                'filename' => $filename,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'url' => '/alkholis/uploads/guru/' . $filename
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Gambar berhasil diupload',
                'data' => $fileInfo
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file']);
        }
        
    } catch (Exception $e) {
        logError("Guru upload error: " . $e->getMessage(), E_USER_ERROR);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Handle DELETE request to remove image
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $filename = $input['filename'] ?? '';
        
        if (empty($filename)) {
            echo json_encode(['success' => false, 'message' => 'Filename required']);
            exit;
        }
        
        // Validate filename (only allow guru_ prefix)
        if (!preg_match('/^guru_\d+_[a-f0-9]+\.(jpg|jpeg|png)$/i', $filename)) {
            echo json_encode(['success' => false, 'message' => 'Invalid filename']);
            exit;
        }
        
        $filepath = '../../uploads/guru/' . $filename;
        
        if (file_exists($filepath)) {
            if (unlink($filepath)) {
                echo json_encode(['success' => true, 'message' => 'Gambar berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
        }
        
    } catch (Exception $e) {
        logError("Guru delete error: " . $e->getMessage(), E_USER_ERROR);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

/**
 * Optimize image if it's too large
 * @param string $filepath
 */
function optimizeImage($filepath) {
    if (!extension_loaded('gd')) {
        return; // GD extension not available
    }
    
    $imageInfo = getimagesize($filepath);
    if (!$imageInfo) {
        return;
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Don't resize if image is already small
    if ($width <= 800 && $height <= 800) {
        return;
    }
    
    // Calculate new dimensions (max 800x800 for guru photos)
    $maxWidth = 800;
    $maxHeight = 800;
    
    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Load original image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($filepath);
                // Preserve transparency
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            default:
                return;
        }
        
        if ($originalImage) {
            // Resize image
            imagecopyresampled($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Save optimized image
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($newImage, $filepath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($newImage, $filepath, 8);
                    break;
            }
            
            imagedestroy($originalImage);
            imagedestroy($newImage);
        }
    }
}
?> 