/**
 * Complete Galeri Management JavaScript
 * With full CRUD functionality
 */

console.log('Galeri JavaScript loaded');

let currentEditId = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing galeri...');
    
    // Debug: Check if all required elements exist
    const requiredElements = {
        'searchInput': document.getElementById('searchInput'),
        'categoryFilter': document.getElementById('categoryFilter'),
        'statusFilter': document.getElementById('statusFilter'),
        'imageInput': document.getElementById('imageInput'),
        'imageUploadContainer': document.getElementById('imageUploadContainer'),
        'removeImageBtn': document.getElementById('removeImageBtn'),
        'galeriForm': document.getElementById('galeriForm'),
        'galeriContainer': document.getElementById('galeriContainer'),
        'galeriModal': document.getElementById('galeriModal')
    };
    
    console.log('Required elements check:', requiredElements);
    
    // Check for missing elements
    const missingElements = Object.keys(requiredElements).filter(key => !requiredElements[key]);
    if (missingElements.length > 0) {
        console.warn('Missing elements:', missingElements);
    }
    
    loadGaleriGrid();
    setupEventListeners();
});

function setupEventListeners() {
    // Search and filter
    document.getElementById('searchInput')?.addEventListener('input', filterGaleri);
    document.getElementById('categoryFilter')?.addEventListener('change', filterGaleri);
    document.getElementById('statusFilter')?.addEventListener('change', filterGaleri);
    
    // Image upload - Fix click event
    const imageUploadContainer = document.getElementById('imageUploadContainer');
    const imageInput = document.getElementById('imageInput');
    
    if (imageUploadContainer && imageInput) {
        // Click to upload
        imageUploadContainer.addEventListener('click', function() {
            imageInput.click();
        });
        
        // File change
        imageInput.addEventListener('change', handleImageUpload);
        
        // Drag and drop
        imageUploadContainer.addEventListener('dragover', handleDragOver);
        imageUploadContainer.addEventListener('dragleave', handleDragLeave);
        imageUploadContainer.addEventListener('drop', handleDrop);
    }
    
    // Remove image button
    document.getElementById('removeImageBtn')?.addEventListener('click', removeImage);
    
    // Form submission
    document.getElementById('galeriForm')?.addEventListener('submit', handleFormSubmit);
}

// Load galeri grid
async function loadGaleriGrid() {
    try {
        console.log('Loading galeri grid...');
        const response = await fetch('api/galeri.php');
        const result = await response.json();
        
        if (result.success) {
            console.log('Galeri data loaded:', result.data);
            renderGaleriGrid(result.data);
        } else {
            console.error('Failed to load galeri:', result.error);
            showAlert('danger', result.error || 'Gagal memuat data galeri');
        }
    } catch (error) {
        console.error('Error loading galeri:', error);
        showAlert('danger', 'Error: ' + error.message);
    }
}

