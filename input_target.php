<?php
include 'includes/manajer_init.php';
include_once 'includes/target_helper.php';

ensureTargetTable($koneksi);

$tanggal_form = $_POST['tanggal'] ?? $_GET['tanggal'] ?? date('Y-m-d');
$target_aktif = getTargetAktif($koneksi, $tanggal_form);
$info_aktif   = getInfoPekerjaan($target_aktif['jenis_pekerjaan']);
$pesan = null;
$tipe_pesan = 'success';

if (isset($_POST['simpan'])) {
    $tanggal_form   = $_POST['tanggal'];
    $target_ton     = $_POST['target_ton'];
    $jenis_kerja    = $_POST['jenis_pekerjaan'];

    if (simpanTargetHarian($koneksi, $tanggal_form, $target_ton, $jenis_kerja, $_SESSION['id_user'])) {
        $pesan = 'Target kerja berhasil disimpan untuk tanggal ' . date('d M Y', strtotime($tanggal_form)) . '.';
        $target_aktif = getTargetAktif($koneksi, $tanggal_form);
        $info_aktif   = getInfoPekerjaan($target_aktif['jenis_pekerjaan']);
    } else {
        $pesan = 'Gagal menyimpan target. Periksa kembali input Anda.';
        $tipe_pesan = 'danger';
    }
}

$is_hari_ini = ($tanggal_form === date('Y-m-d'));
$riwayat = getRiwayatTarget($koneksi, 5);
$pekerjaanMap = daftarJenisPekerjaan();

$pageTitle    = 'Target Kerja';
$pageHeading  = 'Target Kerja Operasional';
$pageSubtitle = 'Atur target capaian dan jenis pekerjaan harian karyawan';
$activeMenu   = 'target';

include 'includes/manajer_header.php';
?>

<?php if ($pesan): ?>
<div class="alert-manajer alert-<?= $tipe_pesan ?> mb-4">
    <i class="bi bi-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill"></i>
    <?= htmlspecialchars($pesan) ?>
</div>
<?php endif; ?>

<div class="target-layout">
    <div class="panel target-panel">
        <div class="panel-header">
            <h5><i class="bi bi-bullseye text-success"></i> <?= $is_hari_ini ? 'Target Hari Ini' : 'Atur Target' ?></h5>
            <?php if ($is_hari_ini): ?>
                <span class="stat-pill"><i class="bi bi-calendar-day"></i> <?= date('d M Y') ?></span>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Target</label>
                    <input type="date" name="tanggal" class="form-control form-control-lg target-input" value="<?= htmlspecialchars($tanggal_form) ?>" required onchange="window.location='input_target.php?tanggal='+this.value">
                    <div class="form-text">Pilih tanggal untuk mengatur target kerja karyawan.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jenis Pekerjaan Hari Ini</label>
                    <select name="jenis_pekerjaan" id="jenisPekerjaan" class="form-select form-select-lg target-input" required>
                        <?php foreach ($pekerjaanMap as $jenis => $info): ?>
                        <option value="<?= htmlspecialchars($jenis) ?>" <?= ($target_aktif['jenis_pekerjaan'] === $jenis) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($jenis) ?> (<?= htmlspecialchars($info['satuan']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Setiap jenis pekerjaan memiliki satuan capaian yang berbeda.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" id="labelTarget">Target Hasil (<?= htmlspecialchars($info_aktif['satuan']) ?>)</label>
                    <div class="input-group input-group-lg">
                        <input type="number" step="<?= $info_aktif['step'] ?>" min="0.1" name="target_ton" id="inputTarget" class="form-control target-input" value="<?= htmlspecialchars($target_aktif['target_ton']) ?>" placeholder="<?= htmlspecialchars($info_aktif['placeholder']) ?>" required>
                        <span class="input-group-text" id="satuanBadge"><?= htmlspecialchars($info_aktif['satuan']) ?></span>
                    </div>
                </div>

                <div class="target-preview mb-4">
                    <small class="text-muted d-block mb-2">Preview Target</small>
                    <div class="target-value-box">
                        <div class="value" id="previewNilai"><?= htmlspecialchars($target_aktif['target_ton']) ?></div>
                        <div class="unit"><span id="previewSatuan"><?= htmlspecialchars($info_aktif['satuan']) ?></span> — <span id="previewJenis"><?= htmlspecialchars($target_aktif['jenis_pekerjaan']) ?></span></div>
                    </div>
                </div>

                <button type="submit" name="simpan" class="btn-manajer">
                    <i class="bi bi-save me-2"></i>Simpan Target Kerja
                </button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="bi bi-clock-history text-primary"></i> Riwayat Target</h5>
        </div>
        <?php if (!$riwayat || mysqli_num_rows($riwayat) === 0): ?>
        <div class="empty-state py-5">
            <i class="bi bi-calendar-x"></i>
            <p>Belum ada riwayat target</p>
        </div>
        <?php else: ?>
        <div class="target-history">
            <?php while ($row = mysqli_fetch_assoc($riwayat)):
                $row = enrichTarget($row);
            ?>
            <a href="input_target.php?tanggal=<?= $row['tanggal'] ?>" class="history-item <?= ($row['tanggal'] === $tanggal_form) ? 'active' : '' ?>">
                <div>
                    <strong><?= date('d M Y', strtotime($row['tanggal'])) ?></strong>
                    <span><?= htmlspecialchars($row['jenis_pekerjaan']) ?></span>
                </div>
                <div class="history-ton"><?= formatHasil($row['target_ton'], $row['satuan']) ?></div>
            </a>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const pekerjaanMap = <?= getSatuanMapJson() ?>;
const jenisSelect  = document.getElementById('jenisPekerjaan');
const inputTarget  = document.getElementById('inputTarget');

function updateSatuanUI() {
    const jenis = jenisSelect.value;
    const info  = pekerjaanMap[jenis];
    if (!info) return;

    document.getElementById('labelTarget').textContent = 'Target Hasil (' + info.satuan + ')';
    document.getElementById('satuanBadge').textContent = info.satuan;
    document.getElementById('previewSatuan').textContent = info.satuan;
    document.getElementById('previewJenis').textContent = jenis;
    inputTarget.step = info.step;
    inputTarget.placeholder = info.placeholder;

    if (!inputTarget.dataset.userEdited) {
        inputTarget.value = info.default_target;
        document.getElementById('previewNilai').textContent = info.default_target;
    }
}

jenisSelect?.addEventListener('change', () => {
    inputTarget.dataset.userEdited = '';
    updateSatuanUI();
});

inputTarget?.addEventListener('input', () => {
    inputTarget.dataset.userEdited = '1';
    document.getElementById('previewNilai').textContent = inputTarget.value || '0';
});
</script>

<?php include 'includes/manajer_footer.php'; ?>
