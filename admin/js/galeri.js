/**
 * Galeri Management JavaScript
 * Compatible with the new galeri API
 */

class GaleriManager {
    constructor() {
        this.apiEndpoint = 'api/galeri.php';
        this.uploadEndpoint = 'api/galeri_upload.php';
        this.currentEditId = null;
        this.init();
    }

    init() {
        this.loadGaleriGrid();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Search and filter
        document.getElementById('searchInput')?.addEventListener('input', () => this.filterGaleri());
        document.getElementById('categoryFilter')?.addEventListener('change', () => this.filterGaleri());
        document.getElementById('statusFilter')?.addEventListener('change', () => this.filterGaleri());
        
        // Image upload
        document.getElementById('imageInput')?.addEventListener('change', (e) => this.handleImageUpload(e));
        document.getElementById('imageUploadContainer')?.addEventListener('dragover', (e) => this.handleDragOver(e));
        document.getElementById('imageUploadContainer')?.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        document.getElementById('imageUploadContainer')?.addEventListener('drop', (e) => this.handleDrop(e));
        document.getElementById('removeImageBtn')?.addEventListener('click', () => this.removeImage());
        
        // Form submission
        document.getElementById('galeriForm')?.addEventListener('submit', (e) => this.handleFormSubmit(e));
    }

    // Load galeri grid
    async loadGaleriGrid() {
        try {
            const response = await fetch(this.apiEndpoint);
            const result = await response.json();
            
            if (result.success) {
                this.renderGaleriGrid(result.data);
            } else {
                this.showAlert('danger', result.error || 'Gagal memuat data galeri');
            }
        } catch (error) {
            this.showAlert('danger', 'Error: ' + error.message);
            console.error('Error loading galeri:', error);
        }
    }

