/**
  https://github.com/gurde/ZOOM - 2010-08-27

  This is the customized version for gTheme 03 Framework : https://github.com/geminorum/gtheme_03
  - css class for div wrapping the image link
  - RTL : keydown() reverse
  - find() instead of children()
  - parents() instead of parent()
**/

(function($) {
  var body = $('body');
  body.append('<div id="zoom"><a class="close"></a><a href="#previous" class="previous"></a><a href="#next" class="next"></a><div class="content loading"></div></div>');

  var zoom = $('#zoom').hide(),
    zoomContent = $('#zoom .content'),
    overlay = '<div class="overlay"></div>',
    zoomedIn = false,
    openedImage = null,
    windowWidth = $(window).width(),
    windowHeight = $(window).height(),
    isRTL = $("html[dir~='rtl']").length;

  function open(event) {
    if (event) {
      event.preventDefault();
    }
    var link = $(this),
      src = link.attr('href');
    if (!src) {
      return;
    }
    $('#zoom .previous, #zoom .next').show();
    if (link.hasClass('zoom')) {
      $('#zoom .previous, #zoom .next').hide();
    }
    if (!zoomedIn) {
      zoomedIn = true;
      zoom.show();
      body.addClass('zoomed');
    }
    var image = $(new Image()).hide().css({width: 'auto'});
    body.append(image);
    zoomContent.html('').delay(500).addClass('loading');
    zoomContent.prepend(overlay);
    image.load(render).attr('src', src);
    openedImage = link;

    function render() {
      var image = $(this),
        borderWidth = parseInt(zoomContent.css('borderLeftWidth')),
        maxImageWidth = windowWidth - (borderWidth * 2),
        maxImageHeight = windowHeight - (borderWidth * 2),
        imageWidth = image.width(),
        imageHeight = image.height();
      if (imageWidth == zoomContent.width() && imageWidth <= maxImageWidth && imageHeight == zoomContent.height() && imageHeight <= maxImageHeight) {
          show(image);
          return;
      }
      if (imageWidth > maxImageWidth || imageHeight > maxImageHeight) {
        var desiredHeight = maxImageHeight < imageHeight ? maxImageHeight : imageHeight,
          desiredWidth  = maxImageWidth  < imageWidth  ? maxImageWidth  : imageWidth;
        if ( desiredHeight / imageHeight <= desiredWidth / imageWidth ) {
          image.width(Math.round(imageWidth * desiredHeight / imageHeight));
          image.height(desiredHeight);
        } else {
          image.width(desiredWidth);
          image.height(Math.round(imageHeight * desiredWidth / imageWidth));
        }
      }
      zoomContent.animate({
        width: image.width(),
        height: image.height(),
        marginTop: -(image.height() / 2) - borderWidth,
        marginLeft: -(image.width() / 2) - borderWidth
      }, 100, function() {
        show(image);
      });

      function show(image) {
        zoomContent.html(image);
        image.show();
        zoomContent.removeClass('loading');
      }
    }
  }

  function openPrevious() {
    var prev = openedImage.parents('div.-wrap').prev();
    if (prev.length == 0) {
      prev = $('.-gallery div.-wrap:last-child');
    }
    prev.find('a').trigger('click');
  }

  function openNext() {
    var next = openedImage.parents('div.-wrap').next();
    if (next.length == 0) {
      next = $('.-gallery div.-wrap:first-child');
    }
    next.find('a').trigger('click');
  }

  function close(event) {
    if (event) {
      event.preventDefault();
    }
    zoomedIn = false;
    openedImage = null;
    zoom.hide();
    body.removeClass('zoomed');
    zoomContent.empty();
  }

  function changeImageDimensions() {
    windowWidth = $(window).width();
    windowHeight = $(window).height();
  }

  (function bindNavigation() {
    zoom.on('click', function(event) {
      event.preventDefault();
      if ($(event.target).attr('id') == 'zoom') {
        close();
      }
    });

    $('#zoom .close').on('click', close);
    $('#zoom .previous').on('click', openPrevious);
    $('#zoom .next').on('click', openNext);
    $(document).on('keydown', function(event) {
      if (!openedImage) {
        return;
      }
      if (event.which == 38 || event.which == 40) {
        event.preventDefault();
      }
      if (event.which == 27) {
        close();
      }
      if ( isRTL ) {
        if (event.which == 39 && !openedImage.hasClass('zoom')) {
          openPrevious();
        }
        if (event.which == 37 && !openedImage.hasClass('zoom')) {
          openNext();
        }
      } else {
        if (event.which == 37 && !openedImage.hasClass('zoom')) {
          openPrevious();
        }
        if (event.which == 39 && !openedImage.hasClass('zoom')) {
          openNext();
        }
      }
    });

    if ($('.-gallery a').length == 1) {
      $('.-gallery a')[0].addClass('zoom');
    }
    $('.zoom, .-gallery a').on('click', open);
  })();

  (function bindChangeImageDimensions() {
    $(window).on('resize', changeImageDimensions);
  })();

  (function bindScrollControl() {
    $(window).on('mousewheel DOMMouseScroll', function(event) {
      if (!openedImage) {
        return;
      }
      event.stopPropagation();
      event.preventDefault();
      event.cancelBubble = false;
    });
  })();
})(jQuery);
