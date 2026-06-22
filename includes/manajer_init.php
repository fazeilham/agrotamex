<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($koneksi)) {
    include __DIR__ . '/../koneksi.php';
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'manajer') {
    header('location:login.php');
    exit();
}

$nama_manajer = $_SESSION['nama'] ?? 'Manajer';
$initials = strtoupper(substr($nama_manajer, 0, 1));
if (strpos($nama_manajer, ' ') !== false) {
    $parts = explode(' ', $nama_manajer);
    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}

$jml_pending_verifikasi = 0;
$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE status_verifikasi='Pending'");
if ($q_pending) {
    $jml_pending_verifikasi = (int) (mysqli_fetch_assoc($q_pending)['total'] ?? 0);
}
