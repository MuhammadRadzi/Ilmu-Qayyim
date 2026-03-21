<?php
// =============================================
// pages/quiz_manager.php
// Kelola soal quiz — bisa diakses admin & guru
// =============================================
session_start();
require_once '../config/db.php';
require_once '../includes/auth_check.php';

// Hanya admin dan guru yang boleh akses
require_login('../auth/login.php');
if (!in_array($_SESSION['role'], ['admin', 'guru'])) {
    header('Location: ../index.php');
    exit;
}

$base  = '../';
$role  = $_SESSION['role'];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Ambil semua subject
$subjects = db_fetch_all($conn, "SELECT * FROM subjects ORDER BY name ASC");

// Subject yang dipilih
$selected_sid = (int) ($_GET['subject_id'] ?? 0);
$selected_subject = null;
$questions = [];

if ($selected_sid > 0) {
    $selected_subject = db_fetch_one($conn, "SELECT * FROM subjects WHERE id = $selected_sid LIMIT 1");
    if ($selected_subject) {
        $questions = db_fetch_all($conn,
            "SELECT * FROM quiz_questions WHERE subject_id = $selected_sid ORDER BY id ASC"
        );
        // Ambil opsi untuk setiap soal
        foreach ($questions as &$q) {
            $qid = (int) $q['id'];
            $q['options'] = db_fetch_all($conn,
                "SELECT * FROM quiz_options WHERE question_id = $qid ORDER BY id ASC"
            );
        }
        unset($q);
    }
}

// ── Handle POST ───────────────────────────

// Tambah soal baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $sid      = (int) $_POST['subject_id'];
    $question = trim($_POST['question'] ?? '');
    $options  = $_POST['options'] ?? [];
    $correct  = (int) ($_POST['correct'] ?? -1);

    if (!empty($question) && count($options) >= 2 && $correct >= 0) {
        // Insert soal
        $stmt = mysqli_prepare($conn,
            "INSERT INTO quiz_questions (subject_id, question) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'is', $sid, $question);
        mysqli_stmt_execute($stmt);
        $qid = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Insert opsi
        foreach ($options as $idx => $opt_text) {
            $opt_text = trim($opt_text);
            if (empty($opt_text)) continue;
            $is_correct = ($idx === $correct) ? 1 : 0;
            $stmt_opt = mysqli_prepare($conn,
                "INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt_opt, 'isi', $qid, $opt_text, $is_correct);
            mysqli_stmt_execute($stmt_opt);
            mysqli_stmt_close($stmt_opt);
        }

        $_SESSION['flash'] = 'Soal berhasil ditambahkan.';
    } else {
        $_SESSION['flash'] = 'ERROR: Isi soal, minimal 2 pilihan, dan pilih jawaban benar.';
    }
    header("Location: quiz_manager.php?subject_id=$sid");
    exit;
}

// Hapus soal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    $qid = (int) $_POST['delete_question'];
    $sid = (int) $_POST['subject_id'];
    db_execute($conn, "DELETE FROM quiz_questions WHERE id = $qid");
    $_SESSION['flash'] = 'Soal berhasil dihapus.';
    header("Location: quiz_manager.php?subject_id=$sid");
    exit;
}

