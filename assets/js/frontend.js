(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const sliders = document.querySelectorAll('.review-slider-wrapper');
    if (!sliders.length) return;

    sliders.forEach(sliderWrapper => {
      const slider = sliderWrapper.querySelector('.slider');
      if (!slider) return;

      const track = slider.querySelector('.slider-track');
      const dots = slider.querySelector('.dots');
      const autoplay = 3000;
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
      for (let i = 0; i < origLen; i++) {
        const b = document.createElement('button');
        if (i === 0) b.classList.add('active');
        dots.appendChild(b);
      }
      const dotButtons = Array.from(dots.children);

      const updateWidth = () => {
        const slideStyle = window.getComputedStyle(slides[0]);
        const slideMargin = parseFloat(slideStyle.marginLeft) + parseFloat(slideStyle.marginRight);
        cardW = slides[0].getBoundingClientRect().width + slideMargin;
      };
      updateWidth();
      window.addEventListener('resize', updateWidth);

      const updateDots = (i) => {
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
          setTimeout(() => {
            index = 0;
            track.scrollTo({
              left: 0,
              behavior: 'auto'
            });
            updateDots(index);
          }, 500); // 500ms for smooth scroll to finish
        }
      };

      const startAutoplay = () => {
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
      for (let s = 0; s < n; s++) {
        html += `<span style="color:${starColor}">★</span>`;
      }
      el.innerHTML = html;
    });

    // Lazy load images
    const lazyImages = document.querySelectorAll('.rs-thumb[data-src]');
    const lazyImageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const lazyImage = entry.target;
          lazyImage.src = lazyImage.dataset.src;
          lazyImage.removeAttribute('data-src');
          observer.unobserve(lazyImage);
        }
      });
    });
    lazyImages.forEach(lazyImage => {
      lazyImageObserver.observe(lazyImage);
    });

    // Apply theme dot color
    if (typeof rsFront !== 'undefined' && rsFront.theme) {
      const style = document.createElement('style');
      style.innerHTML = `.dots button.active { background: ${rsFront.theme} !important; }`;
      document.head.appendChild(style);
    }
  });
})();