    // Render galeri grid
    renderGaleriGrid(data) {
        const container = document.getElementById('galeriContainer');
        if (!container) return;

        container.innerHTML = '';
        
        if (data.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                    <h5>Tidak ada galeri ditemukan</h5>
                    <p class="text-muted">Mulai dengan menambahkan galeri pertama</p>
                </div>
            `;
            return;
        }

        data.forEach(galeri => {
            const galeriItem = document.createElement('div');
            galeriItem.className = 'col-md-6 col-lg-4 mb-4 galeri-item';
            galeriItem.innerHTML = `
                <div class="card h-100">
                    <img src="${galeri.image_url}" class="galeri-image" alt="${galeri.title}" 
                         onerror="this.src='https://via.placeholder.com/300x200?text=Gambar+Tidak+Tersedia'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-${galeri.status === 'active' ? 'success' : 'secondary'}">${galeri.status}</span>
                            <span class="badge bg-info">${galeri.category}</span>
                        </div>
                        <h6 class="card-title">${galeri.title}</h6>
                        <p class="card-text text-muted small">${galeri.description || 'Tidak ada deskripsi'}</p>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>${this.formatDate(galeri.created_at)}
                        </small>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button class="btn btn-outline-primary" onclick="galeriManager.editGaleri(${galeri.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-outline-danger" onclick="galeriManager.deleteGaleri(${galeri.id})">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(galeriItem);
        });
    }

    // Filter galeri
    filterGaleri() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const categoryFilter = document.getElementById('categoryFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        
        const items = document.querySelectorAll('.galeri-item');
        
        items.forEach(item => {
            const title = item.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const description = item.querySelector('.card-text')?.textContent.toLowerCase() || '';
            const category = item.querySelector('.badge.bg-info')?.textContent || '';
            const status = item.querySelector('.badge.bg-success, .badge.bg-secondary')?.textContent || '';
            
            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesCategory = !categoryFilter || category === categoryFilter;
            const matchesStatus = !statusFilter || status === statusFilter;
            
            item.style.display = matchesSearch && matchesCategory && matchesStatus ? 'block' : 'none';
        });
    }

    // Handle image upload
    handleImageUpload(event) {
        const file = event.target.files[0];
        if (file) {
            this.uploadImage(file);
        }
    }

    handleDragOver(event) {
        event.preventDefault();
        document.getElementById('imageUploadContainer')?.classList.add('dragover');
    }

    handleDragLeave(event) {
        document.getElementById('imageUploadContainer')?.classList.remove('dragover');
    }

    handleDrop(event) {
        event.preventDefault();
        document.getElementById('imageUploadContainer')?.classList.remove('dragover');
        const file = event.dataTransfer.files[0];
        if (file) {
            this.uploadImage(file);
        }
    }

    async uploadImage(file) {
        const uploadContainer = document.getElementById('imageUploadContainer');
        const imagePreview = document.getElementById('imagePreview');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadStatus = document.getElementById('uploadStatus');
        const imageUrlInput = document.getElementById('imageUrl');
        const removeImageBtn = document.getElementById('removeImageBtn');

        if (!uploadContainer || !imagePreview || !uploadProgress || !uploadStatus || !imageUrlInput || !removeImageBtn) {
            return;
        }

        uploadContainer.style.display = 'none';
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        uploadProgress.style.display = 'block';
        uploadStatus.style.display = 'none';
        removeImageBtn.style.display = 'none';

        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch(this.uploadEndpoint, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                imagePreview.src = data.url;
                imagePreview.style.display = 'block';
                imageUrlInput.value = data.url;
                removeImageBtn.style.display = 'block';
                uploadStatus.textContent = 'Gambar berhasil diunggah!';
                uploadStatus.className = 'upload-status text-success';
                uploadStatus.style.display = 'block';
            } else {
                throw new Error(data.error || 'Gagal mengunggah gambar.');
            }
        } catch (error) {
            uploadStatus.textContent = `Gagal mengunggah gambar: ${error.message}`;
            uploadStatus.className = 'upload-status text-danger';
            uploadStatus.style.display = 'block';
            console.error('Error uploading image:', error);
        } finally {
            uploadProgress.style.display = 'none';
        }
    }

    removeImage() {
        const uploadContainer = document.getElementById('imageUploadContainer');
        const imagePreview = document.getElementById('imagePreview');
        const imageUrlInput = document.getElementById('imageUrl');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const uploadStatus = document.getElementById('uploadStatus');

        if (!uploadContainer || !imagePreview || !imageUrlInput || !removeImageBtn || !uploadStatus) {
            return;
        }

        uploadContainer.style.display = 'block';
        imagePreview.src = '';
        imagePreview.style.display = 'none';
        imageUrlInput.value = '';
        removeImageBtn.style.display = 'none';
        uploadStatus.textContent = 'Gambar berhasil dihapus.';
        uploadStatus.className = 'upload-status text-success';
        uploadStatus.style.display = 'block';
    }

    // Add new galeri
    addGaleri() {
        this.currentEditId = null;
        this.clearForm();
        document.getElementById('modalTitle').textContent = 'Tambah Galeri';
        
        // Reset image upload
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('imageUploadContainer').style.display = 'block';
        document.getElementById('removeImageBtn').style.display = 'none';
        document.getElementById('imageUrl').value = '';
        document.getElementById('uploadStatus').style.display = 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('galeriModal'));
        modal.show();
    }

    // Edit galeri
    async editGaleri(id) {
        try {
            const response = await fetch(`${this.apiEndpoint}?id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                this.currentEditId = id;
                this.fillForm(result.data);
                document.getElementById('modalTitle').textContent = 'Edit Galeri';
                
                // Show existing image
                const imageUrl = result.data.image_url;
                if (imageUrl) {
                    document.getElementById('imagePreview').src = imageUrl;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.getElementById('imageUploadContainer').style.display = 'none';
                    document.getElementById('removeImageBtn').style.display = 'block';
                } else {
                    document.getElementById('imagePreview').style.display = 'none';
                    document.getElementById('imageUploadContainer').style.display = 'block';
                    document.getElementById('removeImageBtn').style.display = 'none';
                }
                
                const modal = new bootstrap.Modal(document.getElementById('galeriModal'));
                modal.show();
            } else {
                this.showAlert('danger', result.error || 'Gagal memuat data galeri');
            }
        } catch (error) {
            this.showAlert('danger', 'Error: ' + error.message);
            console.error('Error editing galeri:', error);
        }
    }

    // Delete galeri
    async deleteGaleri(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus galeri ini?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiEndpoint}?id=${id}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', result.message);
                this.loadGaleriGrid();
            } else {
                this.showAlert('danger', result.error || 'Gagal menghapus galeri');
            }
        } catch (error) {
            this.showAlert('danger', 'Error: ' + error.message);
            console.error('Error deleting galeri:', error);
        }
    }

    // Handle form submission
    async handleFormSubmit(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        if (!data.title || !data.image_url) {
            this.showAlert('danger', 'Judul dan gambar harus diisi');
            return;
        }
        
        try {
            let result;
            if (this.currentEditId) {
                data.id = this.currentEditId;
                result = await this.updateGaleri(data);
            } else {
                result = await this.createGaleri(data);
            }
            
            if (result) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('galeriModal'));
                if (modal) {
                    modal.hide();
                }
                this.loadGaleriGrid();
            }
        } catch (error) {
            this.showAlert('danger', 'Error: ' + error.message);
            console.error('Error submitting form:', error);
        }
    }

    // Create galeri
    async createGaleri(data) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                body: new URLSearchParams(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', result.message);
                return result;
            } else {
                this.showAlert('danger', result.error);
                return null;
            }
        } catch (error) {
            this.showAlert('danger', 'Error: ' + error.message);
            return null;
        }
    }

    // Update galeri
    async updateGaleri(data) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', result.message);
                return result;
            } else {
                this.showAlert('danger', result.error);
                return null;
            }
        } catch (error) {
            this.showAlert('danger', 'Error: ' + error.message);
            return null;
        }
    }

    // Fill form with data
    fillForm(data) {
        const form = document.getElementById('galeriForm');
        if (!form) return;

        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = data[key];
            }
        });
    }

    // Clear form
    clearForm() {
        const form = document.getElementById('galeriForm');
        if (!form) return;

        form.reset();
    }

    // Show alert
    showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;

        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHtml;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // Format date
    formatDate(dateString) {
        if (!dateString) return '-';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (error) {
            return dateString;
        }
    }
}

// Initialize galeri manager when DOM is loaded
let galeriManager;
document.addEventListener('DOMContentLoaded', function() {
    galeriManager = new GaleriManager();
});

// Global functions for buttons
function addGaleri() {
    galeriManager?.addGaleri();
}

function editGaleri(id) {
    galeriManager?.editGaleri(id);
}

function deleteGaleri(id) {
    galeriManager?.deleteGaleri(id);
} 