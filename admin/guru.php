<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Kelola Guru';
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
    
    <style>
        .guru-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .guru-card:hover {
            transform: translateY(-5px);
        }
        
        .guru-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1e3a8a;
        }
        
        .btn-action {
            margin: 0 2px;
        }

        /* Image Upload Styles */
        .image-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .image-upload-container:hover {
            border-color: #1e3a8a;
            background: #e3f2fd;
        }

        .image-upload-container.dragover {
            border-color: #1e3a8a;
            background: #e3f2fd;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
        }

        .upload-progress {
            display: none;
            margin-top: 10px;
        }

        .upload-status {
            margin-top: 10px;
            padding: 8px;
            border-radius: 5px;
            display: none;
        }

        .upload-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .upload-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .remove-image-btn {
            display: none;
            margin-top: 10px;
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
                        <h2 class="text-break"><i class="fas fa-chalkboard-teacher me-2"></i>Kelola Guru</h2>
                    </div>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGuruModal">
                            <i class="fas fa-plus me-2"></i>Tambah Guru
                        </button>
                    </div>
                </div>

                <!-- Guru Cards Container -->
                <div id="guruContainer" class="row g-3">
                    <!-- Guru cards will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Guru Modal -->
    <div class="modal fade" id="addGuruModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Guru Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="guruForm">
                    <div class="modal-body">
                        <!-- Image Upload Section -->
                        <div class="mb-4">
                            <label class="form-label">Foto Guru</label>
                            <div id="imageUploadContainer">
                                <input type="file" class="form-control" id="imageFile" accept="image/*">
                                <small class="text-muted">Upload foto guru (JPG, PNG, max 5MB)</small>
                            </div>
                            <div id="imagePreviewContainer" style="display: none;">
                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail mt-2" style="max-width: 200px;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImageBtn" onclick="removeImage()">Hapus Foto</button>
                            </div>
                            <div id="uploadProgress" class="upload-progress">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <div id="uploadStatus" class="upload-status"></div>
                            <input type="hidden" id="photo" name="photo">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" class="form-control" name="nip" placeholder="Nomor Induk Pegawai">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" name="jabatan" value="Guru">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mata Pelajaran</label>
                                    <select class="form-select" name="mata_pelajaran" required>
                                        <option value="">Pilih Mata Pelajaran</option>
                                        <option value="Matematika">Matematika</option>
                                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                                        <option value="Fisika">Fisika</option>
                                        <option value="Kimia">Kimia</option>
                                        <option value="Biologi">Biologi</option>
                                        <option value="Sejarah">Sejarah</option>
                                        <option value="Geografi">Geografi</option>
                                        <option value="Ekonomi">Ekonomi</option>
                                        <option value="Sosiologi">Sosiologi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Biografi</label>
                            <textarea class="form-control" name="bio" rows="4" placeholder="Biografi singkat guru..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CRUD JS -->
    <script src="js/crud.js"></script>
    
    <script>
        // Guru CRUD specific functions
        let currentEditId = null;
        let currentImageFilename = null;
        
        // Load guru cards
        async function loadGuruCards() {
            try {
                const response = await fetch('api/guru.php');
                const result = await response.json();
                
                if (result.success) {
                    const container = document.getElementById('guruContainer');
                    container.innerHTML = '';
                    
                    result.data.forEach(guru => {
                        const card = createGuruCard(guru);
                        container.appendChild(card);
                    });
                } else {
                    showAlert(result.error || 'Gagal memuat data guru', 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        }
        
        // Create guru card element
        function createGuruCard(guru) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 mb-4';
            
            const imageUrl = guru.image || '/alkholis/assets/images/default-guru.jpg';
            
            col.innerHTML = `
                <div class="card guru-card">
                    <div class="card-body text-center">
                        <img src="${imageUrl}" alt="${guru.nama}" class="guru-avatar mb-3">
                        <h5 class="card-title">${guru.nama}</h5>
                        <p class="card-text text-muted">${guru.mata_pelajaran}</p>
                        <p class="card-text small">${guru.jabatan || 'Guru'}</p>
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-sm btn-outline-primary btn-action" onclick="editGuru(${guru.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteGuru(${guru.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            return col;
        }
        
        // Edit guru
        async function editGuru(id) {
            try {
                const response = await fetch(`api/guru.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    currentEditId = id;
                    document.getElementById('modalTitle').textContent = 'Edit Guru';
                    fillGuruForm(result.data);
                    
                    // Use Bootstrap modal
                    const modal = new bootstrap.Modal(document.getElementById('addGuruModal'));
                    modal.show();
                } else {
                    showAlert(result.error || 'Gagal memuat data guru', 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        }
        
        // Fill form with guru data
        function fillGuruForm(guru) {
            const form = document.getElementById('guruForm');
            if (!form) return;
            
            form.nama.value = guru.nama;
            form.nip.value = guru.nip || '';
            form.jabatan.value = guru.jabatan || 'Guru';
            form.mata_pelajaran.value = guru.mata_pelajaran;
            form.email.value = guru.email || '';
            form.phone.value = guru.phone || '';
            form.bio.value = guru.bio || '';
            form.status.value = guru.status;
            
            // Handle image - use 'image' field from database, map to 'photo' form field
            if (guru.image) {
                const imagePreview = document.getElementById('imagePreview');
                const imagePreviewContainer = document.getElementById('imagePreviewContainer');
                const photo = document.getElementById('photo');
                const removeImageBtn = document.getElementById('removeImageBtn');
                const imageUploadContainer = document.getElementById('imageUploadContainer');
                
                if (imagePreview) imagePreview.src = guru.image;
                if (imagePreviewContainer) imagePreviewContainer.style.display = 'block';
                if (photo) photo.value = guru.image;
                if (removeImageBtn) removeImageBtn.style.display = 'block';
                if (imageUploadContainer) imageUploadContainer.style.display = 'none';
                
                // Extract filename from URL
                const urlParts = guru.image.split('/');
                currentImageFilename = urlParts[urlParts.length - 1];
            } else {
                resetImageUpload();
            }
        }
        
        // Delete guru
        async function deleteGuru(id) {
            if (confirm('Apakah Anda yakin ingin menghapus guru ini?')) {
                try {
                    const response = await fetch(`api/guru.php?id=${id}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        loadGuruCards();
                        showAlert('Guru berhasil dihapus', 'success');
                    } else {
                        showAlert(result.error || 'Gagal menghapus guru', 'danger');
                    }
                } catch (error) {
                    showAlert('Error: ' + error.message, 'danger');
                }
            }
        }
        
        // Handle form submission
        document.getElementById('guruForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Convert FormData to object for validation
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Validate required fields
            if (!data.nama || !data.mata_pelajaran) {
                showAlert('Nama dan mata pelajaran harus diisi', 'danger');
                return;
            }
            
            try {
                if (currentEditId) {
                    formData.append('id', currentEditId);
                    
                    // Convert FormData to URLSearchParams for PUT request
                    const params = new URLSearchParams();
                    formData.forEach((value, key) => {
                        params.append(key, value);
                    });
                    
                    // Send as PUT for update
                    const response = await fetch('api/guru.php', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: params.toString()
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Close modal using Bootstrap
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addGuruModal'));
                        if (modal) {
                            modal.hide();
                        }
                        loadGuruCards();
                        showAlert('Guru berhasil diupdate', 'success');
                        resetForm();
                    } else {
                        showAlert(result.error || 'Gagal mengupdate guru', 'danger');
                    }
                } else {
                    // Send as FormData for create
                    const response = await fetch('api/guru.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Close modal using Bootstrap
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addGuruModal'));
                        if (modal) {
                            modal.hide();
                        }
                        loadGuruCards();
                        showAlert('Guru berhasil ditambahkan', 'success');
                        resetForm();
                    } else {
                        showAlert(result.error || 'Gagal menambahkan guru', 'danger');
                    }
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        });
        
        // Reset form
        function resetForm() {
            document.getElementById('guruForm').reset();
            document.getElementById('modalTitle').textContent = 'Tambah Guru Baru';
            currentEditId = null;
            currentImageFilename = null;
            resetImageUpload();
        }
        
        // Reset image upload
        function resetImageUpload() {
            const imagePreview = document.getElementById('imagePreview');
            const photo = document.getElementById('photo');
            const removeImageBtn = document.getElementById('removeImageBtn');
            const imageUploadContainer = document.getElementById('imageUploadContainer');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const uploadStatus = document.getElementById('uploadStatus');
            const uploadProgress = document.getElementById('uploadProgress');
            
            if (imagePreview) imagePreview.src = '';
            if (photo) photo.value = '';
            if (removeImageBtn) removeImageBtn.style.display = 'none';
            if (imageUploadContainer) imageUploadContainer.style.display = 'block';
            if (imagePreviewContainer) imagePreviewContainer.style.display = 'none';
            if (uploadStatus) uploadStatus.style.display = 'none';
            if (uploadProgress) uploadProgress.style.display = 'none';
            currentImageFilename = null;
        }
        
        // Image upload functionality
        const imageUploadContainer = document.getElementById('imageUploadContainer');
        const imageFileInput = document.getElementById('imageFile');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadStatus = document.getElementById('uploadStatus');
        const removeImageBtn = document.getElementById('removeImageBtn');
        
        // Only add event listeners if elements exist
        if (imageUploadContainer && imageFileInput) {
            // Click to upload
            imageUploadContainer.addEventListener('click', () => {
                imageFileInput.click();
            });
            
            // Drag and drop
            imageUploadContainer.addEventListener('dragover', (e) => {
                e.preventDefault();
                imageUploadContainer.classList.add('dragover');
            });
            
            imageUploadContainer.addEventListener('dragleave', () => {
                imageUploadContainer.classList.remove('dragover');
            });
            
            imageUploadContainer.addEventListener('drop', (e) => {
                e.preventDefault();
                imageUploadContainer.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleImageUpload(files[0]);
                }
            });
        }
        
        if (imageFileInput) {
            // File input change
            imageFileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleImageUpload(e.target.files[0]);
                }
            });
        }
        
        // Handle image upload
        async function handleImageUpload(file) {
            // Validate file type
            if (!file.type.match('image/(jpeg|jpg|png)')) {
                showUploadStatus('Hanya file JPG/PNG yang diizinkan', 'error');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showUploadStatus('Ukuran file maksimal 5MB', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('image', file);
            
            // Show progress
            if (uploadProgress) uploadProgress.style.display = 'block';
            if (uploadStatus) uploadStatus.style.display = 'none';
            
            try {
                const response = await fetch('api/guru_upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show preview
                    if (imagePreview) imagePreview.src = result.data.url;
                    if (imagePreviewContainer) imagePreviewContainer.style.display = 'block';
                    
                    // Update hidden input with photo path - CRITICAL for database sync
                    const photoPath = result.data.url;
                    const photo = document.getElementById('photo');
                    if (photo) photo.value = photoPath;
                    
                    if (imageUploadContainer) imageUploadContainer.style.display = 'none';
                    if (removeImageBtn) removeImageBtn.style.display = 'block';
                    
                    // Hide progress
                    if (uploadProgress) uploadProgress.style.display = 'none';
                    
                    showUploadStatus('Gambar berhasil diupload dan database field image terupdate', 'success');
                } else {
                    showUploadStatus(result.message, 'error');
                }
            } catch (error) {
                showUploadStatus('Error: ' + error.message, 'error');
            }
            
            // Hide progress
            if (uploadProgress) uploadProgress.style.display = 'none';
        }
        
        // Remove image
        async function removeImage() {
            if (currentImageFilename) {
                try {
                    const response = await fetch('api/guru_upload.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ filename: currentImageFilename })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        showUploadStatus('Gambar berhasil dihapus', 'success');
                    }
                } catch (error) {
                    console.error('Error deleting image:', error);
                }
            }
            
            resetImageUpload();
        }
        
        // Show upload status
        function showUploadStatus(message, type) {
            if (!uploadStatus) return;
            
            uploadStatus.textContent = message;
            uploadStatus.className = `upload-status ${type}`;
            uploadStatus.style.display = 'block';
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                if (uploadStatus) uploadStatus.style.display = 'none';
            }, 3000);
        }
        
        // Show alert
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.main-content').firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadGuruCards();
            
            // Reset form when modal is hidden
            document.getElementById('addGuruModal').addEventListener('hidden.bs.modal', function() {
                resetForm();
            });
        });
    </script>
</body>
</html> 