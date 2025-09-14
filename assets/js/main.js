// Initialize interactive components
(function(){
  // WOW animations (optional)
  if (typeof WOW !== 'undefined') {
    try { new WOW().init(); } catch (e) {}
  }

  // Swipers
  if (typeof Swiper !== 'undefined') {
    try {
      new Swiper('.brandSwiper', { slidesPerView: 4, spaceBetween: 30, loop: true, autoplay: { delay: 2500 }, breakpoints: { 0:{slidesPerView:2},768:{slidesPerView:3},992:{slidesPerView:4} } });
      new Swiper('.testimonialSwiper-3', { slidesPerView: 1, spaceBetween: 24, loop: true, pagination: { el: '.testimonialSwiper-swiper-pagination', clickable: true } });
    } catch (e) {}
  }

  // Magnific Popup (video)
  if (window.jQuery && typeof jQuery.fn.magnificPopup === 'function') {
    jQuery('.video-popup').magnificPopup({ type: 'iframe' });
  }

  // Odometer counters
  document.querySelectorAll('.odometer').forEach(function(el){
    el.innerHTML = el.dataset.countTo || '0';
  });
})();
