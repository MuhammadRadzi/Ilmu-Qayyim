<?php
// pages/about.php
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';
$base = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>
<?php require_once '../includes/navbar.php'; ?>

<!-- Siapa Kami -->
<section class="about-section">
    <div class="text">
        <h2>Apa itu Ilmu Qayyim?</h2>
        <p>
            Ilmu Qayyim adalah platform pembelajaran online yang dibuat oleh siswa SMKIT Ibnul Qayyim
            sebagai media belajar mandiri bagi pelajar SMK. Website ini menyediakan materi berbagai
            mata pelajaran SMK, khususnya mata pelajaran umum. Tujuannya yaitu membantu siapa pun
            yang ingin memahami materi pelajaran SMK dengan mudah, praktis, dan bisa diakses kapan saja.
        </p>
    </div>
    <div class="image">
        <img src="../assets/images/iqis-logo.png" alt="IQIS Logo">
    </div>
</section>

<!-- Latar Belakang -->
<section class="about-alt">
    <div class="about-section" style="margin:0 auto;">
        <div class="image">
            <img src="../assets/images/team.jpg" alt="Tim" style="border-radius:14px;">
        </div>
        <div class="text">
            <h2>Latar Belakang</h2>
            <p>
                Website ini dibuat sebagai bentuk tugas yang diberikan oleh guru kami sejak Februari 2025
                dan terus dikembangkan secara bertahap oleh tim. Kami memilih untuk mengembangkan website
                bertema pendidikan karena kami melihat bahwa hasil pembelajaran siswa di sekolah masih kurang
                maksimal. Website ini bertujuan untuk membantu siapa pun, khususnya siswa SMK kelas 10,
                dalam memahami materi pelajaran dengan cara yang lebih lengkap dan leluasa.
            </p>
        </div>
    </div>
</section>

<!-- Visi Misi -->
<section class="about-section">
    <div class="text">
        <h2>Visi &amp; Misi</h2>
        <p>
            Kami memiliki visi untuk menciptakan generasi yang berilmu, beradab, dan bertakwa,
            dan menjadikan platform pembelajaran digital yang inspiratif, mudah diakses, dan bermakna
            bagi seluruh pelajar. Misi kami mencakup pendidikan yang holistik, berlandaskan nilai-nilai
            keislaman dan keterampilan abad 21, serta membina akhlak mulia dan semangat belajar sepanjang hayat.
        </p>
    </div>
    <div class="image">
        <img src="../assets/images/team.jpg" alt="Visi Misi" style="border-radius:14px;">
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>