<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( is_paged() ) {

	get_template_part( 'index', gtheme_template_base() );

} else {

	get_template_part( 'partials/home', gtheme_template_base() );
}
