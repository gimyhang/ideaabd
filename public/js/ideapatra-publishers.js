/**
 * Idea Shop — Dynamic Publisher Slider & Ideapatra Literary Interactive Engine
 */

// 1. Publisher Slider Controller
window.scrollPublisherSlider = function (direction) {
    const track = document.getElementById('publisherSliderTrack');
    if (!track) return;
    const card = track.querySelector('.publisher-slide-card');
    const scrollStep = card ? (card.offsetWidth + 14) * 2 : 360;
    track.scrollBy({ left: scrollStep * direction, behavior: 'smooth' });
};

document.addEventListener('DOMContentLoaded', function () {
    const pubTrack = document.getElementById('publisherSliderTrack');
    const prevBtn = document.getElementById('pubSliderPrevBtn');
    const nextBtn = document.getElementById('pubSliderNextBtn');

    if (pubTrack) {
        // Arrow Button Opacity State
        const updateArrowStates = () => {
            if (prevBtn) {
                prevBtn.style.opacity = pubTrack.scrollLeft <= 10 ? '0.35' : '1';
                prevBtn.style.pointerEvents = pubTrack.scrollLeft <= 10 ? 'none' : 'auto';
            }
            if (nextBtn) {
                const maxScroll = pubTrack.scrollWidth - pubTrack.clientWidth;
                nextBtn.style.opacity = pubTrack.scrollLeft >= maxScroll - 10 ? '0.35' : '1';
                nextBtn.style.pointerEvents = pubTrack.scrollLeft >= maxScroll - 10 ? 'none' : 'auto';
            }
        };

        pubTrack.addEventListener('scroll', updateArrowStates);
        updateArrowStates();

        // Mouse Drag to Scroll
        let isDown = false;
        let startX;
        let scrollLeft;

        pubTrack.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - pubTrack.offsetLeft;
            scrollLeft = pubTrack.scrollLeft;
            pubTrack.style.cursor = 'grabbing';
        });

        pubTrack.addEventListener('mouseleave', () => {
            isDown = false;
            pubTrack.style.cursor = '';
        });

        pubTrack.addEventListener('mouseup', () => {
            isDown = false;
            pubTrack.style.cursor = '';
        });

        pubTrack.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - pubTrack.offsetLeft;
            const walk = (x - startX) * 1.5;
            pubTrack.scrollLeft = scrollLeft - walk;
        });

        // Gentle Auto-Advance (Pauses on Hover)
        let autoSlideTimer = null;
        const startAutoSlide = () => {
            autoSlideTimer = setInterval(() => {
                const maxScroll = pubTrack.scrollWidth - pubTrack.clientWidth;
                if (maxScroll <= 20) return;
                if (pubTrack.scrollLeft >= maxScroll - 15) {
                    pubTrack.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    const card = pubTrack.querySelector('.publisher-slide-card');
                    const step = card ? (card.offsetWidth + 14) : 200;
                    pubTrack.scrollBy({ left: step, behavior: 'smooth' });
                }
            }, 4200);
        };

        const stopAutoSlide = () => {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
        };

        startAutoSlide();
        pubTrack.addEventListener('mouseenter', stopAutoSlide);
        pubTrack.addEventListener('mouseleave', startAutoSlide);
        pubTrack.addEventListener('touchstart', stopAutoSlide, { passive: true });
        pubTrack.addEventListener('touchend', startAutoSlide, { passive: true });
    }

    // 2. Ideapatra Column 2 Tab Switcher
    window.switchIdeapatraTab = function (tabType) {
        const btnMostRead = document.getElementById('tabBtnMostRead');
        const btnFeatured = document.getElementById('tabBtnFeatured');
        const secMostRead = document.getElementById('ideapatraSecMostRead');
        const secFeatured = document.getElementById('ideapatraSecFeatured');

        if (!secMostRead || !secFeatured) return;

        if (tabType === 'mostread') {
            secMostRead.style.display = 'flex';
            secFeatured.style.display = 'none';
            if (btnMostRead) btnMostRead.classList.add('active');
            if (btnFeatured) btnFeatured.classList.remove('active');
        } else {
            secMostRead.style.display = 'none';
            secFeatured.style.display = 'flex';
            if (btnFeatured) btnFeatured.classList.add('active');
            if (btnMostRead) btnMostRead.classList.remove('active');
        }
    };
});
