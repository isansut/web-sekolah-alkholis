<?php
/**
 * BACKUP SYSTEM FOR SMA AL-KHOLIS WEBSITE
 * Handles automatic database and file backups
 */

// Include necessary files
require_once 'config/database.php';
require_once 'includes/auth_helper.php';
require_once 'includes/error_handler.php';

/**
 * Backup configuration
 */
class BackupConfig {
    const BACKUP_DIR = __DIR__ . '/backups/';
    const MAX_BACKUP_SIZE = 100 * 1024 * 1024; // 100MB
    const BACKUP_RETENTION_DAYS = 30;
    const AUTO_BACKUP_ENABLED = true;
    const BACKUP_TIME = '02:00'; // Daily backup at 2 AM
    
    // Files to exclude from backup
    const EXCLUDE_FILES = [
        'backups/',
        'logs/',
        'temp/',
        'cache/',
        '.git/',
        'node_modules/',
        'vendor/',
        '*.log',
        '*.tmp',
        '*.cache'
    ];
    
    // Database tables to exclude from backup
    const EXCLUDE_TABLES = [
        'activity_logs',
        'temp_*'
    ];
}

/**
 * Main backup system class
 */
class BackupSystem {
    private $backupDir;
    private $dbConfig;
    
    public function __construct() {
        $this->backupDir = BackupConfig::BACKUP_DIR;
        $this->dbConfig = [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS
        ];
        
        // Create backup directory if it doesn't exist
        $this->createBackupDirectory();
    }
    
    /**
     * Create backup directory if it doesn't exist
     */
    private function createBackupDirectory() {
        if (!file_exists($this->backupDir)) {
            if (!mkdir($this->backupDir, 0755, true)) {
                throw new Exception("Failed to create backup directory: {$this->backupDir}");
            }
        }
    }
    
    /**
     * Create full backup (database + files)
     * @param string $type 'manual' or 'auto'
     * @return array Backup result
     */
    public function createFullBackup($type = 'manual') {
        $startTime = microtime(true);
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = "backup_{$type}_{$timestamp}";
        $backupPath = $this->backupDir . $backupName;
        
        try {
            // Create backup directory
            if (!mkdir($backupPath, 0755, true)) {
                throw new Exception("Failed to create backup directory: {$backupPath}");
            }
            
            $results = [
                'database' => $this->backupDatabase($backupPath),
                'files' => $this->backupFiles($backupPath),
                'metadata' => $this->createBackupMetadata($backupPath, $type, $startTime)
            ];
            
            // Create backup archive
            $archivePath = $this->createBackupArchive($backupPath, $backupName);
            
            // Clean up temporary files
            $this->cleanupBackupDirectory($backupPath);
            
            // Log backup success
            logError("Backup created successfully: {$archivePath}", E_USER_NOTICE);
            
            return [
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'backup_path' => $archivePath,
                'backup_size' => $this->formatFileSize(filesize($archivePath)),
                'duration' => round(microtime(true) - $startTime, 2),
                'results' => $results
            ];
            
        } catch (Exception $e) {
            // Clean up on failure
            $this->cleanupBackupDirectory($backupPath);
            
            logError("Backup failed: " . $e->getMessage(), E_USER_ERROR);
            
            return [
                'success' => false,
                'message' => 'Backup gagal: ' . $e->getMessage(),
                'backup_path' => null
            ];
        }
    }
    
    /**
     * Backup database
     * @param string $backupPath
     * @return array
     */
    private function backupDatabase($backupPath) {
        $dbFile = $backupPath . '/database.sql';
        $tables = $this->getDatabaseTables();
        
        $sql = "-- Database Backup for SMA Al-Kholis\n";
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . $this->dbConfig['name'] . "\n\n";
        
        foreach ($tables as $table) {
            if ($this->shouldExcludeTable($table)) {
                continue;
            }
            
            // Get table structure
            $structure = $this->getTableStructure($table);
            $sql .= $structure . "\n\n";
            
            // Get table data
            $data = $this->getTableData($table);
            if (!empty($data)) {
                $sql .= $data . "\n\n";
            }
        }
        
        // Write SQL file
        if (file_put_contents($dbFile, $sql) === false) {
            throw new Exception("Failed to write database backup file");
        }
        
        return [
            'file' => $dbFile,
            'size' => filesize($dbFile),
            'tables' => count($tables)
        ];
    }
    
