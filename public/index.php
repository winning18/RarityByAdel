<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/config/config.php';

$pageTitle = 'Home';
$currentPage = 'home';

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="hero-section">
    <div class="hero-slider">
        <div class="hero-slide active" style="background-image: url('https://picsum.photos/seed/rarity-fashion-1/1600/1100');"></div>
        <div class="hero-slide" style="background-image: url('https://picsum.photos/seed/rarity-fashion-2/1600/1100');"></div>
        <div class="hero-slide" style="background-image: url('https://picsum.photos/seed/rarity-fashion-3/1600/1100');"></div>
    </div>

    <div class="hero-overlay"></div>

    <div class="container hero-content">
        <span class="hero-kicker">RarityByAdel</span>
        <h1>Premium Ghanaian Brand</h1>
        <p>Elevated clothing and jewelry curated with a modern, refined, and timeless point of view.</p>

        <div class="hero-actions">
            <a href="<?= url('clothings.php') ?>" class="btn btn-dark">
                Shop Now
                <span class="btn-arrow">&rarr;</span>
            </a>
        </div>

        <div class="hero-search-card">
            <form action="<?= url('search.php') ?>" method="GET" class="hero-search-form">
                <label for="hero-search" class="sr-only">Search products</label>
                <input type="text" id="hero-search" name="q" placeholder="Search clothing, jewelry, styles..." />
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="hero-controls">
        <button class="hero-dot active" type="button" data-slide="0" aria-label="Go to slide 1"></button>
        <button class="hero-dot" type="button" data-slide="1" aria-label="Go to slide 2"></button>
        <button class="hero-dot" type="button" data-slide="2" aria-label="Go to slide 3"></button>
    </div>
</section>


<section class="featured-categories section-space">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Curated Collections</span>
            <h2>Shop the signature mood of RarityByAdel</h2>
            <p>Step into carefully selected pieces that combine elegance, femininity, and a premium Ghanaian brand identity.</p>
        </div>

        <div class="category-grid">
            <article class="category-card large-card">
                <div class="category-image" style="background-image: url('https://picsum.photos/seed/rarity-clothing/900/1100');"></div>
                <div class="category-content">
                    <span>Clothings</span>
                    <h3>Refined everyday luxury</h3>
                    <p>Explore statement fashion pieces designed for graceful presence and modern sophistication.</p>
                    <a href="<?= url('clothings.php') ?>" class="text-link">Explore Clothings &rarr;</a>
                </div>
            </article>

            <article class="category-card">
                <div class="category-image" style="background-image: url('https://picsum.photos/seed/rarity-jewelry/800/900');"></div>
                <div class="category-content">
                    <span>Jewelry</span>
                    <h3>Coming soon</h3>
                    <p>A premium jewelry selection is being prepared to complete the RarityByAdel experience.</p>
                    <a href="<?= url('jewelry.php') ?>" class="text-link">View teaser &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="brand-story section-space soft-section">
    <div class="container story-grid">
        <div class="story-copy">
            <span class="section-label">About the Brand</span>
            <h2>Minimal fashion shaped with quiet confidence</h2>
            <p>RarityByAdel is built for women who appreciate clean luxury, subtle detail, and elevated styling. Every piece is selected to express beauty through simplicity, confidence, and thoughtful design.</p>
            <p>Our vision is to create a premium Ghanaian fashion identity that feels modern, memorable, and effortlessly classy in every setting.</p>
            <a href="<?= url('about.php') ?>" class="btn btn-outline">Discover Our Story</a>
        </div>

        <div class="story-visual">
            <div class="story-panel panel-primary" style="background-image: url('https://picsum.photos/seed/rarity-story-1/700/900');"></div>
            <div class="story-panel panel-secondary" style="background-image: url('https://picsum.photos/seed/rarity-story-2/520/620');"></div>
        </div>
    </div>
</section>

<section class="trust-strip section-space">
    <div class="container">
        <div class="section-heading center-heading">
            <span class="section-label">Why Choose Us</span>
            <h2>Designed for a smooth premium shopping experience</h2>
        </div>

        <div class="trust-grid">
            <article class="trust-card">
                <h3>Elegant curation</h3>
                <p>Every collection is presented with a premium, editorial feel rather than a crowded marketplace experience.</p>
            </article>

            <article class="trust-card">
                <h3>Clear sizing support</h3>
                <p>International size guidance helps customers choose confidently and reduces uncertainty before purchase.</p>
            </article>

            <article class="trust-card">
                <h3>Simple payment flow</h3>
                <p>A direct checkout and payment reference process keeps ordering clear, modern, and easy to follow.</p>
            </article>
        </div>
    </div>
</section>



<section class="home-featured">
    <div class="container">
        <div class="section-intro">
            <span class="section-tag">Featured Collections</span>
            <h2>Shop refined pieces with a premium mood</h2>
            <p>Browse our signature clothing selection and get an early look at the jewelry collection coming soon.</p>
        </div>

        <div class="featured-grid">
            <article class="featured-card featured-card-large" style="background-image: url('https://picsum.photos/seed/rarity-collection-1/1000/1200');">
                <div class="featured-overlay"></div>
                <div class="featured-content">
                    <span class="featured-label">Clothings</span>
                    <h3>Modern elegance for every moment</h3>
                    <p>Discover premium pieces curated for confident, elevated styling.</p>
                    <a href="<?= url('clothings.php') ?>" class="featured-link">Shop Clothings &rarr;</a>
                </div>
            </article>

            <article class="featured-card" style="background-image: url('https://picsum.photos/seed/rarity-collection-2/800/1000');">
                <div class="featured-overlay"></div>
                <div class="featured-content">
                    <span class="featured-label">Jewelry</span>
                    <h3>Coming soon</h3>
                    <p>A premium jewelry drop is being prepared to complete the RarityByAdel experience.</p>
                    <a href="<?= url('jewelry.php') ?>" class="featured-link">View Jewelry &rarr;</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="home-values">
    <div class="container">
        <div class="values-grid">
            <article class="value-card">
                <h3>Premium curation</h3>
                <p>Each piece is selected to reflect a classy, modern, and confident fashion identity.</p>
            </article>

            <article class="value-card">
                <h3>Simple shopping flow</h3>
                <p>From discovery to checkout, the experience is designed to feel clean, easy, and elegant.</p>
            </article>

            <article class="value-card">
                <h3>Trusted support</h3>
                <p>Clear contact details, size guidance, and payment steps help customers shop with confidence.</p>
            </article>
        </div>
    </div>
</section>


<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>