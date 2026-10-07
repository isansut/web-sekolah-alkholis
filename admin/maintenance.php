<?php
/**
 * MAINTENANCE PAGE FOR ADMIN PANEL
 * Handles system maintenance, monitoring, and diagnostics
 */

// Include necessary files
require_once '../includes/auth_helper.php';
require_once '../includes/error_handler.php';
require_once '../backup_system.php';

// Require admin authentication
requireAdminAuth();

$pageTitle = 'Maintenance & Monitoring';
$currentAdmin = getCurrentAdmin();

// Handle maintenance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            $result = createManualBackup();
            setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            break;
            
        case 'cleanup_backups':
            $deletedCount = cleanupOldBackups();
            setFlashMessage('success', "Berhasil menghapus {$deletedCount} backup lama");
            break;
            
        case 'cleanup_logs':
            cleanupErrorLogs();
            setFlashMessage('success', 'Log error berhasil dibersihkan');
            break;
            
        case 'optimize_database':
            try {
                $pdo = getDBConnection();
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                $optimizedCount = 0;
                
                foreach ($tables as $table) {
                    $pdo->exec("OPTIMIZE TABLE `{$table}`");
                    $optimizedCount++;
                }
                
                setFlashMessage('success', "Berhasil mengoptimalkan {$optimizedCount} tabel");
            } catch (Exception $e) {
                setFlashMessage('error', 'Gagal mengoptimalkan database: ' . $e->getMessage());
            }
            break;
            
        case 'clear_cache':
            // Clear any cached files
            $cacheDirs = ['../temp/', '../cache/'];
            $clearedCount = 0;
            
            foreach ($cacheDirs as $cacheDir) {
                if (file_exists($cacheDir)) {
                    $files = glob($cacheDir . '*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                            $clearedCount++;
                        }
                    }
                }
            }
            
            setFlashMessage('success', "Berhasil membersihkan {$clearedCount} file cache");
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: maintenance.php');
    exit;
}

// Get system information
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database_info' => null,
    'disk_usage' => null,
    'memory_usage' => null,
    'error_stats' => null,
    'backup_stats' => null
];

// Get database information
try {
    $pdo = getDBConnection();
    $systemInfo['database_info'] = [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'tables' => $pdo->query("SHOW TABLES")->rowCount(),
        'size' => 0
    ];
    
    // Calculate database size
    $result = $pdo->query("SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    $row = $result->fetch();
    $systemInfo['database_info']['size'] = $row['size'] ?? 0;
    
} catch (Exception $e) {
    $systemInfo['database_info'] = ['error' => $e->getMessage()];
}

// Get disk usage
$diskTotal = disk_total_space(__DIR__);
$diskFree = disk_free_space(__DIR__);
$diskUsed = $diskTotal - $diskFree;

$systemInfo['disk_usage'] = [
    'total' => $diskTotal,
    'used' => $diskUsed,
    'free' => $diskFree,
    'percentage' => round(($diskUsed / $diskTotal) * 100, 2)
];

// Get memory usage
$memoryLimit = ini_get('memory_limit');
$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);

$systemInfo['memory_usage'] = [
    'limit' => $memoryLimit,
    'current' => $memoryUsage,
    'peak' => $memoryPeak
];

// Get error statistics
$systemInfo['error_stats'] = getErrorStatistics(7);

// Get backup statistics
try {
    $backups = listBackups();
    $totalBackupSize = 0;
    foreach ($backups as $backup) {
        $totalBackupSize += $backup['size'];
    }
    
    $systemInfo['backup_stats'] = [
        'total_backups' => count($backups),
        'total_size' => $totalBackupSize,
        'latest_backup' => count($backups) > 0 ? $backups[0]['created_at'] : null
    ];
} catch (Exception $e) {
    $systemInfo['backup_stats'] = ['error' => $e->getMessage()];
}

