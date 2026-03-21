<?php
// =============================================
// includes/footer.php
// Cara pakai: require_once 'includes/footer.php';
// =============================================
if (!isset($base)) $base = '';
?>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <img src="<?= $base ?>assets/images/IQ-Contrast.png" alt="Ilmu Qayyim" id="scrollUpBtn">
            <span>Ilmu Qayyim</span>
        </div>

        <p class="footer-tagline">
            Platform pembelajaran online SMKIT Ibnul Qayyim
        </p>

        <div class="footer-socials">
            <a href="https://facebook.com"  target="_blank" rel="noopener" aria-label="Facebook">
                <img src="<?= $base ?>assets/images/socmed/Facebook.png"  alt="Facebook">
            </a>
            <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram">
                <img src="<?= $base ?>assets/images/socmed/Instagram.png" alt="Instagram">
            </a>
            <a href="https://tiktok.com"    target="_blank" rel="noopener" aria-label="TikTok">
                <img src="<?= $base ?>assets/images/socmed/TikTok.png"    alt="TikTok">
            </a>
            <a href="https://youtube.com"   target="_blank" rel="noopener" aria-label="YouTube">
                <img src="<?= $base ?>assets/images/socmed/Youtube.png"   alt="YouTube">
            </a>
            <a href="https://twitter.com"   target="_blank" rel="noopener" aria-label="Twitter">
                <img src="<?= $base ?>assets/images/socmed/twitter.png"   alt="Twitter">
            </a>
            <a href="https://wa.me/"        target="_blank" rel="noopener" aria-label="WhatsApp">
                <img src="<?= $base ?>assets/images/socmed/WhatsApp.png"  alt="WhatsApp">
            </a>
            <a href="https://telegram.org"  target="_blank" rel="noopener" aria-label="Telegram">
                <img src="<?= $base ?>assets/images/socmed/Telegram.png"  alt="Telegram">
            </a>
        </div>

        <p class="footer-copy">&copy; <?= date('Y') ?> Ilmu Qayyim. All rights reserved.</p>
    </div>
</footer>

<!-- Scroll to top button -->
<button id="scrollUpBtn" class="scroll-up-btn" aria-label="Scroll to top" title="Kembali ke atas">↑</button>


<!-- Dark mode toggle -->
<button class="dark-toggle" id="darkToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
    🌙
</button>

<script>
(function() {
    const btn  = document.getElementById('darkToggle');
    const html = document.documentElement;

    // Load preferensi tersimpan
    const saved = localStorage.getItem('iq_theme') || 'light';
    html.setAttribute('data-theme', saved);
    btn.textContent = saved === 'dark' ? '☀️' : '🌙';

    btn.addEventListener('click', () => {
        const current = html.getAttribute('data-theme');
        const next    = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('iq_theme', next);
        btn.textContent = next === 'dark' ? '☀️' : '🌙';
    });
})();
</script>
<script>
(function() {
    const btn = document.getElementById('scrollUpBtn');
    if (!btn) return;
    window.addEventListener('scroll', () => {
        btn.classList.toggle('visible', window.scrollY > 200);
    });
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>