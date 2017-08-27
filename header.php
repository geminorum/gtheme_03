<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

do_action( 'gtheme_do_after_header', gtheme_template_base() );
get_template_part( 'head', gtheme_template_base() );