// Render galeri grid
function renderGaleriGrid(data) {
    const container = document.getElementById('galeriContainer');
    if (!container) {
        console.error('Galeri container not found');
        return;
    }

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
        // Fix image path for admin panel - use 'image' field instead of 'image_url'
        const imagePath = galeri.image.startsWith('uploads/') ? '../' + galeri.image : galeri.image;
        
        const galeriItem = document.createElement('div');
        galeriItem.className = 'col-md-6 col-lg-4 mb-3 galeri-item';
        galeriItem.innerHTML = `
            <div class="card h-100 galeri-card">
                <img src="${imagePath}" class="galeri-image" alt="${galeri.title}" 
                     onerror="this.src='https://via.placeholder.com/300x200?text=Gambar+Tidak+Tersedia'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-${galeri.status === 'active' ? 'success' : 'secondary'}">${galeri.status}</span>
                        <span class="badge bg-info">${galeri.category}</span>
                    </div>
                    <h6 class="card-title">${galeri.title}</h6>
                    <p class="card-text text-muted small">${galeri.description || 'Tidak ada deskripsi'}</p>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>${formatDate(galeri.created_at)}
                    </small>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button class="btn btn-outline-primary" onclick="editGaleri(${galeri.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteGaleri(${galeri.id})">
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
function filterGaleri() {
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
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        uploadImage(file);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    document.getElementById('imageUploadContainer')?.classList.add('dragover');
}

function handleDragLeave(event) {
    document.getElementById('imageUploadContainer')?.classList.remove('dragover');
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('imageUploadContainer')?.classList.remove('dragover');
    const file = event.dataTransfer.files[0];
    if (file) {
        uploadImage(file);
    }
}

async function uploadImage(file) {
    const uploadContainer = document.getElementById('imageUploadContainer');
    const imagePreview = document.getElementById('imagePreview');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadStatus = document.getElementById('uploadStatus');
    const imageUrlInput = document.getElementById('imageUrl');
    const removeImageBtn = document.getElementById('removeImageBtn');

    if (!uploadContainer || !imagePreview || !uploadProgress || !uploadStatus || !imageUrlInput || !removeImageBtn) {
        console.error('Required elements not found for upload');
        return;
    }

    // Show progress
    uploadContainer.style.display = 'none';
    imagePreview.src = '';
    imagePreview.style.display = 'none';
    uploadProgress.style.display = 'block';
    uploadStatus.style.display = 'none';
    removeImageBtn.style.display = 'none';

    const formData = new FormData();
    formData.append('image', file);

    try {
        console.log('Uploading file:', file.name, 'Size:', file.size);
        
        const response = await fetch('api/galeri_upload.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Upload response:', data);
        
        if (data.success) {
            // Show preview with correct path
            const previewPath = data.url.startsWith('uploads/') ? '../' + data.url : data.url;
            imagePreview.src = previewPath;
            imagePreview.style.display = 'block';
            
            // Update hidden input with image path - CRITICAL for database sync
            imageUrlInput.value = data.url;
            
            removeImageBtn.style.display = 'block';
            
            // Show success status
            uploadStatus.textContent = 'Gambar berhasil diunggah dan database field image terupdate!';
            uploadStatus.className = 'upload-status text-success';
            uploadStatus.style.display = 'block';
            
            showAlert('success', 'Gambar berhasil diunggah dan database field image terupdate!');
            
            console.log('Image preview set to:', previewPath);
            console.log('Image URL input set to:', data.url);
        } else {
            throw new Error(data.error || 'Gagal mengunggah gambar.');
        }
    } catch (error) {
        console.error('Error uploading image:', error);
        
        // Show error status
        uploadStatus.textContent = `Gagal mengunggah gambar: ${error.message}`;
        uploadStatus.className = 'upload-status text-danger';
        uploadStatus.style.display = 'block';
        
        // Show error alert
        showAlert('danger', `Gagal mengunggah gambar: ${error.message}`);
        
        // Reset upload container
        uploadContainer.style.display = 'block';
    } finally {
        uploadProgress.style.display = 'none';
    }
}

function removeImage() {
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

// Reset filters
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('statusFilter').value = '';
    filterGaleri();
}

// Add new galeri
function addGaleri() {
    currentEditId = null;
    document.getElementById('galeriForm').reset();
    document.getElementById('modalTitle').textContent = 'Tambah Galeri';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('removeImageBtn').style.display = 'none';
    document.getElementById('imageUrl').value = '';
    
    // Reset image upload container
    const uploadContainer = document.getElementById('imageUploadContainer');
    uploadContainer.innerHTML = `
        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
        <p class="mb-2"><strong>Klik di sini untuk pilih gambar</strong></p>
        <p class="mb-2">atau drag & drop gambar di sini</p>
        <p class="text-muted small">Format: JPG/PNG (Maksimal 5MB)</p>
        <input type="file" id="imageInput" accept="image/jpeg,image/jpg,image/png" style="display: none;">
    `;
    
    // Re-setup image input event listener
    const imageInput = document.getElementById('imageInput');
    if (imageInput) {
        imageInput.addEventListener('change', handleImageUpload);
    }
    
    const modal = new bootstrap.Modal(document.getElementById('galeriModal'));
    modal.show();
}

// Edit galeri
async function editGaleri(id) {
    console.log('Editing galeri with ID:', id);
    
    try {
        const response = await fetch(`api/galeri.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            currentEditId = id;
            fillForm(result.data);
            
            // Set modal title
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) {
                modalTitle.textContent = 'Edit Galeri';
            }
            
            // Show existing image with correct path - use 'image' field instead of 'image_url'
            const imageUrl = result.data.image;
            const imagePreview = document.getElementById('imagePreview');
            const imageUploadContainer = document.getElementById('imageUploadContainer');
            const removeImageBtn = document.getElementById('removeImageBtn');
            
            if (imageUrl && imagePreview && imageUploadContainer && removeImageBtn) {
                const imagePath = imageUrl.startsWith('uploads/') ? '../' + imageUrl : imageUrl;
                imagePreview.src = imagePath;
                imagePreview.style.display = 'block';
                imageUploadContainer.style.display = 'none';
                removeImageBtn.style.display = 'block';
                
                console.log('Edit mode - Image preview set to:', imagePath);
            } else {
                // Fallback if elements not found
                if (imagePreview) imagePreview.style.display = 'none';
                if (imageUploadContainer) imageUploadContainer.style.display = 'block';
                if (removeImageBtn) removeImageBtn.style.display = 'none';
            }
            
            // Show modal
            try {
                const modal = new bootstrap.Modal(document.getElementById('galeriModal'));
                modal.show();
            } catch (error) {
                console.error('Error showing edit modal:', error);
                // Fallback: try to show modal manually
                const modalElement = document.getElementById('galeriModal');
                if (modalElement) {
                    modalElement.classList.add('show');
                    modalElement.style.display = 'block';
                    modalElement.setAttribute('aria-hidden', 'false');
                }
            }
        } else {
            showAlert('danger', result.error || 'Gagal memuat data galeri');
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
        console.error('Error editing galeri:', error);
    }
}

