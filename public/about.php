<?php
require __DIR__ . '/config/config.php';

$pageTitle = 'About Us';
$currentPage = 'about';

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="brand-page about-page">
    <div class="container">
        <div class="brand-hero">
          <br>
            <span class="brand-kicker">About RARITYBYADEL</span>
            <h1>A premium Ghanaian fashion expression shaped by elegance and intention.</h1>
            <p>RARITYBYADEL is a modern fashion and jewelry brand created for customers who appreciate refined style, confident simplicity, and pieces that feel special from the very first wear.</p>
        </div>

        <div class="brand-story-grid">
            <div class="brand-story-card">
                <span class="mini-tag">Our Story</span>
                <h2>Designed to feel rare, timeless, and personal.</h2>
                <p>We created RARITYBYADEL to celebrate beauty through thoughtfully selected clothing and jewelry that blend modern femininity with a premium African identity. Every collection is curated to feel polished, wearable, and memorable.</p>
            </div>

            <div class="brand-story-card highlight-card">
                <span class="mini-tag">Our Promise</span>
                <p>We believe style should feel effortless, elevated, and expressive. That is why we focus on quality presentation, flattering pieces, and an experience that feels luxurious from browsing to delivery.</p>
            </div>
        </div>

        <div class="brand-values">
            <div class="value-card">
                <h3>Craft</h3>
                <p>We value quality, finishing, and thoughtful presentation in every item we offer.</p>
            </div>
            <br>

            <div class="value-card">
                <h3>Detail</h3>
                <p>From silhouettes to accessories, we pay attention to the small touches that create a premium feel.</p>
            </div>
            <br>

            <div class="value-card">
                <h3>Confidence</h3>
                <p>Our collections are selected to help customers step out feeling elegant, bold, and beautifully distinct.</p>
            </div>
            <br>
        </div>

        <div class="brand-cta-panel">
            <div>
                <span class="mini-tag">Shop With Us</span>
                <h2>Fashion and jewelry curated for standout everyday elegance.</h2>
            </div>
            <a href="<?= url('clothings.php') ?>" class="btn btn-dark">Explore Collection</a>
        </div>
        <br>
    </div>
</section>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>