<?php
// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Include error handler and auth helper
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/auth_helper.php';

// Helper functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function getPagination($totalRecords, $perPage, $currentPage) {
    $totalPages = ceil($totalRecords / $perPage);
    $pagination = [];
    
    if ($totalPages > 1) {
        $pagination['current'] = $currentPage;
        $pagination['total'] = $totalPages;
        $pagination['per_page'] = $perPage;
        $pagination['total_records'] = $totalRecords;
        
        // Previous page
        if ($currentPage > 1) {
            $pagination['prev'] = $currentPage - 1;
        }
        
        // Next page
        if ($currentPage < $totalPages) {
            $pagination['next'] = $currentPage + 1;
        }
        
        // Page numbers
        $pagination['pages'] = [];
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $pagination['pages'][] = $i;
        }
    }
    
    return $pagination;
}

function formatDate($date) {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    if ($timestamp === false) return $date;
    
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } else {
        return date('d M Y', $timestamp);
    }
}

function truncateText($text, $length = 100) {
    if (empty($text)) return '';
    
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $truncated = substr($text, 0, $length);
    $lastSpace = strrpos($truncated, ' ');
    
    if ($lastSpace !== false) {
        $truncated = substr($truncated, 0, $lastSpace);
    }
    
    return $truncated . '...';
}

