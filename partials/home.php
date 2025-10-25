<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

get_template_part( 'partials/dashboard', gtheme_template_base() );
// gThemeSideBar::renderWidgetShelfs( 'home' ); // NOTE: WORKING EXAMPLE
// gThemeContent::masonry( 'home', '<div class="col">', '</div>', NULL ); // NOTE: WORKING EXAMPLE
get_template_part( 'index', gtheme_template_base() );
