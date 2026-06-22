<?php
session_start();
header('Content-Type: application/json');
include 'koneksi.php';
include_once 'includes/target_helper.php';

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$target  = getTargetAktif($koneksi, $tanggal);
$info    = getInfoPekerjaan($target['jenis_pekerjaan']);

echo json_encode([
    'nilai'       => (string) $target['target_ton'],
    'ton'         => (string) $target['target_ton'],
    'jenis'       => $target['jenis_pekerjaan'],
    'satuan'      => $target['satuan'],
    'step'        => $info['step'],
    'placeholder' => $info['placeholder'],
    'tanggal'     => $tanggal,
]);