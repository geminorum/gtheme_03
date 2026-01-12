<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

get_template_part( 'partials/navbar' );
// get_template_part( 'partials/band', gtheme_template_base() );

gThemeWrap::wrapperOpen( 'main', 'justify-content-center' );

get_template_part( 'partials/start', gtheme_template_base() );
