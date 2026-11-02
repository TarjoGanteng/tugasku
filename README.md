# TugasKu — Sistem Manajemen Tugas Mahasiswa

Aplikasi web responsif lokal untuk manajemen tugas kuliah mahasiswa, dibangun dengan **PHP 8.1+ Native**, **MySQL / MariaDB**, **Vanilla JavaScript (ES Modules)**, dan **CSS Modern** tanpa framework CSS maupun bundler.

> Proyek Mata Kuliah: **Aplikasi Web (INF60285)**  
> Kelas: **Rombel K**  
> Institusi: **Universitas Negeri Yogyakarta**  
> Kelompok: **02**  
> Pengguna: **Single User "Mahasiswa" tanpa Login (DEC-01)**  
> Zona Waktu: **WIB (Asia/Jakarta / +07:00) (A06)**

---

## 1. Fitur Utama & Pemenuhan Kebutuhan

| ID Kebutuhan | Keterangan Implementasi |
|---|---|
| **FR-01** | Tambah tugas melalui modal: Judul*, Mata Kuliah*, Deadline*, Prioritas (Rendah/Sedang/Tinggi), Catatan (opsional). Tersimpan tanpa reload via AJAX. |
| **FR-02** | Daftar tugas otomatis terurut berdasarkan deadline terdekat; tugas berstatus `Selesai` otomatis berada di bagian paling bawah. |
| **FR-03** | **Urgent Badge** otomatis muncul (merah tua kapital `URGENT` + ikon) untuk tugas yang belum `Selesai` dan sisa waktu kurang dari 24 jam. |
| **FR-04** | Ubah status cepat (`Belum`, `Dalam Proses`, `Selesai`) via dropdown atau segmented button; respon instan dengan optimisme UI dan pembaruan server < 2 detik. |
| **FR-05** | Dashboard ringkasan: jumlah kartu per status (`Total`, `Belum`, `Dalam Proses`, `Selesai`) serta progress bar persentase penyelesaian tugas. |
| **FR-06** | Halaman detail tugas (`tugas.php?id=`): informasi lengkap mata kuliah (dosen & SKS), catatan tugas, deadline countdown timer real-time, dan riwayat kronologis perubahan status dari tabel `log_status_tugas`. |
| **FR-07** | Hapus tugas wajib melalui modal konfirmasi permanen dengan tombol "Batal" dan "Hapus" merah destruktif, menghapus rekursif riwayat status via `ON DELETE CASCADE`. |
| **FR-08** | Halaman Cari & Filter (`cari.php`): pencarian real-time debounce 300ms, filter mata kuliah dan status, chip filter aktif yang dapat dihapus, empty state dengan tombol Reset Filter, dan sinkronisasi URL state (`history.replaceState`). |
| **NFR-01** | Tampilan ultra-responsif: layout tabel pada desktop (≥ 1024px) dan layout kartu pada mobile (< 1024px). Bebas horizontal scrollbar pada lebar 360px. |
| **NFR-02** | Waktu respon API dan pembaruan status < 2 detik; timeout fetch 10 detik. |
| **NFR-03** | Konsistensi UI: Inter font, Lucide inline SVG, token warna status (`#DC2626` Belum, `#F5B301` Dalam Proses, `#16A34A` Selesai). |
| **NFR-04** | Kompatibel penuh dengan browser modern: Google Chrome, Microsoft Edge, Mozilla Firefox, dan Apple Safari. |
| **NFR-05** | Keamanan validasi ganda (klien & server), sanitasi masukan, proteksi prepared statement PDO MySQL, serta penanganan XSS via `textContent` & escaping. |

---

## 2. Struktur Direktori

```
tugasku/
├── config/
│   └── config.php          # Konfigurasi database default (bisa ditimpa config.local.php)
├── database/
│   ├── schema.sql          # Skema 3 tabel: mata_kuliah, tugas, log_status_tugas
│   └── seed.sql            # Seed 5 mata kuliah & 12 tugas relatif terhadap NOW()
├── docs/
│   └── design/             # Gambar acuan desain UI & wireframe
├── public/
│   ├── api/
│   │   └── index.php       # Front controller API RESTful (/api/tugas, /api/ringkasan, /api/matakuliah)
│   ├── assets/
│   │   ├── css/
│   │   │   └── app.css     # CSS Custom Properties, reset, layout shell, badges, modal, toast
│   │   └── js/
│   │       ├── api.js      # Centralized API fetch wrapper dengan timeout & error normalization
│   │       ├── toast.js    # Notifikasi toast berbasis accessible DOM
│   │       └── ui.js       # Formatters, DOM safe helpers, modal focus trap, quick status
│   ├── cari.php            # Halaman Pencarian & Filter (P06)
│   ├── index.php           # Halaman Dashboard Utama (P01)
│   ├── layout.php          # Shared PHP components (topbar, sidebar, mobile nav)
│   ├── matakuliah.php      # Halaman Manajemen Mata Kuliah (CRUD)
│   ├── router.php          # Router untuk PHP Built-in Server
│   ├── tugas.php           # Halaman Detail Tugas & Riwayat (P04)
│   └── .htaccess           # Apache mod_rewrite rules untuk XAMPP
├── src/
│   ├── Db.php              # Singleton koneksi PDO MySQL dengan set timezone Asia/Jakarta (+07:00)
│   ├── MataKuliahRepository.php # Operasi database mata kuliah & proteksi relasi
│   ├── Response.php        # JSON Response helper seragam & error handling
│   ├── StatusFlow.php      # State machine & transaksi database status + logging
│   ├── TugasRepository.php # Query JOIN tugas, filtering, sorting, & komputasi sisa_detik/is_urgent
│   └── Validator.php       # Validasi masukan server-side & error mapping
├── tests/
│   ├── smoke.sh            # API smoke test suite (Bash / cURL)
│   └── smoke.ps1           # API smoke test suite (PowerShell)
└── README.md
```

