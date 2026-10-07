<?php
/**
 * UPLOAD HANDLER FOR SMA AL-KHOLIS WEBSITE
 * Handles file uploads with security validation and proper organization
 */

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_helper.php';

/**
 * Upload configuration
 */
class UploadConfig {
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const ALLOWED_DOCUMENT_TYPES = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    const UPLOAD_DIR = __DIR__ . '/../uploads/';
    
    // Subdirectories for different content types
    const SUBDIRS = [
        'berita' => 'berita/',
        'guru' => 'guru/',
        'karya-siswa' => 'karya-siswa/',
        'galeri' => 'galeri/',
        'pengumuman' => 'pengumuman/',
        'documents' => 'documents/'
    ];
}

/**
 * Main upload handler class
 */
class UploadHandler {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    private $errors = [];
    
    public function __construct($type = 'image', $subdir = '') {
        $this->uploadDir = UploadConfig::UPLOAD_DIR . $subdir;
        $this->maxFileSize = UploadConfig::MAX_FILE_SIZE;
        
        // Set allowed types based on upload type
        switch ($type) {
            case 'image':
                $this->allowedTypes = UploadConfig::ALLOWED_IMAGE_TYPES;
                break;
            case 'document':
                $this->allowedTypes = UploadConfig::ALLOWED_DOCUMENT_TYPES;
                break;
            default:
                $this->allowedTypes = array_merge(UploadConfig::ALLOWED_IMAGE_TYPES, UploadConfig::ALLOWED_DOCUMENT_TYPES);
        }
        
        // Create upload directory if it doesn't exist
        $this->createUploadDirectory();
    }
    
