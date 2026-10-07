<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Dashboard Admin';

// Get dashboard statistics
try {
    $pdo = getDBConnection();
    
    // Test database connection
    $pdo->query("SELECT 1");
    
    // Get total counts
    $stats = [];
    
    // Total Berita
    $stmt = $pdo->query("SELECT COUNT(*) FROM berita WHERE status = 'published'");
    $stats['berita'] = $stmt->fetchColumn();
    
    // Total Guru
    $stmt = $pdo->query("SELECT COUNT(*) FROM guru WHERE status = 'active'");
    $stats['guru'] = $stmt->fetchColumn();
    
    // Total Siswa
    $stmt = $pdo->query("SELECT COUNT(*) FROM siswa WHERE status = 'active'");
    $stats['siswa'] = $stmt->fetchColumn();
    
    // Total Pesan Baru
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $stats['pesan'] = $stmt->fetchColumn();
    
    // Total Prestasi
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestasi WHERE status = 'active'");
    $stats['prestasi'] = $stmt->fetchColumn();
    
    // Total Karya Siswa
    $stmt = $pdo->query("SELECT COUNT(*) FROM karya_siswa WHERE status = 'approved'");
    $stats['karya'] = $stmt->fetchColumn();
    
    // Total Galeri
    $stmt = $pdo->query("SELECT COUNT(*) FROM galeri WHERE status = 'active'");
    $stats['galeri'] = $stmt->fetchColumn();
    
    // Get recent activities
    $recentActivities = [];
    
    // Recent berita
    $stmt = $pdo->prepare("SELECT 'berita' as type, title as name, created_at, 'Berita baru dipublikasikan' as description FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recentActivities = array_merge($recentActivities, $stmt->fetchAll());
    
    // Recent guru
    $stmt = $pdo->prepare("SELECT 'guru' as type, nama as name, created_at, 'Guru baru ditambahkan' as description FROM guru WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $recentActivities = array_merge($recentActivities, $stmt->fetchAll());
    
    // Recent siswa
    $stmt = $pdo->prepare("SELECT 'siswa' as type, nama as name, created_at, 'Siswa baru ditambahkan' as description FROM siswa WHERE status = 'active' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $recentActivities = array_merge($recentActivities, $stmt->fetchAll());
    
    // Sort activities by date
    usort($recentActivities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Take only top 8 activities
    $recentActivities = array_slice($recentActivities, 0, 8);
    
    // Debug information (remove in production)
    if (isset($_GET['debug'])) {
        error_log("Dashboard Stats: " . json_encode($stats));
        error_log("Dashboard Activities: " . count($recentActivities));
    }
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = [
        'berita' => 0,
        'guru' => 0,
        'siswa' => 0,
        'pesan' => 0,
        'prestasi' => 0,
        'karya' => 0,
        'galeri' => 0
    ];
    $recentActivities = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SMA Al-Kholis</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <h2 class="mb-1 text-break">Dashboard Admin</h2>
                        <p class="text-muted mb-0 text-break">Selamat datang di panel admin SMA Al-Kholis</p>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-primary-gradient me-3">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Berita</h6>
                                        <h4 class="mb-0"><?php echo $stats['berita']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-success-gradient me-3">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Guru</h6>
                                        <h4 class="mb-0"><?php echo $stats['guru']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-warning-gradient me-3">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Siswa</h6>
                                        <h4 class="mb-0"><?php echo $stats['siswa']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-danger-gradient me-3">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Pesan Baru</h6>
                                        <h4 class="mb-0"><?php echo $stats['pesan']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Stats Row -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-info-gradient me-3">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Prestasi</h6>
                                        <h4 class="mb-0"><?php echo $stats['prestasi']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-secondary-gradient me-3">
                                        <i class="fas fa-palette"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Karya</h6>
                                        <h4 class="mb-0"><?php echo $stats['karya']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-dark-gradient me-3">
                                        <i class="fas fa-images"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Galeri</h6>
                                        <h4 class="mb-0"><?php echo $stats['galeri']; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 col-sm-12">
                        <div class="card card-stats">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon bg-light-gradient me-3">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-1">Total Data</h6>
                                        <h4 class="mb-0"><?php echo array_sum($stats); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3 col-sm-6">
                                        <a href="berita.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                            <i class="fas fa-newspaper fa-2x mb-2"></i>
                                            <span>Kelola Berita</span>
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <a href="guru.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                            <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                                            <span>Kelola Guru</span>
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <a href="siswa.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                            <i class="fas fa-user-graduate fa-2x mb-2"></i>
                                            <span>Kelola Siswa</span>
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <a href="kontak.php" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                            <i class="fas fa-envelope fa-2x mb-2"></i>
                                            <span>Pesan Baru</span>
                                            <?php if ($stats['pesan'] > 0): ?>
                                                <span class="badge bg-danger ms-1"><?php echo $stats['pesan']; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="row g-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recentActivities)): ?>
                                    <div class="activity-list">
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <div class="activity-item d-flex align-items-start mb-3 p-3 border rounded">
                                                <div class="activity-icon me-3">
                                                    <?php
                                                    $icon = '';
                                                    $color = '';
                                                    switch ($activity['type']) {
                                                        case 'berita':
                                                            $icon = 'fas fa-newspaper';
                                                            $color = 'text-primary';
                                                            break;
                                                        case 'guru':
                                                            $icon = 'fas fa-chalkboard-teacher';
                                                            $color = 'text-success';
                                                            break;
                                                        case 'siswa':
                                                            $icon = 'fas fa-user-graduate';
                                                            $color = 'text-warning';
                                                            break;
                                                        default:
                                                            $icon = 'fas fa-info-circle';
                                                            $color = 'text-info';
                                                    }
                                                    ?>
                                                    <i class="<?php echo $icon . ' ' . $color; ?> fa-lg"></i>
                                                </div>
                                                <div class="activity-content flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['name']); ?></h6>
                                                    <p class="text-muted mb-1 small"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php 
                                                        $date = new DateTime($activity['created_at']);
                                                        echo $date->format('d M Y H:i');
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Belum ada aktivitas terbaru</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistik</h5>
                            </div>
                            <div class="card-body">
                                <div class="stats-summary">
                                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Berita</span>
                                        <span class="badge bg-primary"><?php echo $stats['berita']; ?></span>
                                    </div>
                                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Guru</span>
                                        <span class="badge bg-success"><?php echo $stats['guru']; ?></span>
                                    </div>
                                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Siswa</span>
                                        <span class="badge bg-warning"><?php echo $stats['siswa']; ?></span>
                                    </div>
                                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Prestasi</span>
                                        <span class="badge bg-info"><?php echo $stats['prestasi']; ?></span>
                                    </div>
                                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Karya</span>
                                        <span class="badge bg-secondary"><?php echo $stats['karya']; ?></span>
                                    </div>
                                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">Galeri</span>
                                        <span class="badge bg-dark"><?php echo $stats['galeri']; ?></span>
                                    </div>
                                    <hr>
                                    <div class="stat-item d-flex justify-content-between align-items-center">
                                        <strong>Total</strong>
                                        <strong class="text-primary"><?php echo array_sum($stats); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        /* Dashboard specific styles */
        .card-stats {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5em;
        }
        
        .bg-primary-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-success-gradient { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-warning-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-danger-gradient { background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); }
        .bg-info-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .bg-secondary-gradient { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .bg-dark-gradient { background: linear-gradient(135deg, #434343 0%, #000000 100%); }
        .bg-light-gradient { background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); }
        
        .activity-item {
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .activity-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .quick-actions .btn {
            transition: all 0.3s ease;
            border-radius: 15px;
        }
        
        .quick-actions .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .stats-summary .stat-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stats-summary .stat-item:last-child {
            border-bottom: none;
        }
        
        .main-content {
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .stats-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2em;
            }
        }
    </style>
</body>
</html> 