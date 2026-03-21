<?php
// =============================================
// auth/register.php
// =============================================

session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = esc($conn, $_POST['name']     ?? '');
    $email    = esc($conn, $_POST['email']    ?? '');
    $password = $_POST['password']            ?? '';
    $confirm  = $_POST['confirm_password']    ?? '';

    // Validasi
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek email duplikat
        $existing = db_fetch_one($conn, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
        if ($existing) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $result = db_execute($conn, "INSERT INTO users (name, email, password, role)
                                         VALUES ('$name', '$email', '$hashed', 'siswa')");
            if ($result) {
                $success = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Gagal membuat akun. Coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
    <style>
        body { height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #74b9ff, #0984e3); }
        .auth-box { position: relative; background: #fff; padding: 36px 28px; border-radius: 14px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); width: 100%; max-width: 380px; animation: fadeUp 0.5s ease; }
        .auth-box h1 { margin-bottom: 24px; color: #2d3436; font-size: 22px; text-align: center; }
        .back-btn { position: absolute; top: 14px; left: 14px; font-size: 20px; color: #0984e3; text-decoration: none; }
        .alert-error   { background: #ffe3e3; color: #d63031; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; color: #636e72; margin-bottom: 5px; font-weight: 600; }
        .form-group input { width: 100%; padding: 11px 14px; border: 1.5px solid #dcdde1; border-radius: 8px; font-size: 14px; transition: border-color .3s; box-sizing: border-box; }
        .form-group input:focus { border-color: #0984e3; outline: none; }
        .btn-submit { width: 100%; padding: 13px; background: #0984e3; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background .3s; }
        .btn-submit:hover { background: #0770c2; }
        .auth-links { margin-top: 16px; font-size: 13px; text-align: center; color: #636e72; }
        .auth-links a { color: #0984e3; text-decoration: none; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body>
<div class="auth-box">
    <a href="../index.php" class="back-btn">←</a>
    <h1>Daftar Akun</h1>

    <?php if ($error):   ?><div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="name" required placeholder="Muhammad Radzi" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="contoh@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password <small style="color:#999">(min. 6 karakter)</small></label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm_password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-submit">Buat Akun</button>
    </form>

    <div class="auth-links">
        Sudah punya akun? <a href="login.php">Login</a>
    </div>
</div>
</body>
</html>