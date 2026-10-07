# Website SMA Al-Kholis - PHP Version

Website resmi SMA Al-Kholis yang dibangun menggunakan PHP dan MySQL. Website ini menyediakan informasi lengkap tentang sekolah, berita, guru, galeri, dan karya siswa.

> **Catatan:** Folder `uploads/` dikosongkan di repo ini karena berisi foto asli siswa/guru (privasi). Struktur foldernya tetap dipertahankan — cukup upload ulang foto saat instalasi. Dump database asli juga tidak disertakan; gunakan `database/schema.sql` + `database/sample_data.sql` (data contoh) untuk instalasi baru.

## 🚀 Fitur Utama

### Frontend
- **Halaman Beranda** - Hero section, fitur-fitur sekolah, dan informasi terbaru
- **Berita** - Halaman berita sekolah dengan pagination
- **Guru** - Profil dan informasi guru
- **Galeri** - Galeri foto kegiatan sekolah
- **Karya Siswa** - Pameran karya siswa dalam berbagai kategori
- **Kontak** - Formulir kontak dan informasi sekolah

### Admin Panel
- **Dashboard** - Overview statistik website
- **Kelola Berita** - CRUD berita sekolah
- **Kelola Guru** - CRUD data guru
- **Kelola Karya Siswa** - Approve/reject karya siswa
- **Kelola Galeri** - Upload dan kelola foto galeri
- **Pengaturan** - Konfigurasi website

### Fitur Teknis
- Responsive design dengan Bootstrap 5
- Modern UI/UX dengan animasi
- Image upload dan management
- Search dan filter functionality
- Pagination
- Flash messages
- Security features (CSRF protection, input sanitization)

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Extensions PHP: PDO, PDO_MySQL, GD, FileInfo

## 🛠️ Instalasi

### 1. Clone Repository
```bash
git clone [repository-url]
cd websekolah-alkholis-php
```

### 2. Setup Database
1. Buat database MySQL baru
2. Import file `database/schema.sql`
3. Konfigurasi database di `config/database.php`

### 3. Konfigurasi
1. Copy `config/database.php` dan sesuaikan kredensial database
2. Pastikan folder `uploads/` memiliki permission write
3. Sesuaikan URL base di `includes/functions.php` jika diperlukan

### 4. Upload ke Hostinger
1. Upload semua file ke folder public_html
2. Import database melalui phpMyAdmin
3. Update konfigurasi database untuk production

## 📁 Struktur Folder

```
websekolah-alkholis-php/
├── admin/                 # Admin panel files
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images
├── config/              # Configuration files
├── database/            # Database schema
├── includes/            # Common includes
├── uploads/             # Uploaded files
│   ├── berita/         # News images
│   ├── guru/           # Teacher images
│   └── karya-siswa/    # Student work images
├── index.php           # Homepage
├── berita.php          # News page
├── guru.php            # Teachers page
├── galeri.php          # Gallery page
├── karya-siswa.php     # Student work page
├── kontak.php          # Contact page
└── README.md           # This file
```

## 🗄️ Database Schema

### Tabel Utama
- `users` - User authentication
- `berita` - News articles
- `guru` - Teacher profiles
- `karya_siswa` - Student works
- `galeri` - Gallery images
- `pengumuman` - Announcements
- `settings` - Website settings

## 🔐 Keamanan

- Password hashing menggunakan `password_hash()`
- CSRF protection untuk form
- Input sanitization
- Prepared statements untuk query database
- File upload validation
- Session management

## 🎨 Customization

### Styling
- Edit `assets/css/style.css` untuk custom styling
- Gunakan Bootstrap 5 classes untuk layout
- Custom CSS variables untuk theming

### Content
- Update informasi sekolah di database table `settings`
- Upload logo dan images ke `assets/images/`
- Customize hero section di `index.php`

## 📱 Responsive Design

Website sudah responsive untuk:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🚀 Deployment ke Hostinger

### 1. Upload Files
1. Login ke Hostinger File Manager
2. Navigate ke `public_html`
3. Upload semua file project

### 2. Setup Database
1. Buka Hostinger phpMyAdmin
2. Buat database baru
3. Import `database/schema.sql`

### 3. Konfigurasi
1. Update `config/database.php` dengan kredensial Hostinger
2. Set folder permissions untuk `uploads/`
3. Test website

### 4. Domain Setup
1. Point domain ke folder project
2. Setup SSL certificate
3. Test semua fitur

## 🔧 Maintenance

### Backup Database
```bash
mysqldump -u username -p database_name > backup.sql
```

### Update Website
1. Backup database dan files
2. Upload new files
3. Run database migrations if any
4. Test functionality

### Monitoring
- Check error logs
- Monitor database performance
- Regular backup
- Update dependencies

## 📞 Support

Untuk bantuan dan support:
- Email: admin@alkholis.sch.id
- Phone: +62-21-1234567
- Address: Jl. Pendidikan No. 123, Jakarta

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the project
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📝 Changelog

### Version 1.0.0
- Initial release
- Basic CRUD functionality
- Responsive design
- Admin panel
- Image upload system

---

**Dibuat dengan ❤️ untuk SMA Al-Kholis** 