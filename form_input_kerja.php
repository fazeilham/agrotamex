<?php
include 'includes/karyawan_init.php';
include_once 'includes/target_helper.php';

$tanggal_default = date('Y-m-d');
$target_hari_ini = getTargetAktif($koneksi, $tanggal_default);
$info_hari_ini   = getInfoPekerjaan($target_hari_ini['jenis_pekerjaan']);

if (isset($_POST['kirim'])) {
    $tanggal = $_POST['tanggal'];
    $jenis   = $_POST['jenis_pekerjaan'];
    $hasil   = $_POST['hasil_kerja'];

    $target_data = getTargetAktif($koneksi, $tanggal);
    $target = $target_data['target_ton'];
    $satuan = $target_data['satuan'];

    if ($jenis !== $target_data['jenis_pekerjaan']) {
        $error_msg = 'Jenis pekerjaan harus sesuai target manajemen: ' . $target_data['jenis_pekerjaan'];
    } else {
        $tanggal_esc = mysqli_real_escape_string($koneksi, $tanggal);
        $cek_duplikat = mysqli_query($koneksi, "SELECT id_aktivitas FROM aktivitas WHERE id_user='$id_user' AND tanggal='$tanggal_esc' AND status_verifikasi IN ('Pending','Disetujui') LIMIT 1");
        if ($cek_duplikat && mysqli_num_rows($cek_duplikat) > 0) {
            $error_msg = 'Anda sudah mengirim laporan untuk tanggal ini. Tunggu verifikasi atau hubungi manajer jika ditolak.';
        } else {
        ensureAktivitasSatuan($koneksi);
        $foto = $_FILES['foto']['name'] ?: ('bukti_' . time() . '.jpg');
        $tmp  = $_FILES['foto']['tmp_name'];
        $foto_baru = time() . "_" . $foto;
        $path = "uploads/" . $foto_baru;

        if (move_uploaded_file($tmp, $path)) {
            $tanggal_esc = mysqli_real_escape_string($koneksi, $tanggal);
            $jenis_esc   = mysqli_real_escape_string($koneksi, $jenis);
            $satuan_esc  = mysqli_real_escape_string($koneksi, $satuan);
            $insert = mysqli_query($koneksi, "INSERT INTO aktivitas (id_user, tanggal, jenis_pekerjaan, hasil_kerja, target_kerja, satuan, foto_bukti, status_verifikasi) 
                      VALUES ('$id_user', '$tanggal_esc', '$jenis_esc', '$hasil', '$target', '$satuan_esc', '$foto_baru', 'Pending')");
            if ($insert) {
                header('location:dashboard_karyawan.php?msg=success');
                exit();
            } else {
                $error_msg = 'Gagal menyimpan data ke database.';
            }
        } else {
            $error_msg = 'Gagal mengunggah foto bukti. Pastikan folder uploads sudah ada.';
        }
        }
    }
}

$pageTitle    = 'Input Hasil Lapangan';
$pageHeading  = 'Input Hasil Lapangan';
$pageSubtitle = 'Kirim laporan aktivitas kerja harian ke manajemen';
$activeMenu   = 'input';

include 'includes/karyawan_header.php';
?>

<?php if (!empty($error_msg)): ?>
<div class="alert-manajer alert-danger mb-4">
    <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error_msg) ?>
</div>
<?php endif; ?>

