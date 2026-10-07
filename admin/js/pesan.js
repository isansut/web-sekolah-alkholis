// Pesan Admin Panel JavaScript
class PesanManager {
    constructor() {
        this.apiEndpoint = 'api/pesan.php';
        this.currentPesanId = null;
        this.init();
    }

    init() {
        console.log('Initializing PesanManager...');
        this.loadPesanList();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Search and filter
        const searchInput = document.getElementById('searchInput');
        const priorityFilter = document.getElementById('priorityFilter');
        const statusFilter = document.getElementById('statusFilter');

        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterPesan());
        }
        if (priorityFilter) {
            priorityFilter.addEventListener('change', () => this.filterPesan());
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterPesan());
        }

        // Action buttons
        const markAllReadBtn = document.querySelector('button[onclick="markAllRead()"]');
        const deleteAllReadBtn = document.querySelector('button[onclick="deleteAllRead()"]');

        if (markAllReadBtn) {
            markAllReadBtn.onclick = () => this.markAllRead();
        }
        if (deleteAllReadBtn) {
            deleteAllReadBtn.onclick = () => this.deleteAllRead();
        }
    }

    async loadPesanList() {
        try {
            console.log('Loading pesan list...');
            const container = document.getElementById('pesanContainer');
            
            if (!container) {
                console.error('Container not found: pesanContainer');
                return;
            }

            // Show loading state
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="loading-spinner mb-3"></div>
                    <p class="text-muted">Memuat data pesan...</p>
                </div>
            `;

            const response = await fetch(this.apiEndpoint);
            const result = await response.json();

            if (!result.success) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="text-warning">Gagal memuat data</h5>
                        <p class="text-muted">${result.error || 'Terjadi kesalahan saat memuat data'}</p>
                        <button class="btn btn-primary" onclick="pesanManager.loadPesanList()">
                            <i class="fas fa-redo me-2"></i>Coba Lagi
                        </button>
                    </div>
                `;
                return;
            }

            const data = result.data || [];
            console.log(`Loaded ${data.length} pesan items`);

            if (data.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada pesan</h5>
                        <p class="text-muted">Pesan dari website akan muncul di sini</p>
                    </div>
                `;
                return;
            }

            // Clear container and render data
            container.innerHTML = '';
            data.forEach(pesan => {
                const pesanItem = this.createPesanItem(pesan);
                container.appendChild(pesanItem);
            });

        } catch (error) {
            console.error('Error loading pesan list:', error);
            const container = document.getElementById('pesanContainer');
            if (container) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error</h5>
                        <p class="text-muted">${error.message || 'Terjadi kesalahan yang tidak diketahui'}</p>
                        <button class="btn btn-primary" onclick="pesanManager.loadPesanList()">
                            <i class="fas fa-redo me-2"></i>Coba Lagi
                        </button>
                    </div>
                `;
            }
        }
    }

    createPesanItem(pesan) {
        const pesanItem = document.createElement('div');
        pesanItem.className = 'col-12 mb-3 pesan-item';
        pesanItem.dataset.priority = pesan.priority || '';
        pesanItem.dataset.status = pesan.status || '';
        pesanItem.dataset.search = `${(pesan.name||'')} ${(pesan.subject||'')} ${(pesan.message||'')}`.toLowerCase();

        const priorityClass = pesan.priority === 'high' ? 'priority-high' : 
                            pesan.priority === 'medium' ? 'priority-medium' : 'priority-low';
        const priorityText = pesan.priority === 'high' ? 'Tinggi' : 
                           pesan.priority === 'medium' ? 'Sedang' : 'Rendah';
        const priorityBadgeClass = pesan.priority === 'high' ? 'danger' : 
                                 pesan.priority === 'medium' ? 'warning' : 'success';
        
        const statusBadgeClass = pesan.status === 'unread' ? 'primary' : 
                               pesan.status === 'read' ? 'secondary' : 'success';
        const statusText = pesan.status === 'unread' ? 'Belum Dibaca' : 
                         pesan.status === 'read' ? 'Sudah Dibaca' : 'Sudah Dibalas';
        
        const unreadClass = pesan.status === 'unread' ? 'unread' : '';
        
        pesanItem.innerHTML = `
            <div class="card ${priorityClass} ${unreadClass} pesan-card" onclick="pesanManager.viewPesan(${pesan.id})" style="cursor: pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">
                                ${pesan.status === 'unread' ? '<i class="fas fa-circle text-primary me-2"></i>' : ''}
                                ${pesan.subject || 'Tidak ada subjek'}
                            </h6>
                            <p class="card-text text-muted small mb-1">
                                <i class="fas fa-user me-1"></i>${pesan.name || 'Anonim'}
                                <i class="fab fa-whatsapp me-2 ms-3 text-success"></i>${pesan.whatsapp || 'N/A'}
                            </p>
                            <p class="card-text message-preview text-muted">${pesan.message || 'Tidak ada pesan'}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${priorityBadgeClass} me-1">${priorityText}</span>
                            <span class="badge bg-${statusBadgeClass}">${statusText}</span>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${this.formatDate(pesan.created_at)}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return pesanItem;
    }

    async viewPesan(id) {
        this.currentPesanId = id;
        
        try {
            const response = await fetch(`${this.apiEndpoint}?id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                const pesan = result.data;
                this.showPesanModal(pesan);
            } else {
                this.showAlert('Error loading pesan', 'danger');
            }
        } catch (error) {
            console.error('Error loading pesan:', error);
            this.showAlert('Error loading pesan', 'danger');
        }
    }

    showPesanModal(pesan) {
        const priorityText = pesan.priority === 'high' ? 'Tinggi' : 
                           pesan.priority === 'medium' ? 'Sedang' : 'Rendah';
        const priorityBadgeClass = pesan.priority === 'high' ? 'danger' : 
                                 pesan.priority === 'medium' ? 'warning' : 'success';
        
        const statusText = pesan.status === 'unread' ? 'Belum Dibaca' : 
                         pesan.status === 'read' ? 'Sudah Dibaca' : 'Sudah Dibalas';
        const statusBadgeClass = pesan.status === 'unread' ? 'primary' : 
                               pesan.status === 'read' ? 'secondary' : 'success';
        
        const modalBody = document.getElementById('pesanDetail');
        if (modalBody) {
            modalBody.innerHTML = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6>${pesan.subject || 'Tidak ada subjek'}</h6>
                        <div>
                            <span class="badge bg-${priorityBadgeClass} me-1">${priorityText}</span>
                            <span class="badge bg-${statusBadgeClass}">${statusText}</span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nama:</strong> ${pesan.name || 'Anonim'}
                    </div>
                    <div class="col-md-6">
                        <strong>WhatsApp:</strong> 
                        <a href="https://wa.me/${pesan.whatsapp}" target="_blank" class="text-decoration-none">
                            <i class="fab fa-whatsapp text-success me-1"></i>${pesan.whatsapp || 'N/A'}
                        </a>
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Pesan:</strong>
                    <div class="border rounded p-3 mt-2 bg-light">
                        ${(pesan.message || 'Tidak ada pesan').replace(/\n/g, '<br>')}
                    </div>
                </div>
                <div class="text-muted">
                    <small>
                        <i class="fas fa-calendar me-1"></i>Dikirim: ${this.formatDate(pesan.created_at)}
                    </small>
                </div>
            `;
        }

        // Update button visibility
        const markReadBtn = document.getElementById('markReadBtn');
        if (markReadBtn) {
            markReadBtn.style.display = pesan.status === 'unread' ? 'inline-block' : 'none';
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('pesanModal'));
        modal.show();
    }

    async markAsRead() {
        if (!this.currentPesanId) return;
        
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: this.currentPesanId,
                    status: 'read'
                })
            });
            
            const result = await response.json();
            if (result.success) {
                this.showAlert('Pesan berhasil ditandai sebagai dibaca', 'success');
                this.loadPesanList();
                const modal = bootstrap.Modal.getInstance(document.getElementById('pesanModal'));
                if (modal) modal.hide();
            } else {
                this.showAlert('Gagal menandai pesan sebagai dibaca', 'danger');
            }
        } catch (error) {
            console.error('Error marking as read:', error);
            this.showAlert('Error marking as read', 'danger');
        }
    }

    async deletePesan() {
        if (!this.currentPesanId) return;
        
        if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
            try {
                const response = await fetch(`${this.apiEndpoint}?id=${this.currentPesanId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                if (result.success) {
                    this.showAlert('Pesan berhasil dihapus', 'success');
                    this.loadPesanList();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('pesanModal'));
                    if (modal) modal.hide();
                } else {
                    this.showAlert('Gagal menghapus pesan', 'danger');
                }
            } catch (error) {
                console.error('Error deleting pesan:', error);
                this.showAlert('Error deleting pesan', 'danger');
            }
        }
    }

    async markAllRead() {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_all_read'
                })
            });
            
            const result = await response.json();
            if (result.success) {
                this.showAlert('Semua pesan berhasil ditandai sebagai dibaca', 'success');
                this.loadPesanList();
            } else {
                this.showAlert('Gagal menandai semua pesan sebagai dibaca', 'danger');
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
            this.showAlert('Error marking all as read', 'danger');
        }
    }

    async deleteAllRead() {
        if (confirm('Apakah Anda yakin ingin menghapus semua pesan yang sudah dibaca?')) {
            try {
                const response = await fetch(this.apiEndpoint, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_all_read'
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    this.showAlert('Semua pesan yang sudah dibaca berhasil dihapus', 'success');
                    this.loadPesanList();
                } else {
                    this.showAlert('Gagal menghapus pesan yang sudah dibaca', 'danger');
                }
            } catch (error) {
                console.error('Error deleting all read:', error);
                this.showAlert('Error deleting all read', 'danger');
            }
        }
    }

    filterPesan() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const priorityFilter = document.getElementById('priorityFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        
        const items = document.querySelectorAll('.pesan-item');
        
        items.forEach(item => {
            const matchesSearch = !searchTerm || (item.dataset.search || '').includes(searchTerm);
            const matchesPriority = !priorityFilter || (item.dataset.priority || '') === priorityFilter;
            const matchesStatus = !statusFilter || (item.dataset.status || '') === statusFilter;
            
            item.style.display = (matchesSearch && matchesPriority && matchesStatus) ? 'block' : 'none';
        });
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            alertContainer.appendChild(div);
            setTimeout(() => div.remove(), 4000);
        }
    }
}

// Global functions for onclick handlers
function markAllRead() {
    if (window.pesanManager) {
        window.pesanManager.markAllRead();
    }
}

function deleteAllRead() {
    if (window.pesanManager) {
        window.pesanManager.deleteAllRead();
    }
}

function markAsRead() {
    if (window.pesanManager) {
        window.pesanManager.markAsRead();
    }
}

function deletePesan() {
    if (window.pesanManager) {
        window.pesanManager.deletePesan();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing PesanManager...');
    window.pesanManager = new PesanManager();
}); 