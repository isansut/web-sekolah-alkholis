<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kontak - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="includes/admin-styles.css" rel="stylesheet">
    <style>
        .setting-group {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .setting-group h5 {
            color: #495057;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #ced4da;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .btn-save {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 6px;
            padding: 10px 25px;
            font-weight: 600;
        }
        
        .btn-save:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
        
        .preview-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .social-links .form-group {
            margin-bottom: 15px;
        }
        
        .map-preview {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h2 class="text-break"><i class="fas fa-address-book me-2"></i>Kelola Kontak</h2>
                            <p class="text-muted">Kelola informasi kontak dan halaman kontak website</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-success" onclick="saveAllSettings()">
                                <i class="fas fa-save me-2"></i>Simpan Semua
                            </button>
                            <button class="btn btn-info" onclick="previewContact()">
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                            <button class="btn btn-secondary" onclick="resetToDefault()">
                                <i class="fas fa-undo me-2"></i>Reset Default
                            </button>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertContainer"></div>

                    <!-- Contact Settings Form -->
                    <form id="contactForm">
                        <!-- Basic Information -->
                        <div class="setting-group">
                            <h5><i class="fas fa-info-circle me-2"></i>Informasi Dasar</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label">Nama Sekolah</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="website" name="website">
                                </div>
                                <div class="col-12">
                                    <label for="about_text" class="form-label">Tentang Sekolah</label>
                                    <textarea class="form-control" id="about_text" name="about_text" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="setting-group">
                            <h5><i class="fas fa-phone me-2"></i>Informasi Kontak</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="address" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="office_hours" class="form-label">Jam Operasional</label>
                                    <input type="text" class="form-control" id="office_hours" name="office_hours">
                                </div>
                                <div class="col-md-4">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-4">
                                    <label for="whatsapp" class="form-label">WhatsApp</label>
                                    <input type="tel" class="form-control" id="whatsapp" name="whatsapp">
                                </div>
                                <div class="col-md-4">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                        </div>

                        <!-- Vision & Mission -->
                        <div class="setting-group">
                            <h5><i class="fas fa-bullseye me-2"></i>Visi & Misi</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="vision" class="form-label">Visi</label>
                                    <textarea class="form-control" id="vision" name="vision" rows="3"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="mission" class="form-label">Misi</label>
                                    <textarea class="form-control" id="mission" name="mission" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="setting-group">
                            <h5><i class="fas fa-share-alt me-2"></i>Media Sosial</h5>
                            <div class="row g-3 social-links">
                                <div class="col-md-6">
                                    <label for="facebook" class="form-label">Facebook</label>
                                    <input type="url" class="form-control" id="facebook" name="facebook" placeholder="https://facebook.com/...">
                                </div>
                                <div class="col-md-6">
                                    <label for="instagram" class="form-label">Instagram</label>
                                    <input type="url" class="form-control" id="instagram" name="instagram" placeholder="https://instagram.com/...">
                                </div>
                                <div class="col-md-6">
                                    <label for="youtube" class="form-label">YouTube</label>
                                    <input type="url" class="form-control" id="youtube" name="youtube" placeholder="https://youtube.com/...">
                                </div>
                                <div class="col-md-6">
                                    <label for="twitter" class="form-label">Twitter</label>
                                    <input type="url" class="form-control" id="twitter" name="twitter" placeholder="https://twitter.com/...">
                                </div>
                            </div>
                        </div>

                        <!-- Map & Location -->
                        <div class="setting-group">
                            <h5><i class="fas fa-map-marker-alt me-2"></i>Lokasi & Peta</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" placeholder="-6.1750">
                                    <small class="text-muted">Koordinat latitude lokasi sekolah</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" placeholder="106.8222">
                                    <small class="text-muted">Koordinat longitude lokasi sekolah</small>
                                </div>
                                <div class="col-12">
                                    <label for="map_embed" class="form-label">Embed Google Maps</label>
                                    <textarea class="form-control" id="map_embed" name="map_embed" rows="4" placeholder="<iframe src='...'></iframe>"></textarea>
                                    <small class="text-muted">Paste kode embed dari Google Maps atau gunakan koordinat di atas</small>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-outline-primary" onclick="generateMapEmbed()">
                                        <i class="fas fa-map me-2"></i>Generate Map dari Koordinat
                                    </button>
                                    <small class="text-muted ms-2">Klik untuk membuat embed map otomatis dari koordinat</small>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-save btn-lg">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" style="--bs-modal-width: 90%;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Halaman Kontak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="../kontak.php" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-2"></i>Buka di Tab Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/kontak.js"></script>
</body>
</html> 