<div class="panel form-panel">
    <div class="panel-header">
        <h5><i class="bi bi-file-earmark-medical text-success"></i> Form Laporan Kerja</h5>
    </div>
    <div class="panel-body">
        <div class="target-info-box">
            <div class="row g-3">
                <div class="col-6">
                    <div class="label">Target Hari Ini</div>
                    <div class="value"><?= formatHasil($target_hari_ini['target_ton'], $target_hari_ini['satuan']) ?></div>
                </div>
                <div class="col-6">
                    <div class="label">Pekerjaan Wajib</div>
                    <div class="value" style="font-size:0.9rem;"><?= htmlspecialchars($target_hari_ini['jenis_pekerjaan']) ?></div>
                </div>
            </div>
        </div>

        <form method="POST" action="" enctype="multipart/form-data" id="formLaporan">
            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Kerja</label>
                <input type="date" name="tanggal" id="tanggalKerja" class="form-control form-control-modern" value="<?= $tanggal_default ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Pekerjaan</label>
                <input type="text" class="form-control form-control-modern bg-light" id="jenisDisplay" value="<?= htmlspecialchars($target_hari_ini['jenis_pekerjaan']) ?>" readonly>
                <input type="hidden" name="jenis_pekerjaan" id="jenisHidden" value="<?= htmlspecialchars($target_hari_ini['jenis_pekerjaan']) ?>">
                <div class="form-text">Ditentukan oleh manajemen sesuai target harian.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" id="labelHasil">Hasil Nyata (<?= htmlspecialchars($info_hari_ini['satuan']) ?>)</label>
                <input type="number" step="<?= $info_hari_ini['step'] ?>" name="hasil_kerja" id="inputHasil" class="form-control form-control-modern" placeholder="<?= htmlspecialchars($info_hari_ini['placeholder']) ?>" required>
                <div class="form-text">Target capaian: <strong id="targetLabel"><?= formatHasil($target_hari_ini['target_ton'], $target_hari_ini['satuan']) ?></strong></div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Foto Bukti Lapangan</label>

                <input type="file" name="foto" id="fotoInput" accept="image/*" required hidden>
                <input type="file" id="fotoGaleri" accept="image/*" hidden>

                <div class="camera-upload" id="cameraUpload">
                    <div class="camera-preview" id="cameraPreview">
                        <i class="bi bi-camera"></i>
                        <span>Belum ada foto</span>
                    </div>
                    <div class="camera-actions">
                        <button type="button" class="btn-camera primary" id="btnBukaKamera">
                            <i class="bi bi-camera-fill"></i> Ambil Foto Kamera
                        </button>
                        <button type="button" class="btn-camera secondary" id="btnPilihGaleri">
                            <i class="bi bi-images"></i> Pilih Galeri
                        </button>
                    </div>
                </div>
                <div class="form-text text-muted">Gunakan tombol kamera untuk langsung memotret bukti di lapangan.</div>
            </div>

            <button type="submit" name="kirim" class="btn-manajer">
                <i class="bi bi-send me-2"></i>Kirim Laporan Kerja
            </button>
        </form>
    </div>
</div>

<!-- Modal Kamera Langsung -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content camera-modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-camera-fill text-success"></i> Ambil Foto Bukti</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="btnTutupKamera"></button>
            </div>
            <div class="modal-body p-3 pt-2">
                <div class="camera-live-wrap">
                    <video id="cameraVideo" autoplay playsinline muted></video>
                    <canvas id="cameraCanvas" hidden></canvas>
                    <div class="camera-loading" id="cameraLoading">
                        <div class="spinner-border text-success" role="status"></div>
                        <span>Membuka kamera...</span>
                    </div>
                </div>
                <p class="text-muted small text-center mt-2 mb-0">Arahkan kamera ke bukti pekerjaan lapangan</p>
            </div>
            <div class="modal-footer border-0 pt-0 flex-nowrap gap-2">
                <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-manajer flex-fill py-2" id="btnCapture" disabled>
                    <i class="bi bi-camera"></i> Ambil Foto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const targetCache = {
    '<?= $tanggal_default ?>': {
        nilai: '<?= addslashes($target_hari_ini['target_ton']) ?>',
        ton: '<?= addslashes($target_hari_ini['target_ton']) ?>',
        jenis: '<?= addslashes($target_hari_ini['jenis_pekerjaan']) ?>',
        satuan: '<?= addslashes($target_hari_ini['satuan']) ?>',
        step: '<?= $info_hari_ini['step'] ?>',
        placeholder: '<?= addslashes($info_hari_ini['placeholder']) ?>'
    }
};

document.getElementById('tanggalKerja')?.addEventListener('change', async function() {
    const tgl = this.value;
    if (targetCache[tgl]) {
        applyTarget(targetCache[tgl]);
        return;
    }
    try {
        const res = await fetch('api_target.php?tanggal=' + tgl);
        const data = await res.json();
        targetCache[tgl] = data;
        applyTarget(data);
    } catch (e) {
        alert('Gagal memuat target untuk tanggal tersebut.');
    }
});

