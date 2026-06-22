<?php
include 'includes/manajer_init.php';

$pesan = null;
$tipe_pesan = 'success';

if (isset($_POST['tambah'])) {
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $username === '' || $password === '') {
        $pesan = 'Semua field wajib diisi.';
        $tipe_pesan = 'danger';
    } else {
        $nama_esc = mysqli_real_escape_string($koneksi, $nama);
        $user_esc = mysqli_real_escape_string($koneksi, $username);
        $pass_esc = mysqli_real_escape_string($koneksi, $password);

        $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username='$user_esc'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $pesan = 'Username sudah digunakan. Pilih username lain.';
            $tipe_pesan = 'danger';
        } else {
            $ok = mysqli_query($koneksi, "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama_esc', '$user_esc', '$pass_esc', 'karyawan')");
            if ($ok) {
                $pesan = 'Karyawan "' . $nama . '" berhasil ditambahkan.';
            } else {
                $pesan = 'Gagal menambahkan karyawan.';
                $tipe_pesan = 'danger';
            }
        }
    }
}

$pageTitle    = 'Data Karyawan';
$pageHeading  = 'Data Personel Karyawan';
$pageSubtitle = 'Kelola akun karyawan lapangan';
$activeMenu   = 'karyawan';

$q = mysqli_query($koneksi, "SELECT * FROM users WHERE role='karyawan' ORDER BY nama_lengkap ASC");
$total_karyawan = $q ? mysqli_num_rows($q) : 0;

$topBarExtra = '<span class="stat-pill"><i class="bi bi-people-fill"></i> ' . $total_karyawan . ' karyawan</span>';

include 'includes/manajer_header.php';
?>

<?php if ($pesan): ?>
<div class="alert-manajer alert-<?= $tipe_pesan ?> mb-4">
    <i class="bi bi-<?= $tipe_pesan === 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill"></i>
    <?= htmlspecialchars($pesan) ?>
</div>
<?php endif; ?>

<div class="target-layout">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="bi bi-people-fill text-primary"></i> Daftar Karyawan</h5>
        </div>

        <?php if ($total_karyawan == 0): ?>
        <div class="empty-state">
            <i class="bi bi-person-x"></i>
            <p>Belum ada karyawan terdaftar</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">UID</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th class="pe-4">Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_array($q)):
                        $emp_init = strtoupper(substr($row['nama_lengkap'], 0, 1));
                    ?>
                    <tr>
                        <td class="ps-4"><span class="uid-badge">#ASA-0<?= $row['id_user'] ?></span></td>
                        <td>
                            <div class="employee-cell">
                                <div class="employee-avatar"><?= $emp_init ?></div>
                                <span class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                            </div>
                        </td>
                        <td><span class="username-badge"><?= htmlspecialchars($row['username']) ?></span></td>
                        <td class="pe-4"><span class="role-badge">Operator Lapangan</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="bi bi-person-plus text-success"></i> Tambah Karyawan</h5>
        </div>
        <div class="panel-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control form-control-modern" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control form-control-modern" placeholder="Contoh: budi_santoso" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="text" name="password" class="form-control form-control-modern" placeholder="Password awal karyawan" required>
                </div>
                <button type="submit" name="tambah" class="btn-manajer">
                    <i class="bi bi-person-plus me-2"></i>Simpan Karyawan
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/manajer_footer.php'; ?>
