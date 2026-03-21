<?php
// =============================================
// pages/quiz.php
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

$subject = db_fetch_one($conn, "SELECT * FROM subjects WHERE slug = '$slug' LIMIT 1");
if (!$subject) {
    header('Location: subjects.php');
    exit;
}

$sid = (int) $subject['id'];

// Ambil semua soal + pilihan
$questions = db_fetch_all($conn,
    "SELECT * FROM quiz_questions WHERE subject_id = $sid ORDER BY id ASC"
);

$result_data = null; // data hasil quiz

// ── Handle submit quiz ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $score = 0;
    $total = count($questions);
    $answers = []; // [question_id => selected_option_id]

    foreach ($questions as $q) {
        $qid        = (int) $q['id'];
        $chosen_id  = (int) ($_POST['q_' . $qid] ?? 0);
        $answers[$qid] = $chosen_id;

        if ($chosen_id > 0) {
            $opt = db_fetch_one($conn,
                "SELECT is_correct FROM quiz_options WHERE id = $chosen_id AND question_id = $qid LIMIT 1"
            );
            if ($opt && $opt['is_correct'] == 1) $score++;
        }
    }

    // Simpan ke database kalau sudah login
    if (is_logged_in()) {
        $uid = (int) $_SESSION['user_id'];
        db_execute($conn,
            "INSERT INTO quiz_results (user_id, subject_id, score, total)
             VALUES ($uid, $sid, $score, $total)"
        );
    }

    // Ambil jawaban benar untuk ditampilkan
    $correct_map = []; // [question_id => correct_option_id]
    foreach ($questions as $q) {
        $qid = (int) $q['id'];
        $correct = db_fetch_one($conn,
            "SELECT id FROM quiz_options WHERE question_id = $qid AND is_correct = 1 LIMIT 1"
        );
        $correct_map[$qid] = $correct ? (int)$correct['id'] : 0;
    }

    $result_data = [
        'score'       => $score,
        'total'       => $total,
        'percent'     => $total > 0 ? round($score / $total * 100) : 0,
        'answers'     => $answers,
        'correct_map' => $correct_map,
    ];
}

