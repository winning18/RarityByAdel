document.addEventListener('DOMContentLoaded', () => {
    /* =========================
       HEADER / MOBILE NAV
    ========================= */
    const navToggle = document.querySelector('.nav-toggle');
    const siteNav = document.querySelector('.site-nav');

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', () => {
            const isOpen = siteNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (event) => {
            if (!siteNav.contains(event.target) && !navToggle.contains(event.target)) {
                siteNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.querySelectorAll('.site-nav a').forEach((link) => {
            link.addEventListener('click', () => {
                siteNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* =========================
       HOME / HERO SLIDER
    ========================= */
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let currentSlide = 0;
    let sliderInterval = null;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });

        currentSlide = index;
    }

    function startSlider() {
        if (!slides.length) return;

        sliderInterval = setInterval(() => {
            const nextSlide = (currentSlide + 1) % slides.length;
            showSlide(nextSlide);
        }, 5000);
    }

    if (slides.length && dots.length) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
                clearInterval(sliderInterval);
                startSlider();
            });
        });

        startSlider();
    }

    /* =========================
       CATALOG / FILTERS
    ========================= */
    document.querySelectorAll('.filter-options button').forEach((button) => {
        button.addEventListener('click', () => {
            button.classList.toggle('is-active');
        });
    });

    const clearFiltersButton = document.querySelector('.clear-filters');

    if (clearFiltersButton) {
        clearFiltersButton.addEventListener('click', () => {
            document.querySelectorAll('.filter-options button').forEach((button) => {
                button.classList.remove('is-active');
            });

            document.querySelectorAll('.filter-checks input').forEach((input) => {
                input.checked = false;
            });

            const searchInput = document.querySelector('#catalog-search');

            if (searchInput) {
                searchInput.value = '';
            }
        });
    }

    /* =========================
       PRODUCT PAGE
    ========================= */
    const mainProductImage = document.querySelector('#mainProductImage');
    const thumbButtons = document.querySelectorAll('.thumb-btn');

    if (mainProductImage && thumbButtons.length) {
        thumbButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const image = button.getAttribute('data-image');

                if (image) {
                    mainProductImage.src = image;
                }

                thumbButtons.forEach((btn) => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
    }

    const sizeButtons = document.querySelectorAll('.size-btn');

    if (sizeButtons.length) {
        sizeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                sizeButtons.forEach((btn) => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
    }

    const quantityInput = document.querySelector('#quantityInput');
    const qtyButtons = document.querySelectorAll('.qty-btn');

    if (quantityInput && qtyButtons.length) {
        qtyButtons.forEach((button) => {
            button.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value, 10) || 1;
                const action = button.getAttribute('data-action');

                if (action === 'plus') {
                    currentValue += 1;
                }

                if (action === 'minus' && currentValue > 1) {
                    currentValue -= 1;
                }

                quantityInput.value = currentValue;
            });
        });
    }

    const sizeGuideToggle = document.querySelector('.size-guide-toggle');
    const sizeChartModal = document.querySelector('#sizeChartModal');
    const sizeChartClose = document.querySelector('.size-chart-close');
    const sizeChartBackdrop = document.querySelector('.size-chart-backdrop');

    if (sizeGuideToggle && sizeChartModal) {
        sizeGuideToggle.addEventListener('click', () => {
            sizeChartModal.classList.add('open');
        });
    }

    if (sizeChartClose && sizeChartModal) {
        sizeChartClose.addEventListener('click', () => {
            sizeChartModal.classList.remove('open');
        });
    }

    if (sizeChartBackdrop && sizeChartModal) {
        sizeChartBackdrop.addEventListener('click', () => {
            sizeChartModal.classList.remove('open');
        });
    }

    /* =========================
       CHECKOUT / PAYMENT
    ========================= */
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentPanels = document.querySelectorAll('.payment-panel');

    if (paymentOptions.length && paymentPanels.length) {
        paymentOptions.forEach((option) => {
            const input = option.querySelector('input');

            option.addEventListener('click', () => {
                paymentOptions.forEach((item) => item.classList.remove('active'));
                option.classList.add('active');

                const selectedMethod = input ? input.value : '';

                paymentPanels.forEach((panel) => {
                    panel.classList.remove('active');

                    if (panel.getAttribute('data-method-panel') === selectedMethod) {
                        panel.classList.add('active');
                    }
                });
            });
        });
    }

    /* =========================
       AUTH / PASSWORD TOGGLE
    ========================= */
    document.querySelectorAll('.password-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const targetId = toggle.getAttribute('data-target');
            const input = targetId ? document.getElementById(targetId) : null;

            if (!input) return;

            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            toggle.textContent = isPassword ? 'Hide' : 'Show';
        });
    });
});