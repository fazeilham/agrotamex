<?php
include 'includes/karyawan_init.php';
include_once 'includes/target_helper.php';

$target_hari_ini  = getTargetAktif($koneksi, date('Y-m-d'));
$tanggal_hari_ini = date('Y-m-d');
$q_hari_ini = mysqli_query($koneksi, "SELECT * FROM aktivitas WHERE id_user='$id_user' AND tanggal='$tanggal_hari_ini' ORDER BY id_aktivitas DESC LIMIT 1");
$data_hari_ini = mysqli_fetch_assoc($q_hari_ini);

$q_status = mysqli_query($koneksi, "SELECT 
            SUM(CASE WHEN status_verifikasi='Pending' THEN 1 ELSE 0 END) as jml_pending,
            SUM(CASE WHEN status_verifikasi='Disetujui' THEN 1 ELSE 0 END) as jml_setuju
            FROM aktivitas WHERE id_user='$id_user'");
$data_status = mysqli_fetch_assoc($q_status);

$pageTitle    = 'Dashboard Karyawan';
$pageHeading  = 'Dashboard Saya';
$pageSubtitle = 'Pantau target harian dan status laporan kerja Anda';
$activeMenu   = 'dashboard';

$topBarExtra = '<a href="form_input_kerja.php" class="btn-welcome"><i class="bi bi-plus-lg"></i> Lapor Kerja</a>';

include 'includes/karyawan_header.php';
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
<div class="alert-manajer alert-success mb-4">
    <i class="bi bi-check-circle-fill"></i> Laporan hasil kerja berhasil dikirim! Menunggu verifikasi manajer.
</div>
<?php endif; ?>

<div class="welcome-panel d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h3>Selamat Datang, <?= htmlspecialchars($nama_karyawan) ?>!</h3>
        <p>Sistem Pemantauan Produktivitas Karyawan Operasional</p>
    </div>
    <a href="form_input_kerja.php" class="btn-welcome"><i class="bi bi-plus-lg"></i> Lapor Kerja Hari Ini</a>
</div>

<div class="target-banner">
    <div>
        <h6 class="fw-bold mb-1"><i class="bi bi-bullseye text-success"></i> Target Kerja Hari Ini</h6>
        <p class="mb-0 text-muted small">Pekerjaan: <strong class="text-dark"><?= htmlspecialchars($target_hari_ini['jenis_pekerjaan']) ?></strong></p>
    </div>
    <div class="text-end">
        <div class="target-num"><?= htmlspecialchars($target_hari_ini['target_ton']) ?></div>
        <span class="text-muted fw-semibold"><?= htmlspecialchars($target_hari_ini['satuan']) ?></span>
    </div>
</div>

<div class="content-grid-karyawan">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="bi bi-calendar-check text-success"></i> Status Aktivitas Hari Ini</h5>
        </div>
        <div class="panel-body">
            <?php if ($data_hari_ini):
                $satuan_hari_ini = getSatuanAktivitas($data_hari_ini);
                $persen_hari_ini = ($data_hari_ini['target_kerja'] > 0) ? ($data_hari_ini['hasil_kerja'] / $data_hari_ini['target_kerja']) * 100 : 0;
                $badge_class = ($data_hari_ini['status_verifikasi'] == 'Disetujui') ? 'approved' : (($data_hari_ini['status_verifikasi'] == 'Ditolak') ? 'rejected' : 'pending');
            ?>
            <div class="mb-3">
                <span class="job-badge"><?= htmlspecialchars($data_hari_ini['jenis_pekerjaan']) ?></span>
            </div>

            <div class="verify-stats mb-3">
                <div class="verify-stat">
                    <small>Hasil Capaian</small>
                    <strong class="text-success"><?= formatHasil($data_hari_ini['hasil_kerja'], $satuan_hari_ini) ?></strong>
                </div>
                <div class="verify-stat">
                    <small>Target</small>
                    <strong><?= formatHasil($data_hari_ini['target_kerja'], $satuan_hari_ini) ?></strong>
                </div>
                <div class="verify-stat">
                    <small>Capaian</small>
                    <strong class="text-success"><?= number_format($persen_hari_ini, 1) ?>%</strong>
                </div>
            </div>

            <div class="verify-progress mb-3">
                <div class="d-flex justify-content-between small fw-bold mb-1">
                    <span>Ketercapaian Target</span>
                    <span class="text-success"><?= number_format($persen_hari_ini, 1) ?>%</span>
                </div>
                <div class="progress" style="height:10px;">
                    <div class="progress-bar bg-success" style="width:<?= min(100, $persen_hari_ini) ?>%"></div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Status Verifikasi:</span>
                <span class="status-badge <?= $badge_class ?>"><?= htmlspecialchars($data_hari_ini['status_verifikasi']) ?></span>
            </div>
            <?php else: ?>
            <div class="empty-state py-4">
                <i class="bi bi-exclamation-triangle"></i>
                <p>Belum ada laporan hari ini</p>
                <small>Silakan kirim laporan aktivitas lapangan Anda</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="bi bi-folder-symlink text-primary"></i> Ringkasan Berkas</h5>
        </div>
        <div class="panel-body d-flex flex-column">
            <p class="small text-muted mb-3">Status akumulasi seluruh laporan harian yang telah dikirim.</p>

            <div class="summary-row warning">
                <div class="label">Menunggu Diperiksa</div>
                <div class="value text-dark"><?= $data_status['jml_pending'] ?? 0 ?></div>
            </div>
            <div class="summary-row success">
                <div class="label">Telah Disetujui</div>
                <div class="value text-success"><?= $data_status['jml_setuju'] ?? 0 ?></div>
            </div>

            <a href="catatan_kinerja.php" class="btn-outline-manajer text-center mt-3">
                Lihat Grafik & Histori <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/karyawan_footer.php'; ?>
