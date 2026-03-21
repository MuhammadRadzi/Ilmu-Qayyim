<?php
// =============================================
// pages/guru.php — Dashboard Guru
// =============================================
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';
require_role('guru', '../index.php');

$base = '../';
$page = $_GET['page'] ?? 'dashboard';

// ── Handle POST actions ───────────────────

// Tambah chapter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_chapter'])) {
    $subject_id = (int) $_POST['subject_id'];
    $title      = esc($conn, $_POST['title']);
    $video_url  = esc($conn, $_POST['video_url']);

    // Ambil order_num terakhir untuk subject ini
    $last = db_fetch_one($conn,
        "SELECT MAX(order_num) AS max_order FROM chapters WHERE subject_id = $subject_id"
    );
    $order_num = ($last['max_order'] ?? 0) + 1;

    if (!empty($title) && $subject_id > 0) {
        db_execute($conn,
            "INSERT INTO chapters (subject_id, title, video_url, order_num)
             VALUES ($subject_id, '$title', '$video_url', $order_num)"
        );
        $_SESSION['flash'] = 'Chapter berhasil ditambahkan.';
    }
    header('Location: guru.php?page=chapters&subject_id=' . $subject_id);
    exit;
}

// Edit chapter (load data ke form via GET)
// Save edit chapter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_chapter'])) {
    $chap_id    = (int) $_POST['chap_id'];
    $subject_id = (int) $_POST['subject_id'];
    $title      = esc($conn, $_POST['title']);
    $video_url  = esc($conn, $_POST['video_url']);
    $order_num  = (int) $_POST['order_num'];

    db_execute($conn,
        "UPDATE chapters SET title = '$title', video_url = '$video_url', order_num = $order_num
         WHERE id = $chap_id"
    );
    $_SESSION['flash'] = 'Chapter berhasil diperbarui.';
    header('Location: guru.php?page=chapters&subject_id=' . $subject_id);
    exit;
}

// Hapus chapter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_chapter'])) {
    $chap_id    = (int) $_POST['delete_chapter'];
    $subject_id = (int) $_POST['subject_id'];
    db_execute($conn, "DELETE FROM chapters WHERE id = $chap_id");
    $_SESSION['flash'] = 'Chapter berhasil dihapus.';
    header('Location: guru.php?page=chapters&subject_id=' . $subject_id);
    exit;
}

// Naik/turun urutan chapter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder'])) {
    $chap_id    = (int) $_POST['chap_id'];
    $direction  = $_POST['direction']; // 'up' | 'down'
    $subject_id = (int) $_POST['subject_id'];

    $current = db_fetch_one($conn, "SELECT * FROM chapters WHERE id = $chap_id");
    if ($current) {
        $cur_order = (int) $current['order_num'];
        if ($direction === 'up') {
            $swap = db_fetch_one($conn,
                "SELECT * FROM chapters WHERE subject_id = $subject_id AND order_num < $cur_order
                 ORDER BY order_num DESC LIMIT 1"
            );
        } else {
            $swap = db_fetch_one($conn,
                "SELECT * FROM chapters WHERE subject_id = $subject_id AND order_num > $cur_order
                 ORDER BY order_num ASC LIMIT 1"
            );
        }
        if ($swap) {
            $swap_order = (int) $swap['order_num'];
            $swap_id    = (int) $swap['id'];
            db_execute($conn, "UPDATE chapters SET order_num = $swap_order WHERE id = $chap_id");
            db_execute($conn, "UPDATE chapters SET order_num = $cur_order  WHERE id = $swap_id");
        }
    }
    header('Location: guru.php?page=chapters&subject_id=' . $subject_id);
    exit;
}

// ── Data ─────────────────────────────────
$subjects = db_fetch_all($conn, "SELECT * FROM subjects ORDER BY name ASC");

// Untuk page chapters
$selected_subject = null;
$chapters         = [];
$editing_chapter  = null;

if ($page === 'chapters') {
    $subject_id = (int) ($_GET['subject_id'] ?? 0);
    if ($subject_id > 0) {
        $selected_subject = db_fetch_one($conn,
            "SELECT * FROM subjects WHERE id = $subject_id LIMIT 1"
        );
        $chapters = db_fetch_all($conn,
            "SELECT * FROM chapters WHERE subject_id = $subject_id ORDER BY order_num ASC"
        );
    }
    // Load data chapter yang akan diedit
    if (isset($_GET['edit_chap'])) {
        $edit_id = (int) $_GET['edit_chap'];
        $editing_chapter = db_fetch_one($conn, "SELECT * FROM chapters WHERE id = $edit_id LIMIT 1");
    }
}

