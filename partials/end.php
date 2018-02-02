<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeMenu::nav( 'foot-nav', array(
	'depth'     => 1,
	'container' => '',
	'class'     => 'list-inline list-unstyled',
), '<div class="wrapper -footer"><div class="container -footer">', '</div></div>' );

echo '<div class="wrapper -copyright"><div class="container -copyright">';
	echo '<p class="copyright text-muted credit">';
		gThemeTemplate::copyright( '', '' );
		gThemeEditorial::siteModified( array(
			'title'  => FALSE,
			'before' => ' '._x( 'Last updated on', 'Root: End: Before Site Modified', GTHEME_TEXTDOMAIN ).' ',
		) );
echo '</p></div></div>';
