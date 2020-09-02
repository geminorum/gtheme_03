jQuery(function ($) {
  $('.wrap-slick-carousel .-carousel').slick({
    rtl: $('html').attr('dir') === 'rtl',
    // slidesToShow: 5,
    // slidesToScroll: 1,
    // autoplay: true,
    // autoplaySpeed: 3000,
    // arrows: false,
    // dots: false,
    // pauseOnHover: false,
    // responsive: [
    //   {
    //     breakpoint: 768,
    //     settings: {
    //       slidesToShow: 3
    //     }
    //   },
    //   {
    //     breakpoint: 520,
    //     settings: {
    //       slidesToShow: 2
    //     }
    //   }
    // ]
  });
});
