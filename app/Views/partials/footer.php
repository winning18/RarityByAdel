    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <h3>RarityByAdel</h3>
                <p>Minimal, classy, and premium fashion crafted for modern elegance.</p>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="<?= url('index.php') ?>">Home</a>
                <a href="<?= url('clothings.php') ?>">Clothings</a>
                <a href="<?= url('jewelry.php') ?>">Jewelry</a>
                <a href="<?= url('about.php') ?>">About Us</a>
                <a href="<?= url('contact.php') ?>">Contact Us</a>
                <a href="<?= url('faq.php') ?>">Faq</a>
                <a href="<?= url('order-status.php') ?>">Order Status</a>
                <a href="<?= url('admin/login.php') ?>">Admin Panel</a>
            </div>

            <div class="footer-contact">
                <h4>Contact</h4>
                <a href="mailto:<?= e(BUSINESS_EMAIL) ?>"><?= e(BUSINESS_EMAIL) ?></a>
                <a href="tel:+233551812055"><?= e(BUSINESS_PHONE) ?></a>
                <div class="social-links">
                    <a href="<?= e(SOCIAL_TIKTOK) ?>" aria-label="TikTok">Tk</a>
                    <a href="<?= e(SOCIAL_INSTAGRAM) ?>" aria-label="Instagram">Ig</a>
                    <a href="<?= e(SOCIAL_SNAPCHAT) ?>" aria-label="Snapchat">Sc</a>
                </div>
            </div>
        </div>

        <div class="container footer-bottom">
            <p>&copy; <?= date('Y') ?> RarityByAdel. All rights reserved.</p>
        </div>
    </footer>

    
    <script src="<?= asset('js/app.js') . '?v=' . time() ?>"></script>
</body>
</html>