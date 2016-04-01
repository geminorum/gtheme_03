<?php

// NOTE: SAMPLE

if ( $ltr ) {
	
	$css = array(

		// '../../gtheme_03/libs/flexslider.css',
		// '../../gtheme_03/libs/zoom.css',
		// '../packages/flexslider/flexslider.css',

		// 'style-ltr.css',
		'style.css',

		'../../../plugins/gnetwork/assets/css/front.all.raw.css',
		'../../../plugins/geditorial/assets/css/front.all.raw.css',
	);
	
} else {
	
	$css = array(

		// '../../gtheme_03/libs/flexslider-rtl.css',
		// '../../gtheme_03/libs/zoom.css',
		// '../packages/flexslider/flexslider.css',

		'style.css',

		'../../../plugins/gnetwork/assets/css/front.all.raw.css',
		'../../../plugins/geditorial/assets/css/front.all.raw.css',
	);
}

// echo '@charset "UTF-8";';
include_css( $css );
