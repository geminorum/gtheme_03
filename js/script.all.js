jQuery(function ($) {
  // @SEE: `gThemeWrap::bodyClose()`
  // $('html').removeClass('no-js');

  $('a.scroll').click(function (e) {
    e.preventDefault();
    $('html,body').animate({
      scrollTop: $(this.hash).offset().top
    }, 500);
  });

  $('a.scroll-to-top').click(function (e) {
    e.preventDefault();
    $('html, body').animate({
      scrollTop: 0
    }, 'slow');
  });

  $('img').on('error', function () {
    console.log('error loading image: ' + $(this).attr('src'));
    $(this).addClass('error-image').hide();
  });

  $('a[href="#"]').click(function (e) {
    e.preventDefault();
  });

  // for BS3
  // $('[data-toggle="tooltip"]').tooltip();
});
