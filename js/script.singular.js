/*
	Autosize 3.0.15
	license: MIT
	http://www.jacklmoore.com/autosize
*/
!function(e,t){if("function"==typeof define&&define.amd)define(["exports","module"],t);else if("undefined"!=typeof exports&&"undefined"!=typeof module)t(exports,module);else{var n={exports:{}};t(n.exports,n),e.autosize=n.exports}}(this,function(e,t){"use strict";function n(e){function t(){var t=window.getComputedStyle(e,null);p=t.overflowY,"vertical"===t.resize?e.style.resize="none":"both"===t.resize&&(e.style.resize="horizontal"),c="content-box"===t.boxSizing?-(parseFloat(t.paddingTop)+parseFloat(t.paddingBottom)):parseFloat(t.borderTopWidth)+parseFloat(t.borderBottomWidth),isNaN(c)&&(c=0),i()}function n(t){var n=e.style.width;e.style.width="0px",e.offsetWidth,e.style.width=n,p=t,f&&(e.style.overflowY=t),o()}function o(){var t=window.pageYOffset,n=document.body.scrollTop,o=e.style.height;e.style.height="auto";var i=e.scrollHeight+c;return 0===e.scrollHeight?void(e.style.height=o):(e.style.height=i+"px",v=e.clientWidth,document.documentElement.scrollTop=t,void(document.body.scrollTop=n))}function i(){var t=e.style.height;o();var i=window.getComputedStyle(e,null);if(i.height!==e.style.height?"visible"!==p&&n("visible"):"hidden"!==p&&n("hidden"),t!==e.style.height){var r=d("autosize:resized");e.dispatchEvent(r)}}var s=void 0===arguments[1]?{}:arguments[1],a=s.setOverflowX,l=void 0===a?!0:a,u=s.setOverflowY,f=void 0===u?!0:u;if(e&&e.nodeName&&"TEXTAREA"===e.nodeName&&!r.has(e)){var c=null,p=null,v=e.clientWidth,h=function(){e.clientWidth!==v&&i()},y=function(t){window.removeEventListener("resize",h,!1),e.removeEventListener("input",i,!1),e.removeEventListener("keyup",i,!1),e.removeEventListener("autosize:destroy",y,!1),e.removeEventListener("autosize:update",i,!1),r["delete"](e),Object.keys(t).forEach(function(n){e.style[n]=t[n]})}.bind(e,{height:e.style.height,resize:e.style.resize,overflowY:e.style.overflowY,overflowX:e.style.overflowX,wordWrap:e.style.wordWrap});e.addEventListener("autosize:destroy",y,!1),"onpropertychange"in e&&"oninput"in e&&e.addEventListener("keyup",i,!1),window.addEventListener("resize",h,!1),e.addEventListener("input",i,!1),e.addEventListener("autosize:update",i,!1),r.add(e),l&&(e.style.overflowX="hidden",e.style.wordWrap="break-word"),t()}}function o(e){if(e&&e.nodeName&&"TEXTAREA"===e.nodeName){var t=d("autosize:destroy");e.dispatchEvent(t)}}function i(e){if(e&&e.nodeName&&"TEXTAREA"===e.nodeName){var t=d("autosize:update");e.dispatchEvent(t)}}var r="function"==typeof Set?new Set:function(){var e=[];return{has:function(t){return Boolean(e.indexOf(t)>-1)},add:function(t){e.push(t)},"delete":function(t){e.splice(e.indexOf(t),1)}}}(),d=function(e){return new Event(e)};try{new Event("test")}catch(s){d=function(e){var t=document.createEvent("Event");return t.initEvent(e,!0,!1),t}}var a=null;"undefined"==typeof window||"function"!=typeof window.getComputedStyle?(a=function(e){return e},a.destroy=function(e){return e},a.update=function(e){return e}):(a=function(e,t){return e&&Array.prototype.forEach.call(e.length?e:[e],function(e){return n(e,t)}),e},a.destroy=function(e){return e&&Array.prototype.forEach.call(e.length?e:[e],o),e},a.update=function(e){return e&&Array.prototype.forEach.call(e.length?e:[e],i),e}),t.exports=a});


