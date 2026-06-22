<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_produktivitas";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("<div class='alert alert-danger'>Koneksi database gagal: " . mysqli_connect_error() . "</div>");
}

require_once __DIR__ . '/includes/db_setup.php';
setupDatabase($koneksi);
?>