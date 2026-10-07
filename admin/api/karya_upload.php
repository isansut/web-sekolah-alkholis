<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'karya' . DIRECTORY_SEPARATOR;
$publicBase = '/alkholis/uploads/karya/';

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_FILES['image'])) {
            echo json_encode(['success' => false, 'error' => 'File gambar tidak ditemukan']);
            exit;
        }
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Upload gagal (kode ' . $file['error'] . ')']);
            exit;
        }
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            echo json_encode(['success' => false, 'error' => 'Ukuran file maksimal 5MB']);
            exit;
        }

        // Validate mime using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        if (!isset($allowed[$mime])) {
            echo json_encode(['success' => false, 'error' => 'Hanya file JPG/PNG yang diizinkan']);
            exit;
        }
        $ext = $allowed[$mime];

        // Generate random name and ensure not exists
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        $base = "karya_{$timestamp}_{$random}";
        $filename = $base . ".{$ext}";
        $targetPath = $uploadDir . $filename;
        $counter = 1;
        while (file_exists($targetPath)) {
            $filename = $base . "_{$counter}.{$ext}";
            $targetPath = $uploadDir . $filename;
            $counter++;
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => false, 'error' => 'Gagal menyimpan file']);
            exit;
        }

        // Optional: resize to max 1000x1000
        try {
            if ($ext === 'jpg') {
                $image = imagecreatefromjpeg($targetPath);
            } else { // png
                $image = imagecreatefrompng($targetPath);
            }
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                $maxDim = 1000;
                if ($width > $maxDim || $height > $maxDim) {
                    $scale = min($maxDim / $width, $maxDim / $height);
                    $newW = (int)floor($width * $scale);
                    $newH = (int)floor($height * $scale);
                    $resized = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
                    if ($ext === 'jpg') {
                        imagejpeg($resized, $targetPath, 85);
                    } else {
                        imagepng($resized, $targetPath, 6);
                    }
                    imagedestroy($resized);
                }
                imagedestroy($image);
            }
        } catch (Throwable $t) {
            // ignore resize errors
        }

        echo json_encode([
            'success' => true,
            'image' => $filename,
            'filename' => $filename
        ]);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete by filename (basename only, whitelist pattern)
        parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
        $filename = isset($qs['filename']) ? basename($qs['filename']) : '';
        if ($filename === '' || !preg_match('/^karya_[0-9]+_[a-f0-9]{16}(?:_[0-9]+)?\.(jpg|png)$/i', $filename)) {
            echo json_encode(['success' => false, 'error' => 'Filename tidak valid']);
            exit;
        }
        $path = $uploadDir . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
        echo json_encode(['success' => true]);
        exit;
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
} 