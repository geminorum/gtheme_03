<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '</div></div>'; // `.row`, `.container.-main`
echo '</div>'; // `.wrapper.-main`

// echo '<div class="wrapper -end"><div class="container -end"><div class="row">';
// echo '</div></div></div>';

get_template_part( 'partials/end', gtheme_template_base() );
