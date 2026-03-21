<?php
// =============================================
// pages/profile.php — Profil Siswa
// =============================================
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';
require_login('../auth/login.php');

$base   = '../';
$uid    = (int) $_SESSION['user_id'];
$flash  = ['type' => '', 'msg' => ''];

// ── Handle POST ───────────────────────────

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name    = esc($conn, $_POST['name']  ?? '');
    $email   = esc($conn, $_POST['email'] ?? '');
    $new_pw  = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $current = $_POST['current_password'] ?? '';

    $errors = [];

    if (empty($name))  $errors[] = 'Nama tidak boleh kosong.';
    if (empty($email)) $errors[] = 'Email tidak boleh kosong.';
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';

    // Cek email duplikat (kecuali milik sendiri)
    $dup = db_fetch_one($conn, "SELECT id FROM users WHERE email = '$email' AND id != $uid LIMIT 1");
    if ($dup) $errors[] = 'Email sudah dipakai akun lain.';

    // Kalau mau ganti password
    $pw_update = '';
    if (!empty($new_pw)) {
        $me = db_fetch_one($conn, "SELECT password FROM users WHERE id = $uid LIMIT 1");
        if (!password_verify($current, $me['password'])) {
            $errors[] = 'Password saat ini salah.';
        } elseif (strlen($new_pw) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($new_pw !== $confirm) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        } else {
            $hashed   = password_hash($new_pw, PASSWORD_BCRYPT);
            $pw_update = ", password = '$hashed'";
        }
    }

    if (empty($errors)) {
        db_execute($conn,
            "UPDATE users SET name = '$name', email = '$email' $pw_update WHERE id = $uid"
        );
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['email']     = $email;
        $flash = ['type' => 'success', 'msg' => 'Profil berhasil diperbarui!'];
    } else {
        $flash = ['type' => 'error', 'msg' => implode('<br>', $errors)];
    }
}

// ── Ambil data user (prepared statement agar semua kolom terbaca) ──
$stmt_u = mysqli_prepare($conn, "SELECT id, name, email, role, avatar, created_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_u, 'i', $uid);
mysqli_stmt_execute($stmt_u);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_u));
mysqli_stmt_close($stmt_u);

// ── Statistik quiz ────────────────────────
$quiz_stats = db_fetch_one($conn,
    "SELECT
        COUNT(*)                                    AS total_attempts,
        COUNT(DISTINCT subject_id)                  AS subjects_tried,
        COALESCE(AVG(score / total * 100), 0)       AS avg_score,
        COALESCE(MAX(score / total * 100), 0)       AS best_score
     FROM quiz_results
     WHERE user_id = $uid AND total > 0"
);

// ── Riwayat quiz lengkap ──────────────────
$tab = $_GET['tab'] ?? 'profile'; // 'profile' | 'history'

$quiz_history = [];
if ($tab === 'history') {
    $quiz_history = db_fetch_all($conn,
        "SELECT qr.*, s.name AS subject_name, s.slug
         FROM quiz_results qr
         JOIN subjects s ON s.id = qr.subject_id
         WHERE qr.user_id = $uid
         ORDER BY qr.taken_at DESC"
    );
}

