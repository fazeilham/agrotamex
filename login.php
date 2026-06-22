<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['role'])) {
    header('location:' . ($_SESSION['role'] === 'manajer' ? 'dashboard_manajer.php' : 'dashboard_karyawan.php'));
    exit();
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama']     = $data['nama_lengkap'];
        $_SESSION['role']     = $data['role'];

        header('location:' . ($data['role'] === 'manajer' ? 'dashboard_manajer.php' : 'dashboard_karyawan.php'));
        exit();
    }
    $error = 'Username atau password salah!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISPKO | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/manajer.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-wrap { width: 100%; max-width: 420px; }
        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-brand .logo-icon {
            width: 56px; height: 56px;
            margin: 0 auto 16px;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #10b981, #0f766e);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: white;
        }
        .login-brand h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
            color: var(--text);
        }
        .login-brand p { color: var(--muted); margin: 6px 0 0; font-size: 0.875rem; }
        .login-panel {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.04);
        }
        .login-hint {
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.78rem;
            color: #0f766e;
            margin-top: 20px;
        }
        .login-hint strong { display: block; margin-bottom: 6px; }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="login-brand">
        <div class="logo-icon"><i class="bi bi-leaf-fill"></i></div>
        <h1>SISPKO</h1>
        <p>PT Agrotamex Sumindo Abadi</p>
    </div>

    <div class="login-panel">
        <h5 class="fw-bold mb-1">Masuk Sistem</h5>
        <p class="text-muted small mb-4">Sistem Pemantauan Produktivitas Karyawan</p>

        <?php if (!empty($error)): ?>
        <div class="alert-manajer alert-danger mb-4">
            <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control form-control-modern" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control form-control-modern" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login" class="btn-manajer">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <div class="login-hint">
            <strong><i class="bi bi-info-circle"></i> Akun Demo</strong>
            Manajer: <code>manajer</code> / <code>manajer123</code><br>
            Karyawan: <code>karyawan1</code> / <code>karyawan123</code>
        </div>
    </div>

    <p class="text-center text-muted small mt-4 mb-0">&copy; 2026 SISPKO — Perancangan Sistem Informasi Perkebunan</p>
</div>

</body>
</html>
