<?php
$pageTitle = 'Detail Berita';
require_once 'includes/header.php';

// Get berita ID from URL
$berita_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($berita_id <= 0) {
    header('Location: berita.php');
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Get berita detail - explicitly select only needed fields
            $stmt = $pdo->prepare("SELECT id, title, content, excerpt, image, author, status, created_at FROM berita WHERE id = ? AND status = 'published'");
    $stmt->execute([$berita_id]);
    $berita = $stmt->fetch();
    
    if (!$berita) {
        header('Location: berita.php');
        exit();
    }
    
    // Get related berita - explicitly select only needed fields
    $stmt = $pdo->prepare("SELECT id, title, excerpt, created_at FROM berita WHERE status = 'published' AND id != ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$berita_id]);
    $relatedBerita = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Database error in berita-detail.php: " . $e->getMessage());
    header('Location: berita.php');
    exit();
}
?>

<style>
    /* Berita detail specific styles */
    .berita-image {
        width: 100%;
        height: auto;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .excerpt-box {
        border-left: 4px solid #007bff;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .content-text {
        line-height: 1.8;
        font-size: 1.1em;
        color: #333;
    }
    
    .content-text p {
        margin-bottom: 1.2rem;
    }
    
    .share-buttons .btn {
        border-radius: 25px;
        padding: 8px 20px;
        transition: all 0.3s ease;
    }
    
    .share-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .related-item {
        padding: 15px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .related-item:hover {
        background: #f8f9fa;
        transform: translateX(5px);
    }
    
    .related-item a {
        color: #333;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .related-item a:hover {
        color: #007bff;
    }
    
    .shadow-custom {
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: none;
        border-radius: 15px;
    }
    
    @media (max-width: 768px) {
        .berita-image {
            border-radius: 10px;
        }
        
        .content-text {
            font-size: 1em;
            line-height: 1.6;
        }
    }
</style>

<!-- Page Header -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="berita.php" class="text-white">Berita</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Berita</li>
                    </ol>
                </nav>
                <h1 class="section-title text-white mb-3"><?php echo htmlspecialchars($berita['title'] ?? 'Judul Berita'); ?></h1>
                <div class="d-flex align-items-center mt-3">
                    <small class="me-3">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($berita['author'] ?? 'Admin'); ?>
                    </small>
                    <small class="me-3">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo formatDate($berita['created_at'] ?? date('Y-m-d')); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Berita Content -->
<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <article class="berita-content">
                    <?php 
                    // Clean and validate image data
                    $imageFile = trim($berita['image'] ?? '');
                    $imagePath = '';
                    $imageExists = false;
                    
                    if (!empty($imageFile) && is_string($imageFile)) {
                        // Handle both relative and absolute paths
                        if (strpos($imageFile, '/') === 0) {
                            // Absolute path from root (e.g., /alkholis/uploads/berita/filename.jpg)
                            $imagePath = $imageFile;
                            $imageExists = true; // Assume it exists if it's a valid path
                        } else {
                            // Relative path (e.g., filename.jpg)
                            $imagePath = 'uploads/berita/' . $imageFile;
                            $imageExists = file_exists($imagePath) && is_file($imagePath);
                        }
                    }
                    ?>
                    
                    <?php if ($imageExists): ?>
                    <div class="berita-image mb-4">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($berita['title']); ?>" 
                             class="berita-image">
                        <div class="berita-fallback" style="display: none;">
                            <i class="fas fa-newspaper fa-3x text-muted"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                    

                    
                    <div class="berita-text">
                        <?php if (!empty($berita['excerpt']) && trim($berita['excerpt']) !== trim($berita['content'])): ?>
                        <div class="excerpt mb-4">
                            <div class="excerpt-box p-3 bg-light rounded">
                                <h6 class="text-primary mb-2"><i class="fas fa-quote-left me-2"></i>Ringkasan</h6>
                                <p class="section-subtitle mb-0"><?php echo nl2br(htmlspecialchars($berita['excerpt'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="content">
                            <h6 class="text-dark mb-3">Konten Lengkap:</h6>
                            <div class="content-text">
                                <?php echo nl2br(htmlspecialchars($berita['content'] ?? '')); ?>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-5">
                    
                    <!-- Share buttons -->
                    <div class="share-buttons">
                        <h5 class="text-dark mb-3">Bagikan Berita Ini:</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="btn btn-outline-primary">
                                <i class="fab fa-facebook-f me-2"></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($berita['title'] ?? 'Berita'); ?>" 
                               target="_blank" class="btn btn-outline-info">
                                <i class="fab fa-twitter me-2"></i>Twitter
                            </a>
                            <a href="https://wa.me/?text=<?php echo urlencode(($berita['title'] ?? 'Berita') . ' - ' . $_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="btn btn-outline-success">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            
            <div class="col-lg-4">
                <!-- Related Berita -->
                <?php if (!empty($relatedBerita)): ?>
                <div class="card shadow-custom">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">Berita Terkait</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($relatedBerita as $related): ?>
                        <div class="related-item mb-3">
                            <h6 class="text-dark">
                                <a href="berita-detail.php?id=<?php echo (int)($related['id']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($related['title'] ?? 'Judul Berita'); ?>
                                </a>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo formatDate($related['created_at'] ?? date('Y-m-d')); ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Links -->
                <div class="card mt-4 shadow-custom">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">Link Cepat</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="berita.php" class="text-decoration-none text-dark">
                                    <i class="fas fa-newspaper me-2 text-primary"></i>Semua Berita
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="kontak.php" class="text-decoration-none text-dark">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Hubungi Kami
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="index.php" class="text-decoration-none text-dark">
                                    <i class="fas fa-home me-2 text-primary"></i>Beranda
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 