function getSetting($key, $default = '') {
    // Default settings for contact information
    $settings = [
        'contact_address' => 'Jl. Pendidikan No. 123, Jakarta Selatan',
        'contact_phone' => '+62-21-1234567',
        'contact_email' => 'info@alkholis.sch.id',
        'school_name' => 'SMA Al-Kholis',
        'school_description' => 'Sekolah Islam Terpadu dengan Akreditasi A'
    ];
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e40af">
    <meta name="msapplication-TileColor" content="#1e40af">
    
    <!-- Prevent browser caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>SMA Al-Kholis</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/sma.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/sma.png">
    <link rel="shortcut icon" href="assets/images/sma.png">
    <link rel="apple-touch-icon" href="assets/images/sma.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome - Multiple CDN sources for better compatibility -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v6.4.0/css/all.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/global-improvements.css" rel="stylesheet">
    <link href="assets/css/navbar-complete.css" rel="stylesheet">
    <link href="assets/css/image-optimization.css" rel="stylesheet">
    
    <!-- Inline CSS to override cache issues -->
    <style>
        /* Navbar styling moved to global-improvements.css for better organization */
        
        /* Ensure all text is readable */
        h1, h2, h3, h4, h5, h6 {
            color: #0f172a !important;
            font-weight: 700 !important;
        }
        
        p {
            color: #475569 !important;
            font-weight: 400 !important;
        }
        
        .text-dark {
            color: #0f172a !important;
            font-weight: 600 !important;
        }
        
        .text-muted {
            color: #475569 !important;
            font-weight: 500 !important;
        }
        
        /* Remove all blur effects from photos and images */
        img, .hero-slide, .hero-section::before, .hero-slide::after {
            filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            -webkit-filter: none !important;
        }
        
        /* Ensure photos are displayed clearly without blue overlay */
        .hero-slide::after {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.1), rgba(30, 58, 138, 0.2)) !important;
        }
        
        .hero-section::before {
            opacity: 0.05 !important;
        }
        
        /* Remove any remaining backdrop filters */
        .info-badge, .btn-light {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        
        /* Enhanced 3D Text Effects for Hero Section */
        .hero-title {
            text-shadow: 
                0 1px 0 #ccc,
                0 2px 0 #c9c9c9,
                0 3px 0 #bbb,
                0 4px 0 #b9b9b9,
                0 5px 0 #aaa,
                0 6px 1px rgba(0,0,0,.1),
                0 0 5px rgba(0,0,0,.1),
                0 1px 3px rgba(0,0,0,.3),
                0 3px 5px rgba(0,0,0,.2),
                0 5px 10px rgba(0,0,0,.25),
                0 10px 10px rgba(0,0,0,.2),
                0 20px 20px rgba(0,0,0,.15) !important;
            transition: text-shadow 0.3s ease !important;
            color: #ffffff !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
        }
        
        .hero-title:hover {
            text-shadow: 
                0 1px 0 #ddd,
                0 2px 0 #d9d9d9,
                0 3px 0 #ccc,
                0 4px 0 #c9c9c9,
                0 5px 0 #bbb,
                0 6px 1px rgba(0,0,0,.1),
                0 0 5px rgba(0,0,0,.1),
                0 1px 3px rgba(0,0,0,.3),
                0 3px 5px rgba(0,0,0,.2),
                0 5px 10px rgba(0,0,0,.25),
                0 10px 10px rgba(0,0,0,.2),
                0 20px 20px rgba(0,0,0,.15),
                0 0 20px rgba(255,255,255,0.1) !important;
        }
        
        .hero-subtitle {
            text-shadow: 
                0 1px 0 #ccc,
                0 2px 0 #c9c9c9,
                0 3px 0 #bbb,
                0 4px 0 #b9b9b9,
                0 5px 0 #aaa,
                0 6px 1px rgba(0,0,0,.1),
                0 0 5px rgba(0,0,0,.1),
                0 1px 3px rgba(0,0,0,.3),
                0 3px 5px rgba(0,0,0,.2) !important;
            transition: color 0.3s ease !important;
            font-weight: 600 !important;
            letter-spacing: 1px !important;
            color: rgba(255, 255, 255, 0.95) !important;
        }
        
        .hero-subtitle:hover {
            color: #ffffff !important;
        }
        
        /* Image loading fixes */
        img {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
            object-fit: cover !important;
        }
        
        /* Fix broken images */
        img[src*="uploads/"] {
            min-height: 200px !important;
            background: #f8fafc !important;
            border: 2px dashed #e2e8f0 !important;
        }
        
        /* Card image fixes */
        .card-img-top {
            background: #f8fafc !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        
        /* Default image fallbacks */
        .img-fallback {
            width: 100% !important;
            height: 200px !important;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
            border: 2px dashed #e2e8f0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
        }
        
        .fallback-content {
            text-align: center !important;
            color: #475569 !important;
        }
        
        .fallback-icon {
            font-size: 3rem !important;
            margin-bottom: 0.5rem !important;
            opacity: 0.7 !important;
        }
        
        .fallback-text {
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.25rem !important;
            color: #0f172a !important;
        }
        
        .fallback-subtitle {
            font-size: 0.875rem !important;
            color: #64748b !important;
            font-style: italic !important;
        }
        
        /* Loading states */
        .img-loading {
            background: linear-gradient(90deg, #f8fafc 25%, #e2e8f0 50%, #f8fafc 75%) !important;
            background-size: 200% 100% !important;
            animation: loading 1.5s infinite !important;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Full Background Image Styles */
        .full-background,
        [style*="background-image"] {
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            min-height: 100vh !important;
            width: 100% !important;
        }
        
        /* Specific background image styles */
        [style*="assets/images/oke.jpg"] {
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            min-height: 100vh !important;
            width: 100% !important;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .full-background,
            [style*="background-image"] {
                background-attachment: scroll !important;
            }
        }
    </style>
    
    <!-- Image Optimization JavaScript -->
    <script src="assets/js/image-optimization.js" defer></script>
    <!-- Navbar Fixes JavaScript -->
    <script src="assets/js/navbar-fixes.js" defer></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/sma.png" alt="SMA Al-Kholis" class="navbar-logo">
                SMA Al-Kholis
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="informasiDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-info-circle me-1"></i>Informasi
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="informasiDropdown">
                            <li><a class="dropdown-item" href="berita.php"><i class="fas fa-newspaper me-2"></i>Berita</a></li>
                            <li><a class="dropdown-item" href="pengumuman.php"><i class="fas fa-bullhorn me-2"></i>Pengumuman</a></li>
                            <li><a class="dropdown-item" href="profil-sekolah.php"><i class="fas fa-school me-2"></i>Profil Sekolah</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'jadwal-pelajaran.php' ? 'active' : ''; ?>" href="jadwal-pelajaran.php">
                            <i class="fas fa-calendar-alt me-1"></i>Jadwal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ekstrakurikuler.php' ? 'active' : ''; ?>" href="ekstrakurikuler.php">
                            <i class="fas fa-users me-1"></i>Ekstrakurikuler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'prestasi.php' ? 'active' : ''; ?>" href="prestasi.php">
                            <i class="fas fa-trophy me-1"></i>Prestasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'karya-siswa.php' ? 'active' : ''; ?>" href="karya-siswa.php">
                            <i class="fas fa-palette me-1"></i>Karya Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'guru.php' ? 'active' : ''; ?>" href="guru.php">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Guru
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'siswa.php' ? 'active' : ''; ?>" href="siswa.php">
                            <i class="fas fa-user-graduate me-1"></i>Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'galeri.php' ? 'active' : ''; ?>" href="galeri.php">
                            <i class="fas fa-images me-1"></i>Galeri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kontak.php' ? 'active' : ''; ?>" href="kontak.php">
                            <i class="fas fa-envelope me-1"></i>Kontak
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main style="margin-top: 76px;">
        <!-- Flash Messages -->
        <?php displayFlashMessage(); ?> 