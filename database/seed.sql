-- TugasKu - Seed Data
-- Data relatif terhadap NOW() untuk demo Urgent Badge selalu benar
-- Semua waktu dalam WIB (UTC+7)

USE tugasku;

-- Hapus data lama (urutan penting karena FK)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE log_status_tugas;
TRUNCATE TABLE tugas;
TRUNCATE TABLE mata_kuliah;
SET FOREIGN_KEY_CHECKS = 1;

-- 5 Mata Kuliah
INSERT INTO mata_kuliah (id_mk, kode_mk, nama_mk, dosen, sks, warna_label) VALUES
(1, 'INF60285', 'Aplikasi Web',         'Ir. Susilo Romadhon, S.Kom., M.Cs.', 3, '#4F46E5'),
(2, 'INF60120', 'Etika Profesi',        'Dr. Hendra Prasetyo, M.Kom.',         2, '#0D9488'),
(3, 'INF60101', 'Basis Data',           'Dra. Siti Rahayu, M.T.',              3, '#D97706'),
(4, 'INF60202', 'Jaringan Komputer',    'Ir. Bambang Sulistyo, M.T.',          3, '#E11D48'),
(5, 'INF60311', 'Pemrograman Mobile',   'Drs. Agus Widodo, M.Cs.',             3, '#0EA5E9');

-- 12 Tugas: 5 Belum, 4 Dalam Proses, 3 Selesai
-- Tugas wajib:
--   - "Laporan Praktikum Web" => deadline NOW()+6jam, Dalam Proses, Tinggi
--   - "Makalah Etika Profesi" => deadline NOW()+20jam, Belum
--   - Satu tugas sudah lewat 2 jam, belum Selesai (terlambat)
-- Sisanya +2 s.d. +14 hari

INSERT INTO tugas (id_tugas, id_mk, judul, deskripsi, deadline, prioritas, status, selesai_pada) VALUES
-- ID 1: Dalam Proses, Tinggi, deadline +6 jam (URGENT)
(1,  1, 'Laporan Praktikum Web',
     'Buat laporan praktikum modul REST API dengan dokumentasi endpoint.',
     DATE_ADD(NOW(), INTERVAL 6 HOUR), 'Tinggi', 'Dalam Proses', NULL),

-- ID 2: Belum, Sedang, deadline +20 jam (URGENT)
(2,  2, 'Makalah Etika Profesi',
     'Tulis makalah tentang etika penggunaan AI dalam dunia kerja, minimal 10 halaman.',
     DATE_ADD(NOW(), INTERVAL 20 HOUR), 'Sedang', 'Belum', NULL),

-- ID 3: Belum, Tinggi, deadline sudah LEWAT 2 jam (Terlambat, masih urgent)
(3,  3, 'Quiz Basis Data Bab 4',
     'Kerjakan soal-soal quiz tentang normalisasi dan ERD di e-learning.',
     DATE_SUB(NOW(), INTERVAL 2 HOUR), 'Tinggi', 'Belum', NULL),

-- ID 4: Dalam Proses, Sedang, deadline +2 hari
(4,  4, 'Ringkasan Jurnal Jaringan',
     'Buat ringkasan dari jurnal IEEE tentang SDN (Software Defined Networking).',
     DATE_ADD(NOW(), INTERVAL 2 DAY), 'Sedang', 'Dalam Proses', NULL),

-- ID 5: Belum, Rendah, deadline +3 hari
(5,  5, 'Tugas UI Flutter Pertama',
     'Buat aplikasi Flutter sederhana dengan bottom navigation dan 3 halaman.',
     DATE_ADD(NOW(), INTERVAL 3 DAY), 'Rendah', 'Belum', NULL),

-- ID 6: Dalam Proses, Tinggi, deadline +4 hari
(6,  1, 'Proyek Mini Web CRUD',
     'Bangun aplikasi web CRUD sederhana menggunakan PHP native dan MySQL.',
     DATE_ADD(NOW(), INTERVAL 4 DAY), 'Tinggi', 'Dalam Proses', NULL),

-- ID 7: Belum, Sedang, deadline +5 hari
(7,  2, 'Presentasi Kode Etik IT',
     'Siapkan slide presentasi tentang kode etik profesi IT berdasarkan ACM Code of Ethics.',
     DATE_ADD(NOW(), INTERVAL 5 DAY), 'Sedang', 'Belum', NULL),

-- ID 8: Dalam Proses, Rendah, deadline +7 hari
(8,  3, 'ER Diagram Studi Kasus',
     'Buat ER Diagram untuk studi kasus sistem perpustakaan menggunakan draw.io.',
     DATE_ADD(NOW(), INTERVAL 7 DAY), 'Rendah', 'Dalam Proses', NULL),

-- ID 9: Belum, Tinggi, deadline +10 hari
(9,  4, 'Konfigurasi VLAN Packet Tracer',
     'Lakukan konfigurasi VLAN dan inter-VLAN routing pada simulator Cisco Packet Tracer.',
     DATE_ADD(NOW(), INTERVAL 10 DAY), 'Tinggi', 'Belum', NULL),

-- ID 10: Selesai, Tinggi, deadline +14 hari
(10, 5, 'Tugas Pemrograman Mobile',
     'Implementasikan fitur autentikasi menggunakan Firebase Auth pada aplikasi Flutter.',
     DATE_ADD(NOW(), INTERVAL 14 DAY), 'Tinggi', 'Selesai', NOW()),

-- ID 11: Selesai, Sedang, deadline +12 hari
(11, 1, 'Dokumentasi API Aplikasi Web',
     'Buat dokumentasi API menggunakan Swagger/OpenAPI untuk proyek akhir.',
     DATE_ADD(NOW(), INTERVAL 12 DAY), 'Sedang', 'Selesai', DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- ID 12: Selesai, Rendah, deadline +8 hari
(12, 2, 'Refleksi Etika Digital',
     'Tulis refleksi pribadi tentang dampak teknologi digital terhadap etika profesional.',
     DATE_ADD(NOW(), INTERVAL 8 DAY), 'Rendah', 'Selesai', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Log Status Tugas
-- Tugas Dalam Proses: 1 log (Belum -> Dalam Proses)
INSERT INTO log_status_tugas (id_tugas, status_lama, status_baru, waktu_perubahan) VALUES
(1,  'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4,  'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6,  'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(8,  'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 12 HOUR)),

-- Tugas Selesai: 2 log (Belum -> Dalam Proses -> Selesai)
(10, 'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(10, 'Dalam Proses', 'Selesai', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(11, 'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(11, 'Dalam Proses', 'Selesai', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(12, 'Belum', 'Dalam Proses', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(12, 'Dalam Proses', 'Selesai', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Tugas yang tidak pernah berganti status TIDAK punya log (ID 2,3,5,7,9)
