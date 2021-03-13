// jQuery jFontSize Plugin | Version 2.0 by Vincent Chabredier / Ouvrages | https://github.com/ouvrages/jfontsize
// (function(a){return a.fn.jfontsize=function(e){var g,b,c,f,d;g=a(this);f={btnMinusClasseId:"#jfontsize-minus",btnDefaultClasseId:"#jfontsize-default",btnPlusClasseId:"#jfontsize-plus",btnMinusMaxHits:10,btnPlusMaxHits:10,sizeChange:1};if(e){e=a.extend(f,e)}d=function(){return a.jStorage.set("jfontsize",c)};b=function(){return g.each(function(j){var k,h;if(!(a(this).data("initial_size")!=null)){k=a(this).css("font-size");k=parseInt(k.replace("px",""));a(this).data("initial_size",k)}h=a(this).data("initial_size")+(c*e.sizeChange);return a(this).css("font-size",h+"px")})};a(e.btnMinusClasseId+", "+e.btnDefaultClasseId+", "+e.btnPlusClasseId).removeAttr("href");a(e.btnMinusClasseId+", "+e.btnDefaultClasseId+", "+e.btnPlusClasseId).css("cursor","pointer");c=a.jStorage.get("jfontsize",0);if(c===(-e.btnMinusMaxHits)){a(e.btnMinusClasseId).addClass("jfontsize-disabled")}if(c===e.btnPlusMaxHits){a(e.btnPlusClasseId).addClass("jfontsize-disabled")}b();a(e.btnMinusClasseId).click(function(){a(e.btnPlusClasseId).removeClass("jfontsize-disabled");if(c>(-e.btnMinusMaxHits)){c--;if(c===(-e.btnMinusMaxHits)){a(e.btnMinusClasseId).addClass("jfontsize-disabled")}b();return d()}});a(e.btnDefaultClasseId).click(function(){a(e.btnMinusClasseId).removeClass("jfontsize-disabled");a(e.btnPlusClasseId).removeClass("jfontsize-disabled");c=0;g.each(function(h){return a(this).css("font-size",a(this).data("initial_size")+"px")});return d()});return a(e.btnPlusClasseId).click(function(){a(e.btnMinusClasseId).removeClass("jfontsize-disabled");if(c<e.btnPlusMaxHits){c++;if(c===e.btnPlusMaxHits){a(e.btnPlusClasseId).addClass("jfontsize-disabled")}b();return d()}})}})(jQuery);

jQuery(function ($) {
  // FIXME: WORKING: but add check if is in page!
  // if ($('.entry-content p').length){
  //   $('.entry-content p').jfontsize({
  //     btnMinusClasseId: "#gtheme-fontsize-minus",
  //     btnDefaultClasseId: "#gtheme-fontsize-default",
  //     btnPlusClasseId: "#gtheme-fontsize-plus",
  //     btnMinusMaxHits: 10,
  //     btnPlusMaxHits: 10,
  //     sizeChange: 1
  //   });
  // };

  $('#text-justify, #text-unjustify').removeAttr('href').css('cursor', 'pointer');

  $('#text-justify').on('click', function (e) {
    e.preventDefault();
    $('.entry-content p').each(function () {
      $(this).css('text-align', 'justify');
    });
    $('#text-unjustify').fadeIn();
    $('#text-justify').hide();
  });

  $('#text-unjustify').on('click', function (e) {
    e.preventDefault();
    $('body.rtl .entry-content p').each(function () {
      $(this).css('text-align', 'right');
    });
    $('#text-justify').fadeIn();
    $('#text-unjustify').hide();
  });
});
