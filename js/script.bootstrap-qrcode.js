// NOTE: needs bootstrap for dropdown
jQuery(function ($) {
  $('.-action.-bootstrap-qrcode')
    // .attr('style', 'display:list-item!important') // couples with `.d-none`
    .on('show.bs.dropdown', function (event) {
      if ($(this).data('qrcode')) return;
      var $link = $(this).find('a.bootstrap-qrcode-toggle');
      var size = $link.data('qrcode-size');
      $(this).find('.-qrcode-wrap').html($('<img />', {
        src: 'https://chart.apis.google.com/chart?cht=qr&chs=' + size + 'x' + size + '&chld=L%7C2&chl=' + encodeURIComponent($link.data('qrcode-url')),
        alt: 'qrcode'
      }));
      $(this).data('qrcode', true);
    });
});