// Delete galeri
async function deleteGaleri(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus galeri ini?')) {
        return;
    }

    try {
        const response = await fetch(`api/galeri.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            loadGaleriGrid(); // Refresh data
        } else {
            showAlert('danger', result.error || 'Gagal menghapus galeri');
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
        console.error('Error deleting galeri:', error);
    }
}

// Handle form submission
async function handleFormSubmit(event) {
    event.preventDefault();
    console.log('Form submitted');
    
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    console.log('Form data:', data);
    
    // Validate required fields
    if (!data.title || !data.title.trim()) {
        showAlert('danger', 'Judul galeri harus diisi');
        return;
    }
    
    if (!data.image || !data.image.trim()) {
        showAlert('danger', 'Gambar harus diupload');
        return;
    }
    
    try {
        let result;
        if (currentEditId) {
            data.id = currentEditId;
            console.log('Updating galeri with data:', data);
            result = await updateGaleri(data);
        } else {
            console.log('Creating new galeri with data:', data);
            result = await createGaleri(data);
        }
        
        if (result) {
            // Close modal
            try {
                const modal = bootstrap.Modal.getInstance(document.getElementById('galeriModal'));
                if (modal) {
                    modal.hide();
                } else {
                    // Fallback: hide modal manually
                    const modalElement = document.getElementById('galeriModal');
                    if (modalElement) {
                        modalElement.classList.remove('show');
                        modalElement.style.display = 'none';
                        modalElement.setAttribute('aria-hidden', 'true');
                    }
                }
            } catch (error) {
                console.error('Error closing modal:', error);
            }
            
            // Refresh data
            await loadGaleriGrid();
            
            // Show success message
            showAlert('success', currentEditId ? 'Galeri berhasil diupdate!' : 'Galeri berhasil ditambahkan!');
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
        console.error('Error submitting form:', error);
    }
}

// Create galeri
async function createGaleri(data) {
    try {
        const response = await fetch('api/galeri.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            return result;
        } else {
            showAlert('danger', result.error);
            return null;
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
        return null;
    }
}

// Update galeri
async function updateGaleri(data) {
    try {
        const response = await fetch('api/galeri.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            return result;
        } else {
            showAlert('danger', result.error);
            return null;
        }
    } catch (error) {
        showAlert('danger', 'Error: ' + error.message);
        return null;
    }
}

// Fill form with data
function fillForm(data) {
    const form = document.getElementById('galeriForm');
    if (!form) return;

    Object.keys(data).forEach(key => {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) {
            field.value = data[key];
        }
    });
}

// Show alert
function showAlert(type, message) {
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
function formatDate(dateString) {
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

console.log('Galeri JavaScript initialization complete'); 