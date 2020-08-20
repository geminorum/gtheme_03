<?php //////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// NOTE: SAMPLE

////////////////////////////////////////////////////////////////////////////////
$direction = $ltr ? '' : '-rtl';

// supported groups other than `main`
$groups = [
	// 'news',
	// 'dev',
];

$styles = [
	// works with setting `GNETWORK_DISABLE_FRONT_STYLES`
	"../../../plugins/gnetwork/assets/css/front.all{$direction}.css",

	// works with setting `GEDITORIAL_DISABLE_FRONT_STYLES`
	"../../../plugins/geditorial/assets/css/front.all{$direction}.css", // or by module, see below
	// "../../../plugins/geditorial/assets/css/front.headings{$direction}.css",
	// "../../../plugins/geditorial/assets/css/front.like{$direction}.css",
	// "../../../plugins/geditorial/assets/css/front.entry{$direction}.css",
];

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
if ( in_array( $group, $groups ) ) {

	$styles = array_merge( [

		"{$group}.screen{$direction}.css",
		// "../../gtheme_03/css/{$group}.screen{$direction}.css",

	], $styles );

} else {

	$styles = array_merge( [

		"main.screen{$direction}.css",
		// "../../gtheme_03/css/main.screen{$direction}.css",

		// only on main group which typically is on the main site of the network
		"../../../plugins/gnetwork/assets/css/signup.all{$direction}.css",
		"../../../plugins/gnetwork/assets/css/activate.all{$direction}.css",

	], $styles );
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
include_css( $styles );
