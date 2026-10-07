<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$pageTitle = 'Kelola Galeri';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SMA Al-Kholis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
    <style>
        .galeri-item {
            transition: transform 0.2s;
        }
        .galeri-item:hover {
            transform: translateY(-2px);
        }
        .galeri-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        .image-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        .image-upload-container:hover {
            border-color: #1e3a8a;
            background: #e3f2fd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .image-upload-container.dragover {
            border-color: #1e3a8a;
            background: #e3f2fd;
            transform: scale(1.02);
        }
        .image-upload-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(30, 58, 138, 0.05);
            border-radius: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .image-upload-container:hover::before {
            opacity: 1;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
        }
        .upload-progress {
            margin-top: 10px;
        }
        .upload-status {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        .remove-image-btn {
            margin-top: 10px;
        }
        
        /* Improved spacing for galeri page */
        .page-header-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .search-filter-section {
            background: white;
            padding: 18px 22px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .galeri-grid-section {
            margin-top: 20px;
        }
        
        .galeri-card {
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .galeri-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        
        .galeri-card .card-body {
            padding: 15px 18px;
        }
        
        .galeri-card .card-footer {
            padding: 12px 18px;
            background: transparent;
            border-top: 1px solid #f1f3f4;
        }
        
        .btn-group .btn {
            border-radius: 6px;
            margin: 0 1px;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }
        
        /* Reduce spacing between galeri items */
        .galeri-item {
            margin-bottom: 15px !important;
        }
        
        /* Reduce spacing in search section */
        .search-filter-section .row > div {
            margin-bottom: 10px;
        }
        
        /* Reduce spacing in modal */
        .modal-body .mb-3 {
            margin-bottom: 15px !important;
        }
    </style>
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
                        <h2 class="mb-1 text-break">Kelola Galeri</h2>
                        <p class="text-muted mb-0 text-break">Tambah, edit, dan kelola galeri foto sekolah</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="addGaleri()">
                            <i class="fas fa-plus me-2"></i>Tambah Galeri
                        </button>
                    </div>
                </div>
                
                <!-- Search and Filter Section -->
                <div class="search-filter-section mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari galeri...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                <option value="kegiatan">Kegiatan</option>
                                <option value="acara">Acara</option>
                                <option value="prestasi">Prestasi</option>
                                <option value="fasilitas">Fasilitas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Galeri Grid -->
                <div class="galeri-grid-section">
                    <div class="row" id="galeriContainer">
                        <!-- Galeri items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Galeri Modal -->
    <div class="modal fade" id="galeriModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Galeri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="galeriForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Galeri *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Kategori</label>
                                            <select class="form-select" id="category" name="category">
                                                <option value="kegiatan">Kegiatan</option>
                                                <option value="acara">Acara</option>
                                                <option value="prestasi">Prestasi</option>
                                                <option value="fasilitas">Fasilitas</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active">Aktif</option>
                                                <option value="inactive">Tidak Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Upload Gambar *</label>
                                    <div class="image-upload-container" id="imageUploadContainer">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <p class="mb-2"><strong>Klik di sini untuk pilih gambar</strong></p>
                                        <p class="mb-2">atau drag & drop gambar di sini</p>
                                        <p class="text-muted small">Format: JPG/PNG (Maksimal 5MB)</p>
                                        <input type="file" id="imageInput" accept="image/jpeg,image/jpg,image/png" style="display: none;">
                                    </div>
                                    <img id="imagePreview" class="image-preview" alt="Preview">
                                    <div class="upload-progress" id="uploadProgress" style="display: none;">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div class="upload-status" id="uploadStatus" style="display: none;"></div>
                                    <button type="button" class="btn btn-danger btn-sm remove-image-btn" id="removeImageBtn" style="display: none;">
                                        <i class="fas fa-trash me-1"></i>Hapus Gambar
                                    </button>
                                    <input type="hidden" id="imageUrl" name="image">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Galeri JavaScript Complete -->
    <script src="js/galeri_complete.js"></script>
</body>
</html> 