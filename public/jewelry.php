<?php
require __DIR__ . '/config/config.php';

$pageTitle = 'Jewelry';
$currentPage = 'jewelry';

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="catalog-hero">
    <div class="container">
        <span class="section-tag">Jewelry</span>
        <h1>Premium jewelry is coming soon</h1>
        <p>A refined jewelry collection is being prepared to complement the RarityByAdel fashion experience.</p>
    </div>
</section>

<section class="coming-soon-section">
    <div class="container">
        <div class="coming-soon-card">
            <div class="coming-soon-image" style="background-image: url('https://picsum.photos/seed/rarity-jewelry-coming/1100/700');"></div>

            <div class="coming-soon-content">
                <span class="section-tag">Coming soon</span>
                <h2>Elegant pieces worth waiting for</h2>
                <p>Our jewelry line is being curated with the same premium attention to detail, elegance, and style that defines the RarityByAdel brand.</p>

                <div class="coming-soon-filters">
                    <div class="coming-pill">Necklaces</div>
                    <div class="coming-pill">Bracelets</div>
                    <div class="coming-pill">Earrings</div>
                    <div class="coming-pill">Statement sets</div>
                </div>

                <a href="<?= url('contact.php') ?>" class="btn btn-dark">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>