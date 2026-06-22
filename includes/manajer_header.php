<?php
/**
 * Variabel yang harus diset sebelum include:
 * $pageTitle   - judul tab browser
 * $pageHeading - judul halaman (h1)
 * $pageSubtitle - subjudul halaman
 * $activeMenu  - dashboard | verifikasi | target | karyawan | histori
 */
$menus = [
    'dashboard'  => ['href' => 'dashboard_manajer.php', 'icon' => 'bi-grid-1x2-fill',  'label' => 'Dashboard'],
    'verifikasi' => ['href' => 'verifikasi.php',        'icon' => 'bi-shield-check',   'label' => 'Verifikasi'],
    'target'     => ['href' => 'input_target.php',      'icon' => 'bi-bullseye',       'label' => 'Target Kerja'],
    'karyawan'   => ['href' => 'data_karyawan.php',     'icon' => 'bi-people-fill',    'label' => 'Data Karyawan'],
    'histori'    => ['href' => 'laporan_histori.php',   'icon' => 'bi-clock-history',  'label' => 'Laporan Histori'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISPKO | <?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/manajer.css">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-3">
            <div class="logo-icon"><i class="bi bi-leaf-fill"></i></div>
            <div>
                <span>SISPKO</span>
                <small>PT Agrotamex Sumindo Abadi</small>
            </div>
        </div>
    </div>

    <nav class="nav flex-column">
        <?php foreach ($menus as $key => $menu): ?>
        <a class="nav-link <?= ($activeMenu === $key) ? 'active' : '' ?>" href="<?= $menu['href'] ?>">
            <i class="bi <?= $menu['icon'] ?>"></i> <?= $menu['label'] ?>
            <?php if ($key === 'verifikasi' && !empty($jml_pending_verifikasi)): ?>
                <span class="nav-badge"><?= $jml_pending_verifikasi ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left"></i> Keluar Sistem</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div>
            <h1><?= htmlspecialchars($pageHeading) ?></h1>
            <p><?= htmlspecialchars($pageSubtitle) ?></p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <?php if (!empty($topBarExtra)) echo $topBarExtra; ?>
            <div class="user-chip">
                <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
                <div>
                    <div class="name"><?= htmlspecialchars($nama_manajer) ?></div>
                    <div class="role">Manajer Operasional</div>
                </div>
            </div>
        </div>
    </div>
