<?php

function daftarJenisPekerjaan() {
    return [
        'Pemanenan Kelapa Sawit' => [
            'satuan'         => 'Ton',
            'default_target' => 35,
            'step'           => 0.1,
            'placeholder'    => 'Contoh: 32.4',
        ],
        'Pemupukan Blok Lahan' => [
            'satuan'         => 'Hektar',
            'default_target' => 10,
            'step'           => 0.1,
            'placeholder'    => 'Contoh: 8.5',
        ],
        'Penyemprotan Gulma/Rumput' => [
            'satuan'         => 'Liter',
            'default_target' => 200,
            'step'           => 1,
            'placeholder'    => 'Contoh: 180',
        ],
    ];
}

function getInfoPekerjaan($jenis) {
    $daftar = daftarJenisPekerjaan();
    return $daftar[$jenis] ?? [
        'satuan'         => 'Unit',
        'default_target' => 1,
        'step'           => 0.1,
        'placeholder'    => 'Contoh: 10',
    ];
}

function getSatuanPekerjaan($jenis) {
    return getInfoPekerjaan($jenis)['satuan'];
}

function daftarNamaJenisPekerjaan() {
    return array_keys(daftarJenisPekerjaan());
}

function formatHasil($nilai, $satuan) {
    $num = is_numeric($nilai) ? (float) $nilai : 0;
    $formatted = (floor($num) == $num) ? number_format($num, 0) : number_format($num, 1);
    return $formatted . ' ' . $satuan;
}

function enrichTarget($target) {
    if (!$target) {
        return null;
    }
    $info = getInfoPekerjaan($target['jenis_pekerjaan'] ?? '');
    if (empty($target['satuan'])) {
        $target['satuan'] = $info['satuan'];
    }
    return $target;
}

function getSatuanAktivitas($row) {
    if (!empty($row['satuan'])) {
        return $row['satuan'];
    }
    return getSatuanPekerjaan($row['jenis_pekerjaan'] ?? '');
}

function ensureTargetTable($koneksi) {
    mysqli_query($koneksi, "
        CREATE TABLE IF NOT EXISTS target_harian (
            id_target INT AUTO_INCREMENT PRIMARY KEY,
            tanggal DATE NOT NULL UNIQUE,
            target_ton DECIMAL(10,2) NOT NULL DEFAULT 35,
            jenis_pekerjaan VARCHAR(100) NOT NULL,
            satuan VARCHAR(20) NOT NULL DEFAULT 'Ton',
            diupdate_oleh INT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    $cek = mysqli_query($koneksi, "SHOW COLUMNS FROM target_harian LIKE 'satuan'");
    if ($cek && mysqli_num_rows($cek) === 0) {
        mysqli_query($koneksi, "ALTER TABLE target_harian ADD COLUMN satuan VARCHAR(20) NOT NULL DEFAULT 'Ton' AFTER jenis_pekerjaan");
    }
}

function ensureAktivitasSatuan($koneksi) {
    $cek = mysqli_query($koneksi, "SHOW COLUMNS FROM aktivitas LIKE 'satuan'");
    if ($cek && mysqli_num_rows($cek) === 0) {
        mysqli_query($koneksi, "ALTER TABLE aktivitas ADD COLUMN satuan VARCHAR(20) NULL AFTER target_kerja");
    }
}

function getTargetByTanggal($koneksi, $tanggal) {
    ensureTargetTable($koneksi);
    $tanggal = mysqli_real_escape_string($koneksi, $tanggal);
    $q = mysqli_query($koneksi, "SELECT * FROM target_harian WHERE tanggal='$tanggal' LIMIT 1");
    if ($q && ($row = mysqli_fetch_assoc($q))) {
        return enrichTarget($row);
    }
    return null;
}

function getTargetHariIni($koneksi) {
    return getTargetByTanggal($koneksi, date('Y-m-d'));
}

function getTargetDefault($jenis = 'Pemanenan Kelapa Sawit') {
    $info = getInfoPekerjaan($jenis);
    return [
        'target_ton'      => $info['default_target'],
        'jenis_pekerjaan' => $jenis,
        'satuan'          => $info['satuan'],
        'tanggal'         => date('Y-m-d'),
    ];
}

function getTargetAktif($koneksi, $tanggal = null) {
    $tanggal = $tanggal ?? date('Y-m-d');
    $target = getTargetByTanggal($koneksi, $tanggal);
    if ($target) {
        return $target;
    }
    $def = getTargetDefault();
    $def['tanggal'] = $tanggal;
    return $def;
}

function simpanTargetHarian($koneksi, $tanggal, $target_ton, $jenis_pekerjaan, $id_manajer = null) {
    ensureTargetTable($koneksi);

    $tanggal = mysqli_real_escape_string($koneksi, $tanggal);
    $target_ton = (float) $target_ton;
    $jenis_pekerjaan = mysqli_real_escape_string($koneksi, $jenis_pekerjaan);
    $id_manajer = $id_manajer ? (int) $id_manajer : 'NULL';

    if ($target_ton <= 0) {
        return false;
    }

    $allowed = daftarNamaJenisPekerjaan();
    if (!in_array($jenis_pekerjaan, $allowed, true)) {
        return false;
    }

    $satuan = mysqli_real_escape_string($koneksi, getSatuanPekerjaan($jenis_pekerjaan));

    $existing = getTargetByTanggal($koneksi, $tanggal);
    if ($existing) {
        $sql = "UPDATE target_harian SET
                target_ton='$target_ton',
                jenis_pekerjaan='$jenis_pekerjaan',
                satuan='$satuan',
                diupdate_oleh=" . ($id_manajer === 'NULL' ? 'NULL' : $id_manajer) . "
                WHERE tanggal='$tanggal'";
    } else {
        $sql = "INSERT INTO target_harian (tanggal, target_ton, jenis_pekerjaan, satuan, diupdate_oleh)
                VALUES ('$tanggal', '$target_ton', '$jenis_pekerjaan', '$satuan', " . ($id_manajer === 'NULL' ? 'NULL' : $id_manajer) . ")";
    }

    return (bool) mysqli_query($koneksi, $sql);
}

function getRiwayatTarget($koneksi, $limit = 7) {
    ensureTargetTable($koneksi);
    $limit = (int) $limit;
    return mysqli_query($koneksi, "SELECT * FROM target_harian ORDER BY tanggal DESC LIMIT $limit");
}

function getSatuanMapJson() {
    $map = [];
    foreach (daftarJenisPekerjaan() as $nama => $info) {
        $map[$nama] = $info;
    }
    return json_encode($map, JSON_UNESCAPED_UNICODE);
}
