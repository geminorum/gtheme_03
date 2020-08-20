jQuery(function ($) {
  var lang = $('html').attr('lang');

  var toPersianDigit = function (number) {
    var pzero = '۰'.charCodeAt(0);
    return number.toString().replace(/\d+/g, function (match) {
      return match.split('').map(function (number) {
        return String.fromCharCode(pzero + parseInt(number));
      }).join('');
    });
  };

  $(function () {
    $('.wrap-jcarousel-paginated .-carousel')
      // responsive!
      .on('jcarousel:create jcarousel:reload', function () {
        var element = $(this);
        var width = element.innerWidth();

        // This shows 1 item at a time.
        // Divide `width` to the number of items you want to display,
        // eg. `width = width / 3` to display 3 items at a time.
        element.jcarousel('items').css('width', width + 'px');
      })
      .jcarousel({
        rtl: $('html').attr('dir') === 'rtl',
        transitions: true,
        center: true,
        wrap: 'circular'
      })
      .jcarouselAutoscroll({
        interval: 6000,
        target: '+=1',
        autostart: true
      });

    // // Prev control initialization
    // $('.jcarousel-control-prev')
    //   .on('jcarouselcontrol:active', function () {
    //     $(this).removeClass('inactive');
    //   })
    //   .on('jcarouselcontrol:inactive', function () {
    //     $(this).addClass('inactive');
    //   })
    //   .jcarouselControl({
    //     // Options go here
    //     target: '-=1'
    //   });
    //
    // // Next control initialization
    // $('.jcarousel-control-next')
    //   .on('jcarouselcontrol:active', function () {
    //     $(this).removeClass('inactive');
    //   })
    //   .on('jcarouselcontrol:inactive', function () {
    //     $(this).addClass('inactive');
    //   })
    //   .jcarouselControl({
    //     // Options go here
    //     target: '+=1'
    //   });

    $('.wrap-jcarousel-paginated .-pagination')
      .on('jcarouselpagination:active', 'a', function () {
        $(this).addClass('active');
      })
      .on('jcarouselpagination:inactive', 'a', function () {
        $(this).removeClass('active');
      })
      .jcarouselPagination({
        item: function (page) {
          return '<a href="#' + page + '">' + (lang === 'fa-IR' ? toPersianDigit(page) : page) + '</a>';
        }
      });
  });
});
