<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapClose( gtheme_template_base() );

echo '</div></div></div>';

get_template_part( 'end', gtheme_template_base() );

do_action( 'gtheme_do_before_footer', gtheme_template_base() );
get_template_part( 'foot' );
