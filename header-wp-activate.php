<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

get_template_part( 'head' );
do_action( 'gtheme_do_after_header', 'activate' );

get_template_part( 'start', 'activate' );

gThemeTemplate::wrapOpen( 'activate' );
gThemeContent::wrapOpen( 'activate' );
