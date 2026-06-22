-- SISPKO Database Schema
-- PT Agrotamex Sumindo Abadi

CREATE DATABASE IF NOT EXISTS db_produktivitas CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_produktivitas;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('manajer','karyawan') NOT NULL DEFAULT 'karyawan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS aktivitas (
    id_aktivitas INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    tanggal DATE NOT NULL,
    jenis_pekerjaan VARCHAR(100) NOT NULL,
    hasil_kerja DECIMAL(10,2) NOT NULL,
    target_kerja DECIMAL(10,2) NOT NULL,
    satuan VARCHAR(20) NULL,
    foto_bukti VARCHAR(255) NOT NULL,
    status_verifikasi ENUM('Pending','Disetujui','Ditolak') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_tanggal (id_user, tanggal),
    INDEX idx_status (status_verifikasi)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS target_harian (
    id_target INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL UNIQUE,
    target_ton DECIMAL(10,2) NOT NULL DEFAULT 35,
    jenis_pekerjaan VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL DEFAULT 'Ton',
    diupdate_oleh INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Akun demo (password plain text sesuai implementasi saat ini)
INSERT IGNORE INTO users (nama_lengkap, username, password, role) VALUES
('Manajer Operasional', 'manajer', 'manajer123', 'manajer'),
('Budi Santoso', 'karyawan1', 'karyawan123', 'karyawan'),
('Andi Wijaya', 'karyawan2', 'karyawan123', 'karyawan');
