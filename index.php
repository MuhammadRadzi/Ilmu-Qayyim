<?php
// =============================================
// index.php — Halaman Utama
// =============================================
session_start();
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$base = '';

// Ambil semua mata pelajaran untuk carousel
$subjects = db_fetch_all($conn, "SELECT * FROM subjects ORDER BY id ASC");

// Ambil riwayat belajar user (kalau sudah login)
$recent_results = [];
if (is_logged_in()) {
    $uid = (int) $_SESSION['user_id'];
    $recent_results = db_fetch_all($conn,
        "SELECT qr.*, s.name AS subject_name, s.slug, s.image
         FROM quiz_results qr
         JOIN subjects s ON s.id = qr.subject_id
         WHERE qr.user_id = $uid
         ORDER BY qr.taken_at DESC
         LIMIT 5"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Ilmu Qayyim</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>

<?php require_once 'includes/navbar.php'; ?>

<!-- ── Hero Banner ───────────────────────── -->
<section class="banner">
    <img src="assets/images/gedung-iqis.jpg" alt="Gedung IQIS">
    <div class="banner-text">
        <h1>Belajar Dengan<br>Ilmu Qayyim</h1>
        <p>"Menuntut ilmu itu wajib atas setiap muslim" — Rasulullah ﷺ</p>
        <a href="pages/subjects.php" class="banner-btn">Mulai Belajar</a>
    </div>
</section>

<!-- ── Carousel Mata Pelajaran ───────────── -->
<h2 class="section-title">Mata Pelajaran</h2>

<div class="carousel-wrap">
    <button class="carousel-btn prev" id="prevBtn">&#10094;</button>
    <div class="carousel-track" id="carouselTrack">
        <?php foreach ($subjects as $s): ?>
        <div class="card">
            <img src="<?= htmlspecialchars($s['image']) ?>"
                 alt="<?= htmlspecialchars($s['name']) ?>"
                 onerror="this.src='assets/images/placeholder.png'">
            <div class="card-body">
                <div class="card-title"><?= htmlspecialchars($s['name']) ?></div>
                <div class="card-desc"><?= htmlspecialchars($s['description']) ?></div>
                <a href="pages/subject_detail.php?slug=<?= urlencode($s['slug']) ?>" class="card-btn">
                    Buka Materi
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-btn next" id="nextBtn">&#10095;</button>
</div>

<!-- ── Riwayat Quiz (kalau sudah login) ──── -->
<?php if (is_logged_in() && !empty($recent_results)): ?>
<h2 class="section-title">Riwayat Quiz Kamu</h2>
<div style="max-width:900px;margin:0 auto 60px;padding:0 24px;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mata Pelajaran</th>
                <th>Skor</th>
                <th>Total Soal</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_results as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['subject_name']) ?></td>
                <td><strong><?= $r['score'] ?></strong></td>
                <td><?= $r['total'] ?></td>
                <td style="font-size:12px;color:var(--text-muted)">
                    <?= date('d M Y, H:i', strtotime($r['taken_at'])) ?>
                </td>
                <td>
                    <a href="pages/quiz.php?slug=<?= urlencode($r['slug']) ?>"
                       style="color:var(--blue);font-size:13px;font-weight:700;">
                        Ulangi
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php elseif (!is_logged_in()): ?>
<!-- CTA untuk login -->
<div style="text-align:center;padding:40px 20px 60px;background:var(--blue-light);margin-bottom:0;">
    <h3 style="color:var(--blue);font-size:22px;margin-bottom:10px;">Ingin lacak progress belajarmu?</h3>
    <p style="color:var(--text-muted);margin-bottom:20px;">Login untuk menyimpan hasil quiz dan riwayat belajar.</p>
    <a href="auth/login.php" class="banner-btn">Login Sekarang</a>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

<script>
(function() {
    const track   = document.getElementById('carouselTrack');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    if (!track) return;

    let idx = 0;
    let autoTimer;

    function getVisible() {
        const w = track.parentElement.offsetWidth;
        if (w >= 1024) return 3;
        if (w >= 640)  return 2;
        return 1;
    }

    function getCards() {
        return track.querySelectorAll('.card');
    }

    function updateSlide() {
        const cards   = getCards();
        const visible = getVisible();
        const max     = cards.length - visible;
        if (idx < 0) idx = max;
        if (idx > max) idx = 0;
        const cardW = cards[0].offsetWidth + 20; // width + margin
        track.style.transform = `translateX(-${idx * cardW}px)`;
    }

    function next() { idx++; updateSlide(); resetAuto(); }
    function prev() { idx--; updateSlide(); resetAuto(); }

    function resetAuto() {
        clearInterval(autoTimer);
        autoTimer = setInterval(next, 3500);
    }

    nextBtn.addEventListener('click', next);
    prevBtn.addEventListener('click', prev);
    window.addEventListener('resize', updateSlide);
    resetAuto();
})();
</script>
</body>
</html>