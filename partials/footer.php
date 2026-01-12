<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeMenu::navNetwork( '<div class="wrapper -footer"><div class="container-wrap -footer '.gThemeOptions::info( 'wrap_container_class', 'container-xl' ).'">', '</div></div>' );

gThemeWrap::wrapperOpen( 'copyright', FALSE, 'text-center' );
	gThemeTemplate::copyright();
gThemeWrap::wrapperClose( 'copyright', 2 );
