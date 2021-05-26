<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'latest' ] );
gThemeContent::content();
gThemeContent::rows( 'latest', '<div class="entry-after after-latest after-rows">', '</div>' );

gThemePages::link( 'archives', [
	'class'  => 'btn btn-outline-secondary',
	'before' => '<div class="entry-after after-navigate after-buttons">',
	'after'  => '</div>',
] );

gThemeUtilities::enqueueTimeAgo();
