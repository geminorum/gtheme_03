// FontDetect v3.0.1 | 20150310 | https://github.com/JenniferSimonds/FontDetect
FontDetect=function(){function e(){if(!n){n=!0;var e=document.body,t=document.body.firstChild,i=document.createElement("div");i.id="fontdetectHelper",r=document.createElement("span"),r.innerText="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",i.appendChild(r),e.insertBefore(i,t),i.style.position="absolute",i.style.visibility="hidden",i.style.top="-200px",i.style.left="-100000px",i.style.width="100000px",i.style.height="200px",i.style.fontSize="100px"}}function t(e,t){return e instanceof Element?window.getComputedStyle(e).getPropertyValue(t):window.jQuery?$(e).css(t):""}var n=!1,i=["serif","sans-serif","monospace","cursive","fantasy"],r=null;return{onFontLoaded:function(t,i,r,o){if(t){var s=o&&o.msInterval?o.msInterval:100,a=o&&o.msTimeout?o.msTimeout:2e3;if(i||r){if(n||e(),this.isFontLoaded(t))return void(i&&i(t));var l=this,f=(new Date).getTime(),d=setInterval(function(){if(l.isFontLoaded(t))return clearInterval(d),void i(t);var e=(new Date).getTime();e-f>a&&(clearInterval(d),r&&r(t))},s)}}},isFontLoaded:function(t){var o=0,s=0;n||e();for(var a=0;a<i.length;++a){if(r.style.fontFamily='"'+t+'",'+i[a],o=r.offsetWidth,a>0&&o!=s)return!1;s=o}return!0},whichFont:function(e){for(var n=t(e,"font-family"),r=n.split(","),o=r.shift();o;){o=o.replace(/^\s*['"]?\s*([^'"]*)\s*['"]?\s*$/,"$1");for(var s=0;s<i.length;s++)if(o==i[s])return o;if(this.isFontLoaded(o))return o;o=r.shift()}return null}}}();
jQuery(document).ready(function($){"use strict";
	$('html').removeClass('no-js');
	
	$.each($('html').data('font-stack'), function(i,v) {
		var wf=v,wt=wf.toLowerCase().replace(/'/g, '').replace(/\s+/g, '-');
		FontDetect.onFontLoaded(wf, function(){
			$('body').addClass('wf-'+wt);
			console.log("font loaded: " + wf);
		}, function(){
			$('body').addClass('wf-not-'+wt);
			console.log("font not loaded: " + wf);
		},{msTimeout:3000});
	});	
	
	$("a.scroll").click(function(e){e.preventDefault();$('html,body').animate({scrollTop:$(this.hash).offset().top}, 500);});
	$('a.scroll-to-top').click(function(e){e.preventDefault();$('html, body').animate({scrollTop:0},'slow');});
	$('img').error(function(){$(this).hide();});
	$('a[href="#"]').click( function(e){e.preventDefault();});
	
	// $('[data-toggle="tooltip"]').tooltip();
});