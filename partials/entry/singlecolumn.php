<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="entry-wrap-singlecolumn">';

	gThemeSideBar::sidebar( 'entry-before', '<div class="wrap-side sidebar-entry-before">', '</div>' );
	gThemeContent::image( 'singular' );
	gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );
	gThemeContent::content();
	gThemeSideBar::sidebar( 'entry-content', '<div class="wrap-side sidebar-entry-content">', '</div>' );
	gThemeSideBar::sidebar( 'entry-side', '<div class="wrap-side sidebar-entry-side">', '</div>' );
	gThemeSideBar::sidebar( 'entry-after', '<div class="wrap-side sidebar-entry-after">', '</div>' );
	gThemeEditorial::tabsPostTabs();
	gThemeNavigation::content( 'singular', TRUE );

echo '</div>';
