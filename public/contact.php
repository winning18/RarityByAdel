<?php
require __DIR__ . '/config/config.php';

$pageTitle = 'Contact Us';
$currentPage = 'contact';

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="brand-page contact-page">
    <div class="container">
        <div class="brand-hero compact-hero">
            <span class="brand-kicker">Contact Us</span>
            <h1>We would love to hear from you.</h1>
            <p>Reach out for order support, product questions, collaborations, or general enquiries. We will respond as soon as possible.</p>
        </div>

        <div class="contact-layout">
            <div class="contact-info-card">
                <span class="mini-tag">Get In Touch</span>
                <h2>Contact details</h2>

                <div class="contact-list">
                    <div class="contact-line">
                        <span>Phone</span>
                        <a href="tel:+233551812055">+233 551 812 055</a>
                    </div>

                    <div class="contact-line">
                        <span>Email</span>
                        <a href="mailto:hello@raritybyadel.com">hello@raritybyadel.com</a>
                    </div>
                    <br>
                    <div class="contact-line">
                        <span>Instagram:</span>
                        <a href="#" target="_blank" rel="noopener noreferrer">@raritybyadel</a>
                    </div>

                    <div class="contact-line">
                        <span>TikTok:</span>
                        <a href="#" target="_blank" rel="noopener noreferrer">@raritybyadel</a>
                    </div>

                    <div class="contact-line">
                        <span>Snapchat:</span>
                        <a href="#" target="_blank" rel="noopener noreferrer">@raritybyadel</a>
                    </div>
                </div>

                <p class="contact-note">For urgent order matters, calling or sending a WhatsApp message to our business line is recommended.</p>
            </div>

            <div class="contact-form-card">
                <span class="mini-tag">Send a Message</span>
                <h2>Tell us how we can help</h2>

                <form action="#" method="POST" class="contact-form-grid">
                    <div class="form-field">
                        <label for="contact_name">Full Name</label>
                        <input type="text" id="contact_name" name="name" placeholder="Enter your full name">
                    </div>
                    <br>
                    <div class="form-field">
                        <label for="contact_email">Email Address</label>
                        <input type="email" id="contact_email" name="email" placeholder="Enter your email">
                    </div>
                    <br>
                    <div class="form-field">
                        <label for="contact_subject">Subject</label>
                        <input type="text" id="contact_subject" name="subject" placeholder="What is this about?">
                    </div>
                    <br>

                    <div class="form-field full-span">
                        <label for="contact_message">Message</label>
                        <textarea id="contact_message" name="message" rows="6" placeholder="Tell us how we can help you"></textarea>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-dark contact-submit-btn">Send Message</button>
                    
                </form>
              <br>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>