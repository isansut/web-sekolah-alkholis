<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Kelola Berita';
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
                        <h2 class="mb-1 text-break">Kelola Berita</h2>
                        <p class="text-muted mb-0 text-break">Tambah, edit, dan kelola berita sekolah</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="addBerita()">
                            <i class="fas fa-plus me-2"></i>Tambah Berita
                        </button>
                    </div>
                </div>
                
                <!-- Berita Table -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="beritaTable">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Penulis</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Berita Modal -->
    <div class="modal fade" id="beritaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Berita Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="beritaForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="beritaId">
                        <div class="mb-3">
                            <label class="form-label">Judul Berita</label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ringkasan (Excerpt)</label>
                            <textarea class="form-control" name="excerpt" id="excerpt" rows="3" placeholder="Ringkasan singkat berita (opsional)"></textarea>
                            <small class="text-muted">Jika kosong, akan dibuat otomatis dari konten</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konten Lengkap</label>
                            <textarea class="form-control" name="content" id="content" rows="8" required placeholder="Tulis konten berita lengkap di sini..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Penulis</label>
                                    <input type="text" class="form-control" name="author" id="author" value="Admin">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Sampul (opsional)</label>
                            <div class="border rounded p-3 mb-2 text-center" id="uploadAreaBerita" style="cursor: pointer; background:#fafafa">
                                <div class="mb-2"><i class="fas fa-cloud-upload-alt fa-2x text-muted"></i></div>
                                <div class="small text-muted">Klik untuk memilih file atau seret & lepas ke sini (JPG/PNG, maks 5MB)</div>
                                <input type="file" id="imageFileBerita" accept="image/jpeg,image/png" style="display:none;" />
                            </div>
                            <input type="hidden" id="image" name="image">
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="removeImageBtnBerita">Hapus Gambar</button>
                                <small class="text-muted">Gambar akan tersimpan setelah upload</small>
                            </div>
                            <div class="border rounded p-2 text-center">
                                <img id="imagePreviewBerita" src="" alt="Preview" class="img-fluid" style="max-height: 150px; display: none;">
                                <div id="noImageBerita" class="text-muted">Tidak ada gambar</div>
                            </div>
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
    <!-- CRUD JS -->
    <script src="js/crud.js"></script>
    
    <script>
        // Berita CRUD specific functions (fetch native)
        let currentEditId = null;

        async function loadBeritaTable() {
            try {
                const res = await fetch('api/berita.php');
                const json = await res.json();
                if (!json.success) return;
                const data = json.data || [];
                const tbody = document.querySelector('#beritaTable tbody');
                tbody.innerHTML = '';
                data.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${item.title}</td>
                        <td>${item.author || '-'}</td>
                        <td><span class="badge ${item.status==='published'?'bg-success':'bg-secondary'}">${item.status}</span></td>
                        <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-action" onclick="editBerita(${item.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteBerita(${item.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (e) {}
        }

        function addBerita() {
            currentEditId = null;
            document.getElementById('beritaForm').reset();
            document.querySelector('#beritaModal .modal-title').textContent = 'Tambah Berita Baru';
            setPreview('');
            const modal = new bootstrap.Modal(document.getElementById('beritaModal'));
            modal.show();
        }

        async function editBerita(id) {
            currentEditId = id;
            try {
                const res = await fetch(`api/berita.php?id=${id}`);
                const json = await res.json();
                if (!json.success || !json.data) return;
                const d = json.data;
                const f = document.getElementById('beritaForm');
                
                // Update modal title
                document.querySelector('#beritaModal .modal-title').textContent = 'Edit Berita';
                
                // Fill form fields using IDs
                document.getElementById('beritaId').value = d.id;
                document.getElementById('title').value = d.title || '';
                document.getElementById('excerpt').value = d.excerpt || '';
                document.getElementById('content').value = d.content || '';
                document.getElementById('author').value = d.author || 'Admin';
                document.getElementById('status').value = d.status || 'draft';
                document.getElementById('image').value = d.image || '';
                setPreview(d.image || '');
                
                console.log('Form filled with data:', d); // Debug log
            } catch(e) {
                console.error('Error loading berita data:', e); // Debug log
            }
            const modal = new bootstrap.Modal(document.getElementById('beritaModal'));
            modal.show();
        }

        async function deleteBerita(id) {
            if (!confirm('Hapus berita ini?')) return;
            try {
                const res = await fetch(`api/berita.php?id=${id}`, { method: 'DELETE' });
                const json = await res.json();
                if (json.success) loadBeritaTable();
            } catch (e) {}
        }

        document.getElementById('beritaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const formData = new FormData(this);
                
                if (currentEditId) {
                    // Update existing berita
                    formData.set('id', currentEditId);
                    const params = new URLSearchParams();
                    formData.forEach((v,k)=>params.append(k,v));
                    
                    console.log('Updating berita with data:', Object.fromEntries(formData)); // Debug log
                    
                    const res = await fetch('api/berita.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString()
                    });
                    const json = await res.json();
                    if (!json.success) {
                        console.error('Update failed:', json); // Debug log
                        return;
                    }
                    console.log('Update successful:', json); // Debug log
                } else {
                    // Create new berita
                    console.log('Creating new berita with data:', Object.fromEntries(formData)); // Debug log
                    
                    const res = await fetch('api/berita.php', { method: 'POST', body: formData });
                    const json = await res.json();
                    if (!json.success) {
                        console.error('Create failed:', json); // Debug log
                        return;
                    }
                    console.log('Create successful:', json); // Debug log
                }
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('beritaModal'));
                if (modal) modal.hide();
                loadBeritaTable();
            } catch (e) {
                console.error('Form submission error:', e); // Debug log
            }
        });

        // Upload handlers berita
        const uploadArea = document.getElementById('uploadAreaBerita');
        const fileInput = document.getElementById('imageFileBerita');
        const removeBtn = document.getElementById('removeImageBtnBerita');
        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('border-primary');});
        uploadArea.addEventListener('dragleave', e => { uploadArea.classList.remove('border-primary');});
        uploadArea.addEventListener('drop', e => { e.preventDefault(); uploadArea.classList.remove('border-primary'); if (e.dataTransfer.files && e.dataTransfer.files[0]) { handleUploadBerita(e.dataTransfer.files[0]); }});
        fileInput.addEventListener('change', e => { if (e.target.files && e.target.files[0]) { handleUploadBerita(e.target.files[0]); }});
        removeBtn.addEventListener('click', removeImageBerita);

        function setPreview(url){
            const preview = document.getElementById('imagePreviewBerita');
            const noImage = document.getElementById('noImageBerita');
            if (url) {
                // Handle both relative and absolute paths
                let imageUrl = url;
                if (url.startsWith('/')) {
                    // Absolute path - use as is
                    imageUrl = url;
                } else if (url && !url.includes('/')) {
                    // Just filename - construct relative path
                    imageUrl = '../uploads/berita/' + url;
                }
                preview.src = imageUrl;
                preview.style.display = 'block';
                noImage.style.display = 'none';
            } else {
                preview.style.display = 'none';
                noImage.style.display = 'block';
            }
        }

        async function handleUploadBerita(file){
            if (!file) return;
            if (!['image/jpeg','image/png'].includes(file.type)) return;
            if (file.size > 5*1024*1024) return;
            const fd = new FormData();
            fd.append('image', file);
            try {
                const res = await fetch('api/berita_upload.php', { method: 'POST', body: fd });
                const json = await res.json();
                if (!json.success) return;
                document.getElementById('image').value = json.image;
                document.getElementById('image').dataset.filename = json.filename;
                setPreview(json.image);
            } catch (e) {}
        }

        async function removeImageBerita(){
            const input = document.getElementById('image');
            const filename = input.dataset.filename;
            if (filename) {
                try { await fetch(`api/berita_upload.php?filename=${encodeURIComponent(filename)}`, { method: 'DELETE' }); } catch (_){ }
            }
            input.value = '';
            input.dataset.filename = '';
            setPreview('');
        }

        window.editItem = editBerita;
        window.deleteItem = deleteBerita;
        window.addItem = addBerita;

        document.addEventListener('DOMContentLoaded', function(){
            loadBeritaTable();
        });
    </script>
</body>
</html> 