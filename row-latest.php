<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="row-item row-latest">';

	gThemeDate::once( [
		'timeago' => FALSE,
		'before'  => '<div class="row-date">',
		'after'   => '</div>',
	] );

	echo '<div class="wrap-header">';

		gThemeContent::header( [
			'wrap_tag'  => 'div',
			'context'   => 'list',
			'prefix'    => 'row',
			'title_tag' => 'h4',
			'meta_tag'  => 'h6',
			'shortlink' => FALSE, // short link is on date
		] );

		gThemeContent::byline( NULL, '<div class="row-byline">', '</div>' );

	echo '</div>';
echo '</div>';
