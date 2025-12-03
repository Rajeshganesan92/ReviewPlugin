(() => {
  // Utility function for debouncing
  function debounce(func, wait, immediate) {
    let timeout;
    return function() {
      const context = this, args = arguments;
      const later = function() {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      const callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  }

  document.addEventListener('DOMContentLoaded', () => {
    const sliders = document.querySelectorAll('.review-slider-wrapper');
    if (!sliders.length) return;

    sliders.forEach(sliderWrapper => {
      const slider = sliderWrapper.querySelector('.slider');
      if (!slider) return;

      const track = slider.querySelector('.slider-track');
      const dots = slider.querySelector('.dots');
      // Make autoplay speed configurable via data attribute, with a default fallback
      const autoplay = parseInt(sliderWrapper.dataset.autoplaySpeed, 10) || 3000;
      let index = 0;
      let cardW;
      let auto;

      let slides = Array.from(track.children);
      const origLen = slides.length;

      if (origLen === 0) return;

      // Clone for seamless loop
      slides.forEach(s => track.appendChild(s.cloneNode(true)));
      slides = Array.from(track.children);

      // Create dots
      if (dots) {
          for (let i = 0; i < origLen; i++) {
            const b = document.createElement('button');
            if (i === 0) b.classList.add('active');
            dots.appendChild(b);
          }
      }
      const dotButtons = dots ? Array.from(dots.children) : [];

      const updateWidth = () => {
        if (slides.length === 0) return;
        const slideStyle = window.getComputedStyle(slides[0]);
        const slideMargin = parseFloat(slideStyle.marginLeft) + parseFloat(slideStyle.marginRight);
        cardW = slides[0].getBoundingClientRect().width + slideMargin;
      };
      updateWidth();
      // Debounce the resize event listener
      window.addEventListener('resize', debounce(updateWidth, 250));

      const updateDots = (i) => {
        if (!dots) return;
        dotButtons.forEach(d => d.classList.remove('active'));
        dotButtons[i % origLen].classList.add('active');
      };

      const moveNext = () => {
        index++;
        track.scrollTo({
          left: index * cardW,
          behavior: 'smooth'
        });
        updateDots(index);

        if (index >= origLen) {
          // After the smooth scroll animation finishes, reset to the beginning.
          // The timeout should be slightly longer than the scroll animation duration.
          setTimeout(() => {
            index = 0;
            track.scrollTo({
              left: 0,
              behavior: 'auto' // Use 'auto' for an instant jump
            });
            updateDots(index);
          }, 700); // Increased timeout for safety
        }
      };

      const startAutoplay = () => {
        if (autoplay === 0) return; // Do not start if autoplay is disabled
        auto = setInterval(moveNext, autoplay);
      };

      const stopAutoplay = () => {
        clearInterval(auto);
      };

      slider.addEventListener('mouseenter', stopAutoplay);
      slider.addEventListener('mouseleave', startAutoplay);

      dotButtons.forEach((b, i) => {
        b.addEventListener('click', () => {
          index = i;
          track.scrollTo({
            left: i * cardW,
            behavior: 'smooth'
          });
          updateDots(i);
        });
      });

      startAutoplay();
    });

    // Render stars
    const starEls = document.querySelectorAll('.rs-stars');
    starEls.forEach(el => {
      const n = parseInt(el.getAttribute('data-stars') || 5, 10);
      const starColor = (typeof rsFront !== 'undefined' && rsFront.star) ? rsFront.star : '#FFD700';
      let html = '';
      for (let s = 0; s < 5; s++) {
        if (s < n) {
          html += `<span style="color:${starColor}">★</span>`;
        } else {
          html += `<span style="color:#ccc">★</span>`;
        }
      }
      el.innerHTML = html;
    });

    // Apply theme dot color
    if (typeof rsFront !== 'undefined' && rsFront.theme) {
      const style = document.createElement('style');
      style.innerHTML = `.review-slider-wrapper .dots button.active { background: ${rsFront.theme} !important; }`;
      document.head.appendChild(style);
    }
  });
})();
