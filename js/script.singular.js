/* global jQuery, autosize */

/**
  Autosize 4.0.0 - 2018-01-21
  license: MIT
  http://www.jacklmoore.com/autosize
*/
!function(e,t){if("function"==typeof define&&define.amd)define(["exports","module"],t);else if("undefined"!=typeof exports&&"undefined"!=typeof module)t(exports,module);else{var n={exports:{}};t(n.exports,n),e.autosize=n.exports}}(this,function(e,t){"use strict";function n(e){function t(){var t=window.getComputedStyle(e,null);"vertical"===t.resize?e.style.resize="none":"both"===t.resize&&(e.style.resize="horizontal"),s="content-box"===t.boxSizing?-(parseFloat(t.paddingTop)+parseFloat(t.paddingBottom)):parseFloat(t.borderTopWidth)+parseFloat(t.borderBottomWidth),isNaN(s)&&(s=0),l()}function n(t){var n=e.style.width;e.style.width="0px",e.offsetWidth,e.style.width=n,e.style.overflowY=t}function o(e){for(var t=[];e&&e.parentNode&&e.parentNode instanceof Element;)e.parentNode.scrollTop&&t.push({node:e.parentNode,scrollTop:e.parentNode.scrollTop}),e=e.parentNode;return t}function r(){var t=e.style.height,n=o(e),r=document.documentElement&&document.documentElement.scrollTop;e.style.height="";var i=e.scrollHeight+s;return 0===e.scrollHeight?void(e.style.height=t):(e.style.height=i+"px",u=e.clientWidth,n.forEach(function(e){e.node.scrollTop=e.scrollTop}),void(r&&(document.documentElement.scrollTop=r)))}function l(){r();var t=Math.round(parseFloat(e.style.height)),o=window.getComputedStyle(e,null),i="content-box"===o.boxSizing?Math.round(parseFloat(o.height)):e.offsetHeight;if(i!==t?"hidden"===o.overflowY&&(n("scroll"),r(),i="content-box"===o.boxSizing?Math.round(parseFloat(window.getComputedStyle(e,null).height)):e.offsetHeight):"hidden"!==o.overflowY&&(n("hidden"),r(),i="content-box"===o.boxSizing?Math.round(parseFloat(window.getComputedStyle(e,null).height)):e.offsetHeight),a!==i){a=i;var l=d("autosize:resized");try{e.dispatchEvent(l)}catch(e){}}}if(e&&e.nodeName&&"TEXTAREA"===e.nodeName&&!i.has(e)){var s=null,u=e.clientWidth,a=null,c=function(){e.clientWidth!==u&&l()},p=function(t){window.removeEventListener("resize",c,!1),e.removeEventListener("input",l,!1),e.removeEventListener("keyup",l,!1),e.removeEventListener("autosize:destroy",p,!1),e.removeEventListener("autosize:update",l,!1),Object.keys(t).forEach(function(n){e.style[n]=t[n]}),i.delete(e)}.bind(e,{height:e.style.height,resize:e.style.resize,overflowY:e.style.overflowY,overflowX:e.style.overflowX,wordWrap:e.style.wordWrap});e.addEventListener("autosize:destroy",p,!1),"onpropertychange"in e&&"oninput"in e&&e.addEventListener("keyup",l,!1),window.addEventListener("resize",c,!1),e.addEventListener("input",l,!1),e.addEventListener("autosize:update",l,!1),e.style.overflowX="hidden",e.style.wordWrap="break-word",i.set(e,{destroy:p,update:l}),t()}}function o(e){var t=i.get(e);t&&t.destroy()}function r(e){var t=i.get(e);t&&t.update()}var i="function"==typeof Map?new Map:function(){var e=[],t=[];return{has:function(t){return e.indexOf(t)>-1},get:function(n){return t[e.indexOf(n)]},set:function(n,o){e.indexOf(n)===-1&&(e.push(n),t.push(o))},delete:function(n){var o=e.indexOf(n);o>-1&&(e.splice(o,1),t.splice(o,1))}}}(),d=function(e){return new Event(e,{bubbles:!0})};try{new Event("test")}catch(e){d=function(e){var t=document.createEvent("Event");return t.initEvent(e,!0,!1),t}}var l=null;"undefined"==typeof window||"function"!=typeof window.getComputedStyle?(l=function(e){return e},l.destroy=function(e){return e},l.update=function(e){return e}):(l=function(e,t){return e&&Array.prototype.forEach.call(e.length?e:[e],function(e){return n(e,t)}),e},l.destroy=function(e){return e&&Array.prototype.forEach.call(e.length?e:[e],o),e},l.update=function(e){return e&&Array.prototype.forEach.call(e.length?e:[e],r),e}),t.exports=l});


// jQuery jFontSize Plugin | Version 2.0 by Vincent Chabredier / Ouvrages | https://github.com/ouvrages/jfontsize
// (function(a){return a.fn.jfontsize=function(e){var g,b,c,f,d;g=a(this);f={btnMinusClasseId:"#jfontsize-minus",btnDefaultClasseId:"#jfontsize-default",btnPlusClasseId:"#jfontsize-plus",btnMinusMaxHits:10,btnPlusMaxHits:10,sizeChange:1};if(e){e=a.extend(f,e)}d=function(){return a.jStorage.set("jfontsize",c)};b=function(){return g.each(function(j){var k,h;if(!(a(this).data("initial_size")!=null)){k=a(this).css("font-size");k=parseInt(k.replace("px",""));a(this).data("initial_size",k)}h=a(this).data("initial_size")+(c*e.sizeChange);return a(this).css("font-size",h+"px")})};a(e.btnMinusClasseId+", "+e.btnDefaultClasseId+", "+e.btnPlusClasseId).removeAttr("href");a(e.btnMinusClasseId+", "+e.btnDefaultClasseId+", "+e.btnPlusClasseId).css("cursor","pointer");c=a.jStorage.get("jfontsize",0);if(c===(-e.btnMinusMaxHits)){a(e.btnMinusClasseId).addClass("jfontsize-disabled")}if(c===e.btnPlusMaxHits){a(e.btnPlusClasseId).addClass("jfontsize-disabled")}b();a(e.btnMinusClasseId).click(function(){a(e.btnPlusClasseId).removeClass("jfontsize-disabled");if(c>(-e.btnMinusMaxHits)){c--;if(c===(-e.btnMinusMaxHits)){a(e.btnMinusClasseId).addClass("jfontsize-disabled")}b();return d()}});a(e.btnDefaultClasseId).click(function(){a(e.btnMinusClasseId).removeClass("jfontsize-disabled");a(e.btnPlusClasseId).removeClass("jfontsize-disabled");c=0;g.each(function(h){return a(this).css("font-size",a(this).data("initial_size")+"px")});return d()});return a(e.btnPlusClasseId).click(function(){a(e.btnMinusClasseId).removeClass("jfontsize-disabled");if(c<e.btnPlusMaxHits){c++;if(c===e.btnPlusMaxHits){a(e.btnPlusClasseId).addClass("jfontsize-disabled")}b();return d()}})}})(jQuery);

jQuery(function ($) {
  autosize($('textarea'));

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

  $('#text-justify').click(function (e) {
    e.preventDefault();
    $('.entry-content p').each(function () {
      $(this).css('text-align', 'justify');
    });
    $('#text-unjustify').fadeIn();
    $('#text-justify').hide();
  });

  $('#text-unjustify').click(function (e) {
    e.preventDefault();
    $('body.rtl .entry-content p').each(function () {
      $(this).css('text-align', 'right');
    });
    $('#text-justify').fadeIn();
    $('#text-unjustify').hide();
  });
});