// Statistik untuk dashboard
$stats = [
    'subjects' => count($subjects),
    'chapters' => db_fetch_one($conn, "SELECT COUNT(*) AS c FROM chapters")['c'] ?? 0,
    'quizzes'  => db_fetch_one($conn, "SELECT COUNT(*) AS c FROM quiz_questions")['c'] ?? 0,
];

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
    <style>
        .chapter-row {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 10px;
            transition: box-shadow 0.2s;
        }
        .chapter-row:hover { box-shadow: var(--shadow); }
        .chapter-order {
            width: 32px; height: 32px;
            background: var(--blue-light);
            color: var(--blue);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            flex-shrink: 0;
        }
        .chapter-info { flex: 1; min-width: 0; }
        .chapter-title { font-weight: 700; font-size: 15px; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .chapter-url   { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .chapter-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .btn-sm {
            padding: 5px 12px; font-size: 12px; font-weight: 700;
            border-radius: 6px; border: none; cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-sm:hover { opacity: 0.85; }
        .btn-edit   { background: var(--blue-light); color: var(--blue); }
        .btn-delete { background: #ffe3e3; color: #d63031; }
        .btn-order  { background: var(--bg); color: var(--text-muted); padding: 5px 8px; }
        .subject-select-card {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .subject-select-card:hover {
            border-color: var(--blue);
            box-shadow: var(--shadow);
        }
        .subject-select-card .chap-count {
            font-size: 12px;
            color: var(--text-muted);
            background: var(--blue-light);
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .edit-form-box {
            background: var(--blue-light);
            border: 1.5px solid var(--blue);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .edit-form-box h3 { color: var(--blue); font-size: 15px; margin-bottom: 14px; }
        .video-preview {
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 16/9;
            max-width: 360px;
            margin-top: 10px;
        }
        .video-preview iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<div class="dash-layout">

    <!-- ── Sidebar ──────────────────────── -->
    <aside class="dash-sidebar">
        <h2>Guru Panel</h2>
        <ul>
            <li><a href="guru.php?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>">
                🏠 Dashboard
            </a></li>
            <li><a href="guru.php?page=subjects" class="<?= $page==='subjects'||$page==='chapters'?'active':'' ?>">
                📚 Kelola Materi
            </a></li>
            <li><a href="../auth/logout.php" class="logout-link">🚪 Logout</a></li>
        </ul>
    </aside>

    <!-- ── Konten Utama ──────────────────── -->
    <main class="dash-main">

        <!-- Flash message -->
        <?php if ($flash): ?>
        <div class="alert alert-success" style="margin-bottom:20px;">✓ <?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
        <!-- ── Dashboard ─────────────────── -->
        <div class="dash-header">
            <h1>Dashboard Guru</h1>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>

        <div class="stat-cards">
            <div class="stat-card">
                <h3>Mata Pelajaran</h3>
                <p><?= $stats['subjects'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Chapter</h3>
                <p><?= $stats['chapters'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Soal Quiz</h3>
                <p><?= $stats['quizzes'] ?></p>
            </div>
        </div>

        <div style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);padding:24px;">
            <h2 style="font-size:16px;color:var(--blue);margin-bottom:16px;">Mulai Kelola Materi</h2>
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
                Pilih mata pelajaran untuk menambah atau mengedit chapter dan video materi.
            </p>
            <a href="guru.php?page=subjects" class="btn-primary" style="display:inline-block;">
                Lihat Semua Mata Pelajaran →
            </a>
        </div>


        <?php elseif ($page === 'subjects'): ?>
        <!-- ── Pilih Mata Pelajaran ──────── -->
        <div class="dash-header">
            <h1>Kelola Materi</h1>
            <p>Pilih mata pelajaran yang ingin kamu kelola chapternya.</p>
        </div>

        <?php foreach ($subjects as $s):
            $sid_s = (int) $s['id'];
            $chap_count = db_fetch_one($conn,
                "SELECT COUNT(*) AS c FROM chapters WHERE subject_id = $sid_s"
            )['c'] ?? 0;
        ?>
        <a href="guru.php?page=chapters&subject_id=<?= $sid_s ?>" class="subject-select-card">
            <div>
                <div style="font-weight:700;font-size:16px;"><?= htmlspecialchars($s['name']) ?></div>
                <div style="font-size:13px;color:var(--text-muted);margin-top:3px;">
                    <?= htmlspecialchars($s['description']) ?>
                </div>
            </div>
            <span class="chap-count"><?= $chap_count ?> chapter</span>
        </a>
        <?php endforeach; ?>


        <?php elseif ($page === 'chapters' && $selected_subject): ?>
        <!-- ── Kelola Chapter ────────────── -->
        <div class="dash-header">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <a href="guru.php?page=subjects"
                   style="color:var(--text-muted);font-size:13px;font-weight:600;">← Kembali</a>
                <h1><?= htmlspecialchars($selected_subject['name']) ?></h1>
            </div>
            <p><?= count($chapters) ?> chapter tersedia</p>
        </div>

        <!-- Form edit chapter (tampil kalau ada ?edit_chap=) -->
        <?php if ($editing_chapter): ?>
        <div class="edit-form-box">
            <h3>✏️ Edit Chapter</h3>
            <form method="POST" action="">
                <input type="hidden" name="save_chapter"  value="1">
                <input type="hidden" name="chap_id"       value="<?= $editing_chapter['id'] ?>">
                <input type="hidden" name="subject_id"    value="<?= $selected_subject['id'] ?>">
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                    <div class="form-group" style="flex:2;min-width:200px;margin:0;">
                        <label>Judul Chapter</label>
                        <input type="text" name="title" required
                               value="<?= htmlspecialchars($editing_chapter['title']) ?>">
                    </div>
                    <div class="form-group" style="flex:1;min-width:80px;margin:0;">
                        <label>Urutan</label>
                        <input type="number" name="order_num" min="1" required
                               value="<?= $editing_chapter['order_num'] ?>">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:12px;">
                    <label>URL Video YouTube (embed)</label>
                    <input type="text" name="video_url" id="editVideoUrl"
                           placeholder="https://www.youtube.com/embed/XXXXXXX"
                           value="<?= htmlspecialchars($editing_chapter['video_url'] ?? '') ?>"
                           oninput="updatePreview('editVideoUrl','editPreview')">
                    <small style="color:var(--text-muted);font-size:12px;">
                        Pakai URL format embed: youtube.com/embed/ID_VIDEO
                    </small>
                    <?php if (!empty($editing_chapter['video_url'])): ?>
                    <div class="video-preview" id="editPreview">
                        <iframe src="<?= htmlspecialchars($editing_chapter['video_url']) ?>"
                                allowfullscreen></iframe>
                    </div>
                    <?php else: ?>
                    <div id="editPreview"></div>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    <a href="guru.php?page=chapters&subject_id=<?= $selected_subject['id'] ?>"
                       style="padding:11px 20px;background:var(--bg);color:var(--text);
                              border-radius:8px;font-size:14px;font-weight:600;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Form tambah chapter baru -->
        <div style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);
                    padding:20px;margin-bottom:28px;">
            <h3 style="font-size:15px;color:var(--blue);margin-bottom:14px;">+ Tambah Chapter Baru</h3>
            <form method="POST" action="">
                <input type="hidden" name="add_chapter" value="1">
                <input type="hidden" name="subject_id"  value="<?= $selected_subject['id'] ?>">
                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                    <div class="form-group" style="flex:2;min-width:200px;margin:0;">
                        <label>Judul Chapter</label>
                        <input type="text" name="title" required
                               placeholder="contoh: Bab 1 - Eksponen">
                    </div>
                    <div class="form-group" style="flex:3;min-width:240px;margin:0;">
                        <label>URL Video YouTube (embed)</label>
                        <input type="text" name="video_url" id="addVideoUrl"
                               placeholder="https://www.youtube.com/embed/XXXXXXX"
                               oninput="updatePreview('addVideoUrl','addPreview')">
                        <div id="addPreview" style="margin-top:8px;"></div>
                    </div>
                    <div style="padding-top:22px;">
                        <button type="submit" class="btn-primary">Tambah</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Daftar chapter -->
        <?php if (empty($chapters)): ?>
        <div style="text-align:center;padding:48px;color:var(--text-muted);">
            Belum ada chapter. Tambahkan chapter pertama di atas!
        </div>
        <?php else: ?>
        <div id="chapterList">
            <?php foreach ($chapters as $i => $ch): ?>
            <div class="chapter-row">
                <div class="chapter-order"><?= $ch['order_num'] ?></div>

                <div class="chapter-info">
                    <div class="chapter-title"><?= htmlspecialchars($ch['title']) ?></div>
                    <?php if (!empty($ch['video_url'])): ?>
                    <div class="chapter-url">🎬 <?= htmlspecialchars($ch['video_url']) ?></div>
                    <?php else: ?>
                    <div class="chapter-url" style="color:#e17055;">⚠ Belum ada video</div>
                    <?php endif; ?>
                </div>

                <div class="chapter-actions">
                    <!-- Naik/turun urutan -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="reorder"    value="1">
                        <input type="hidden" name="chap_id"    value="<?= $ch['id'] ?>">
                        <input type="hidden" name="subject_id" value="<?= $selected_subject['id'] ?>">
                        <input type="hidden" name="direction"  value="up">
                        <button type="submit" class="btn-sm btn-order"
                                title="Pindah ke atas" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="reorder"    value="1">
                        <input type="hidden" name="chap_id"    value="<?= $ch['id'] ?>">
                        <input type="hidden" name="subject_id" value="<?= $selected_subject['id'] ?>">
                        <input type="hidden" name="direction"  value="down">
                        <button type="submit" class="btn-sm btn-order"
                                title="Pindah ke bawah" <?= $i === count($chapters)-1 ? 'disabled' : '' ?>>↓</button>
                    </form>

                    <!-- Edit -->
                    <a href="guru.php?page=chapters&subject_id=<?= $selected_subject['id'] ?>&edit_chap=<?= $ch['id'] ?>"
                       class="btn-sm btn-edit">Edit</a>

                    <!-- Hapus -->
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Hapus chapter \'<?= htmlspecialchars(addslashes($ch['title'])) ?>\'?')">
                        <input type="hidden" name="delete_chapter" value="<?= $ch['id'] ?>">
                        <input type="hidden" name="subject_id"     value="<?= $selected_subject['id'] ?>">
                        <button type="submit" class="btn-sm btn-delete">Hapus</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Tombol lihat di frontend -->
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
            <a href="../pages/subject_detail.php?slug=<?= urlencode($selected_subject['slug']) ?>"
               target="_blank"
               style="color:var(--blue);font-size:14px;font-weight:700;">
                Lihat di halaman materi →
            </a>
        </div>

        <?php elseif ($page === 'chapters'): ?>
        <!-- subject_id tidak valid -->
        <div style="text-align:center;padding:60px;color:var(--text-muted);">
            <p>Mata pelajaran tidak ditemukan.</p>
            <a href="guru.php?page=subjects" style="color:var(--blue);font-weight:700;margin-top:12px;display:inline-block;">
                ← Kembali
            </a>
        </div>
        <?php endif; ?>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Preview video YouTube otomatis saat input URL
function updatePreview(inputId, previewId) {
    const url     = document.getElementById(inputId).value.trim();
    const preview = document.getElementById(previewId);
    if (!preview) return;

    // Validasi apakah URL adalah embed YouTube
    if (url.includes('youtube.com/embed/') || url.includes('youtu.be/')) {
        // Konversi youtu.be ke embed kalau perlu
        let embedUrl = url;
        if (url.includes('youtu.be/')) {
            const id = url.split('youtu.be/')[1].split('?')[0];
            embedUrl = 'https://www.youtube.com/embed/' + id;
        }
        preview.innerHTML = `
            <div class="video-preview">
                <iframe src="${embedUrl}" allowfullscreen></iframe>
            </div>`;
    } else if (url === '') {
        preview.innerHTML = '';
    } else {
        preview.innerHTML = `<p style="color:#e17055;font-size:12px;margin-top:6px;">
            Pastikan URL format embed: https://www.youtube.com/embed/ID_VIDEO
        </p>`;
    }
}
</script>
</body>
</html>