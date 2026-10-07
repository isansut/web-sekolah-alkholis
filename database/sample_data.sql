-- =====================================================
-- SAMPLE DATA FOR SMA AL-KHOLIS WEBSITE
-- =====================================================

USE websekolah_alkholis;

-- =====================================================
-- USERS DATA (Admin, Guru, Siswa)
-- =====================================================
INSERT INTO users (username, password, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@alkholis.sch.id', 'admin', 'active'),
('guru001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru001@alkholis.sch.id', 'guru', 'active'),
('guru002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru002@alkholis.sch.id', 'guru', 'active'),
('siswa001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa001@alkholis.sch.id', 'siswa', 'active'),
('siswa002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siswa002@alkholis.sch.id', 'siswa', 'active');

-- =====================================================
-- GURU DATA (Teachers)
-- =====================================================
INSERT INTO guru (nama, nip, jabatan, mata_pelajaran, email, phone, alamat, bio, status) VALUES
('Ahmad Supriyadi, S.Pd', '198501152010012001', 'Guru', 'Matematika', 'ahmad.supriyadi@alkholis.sch.id', '081234567890', 'Jl. Merdeka No. 123, Jakarta', 'Guru Matematika dengan pengalaman 10 tahun mengajar. Spesialisasi dalam aljabar dan geometri.', 'active'),
('Siti Nurhaliza, M.Pd', '198603202010012002', 'Guru', 'Bahasa Indonesia', 'siti.nurhaliza@alkholis.sch.id', '081234567891', 'Jl. Sudirman No. 456, Jakarta', 'Guru Bahasa Indonesia dengan gelar Magister Pendidikan. Ahli dalam sastra dan tata bahasa.', 'active'),
('Budi Santoso, S.Pd', '198708152010012003', 'Guru', 'Fisika', 'budi.santoso@alkholis.sch.id', '081234567892', 'Jl. Thamrin No. 789, Jakarta', 'Guru Fisika yang aktif dalam penelitian dan olimpiade sains tingkat nasional.', 'active'),
('Dewi Sartika, S.Pd', '198912102010012004', 'Guru', 'Kimia', 'dewi.sartika@alkholis.sch.id', '081234567893', 'Jl. Gatot Subroto No. 321, Jakarta', 'Guru Kimia dengan passion dalam eksperimen laboratorium dan penelitian.', 'active'),
('Rudi Hartono, S.Pd', '199002251010012005', 'Guru', 'Biologi', 'rudi.hartono@alkholis.sch.id', '081234567894', 'Jl. Rasuna Said No. 654, Jakarta', 'Guru Biologi yang aktif dalam kegiatan ekstrakurikuler dan penelitian siswa.', 'active');

-- =====================================================
-- SISWA DATA (Students)
-- =====================================================
INSERT INTO siswa (nama, nisn, nis, kelas, jurusan, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, email, phone, alamat, nama_ortu, phone_ortu, status) VALUES
('Muhammad Rizki Pratama', '1234567890', '2024001', 'XII', 'IPA', 'Jakarta', '2006-03-15', 'L', 'Islam', 'rizki.pratama@alkholis.sch.id', '081234567895', 'Jl. Kebayoran Lama No. 123, Jakarta', 'Ahmad Pratama', '081234567896', 'active'),
('Nurul Hidayah', '1234567891', '2024002', 'XII', 'IPA', 'Jakarta', '2006-07-22', 'P', 'Islam', 'nurul.hidayah@alkholis.sch.id', '081234567897', 'Jl. Kebayoran Baru No. 456, Jakarta', 'Siti Aminah', '081234567898', 'active'),
('Aditya Nugraha', '1234567892', '2024003', 'XI', 'IPS', 'Jakarta', '2007-01-10', 'L', 'Islam', 'aditya.nugraha@alkholis.sch.id', '081234567899', 'Jl. Pondok Indah No. 789, Jakarta', 'Budi Nugraha', '081234567900', 'active'),
('Putri Wulandari', '1234567893', '2024004', 'XI', 'IPA', 'Jakarta', '2007-05-18', 'P', 'Islam', 'putri.wulandari@alkholis.sch.id', '081234567901', 'Jl. Cilandak No. 321, Jakarta', 'Dewi Sartika', '081234567902', 'active'),
('Fajar Ramadhan', '1234567894', '2024005', 'X', 'IPA', 'Jakarta', '2008-09-30', 'L', 'Islam', 'fajar.ramadhan@alkholis.sch.id', '081234567903', 'Jl. Lebak Bulus No. 654, Jakarta', 'Rudi Hartono', '081234567904', 'active');

-- =====================================================
-- JADWAL DATA (Class Schedules)
-- =====================================================
INSERT INTO jadwal (kelas, hari, jam_mulai, jam_selesai, mata_pelajaran, guru_id, ruangan, semester, tahun_ajaran, status) VALUES
('XII IPA', 'Senin', '07:00:00', '08:30:00', 'Matematika', 1, 'Ruang 1', 'Ganjil', '2024/2025', 'active'),
('XII IPA', 'Senin', '08:45:00', '10:15:00', 'Bahasa Indonesia', 2, 'Ruang 1', 'Ganjil', '2024/2025', 'active'),
('XII IPA', 'Senin', '10:30:00', '12:00:00', 'Fisika', 3, 'Lab Fisika', 'Ganjil', '2024/2025', 'active'),
('XII IPA', 'Selasa', '07:00:00', '08:30:00', 'Kimia', 4, 'Lab Kimia', 'Ganjil', '2024/2025', 'active'),
('XII IPA', 'Selasa', '08:45:00', '10:15:00', 'Biologi', 5, 'Lab Biologi', 'Ganjil', '2024/2025', 'active'),
('XI IPA', 'Senin', '07:00:00', '08:30:00', 'Matematika', 1, 'Ruang 2', 'Ganjil', '2024/2025', 'active'),
('XI IPA', 'Senin', '08:45:00', '10:15:00', 'Bahasa Indonesia', 2, 'Ruang 2', 'Ganjil', '2024/2025', 'active'),
('X IPA', 'Senin', '07:00:00', '08:30:00', 'Matematika', 1, 'Ruang 3', 'Ganjil', '2024/2025', 'active');

-- =====================================================
-- EKSTRAKURIKULER DATA
-- =====================================================
INSERT INTO ekstrakurikuler (nama, deskripsi, pembina_id, kategori, jadwal, tempat, kuota, status) VALUES
('Basket', 'Ekstrakurikuler basket untuk mengembangkan bakat olahraga siswa dan prestasi sekolah', 1, 'Olahraga', 'Senin & Rabu 15:00-17:00', 'Lapangan Basket', 25, 'active'),
('English Club', 'Klub bahasa Inggris untuk meningkatkan kemampuan berbahasa dan persiapan TOEFL', 2, 'Akademik', 'Selasa & Kamis 15:00-16:30', 'Ruang Bahasa', 30, 'active'),
('Rohis', 'Rohani Islam untuk pembentukan karakter religius dan kegiatan keagamaan', 3, 'Keagamaan', 'Jumat 15:00-17:00', 'Masjid Sekolah', 40, 'active'),
('Robotik', 'Klub robotik untuk mengembangkan kreativitas dan inovasi teknologi', 4, 'Teknologi', 'Sabtu 08:00-11:00', 'Lab Komputer', 20, 'active'),
('Seni Tari', 'Ekstrakurikuler seni tari tradisional dan modern untuk melestarikan budaya', 5, 'Seni', 'Rabu & Jumat 15:00-17:00', 'Aula Sekolah', 25, 'active');

-- =====================================================
-- SISWA EKSTRAKURIKULER DATA
-- =====================================================
INSERT INTO siswa_ekstrakurikuler (siswa_id, ekstrakurikuler_id, tanggal_daftar, status) VALUES
(1, 1, '2024-01-15', 'active'),
(1, 2, '2024-01-15', 'active'),
(2, 2, '2024-01-15', 'active'),
(2, 3, '2024-01-15', 'active'),
(3, 1, '2024-01-15', 'active'),
(3, 4, '2024-01-15', 'active'),
(4, 2, '2024-01-15', 'active'),
(4, 5, '2024-01-15', 'active'),
(5, 1, '2024-01-15', 'active'),
(5, 3, '2024-01-15', 'active');

-- =====================================================
-- BERITA DATA (News)
-- =====================================================
INSERT INTO berita (title, content, excerpt, author, category, tags, status, featured, view_count) VALUES
('SMA Al-Kholis Raih Juara 1 Olimpiade Sains Nasional', 'Tim SMA Al-Kholis berhasil meraih juara 1 dalam Olimpiade Sains Nasional tingkat provinsi DKI Jakarta. Prestasi ini merupakan hasil kerja keras siswa dan pembina selama berbulan-bulan.', 'Tim SMA Al-Kholis berhasil meraih juara 1 dalam Olimpiade Sains Nasional tingkat provinsi DKI Jakarta.', 'Admin', 'Prestasi', 'olimpiade,sains,prestasi', 'published', 1, 150),
('Kegiatan Outbound Siswa Kelas XII', 'Siswa kelas XII mengikuti kegiatan outbound di Taman Safari Indonesia sebagai bagian dari program pengembangan karakter dan kerjasama tim.', 'Siswa kelas XII mengikuti kegiatan outbound di Taman Safari Indonesia sebagai bagian dari program pengembangan karakter.', 'Admin', 'Kegiatan', 'outbound,karakter,kerjasama', 'published', 0, 89),
('Workshop Teknologi Informasi untuk Guru', 'SMA Al-Kholis mengadakan workshop teknologi informasi untuk meningkatkan kompetensi digital guru dalam pembelajaran jarak jauh.', 'Workshop teknologi informasi untuk meningkatkan kompetensi digital guru dalam pembelajaran jarak jauh.', 'Admin', 'Pendidikan', 'workshop,teknologi,guru', 'published', 0, 67),
('Kunjungan Industri ke PT Telkom Indonesia', 'Siswa jurusan IPA melakukan kunjungan industri ke PT Telkom Indonesia untuk mengenal dunia kerja dan teknologi telekomunikasi.', 'Siswa jurusan IPA melakukan kunjungan industri ke PT Telkom Indonesia untuk mengenal dunia kerja.', 'Admin', 'Kegiatan', 'kunjungan,industri,teknologi', 'published', 0, 45),
('Pelaksanaan Ujian Tengah Semester', 'Ujian Tengah Semester (UTS) akan dilaksanakan pada tanggal 15-25 Maret 2024. Semua siswa diharapkan mempersiapkan diri dengan baik.', 'Ujian Tengah Semester (UTS) akan dilaksanakan pada tanggal 15-25 Maret 2024.', 'Admin', 'Akademik', 'ujian,semester,akademik', 'published', 0, 123);

-- =====================================================
-- GALERI DATA (Gallery)
-- =====================================================
INSERT INTO galeri (title, description, category, album, status) VALUES
('Gedung Sekolah', 'Gedung utama SMA Al-Kholis yang megah dan modern', 'Infrastruktur', 'Gedung Sekolah', 'active'),
('Perpustakaan', 'Perpustakaan sekolah dengan koleksi buku yang lengkap', 'Infrastruktur', 'Fasilitas Sekolah', 'active'),
('Lab Komputer', 'Laboratorium komputer dengan peralatan terbaru', 'Infrastruktur', 'Fasilitas Sekolah', 'active'),
('Kegiatan Belajar', 'Suasana belajar yang kondusif di kelas', 'Kegiatan', 'Kegiatan Belajar', 'active'),
('Ekstrakurikuler', 'Kegiatan ekstrakurikuler yang beragam dan menarik', 'Kegiatan', 'Ekstrakurikuler', 'active'),
('Upacara Bendera', 'Upacara bendera setiap hari Senin', 'Kegiatan', 'Upacara', 'active'),
('Kunjungan Industri', 'Kunjungan siswa ke berbagai industri', 'Kegiatan', 'Kunjungan', 'active'),
('Prestasi Siswa', 'Berbagai prestasi yang diraih siswa', 'Prestasi', 'Prestasi', 'active');

-- =====================================================
-- KARYA SISWA DATA (Student Works)
-- =====================================================
INSERT INTO karya_siswa (title, author, category, description, year, achievement, status) VALUES
('Aplikasi E-Learning untuk SMA', 'Muhammad Rizki Pratama', 'teknologi', 'Aplikasi pembelajaran online yang memudahkan siswa belajar dari rumah dengan fitur video conference dan quiz interaktif.', '2024', 'Juara 1 Lomba Aplikasi Mobile Tingkat Provinsi', 'approved'),
('Lukisan Digital "Keindahan Alam Indonesia"', 'Nurul Hidayah', 'seni', 'Lukisan digital yang menggambarkan keindahan alam Indonesia dengan teknik digital painting yang modern.', '2024', 'Juara 2 Lomba Seni Digital Tingkat Nasional', 'approved'),
('Penelitian Pengaruh Musik terhadap Konsentrasi Belajar', 'Aditya Nugraha', 'penelitian', 'Penelitian tentang pengaruh jenis musik terhadap tingkat konsentrasi siswa dalam belajar.', '2024', 'Juara 3 Lomba Penelitian Ilmiah Tingkat Kota', 'approved'),
('Video Dokumenter "Sejarah Jakarta"', 'Putri Wulandari', 'multimedia', 'Video dokumenter tentang sejarah perkembangan kota Jakarta dari masa ke masa.', '2024', 'Juara 1 Lomba Video Dokumenter Tingkat Provinsi', 'approved'),
('Cerpen "Mimpi di Pagi Hari"', 'Fajar Ramadhan', 'literasi', 'Cerita pendek tentang perjuangan seorang siswa dalam meraih cita-citanya.', '2024', 'Juara 2 Lomba Menulis Cerpen Tingkat Sekolah', 'approved');

-- =====================================================
-- PENGUMUMAN DATA (Announcements)
-- =====================================================
INSERT INTO pengumuman (title, content, priority, status, target_audience, expiry_date) VALUES
('Libur Sekolah Hari Raya Idul Fitri', 'Sekolah akan libur pada tanggal 10-12 April 2024 dalam rangka Hari Raya Idul Fitri. Kegiatan belajar mengajar akan dilanjutkan pada tanggal 15 April 2024.', 'high', 'published', 'semua', '2024-04-15'),
('Jadwal Ujian Nasional 2024', 'Ujian Nasional akan dilaksanakan pada tanggal 20-25 Mei 2024. Semua siswa kelas XII diharapkan mempersiapkan diri dengan baik.', 'urgent', 'published', 'siswa', '2024-05-25'),
('Pendaftaran Ekstrakurikuler Semester Genap', 'Pendaftaran ekstrakurikuler untuk semester genap akan dibuka pada tanggal 1-10 Februari 2024. Silakan daftar di ruang BK.', 'medium', 'published', 'siswa', '2024-02-10'),
('Workshop Parenting untuk Orang Tua', 'Workshop parenting akan diadakan pada tanggal 15 Februari 2024 pukul 09:00 WIB di aula sekolah. Semua orang tua diundang untuk hadir.', 'medium', 'published', 'orang_tua', '2024-02-15'),
('Kegiatan Pramuka Wajib', 'Kegiatan pramuka wajib untuk semua siswa akan dilaksanakan setiap hari Sabtu pukul 07:00-10:00 WIB.', 'low', 'published', 'siswa', '2024-12-31');

-- =====================================================
-- PRESTASI DATA (Achievements)
-- =====================================================
INSERT INTO prestasi (nama_prestasi, kategori, tingkat, peringkat, tahun, deskripsi, siswa_id, guru_pembimbing_id, status) VALUES
('Juara 1 Olimpiade Sains Nasional', 'akademik', 'nasional', 'Juara 1', '2024', 'Tim SMA Al-Kholis berhasil meraih juara 1 dalam Olimpiade Sains Nasional bidang Matematika.', 1, 1, 'active'),
('Juara 2 Lomba Debat Bahasa Inggris', 'non-akademik', 'provinsi', 'Juara 2', '2024', 'Tim debat bahasa Inggris SMA Al-Kholis berhasil meraih juara 2 tingkat provinsi.', 2, 2, 'active'),
('Juara 1 Lomba Basket Antar SMA', 'olahraga', 'kota', 'Juara 1', '2024', 'Tim basket SMA Al-Kholis berhasil menjadi juara umum dalam turnamen basket antar SMA se-Jakarta.', 3, 1, 'active'),
('Juara 3 Lomba Seni Lukis', 'seni', 'provinsi', 'Juara 3', '2024', 'Siswa SMA Al-Kholis berhasil meraih juara 3 dalam lomba seni lukis tingkat provinsi.', 4, 5, 'active'),
('Juara 1 Lomba Robotik', 'teknologi', 'nasional', 'Juara 1', '2024', 'Tim robotik SMA Al-Kholis berhasil meraih juara 1 dalam kompetisi robotik tingkat nasional.', 5, 4, 'active');

-- =====================================================
-- CONTACT MESSAGES DATA
-- =====================================================
INSERT INTO contact_messages (name, email, subject, message, priority, status) VALUES
('Ahmad Supriyadi', 'ahmad.supriyadi@email.com', 'Informasi Pendaftaran Siswa Baru', 'Saya ingin bertanya tentang pendaftaran siswa baru untuk tahun ajaran 2024/2025. Kapan pendaftaran dibuka dan apa saja persyaratannya?', 'high', 'unread'),
('Siti Aminah', 'siti.aminah@email.com', 'Konsultasi Akademik', 'Saya ingin berkonsultasi tentang perkembangan akademik anak saya yang saat ini duduk di kelas XI IPA.', 'medium', 'unread'),
('Budi Santoso', 'budi.santoso@email.com', 'Kerjasama Program Magang', 'Saya dari PT Maju Bersama ingin menawarkan program magang untuk siswa SMA Al-Kholis.', 'medium', 'unread'),
('Dewi Sartika', 'dewi.sartika@email.com', 'Donasi Buku Perpustakaan', 'Saya ingin mendonasikan beberapa buku untuk perpustakaan sekolah. Bagaimana prosedurnya?', 'low', 'unread'),
('Rudi Hartono', 'rudi.hartono@email.com', 'Kunjungan Industri', 'Saya ingin mengajukan kunjungan industri untuk siswa jurusan IPA. Apakah ada slot waktu yang tersedia?', 'medium', 'unread');

-- =====================================================
-- SETTINGS DATA (Website Configuration)
-- =====================================================
INSERT INTO settings (setting_key, setting_value, description, category) VALUES
('site_name', 'SMA Al-Kholis', 'Nama website sekolah', 'general'),
('site_description', 'Sekolah Menengah Atas Al-Kholis - Unggul dalam Prestasi, Berakhlak Mulia', 'Deskripsi website sekolah', 'general'),
('site_address', 'Jl. Pendidikan No. 123, Jakarta Selatan', 'Alamat sekolah', 'contact'),
('site_phone', '+62-21-12345678', 'Nomor telepon sekolah', 'contact'),
('site_email', 'info@alkholis.sch.id', 'Email sekolah', 'contact'),
('site_website', 'www.alkholis.sch.id', 'Website sekolah', 'contact'),
('social_facebook', 'https://facebook.com/smaalkholis', 'Link Facebook sekolah', 'social'),
('social_instagram', 'https://instagram.com/smaalkholis', 'Link Instagram sekolah', 'social'),
('social_youtube', 'https://youtube.com/smaalkholis', 'Link YouTube sekolah', 'social'),
('academic_year', '2024/2025', 'Tahun ajaran aktif', 'academic'),
('semester', 'Ganjil', 'Semester aktif', 'academic'),
('school_level', 'SMA', 'Tingkat sekolah', 'general'),
('school_type', 'Negeri', 'Jenis sekolah', 'general'),
('school_accreditation', 'A', 'Akreditasi sekolah', 'general'),
('school_establishment', '1990', 'Tahun berdirinya sekolah', 'general');

-- =====================================================
-- ACTIVITY LOGS DATA
-- =====================================================
INSERT INTO activity_logs (user_id, action, table_name, record_id, details, ip_address) VALUES
(1, 'login', 'users', 1, 'Admin login ke sistem', '192.168.1.100'),
(1, 'create', 'berita', 1, 'Membuat berita baru: SMA Al-Kholis Raih Juara 1 Olimpiade Sains Nasional', '192.168.1.100'),
(1, 'create', 'guru', 1, 'Menambahkan guru baru: Ahmad Supriyadi, S.Pd', '192.168.1.100'),
(1, 'create', 'siswa', 1, 'Menambahkan siswa baru: Muhammad Rizki Pratama', '192.168.1.100'),
(1, 'update', 'settings', 1, 'Mengupdate pengaturan website', '192.168.1.100'); 