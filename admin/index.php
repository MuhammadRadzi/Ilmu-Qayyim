<?php
// =============================================
// admin/index.php — Dashboard Admin
// =============================================
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';
require_role('admin', '../index.php');

$base = '../';
$page = $_GET['page'] ?? 'dashboard';

// ── Handle POST actions ───────────────────

// Hapus user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $del_id = (int) $_POST['delete_user'];
    if ($del_id !== (int)$_SESSION['user_id']) { // jangan hapus diri sendiri
        db_execute($conn, "DELETE FROM users WHERE id = $del_id");
    }
    header('Location: index.php?page=users');
    exit;
}

// Update role user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $upd_id   = (int) esc($conn, $_POST['user_id']);
    $new_role = esc($conn, $_POST['role']);
    if (in_array($new_role, ['siswa', 'guru', 'admin'])) {
        db_execute($conn, "UPDATE users SET role = '$new_role' WHERE id = $upd_id");
    }
    header('Location: index.php?page=users');
    exit;
}

// Tambah mata pelajaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = esc($conn, $_POST['name']);
    $slug = esc($conn, strtolower(str_replace(' ', '-', $_POST['name'])));
    $desc = esc($conn, $_POST['description']);
    if (!empty($name)) {
        db_execute($conn,
            "INSERT INTO subjects (name, slug, description) VALUES ('$name', '$slug', '$desc')"
        );
    }
    header('Location: index.php?page=subjects');
    exit;
}

// Hapus mata pelajaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_subject'])) {
    $del_sid = (int) $_POST['delete_subject'];
    db_execute($conn, "DELETE FROM subjects WHERE id = $del_sid");
    header('Location: index.php?page=subjects');
    exit;
}

// ── Ambil data sesuai page ────────────────
$stats = [
    'users'    => db_fetch_one($conn, "SELECT COUNT(*) AS c FROM users")['c'] ?? 0,
    'subjects' => db_fetch_one($conn, "SELECT COUNT(*) AS c FROM subjects")['c'] ?? 0,
    'quizzes'  => db_fetch_one($conn, "SELECT COUNT(*) AS c FROM quiz_results")['c'] ?? 0,
    'siswa'    => db_fetch_one($conn, "SELECT COUNT(*) AS c FROM users WHERE role='siswa'")['c'] ?? 0,
];

