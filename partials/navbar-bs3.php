<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<nav ';
	gThemeBootstrap::navbarClass( 'wrapper -navbar' );
echo '><div class="container -nav">';

	gThemeBootstrap::navbarHeader();

	echo '<div id="navbar" class="navbar-collapse collapse yamm">';

		// gThemeBootstrap::navbarForm( NULL, 'navbar-right' );
		gThemeBootstrap::navbarNav( 'tertiary', FALSE, 'navbar-right' );
		gThemeBootstrap::navbarNav( 'secondary', FALSE, 'navbar-left' );

echo '</div></div></nav>'."\n";
