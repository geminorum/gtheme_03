<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeTemplate::wrapOpen( '404' );

	get_template_part( 'content', '404' );

gThemeTemplate::wrapClose( '404' );
gThemeTemplate::sidebar( gtheme_template_base() );
