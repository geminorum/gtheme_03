<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<nav class="wrapper -navbar"><div class="container container-wrap -nav">';

	echo '<div class="-branding">';
		gThemeTemplate::logo( 'navbar', '<h1><a class="navbar-brand no-outline" href="{{home_url}}" title="{{{logo_title}}}" rel="home">{{site_name}}</a></h1>' );
		gThemeTemplate::about( '<small class="navbar-text">', '</small>' );
	echo '</div>';

	echo '<div class="-rule"></div>';

	gThemeMenu::nav( 'primary', [ 'class' => '-navigation -print-hide' ] );

echo '</div></nav>'."\n";
