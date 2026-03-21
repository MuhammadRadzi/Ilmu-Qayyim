<?php
// =============================================
// pages/subject_detail.php
// =============================================
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base = '../';
$slug = esc($conn, $_GET['slug'] ?? '');

if (empty($slug)) {
    header('Location: subjects.php');
    exit;
}

// Ambil data subject
$subject = db_fetch_one($conn, "SELECT * FROM subjects WHERE slug = '$slug' LIMIT 1");
if (!$subject) {
    header('Location: subjects.php');
    exit;
}

$sid = (int) $subject['id'];

// Ambil semua chapter subject ini
$chapters = db_fetch_all($conn,
    "SELECT * FROM chapters WHERE subject_id = $sid ORDER BY order_num ASC"
);

// Chapter yang sedang ditampilkan (default = pertama)
$chap_id = (int) ($_GET['chap'] ?? ($chapters[0]['id'] ?? 0));
$current = null;
$prev_id = null;
$next_id = null;

foreach ($chapters as $i => $ch) {
    if ((int)$ch['id'] === $chap_id) {
        $current = $ch;
        $prev_id = $chapters[$i - 1]['id'] ?? null;
        $next_id = $chapters[$i + 1]['id'] ?? null;
        break;
    }
}

// Kalau chap_id tidak valid, pakai chapter pertama
if (!$current && !empty($chapters)) {
    $current = $chapters[0];
    $next_id = $chapters[1]['id'] ?? null;
    $chap_id = (int) $current['id'];
}

// Cek apakah ada quiz untuk subject ini
$has_quiz = db_fetch_one($conn,
    "SELECT id FROM quiz_questions WHERE subject_id = $sid LIMIT 1"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject['name']) ?> | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<div class="materi-layout">

    <!-- ── Sidebar ──────────────────────── -->
    <aside class="materi-sidebar">
        <a href="subjects.php" class="back-link">← <?= htmlspecialchars($subject['name']) ?></a>

        <?php if (!empty($chapters)): ?>
        <ul>
            <?php foreach ($chapters as $ch): ?>
            <li>
                <a href="?slug=<?= urlencode($slug) ?>&chap=<?= $ch['id'] ?>"
                   class="<?= (int)$ch['id'] === $chap_id ? 'active' : '' ?>">
                    <?= htmlspecialchars($ch['title']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p style="color:var(--text-muted);font-size:13px;margin-top:16px;">
            Belum ada chapter.
        </p>
        <?php endif; ?>

        <?php if ($has_quiz): ?>
        <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--border);">
            <a href="quiz.php?slug=<?= urlencode($slug) ?>"
               style="display:block;padding:11px;background:var(--blue);color:#fff;
                      text-align:center;border-radius:8px;font-weight:700;font-size:14px;">
                Mulai Quiz &#9654;
            </a>
        </div>
        <?php endif; ?>
    </aside>

    <!-- ── Konten Utama ──────────────────── -->
    <main class="materi-content">
        <?php if ($current): ?>

        <h2><?= htmlspecialchars($current['title']) ?></h2>

        <!-- Video embed -->
        <?php if (!empty($current['video_url'])): ?>
        <div class="video-wrap">
            <iframe src="<?= htmlspecialchars($current['video_url']) ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>
        </div>
        <?php else: ?>
        <div class="video-wrap" style="display:flex;align-items:center;justify-content:center;background:#eee;">
            <p style="color:var(--text-muted);">Video belum tersedia.</p>
        </div>
        <?php endif; ?>

        <!-- Navigasi antar chapter -->
        <div class="materi-nav">
            <?php if ($prev_id): ?>
            <a href="?slug=<?= urlencode($slug) ?>&chap=<?= $prev_id ?>" class="prev-btn">← Sebelumnya</a>
            <?php else: ?>
            <span></span>
            <?php endif; ?>

            <?php if ($next_id): ?>
            <a href="?slug=<?= urlencode($slug) ?>&chap=<?= $next_id ?>" class="next-btn">Berikutnya →</a>
            <?php elseif ($has_quiz): ?>
            <a href="quiz.php?slug=<?= urlencode($slug) ?>" class="next-btn">Selesai — Ambil Quiz →</a>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div style="padding:60px;text-align:center;color:var(--text-muted);">
            <p style="font-size:18px;">Belum ada materi untuk mata pelajaran ini.</p>
            <a href="subjects.php" style="display:inline-block;margin-top:16px;color:var(--blue);font-weight:700;">
                ← Kembali ke Subjects
            </a>
        </div>
        <?php endif; ?>
    </main>

</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>