// Ambil opsi untuk setiap soal
$options_map = []; // [question_id => [options]]
foreach ($questions as $q) {
    $qid = (int) $q['id'];
    $options_map[$qid] = db_fetch_all($conn,
        "SELECT * FROM quiz_options WHERE question_id = $qid ORDER BY id ASC"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz <?= htmlspecialchars($subject['name']) ?> | Ilmu Qayyim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="shortcut icon" href="../assets/images/IQ-Transparent.png" type="image/x-icon">
</head>
<body>

<?php require_once '../includes/navbar.php'; ?>

<div class="quiz-wrap">

    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <a href="subject_detail.php?slug=<?= urlencode($slug) ?>"
               style="color:var(--text-muted);font-size:13px;display:block;margin-bottom:4px;">
                ← Kembali ke Materi
            </a>
            <h1 style="color:var(--blue);font-size:22px;">
                Quiz: <?= htmlspecialchars($subject['name']) ?>
            </h1>
        </div>
        <div class="quiz-info"><?= count($questions) ?> Soal</div>
    </div>

    <?php if (empty($questions)): ?>
    <!-- Belum ada soal -->
    <div style="text-align:center;padding:80px 20px;color:var(--text-muted);">
        <p style="font-size:18px;margin-bottom:16px;">Belum ada soal quiz untuk mata pelajaran ini.</p>
        <a href="subjects.php" class="banner-btn">Kembali ke Subjects</a>
    </div>

    <?php elseif ($result_data): ?>
    <!-- ── Hasil Quiz ───────────────────── -->
    <div class="quiz-result">
        <div class="score-num"><?= $result_data['percent'] ?>%</div>
        <p style="font-size:18px;margin-bottom:4px;">
            Kamu menjawab benar <strong><?= $result_data['score'] ?></strong>
            dari <strong><?= $result_data['total'] ?></strong> soal
        </p>
        <?php if (!is_logged_in()): ?>
        <p style="color:var(--text-muted);font-size:13px;margin-top:8px;">
            <a href="../auth/login.php" style="color:var(--blue);font-weight:700;">Login</a>
            untuk menyimpan hasil quizmu.
        </p>
        <?php else: ?>
        <p style="color:#28a745;font-size:13px;margin-top:8px;">✓ Hasil tersimpan</p>
        <?php endif; ?>
        <div style="margin-top:20px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="quiz.php?slug=<?= urlencode($slug) ?>">Ulangi Quiz</a>
            <a href="subjects.php" style="background:var(--bg);color:var(--blue);border:2px solid var(--blue);">
                Subjects Lain
            </a>
        </div>
    </div>

    <!-- Review jawaban -->
    <h2 style="color:var(--blue);font-size:18px;margin:36px 0 16px;">Review Jawaban</h2>
    <?php foreach ($questions as $i => $q):
        $qid       = (int) $q['id'];
        $chosen    = $result_data['answers'][$qid] ?? 0;
        $correct   = $result_data['correct_map'][$qid] ?? 0;
        $opts      = $options_map[$qid] ?? [];
    ?>
    <div class="question-card">
        <h3><?= ($i + 1) ?>. <?= htmlspecialchars($q['question']) ?></h3>
        <?php foreach ($opts as $opt):
            $oid = (int) $opt['id'];
            $cls = '';
            if ($oid === $correct)                         $cls = 'correct';
            elseif ($oid === $chosen && $oid !== $correct) $cls = 'wrong';
        ?>
        <label class="option-label <?= $cls ?>" style="cursor:default;">
            <?= htmlspecialchars($opt['option_text']) ?>
            <?php if ($oid === $correct): ?> ✓<?php endif; ?>
            <?php if ($oid === $chosen && $oid !== $correct): ?> ✗<?php endif; ?>
        </label>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <!-- ── Form Quiz ────────────────────── -->
    <?php if (!is_logged_in()): ?>
    <div class="alert alert-info" style="margin-bottom:20px;">
        <a href="../auth/login.php" style="font-weight:700;">Login</a>
        agar hasil quizmu bisa tersimpan.
    </div>
    <?php endif; ?>

    <form method="POST" action="" id="quizForm">
        <?php foreach ($questions as $i => $q):
            $qid  = (int) $q['id'];
            $opts = $options_map[$qid] ?? [];
        ?>
        <div class="question-card">
            <h3><?= ($i + 1) ?>. <?= htmlspecialchars($q['question']) ?></h3>
            <?php foreach ($opts as $opt): ?>
            <label class="option-label">
                <input type="radio" name="q_<?= $qid ?>" value="<?= $opt['id'] ?>" required>
                <?= htmlspecialchars($opt['option_text']) ?>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <button type="submit" name="submit_quiz" class="quiz-submit-btn">
            Kumpulkan Jawaban
        </button>
    </form>

    <script>
    // Konfirmasi sebelum submit
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        const total    = <?= count($questions) ?>;
        const answered = document.querySelectorAll('input[type=radio]:checked').length;
        if (answered < total) {
            if (!confirm(`Kamu baru menjawab ${answered} dari ${total} soal. Tetap kumpulkan?`)) {
                e.preventDefault();
            }
        }
    });

    // Highlight label saat dipilih
    document.querySelectorAll('input[type=radio]').forEach(radio => {
        radio.addEventListener('change', function() {
            const name = this.name;
            document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
                r.closest('.option-label').style.borderColor = '';
                r.closest('.option-label').style.background  = '';
            });
            this.closest('.option-label').style.borderColor = 'var(--blue)';
            this.closest('.option-label').style.background  = 'var(--blue-light)';
        });
    });
    </script>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>