---

## 3. Persyaratan Sistem

- **PHP**: Versi 8.1 atau lebih baru (ekstensi `pdo_mysql` aktif).
- **MySQL / MariaDB**: Versi 8.0+ atau MariaDB 10.4+ (tersedia bawaan di XAMPP).
- **Web Server**: Apache (XAMPP) atau PHP Built-in Web Server.

---

## 4. Cara Menjalankan

### Opsi A: Menggunakan PHP Built-in Server (Direkomendasikan & Praktis)

1. **Import Database**:
   Buka MySQL client (misal: phpMyAdmin atau terminal MySQL), lalu eksekusi:
   ```sql
   SOURCE database/schema.sql;
   SOURCE database/seed.sql;
   ```
   Atau via baris perintah:
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p tugasku < database/seed.sql
   ```

2. **Konfigurasi Database** (Jika MySQL memakai password):
   Buat file `config/config.local.php` (file ini otomatis di-ignore oleh git):
   ```php
   <?php
   return [
       'db_host' => '127.0.0.1',
       'db_name' => 'tugasku',
       'db_user' => 'root',
       'db_pass' => 'password_kamu',
       'db_port' => 3306,
   ];
   ```

3. **Jalankan Web Server**:
   Dari root direktori `tugasku/`, jalankan:
   ```bash
   php -S localhost:8000 -t public public/router.php
   ```

4. **Akses Aplikasi**:
   Buka peramban (browser) di: [http://localhost:8000](http://localhost:8000)

---

### Opsi B: Menggunakan XAMPP

1. Salin seluruh folder `tugasku` ke dalam direktori `htdocs/` XAMPP Anda (`C:\xampp\htdocs\tugasku`).
2. Nyalakan modul **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka phpMyAdmin di [http://localhost/phpmyadmin](http://localhost/phpmyadmin):
   - Buat database `tugasku`.
   - Import `database/schema.sql`, kemudian import `database/seed.sql`.
4. Buka browser di [http://localhost/tugasku/public/](http://localhost/tugasku/public/).

---

## 5. Pengujian Otomatis (Smoke Test)

Pastikan server telah berjalan pada port 8000, lalu jalankan script uji:

**Melalui PowerShell (Windows):**
```powershell
powershell -ExecutionPolicy Bypass -File tests/smoke.ps1
```

**Melalui Bash / Git Bash / Linux / macOS:**
```bash
bash tests/smoke.sh
```

Pengujian memverifikasi 10 tahapan vital:
1. Endpoint `GET /api/ringkasan` menghasilkan kalkulasi akurat.
2. Endpoint `GET /api/tugas` mengembalikan daftar tugas terurut.
3. Pembuatan Mata Kuliah baru `POST /api/matakuliah`.
4. Pembuatan Tugas baru `POST /api/tugas` dengan validasi masa depan.
5. Transisi status `PATCH /api/tugas/{id}/status` ke `Dalam Proses`.
6. Transisi status `PATCH /api/tugas/{id}/status` ke `Selesai` beserta pencatatan `selesai_pada`.
7. Riwayat log perubahan tercatat utuh di `log_status_tugas`.
8. Proteksi penghapusan Mata Kuliah yang masih memiliki tugas aktif (`MK_MASIH_DIPAKAI`).
9. Penghapusan tugas `DELETE /api/tugas/{id}` dengan konfirmasi.
10. Penghapusan Mata Kuliah yang telah bebas tugas.

---

## 6. Keputusan Implementasi & Resolusi Konflik (§2 & §12)

1. **Nama Produk & Brand**:
   - Diputuskan menggunakan nama **TugasKu** dengan logo kotak indigo membulat berisi ikon centang putih, mengacu pada brief teks spesifikasi.
2. **Status Tugas**:
   - Database dan UI dibatasi tegas pada 3 nilai enum: `Belum`, `Dalam Proses`, dan `Selesai`.
   - "Urgent" bukan merupakan status database, melainkan computed badge (indikator turunan) yang aktif saat tugas berstatus selain `Selesai` dan selisih sisa deadline kurang dari 24 jam.
3. **Penyajian Daftar Tugas**:
   - Layar Desktop (≥ 1024px): Menampilkan representasi **Tabel** visual modern (Judul + Urgent Badge, Mata Kuliah, Deadline, Status, Aksi).
   - Layar Mobile & Tablet (< 1024px): Beralih otomatis ke representasi **Kartu** (Card view) yang nyaman disentuh tanpa scroll horizontal.
4. **Keamanan Transaksi & Concurrency**:
   - Pergantian status tugas menggunakan transaksi database MySQL dengan penguncian baris `SELECT ... FOR UPDATE` dalam `StatusFlow::transition()` untuk memastikan konsistensi antara tabel `tugas` dan pencatatan riwayat di `log_status_tugas`.
5. **Zona Waktu**:
   - Seluruh kalkulasi waktu dan perbandingan deadline menggunakan waktu WIB (`Asia/Jakarta` / `+07:00`) pada PHP maupun PDO session.
6. **Proteksi XSS**:
   - Seluruh data dinamis dari pengguna dirender ke DOM menggunakan `element.textContent` atau text node, menghindari celah keamanan injeksi HTML.

