<?php
// =============================================
// pages/subjects.php
// =============================================
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base     = '../';
$subjects = db_fetch_all($conn, "SELECT * FROM subjects ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Pelajaran | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<h1 class="section-title" style="padding-top:40px;">Mata Pelajaran</h1>
<p style="text-align:center;color:var(--text-muted);margin-bottom:36px;font-size:15px;">
    Pilih mata pelajaran yang ingin kamu pelajari
</p>

<div class="subjects-grid">
    <?php foreach ($subjects as $s): ?>
    <div class="subject-card">
        <img src="../<?= htmlspecialchars($s['image']) ?>"
             alt="<?= htmlspecialchars($s['name']) ?>"
             onerror="this.src='../assets/images/placeholder.png'">
        <div class="subject-card-body">
            <h3><?= htmlspecialchars($s['name']) ?></h3>
            <p><?= htmlspecialchars($s['description']) ?></p>
            <a href="subject_detail.php?slug=<?= urlencode($s['slug']) ?>">Buka Materi</a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($subjects)): ?>
    <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted);">
        Belum ada mata pelajaran.
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>