    /**
     * Get database tables
     * @return array
     */
    private function getDatabaseTables() {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SHOW TABLES");
            $tables = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            
            return $tables;
        } catch (Exception $e) {
            throw new Exception("Failed to get database tables: " . $e->getMessage());
        }
    }
    
    /**
     * Check if table should be excluded
     * @param string $table
     * @return bool
     */
    private function shouldExcludeTable($table) {
        foreach (BackupConfig::EXCLUDE_TABLES as $pattern) {
            if (fnmatch($pattern, $table)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get table structure
     * @param string $table
     * @return string
     */
    private function getTableStructure($table) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            
            return "-- Table structure for table `{$table}`\n" . $row[1] . ";";
        } catch (Exception $e) {
            throw new Exception("Failed to get structure for table {$table}: " . $e->getMessage());
        }
    }
    
    /**
     * Get table data
     * @param string $table
     * @return string
     */
    private function getTableData($table) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($rows)) {
                return "";
            }
            
            $sql = "-- Data for table `{$table}`\n";
            $sql .= "INSERT INTO `{$table}` VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = "(" . implode(', ', $rowValues) . ")";
            }
            
            $sql .= implode(",\n", $values) . ";";
            
            return $sql;
        } catch (Exception $e) {
            throw new Exception("Failed to get data for table {$table}: " . $e->getMessage());
        }
    }
    
    /**
     * Backup files
     * @param string $backupPath
     * @return array
     */
    private function backupFiles($backupPath) {
        $filesDir = $backupPath . '/files';
        $projectRoot = __DIR__;
        
        if (!mkdir($filesDir, 0755, true)) {
            throw new Exception("Failed to create files backup directory");
        }
        
        $files = $this->getProjectFiles($projectRoot);
        $backedUpFiles = 0;
        $totalSize = 0;
        
        foreach ($files as $file) {
            $relativePath = str_replace($projectRoot . '/', '', $file);
            $backupFile = $filesDir . '/' . $relativePath;
            $backupDir = dirname($backupFile);
            
            // Create directory if it doesn't exist
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Copy file
            if (copy($file, $backupFile)) {
                $backedUpFiles++;
                $totalSize += filesize($file);
            }
        }
        
        return [
            'directory' => $filesDir,
            'files_count' => $backedUpFiles,
            'total_size' => $totalSize
        ];
    }
    
    /**
     * Get project files
     * @param string $directory
     * @return array
     */
    private function getProjectFiles($directory) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                
                // Check if file should be excluded
                if (!$this->shouldExcludeFile($filePath)) {
                    $files[] = $filePath;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Check if file should be excluded
     * @param string $filePath
     * @return bool
     */
    private function shouldExcludeFile($filePath) {
        $relativePath = str_replace(__DIR__ . '/', '', $filePath);
        
        foreach (BackupConfig::EXCLUDE_FILES as $pattern) {
            if (fnmatch($pattern, $relativePath) || fnmatch($pattern, basename($filePath))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Create backup metadata
     * @param string $backupPath
     * @param string $type
     * @param float $startTime
     * @return array
     */
    private function createBackupMetadata($backupPath, $type, $startTime) {
        $metadata = [
            'backup_type' => $type,
            'created_at' => date('Y-m-d H:i:s'),
            'duration' => round(microtime(true) - $startTime, 2),
            'php_version' => PHP_VERSION,
            'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_info' => [
                'host' => $this->dbConfig['host'],
                'name' => $this->dbConfig['name'],
                'tables_count' => count($this->getDatabaseTables())
            ],
            'files_info' => [
                'project_root' => __DIR__,
                'backup_path' => $backupPath
            ]
        ];
        
        $metadataFile = $backupPath . '/metadata.json';
        file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        
        return $metadata;
    }
    
    /**
     * Create backup archive
     * @param string $backupPath
     * @param string $backupName
     * @return string
     */
    private function createBackupArchive($backupPath, $backupName) {
        $archivePath = $this->backupDir . $backupName . '.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Failed to create backup archive");
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($backupPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $relativePath = str_replace($backupPath . '/', '', $filePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        
        return $archivePath;
    }
    
    /**
     * Clean up backup directory
     * @param string $backupPath
     */
    private function cleanupBackupDirectory($backupPath) {
        if (file_exists($backupPath)) {
            $this->deleteDirectory($backupPath);
        }
    }
    
    /**
     * Delete directory recursively
     * @param string $directory
     */
    private function deleteDirectory($directory) {
        if (!file_exists($directory)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($directory);
    }
    
    /**
     * Restore backup
     * @param string $backupPath
     * @return array
     */
    public function restoreBackup($backupPath) {
        if (!file_exists($backupPath)) {
            return [
                'success' => false,
                'message' => 'Backup file tidak ditemukan'
            ];
        }
        
        $startTime = microtime(true);
        
        try {
            // Extract backup
            $extractPath = $this->backupDir . 'temp_restore_' . time();
            $this->extractBackup($backupPath, $extractPath);
            
            // Restore database
            $dbResult = $this->restoreDatabase($extractPath);
            
            // Restore files (optional)
            $filesResult = $this->restoreFiles($extractPath);
            
            // Clean up
            $this->deleteDirectory($extractPath);
            
            return [
                'success' => true,
                'message' => 'Backup berhasil dipulihkan',
                'duration' => round(microtime(true) - $startTime, 2),
                'database' => $dbResult,
                'files' => $filesResult
            ];
            
        } catch (Exception $e) {
            // Clean up on failure
            if (isset($extractPath) && file_exists($extractPath)) {
                $this->deleteDirectory($extractPath);
            }
            
            return [
                'success' => false,
                'message' => 'Restore gagal: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract backup archive
     * @param string $backupPath
     * @param string $extractPath
     */
    private function extractBackup($backupPath, $extractPath) {
        $zip = new ZipArchive();
        if ($zip->open($backupPath) !== true) {
            throw new Exception("Failed to open backup archive");
        }
        
        if (!$zip->extractTo($extractPath)) {
            throw new Exception("Failed to extract backup archive");
        }
        
        $zip->close();
    }
    
    /**
     * Restore database
     * @param string $extractPath
     * @return array
     */
    private function restoreDatabase($extractPath) {
        $dbFile = $extractPath . '/database.sql';
        
        if (!file_exists($dbFile)) {
            throw new Exception("Database backup file not found");
        }
        
        $sql = file_get_contents($dbFile);
        $queries = $this->parseSQLQueries($sql);
        
        $pdo = getDBConnection();
        $executedQueries = 0;
        
        foreach ($queries as $query) {
            if (!empty(trim($query))) {
                $pdo->exec($query);
                $executedQueries++;
            }
        }
        
        return [
            'queries_executed' => $executedQueries,
            'file_size' => filesize($dbFile)
        ];
    }
    
    /**
     * Parse SQL queries
     * @param string $sql
     * @return array
     */
    private function parseSQLQueries($sql) {
        $queries = [];
        $currentQuery = '';
        $inString = false;
        $delimiter = ';';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '--') === 0 || strpos($line, '#') === 0) {
                continue;
            }
            
            // Check for delimiter change
            if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
                if (!empty($currentQuery)) {
                    $queries[] = $currentQuery;
                }
                $delimiter = $matches[1];
                $currentQuery = '';
                continue;
            }
            
            $currentQuery .= $line . "\n";
            
            // Check if query ends with delimiter
            if (substr($line, -strlen($delimiter)) === $delimiter) {
                $currentQuery = substr($currentQuery, 0, -strlen($delimiter));
                $queries[] = $currentQuery;
                $currentQuery = '';
            }
        }
        
        // Add remaining query
        if (!empty($currentQuery)) {
            $queries[] = $currentQuery;
        }
        
        return $queries;
    }
    
    /**
     * Restore files (optional)
     * @param string $extractPath
     * @return array
     */
    private function restoreFiles($extractPath) {
        $filesDir = $extractPath . '/files';
        
        if (!file_exists($filesDir)) {
            return ['message' => 'No files to restore'];
        }
        
        // For safety, we don't automatically restore files
        // This should be done manually by the administrator
        return [
            'message' => 'Files backup found but not automatically restored for safety',
            'files_directory' => $filesDir
        ];
    }
    
    /**
     * List available backups
     * @return array
     */
    public function listBackups() {
        $backups = [];
        $files = glob($this->backupDir . '*.zip');
        
        foreach ($files as $file) {
            $backupInfo = $this->getBackupInfo($file);
            if ($backupInfo) {
                $backups[] = $backupInfo;
            }
        }
        
        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backups;
    }
    
    /**
     * Get backup information
     * @param string $backupPath
     * @return array|null
     */
    private function getBackupInfo($backupPath) {
        if (!file_exists($backupPath)) {
            return null;
        }
        
        $filename = basename($backupPath);
        $size = filesize($backupPath);
        $modified = filemtime($backupPath);
        
        // Try to extract metadata
        $metadata = $this->extractBackupMetadata($backupPath);
        
        return [
            'filename' => $filename,
            'path' => $backupPath,
            'size' => $size,
            'size_formatted' => $this->formatFileSize($size),
            'created_at' => $metadata['created_at'] ?? date('Y-m-d H:i:s', $modified),
            'backup_type' => $metadata['backup_type'] ?? 'unknown',
            'duration' => $metadata['duration'] ?? 0,
            'database_info' => $metadata['database_info'] ?? null
        ];
    }
    
    /**
     * Extract backup metadata
     * @param string $backupPath
     * @return array
     */
    private function extractBackupMetadata($backupPath) {
        $zip = new ZipArchive();
        if ($zip->open($backupPath) !== true) {
            return [];
        }
        
        $metadata = [];
        $metadataContent = $zip->getFromName('metadata.json');
        
        if ($metadataContent) {
            $metadata = json_decode($metadataContent, true) ?: [];
        }
        
        $zip->close();
        
        return $metadata;
    }
    
    /**
     * Clean up old backups
     * @return int Number of backups deleted
     */
    public function cleanupOldBackups() {
        $cutoffTime = time() - (BackupConfig::BACKUP_RETENTION_DAYS * 24 * 60 * 60);
        $deletedCount = 0;
        
        $backups = $this->listBackups();
        
        foreach ($backups as $backup) {
            if (strtotime($backup['created_at']) < $cutoffTime) {
                if (unlink($backup['path'])) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Format file size
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
     * Check if auto backup should run
     * @return bool
     */
    public function shouldRunAutoBackup() {
        if (!BackupConfig::AUTO_BACKUP_ENABLED) {
            return false;
        }
        
        $lastBackupFile = $this->backupDir . 'last_auto_backup.txt';
        
        if (!file_exists($lastBackupFile)) {
            return true;
        }
        
        $lastBackupTime = file_get_contents($lastBackupFile);
        $today = date('Y-m-d');
        
        return $lastBackupTime !== $today;
    }
    
    /**
     * Mark auto backup as completed
     */
    public function markAutoBackupCompleted() {
        $lastBackupFile = $this->backupDir . 'last_auto_backup.txt';
        file_put_contents($lastBackupFile, date('Y-m-d'));
    }
}

/**
 * Convenience functions
 */

/**
 * Create manual backup
 * @return array
 */
function createManualBackup() {
    $backupSystem = new BackupSystem();
    return $backupSystem->createFullBackup('manual');
}

/**
 * Create automatic backup
 * @return array
 */
function createAutoBackup() {
    $backupSystem = new BackupSystem();
    
    if ($backupSystem->shouldRunAutoBackup()) {
        $result = $backupSystem->createFullBackup('auto');
        if ($result['success']) {
            $backupSystem->markAutoBackupCompleted();
        }
        return $result;
    }
    
    return [
        'success' => false,
        'message' => 'Auto backup not needed today'
    ];
}

/**
 * Restore backup
 * @param string $backupPath
 * @return array
 */
function restoreBackup($backupPath) {
    $backupSystem = new BackupSystem();
    return $backupSystem->restoreBackup($backupPath);
}

/**
 * List backups
 * @return array
 */
function listBackups() {
    $backupSystem = new BackupSystem();
    return $backupSystem->listBackups();
}

/**
 * Clean up old backups
 * @return int
 */
function cleanupOldBackups() {
    $backupSystem = new BackupSystem();
    return $backupSystem->cleanupOldBackups();
}

/**
 * Handle backup via web interface
 */
function handleBackupRequest() {
    header('Content-Type: application/json');
    
    // Check if user is logged in
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $result = createManualBackup();
            break;
            
        case 'list':
            $result = ['success' => true, 'backups' => listBackups()];
            break;
            
        case 'restore':
            $backupPath = $_POST['backup_path'] ?? '';
            if (empty($backupPath)) {
                $result = ['success' => false, 'message' => 'Backup path required'];
            } else {
                $result = restoreBackup($backupPath);
            }
            break;
            
        case 'cleanup':
            $deletedCount = cleanupOldBackups();
            $result = [
                'success' => true,
                'message' => "Cleaned up {$deletedCount} old backups"
            ];
            break;
            
        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
    }
    
    echo json_encode($result);
}

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET')) {
    handleBackupRequest();
}
?> 