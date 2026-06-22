<?php
include 'includes/manajer_init.php';
include_once 'includes/target_helper.php';

$pageTitle    = 'Laporan Histori';
$pageHeading  = 'Histori Aktivitas';
$pageSubtitle = 'Arsip dan filter laporan kerja karyawan';
$activeMenu   = 'histori';

$filter_status = $_GET['status'] ?? '';
$filter_dari   = $_GET['dari'] ?? '';
$filter_sampai = $_GET['sampai'] ?? '';
$filter_nama   = trim($_GET['cari'] ?? '');

$where = ['1=1'];
if (in_array($filter_status, ['Pending', 'Disetujui', 'Ditolak'], true)) {
    $where[] = "aktivitas.status_verifikasi='" . mysqli_real_escape_string($koneksi, $filter_status) . "'";
}
if ($filter_dari !== '') {
    $where[] = "aktivitas.tanggal >= '" . mysqli_real_escape_string($koneksi, $filter_dari) . "'";
}
if ($filter_sampai !== '') {
    $where[] = "aktivitas.tanggal <= '" . mysqli_real_escape_string($koneksi, $filter_sampai) . "'";
}
if ($filter_nama !== '') {
    $cari = mysqli_real_escape_string($koneksi, $filter_nama);
    $where[] = "users.nama_lengkap LIKE '%$cari%'";
}

$sql = "SELECT aktivitas.*, users.nama_lengkap FROM aktivitas
        JOIN users ON aktivitas.id_user = users.id_user
        WHERE " . implode(' AND ', $where) . "
        ORDER BY aktivitas.tanggal DESC, aktivitas.id_aktivitas DESC";
$query = mysqli_query($koneksi, $sql);
$total_laporan = $query ? mysqli_num_rows($query) : 0;

$topBarExtra = '
    <div class="btn-print-box">
        <button onclick="window.print()" class="btn-outline-manajer">
            <i class="bi bi-printer me-2"></i>Cetak
        </button>
    </div>';

include 'includes/manajer_header.php';
?>

<div class="panel mb-4">
    <div class="panel-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Cari Karyawan</label>
                <input type="text" name="cari" class="form-control form-control-modern" value="<?= htmlspecialchars($filter_nama) ?>" placeholder="Nama karyawan...">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-control-modern">
                    <option value="">Semua</option>
                    <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Disetujui" <?= $filter_status === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Ditolak" <?= $filter_status === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control form-control-modern" value="<?= htmlspecialchars($filter_dari) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control form-control-modern" value="<?= htmlspecialchars($filter_sampai) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn-manajer py-2 flex-fill"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="laporan_histori.php" class="btn-outline-manajer py-2 px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="bi bi-archive-fill text-success"></i> Arsip Laporan</h5>
        <span class="stat-pill"><i class="bi bi-file-earmark-text"></i> <?= $total_laporan ?> record</span>
    </div>

    <?php if ($total_laporan == 0): ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Tidak ada data sesuai filter</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-modern align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Tanggal</th>
                    <th>Karyawan</th>
                    <th>Pekerjaan</th>
                    <th class="text-center">Hasil</th>
                    <th class="text-center">Target</th>
                    <th class="text-center pe-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($query)):
                    $badge_class = ($row['status_verifikasi'] == 'Disetujui') ? 'approved' : (($row['status_verifikasi'] == 'Ditolak') ? 'rejected' : 'pending');
                    $emp_init = strtoupper(substr($row['nama_lengkap'], 0, 1));
                    $satuan = getSatuanAktivitas($row);
                ?>
                <tr>
                    <td class="ps-4" style="font-size:0.85rem; color:var(--muted);"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                    <td>
                        <div class="employee-cell">
                            <div class="employee-avatar"><?= $emp_init ?></div>
                            <span class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                        </div>
                    </td>
                    <td><span class="job-badge"><?= htmlspecialchars($row['jenis_pekerjaan']) ?></span></td>
                    <td class="text-center"><span class="ton-value"><?= formatHasil($row['hasil_kerja'], $satuan) ?></span></td>
                    <td class="text-center" style="color:var(--muted);"><?= formatHasil($row['target_kerja'], $satuan) ?></td>
                    <td class="text-center pe-4"><span class="status-badge <?= $badge_class ?>"><?= htmlspecialchars($row['status_verifikasi']) ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/manajer_footer.php'; ?>
