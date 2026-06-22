<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($koneksi)) {
    include __DIR__ . '/../koneksi.php';
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'karyawan') {
    header('location:login.php');
    exit();
}

$id_user       = $_SESSION['id_user'];
$nama_karyawan = $_SESSION['nama'] ?? 'Karyawan';
$initials      = strtoupper(substr($nama_karyawan, 0, 1));
if (strpos($nama_karyawan, ' ') !== false) {
    $parts = explode(' ', $nama_karyawan);
    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}
