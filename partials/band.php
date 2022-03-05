<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeTemplate::customLogo( 'band', '<div class="-brand">', '</div>' );
gThemeMenu::nav( 'primary', [ 'class' => '-navigation -print-hide' ] );
// gThemeMenu::nav( 'band', [ 'class' => '-navigation -print-hide' ] );
// gThemeBootstrap::navbarNav( 'primary', FALSE, '-navigation -print-hide' );
echo '<div class="-end">';
gThemeSearch::formSimple( 'band' );
gThemeEditorial::socialite( [ 'class' => [ 'wrap-social' ] ] );
echo '</div>';
