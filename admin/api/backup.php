 <?php
/**
 * BACKUP API ENDPOINT FOR ADMIN PANEL
 * Handles backup operations for database and files
 */

// Include necessary files
require_once '../../includes/auth_helper.php';
require_once '../../backup_system.php';
require_once '../../includes/error_handler.php';

// Set headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if request method is POST or GET
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Create manual backup
            $result = createManualBackup();
            break;
            
        case 'create_auto':
            // Create automatic backup
            $result = createAutoBackup();
            break;
            
        case 'list':
            // List available backups
            $backups = listBackups();
            $result = [
                'success' => true,
                'backups' => $backups,
                'count' => count($backups)
            ];
            break;
            
        case 'restore':
            // Restore backup
            $backupPath = $_POST['backup_path'] ?? '';
            if (empty($backupPath)) {
                throw new Exception('Path backup diperlukan');
            }
            
            // Validate backup path
            $backupDir = __DIR__ . '/../../backups/';
            $fullPath = $backupDir . basename($backupPath);
            
            if (!file_exists($fullPath)) {
                throw new Exception('File backup tidak ditemukan');
            }
            
            $result = restoreBackup($fullPath);
            break;
            
        case 'delete':
            // Delete backup
            $backupPath = $_POST['backup_path'] ?? '';
            if (empty($backupPath)) {
                throw new Exception('Path backup diperlukan');
            }
            
            // Validate backup path
            $backupDir = __DIR__ . '/../../backups/';
            $fullPath = $backupDir . basename($backupPath);
            
            if (!file_exists($fullPath)) {
                throw new Exception('File backup tidak ditemukan');
            }
            
            if (unlink($fullPath)) {
                $result = [
                    'success' => true,
                    'message' => 'Backup berhasil dihapus'
                ];
            } else {
                throw new Exception('Gagal menghapus backup');
            }
            break;
            
        case 'cleanup':
            // Clean up old backups
            $deletedCount = cleanupOldBackups();
            $result = [
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} backup lama",
                'deleted_count' => $deletedCount
            ];
            break;
            
        case 'download':
            // Download backup file
            $backupPath = $_GET['backup_path'] ?? '';
            if (empty($backupPath)) {
                throw new Exception('Path backup diperlukan');
            }
            
            // Validate backup path
            $backupDir = __DIR__ . '/../../backups/';
            $fullPath = $backupDir . basename($backupPath);
            
            if (!file_exists($fullPath)) {
                throw new Exception('File backup tidak ditemukan');
            }
            
            // Set headers for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
            header('Content-Length: ' . filesize($fullPath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            // Output file
            readfile($fullPath);
            exit;
            
        case 'info':
            // Get backup information
            $backupPath = $_GET['backup_path'] ?? '';
            if (empty($backupPath)) {
                throw new Exception('Path backup diperlukan');
            }
            
            // Validate backup path
            $backupDir = __DIR__ . '/../../backups/';
            $fullPath = $backupDir . basename($backupPath);
            
            if (!file_exists($fullPath)) {
                throw new Exception('File backup tidak ditemukan');
            }
            
            $backupSystem = new BackupSystem();
            $backupInfo = $backupSystem->getBackupInfo(basename($fullPath));
            
            if ($backupInfo) {
                $result = [
                    'success' => true,
                    'backup_info' => $backupInfo
                ];
            } else {
                throw new Exception('Tidak dapat membaca informasi backup');
            }
            break;
            
        case 'status':
            // Get backup system status
            $backupSystem = new BackupSystem();
            $backups = listBackups();
            
            // Calculate total size
            $totalSize = 0;
            foreach ($backups as $backup) {
                $totalSize += $backup['size'];
            }
            
            // Check if auto backup should run
            $shouldRunAuto = $backupSystem->shouldRunAutoBackup();
            
            $result = [
                'success' => true,
                'status' => [
                    'total_backups' => count($backups),
                    'total_size' => $totalSize,
                    'total_size_formatted' => formatFileSize($totalSize),
                    'auto_backup_enabled' => BackupConfig::AUTO_BACKUP_ENABLED,
                    'should_run_auto' => $shouldRunAuto,
                    'backup_retention_days' => BackupConfig::BACKUP_RETENTION_DAYS,
                    'max_backup_size' => BackupConfig::MAX_BACKUP_SIZE,
                    'max_backup_size_formatted' => formatFileSize(BackupConfig::MAX_BACKUP_SIZE)
                ]
            ];
            break;
            
        default:
            throw new Exception('Aksi tidak valid');
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    logError("Backup API error: " . $e->getMessage(), E_USER_ERROR);
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Format file size helper function
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?> 