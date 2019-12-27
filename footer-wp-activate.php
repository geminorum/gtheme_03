<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapClose( 'activate' );

gThemeTemplate::wrapClose( 'activate' );

get_template_part( 'end', 'activate' );

do_action( 'gtheme_do_before_footer', 'activate' );
get_template_part( 'foot' );
