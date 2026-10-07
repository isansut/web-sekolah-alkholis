// Kontak Admin Panel JavaScript
class KontakManager {
    constructor() {
        this.apiEndpoint = 'api/kontak.php';
        this.settings = {};
        this.init();
    }

    init() {
        console.log('Initializing KontakManager...');
        this.loadSettings();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Form submission
        const form = document.getElementById('contactForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Auto-save on input change
        const inputs = document.querySelectorAll('#contactForm input, #contactForm textarea');
        inputs.forEach(input => {
            input.addEventListener('input', () => this.autoSave(input));
        });
    }

    async loadSettings() {
        try {
            console.log('Loading contact settings...');
            const response = await fetch(this.apiEndpoint);
            const result = await response.json();

            if (!result.success) {
                this.showAlert('Gagal memuat pengaturan kontak', 'danger');
                return;
            }

            this.settings = {};
            result.data.forEach(setting => {
                this.settings[setting.setting_key] = setting.setting_value;
            });

            this.populateForm();
            console.log('Settings loaded:', this.settings);

        } catch (error) {
            console.error('Error loading settings:', error);
            this.showAlert('Error loading settings', 'danger');
        }
    }

    populateForm() {
        // Basic Information
        this.setFieldValue('company_name', this.settings.company_name || '');
        this.setFieldValue('website', this.settings.website || '');
        this.setFieldValue('about_text', this.settings.about_text || '');

        // Contact Information
        this.setFieldValue('address', this.settings.address || '');
        this.setFieldValue('office_hours', this.settings.office_hours || '');
        this.setFieldValue('phone', this.settings.phone || '');
        this.setFieldValue('whatsapp', this.settings.whatsapp || '');
        this.setFieldValue('email', this.settings.email || '');

        // Vision & Mission
        this.setFieldValue('vision', this.settings.vision || '');
        this.setFieldValue('mission', this.settings.mission || '');

        // Social Media
        this.setFieldValue('facebook', this.settings.facebook || '');
        this.setFieldValue('instagram', this.settings.instagram || '');
        this.setFieldValue('youtube', this.settings.youtube || '');
        this.setFieldValue('twitter', this.settings.twitter || '');

        // Map & Coordinates
        this.setFieldValue('latitude', this.settings.latitude || '');
        this.setFieldValue('longitude', this.settings.longitude || '');
        this.setFieldValue('map_embed', this.settings.map_embed || '');
    }

    setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value;
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        await this.saveAllSettings();
    }

