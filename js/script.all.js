jQuery(document).ready(function($){
	$("a.scroll").click(function(e){e.preventDefault();$('html,body').animate({scrollTop:$(this.hash).offset().top}, 500);});
	$('a.scroll-to-top').click(function(e){e.preventDefault();$('html, body').animate({scrollTop:0},'slow');});
	$('img').error(function(){$(this).hide();});
	$('a[href="#"]').click( function(e){e.preventDefault();});
});