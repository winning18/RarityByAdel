<?php
require __DIR__ . '/config/config.php';

$pageTitle = 'FAQ';
$currentPage = 'faq';

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="brand-page faq-page">
    <div class="container">
        <div class="brand-hero compact-hero">
            <span class="brand-kicker">Frequently Asked Questions</span>
            <h1>Helpful answers before you place your order.</h1>
            <p>Here are some of the common questions customers ask about products, sizing, payments, shipping, and support.</p>
        </div>

        <div class="faq-search-card">
            <input type="text" id="faqSearch" placeholder="Search a question...">
        </div>
        <br>

        <div class="faq-list" id="faqList">
            <div class="faq-item">
                <button type="button" class="faq-question">How do I place an order?</button>
                <div class="faq-answer">
                    <p>Browse the store, select your preferred item, choose your size and quantity, add it to cart, and proceed to checkout to submit your order.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">What payment methods do you accept?</button>
                <div class="faq-answer">
                    <p>We provide payment options for Mobile Money, card, and bank transfer at checkout. Mobile Money is currently set as the default selection.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">How do I submit my payment reference?</button>
                <div class="faq-answer">
                    <p>After placing your order, you will be directed to a payment step page where you can complete payment and submit your payment reference number for confirmation.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">How do I choose the right clothing size?</button>
                <div class="faq-answer">
                    <p>Each clothing product includes size options and an international size chart to guide your selection. If you need help, contact us before ordering.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">Do you offer delivery?</button>
                <div class="faq-answer">
                    <p>Yes. Delivery details will depend on your order location and chosen fulfillment arrangement. More shipping details can be shared after order confirmation.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">Can I track my order status?</button>
                <div class="faq-answer">
                    <p>Yes. Once your payment is confirmed and your order is being processed, updates such as confirmation, shipping, and delivery can be shared by email.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">Can I return or exchange an item?</button>
                <div class="faq-answer">
                    <p>Return and exchange eligibility will depend on the product condition and store policy. We recommend contacting us quickly after delivery if there is an issue with your order.</p>
                </div>
            </div>

            <div class="faq-item">
                <button type="button" class="faq-question">How can I contact customer support?</button>
                <div class="faq-answer">
                    <p>You can reach us through our contact form, email, social media platforms, or by calling our business number directly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const item = button.closest('.faq-item');
        item.classList.toggle('active');
    });
});

const faqSearch = document.getElementById('faqSearch');
const faqItems = document.querySelectorAll('#faqList .faq-item');

if (faqSearch) {
    faqSearch.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();

        faqItems.forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(keyword) ? '' : 'none';
        });
    });
}
</script>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>