// Format file size helper
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .system-card {
            transition: transform 0.2s;
        }
        .system-card:hover {
            transform: translateY(-2px);
        }
        .progress {
            height: 8px;
        }
        .metric-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="berita.php">
                                <i class="fas fa-newspaper me-2"></i>Berita
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="guru.php">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Guru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="karya_siswa.php">
                                <i class="fas fa-palette me-2"></i>Karya Siswa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="galeri.php">
                                <i class="fas fa-images me-2"></i>Galeri
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="pengumuman.php">
                                <i class="fas fa-bullhorn me-2"></i>Pengumuman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="jadwal.php">
                                <i class="fas fa-calendar-alt me-2"></i>Jadwal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="pesan.php">
                                <i class="fas fa-envelope me-2"></i>Pesan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="maintenance.php">
                                <i class="fas fa-tools me-2"></i>Maintenance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Pengaturan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tools me-2"></i>Maintenance & Monitoring
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php displayFlashMessage(); ?>

                <!-- System Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4><i class="fas fa-server me-2"></i>System Overview</h4>
                    </div>
                </div>

                <div class="row mb-4">
                    <!-- PHP Version -->
                    <div class="col-md-3 mb-3">
                        <div class="card system-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-code fa-2x text-primary mb-2"></i>
                                <div class="metric-value"><?php echo $systemInfo['php_version']; ?></div>
                                <div class="metric-label">PHP Version</div>
                            </div>
                        </div>
                    </div>

                    <!-- Server Software -->
                    <div class="col-md-3 mb-3">
                        <div class="card system-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-server fa-2x text-success mb-2"></i>
                                <div class="metric-value"><?php echo substr($systemInfo['server_software'], 0, 20) . '...'; ?></div>
                                <div class="metric-label">Server Software</div>
                            </div>
                        </div>
                    </div>

                    <!-- Database Tables -->
                    <div class="col-md-3 mb-3">
                        <div class="card system-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x text-info mb-2"></i>
                                <div class="metric-value">
                                    <?php echo isset($systemInfo['database_info']['tables']) ? $systemInfo['database_info']['tables'] : 'N/A'; ?>
                                </div>
                                <div class="metric-label">Database Tables</div>
                            </div>
                        </div>
                    </div>

                    <!-- Memory Usage -->
                    <div class="col-md-3 mb-3">
                        <div class="card system-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-memory fa-2x text-warning mb-2"></i>
                                <div class="metric-value"><?php echo formatFileSize($systemInfo['memory_usage']['current']); ?></div>
                                <div class="metric-label">Memory Usage</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disk Usage -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-hdd me-2"></i>Disk Usage</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="progress mb-2">
                                            <div class="progress-bar <?php echo $systemInfo['disk_usage']['percentage'] > 80 ? 'bg-danger' : ($systemInfo['disk_usage']['percentage'] > 60 ? 'bg-warning' : 'bg-success'); ?>" 
                                                 style="width: <?php echo $systemInfo['disk_usage']['percentage']; ?>%"></div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo $systemInfo['disk_usage']['percentage']; ?>% used 
                                            (<?php echo formatFileSize($systemInfo['disk_usage']['used']); ?> of <?php echo formatFileSize($systemInfo['disk_usage']['total']); ?>)
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="text-success">
                                            <strong><?php echo formatFileSize($systemInfo['disk_usage']['free']); ?></strong><br>
                                            <small>Available</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Statistics -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Error Statistics (Last 7 Days)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($systemInfo['error_stats']['total_errors'])): ?>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="metric-value text-danger"><?php echo $systemInfo['error_stats']['total_errors']; ?></div>
                                            <div class="metric-label">Total Errors</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="metric-value text-warning"><?php echo count($systemInfo['error_stats']['error_types']); ?></div>
                                            <div class="metric-label">Error Types</div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No error data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-archive me-2"></i>Backup Statistics</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($systemInfo['backup_stats']['total_backups'])): ?>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="metric-value text-info"><?php echo $systemInfo['backup_stats']['total_backups']; ?></div>
                                            <div class="metric-label">Total Backups</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="metric-value text-success"><?php echo formatFileSize($systemInfo['backup_stats']['total_size']); ?></div>
                                            <div class="metric-label">Total Size</div>
                                        </div>
                                    </div>
                                    <?php if ($systemInfo['backup_stats']['latest_backup']): ?>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">
                                                Latest: <?php echo date('d M Y H:i', strtotime($systemInfo['backup_stats']['latest_backup'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">No backup data available</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4><i class="fas fa-wrench me-2"></i>Maintenance Actions</h4>
                    </div>
                </div>

                <div class="row">
                    <!-- Backup Management -->
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-archive me-2"></i>Backup Management</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="mb-2">
                                    <input type="hidden" name="action" value="create_backup">
                                    <button type="submit" class="btn btn-primary btn-sm w-100 mb-2">
                                        <i class="fas fa-plus me-1"></i>Create Manual Backup
                                    </button>
                                </form>
                                
                                <form method="POST" class="mb-2">
                                    <input type="hidden" name="action" value="cleanup_backups">
                                    <button type="submit" class="btn btn-warning btn-sm w-100" 
                                            onclick="return confirm('Are you sure you want to delete old backups?')">
                                        <i class="fas fa-trash me-1"></i>Cleanup Old Backups
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- System Maintenance -->
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>System Maintenance</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="mb-2">
                                    <input type="hidden" name="action" value="optimize_database">
                                    <button type="submit" class="btn btn-info btn-sm w-100 mb-2">
                                        <i class="fas fa-database me-1"></i>Optimize Database
                                    </button>
                                </form>
                                
                                <form method="POST" class="mb-2">
                                    <input type="hidden" name="action" value="clear_cache">
                                    <button type="submit" class="btn btn-secondary btn-sm w-100 mb-2">
                                        <i class="fas fa-broom me-1"></i>Clear Cache
                                    </button>
                                </form>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="cleanup_logs">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-file-alt me-1"></i>Cleanup Error Logs
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <a href="api/backup.php?action=list" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                                            <i class="fas fa-list me-1"></i>View Backups
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="api/backup.php?action=status" class="btn btn-outline-info btn-sm w-100" target="_blank">
                                            <i class="fas fa-chart-bar me-1"></i>System Status
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="../logs/" class="btn btn-outline-warning btn-sm w-100" target="_blank">
                                            <i class="fas fa-file-alt me-1"></i>View Logs
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <a href="settings.php" class="btn btn-outline-success btn-sm w-100">
                                            <i class="fas fa-cog me-1"></i>System Settings
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh system stats every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html> 