    /**
     * Create upload directory if it doesn't exist
     */
    private function createUploadDirectory() {
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                $this->errors[] = "Failed to create upload directory: {$this->uploadDir}";
            }
        }
    }
    
    /**
     * Upload a single file
     * @param array $file $_FILES array element
     * @param string $customName Custom filename (optional)
     * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
     */
    public function uploadFile($file, $customName = '') {
        // Reset errors
        $this->errors = [];
        
        // Validate file
        if (!$this->validateFile($file)) {
            return [
                'success' => false,
                'message' => implode(', ', $this->errors),
                'filename' => null
            ];
        }
        
        // Generate filename
        $filename = $this->generateFilename($file, $customName);
        $filepath = $this->uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Optimize image if it's an image file
            if (in_array($file['type'], UploadConfig::ALLOWED_IMAGE_TYPES)) {
                $this->optimizeImage($filepath);
            }
            
            return [
                'success' => true,
                'message' => 'File berhasil diupload',
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal memindahkan file yang diupload',
                'filename' => null
            ];
        }
    }
    
    /**
     * Upload multiple files
     * @param array $files $_FILES array
     * @return array Array of upload results
     */
    public function uploadMultipleFiles($files) {
        $results = [];
        
        // Handle multiple files
        if (is_array($files['name'])) {
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $results[] = $this->uploadFile($file);
            }
        } else {
            // Single file
            $results[] = $this->uploadFile($files);
        }
        
        return $results;
    }
    
    /**
     * Validate uploaded file
     * @param array $file
     * @return bool
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = "File terlalu besar. Maksimal " . $this->formatFileSize($this->maxFileSize);
            return false;
        }
        
        // Check file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            $this->errors[] = "Tipe file tidak diizinkan. Tipe yang diizinkan: " . implode(', ', array_map('pathinfo', $this->allowedTypes));
            return false;
        }
        
        // Additional security check
        if (!$this->isValidFileContent($file['tmp_name'], $file['type'])) {
            $this->errors[] = "File tidak valid atau rusak";
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if file content is valid
     * @param string $tmpName
     * @param string $type
     * @return bool
     */
    private function isValidFileContent($tmpName, $type) {
        // Check file signature (magic bytes)
        $handle = fopen($tmpName, 'rb');
        if (!$handle) {
            return false;
        }
        
        $signature = fread($handle, 8);
        fclose($handle);
        
        // Check for common file signatures
        $validSignatures = [
            'image/jpeg' => ["\xFF\xD8\xFF"],
            'image/png' => ["\x89PNG\r\n\x1A\n"],
            'image/gif' => ["GIF87a", "GIF89a"],
            'image/webp' => ["RIFF"],
            'application/pdf' => ["%PDF"]
        ];
        
        if (isset($validSignatures[$type])) {
            foreach ($validSignatures[$type] as $sig) {
                if (strpos($signature, $sig) === 0) {
                    return true;
                }
            }
            return false;
        }
        
        return true; // Allow other types without signature check
    }
    
    /**
     * Generate unique filename
     * @param array $file
     * @param string $customName
     * @return string
     */
    private function generateFilename($file, $customName = '') {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!empty($customName)) {
            $filename = $customName . '.' . $extension;
        } else {
            // Generate unique filename
            $timestamp = time();
            $random = bin2hex(random_bytes(8));
            $filename = $timestamp . '_' . $random . '.' . $extension;
        }
        
        // Ensure filename is unique
        $counter = 1;
        $originalFilename = $filename;
        
        while (file_exists($this->uploadDir . $filename)) {
            $nameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
            $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $filename = $nameWithoutExt . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Optimize image file
     * @param string $filepath
     */
    private function optimizeImage($filepath) {
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
        if ($width <= 1920 && $height <= 1080) {
            return;
        }
        
        // Calculate new dimensions
        $maxWidth = 1920;
        $maxHeight = 1080;
        
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
                case IMAGETYPE_GIF:
                    $originalImage = imagecreatefromgif($filepath);
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
                    case IMAGETYPE_GIF:
                        imagegif($newImage, $filepath);
                        break;
                }
                
                imagedestroy($originalImage);
                imagedestroy($newImage);
            }
        }
    }
    
    /**
     * Delete uploaded file
     * @param string $filename
     * @return bool
     */
    public function deleteFile($filename) {
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Get upload error message
     * @param int $errorCode
     * @return string
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "File melebihi ukuran maksimal yang diizinkan oleh server";
            case UPLOAD_ERR_FORM_SIZE:
                return "File melebihi ukuran maksimal yang diizinkan oleh form";
            case UPLOAD_ERR_PARTIAL:
                return "File hanya terupload sebagian";
            case UPLOAD_ERR_NO_FILE:
                return "Tidak ada file yang diupload";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Folder temporary tidak ditemukan";
            case UPLOAD_ERR_CANT_WRITE:
                return "Gagal menulis file ke disk";
            case UPLOAD_ERR_EXTENSION:
                return "Upload dihentikan oleh extension PHP";
            default:
                return "Error upload yang tidak diketahui";
        }
    }
    
    /**
     * Format file size for display
     * @param int $bytes
     * @return string
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Get file information
     * @param string $filename
     * @return array|null
     */
    public function getFileInfo($filename) {
        $filepath = $this->uploadDir . $filename;
        
        if (!file_exists($filepath)) {
            return null;
        }
        
        $fileInfo = [
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath),
            'type' => mime_content_type($filepath),
            'modified' => filemtime($filepath),
            'url' => $this->getFileUrl($filename)
        ];
        
        // Add image dimensions if it's an image
        if (in_array($fileInfo['type'], UploadConfig::ALLOWED_IMAGE_TYPES)) {
            $imageInfo = getimagesize($filepath);
            if ($imageInfo) {
                $fileInfo['width'] = $imageInfo[0];
                $fileInfo['height'] = $imageInfo[1];
            }
        }
        
        return $fileInfo;
    }
    
    /**
     * Get file URL
     * @param string $filename
     * @return string
     */
    public function getFileUrl($filename) {
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/admin');
        return $baseUrl . '/uploads/' . $filename;
    }
    
    /**
     * List all files in upload directory
     * @param string $pattern File pattern (optional)
     * @return array
     */
    public function listFiles($pattern = '*') {
        $files = [];
        $fileList = glob($this->uploadDir . $pattern);
        
        foreach ($fileList as $filepath) {
            if (is_file($filepath)) {
                $filename = basename($filepath);
                $fileInfo = $this->getFileInfo($filename);
                if ($fileInfo) {
                    $files[] = $fileInfo;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Clean up old files
     * @param int $daysOld Files older than this many days will be deleted
     * @return int Number of files deleted
     */
    public function cleanupOldFiles($daysOld = 30) {
        $deletedCount = 0;
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        
        $files = $this->listFiles();
        
        foreach ($files as $file) {
            if ($file['modified'] < $cutoffTime) {
                if ($this->deleteFile($file['filename'])) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
}

/**
 * Convenience functions for common upload operations
 */

/**
 * Upload image for specific content type
 * @param array $file $_FILES array element
 * @param string $contentType (berita, guru, karya-siswa, galeri, pengumuman)
 * @param string $customName Custom filename (optional)
 * @return array Upload result
 */
function uploadImage($file, $contentType, $customName = '') {
    if (!isset(UploadConfig::SUBDIRS[$contentType])) {
        return [
            'success' => false,
            'message' => 'Tipe konten tidak valid',
            'filename' => null
        ];
    }
    
    $handler = new UploadHandler('image', UploadConfig::SUBDIRS[$contentType]);
    return $handler->uploadFile($file, $customName);
}

/**
 * Upload document
 * @param array $file $_FILES array element
 * @param string $customName Custom filename (optional)
 * @return array Upload result
 */
function uploadDocument($file, $customName = '') {
    $handler = new UploadHandler('document', UploadConfig::SUBDIRS['documents']);
    return $handler->uploadFile($file, $customName);
}

/**
 * Delete uploaded file
 * @param string $filename
 * @param string $contentType
 * @return bool
 */
function deleteUploadedFile($filename, $contentType) {
    if (!isset(UploadConfig::SUBDIRS[$contentType])) {
        return false;
    }
    
    $handler = new UploadHandler('image', UploadConfig::SUBDIRS[$contentType]);
    return $handler->deleteFile($filename);
}

/**
 * Get file URL
 * @param string $filename
 * @param string $contentType
 * @return string
 */
function getUploadedFileUrl($filename, $contentType) {
    if (!isset(UploadConfig::SUBDIRS[$contentType])) {
        return '';
    }
    
    $handler = new UploadHandler('image', UploadConfig::SUBDIRS[$contentType]);
    return $handler->getFileUrl($filename);
}

/**
 * Validate file upload
 * @param array $file $_FILES array element
 * @param string $type 'image' or 'document'
 * @return array ['valid' => bool, 'message' => string]
 */
function validateFileUpload($file, $type = 'image') {
    $handler = new UploadHandler($type);
    
    // Create a temporary validation method
    $reflection = new ReflectionClass($handler);
    $validateMethod = $reflection->getMethod('validateFile');
    $validateMethod->setAccessible(true);
    
    $isValid = $validateMethod->invoke($handler, $file);
    
    return [
        'valid' => $isValid,
        'message' => $isValid ? 'File valid' : implode(', ', $handler->errors)
    ];
}

/**
 * Handle AJAX file upload
 * This function can be called from admin API endpoints
 */
function handleAjaxUpload() {
    header('Content-Type: application/json');
    
    // Check if user is logged in
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diupload']);
        return;
    }
    
    // Get upload parameters
    $contentType = $_POST['content_type'] ?? 'berita';
    $customName = $_POST['custom_name'] ?? '';
    
    // Upload file
    $result = uploadImage($_FILES['file'], $contentType, $customName);
    
    echo json_encode($result);
}
?> 