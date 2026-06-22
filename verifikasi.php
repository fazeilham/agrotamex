<?php
include 'includes/manajer_init.php';
include_once 'includes/target_helper.php';

// Proses verifikasi laporan
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $cek = mysqli_query($koneksi, "SELECT id_aktivitas FROM aktivitas WHERE id_aktivitas='$id' AND status_verifikasi='Pending'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        if ($_GET['aksi'] === 'setuju') {
            mysqli_query($koneksi, "UPDATE aktivitas SET status_verifikasi='Disetujui' WHERE id_aktivitas='$id'");
            header('location:verifikasi.php?msg=setuju');
            exit();
        }
        if ($_GET['aksi'] === 'tolak') {
            mysqli_query($koneksi, "UPDATE aktivitas SET status_verifikasi='Ditolak' WHERE id_aktivitas='$id'");
            header('location:verifikasi.php?msg=tolak');
            exit();
        }
    }
    header('location:verifikasi.php?msg=error');
    exit();
}

$filter = ($_GET['filter'] ?? 'pending') === 'selesai' ? 'selesai' : 'pending';

if ($filter === 'pending') {
    $query = mysqli_query($koneksi, "
        SELECT aktivitas.*, users.nama_lengkap, users.username
        FROM aktivitas
        JOIN users ON aktivitas.id_user = users.id_user
        WHERE aktivitas.status_verifikasi = 'Pending'
        ORDER BY aktivitas.tanggal ASC, aktivitas.id_aktivitas ASC
    ");
} else {
    $query = mysqli_query($koneksi, "
        SELECT aktivitas.*, users.nama_lengkap, users.username
        FROM aktivitas
        JOIN users ON aktivitas.id_user = users.id_user
        WHERE aktivitas.status_verifikasi IN ('Disetujui', 'Ditolak')
        ORDER BY aktivitas.tanggal DESC, aktivitas.id_aktivitas DESC
        LIMIT 20
    ");
}

$jml_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE status_verifikasi='Pending'"))['total'] ?? 0;

$pageTitle    = 'Verifikasi Laporan';
$pageHeading  = 'Verifikasi Laporan Karyawan';
$pageSubtitle = 'Tinjau dan setujui laporan hasil kerja karyawan lapangan';
$activeMenu   = 'verifikasi';

$topBarExtra = '<span class="stat-pill"><i class="bi bi-hourglass-split"></i> ' . $jml_pending . ' menunggu</span>';

include 'includes/manajer_header.php';
?>

<?php if (isset($_GET['msg'])): ?>
<div class="alert-manajer alert-<?= $_GET['msg'] === 'setuju' ? 'success' : ($_GET['msg'] === 'tolak' ? 'warning' : 'danger') ?> mb-4">
    <?php if ($_GET['msg'] === 'setuju'): ?>
        <i class="bi bi-check-circle-fill"></i> Laporan berhasil disetujui.
    <?php elseif ($_GET['msg'] === 'tolak'): ?>
        <i class="bi bi-x-circle-fill"></i> Laporan ditolak. Karyawan dapat mengirim ulang.
    <?php else: ?>
        <i class="bi bi-exclamation-circle-fill"></i> Laporan tidak ditemukan atau sudah diproses.
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="filter-tabs mb-4">
    <a href="verifikasi.php?filter=pending" class="filter-tab <?= $filter === 'pending' ? 'active' : '' ?>">
        <i class="bi bi-inbox-fill"></i> Menunggu Verifikasi
        <?php if ($jml_pending > 0): ?><span class="tab-badge"><?= $jml_pending ?></span><?php endif; ?>
    </a>
    <a href="verifikasi.php?filter=selesai" class="filter-tab <?= $filter === 'selesai' ? 'active' : '' ?>">
        <i class="bi bi-check2-all"></i> Sudah Diproses
    </a>
</div>

<?php if (!$query || mysqli_num_rows($query) === 0): ?>
<div class="panel">
    <div class="empty-state">
        <i class="bi bi-<?= $filter === 'pending' ? 'check2-all' : 'archive' ?>"></i>
        <p><?= $filter === 'pending' ? 'Tidak ada laporan menunggu verifikasi' : 'Belum ada laporan yang diproses' ?></p>
        <small><?= $filter === 'pending' ? 'Semua laporan karyawan sudah ditinjau' : 'Laporan yang disetujui/ditolak akan muncul di sini' ?></small>
    </div>
</div>
<?php else: ?>
<div class="verify-grid">
    <?php while ($row = mysqli_fetch_assoc($query)):
        $emp_init = strtoupper(substr($row['nama_lengkap'], 0, 1));
        $capaian  = ($row['target_kerja'] > 0) ? round(($row['hasil_kerja'] / $row['target_kerja']) * 100) : 0;
        $satuan   = getSatuanAktivitas($row);
        $foto_path = 'uploads/' . $row['foto_bukti'];
        $foto_exists = !empty($row['foto_bukti']) && file_exists($foto_path);
        $status = $row['status_verifikasi'];
    ?>
    <div class="verify-card">
        <div class="verify-card-header">
            <div class="employee-cell">
                <div class="employee-avatar"><?= $emp_init ?></div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                    <div style="font-size:0.75rem; color:var(--muted);">@<?= htmlspecialchars($row['username']) ?></div>
                </div>
            </div>
            <?php if ($filter === 'selesai'): ?>
                <span class="status-badge <?= $status === 'Disetujui' ? 'approved' : 'rejected' ?>"><?= $status ?></span>
            <?php else: ?>
                <span class="status-badge pending">Pending</span>
            <?php endif; ?>
        </div>

        <div class="verify-card-body">
            <div class="verify-meta">
                <div class="verify-meta-item">
                    <i class="bi bi-calendar3"></i>
                    <span><?= date('d M Y', strtotime($row['tanggal'])) ?></span>
                </div>
                <div class="verify-meta-item">
                    <i class="bi bi-briefcase"></i>
                    <span><?= htmlspecialchars($row['jenis_pekerjaan']) ?></span>
                </div>
            </div>

            <div class="verify-stats">
                <div class="verify-stat">
                    <small>Hasil Kerja</small>
                    <strong class="text-success"><?= formatHasil($row['hasil_kerja'], $satuan) ?></strong>
                </div>
                <div class="verify-stat">
                    <small>Target</small>
                    <strong><?= formatHasil($row['target_kerja'], $satuan) ?></strong>
                </div>
                <div class="verify-stat">
                    <small>Capaian</small>
                    <strong class="<?= $capaian >= 100 ? 'text-success' : ($capaian >= 80 ? 'text-warning' : 'text-danger') ?>"><?= $capaian ?>%</strong>
                </div>
            </div>

            <div class="verify-progress">
                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-success" style="width:<?= min(100, $capaian) ?>%"></div>
                </div>
            </div>

            <div class="verify-photo">
                <small class="text-muted d-block mb-2"><i class="bi bi-camera"></i> Bukti Foto Lapangan</small>
                <?php if ($foto_exists): ?>
                <img src="<?= htmlspecialchars($foto_path) ?>" alt="Bukti laporan" class="verify-thumb" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="<?= htmlspecialchars($foto_path) ?>" data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>">
                <?php else: ?>
                <div class="verify-no-photo"><i class="bi bi-image"></i> Foto tidak tersedia</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($filter === 'pending'): ?>
        <div class="verify-card-footer">
            <a href="verifikasi.php?aksi=setuju&id=<?= $row['id_aktivitas'] ?>" class="btn-verify approve" onclick="return confirm('Setujui laporan <?= htmlspecialchars($row['nama_lengkap']) ?>?')">
                <i class="bi bi-check-lg"></i> Setujui
            </a>
            <a href="verifikasi.php?aksi=tolak&id=<?= $row['id_aktivitas'] ?>" class="btn-verify reject" onclick="return confirm('Tolak laporan <?= htmlspecialchars($row['nama_lengkap']) ?>?')">
                <i class="bi bi-x-lg"></i> Tolak
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<!-- Modal Foto -->
<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="fotoModalLabel">Bukti Foto Lapangan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="fotoModalImg" src="" alt="Bukti" class="img-fluid rounded-3" style="max-height:70vh;">
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('fotoModal')?.addEventListener('show.bs.modal', function(e) {
    const trigger = e.relatedTarget;
    document.getElementById('fotoModalImg').src = trigger.getAttribute('data-foto');
    document.getElementById('fotoModalLabel').textContent = 'Bukti Foto — ' + trigger.getAttribute('data-nama');
});
</script>

<?php include 'includes/manajer_footer.php'; ?>
