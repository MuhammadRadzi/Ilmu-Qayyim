<?php
session_start();
if (!empty($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_raw = trim($_POST['email']    ?? '');
    $password  = $_POST['password'] ?? '';

    if (empty($email_raw) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        // Pakai prepared statement supaya fetch semua kolom dengan benar
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email_raw);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['email']     = $user['email'];

            switch ($user['role']) {
                case 'admin': header('Location: ../admin/index.php'); break;
                case 'guru':  header('Location: ../pages/guru.php');  break;
                default:      header('Location: ../index.php');       break;
            }
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
    <style>
        body { height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#74b9ff,#0984e3); }
        .auth-box { position:relative; background:#fff; padding:36px 28px; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,.15); width:100%; max-width:360px; animation:fadeUp .5s ease; }
        .auth-box h1 { margin-bottom:24px; color:#2d3436; font-size:22px; text-align:center; }
        .back-btn { position:absolute; top:14px; left:14px; font-size:20px; color:#0984e3; text-decoration:none; }
        .alert-error { background:#ffe3e3; color:#d63031; padding:10px 14px; border-radius:8px; font-size:14px; margin-bottom:16px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:13px; color:#636e72; margin-bottom:6px; font-weight:600; }
        .form-group input { width:100%; padding:11px 14px; border:1.5px solid #dcdde1; border-radius:8px; font-size:14px; transition:border-color .3s; box-sizing:border-box; }
        .form-group input:focus { border-color:#0984e3; outline:none; }
        .btn-submit { width:100%; padding:13px; background:#0984e3; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; transition:background .3s; }
        .btn-submit:hover { background:#0770c2; }
        .auth-links { margin-top:16px; font-size:13px; text-align:center; color:#636e72; }
        .auth-links a { color:#0984e3; text-decoration:none; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
    </style>
</head>
<body>
<div class="auth-box">
    <a href="../index.php" class="back-btn">←</a>
    <h1>Login</h1>
    <?php if ($error): ?>
    <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="contoh@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-submit">Masuk</button>
    </form>
    <div class="auth-links">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
</div>
</body>
</html>