<?php
/**
 * Variabel yang harus diset sebelum include:
 * $pageTitle, $pageHeading, $pageSubtitle, $activeMenu
 * $activeMenu: dashboard | input | kinerja
 */
$menus = [
    'dashboard' => ['href' => 'dashboard_karyawan.php', 'icon' => 'bi-grid-1x2-fill',           'label' => 'Dashboard'],
    'input'     => ['href' => 'form_input_kerja.php',   'icon' => 'bi-plus-circle-fill',        'label' => 'Input Hasil Lapangan'],
    'kinerja'   => ['href' => 'catatan_kinerja.php',    'icon' => 'bi-file-earmark-bar-graph',  'label' => 'Catatan Kinerja'],
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
                    <div class="name"><?= htmlspecialchars($nama_karyawan) ?></div>
                    <div class="role">Karyawan Lapangan</div>
                </div>
            </div>
        </div>
    </div>
