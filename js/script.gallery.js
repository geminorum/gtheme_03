jQuery(document).ready(function($) {

  var lastDirection;

  $('.gallery').imagesLoaded(function() {
    $('.gallery-spinner').fadeOut(500, function() {
      $('.gallery').animate({
        'opacity': '1'
      }, 500);
    });
  });

  $('.gallery-img').bind('mouseenter', function(e) {

    var img = this;
    lastDirection = getDir($(this), e);

    $(img).addClass(lastDirection);

    setTimeout(function() {
      $(img).addClass('animated');
    }, 1);
  });

  $('.gallery-img').bind('mouseleave', function(e) {
    $(this).removeClass(lastDirection).removeClass('animated');
  });
});

var getDir = function(elem, e) {

  // the width and height of the current div
  var w = elem.width();
  var h = elem.height();
  var offset = elem.offset();

  // calculate the x and y to get an angle to the center of the div from that x and y.
  // gets the x value relative to the center of the DIV and "normalize" it
  var x = (e.pageX - offset.left - (w / 2)) * (w > h ? (h / w) : 1);
  var y = (e.pageY - offset.top - (h / 2)) * (h > w ? (w / h) : 1);

  // the angle and the direction from where the mouse came in/went out clockwise (TRBL=0123);
  /**
    first calculate the angle of the point,
    add 180 deg to get rid of the negative values
    divide by 90 to get the quadrant
    add 3 and do a modulo by 4  to shift the quadrants to a proper clockwise TRBL (top/right/bottom/left)
  **/
  var direction = Math.round((((Math.atan2(y, x) * (180 / Math.PI)) + 180) / 90) + 3) % 4;

  // do your animations here
  switch (direction) {
    case 0:
      return 'top';
    case 1:
      return 'right';
    case 2:
      return 'bottom';
    case 3:
      return 'left';
  }
};
