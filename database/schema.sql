-- TugasKu - Schema SQL
-- Mata Kuliah Aplikasi Web (INF60285), Rombel K, UNY, Kelompok 02
-- Timezone: Asia/Jakarta (WIB)

CREATE DATABASE IF NOT EXISTS tugasku CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tugasku;

CREATE TABLE IF NOT EXISTS mata_kuliah (
  id_mk       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  kode_mk     VARCHAR(20)  NULL UNIQUE,
  nama_mk     VARCHAR(100) NOT NULL,
  dosen       VARCHAR(100) NULL,
  sks         TINYINT UNSIGNED NULL,
  warna_label VARCHAR(7)   NULL,                 -- heksadesimal, mis. #4F46E5
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tugas (
  id_tugas     INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_mk        INT NOT NULL,
  judul        VARCHAR(150) NOT NULL,
  deskripsi    TEXT NULL,                          -- label UI: "Catatan"
  deadline     DATETIME NOT NULL,
  prioritas    ENUM('Rendah','Sedang','Tinggi') NOT NULL DEFAULT 'Sedang',
  status       ENUM('Belum','Dalam Proses','Selesai') NOT NULL DEFAULT 'Belum',
  selesai_pada DATETIME NULL DEFAULT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tugas_mk FOREIGN KEY (id_mk) REFERENCES mata_kuliah (id_mk) ON DELETE RESTRICT,
  CONSTRAINT ck_tugas_selesai CHECK (
    (status = 'Selesai' AND selesai_pada IS NOT NULL) OR (status <> 'Selesai' AND selesai_pada IS NULL)),
  INDEX idx_tugas_deadline (deadline)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS log_status_tugas (
  id_log          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_tugas        INT NOT NULL,
  status_lama     ENUM('Belum','Dalam Proses','Selesai') NOT NULL,
  status_baru     ENUM('Belum','Dalam Proses','Selesai') NOT NULL,
  waktu_perubahan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_tugas FOREIGN KEY (id_tugas) REFERENCES tugas (id_tugas) ON DELETE CASCADE,
  INDEX idx_log_tugas (id_tugas, waktu_perubahan)
) ENGINE=InnoDB;
