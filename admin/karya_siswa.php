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
    <title>Kelola Karya Siswa - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
    <style>
        .karya-item {
            transition: transform 0.2s;
        }
        .karya-item:hover {
            transform: translateY(-2px);
        }
        .karya-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        .achievement-badge {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            border: none;
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1e3a8a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Card styling */
        .karya-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .karya-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        /* Button group responsive */
        .btn-group .btn {
            padding: 0.375rem 0.5rem;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .btn-group .btn {
                padding: 0.25rem 0.375rem;
                font-size: 0.75rem;
            }
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
                            <h2 class="text-break"><i class="fas fa-palette me-2"></i>Kelola Karya Siswa</h2>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="addKarya()">
                                <i class="fas fa-plus me-2"></i>Tambah Karya
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-sm-12">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari karya siswa...">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select class="form-select" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                <option value="teknologi">Teknologi</option>
                                <option value="seni">Seni</option>
                                <option value="penelitian">Penelitian</option>
                                <option value="multimedia">Multimedia</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select class="form-select" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertContainer"></div>

                    <!-- Karya Grid -->
                    <div class="row g-3" id="karyaContainer">
                        <!-- Karya items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Karya Modal -->
    <div class="modal fade" id="karyaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Karya Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="karyaForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Karya *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="author" class="form-label">Nama Siswa *</label>
                                    <input type="text" class="form-control" id="author" name="author" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi Karya *</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Kategori *</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Pilih Kategori</option>
                                                <option value="teknologi">Teknologi</option>
                                                <option value="seni">Seni</option>
                                                <option value="penelitian">Penelitian</option>
                                                <option value="multimedia">Multimedia</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="year" class="form-label">Tahun</label>
                                            <input type="text" class="form-control" id="year" name="year" placeholder="2024">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="achievement" class="form-label">Pencapaian/Penghargaan</label>
                                    <textarea class="form-control" id="achievement" name="achievement" rows="2" placeholder="Juara 1 Lomba Teknologi Tingkat Provinsi"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Gambar Karya</label>
                                    <div class="border rounded p-3 mb-2 text-center" id="uploadArea" style="cursor: pointer; background:#fafafa">
                                        <div class="mb-2"><i class="fas fa-cloud-upload-alt fa-2x text-muted"></i></div>
                                        <div class="small text-muted">Klik untuk memilih file atau seret & lepas ke sini (JPG/PNG, maks 5MB)</div>
                                        <input type="file" id="imageFile" accept="image/jpeg,image/png" style="display:none;" />
                                    </div>
                                    <input type="hidden" id="image" name="image">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="removeImageBtn">Hapus Gambar</button>
                                        <small class="text-muted">Gambar akan tersimpan setelah upload</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Preview Gambar</label>
                                    <div class="border rounded p-2 text-center">
                                        <img id="imagePreview" src="" alt="Preview" class="img-fluid" style="max-height: 150px; display: none;">
                                        <div id="noImage" class="text-muted">Tidak ada gambar</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Disetujui</option>
                                        <option value="rejected">Ditolak</option>
                                    </select>
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
    <script src="js/crud.js"></script>
    <script>
        // Karya Siswa CRUD specific functions (native fetch)
        let currentEditId = null;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing karya siswa...');
            
            // Check if required elements exist
            const requiredElements = {
                'searchInput': document.getElementById('searchInput'),
                'categoryFilter': document.getElementById('categoryFilter'),
                'statusFilter': document.getElementById('statusFilter'),
                'karyaContainer': document.getElementById('karyaContainer'),
                'alertContainer': document.getElementById('alertContainer')
            };
            
            console.log('Required elements check:', requiredElements);
            
            // Check for missing elements
            const missingElements = Object.keys(requiredElements).filter(key => !requiredElements[key]);
            if (missingElements.length > 0) {
                console.warn('Missing elements:', missingElements);
            }
            
            // Initialize event listeners
            if (requiredElements.searchInput) {
                requiredElements.searchInput.addEventListener('input', filterKarya);
            }
            if (requiredElements.categoryFilter) {
                requiredElements.categoryFilter.addEventListener('change', filterKarya);
            }
            if (requiredElements.statusFilter) {
                requiredElements.statusFilter.addEventListener('change', filterKarya);
            }
            
            // Upload handlers
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('imageFile');
            const removeBtn = document.getElementById('removeImageBtn');
            
            if (uploadArea && fileInput && removeBtn) {
                uploadArea.addEventListener('click', () => fileInput.click());
                uploadArea.addEventListener('dragover', e => { 
                    e.preventDefault(); 
                    uploadArea.classList.add('border-primary');
                });
                uploadArea.addEventListener('dragleave', e => { 
                    uploadArea.classList.remove('border-primary');
                });
                uploadArea.addEventListener('drop', e => { 
                    e.preventDefault(); 
                    uploadArea.classList.remove('border-primary'); 
                    if (e.dataTransfer.files && e.dataTransfer.files[0]) { 
                        handleUpload(e.dataTransfer.files[0]); 
                    }
                });
                fileInput.addEventListener('change', e => { 
                    if (e.target.files && e.target.files[0]) { 
                        handleUpload(e.target.files[0]); 
                    }
                });
                removeBtn.addEventListener('click', removeImage);
            }
            
            // Load initial data
            console.log('Loading initial karya grid...');
            loadKaryaGrid();
        });

        async function loadKaryaGrid() {
            try {
                const container = document.getElementById('karyaContainer');
                
                // Show loading state
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="loading-spinner mb-3"></div>
                        <p class="text-muted">Memuat data karya siswa...</p>
                    </div>
                `;
                
                const res = await fetch('api/karya_siswa.php');
                const json = await res.json();
                
                if (!json.success) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5 class="text-warning">Gagal memuat data</h5>
                            <p class="text-muted">${json.error || 'Terjadi kesalahan saat memuat data'}</p>
                            <button class="btn btn-primary" onclick="loadKaryaGrid()">
                                <i class="fas fa-redo me-2"></i>Coba Lagi
                            </button>
                        </div>
                    `;
                    return;
                }
                
                const data = json.data || [];
                
                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-palette fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada karya siswa</h5>
                            <p class="text-muted">Mulai dengan menambahkan karya siswa pertama</p>
                            <button class="btn btn-primary" onclick="addKarya()">
                                <i class="fas fa-plus me-2"></i>Tambah Karya
                            </button>
                        </div>
                    `;
                    return;
                }
                
                // Clear container and render data
                container.innerHTML = '';
                data.forEach(karya => {
                    const karyaItem = document.createElement('div');
                    karyaItem.className = 'col-md-6 col-lg-4 mb-3 karya-item';
                    
                    // Set dataset for filtering
                    karyaItem.dataset.status = karya.status || 'pending';
                    karyaItem.dataset.category = karya.category || '';
                    karyaItem.dataset.search = `${(karya.title||'')} ${(karya.author||'')} ${(karya.description||'')}`.toLowerCase();
                    
                    const statusBadgeClass = karya.status === 'approved' ? 'success' : 
                                           karya.status === 'rejected' ? 'danger' : 'warning';
                    const statusText = karya.status === 'approved' ? 'Disetujui' : 
                                      karya.status === 'rejected' ? 'Ditolak' : 'Pending';
                    
                    karyaItem.innerHTML = `
                        <div class="card h-100 karya-card">
                            <img src="${karya.image ? '../uploads/karya/' + karya.image : 'https://via.placeholder.com/300x200?text=Karya+Siswa'}" 
                                 class="karya-image" alt="${karya.title}" 
                                 onerror="this.src='https://via.placeholder.com/300x200?text=Karya+Siswa'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-${statusBadgeClass}">${statusText}</span>
                                    <span class="badge bg-info">${karya.category || 'Umum'}</span>
                                </div>
                                <h6 class="card-title">${karya.title || 'Judul Karya'}</h6>
                                <p class="card-text text-muted small mb-2">
                                    <i class="fas fa-user me-1"></i>${karya.author || 'Anonim'}
                                    ${karya.year ? `<br><i class="fas fa-calendar me-1"></i>${karya.year}` : ''}
                                </p>
                                <p class="card-text text-muted small">${karya.description || 'Tidak ada deskripsi'}</p>
                                ${karya.achievement ? `<p class="card-text"><small class="achievement-badge badge">🏆 ${karya.achievement}</small></p>` : ''}
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <button class="btn btn-outline-success" onclick="setStatus(${karya.id}, 'approved')" title="Setujui">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" onclick="setStatus(${karya.id}, 'pending')" title="Pending">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="setStatus(${karya.id}, 'rejected')" title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="editKarya(${karya.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteKarya(${karya.id})" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(karyaItem);
                });
                
                console.log(`Loaded ${data.length} karya items`);
                
            } catch (e) {
                console.error('Error loading karya grid:', e);
                const container = document.getElementById('karyaContainer');
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error</h5>
                        <p class="text-muted">${e.message || 'Terjadi kesalahan yang tidak diketahui'}</p>
                        <button class="btn btn-primary" onclick="loadKaryaGrid()">
                            <i class="fas fa-redo me-2"></i>Coba Lagi
                        </button>
                    </div>
                `;
            }
        }

        async function setStatus(id, status) {
            try {
                const res = await fetch(`api/karya_siswa.php?action=status&id=${id}&status=${status}`);
                const result = await res.json();
                if (result.success) {
                    loadKaryaGrid();
                    showAlert('Status karya diperbarui', 'success');
                } else {
                    showAlert(result.error || 'Gagal mengubah status', 'danger');
                }
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
            }
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            alertContainer.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }

        // Filter karya
        function filterKarya() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            const items = document.querySelectorAll('.karya-item');
            
            items.forEach(item => {
                const matchesSearch = !searchTerm || (item.dataset.search || '').includes(searchTerm);
                const matchesCategory = !categoryFilter || (item.dataset.category || '') === categoryFilter;
                const matchesStatus = !statusFilter || (item.dataset.status || '') === statusFilter;
                item.style.display = (matchesSearch && matchesCategory && matchesStatus) ? 'block' : 'none';
            });
        }

        // Update image preview
        function updateImagePreview() {
            const imageUrl = document.getElementById('image').value;
            const preview = document.getElementById('imagePreview');
            const noImage = document.getElementById('noImage');
            
            if (imageUrl) {
                // Check if it's a full URL or just filename
                const imagePath = imageUrl.startsWith('http') ? imageUrl : '../uploads/karya/' + imageUrl;
                preview.src = imagePath;
                preview.style.display = 'block';
                noImage.style.display = 'none';
            } else {
                preview.style.display = 'none';
                noImage.style.display = 'block';
            }
        }

        async function handleUpload(file) {
            if (!file) return;
            if (!['image/jpeg','image/png'].includes(file.type)) {
                showAlert('Format harus JPG/PNG', 'danger');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showAlert('Ukuran file maksimal 5MB', 'danger');
                return;
            }
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch('api/karya_upload.php', { method: 'POST', body: fd });
                const json = await res.json();
                if (!json.success) {
                    showAlert(json.error || 'Upload gagal', 'danger');
                    return;
                }
                const imgInput = document.getElementById('image');
                imgInput.value = json.image;
                imgInput.dataset.filename = json.filename;
                updateImagePreview();
                showAlert('Gambar berhasil diupload', 'success');
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
            }
        }

        async function removeImage() {
            const input = document.getElementById('image');
            const filename = input.dataset.filename;
            if (filename) {
                try {
                    await fetch(`api/karya_upload.php?filename=${encodeURIComponent(filename)}`, { method: 'DELETE' });
                } catch (_) {}
            }
            input.value = '';
            input.dataset.filename = '';
            updateImagePreview();
            showAlert('Gambar dihapus', 'success');
        }

        // Add new karya
        function addKarya() {
            currentEditId = null;
            document.getElementById('karyaForm').reset();
            const imgInput = document.getElementById('image');
            imgInput.value = '';
            imgInput.dataset.filename = '';
            document.getElementById('modalTitle').textContent = 'Tambah Karya Siswa';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('noImage').style.display = 'block';
            const modal = new bootstrap.Modal(document.getElementById('karyaModal'));
            modal.show();
        }

        // Edit karya
        async function editKarya(id) {
            currentEditId = id;
            try {
                const res = await fetch(`api/karya_siswa.php?id=${id}`);
                const json = await res.json();
                if (!json.success || !json.data) {
                    showAlert(json.error || 'Karya tidak ditemukan', 'danger');
                    return;
                }
                const data = json.data;
                const form = document.getElementById('karyaForm');
                ['title','author','description','category','year','achievement','image','status','id'].forEach(name => {
                    const el = form.querySelector(`[name="${name}"]`);
                    if (el && data[name] !== undefined) el.value = data[name] ?? '';
                });
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
                return;
            }
            document.getElementById('modalTitle').textContent = 'Edit Karya Siswa';
            updateImagePreview();
            const modal = new bootstrap.Modal(document.getElementById('karyaModal'));
            modal.show();
        }

        // Delete karya
        async function deleteKarya(id) {
            if (confirm('Apakah Anda yakin ingin menghapus karya ini?')) {
                try {
                    const res = await fetch(`api/karya_siswa.php?id=${id}`, { method: 'DELETE' });
                    const json = await res.json();
                    if (json.success) {
                        showAlert('Karya berhasil dihapus', 'success');
                        loadKaryaGrid();
                    } else {
                        showAlert(json.error || 'Gagal menghapus karya', 'danger');
                    }
                } catch (e) {
                    showAlert('Error: ' + e.message, 'danger');
                }
            }
        }

        // Submit karya form
        document.getElementById('karyaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                if (currentEditId) {
                    // Update with URL-encoded
                    const fd = new FormData(this);
                    fd.set('id', currentEditId);
                    const params = new URLSearchParams();
                    fd.forEach((v, k) => params.append(k, v));
                    const res = await fetch('api/karya_siswa.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString()
                    });
                    const json = await res.json();
                    if (!json.success) {
                        showAlert(json.error || 'Gagal mengupdate karya', 'danger');
                        return;
                    }
                } else {
                    // Create with FormData
                    const fd = new FormData(this);
                    const res = await fetch('api/karya_siswa.php', {
                        method: 'POST',
                        body: fd
                    });
                    const json = await res.json();
                    if (!json.success) {
                        showAlert(json.error || 'Gagal menambah karya', 'danger');
                        return;
                    }
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('karyaModal'));
                if (modal) modal.hide();
                loadKaryaGrid();
                showAlert('Perubahan tersimpan', 'success');
            } catch (e) {
                showAlert('Error: ' + e.message, 'danger');
            }
        });

        // Override global functions
        window.editItem = editKarya;
        window.deleteItem = deleteKarya;
        window.addItem = addKarya;
    </script>
</body>
</html> 