<?php
// pages/contact.php
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';
$base    = '../';
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if (empty($nama) || empty($email) || empty($pesan)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Di sini bisa ditambah kirim email dengan mail() atau PHPMailer
        // Untuk sekarang cukup tampilkan pesan sukses
        $success = 'Pesan kamu sudah kami terima. Terima kasih, ' . htmlspecialchars($nama) . '!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>
<?php require_once '../includes/navbar.php'; ?>

<h1 class="section-title" style="padding-top:40px;">Hubungi Kami</h1>

<div class="contact-wrap">
    <!-- Form -->
    <div class="contact-form-box">
        <h2>Kirim Pesan</h2>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
            Ada pertanyaan atau masukan? Isi formulir di bawah ini.
        </p>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" required placeholder="Nama kamu"
                       value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="email@contoh.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <small style="color:var(--text-muted);font-size:12px;">
                    Kami tidak akan membagikan emailmu ke siapa pun.
                </small>
            </div>
            <div class="form-group">
                <label>Pesan</label>
                <textarea name="pesan" required placeholder="Tulis pesanmu di sini..."><?=
                    htmlspecialchars($_POST['pesan'] ?? '')
                ?></textarea>
            </div>
            <button type="submit" class="btn-primary">Kirim Pesan</button>
        </form>
    </div>

    <!-- Maps -->
    <div class="contact-map-box">
        <h2>Lokasi Kami</h2>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px;">
            SMKIT Ibnul Qayyim Islamic School, Makassar
        </p>
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d9491.842549100233!2d119.52879400000002!3d-5.093049000000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbefb98cf06e587%3A0xe048f9722d2bde85!2sIbnul%20Qayyim%20Islamic%20School%20(IQIS)%20Makassar!5e1!3m2!1sen!2sid!4v1743473213403!5m2!1sen!2sid"
            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>