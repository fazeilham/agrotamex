<?php
include 'includes/karyawan_init.php';
include_once 'includes/target_helper.php';

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total, 
             SUM(CASE WHEN status_verifikasi='Disetujui' THEN 1 ELSE 0 END) as disetujui,
             SUM(CASE WHEN status_verifikasi='Ditolak' THEN 1 ELSE 0 END) as ditolak,
             AVG(CASE WHEN status_verifikasi='Disetujui' THEN (hasil_kerja/target_kerja)*100 ELSE NULL END) as avg_prod
             FROM aktivitas WHERE id_user='$id_user'");
$data_rekap = mysqli_fetch_assoc($q_total);

$total_laporan             = $data_rekap['total'];
$total_disetujui           = $data_rekap['disetujui'];
$total_ditolak             = $data_rekap['ditolak'];
$rata_rata_produktivitas   = $data_rekap['avg_prod'] ?? 0;

$chart_labels = [];
$chart_data   = [];
$rows_kinerja = [];
$query = mysqli_query($koneksi, "SELECT * FROM aktivitas WHERE id_user='$id_user' ORDER BY tanggal ASC");
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $persen = ($row['target_kerja'] > 0) ? ($row['hasil_kerja'] / $row['target_kerja']) * 100 : 0;
        $chart_labels[] = date('d/m', strtotime($row['tanggal']));
        $chart_data[]   = round($persen, 1);
        $rows_kinerja[] = $row;
    }
}

$pageTitle    = 'Catatan Kinerja';
$pageHeading  = 'Catatan Kinerja Saya';
$pageSubtitle = 'Grafik perkembangan dan riwayat laporan kerja pribadi';
$activeMenu   = 'kinerja';

include 'includes/karyawan_header.php';
?>

<div class="stat-grid-4">
    <div class="stat-card workers">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label">Total Laporan</div>
                <div class="stat-value" style="font-size:1.6rem;"><?= $total_laporan ?></div>
            </div>
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
        </div>
    </div>
    <div class="stat-card approved">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label">Disetujui</div>
                <div class="stat-value" style="font-size:1.6rem;"><?= $total_disetujui ?></div>
            </div>
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        </div>
    </div>
    <div class="stat-card pending">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label">Ditolak</div>
                <div class="stat-value" style="font-size:1.6rem;"><?= $total_ditolak ?></div>
            </div>
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
        </div>
    </div>
    <div class="stat-card ton">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label">Rata-rata Capaian</div>
                <div class="stat-value" style="font-size:1.6rem;"><?= number_format($rata_rata_produktivitas, 1) ?>%
                </div>
            </div>
            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
        </div>
    </div>
</div>

<div class="panel mb-4">
    <div class="panel-header">
        <h5><i class="bi bi-graph-up text-success"></i> Grafik Produktivitas</h5>
    </div>
    <div class="panel-body">
        <?php if (empty($rows_kinerja)): ?>
        <div class="empty-state py-4">
            <i class="bi bi-bar-chart"></i>
            <p>Belum ada data untuk grafik</p>
        </div>
        <?php else: ?>
        <div class="chart-wrapper-sm">
            <canvas id="karyawanLineChart"></canvas>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="bi bi-clock-history text-primary"></i> Riwayat Kerja Harian</h5>
    </div>

    <?php if (empty($rows_kinerja)): ?>
    <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Belum ada histori data kinerja</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-modern align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Tanggal</th>
                    <th>Aktivitas</th>
                    <th class="text-center">Hasil</th>
                    <th class="text-center">Target</th>
                    <th class="text-center">Capaian</th>
                    <th class="text-center pe-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows_kinerja as $row):
                    $satuan = getSatuanAktivitas($row);
                    $persen = ($row['target_kerja'] > 0) ? ($row['hasil_kerja'] / $row['target_kerja']) * 100 : 0;
                    $badge_class = ($row['status_verifikasi'] == 'Disetujui') ? 'approved' : (($row['status_verifikasi'] == 'Ditolak') ? 'rejected' : 'pending');
                ?>
                <tr>
                    <td class="ps-4 fw-semibold" style="font-size:0.85rem;">
                        <?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                    <td><span class="job-badge"><?= htmlspecialchars($row['jenis_pekerjaan']) ?></span></td>
                    <td class="text-center"><span
                            class="ton-value"><?= formatHasil($row['hasil_kerja'], $satuan) ?></span></td>
                    <td class="text-center" style="color:var(--muted);">
                        <?= formatHasil($row['target_kerja'], $satuan) ?></td>
                    <td class="text-center fw-bold text-primary"><?= number_format($persen, 1) ?>%</td>
                    <td class="text-center pe-4"><span
                            class="status-badge <?= $badge_class ?>"><?= htmlspecialchars($row['status_verifikasi']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($chart_labels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('karyawanLineChart');
const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 240);
gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Capaian (%)',
            data: <?= json_encode($chart_data) ?>,
            borderColor: '#0f766e',
            backgroundColor: gradient,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                suggestedMax: 150,
                ticks: {
                    callback: v => v + '%',
                    font: {
                        family: 'Plus Jakarta Sans'
                    }
                },
                grid: {
                    color: '#f1f5f9'
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        family: 'Plus Jakarta Sans'
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include 'includes/karyawan_footer.php'; ?>