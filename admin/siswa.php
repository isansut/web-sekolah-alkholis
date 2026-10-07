<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Kelola Siswa';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SMA Al-Kholis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Admin Styles -->
    <link href="includes/admin-styles.css" rel="stylesheet">
    <style>
        .siswa-card { border:none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .siswa-photo { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 3px solid #1e3a8a; }
        .image-upload { border:2px dashed #dee2e6; border-radius:10px; padding:20px; text-align:center; background:#f8f9fa; cursor:pointer; }
        .image-upload.dragover { background:#e3f2fd; border-color:#1e3a8a; }
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
                        <h2 class="mb-1 text-break">Kelola Siswa</h2>
                        <p class="text-muted mb-0 text-break">Tambah, edit, dan kelola data siswa</p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="addSiswa()"><i class="fas fa-user-plus me-2"></i>Tambah Siswa</button>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-5 col-sm-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari siswa (nama, NISN)...">
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <select id="kelasFilter" class="form-select">
                            <option value="">Semua Kelas</option>
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <select id="statusFilter" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div id="alertContainer"></div>
                <div class="row g-3" id="siswaContainer"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="siswaModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="siswaForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" name="nama" class="form-control" required>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="kelas" class="form-select">
                      <option value="">Pilih Kelas</option>
                      <option value="X">X</option>
                      <option value="XI">XI</option>
                      <option value="XII">XII</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                      <option value="active">Aktif</option>
                      <option value="inactive">Tidak Aktif</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" rows="3" class="form-control"></textarea>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Foto Siswa</label>
                <div id="imageUploadContainer">
                    <input type="file" class="form-control" id="imageFile" accept="image/*">
                    <small class="text-muted">Upload foto siswa (JPG, PNG, max 5MB)</small>
                </div>
                <div id="imagePreviewContainer" style="display: none;">
                    <img id="imagePreview" src="" alt="Preview" class="img-thumbnail mt-2" style="max-width: 200px;">
                    <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImageBtn" onclick="removeImage()">Hapus Foto</button>
                </div>
                <input type="hidden" id="imageUrl" name="photo">
              </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentEditId = null;

document.addEventListener('DOMContentLoaded', () => {
  loadSiswa();
  document.getElementById('searchInput').addEventListener('input', filterSiswa);
  document.getElementById('kelasFilter').addEventListener('change', filterSiswa);
  document.getElementById('statusFilter').addEventListener('change', filterSiswa);

  const upload = document.getElementById('imageUploadContainer');
  const file = document.getElementById('imageFile');
  upload.addEventListener('click', () => file.click());
  upload.addEventListener('dragover', e => { e.preventDefault(); upload.classList.add('dragover'); });
  upload.addEventListener('dragleave', () => upload.classList.remove('dragover'));
  upload.addEventListener('drop', e => { e.preventDefault(); upload.classList.remove('dragover'); if (e.dataTransfer.files && e.dataTransfer.files[0]) handleUpload(e.dataTransfer.files[0]); });
  file.addEventListener('change', e => { if (e.target.files && e.target.files[0]) handleUpload(e.target.files[0]); });
  document.getElementById('removeImageBtn').addEventListener('click', removeImage);

  document.getElementById('siswaForm').addEventListener('submit', submitForm);
});

function showAlert(msg, type='success'){ const c = document.getElementById('alertContainer'); const d = document.createElement('div'); d.className = `alert alert-${type} alert-dismissible fade show`; d.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`; c.appendChild(d); setTimeout(()=>d.remove(),4000); }

async function loadSiswa(){
  try{
    const res = await fetch('api/siswa.php');
    const json = await res.json();
    if(!json.success) return;
    const data = json.data || [];
    const container = document.getElementById('siswaContainer');
    container.innerHTML = '';
    data.forEach(s => container.appendChild(renderCard(s)));
  }catch(e){}
}

function renderCard(s){
  const col = document.createElement('div');
  col.className = 'col-md-6 col-lg-4 mb-4';
  col.dataset.kelas = s.kelas||'';
  col.dataset.status = s.status||'';
  col.dataset.search = `${(s.nama||'').toLowerCase()} ${(s.nisn||'').toLowerCase()}`;
  
  // Use 'photo' field instead of 'photo_url' for consistency
  const img = s.photo || 'https://via.placeholder.com/150?text=Siswa';
  
  col.innerHTML = `
    <div class="card siswa-card h-100">
      <div class="card-body text-center">
        <img src="${img}" class="siswa-photo mb-3" onerror="this.src='https://via.placeholder.com/150?text=Siswa'" alt="${s.nama}">
        <h5 class="card-title mb-1">${s.nama}</h5>
        <p class="text-muted small mb-2">${s.kelas || '-'}</p>
        <p class="text-muted small">NISN: ${s.nisn || '-'}</p>
        <div class="btn-group btn-group-sm" role="group">
          <button class="btn btn-outline-primary" onclick="editSiswa(${s.id})"><i class="fas fa-edit"></i></button>
          <button class="btn btn-outline-danger" onclick="deleteSiswa(${s.id})"><i class="fas fa-trash"></i></button>
        </div>
      </div>
    </div>`;
  return col;
}

function filterSiswa(){
  const q = (document.getElementById('searchInput').value||'').toLowerCase();
  const fk = document.getElementById('kelasFilter').value;
  const fs = document.getElementById('statusFilter').value;
  document.querySelectorAll('#siswaContainer > div').forEach(el => {
    const match = (!q || (el.dataset.search||'').includes(q)) && (!fk || el.dataset.kelas===fk) && (!fs || el.dataset.status===fs);
    el.style.display = match ? 'block' : 'none';
  });
}

function addSiswa(){
  currentEditId = null;
  document.getElementById('siswaForm').reset();
  setPhoto('');
  document.getElementById('modalTitle').textContent = 'Tambah Siswa';
  new bootstrap.Modal(document.getElementById('siswaModal')).show();
}

async function editSiswa(id){
  currentEditId = id;
  try{
    const res = await fetch(`api/siswa.php?id=${id}`);
    const json = await res.json();
    if(!json.success || !json.data) return;
    const d = json.data;
    const f = document.getElementById('siswaForm');
    ['nama','nisn','kelas','alamat','status','photo'].forEach(k=>{ const el=f.querySelector(`[name="${k}"]`); if(el) el.value = d[k] || ''; });
    setPhoto(d.photo||'');
  }catch(e){}
  document.getElementById('modalTitle').textContent = 'Edit Siswa';
  new bootstrap.Modal(document.getElementById('siswaModal')).show();
}

async function deleteSiswa(id){
  if(!confirm('Hapus data siswa ini?')) return;
  try{
    const res = await fetch(`api/siswa.php?id=${id}`, { method:'DELETE' });
    const json = await res.json();
    if(json.success){ showAlert('Siswa dihapus','success'); loadSiswa(); }
  }catch(e){}
}

async function submitForm(e){
  e.preventDefault();
  try{
    if(currentEditId){
      const fd = new FormData(this);
      fd.set('id', currentEditId);
      const params = new URLSearchParams(); fd.forEach((v,k)=>params.append(k,v));
      const res = await fetch('api/siswa.php', { method:'PUT', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: params.toString() });
      const json = await res.json();
      if(!json.success) return;
    } else {
      const fd = new FormData(this);
      const res = await fetch('api/siswa.php', { method:'POST', body: fd });
      const json = await res.json();
      if(!json.success) return;
    }
    const modal = bootstrap.Modal.getInstance(document.getElementById('siswaModal')); if(modal) modal.hide();
    loadSiswa();
    showAlert('Data tersimpan','success');
  }catch(e){}
}

function setPhoto(url){
  const img = document.getElementById('imagePreview');
  const no = document.getElementById('imagePreviewContainer');
  document.getElementById('imageUrl').value = url || '';
  if(url){ img.src = url; no.style.display='none'; } else { img.src = ''; no.style.display='block'; }
}

async function handleUpload(file){
  if(!file) return; if(!['image/jpeg','image/png'].includes(file.type)) return; if(file.size>5*1024*1024) return;
  const fd = new FormData();
  // send as 'image' (API juga menerima 'photo')
  fd.append('image', file);
  try{
    const res = await fetch('api/siswa_upload.php', { method:'POST', body: fd });
    const json = await res.json();
    if(!json.success) return;
    const url = json.image || json.photo || '';
    if(!url) return;
    
    // Update hidden input with photo path - CRITICAL for database sync
    setPhoto(url);
    document.getElementById('imageUrl').dataset.filename = json.filename;
    
    showAlert('Foto berhasil diupload dan database field photo terupdate', 'success');
  }catch(e){
    showAlert('Error upload: ' + e.message, 'danger');
  }
}

async function removeImage(){
  const input = document.getElementById('imageUrl');
  const filename = input.dataset.filename; if(filename){ try{ await fetch(`api/siswa_upload.php?filename=${encodeURIComponent(filename)}`, { method:'DELETE' }); }catch(_){} }
  input.value=''; input.dataset.filename=''; setPhoto('');
}
</script>
</body>
</html> 