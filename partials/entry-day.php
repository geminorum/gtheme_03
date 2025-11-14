<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeContent::header( [ 'context' => 'singular', 'byline' => FALSE, 'actions' => NULL ] );

gThemeContent::content();

gThemeNavigation::content( 'singular' );
