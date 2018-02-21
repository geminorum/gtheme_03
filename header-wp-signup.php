<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

defined( 'GTHEME_SOCIAL_META_DISABLED' ) or define( 'GTHEME_SOCIAL_META_DISABLED', TRUE );

get_template_part( 'head' );
do_action( 'gtheme_do_after_header', gtheme_template_base() );

get_template_part( 'start', gtheme_template_base() );

echo '<div class="container -main -signup"><div class="row">';
echo '<div class="col-sm-12 wrap-content" id="content">';

gThemeContent::wrapOpen( gtheme_template_base() );
