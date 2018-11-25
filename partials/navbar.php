<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<nav class="wrapper -navbar"><div class="container -nav">';

	echo '<div class="-branding">';
		gThemeTemplate::logo( 'navbar', '<h1><a href="%1$s" title="%3$s" rel="home">%2$s</a></h1>' );
		gThemeTemplate::description();
	echo '</div>';

	echo '<div class="-rule"></div>';

	gThemeMenu::nav( 'primary', [ 'class' => '-navigation -print-hide' ] );

echo '</div></nav>'."\n";
