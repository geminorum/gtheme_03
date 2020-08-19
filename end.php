<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '</div></div>'; // `.row`, `.container.-main`
echo '</div>'; // `.wrapper.-main`

get_template_part( 'partials/end', gtheme_template_base() );
get_template_part( 'partials/footer', gtheme_template_base() );
