<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

get_template_part( 'partials/navbar' );
// get_template_part( 'partials/band', gtheme_template_base() );

echo '<div class="wrapper -main">';
echo '<div class="container-lg container-wrap -main"><div class="row justify-content-center">';

get_template_part( 'partials/start', gtheme_template_base() );
