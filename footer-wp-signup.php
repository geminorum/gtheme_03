<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::wrapClose( 'signup' );

gThemeTemplate::wrapClose( 'signup' );
echo '</div></div>';

get_template_part( 'end', 'signup' );

do_action( 'gtheme_do_before_footer', 'signup' );
get_template_part( 'foot' );
