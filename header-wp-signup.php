<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

get_template_part( 'head' );
do_action( 'gtheme_do_after_header', 'signup' );

get_template_part( 'start', 'signup' );

gThemeTemplate::wrapOpen( 'signup' );
gThemeContent::wrapOpen( 'signup' );
