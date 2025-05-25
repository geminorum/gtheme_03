<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

// Flow: (Cover) -> (Header/lead/Content) -> (sidebar)

echo '<div class="entry-wrap-double -cover-first">';

echo '<div class="entry-double-top"><div class="-wrap">';

gThemeSideBar::sidebar( 'entry-before', '<div class="wrap-side sidebar-entry-before">', '</div>' );

echo '</div></div><div class="-wrap splitrow"><div class="-side entry-double-head"><div class="-wrap">';

gThemeImage::image( [
	'tag'   => 'single',
	'link'  => 'attachment',
	'empty' => FALSE,
] );

echo '</div></div><div class="-side entry-double-main"><div class="-wrap">';

gThemeContent::header( [ 'context' => 'singular', 'byline' => TRUE, 'actions' => NULL ] );

gThemeEditorial::metaHTML( 'lead', [ 'before' => '<div class="entry-lead">', 'after' => '</div>', 'fallback' => 'abstract' ] );

if ( gThemeTerms::has( 'insert-people' ) )
	gThemeEditorial::personPicture( [ 'before' => '<div class="entry-person">', 'after' => '</div>' ] );

gThemeContent::content();
gThemeContent::navigation();

gThemeEditorial::theSource( [
	'before' => '<div class="entry-after after-single after-source">'.gThemeOptions::info( 'source_before', '' ),
	'after'  => '</div>',
] );

gThemeEditorial::postLikeButton( [ 'before' => '<div class="entry-after after-single after-like">', 'after' => '</div>' ] );

gThemeEditorial::refList( [
	'before' => '<div class="entry-after after-single after-reflist">',
	'after'  => '</div>',
	'title'  => gThemeOptions::info( 'reflist_title', FALSE ),
	'wrap'   => FALSE,
] );

gThemeSideBar::sidebar( 'entry-content', '<div class="wrap-side sidebar-entry-content">', '</div>' );

echo '</div></div><div class="-side entry-double-foot"><div class="-wrap">';

gThemeSideBar::sidebar( 'entry-side', '<div class="wrap-side sidebar-entry-side">', '</div>' );

echo '</div></div></div><div class="entry-double-bottom"><div class="-wrap">';

gThemeEditorial::tabsPostTabs();

gThemeSideBar::sidebar( 'entry-after', '<div class="wrap-side sidebar-entry-after">', '</div>' );

gThemeNavigation::content( 'singular' );

echo '</div></div></div>';