// Edit soal — save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    $qid      = (int) $_POST['question_id'];
    $sid      = (int) $_POST['subject_id'];
    $question = trim($_POST['question'] ?? '');
    $options  = $_POST['options'] ?? [];
    $opt_ids  = $_POST['option_ids'] ?? [];
    $correct  = (int) ($_POST['correct'] ?? -1);

    if (!empty($question)) {
        // Update teks soal
        $stmt = mysqli_prepare($conn, "UPDATE quiz_questions SET question = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $question, $qid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Update tiap opsi
        foreach ($options as $idx => $opt_text) {
            $opt_text   = trim($opt_text);
            $oid        = (int) ($opt_ids[$idx] ?? 0);
            $is_correct = ($idx === $correct) ? 1 : 0;

            if ($oid > 0 && !empty($opt_text)) {
                $stmt_o = mysqli_prepare($conn,
                    "UPDATE quiz_options SET option_text = ?, is_correct = ? WHERE id = ?"
                );
                mysqli_stmt_bind_param($stmt_o, 'sii', $opt_text, $is_correct, $oid);
                mysqli_stmt_execute($stmt_o);
                mysqli_stmt_close($stmt_o);
            } elseif ($oid === 0 && !empty($opt_text)) {
                // Opsi baru (kalau ditambah)
                $stmt_o = mysqli_prepare($conn,
                    "INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt_o, 'isi', $qid, $opt_text, $is_correct);
                mysqli_stmt_execute($stmt_o);
                mysqli_stmt_close($stmt_o);
            }
        }
        $_SESSION['flash'] = 'Soal berhasil diperbarui.';
    }
    header("Location: quiz_manager.php?subject_id=$sid");
    exit;
}

// Soal yang sedang diedit
$editing_qid = (int) ($_GET['edit'] ?? 0);
$editing_q   = null;
if ($editing_qid > 0 && $selected_subject) {
    foreach ($questions as $q) {
        if ((int)$q['id'] === $editing_qid) {
            $editing_q = $q;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Quiz | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
    <style>
        .qm-layout {
            max-width: 960px;
            margin: 32px auto 60px;
            padding: 0 24px;
        }
        .qm-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }
        .qm-header h1 { font-size: 22px; color: var(--blue); }

        /* Pilih subject */
        .subject-select-wrap {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .subject-select-wrap label { font-weight: 700; font-size: 14px; white-space: nowrap; }
        .subject-select-wrap select {
            flex: 1;
            min-width: 200px;
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg);
        }
        .subject-select-wrap select:focus { border-color: var(--blue); outline: none; }

        /* Form tambah soal */
        .add-question-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 28px;
        }
        .add-question-box h3 {
            font-size: 15px; color: var(--blue);
            margin-bottom: 16px;
        }
        .options-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
        .option-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .option-row input[type=radio] { flex-shrink: 0; width: 18px; height: 18px; cursor: pointer; }
        .option-row input[type=text] {
            flex: 1;
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
        }
        .option-row input[type=text]:focus { border-color: var(--blue); outline: none; }
        .option-row .remove-opt {
            background: #ffe3e3; color: #d63031;
            border: none; border-radius: 6px;
            padding: 6px 10px; font-size: 14px;
            cursor: pointer; flex-shrink: 0;
        }
        .correct-hint {
            font-size: 12px; color: var(--text-muted);
            margin-bottom: 14px;
        }
        .correct-hint span { color: #28a745; font-weight: 700; }

        /* Kartu soal */
        .question-item {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            margin-bottom: 14px;
            transition: box-shadow var(--transition);
        }
        .question-item:hover { box-shadow: var(--shadow); }
        .question-item-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }
        .question-num {
            min-width: 28px; height: 28px;
            background: var(--blue);
            color: var(--white);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            flex-shrink: 0; margin-top: 2px;
        }
        .question-text { flex: 1; font-weight: 700; font-size: 15px; line-height: 1.4; }
        .question-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .btn-edit-q {
            padding: 5px 14px; font-size: 12px; font-weight: 700;
            background: var(--blue-light); color: var(--blue);
            border: none; border-radius: 6px; cursor: pointer;
            text-decoration: none; display: inline-block;
        }
        .btn-del-q {
            padding: 5px 14px; font-size: 12px; font-weight: 700;
            background: #ffe3e3; color: #d63031;
            border: none; border-radius: 6px; cursor: pointer;
        }
        .option-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px;
            font-size: 13px; margin: 4px 4px 0 0;
            border: 1.5px solid var(--border);
            background: var(--bg);
        }
        .option-pill.correct {
            background: #d4edda; border-color: #28a745; color: #155724; font-weight: 700;
        }
        .option-pill.correct::before { content: '✓ '; }

        /* Edit form box */
        .edit-q-box {
            background: var(--blue-light);
            border: 1.5px solid var(--blue);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .edit-q-box h3 { font-size: 15px; color: var(--blue); margin-bottom: 16px; }

        .empty-state {
            text-align: center; padding: 48px 20px;
            color: var(--text-muted);
        }
        .empty-state p { font-size: 15px; margin-bottom: 8px; }
    </style>
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<div class="qm-layout">
    <div class="qm-header">
        <div>
            <a href="<?= $role === 'admin' ? '../admin/index.php' : 'guru.php' ?>"
               style="font-size:13px;color:var(--text-muted);font-weight:600;">← Kembali ke Dashboard</a>
            <h1 style="margin-top:4px;">Kelola Soal Quiz</h1>
        </div>
        <?php if ($selected_subject): ?>
        <a href="quiz.php?slug=<?= urlencode($selected_subject['slug']) ?>" target="_blank"
           style="font-size:13px;color:var(--blue);font-weight:700;">
            Preview Quiz →
        </a>
        <?php endif; ?>
    </div>

    <!-- Flash -->
    <?php if ($flash): ?>
    <div class="alert <?= str_starts_with($flash, 'ERROR') ? 'alert-error' : 'alert-success' ?>"
         style="margin-bottom:20px;">
        <?= str_starts_with($flash, 'ERROR') ? '⚠️ ' . htmlspecialchars(substr($flash, 7)) : '✓ ' . htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <!-- Pilih mata pelajaran -->
    <div class="subject-select-wrap">
        <label>Mata Pelajaran:</label>
        <select onchange="location.href='quiz_manager.php?subject_id='+this.value">
            <option value="0">-- Pilih Mata Pelajaran --</option>
            <?php foreach ($subjects as $s): ?>
            <option value="<?= $s['id'] ?>"
                    <?= $s['id'] == $selected_sid ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php if ($selected_subject): ?>
        <span style="font-size:13px;color:var(--text-muted);">
            <?= count($questions) ?> soal
        </span>
        <?php endif; ?>
    </div>

    <?php if (!$selected_subject): ?>
    <div class="empty-state">
        <p>Pilih mata pelajaran di atas untuk melihat dan mengelola soal quiznya.</p>
    </div>
    <?php else: ?>

    <!-- Form Edit Soal -->
    <?php if ($editing_q): ?>
    <div class="edit-q-box">
        <h3>✏️ Edit Soal</h3>
        <form method="POST" action="">
            <input type="hidden" name="save_question" value="1">
            <input type="hidden" name="question_id"  value="<?= $editing_q['id'] ?>">
            <input type="hidden" name="subject_id"   value="<?= $selected_sid ?>">

            <div class="form-group" style="margin-bottom:14px;">
                <label>Teks Soal</label>
                <textarea name="question" required rows="2"
                          style="width:100%;padding:10px 14px;border:1.5px solid var(--border);
                                 border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;"
                ><?= htmlspecialchars($editing_q['question']) ?></textarea>
            </div>

            <p class="correct-hint">Pilih <span>radio button</span> di kiri untuk menandai jawaban yang benar.</p>
            <div class="options-list" id="editOptionsList">
                <?php foreach ($editing_q['options'] as $idx => $opt): ?>
                <div class="option-row">
                    <input type="radio" name="correct" value="<?= $idx ?>"
                           <?= $opt['is_correct'] ? 'checked' : '' ?> required>
                    <input type="hidden" name="option_ids[]" value="<?= $opt['id'] ?>">
                    <input type="text" name="options[]" required
                           value="<?= htmlspecialchars($opt['option_text']) ?>"
                           placeholder="Pilihan <?= chr(65+$idx) ?>">
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="quiz_manager.php?subject_id=<?= $selected_sid ?>"
                   style="padding:11px 20px;background:var(--bg);color:var(--text);
                          border-radius:8px;font-size:14px;font-weight:600;">
                    Batal
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Form Tambah Soal Baru -->
    <div class="add-question-box">
        <h3>+ Tambah Soal Baru</h3>
        <form method="POST" action="" id="addForm">
            <input type="hidden" name="add_question" value="1">
            <input type="hidden" name="subject_id"   value="<?= $selected_sid ?>">

            <div class="form-group" style="margin-bottom:14px;">
                <label>Teks Soal</label>
                <textarea name="question" required rows="2" id="newQuestion"
                          style="width:100%;padding:10px 14px;border:1.5px solid var(--border);
                                 border-radius:8px;font-size:14px;font-family:inherit;resize:vertical;"
                          placeholder="Tulis pertanyaan di sini..."></textarea>
            </div>

            <p class="correct-hint">Klik <span>radio button</span> di kiri untuk menandai jawaban yang benar.</p>
            <div class="options-list" id="addOptionsList">
                <div class="option-row">
                    <input type="radio" name="correct" value="0" required>
                    <input type="text" name="options[]" required placeholder="Pilihan A">
                    <button type="button" class="remove-opt" onclick="removeOption(this)" style="display:none;">✕</button>
                </div>
                <div class="option-row">
                    <input type="radio" name="correct" value="1" required>
                    <input type="text" name="options[]" required placeholder="Pilihan B">
                    <button type="button" class="remove-opt" onclick="removeOption(this)" style="display:none;">✕</button>
                </div>
                <div class="option-row">
                    <input type="radio" name="correct" value="2">
                    <input type="text" name="options[]" placeholder="Pilihan C (opsional)">
                    <button type="button" class="remove-opt" onclick="removeOption(this)">✕</button>
                </div>
                <div class="option-row">
                    <input type="radio" name="correct" value="3">
                    <input type="text" name="options[]" placeholder="Pilihan D (opsional)">
                    <button type="button" class="remove-opt" onclick="removeOption(this)">✕</button>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;align-items:center;">
                <button type="submit" class="btn-primary">Tambah Soal</button>
                <button type="button" onclick="addOption()"
                        style="padding:11px 18px;background:var(--bg);color:var(--blue);
                               border:1.5px solid var(--blue);border-radius:8px;
                               font-size:14px;font-weight:700;cursor:pointer;">
                    + Pilihan
                </button>
                <span style="font-size:12px;color:var(--text-muted);">Maks. 5 pilihan</span>
            </div>
        </form>
    </div>

    <!-- Daftar Soal -->
    <h2 style="font-size:16px;color:var(--blue);margin-bottom:14px;">
        Daftar Soal — <?= htmlspecialchars($selected_subject['name']) ?>
    </h2>

    <?php if (empty($questions)): ?>
    <div class="empty-state">
        <p>Belum ada soal untuk mata pelajaran ini.</p>
        <p style="font-size:13px;">Tambahkan soal pertama menggunakan form di atas.</p>
    </div>
    <?php else: ?>
    <?php foreach ($questions as $i => $q): ?>
    <div class="question-item">
        <div class="question-item-header">
            <div class="question-num"><?= $i + 1 ?></div>
            <div class="question-text"><?= htmlspecialchars($q['question']) ?></div>
            <div class="question-actions">
                <a href="quiz_manager.php?subject_id=<?= $selected_sid ?>&edit=<?= $q['id'] ?>"
                   class="btn-edit-q">Edit</a>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Hapus soal ini?')">
                    <input type="hidden" name="delete_question" value="<?= $q['id'] ?>">
                    <input type="hidden" name="subject_id"      value="<?= $selected_sid ?>">
                    <button type="submit" class="btn-del-q">Hapus</button>
                </form>
            </div>
        </div>
        <div>
            <?php foreach ($q['options'] as $opt): ?>
            <span class="option-pill <?= $opt['is_correct'] ? 'correct' : '' ?>">
                <?= htmlspecialchars($opt['option_text']) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
function addOption() {
    const list  = document.getElementById('addOptionsList');
    const rows  = list.querySelectorAll('.option-row');
    if (rows.length >= 5) return;

    const idx   = rows.length;
    const labels = ['A','B','C','D','E'];
    const row   = document.createElement('div');
    row.className = 'option-row';
    row.innerHTML = `
        <input type="radio" name="correct" value="${idx}">
        <input type="text" name="options[]" placeholder="Pilihan ${labels[idx]}">
        <button type="button" class="remove-opt" onclick="removeOption(this)">✕</button>
    `;
    list.appendChild(row);
    updateRadioValues();
}

function removeOption(btn) {
    const list = document.getElementById('addOptionsList');
    const rows = list.querySelectorAll('.option-row');
    if (rows.length <= 2) return; // minimal 2 opsi
    btn.closest('.option-row').remove();
    updateRadioValues();
}

function updateRadioValues() {
    const list  = document.getElementById('addOptionsList');
    const rows  = list.querySelectorAll('.option-row');
    rows.forEach((row, idx) => {
        const radio = row.querySelector('input[type=radio]');
        const text  = row.querySelector('input[type=text]');
        const rmBtn = row.querySelector('.remove-opt');
        if (radio) radio.value = idx;
        if (text)  text.setAttribute('placeholder', 'Pilihan ' + 'ABCDE'[idx]);
        if (rmBtn) rmBtn.style.display = idx < 2 ? 'none' : '';
    });
}
</script>
</body>
</html>