function applyTarget(data) {
    document.getElementById('jenisDisplay').value = data.jenis;
    document.getElementById('jenisHidden').value = data.jenis;
    const satuan = data.satuan || 'Unit';
    const nilai  = data.nilai || data.ton;
    document.getElementById('labelHasil').textContent = 'Hasil Nyata (' + satuan + ')';
    document.getElementById('targetLabel').textContent = nilai + ' ' + satuan;
    const inputHasil = document.getElementById('inputHasil');
    inputHasil.step = data.step || 0.1;
    inputHasil.placeholder = data.placeholder || '';
}

const fotoInput     = document.getElementById('fotoInput');
const fotoGaleri    = document.getElementById('fotoGaleri');
const cameraPreview = document.getElementById('cameraPreview');
const btnBukaKamera = document.getElementById('btnBukaKamera');
const btnPilihGaleri = document.getElementById('btnPilihGaleri');
const cameraModal   = document.getElementById('cameraModal');
const cameraVideo   = document.getElementById('cameraVideo');
const cameraCanvas  = document.getElementById('cameraCanvas');
const cameraLoading = document.getElementById('cameraLoading');
const btnCapture    = document.getElementById('btnCapture');
let cameraStream    = null;
let bsCameraModal   = null;

function setFotoFile(file) {
    const dt = new DataTransfer();
    dt.items.add(file);
    fotoInput.files = dt.files;
    showPreview(file);
}

function showPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        cameraPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview foto">';
        cameraPreview.classList.add('has-photo');
    };
    reader.readAsDataURL(file);
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    cameraVideo.srcObject = null;
    btnCapture.disabled = true;
    cameraLoading.style.display = 'none';
}

async function startCamera() {
    stopCamera();
    cameraLoading.style.display = 'flex';
    btnCapture.disabled = true;

    const constraints = [
        { video: { facingMode: { exact: 'environment' } }, audio: false },
        { video: { facingMode: 'environment' }, audio: false },
        { video: { facingMode: 'user' }, audio: false },
        { video: true, audio: false }
    ];

    for (const c of constraints) {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia(c);
            break;
        } catch (e) { /* coba constraint berikutnya */ }
    }

    if (!cameraStream) {
        cameraLoading.style.display = 'none';
        alert('Tidak dapat mengakses kamera. Izinkan akses kamera di browser, atau gunakan Pilih Galeri.');
        bsCameraModal?.hide();
        return;
    }

    cameraVideo.srcObject = cameraStream;
    cameraVideo.onloadedmetadata = () => {
        cameraLoading.style.display = 'none';
        btnCapture.disabled = false;
    };
}

btnBukaKamera?.addEventListener('click', () => {
    if (!navigator.mediaDevices?.getUserMedia) {
        alert('Browser tidak mendukung akses kamera langsung. Gunakan Pilih Galeri.');
        return;
    }
    bsCameraModal = bootstrap.Modal.getOrCreateInstance(cameraModal);
    bsCameraModal.show();
});

cameraModal?.addEventListener('shown.bs.modal', startCamera);
cameraModal?.addEventListener('hidden.bs.modal', stopCamera);

btnCapture?.addEventListener('click', () => {
    const w = cameraVideo.videoWidth;
    const h = cameraVideo.videoHeight;
    if (!w || !h) return;

    cameraCanvas.width = w;
    cameraCanvas.height = h;
    cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0, w, h);

    cameraCanvas.toBlob((blob) => {
        if (!blob) return;
        const file = new File([blob], 'bukti_lapangan_' + Date.now() + '.jpg', { type: 'image/jpeg' });
        setFotoFile(file);
        bsCameraModal?.hide();
    }, 'image/jpeg', 0.85);
});

btnPilihGaleri?.addEventListener('click', () => fotoGaleri.click());

fotoGaleri?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar.');
        this.value = '';
        return;
    }
    setFotoFile(file);
    this.value = '';
});
</script>

<?php include 'includes/karyawan_footer.php'; ?>
