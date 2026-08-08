<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="entry-wrap-doublecolumn entry-wrap-double">';

	echo '<div class="entry-double-top"><div class="-wrap">';
		gThemeSideBar::sidebar( 'entry-before', '<div class="wrap-side sidebar-entry-before">', '</div>' );
	echo '</div></div>';

	echo '<div class="-wrap -leaned-area-wrap -leaned-area-start">';
	// echo '<div class="-wrap -leaned-area-wrap -leaned-area-end">';
		echo '<div class="-leaned-area entry-double-head"><div class="-wrap">';

			gThemeContent::header( [
				'context' => 'singular',
				'byline'  => TRUE,
				'actions' => NULL,
			] );

		echo '</div></div>';
		echo '<div class="-leaned-area entry-double-image"><div class="-wrap">';

			gThemeContent::image( 'singular' );

		echo '</div></div>';
		echo '<div class="-leaned-area -leaned-area-column entry-double-main"><div class="-wrap">';

			gThemeContent::content();
			gThemeSideBar::sidebar( 'entry-content', '<div class="wrap-side sidebar-entry-content">', '</div>' );

		echo '</div></div>';
		echo '<div class="-leaned-area -leaned-area-offset entry-double-side"><div class="-wrap">';

			gThemeSideBar::sidebar( 'entry-side', '<div class="wrap-side sidebar-entry-side">', '</div>' );

		echo '</div></div>';

	echo '</div>'; // `.-leaned-area-wrap`

	echo '<div class="entry-double-bottom"><div class="-wrap">';

		gThemeSideBar::sidebar( 'entry-after', '<div class="wrap-side sidebar-entry-after">', '</div>' );
		gThemeEditorial::tabsPostTabs();
		gThemeNavigation::content( 'singular' );

	echo '</div></div>';
echo '</div>';
