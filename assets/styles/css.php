<?php

// NOTE: SAMPLE

if ( $ltr ) {

	$css = [

		// 'style-ltr.css',
		'style.css',

		'../../../plugins/gnetwork/assets/css/front.all-rtl.css',
		'../../../plugins/geditorial/assets/css/front.all-rtl.css',

		'../../../plugins/gnetwork/assets/css/signup.all-rtl.css',
		'../../../plugins/gnetwork/assets/css/activate.all-rtl.css',
	];

} else {

	$css = [

		'style.css',

		'../../../plugins/gnetwork/assets/css/front.all.css',
		'../../../plugins/geditorial/assets/css/front.all.css',

		'../../../plugins/gnetwork/assets/css/signup.all.css',
		'../../../plugins/gnetwork/assets/css/activate.all.css',
	];
}

include_css( $css );
