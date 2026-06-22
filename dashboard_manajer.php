<?php
session_start();
include 'koneksi.php';
include_once 'includes/target_helper.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != "manajer") {
    header("location:login.php");
    exit();
}

$nama_manajer = $_SESSION['nama'] ?? 'Manajer';

// Handler aksi validasi — dialihkan ke halaman verifikasi
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    header("location:verifikasi.php?aksi=" . urlencode($_GET['aksi']) . "&id=" . (int) $_GET['id']);
    exit();
}

function getCount($koneksi, $query) {
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        return $data['total'] ?? 0;
    }
    return 0;
}

function getSum($koneksi, $query) {
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        $data = mysqli_fetch_assoc($result);
        return $data['total'] ?? 0;
    }
    return 0;
}

$d_pending  = getCount($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE status_verifikasi='Pending'");
$d_setuju   = getCount($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE status_verifikasi='Disetujui'");
$d_ditolak  = getCount($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE status_verifikasi='Ditolak'");
$d_karyawan = getCount($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='karyawan'");
$d_hari_ini = getCount($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE tanggal='" . date('Y-m-d') . "'");
$total_ton  = getSum($koneksi, "SELECT SUM(hasil_kerja) as total FROM aktivitas WHERE status_verifikasi='Disetujui' AND jenis_pekerjaan='Pemanenan Kelapa Sawit'");

$query = mysqli_query($koneksi, "SELECT aktivitas.*, users.nama_lengkap FROM aktivitas JOIN users ON aktivitas.id_user = users.id_user WHERE aktivitas.status_verifikasi='Pending' ORDER BY aktivitas.tanggal ASC");

// Data grafik 7 hari terakhir
$chart_labels = [];
$chart_data   = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($tgl));
    $chart_data[]   = getCount($koneksi, "SELECT COUNT(*) as total FROM aktivitas WHERE tanggal='$tgl'");
}

$initials = strtoupper(substr($nama_manajer, 0, 1));
if (strpos($nama_manajer, ' ') !== false) {
    $parts = explode(' ', $nama_manajer);
    $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISPKO | Dashboard Manajer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-w: 270px;
            --primary: #0f766e;
            --primary-dark: #0d5f58;
            --accent: #10b981;
            --surface: #f0fdfa;
            --text: #1e293b;
            --muted: #64748b;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255,255,255,0.06);
        }

        * { box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #f0fdfa 0%, #f8fafc 50%, #ecfdf5 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .sidebar-brand .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: white;
        }

        .sidebar-brand span { color: #f8fafc; font-weight: 800; font-size: 1.15rem; letter-spacing: -0.02em; }
        .sidebar-brand small { color: #64748b; font-size: 0.7rem; display: block; margin-top: 2px; }

        .sidebar nav { flex: 1; padding: 16px 12px; }

        .nav-link {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 12px 16px;
            margin-bottom: 4px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-link:hover { background: var(--sidebar-hover); color: #e2e8f0; }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(15,118,110,0.15));
            color: #6ee7b7;
            font-weight: 600;
        }

        .nav-link i { font-size: 1.1rem; width: 22px; text-align: center; }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .sidebar-footer .nav-link { color: #f87171; }
        .sidebar-footer .nav-link:hover { background: rgba(248,113,113,0.1); color: #fca5a5; }

        /* Main */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 32px 40px 48px;
        }

        /* Top bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .top-bar h1 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
            color: var(--text);
        }

        .top-bar p { color: var(--muted); margin: 4px 0 0; font-size: 0.875rem; }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 8px 16px 8px 8px;
            border-radius: 50px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }

        .user-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 0.85rem;
        }

        .user-chip .name { font-weight: 600; font-size: 0.875rem; line-height: 1.2; }
        .user-chip .role { font-size: 0.7rem; color: var(--muted); }

        /* Stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 22px 24px;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 18px 18px 0 0;
        }

        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.08); }

        .stat-card.pending::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .stat-card.approved::before { background: linear-gradient(90deg, #10b981, #34d399); }
        .stat-card.workers::before  { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .stat-card.ton::before      { background: linear-gradient(90deg, #0f766e, #14b8a6); }

        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }

        .stat-card.pending .stat-icon  { background: #fef3c7; color: #d97706; }
        .stat-card.approved .stat-icon { background: #d1fae5; color: #059669; }
        .stat-card.workers .stat-icon  { background: #dbeafe; color: #2563eb; }
        .stat-card.ton .stat-icon      { background: #ccfbf1; color: #0f766e; }

        .stat-card .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .stat-card .stat-sub { font-size: 0.75rem; color: var(--muted); margin-top: 6px; }

        /* Content grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
        }

        .panel {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .panel-header {
            padding: 22px 28px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h5 {
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-header .badge-count {
            background: #fef3c7;
            color: #b45309;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
        }

        /* Table */
        .table-modern { margin: 0; }
        .table-modern thead th {
            background: #f8fafc;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 14px 20px;
            border: none;
        }

        .table-modern tbody td {
            padding: 16px 20px;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .table-modern tbody tr { transition: background 0.15s; }
        .table-modern tbody tr:hover { background: #f8fafc; }

        .employee-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .employee-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e0f2fe, #dbeafe);
            color: #2563eb;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.75rem;
            flex-shrink: 0;
        }

        .job-badge {
            background: #f1f5f9;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .ton-value {
            font-weight: 800;
            color: #059669;
            font-size: 1rem;
        }

        .btn-action {
            width: 34px; height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-action.approve { background: #d1fae5; color: #059669; }
        .btn-action.approve:hover { background: #059669; color: white; }
        .btn-action.reject  { background: #fee2e2; color: #dc2626; }
        .btn-action.reject:hover  { background: #dc2626; color: white; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 56px 24px;
            color: var(--muted);
        }

        .empty-state i { font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 16px; }
        .empty-state p { font-weight: 600; margin: 0; color: #475569; }

        /* Chart panel */
        .chart-panel { padding: 24px 28px 28px; }
        .chart-panel h6 { font-weight: 700; font-size: 0.9rem; margin-bottom: 4px; }
        .chart-panel p { font-size: 0.78rem; color: var(--muted); margin-bottom: 20px; }
        .chart-wrapper {
            position: relative;
            width: 100%;
            height: 220px;
        }

        .mini-stats { padding: 0 28px 24px; }

        .mini-stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .mini-stat-row:last-child { border-bottom: none; }

        .mini-stat-row .label { font-size: 0.85rem; color: var(--muted); font-weight: 500; }
        .mini-stat-row .value { font-weight: 700; font-size: 1.1rem; }

        .quick-links {
            padding: 20px 28px 28px;
            display: grid;
            gap: 10px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            background: #f8fafc;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .quick-link:hover { background: var(--surface); border-color: #99f6e4; color: var(--primary); }
        .quick-link i { color: var(--primary); font-size: 1.1rem; }

        @media (max-width: 1200px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 20px 16px; }
            .stat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
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
        <a class="nav-link active" href="dashboard_manajer.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a class="nav-link" href="verifikasi.php"><i class="bi bi-shield-check"></i> Verifikasi</a>
        <a class="nav-link" href="input_target.php"><i class="bi bi-bullseye"></i> Target Kerja</a>
        <a class="nav-link" href="data_karyawan.php"><i class="bi bi-people-fill"></i> Data Karyawan</a>
        <a class="nav-link" href="laporan_histori.php"><i class="bi bi-clock-history"></i> Laporan Histori</a>
    </nav>

    <div class="sidebar-footer">
        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-left"></i> Keluar Sistem</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">

    <div class="top-bar">
        <div>
            <h1>Dashboard Eksekutif</h1>
            <p>Monitor operasional & validasi kinerja karyawan lapangan</p>
        </div>
        <div class="user-chip">
            <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
            <div>
                <div class="name"><?= htmlspecialchars($nama_manajer) ?></div>
                <div class="role">Manajer Operasional</div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid">
        <div class="stat-card pending">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Menunggu Validasi</div>
                    <div class="stat-value"><?= $d_pending ?></div>
                    <div class="stat-sub">Perlu tindakan segera</div>
                </div>
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
        <div class="stat-card approved">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Disetujui</div>
                    <div class="stat-value"><?= $d_setuju ?></div>
                    <div class="stat-sub"><?= $d_ditolak ?> ditolak total</div>
                </div>
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
        <div class="stat-card workers">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Karyawan</div>
                    <div class="stat-value"><?= $d_karyawan ?></div>
                    <div class="stat-sub">Aktif di lapangan</div>
                </div>
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
        <div class="stat-card ton">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Pemanenan</div>
                    <div class="stat-value"><?= number_format($total_ton, 1) ?></div>
                    <div class="stat-sub">Ton (pemanenan terverifikasi)</div>
                </div>
                <div class="stat-icon"><i class="bi bi-bar-chart-fill"></i></div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">

        <!-- Validation Queue -->
        <div class="panel">
            <div class="panel-header">
                <h5><i class="bi bi-inbox-fill text-warning"></i> Antrean Validasi</h5>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($d_pending > 0): ?>
                        <span class="badge-count"><?= $d_pending ?> pending</span>
                    <?php endif; ?>
                    <a href="verifikasi.php" class="btn-outline-manajer btn-sm py-1 px-3" style="font-size:0.8rem;">Kelola Verifikasi <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <?php if (mysqli_num_rows($query) > 0): ?>
            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Karyawan</th>
                            <th>Tanggal</th>
                            <th>Aktivitas</th>
                            <th class="text-center">Hasil</th>
                            <th class="text-center">Target</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_array($query)):
                            $emp_init = strtoupper(substr($row['nama_lengkap'], 0, 1));
                            $capaian = ($row['target_kerja'] > 0) ? round(($row['hasil_kerja'] / $row['target_kerja']) * 100) : 0;
                            $satuan  = getSatuanAktivitas($row);
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="employee-cell">
                                    <div class="employee-avatar"><?= $emp_init ?></div>
                                    <div>
                                        <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                        <div style="font-size:0.75rem; color:var(--muted);"><?= $capaian ?>% capaian</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size:0.85rem; color:var(--muted);"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td><span class="job-badge"><?= htmlspecialchars($row['jenis_pekerjaan']) ?></span></td>
                            <td class="text-center"><span class="ton-value"><?= formatHasil($row['hasil_kerja'], $satuan) ?></span></td>
                            <td class="text-center" style="color:var(--muted); font-size:0.9rem;"><?= formatHasil($row['target_kerja'], $satuan) ?></td>
                            <td class="text-center pe-4">
                                <a href="verifikasi.php?aksi=setuju&id=<?= $row['id_aktivitas'] ?>" class="btn-action approve" title="Setujui"><i class="bi bi-check-lg"></i></a>
                                <a href="verifikasi.php?aksi=tolak&id=<?= $row['id_aktivitas'] ?>" class="btn-action reject ms-1" title="Tolak"><i class="bi bi-x-lg"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-check2-all"></i>
                <p>Semua laporan sudah divalidasi</p>
                <small>Tidak ada antrean pending saat ini</small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Panel -->
        <div>
            <div class="panel mb-4">
                <div class="chart-panel">
                    <h6><i class="bi bi-graph-up text-success"></i> Aktivitas 7 Hari</h6>
                    <p>Jumlah laporan harian yang masuk</p>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
                <div class="mini-stats">
                    <div class="mini-stat-row">
                        <span class="label"><i class="bi bi-calendar-day me-1"></i> Laporan Hari Ini</span>
                        <span class="value text-primary"><?= $d_hari_ini ?></span>
                    </div>
                    <div class="mini-stat-row">
                        <span class="label"><i class="bi bi-x-circle me-1"></i> Total Ditolak</span>
                        <span class="value text-danger"><?= $d_ditolak ?></span>
                    </div>
                    <div class="mini-stat-row">
                        <span class="label"><i class="bi bi-percent me-1"></i> Tingkat Persetujuan</span>
                        <span class="value text-success">
                            <?php
                            $total_all = $d_setuju + $d_ditolak + $d_pending;
                            echo $total_all > 0 ? round(($d_setuju / $total_all) * 100) : 0;
                            ?>%
                        </span>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h5><i class="bi bi-lightning-fill text-warning"></i> Akses Cepat</h5>
                </div>
                <div class="quick-links">
                    <a href="verifikasi.php" class="quick-link"><i class="bi bi-shield-check"></i> Verifikasi Laporan Karyawan</a>
                    <a href="input_target.php" class="quick-link"><i class="bi bi-bullseye"></i> Atur Target Kerja</a>
                    <a href="data_karyawan.php" class="quick-link"><i class="bi bi-person-plus"></i> Kelola Data Karyawan</a>
                    <a href="laporan_histori.php" class="quick-link"><i class="bi bi-file-earmark-bar-graph"></i> Lihat Histori Lengkap</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartLabels = <?= json_encode($chart_labels) ?>;
const chartData   = <?= json_encode($chart_data) ?>;
const maxVal      = Math.max(...chartData, 1);

const ctx = document.getElementById('activityChart');
const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Laporan',
            data: chartData,
            borderColor: '#0f766e',
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: '#0f766e',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 12,
                cornerRadius: 8,
                titleFont: { family: 'Plus Jakarta Sans', weight: '600' },
                bodyFont: { family: 'Plus Jakarta Sans' },
                callbacks: {
                    label: (ctx) => ' ' + ctx.parsed.y + ' laporan'
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: '#94a3b8' }
            },
            y: {
                beginAtZero: true,
                suggestedMax: maxVal + Math.max(1, Math.ceil(maxVal * 0.25)),
                ticks: {
                    stepSize: 1,
                    font: { family: 'Plus Jakarta Sans', size: 11 },
                    color: '#94a3b8',
                    precision: 0
                },
                grid: { color: '#f1f5f9', drawBorder: false }
            }
        }
    }
});
</script>
</body>
</html>
