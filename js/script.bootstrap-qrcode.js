// NOTE: needs the bootstrap for dropdown
jQuery(function ($) {
  $('.-action.-bootstrap-qrcode')
    // .attr('style', 'display:list-item!important') // couples with `.d-none`
    .on('show.bs.dropdown', function (event) {
      if ($(this).data('qrcode')) return;
      const $wrap = $(this).find('.-qrcode-wrap');
      const $link = $(this).find('a.bootstrap-qrcode-toggle');
      const size = $link.data('qrcode-size');
      $wrap.html($('<img />', {
        // src: 'https://chart.apis.google.com/chart?cht=qr&chs=' + size + 'x' + size + '&chld=L%7C2&chl=' + encodeURIComponent($link.data('qrcode-url')),
        src: 'https://api.qrserver.com/v1/create-qr-code/?size=' + size + 'x' + size + '&ecc=M&data=' + encodeURIComponent($link.data('qrcode-url')),
        // class: 'bootstrap-qrcode-img',
        alt: 'qrcode'
      }));
      $(this).data('qrcode', true);
      $(this).find('.-qrcode-wrap img').on('error', function (event) {
        $wrap.html('<div class="-na d-flex align-items-center justify-content-center h-100 px-3"><small>' + $link.data('na') + '</small></div>');
      });
    });
});
