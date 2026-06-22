<?php

function setupDatabase($koneksi) {
    mysqli_query($koneksi, "
        CREATE TABLE IF NOT EXISTS users (
            id_user INT AUTO_INCREMENT PRIMARY KEY,
            nama_lengkap VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('manajer','karyawan') NOT NULL DEFAULT 'karyawan',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    mysqli_query($koneksi, "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    include_once __DIR__ . '/target_helper.php';
    ensureTargetTable($koneksi);
    ensureAktivitasSatuan($koneksi);

    $cek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users");
    $row = mysqli_fetch_assoc($cek);
    if (($row['total'] ?? 0) == 0) {
        mysqli_query($koneksi, "INSERT INTO users (nama_lengkap, username, password, role) VALUES
            ('Manajer Operasional', 'manajer', 'manajer123', 'manajer'),
            ('Budi Santoso', 'karyawan1', 'karyawan123', 'karyawan'),
            ('Andi Wijaya', 'karyawan2', 'karyawan123', 'karyawan')
        ");
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
}