    async saveAllSettings() {
        try {
            const saveBtn = document.querySelector('.btn-save');
            const originalText = saveBtn.innerHTML;
            
            // Show loading state
            saveBtn.innerHTML = '<span class="loading-spinner"></span>Menyimpan...';
            saveBtn.disabled = true;

            // Collect all form data
            const formData = this.collectFormData();
            
            // Save to database
            const response = await fetch(this.apiEndpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Pengaturan berhasil disimpan!', 'success');
                // Update local settings
                this.settings = { ...this.settings, ...formData.reduce((acc, item) => {
                    acc[item.key] = item.value;
                    return acc;
                }, {})};
            } else {
                this.showAlert('Gagal menyimpan pengaturan: ' + (result.error || 'Unknown error'), 'danger');
            }

        } catch (error) {
            console.error('Error saving settings:', error);
            this.showAlert('Error saving settings', 'danger');
        } finally {
            // Restore button state
            const saveBtn = document.querySelector('.btn-save');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }

    collectFormData() {
        const formData = [];
        const fields = [
            'company_name', 'website', 'about_text',
            'address', 'office_hours', 'phone', 'whatsapp', 'email',
            'vision', 'mission',
            'facebook', 'instagram', 'youtube', 'twitter',
            'latitude', 'longitude', 'map_embed'
        ];

        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                formData.push({
                    key: field,
                    value: element.value.trim(),
                    description: this.getFieldDescription(field)
                });
            }
        });

        return formData;
    }

    getFieldDescription(field) {
        const descriptions = {
            'company_name': 'Nama Sekolah',
            'website': 'Website Sekolah',
            'about_text': 'Tentang Sekolah',
            'address': 'Alamat Sekolah',
            'office_hours': 'Jam Operasional',
            'phone': 'Nomor Telepon',
            'whatsapp': 'Nomor WhatsApp',
            'email': 'Email Sekolah',
            'vision': 'Visi Sekolah',
            'mission': 'Misi Sekolah',
            'facebook': 'Link Facebook',
            'instagram': 'Link Instagram',
            'youtube': 'Link YouTube',
            'twitter': 'Link Twitter',
            'latitude': 'Latitude (Koordinat Latitude)',
            'longitude': 'Longitude (Koordinat Longitude)',
            'map_embed': 'Embed Google Maps'
        };
        return descriptions[field] || field;
    }

    generateMapEmbed() {
        const latitude = document.getElementById('latitude').value.trim();
        const longitude = document.getElementById('longitude').value.trim();
        
        if (!latitude || !longitude) {
            this.showAlert('Mohon isi latitude dan longitude terlebih dahulu', 'warning');
            return;
        }
        
        // Validate coordinates
        const lat = parseFloat(latitude);
        const lng = parseFloat(longitude);
        
        if (isNaN(lat) || isNaN(lng)) {
            this.showAlert('Format koordinat tidak valid. Gunakan format angka (contoh: -6.1750)', 'warning');
            return;
        }
        
        if (lat < -90 || lat > 90) {
            this.showAlert('Latitude harus antara -90 sampai 90', 'warning');
            return;
        }
        
        if (lng < -180 || lng > 180) {
            this.showAlert('Longitude harus antara -180 sampai 180', 'warning');
            return;
        }
        
        // Generate Google Maps embed code
        const mapEmbed = `<iframe src="https://www.google.com/maps/embed/v1/view?key=YOUR_API_KEY&center=${lat},${lng}&zoom=15&maptype=roadmap" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>`;
        
        document.getElementById('map_embed').value = mapEmbed;
        this.showAlert('Map embed berhasil dibuat! Catatan: Ganti YOUR_API_KEY dengan API key Google Maps Anda', 'success');
    }

    async autoSave(field) {
        // Debounce auto-save
        clearTimeout(this.autoSaveTimeout);
        this.autoSaveTimeout = setTimeout(async () => {
            try {
                const response = await fetch(this.apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        key: field.name,
                        value: field.value.trim(),
                        description: this.getFieldDescription(field.name)
                    })
                });

                const result = await response.json();
                if (result.success) {
                    // Update local settings
                    this.settings[field.name] = field.value.trim();
                    console.log(`Auto-saved: ${field.name}`);
                }
            } catch (error) {
                console.error('Auto-save error:', error);
            }
        }, 1000); // 1 second delay
    }

    async previewContact() {
        try {
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            const previewContent = document.getElementById('previewContent');
            
            // Show loading
            previewContent.innerHTML = '<div class="text-center py-5"><div class="loading-spinner mb-3"></div><p>Loading preview...</p></div>';
            modal.show();

            // Get current form data
            const formData = this.collectFormData();
            const settings = formData.reduce((acc, item) => {
                acc[item.key] = item.value;
                return acc;
            }, {});

            // Generate preview HTML
            const previewHTML = this.generatePreviewHTML(settings);
            previewContent.innerHTML = previewHTML;

        } catch (error) {
            console.error('Error generating preview:', error);
            this.showAlert('Error generating preview', 'danger');
        }
    }

    generatePreviewHTML(settings) {
        return `
            <div class="preview-section">
                <div class="row">
                    <div class="col-md-8">
                        <h3>${settings.company_name || 'Nama Sekolah'}</h3>
                        <p class="text-muted">${settings.about_text || 'Deskripsi sekolah akan muncul di sini'}</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5><i class="fas fa-map-marker-alt text-primary me-2"></i>Alamat</h5>
                                <p>${settings.address || 'Alamat sekolah akan muncul di sini'}</p>
                                
                                <h5><i class="fas fa-clock text-primary me-2"></i>Jam Operasional</h5>
                                <p>${settings.office_hours || 'Jam operasional akan muncul di sini'}</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-phone text-primary me-2"></i>Kontak</h5>
                                <p><strong>Telepon:</strong> ${settings.phone || 'N/A'}</p>
                                <p><strong>WhatsApp:</strong> ${settings.whatsapp || 'N/A'}</p>
                                <p><strong>Email:</strong> ${settings.email || 'N/A'}</p>
                                <p><strong>Website:</strong> ${settings.website || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5><i class="fas fa-bullseye text-primary me-2"></i>Visi</h5>
                                <p>${settings.vision || 'Visi sekolah akan muncul di sini'}</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-bullseye text-primary me-2"></i>Misi</h5>
                                <p>${settings.mission || 'Misi sekolah akan muncul di sini'}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <h5><i class="fas fa-share-alt text-primary me-2"></i>Media Sosial</h5>
                        <div class="d-flex flex-column gap-2">
                            ${settings.facebook ? `<a href="${settings.facebook}" target="_blank" class="btn btn-outline-primary"><i class="fab fa-facebook me-2"></i>Facebook</a>` : ''}
                            ${settings.instagram ? `<a href="${settings.instagram}" target="_blank" class="btn btn-outline-danger"><i class="fab fa-instagram me-2"></i>Instagram</a>` : ''}
                            ${settings.youtube ? `<a href="${settings.youtube}" target="_blank" class="btn btn-outline-danger"><i class="fab fa-youtube me-2"></i>YouTube</a>` : ''}
                            ${settings.twitter ? `<a href="${settings.twitter}" target="_blank" class="btn btn-outline-info"><i class="fab fa-twitter me-2"></i>Twitter</a>` : ''}
                        </div>
                        
                        <h5 class="mt-4"><i class="fas fa-map-marker-alt text-primary me-2"></i>Lokasi</h5>
                        <div class="map-preview">
                            ${settings.map_embed || '<div class="text-center py-4 text-muted">Peta akan muncul di sini</div>'}
                        </div>
                        
                        ${settings.latitude && settings.longitude ? `
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Koordinat: ${settings.latitude}, ${settings.longitude}
                            </small>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    async resetToDefault() {
        if (confirm('Apakah Anda yakin ingin mereset semua pengaturan ke default? Tindakan ini tidak dapat dibatalkan.')) {
            try {
                // Reload page to reset form
                location.reload();
            } catch (error) {
                console.error('Error resetting to default:', error);
                this.showAlert('Error resetting to default', 'danger');
            }
        }
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            const div = document.createElement('div');
            div.className = `alert alert-${type} alert-dismissible fade show`;
            div.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            alertContainer.appendChild(div);
            setTimeout(() => div.remove(), 5000);
        }
    }
}

// Global functions for onclick handlers
function saveAllSettings() {
    if (window.kontakManager) {
        window.kontakManager.saveAllSettings();
    }
}

function previewContact() {
    if (window.kontakManager) {
        window.kontakManager.previewContact();
    }
}

function resetToDefault() {
    if (window.kontakManager) {
        window.kontakManager.resetToDefault();
    }
}

function generateMapEmbed() {
    if (window.kontakManager) {
        window.kontakManager.generateMapEmbed();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing KontakManager...');
    window.kontakManager = new KontakManager();
}); 