// Best score per subject
$best_per_subject = db_fetch_all($conn,
    "SELECT s.name AS subject_name, s.slug,
            MAX(qr.score)  AS best_score,
            MAX(qr.total)  AS total,
            COUNT(*)        AS attempts
     FROM quiz_results qr
     JOIN subjects s ON s.id = qr.subject_id
     WHERE qr.user_id = $uid
     GROUP BY qr.subject_id
     ORDER BY best_score DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
    <style>
        .profile-layout {
            max-width: 1000px;
            margin: 40px auto 60px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* ── Kartu profil kiri ── */
        .profile-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 28px 20px;
            text-align: center;
            position: sticky;
            top: calc(var(--nav-h) + 20px);
        }
        .profile-avatar {
            width: 80px; height: 80px;
            background: var(--blue);
            color: var(--white);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: 800;
            margin: 0 auto 14px;
        }
        .profile-name  { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .profile-email { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; word-break: break-all; }
        .profile-role  {
            display: inline-block;
            padding: 3px 14px;
            background: var(--blue-light);
            color: var(--blue);
            border-radius: 20px;
            font-size: 12px; font-weight: 700;
            margin-bottom: 20px;
        }
        .profile-since { font-size: 12px; color: var(--text-muted); }

        /* Stat mini */
        .mini-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 20px 0;
        }
        .mini-stat {
            background: var(--bg);
            border-radius: 10px;
            padding: 12px 8px;
            text-align: center;
        }
        .mini-stat-num  { font-size: 22px; font-weight: 800; color: var(--blue); line-height: 1; }
        .mini-stat-label{ font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* ── Panel kanan ── */
        .profile-panel {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Tab nav */
        .tab-nav {
            display: flex;
            border-bottom: 1px solid var(--border);
        }
        .tab-btn {
            flex: 1;
            padding: 14px;
            font-size: 14px; font-weight: 700;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            transition: color var(--transition), border-bottom var(--transition);
            border-bottom: 3px solid transparent;
            text-decoration: none;
            text-align: center;
        }
        .tab-btn:hover { color: var(--blue); }
        .tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); }

        .tab-content { padding: 28px; }

        /* Form edit */
        .form-section-title {
            font-size: 15px; font-weight: 700;
            color: var(--blue);
            margin: 24px 0 14px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .form-section-title:first-child { margin-top: 0; border-top: none; padding-top: 0; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Riwayat quiz */
        .history-empty {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-muted);
        }
        .history-empty p { font-size: 15px; margin-bottom: 16px; }

        .subject-progress {
            margin-bottom: 16px;
        }
        .subject-progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .subject-progress-name  { font-weight: 700; font-size: 14px; }
        .subject-progress-score { font-size: 13px; color: var(--text-muted); }
        .progress-bar-bg {
            height: 8px;
            background: var(--bg);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            background: var(--blue);
            transition: width 0.6s ease;
        }
        .progress-bar-fill.good   { background: #28a745; }
        .progress-bar-fill.ok     { background: #ffc107; }
        .progress-bar-fill.low    { background: #dc3545; }

        /* Tabel riwayat */
        .history-table-wrap {
            overflow-x: auto;
            margin-top: 24px;
        }

        @media (max-width: 768px) {
            .profile-layout { grid-template-columns: 1fr; }
            .profile-card   { position: static; }
            .form-row       { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<div class="profile-layout">

    <!-- ── Kartu Profil Kiri ─────────────── -->
    <aside class="profile-card">
        <div class="profile-avatar">
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="profile-name"><?= htmlspecialchars($user['name'] ?? '') ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email'] ?? '-') ?></div>
        <span class="profile-role"><?= ucfirst($user['role'] ?? 'siswa') ?></span>

        <div class="mini-stats">
            <div class="mini-stat">
                <div class="mini-stat-num"><?= (int)($quiz_stats['total_attempts'] ?? 0) ?></div>
                <div class="mini-stat-label">Quiz<br>Dikerjakan</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-num"><?= (int)($quiz_stats['subjects_tried'] ?? 0) ?></div>
                <div class="mini-stat-label">Mapel<br>Dijelajahi</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-num"><?= round($quiz_stats['avg_score'] ?? 0) ?>%</div>
                <div class="mini-stat-label">Rata-rata<br>Skor</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-num"><?= round($quiz_stats['best_score'] ?? 0) ?>%</div>
                <div class="mini-stat-label">Skor<br>Terbaik</div>
            </div>
        </div>

        <div class="profile-since">
            Bergabung sejak <?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?>
        </div>
    </aside>

    <!-- ── Panel Kanan ───────────────────── -->
    <div class="profile-panel">

        <!-- Tab -->
        <nav class="tab-nav">
            <a href="profile.php?tab=profile"
               class="tab-btn <?= $tab === 'profile' ? 'active' : '' ?>">
                Edit Profil
            </a>
            <a href="profile.php?tab=history"
               class="tab-btn <?= $tab === 'history' ? 'active' : '' ?>">
                Riwayat Quiz
            </a>
        </nav>

        <div class="tab-content">

            <?php if ($tab === 'profile'): ?>
            <!-- ── Tab Edit Profil ─────── -->

            <?php if ($flash['msg']): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"
                 style="margin-bottom:20px;">
                <?= $flash['type'] === 'success' ? '✓ ' : '⚠️ ' ?>
                <?= $flash['msg'] ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="update_profile" value="1">

                <p class="form-section-title">Informasi Akun</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" required
                               value="<?= htmlspecialchars($user['name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                </div>

                <p class="form-section-title">Ganti Password <small style="font-weight:400;color:var(--text-muted)">(opsional)</small></p>
                <div class="form-group">
                    <label>Password Saat Ini</label>
                    <input type="password" name="current_password" placeholder="Masukkan password saat ini">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password Baru <small style="color:#999">(min. 6 karakter)</small></label>
                        <input type="password" name="new_password" placeholder="Password baru">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" placeholder="Ulangi password baru">
                    </div>
                </div>

                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </form>


            <?php elseif ($tab === 'history'): ?>
            <!-- ── Tab Riwayat Quiz ────── -->

            <?php if (empty($best_per_subject)): ?>
            <div class="history-empty">
                <p>Kamu belum pernah mengerjakan quiz.</p>
                <a href="subjects.php" class="btn-primary" style="display:inline-block;">
                    Mulai Belajar
                </a>
            </div>

            <?php else: ?>

            <!-- Progress per mapel -->
            <h3 style="font-size:15px;color:var(--blue);margin-bottom:16px;">
                Pencapaian per Mata Pelajaran
            </h3>
            <?php foreach ($best_per_subject as $bp):
                $pct = $bp['total'] > 0 ? round($bp['best_score'] / $bp['total'] * 100) : 0;
                $bar_class = $pct >= 70 ? 'good' : ($pct >= 40 ? 'ok' : 'low');
            ?>
            <div class="subject-progress">
                <div class="subject-progress-header">
                    <span class="subject-progress-name">
                        <?= htmlspecialchars($bp['subject_name']) ?>
                    </span>
                    <span class="subject-progress-score">
                        Terbaik <?= $pct ?>% &nbsp;·&nbsp; <?= $bp['attempts'] ?>x dikerjakan
                    </span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill <?= $bar_class ?>"
                         style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Tabel semua riwayat -->
            <h3 style="font-size:15px;color:var(--blue);margin:28px 0 14px;">
                Semua Riwayat Quiz
            </h3>
            <div class="history-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mata Pelajaran</th>
                            <th>Skor</th>
                            <th>Total</th>
                            <th>Nilai</th>
                            <th>Waktu</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($quiz_history as $i => $r):
                        $pct = $r['total'] > 0 ? round($r['score'] / $r['total'] * 100) : 0;
                        $color = $pct >= 70 ? '#28a745' : ($pct >= 40 ? '#ffc107' : '#dc3545');
                    ?>
                    <tr>
                        <td style="color:var(--text-muted);font-size:12px;"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($r['subject_name']) ?></td>
                        <td><strong><?= $r['score'] ?></strong></td>
                        <td><?= $r['total'] ?></td>
                        <td>
                            <span style="color:<?= $color ?>;font-weight:700;font-size:14px;">
                                <?= $pct ?>%
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                            <?= date('d M Y, H:i', strtotime($r['taken_at'])) ?>
                        </td>
                        <td>
                            <a href="quiz.php?slug=<?= urlencode($r['slug']) ?>"
                                style="color:var(--blue);font-size:12px;font-weight:700;white-space:nowrap;">
                                Ulangi →
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>