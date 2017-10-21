<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

// header
	get_header( gtheme_template_base() );

// before
// echo "\n\n".'<!-- START: START -->'."\n\n";
	// get_template_part( 'start', gtheme_template_base() );
// echo "\n\n".'<!-- START: END -->'."\n\n";

// template
echo "\n\n".'<!-- TEMPLATE: START -->'."\n\n";
	include gtheme_template_path();
echo "\n\n".'<!-- TEMPLATE: END -->'."\n\n";

// end
// echo "\n\n".'<!-- END: START -->'."\n\n";
	// get_template_part( 'end', gtheme_template_base() );
// echo "\n\n".'<!-- END: END -->'."\n\n";

// footer
	get_footer( gtheme_template_base() );
