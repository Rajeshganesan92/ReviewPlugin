(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var slider = document.querySelector('#loopSlider');
    if (!slider) return;
    var track = slider.querySelector('.slider-track');
    var slides = Array.from(track.children);
    var dots = slider.querySelector('.dots');
    var autoplay = 3000; var index=0; var cardW;

    // clone for seamless
    var origLen = slides.length;
    slides.forEach(function(s){ track.appendChild(s.cloneNode(true)); });
    slides = Array.from(track.children);

    // create dots using original count
    var originalCount = origLen;
    for (var i=0;i<originalCount;i++){ var b=document.createElement('button'); if(i==0) b.classList.add('active'); dots.appendChild(b); }
    var dotButtons = Array.from(dots.children);

    function updateWidth(){ cardW = slides[0].getBoundingClientRect().width + 20; }
    updateWidth(); window.addEventListener('resize', updateWidth);

    function updateDots(i){ dotButtons.forEach(function(d){ d.classList.remove('active'); }); dotButtons[i % dotButtons.length].classList.add('active'); }

    function moveNext(){ index++; track.scrollTo({left: index*cardW, behavior:'smooth'}); updateDots(index);
      if (index >= slides.length - originalCount){ setTimeout(function(){ track.scrollTo({left:0,behavior:'auto'}); index=0; updateDots(0); }, 300);
      }
    }

    var auto = setInterval(moveNext, autoplay);
    slider.addEventListener('mouseenter', function(){ clearInterval(auto); });
    slider.addEventListener('mouseleave', function(){ auto = setInterval(moveNext, autoplay); });

    dotButtons.forEach(function(b,i){ b.addEventListener('click', function(){ index = i; track.scrollTo({left: i*cardW, behavior:'smooth'}); updateDots(i); }); });

    // render stars
    var starEls = document.querySelectorAll('.rs-stars');
    starEls.forEach(function(el){
      var n = parseInt(el.getAttribute('data-stars')||5,10);
      var starColor = (typeof rsFront !== 'undefined' && rsFront.star) ? rsFront.star : '#FFD700';
      var html = '';
      for (var s=0;s<n;s++){ html += '<span style="color:'+starColor+'">★</span>'; }
      el.innerHTML = html;
    });

    // apply theme dot color
    if (typeof rsFront !== 'undefined' && rsFront.theme) {
      var style = document.createElement('style');
      style.innerHTML = '.dots button.active{ background: '+rsFront.theme+' !important; }';
      document.head.appendChild(style);
    }
  });
})();
