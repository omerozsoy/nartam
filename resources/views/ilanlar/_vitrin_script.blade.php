<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swiper === 'undefined') {
            return;
        }
        if (document.querySelector('.vitrin-swiper')) {
            new Swiper('.vitrin-swiper', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',
                loop: true,
                coverflowEffect: { rotate: 20, stretch: 0, depth: 350, modifier: 1, slideShadows: true },
                autoplay: { delay: 3500, disableOnInteraction: false },
                pagination: { el: '.vitrin-swiper .swiper-pagination', clickable: true },
                navigation: {
                    nextEl: '.vitrin-swiper .swiper-button-next',
                    prevEl: '.vitrin-swiper .swiper-button-prev',
                },
            });
        }
        if (document.querySelector('.hero-swiper')) {
            new Swiper('.hero-swiper', {
                effect: 'fade',
                fadeEffect: { crossFade: true },
                loop: true,
                autoplay: { delay: 5000, disableOnInteraction: false },
                pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
                navigation: {
                    nextEl: '.hero-swiper .swiper-button-next',
                    prevEl: '.hero-swiper .swiper-button-prev',
                },
            });
        }
    });
</script>
