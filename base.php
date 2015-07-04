<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// header
	get_header( gtheme_template_base() );

// before
echo "\n\n".'<!-- BEFORE: START -->'."\n\n";
	get_template_part( 'before', gtheme_template_base() );
echo "\n\n".'<!-- BEFORE: END -->'."\n\n";

// template
echo "\n\n".'<!-- TEMPLATE: START -->'."\n\n";
	include gtheme_template_path();
echo "\n\n".'<!-- TEMPLATE: END -->'."\n\n";

// after
echo "\n\n".'<!-- AFTER: START -->'."\n\n";
	get_template_part( 'after', gtheme_template_base() );
echo "\n\n".'<!-- AFTER: END -->'."\n\n";

// sidebar
echo "\n\n".'<!-- SIDEBAR: START -->'."\n\n";
	get_sidebar( gtheme_template_base() );
echo "\n\n".'<!-- SIDEBAR: END -->'."\n\n";

// end
echo "\n\n".'<!-- END: START -->'."\n\n";
	get_template_part( 'end', gtheme_template_base() );
echo "\n\n".'<!-- END: END -->'."\n\n";

// footer
	get_footer( gtheme_template_base() );
	