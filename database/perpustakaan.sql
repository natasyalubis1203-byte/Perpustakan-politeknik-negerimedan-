-- --------------------------------------------------
-- Database: perpustakaan_pnm
-- Desain untuk aplikasi Perpustakaan Politeknik Negeri Medan
-- --------------------------------------------------

CREATE DATABASE IF NOT EXISTS perpustakaan_pnm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE perpustakaan_pnm;


CREATE TABLE anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL
);


-- --------------------------------------------------
-- Tabel Users
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- Tabel Books
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  penulis VARCHAR(120) NOT NULL,
  kategori VARCHAR(80) NOT NULL,
  tahun INT NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  cover VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- Tabel Categories (opsional, untuk filter dinamis)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(80) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS book_category (
  book_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (book_id, category_id),
  CONSTRAINT fk_bc_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  CONSTRAINT fk_bc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Tabel Loans (peminjaman)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_jatuh_tempo DATE NOT NULL,
  status ENUM('menunggu', 'disetujui', 'ditolak', 'selesai', 'terlambat') DEFAULT 'menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_loans_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Tabel Loan Items (detail buku per peminjaman)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS loan_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loan_id INT NOT NULL,
  book_id INT NOT NULL,
  tanggal_kembali DATE,
  denda INT DEFAULT 0,
  status ENUM('dipinjam', 'kembali', 'hilang') DEFAULT 'dipinjam',
  CONSTRAINT fk_items_loan FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT
);

-- --------------------------------------------------
-- Tabel Returns (pengembalian fisik)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS returns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loan_item_id INT NOT NULL,
  tanggal_kembali DATE NOT NULL,
  kondisi ENUM('baik','rusak','hilang') DEFAULT 'baik',
  denda INT DEFAULT 0,
  catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_returns_item FOREIGN KEY (loan_item_id) REFERENCES loan_items(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Tabel Login Requests (permohonan nomor login)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS login_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  nim VARCHAR(20) NOT NULL,
  email VARCHAR(120) NOT NULL,
  prodi VARCHAR(80) NOT NULL,
  alasan TEXT NOT NULL,
  login_number VARCHAR(30) DEFAULT NULL,
  status ENUM('pending', 'diterima', 'ditolak') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_nim_email (nim, email)
);

-- --------------------------------------------------
-- Tabel Loan Requests (permintaan peminjaman)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS loan_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  login_number VARCHAR(30) NOT NULL,
  status ENUM('pending', 'disetujui', 'ditolak', 'dikembalikan') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lr_book FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Tabel Fines (riwayat denda)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS fines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  loan_item_id INT NOT NULL,
  nominal INT NOT NULL DEFAULT 0,
  status ENUM('belum_dibayar','dibayar') DEFAULT 'belum_dibayar',
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid_at TIMESTAMP NULL,
  CONSTRAINT fk_fines_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_fines_item FOREIGN KEY (loan_item_id) REFERENCES loan_items(id) ON DELETE CASCADE
);

-- --------------------------------------------------
-- View sederhana untuk laporan bulanan
-- --------------------------------------------------
CREATE OR REPLACE VIEW view_laporan_bulanan AS
SELECT
  DATE_FORMAT(tanggal_pinjam, '%Y-%m') AS periode,
  COUNT(DISTINCT loans.id) AS total_peminjaman,
  COUNT(DISTINCT user_id) AS anggota_aktif
FROM loans
GROUP BY DATE_FORMAT(tanggal_pinjam, '%Y-%m');

-- --------------------------------------------------
-- Contoh data awal (opsional)
-- --------------------------------------------------
INSERT INTO users (nama, username, password, role)
VALUES
  ('Administrator', 'admin', '$2y$10$hashpassworddummy', 'admin')
ON DUPLICATE KEY UPDATE username = username;