/** jQuery jFontSize Plugin | Version 2.0 by Vincent Chabredier / Ouvrages | https://github.com/ouvrages/jfontsize **/
// (function(a){return a.fn.jfontsize=function(e){var g,b,c,f,d;g=a(this);f={btnMinusClasseId:"#jfontsize-minus",btnDefaultClasseId:"#jfontsize-default",btnPlusClasseId:"#jfontsize-plus",btnMinusMaxHits:10,btnPlusMaxHits:10,sizeChange:1};if(e){e=a.extend(f,e)}d=function(){return a.jStorage.set("jfontsize",c)};b=function(){return g.each(function(j){var k,h;if(!(a(this).data("initial_size")!=null)){k=a(this).css("font-size");k=parseInt(k.replace("px",""));a(this).data("initial_size",k)}h=a(this).data("initial_size")+(c*e.sizeChange);return a(this).css("font-size",h+"px")})};a(e.btnMinusClasseId+", "+e.btnDefaultClasseId+", "+e.btnPlusClasseId).removeAttr("href");a(e.btnMinusClasseId+", "+e.btnDefaultClasseId+", "+e.btnPlusClasseId).css("cursor","pointer");c=a.jStorage.get("jfontsize",0);if(c===(-e.btnMinusMaxHits)){a(e.btnMinusClasseId).addClass("jfontsize-disabled")}if(c===e.btnPlusMaxHits){a(e.btnPlusClasseId).addClass("jfontsize-disabled")}b();a(e.btnMinusClasseId).click(function(){a(e.btnPlusClasseId).removeClass("jfontsize-disabled");if(c>(-e.btnMinusMaxHits)){c--;if(c===(-e.btnMinusMaxHits)){a(e.btnMinusClasseId).addClass("jfontsize-disabled")}b();return d()}});a(e.btnDefaultClasseId).click(function(){a(e.btnMinusClasseId).removeClass("jfontsize-disabled");a(e.btnPlusClasseId).removeClass("jfontsize-disabled");c=0;g.each(function(h){return a(this).css("font-size",a(this).data("initial_size")+"px")});return d()});return a(e.btnPlusClasseId).click(function(){a(e.btnMinusClasseId).removeClass("jfontsize-disabled");if(c<e.btnPlusMaxHits){c++;if(c===e.btnPlusMaxHits){a(e.btnPlusClasseId).addClass("jfontsize-disabled")}b();return d()}})}})(jQuery);

jQuery(document).ready(function($){

	// $('textarea').autosize();
	autosize($('textarea'));

	// FIXME: WORKING: but add check if is in page!
	// if ($('.entry-content p').length){
	// 	$('.entry-content p').jfontsize({
	// 		btnMinusClasseId: "#gtheme-fontsize-minus",
	// 		btnDefaultClasseId: "#gtheme-fontsize-default",
	// 		btnPlusClasseId: "#gtheme-fontsize-plus",
	// 		btnMinusMaxHits: 10,
	// 		btnPlusMaxHits: 10,
	// 		sizeChange: 1
	// 	});
	// };

	$('#text-justify, #text-unjustify').removeAttr('href').css('cursor', 'pointer');
	$('#text-justify').click(function(e){e.preventDefault();
		$('.entry-content p').each(function(){$(this).css('text-align', 'justify');});
		$('#text-unjustify').fadeIn();
		$('#text-justify').hide();
	});
	$('#text-unjustify').click(function(e){e.preventDefault();
		$('body.rtl .entry-content p').each(function(){$(this).css('text-align', 'right');});
		$('#text-justify').fadeIn();
		$('#text-unjustify').hide();
	});
});
