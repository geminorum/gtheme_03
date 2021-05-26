<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<div class="row-item row-latest">';

	gThemeDate::date( [
		'timeago' => FALSE,
		'before'  => '<div class="row-date">',
		'after'   => '</div>',
	] );

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
