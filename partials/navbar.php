<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<nav class="wrapper -navbar"><div class="container -nav">';

	gThemeTemplate::logo( 'navbar', '<h1><a href="%1$s" title="%3$s" rel="home">%2$s</a></h1>' );

	echo '<div class="-rule"></div>';

	gThemeMenu::nav( 'primary', [ 'class' => 'list-inline' ] );

echo '</div></nav>'."\n";
