<?php
// =============================================
// includes/navbar.php
// Cara pakai: require_once 'includes/navbar.php';
// Pastikan session sudah di-start sebelum include ini
// =============================================

// Tentukan base path relatif (karena kedalaman folder beda-beda)
// Halaman di root   → $base = ''
// Halaman di pages/ → $base = '../'
// Halaman di admin/ → $base = '../'
if (!isset($base)) $base = '';

$current_page = basename($_SERVER['PHP_SELF']);
$user = [
    'id'   => $_SESSION['user_id']   ?? null,
    'name' => $_SESSION['user_name'] ?? null,
    'role' => $_SESSION['role']      ?? null,
];

// Tentukan link dashboard sesuai role
$dashboard_url = match($user['role']) {
    'admin' => $base . 'admin/index.php',
    'guru'  => $base . 'pages/guru.php',
    default => $base . 'index.php',
};
?>

<nav id="navbar">
    <div class="nav-logo">
        <a href="<?= $base ?>index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
            <img src="<?= $base ?>assets/images/IQ-Contrast.png" alt="Ilmu Qayyim" class="nav-logo-img">
            <span class="nav-logo-text">Ilmu <br>Qayyim</span>
        </a>
    </div>

    <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">&#9776;</button>

    <ul class="nav-links" id="navLinks">
        <li><a href="<?= $base ?>index.php"          class="<?= $current_page === 'index.php'    ? 'active' : '' ?>">Home</a></li>
        <li><a href="<?= $base ?>pages/subjects.php" class="<?= $current_page === 'subjects.php' ? 'active' : '' ?>">Subjects</a></li>
        <li><a href="<?= $base ?>pages/contact.php"  class="<?= $current_page === 'contact.php'  ? 'active' : '' ?>">Contact</a></li>
        <li><a href="<?= $base ?>pages/about.php"    class="<?= $current_page === 'about.php'    ? 'active' : '' ?>">About</a></li>

        <?php if ($user['id']): ?>
        <!-- User sudah login → tampilkan dropdown profil -->
        <li class="nav-dropdown">
            <button class="nav-dropbtn" id="profileBtn">
                <span class="nav-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </span>
                <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> ▾
            </button>
            <div class="nav-dropdown-content" id="profileDropdown">
                <div class="nav-dropdown-header">
                    <div class="nav-dropdown-avatar">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="nav-dropdown-name"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="nav-dropdown-role"><?= ucfirst($user['role']) ?></div>
                    </div>
                </div>
                <div class="nav-dropdown-divider"></div>
                <a href="<?= $dashboard_url ?>">&#9776; Dashboard</a>
                <a href="<?= $base ?>pages/profile.php">&#128100; Profil Saya</a>
                <div class="nav-dropdown-divider"></div>
                <a href="<?= $base ?>auth/logout.php" class="nav-logout">
                    Keluar
                </a>
            </div>
        </li>
        <?php else: ?>
        <!-- Belum login → tampilkan tombol login -->
        <li>
            <a href="<?= $base ?>auth/login.php" class="nav-login-btn">Login</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>

<script>
(function() {
    // Hamburger toggle
    const btn  = document.getElementById('hamburgerBtn');
    const menu = document.getElementById('navLinks');
    if (btn && menu) {
        btn.addEventListener('click', () => menu.classList.toggle('open'));
    }

    // Profile dropdown toggle
    const profileBtn      = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => {
            profileDropdown.classList.remove('show');
        });
    }

    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    function handleScroll() {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    }
    window.addEventListener('scroll', handleScroll);
    handleScroll();
})();
</script>