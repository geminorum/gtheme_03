<?php

// NOTE: SAMPLE

if ( $ltr ) {

	$css = array(

		// 'style-ltr.css',
		'style.css',

		'../../../plugins/gnetwork/assets/css/front.all.raw.css',
		'../../../plugins/geditorial/assets/css/front.all.raw.css',
	);

} else {

	$css = array(

		'style.css',

		'../../../plugins/gnetwork/assets/css/front.all.raw.css',
		'../../../plugins/geditorial/assets/css/front.all.raw.css',
	);
}

// echo '@charset "UTF-8";';
include_css( $css );