$users    = ($page === 'users')    ? db_fetch_all($conn, "SELECT * FROM users ORDER BY created_at DESC") : [];
$subjects = ($page === 'subjects') ? db_fetch_all($conn, "SELECT * FROM subjects ORDER BY name ASC")    : [];
$recent_quiz = ($page === 'dashboard')
    ? db_fetch_all($conn,
        "SELECT qr.*, u.name AS user_name, s.name AS subject_name
         FROM quiz_results qr
         JOIN users u    ON u.id    = qr.user_id
         JOIN subjects s ON s.id    = qr.subject_id
         ORDER BY qr.taken_at DESC LIMIT 10")
    : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<div class="dash-layout">

    <!-- ── Sidebar ──────────────────────── -->
    <aside class="dash-sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php?page=dashboard" class="<?= $page==='dashboard' ? 'active':'' ?>">
                🏠 Dashboard
            </a></li>
            <li><a href="index.php?page=users" class="<?= $page==='users' ? 'active':'' ?>">
                👥 Kelola User
            </a></li>
            <li><a href="index.php?page=subjects" class="<?= $page==='subjects' ? 'active':'' ?>">
                📚 Mata Pelajaran
            </a></li>
            <li><a href="index.php?page=quiz_results" class="<?= $page==='quiz_results' ? 'active':'' ?>">
                📊 Hasil Quiz
            </a></li>
            <li><a href="../pages/quiz_manager.php">
                ✏️ Kelola Soal Quiz
            </a></li>
            <li><a href="../auth/logout.php" class="logout-link">🚪 Logout</a></li>
        </ul>
    </aside>

    <!-- ── Konten Utama ──────────────────── -->
    <main class="dash-main">

        <?php if ($page === 'dashboard'): ?>
        <!-- Dashboard -->
        <div class="dash-header">
            <h1>Dashboard</h1>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>

        <div class="stat-cards">
            <div class="stat-card">
                <h3>Total User</h3>
                <p><?= $stats['users'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Siswa</h3>
                <p><?= $stats['siswa'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Mata Pelajaran</h3>
                <p><?= $stats['subjects'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Quiz Dikerjakan</h3>
                <p><?= $stats['quizzes'] ?></p>
            </div>
        </div>

        <h2 style="font-size:17px;color:var(--blue);margin-bottom:14px;">Aktivitas Quiz Terbaru</h2>
        <?php if (!empty($recent_quiz)): ?>
        <table class="data-table">
            <thead>
                <tr><th>Siswa</th><th>Mata Pelajaran</th><th>Skor</th><th>Total</th><th>Waktu</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_quiz as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['user_name']) ?></td>
                    <td><?= htmlspecialchars($r['subject_name']) ?></td>
                    <td><strong><?= $r['score'] ?></strong></td>
                    <td><?= $r['total'] ?></td>
                    <td style="font-size:12px;color:var(--text-muted)">
                        <?= date('d M Y H:i', strtotime($r['taken_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--text-muted);">Belum ada aktivitas quiz.</p>
        <?php endif; ?>


        <?php elseif ($page === 'users'): ?>
        <!-- Kelola User -->
        <div class="dash-header">
            <h1>Kelola User</h1>
            <p>Total <?= count($users) ?> user terdaftar</p>
        </div>

        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Daftar</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $i => $u): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role" onchange="this.form.submit()"
                                style="font-size:12px;padding:3px 6px;border-radius:6px;border:1px solid var(--border);">
                            <option value="siswa" <?= $u['role']==='siswa' ? 'selected':'' ?>>Siswa</option>
                            <option value="guru"  <?= $u['role']==='guru'  ? 'selected':'' ?>>Guru</option>
                            <option value="admin" <?= $u['role']==='admin' ? 'selected':'' ?>>Admin</option>
                        </select>
                        <input type="hidden" name="update_role" value="1">
                    </form>
                </td>
                <td style="font-size:12px;color:var(--text-muted)">
                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                </td>
                <td>
                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['name']) ?>?')">
                        <button type="submit" name="delete_user" value="<?= $u['id'] ?>"
                                style="background:#e74c3c;color:#fff;border:none;padding:5px 12px;
                                       border-radius:6px;cursor:pointer;font-size:12px;">
                            Hapus
                        </button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:12px;color:var(--text-muted)">Kamu</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>


        <?php elseif ($page === 'subjects'): ?>
        <!-- Mata Pelajaran -->
        <div class="dash-header">
            <h1>Mata Pelajaran</h1>
        </div>

        <!-- Form tambah -->
        <div style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow);
                    padding:24px;margin-bottom:28px;">
            <h3 style="font-size:16px;margin-bottom:16px;color:var(--blue);">Tambah Mata Pelajaran</h3>
            <form method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div class="form-group" style="flex:1;min-width:180px;margin:0;">
                    <label>Nama Mata Pelajaran</label>
                    <input type="text" name="name" required placeholder="contoh: Kimia">
                </div>
                <div class="form-group" style="flex:2;min-width:240px;margin:0;">
                    <label>Deskripsi</label>
                    <input type="text" name="description" placeholder="Deskripsi singkat...">
                </div>
                <button type="submit" name="add_subject" class="btn-primary" style="height:42px;">
                    + Tambah
                </button>
            </form>
        </div>

        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nama</th><th>Slug</th><th>Deskripsi</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $i => $s): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                <td style="font-size:12px;color:var(--text-muted)"><?= $s['slug'] ?></td>
                <td style="font-size:13px;"><?= htmlspecialchars($s['description']) ?></td>
                <td>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Hapus <?= htmlspecialchars($s['name']) ?>? Semua chapter dan quiz akan ikut terhapus.')">
                        <button type="submit" name="delete_subject" value="<?= $s['id'] ?>"
                                style="background:#e74c3c;color:#fff;border:none;padding:5px 12px;
                                       border-radius:6px;cursor:pointer;font-size:12px;">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>


        <?php elseif ($page === 'quiz_results'): ?>
        <!-- Semua Hasil Quiz -->
        <div class="dash-header">
            <h1>Hasil Quiz</h1>
        </div>
        <?php
        $all_results = db_fetch_all($conn,
            "SELECT qr.*, u.name AS user_name, s.name AS subject_name
             FROM quiz_results qr
             JOIN users u    ON u.id    = qr.user_id
             JOIN subjects s ON s.id    = qr.subject_id
             ORDER BY qr.taken_at DESC"
        );
        ?>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Siswa</th><th>Mata Pelajaran</th><th>Skor</th><th>Total</th><th>%</th><th>Waktu</th></tr>
            </thead>
            <tbody>
            <?php foreach ($all_results as $i => $r): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['user_name']) ?></td>
                <td><?= htmlspecialchars($r['subject_name']) ?></td>
                <td><strong><?= $r['score'] ?></strong></td>
                <td><?= $r['total'] ?></td>
                <td>
                    <?php $pct = $r['total'] > 0 ? round($r['score']/$r['total']*100) : 0; ?>
                    <span style="color:<?= $pct >= 70 ? '#28a745' : '#dc3545' ?>;font-weight:700;">
                        <?= $pct ?>%
                    </span>
                </td>
                <td style="font-size:12px;color:var(--text-muted)">
                    <?= date('d M Y H:i', strtotime($r['taken_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($all_results)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">
                Belum ada data.
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php endif; ?>

    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>