<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

// Flow: head:(Header) -> Main:(Cover) -> Foot:(Summary/Content)

echo '<div class="entry-double-top"><div class="-wrap">';

gThemeSideBar::sidebar( 'entry-before', '<div class="wrap-side sidebar-entry-before">', '</div>' );

echo '</div></div><div class="-wrap splitrow"><div class="-side entry-double-head"><div class="-wrap">';

gThemeContent::header( [ 'context' => 'singular', 'byline' => FALSE, 'actions' => FALSE ] );

gThemeEditorial::metaHTML( 'highlight', [
	'before'   => '<div class="entry-highlight">',
	'after'    => '</div>',
	'fallback' => 'cover_blurb',
] );

echo '</div></div><div class="-side entry-double-main"><div class="-wrap">';

gThemeEditorial::theCover( [
	'link'   => 'attachment',
	'before' => '<div class="entry-cover alignleft">',
	'after'  => '</div>',
	'size'   => 'half',
	'wrap'   => FALSE,
] );

gThemeEditorial::theAction( [
	'link_class' => 'btn btn-outline-primary btn-lg btn-block', // `btn-block` is for BS4
	'span_class' => 'btn btn-outline-primary btn-lg btn-block disabled',
	'before'     => '<div class="clearfix"></div><div class="entry-after after-action after-rows d-grid gap-2">', // `d-grid gap-2` is for BS5
	'after'      => '</div>',
	'wrap'       => FALSE,
] );

gThemeEditorial::listAttachments( [
	'title'     => gThemeOptions::info( 'attachments_title', FALSE ),
	'mime_type' => gThemeOptions::info( 'attachments_mimetypes', FALSE ),
	'before'    => '<div class="clearfix"></div><div class="entry-after after-attachments after-rows">',
	'after'     => '</div>',
	'wrap'      => FALSE,
] );

gThemeEditorial::publication( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-publication after-rows">',
	'after'  => '</div>',
	'title'  => FALSE,
] );

gThemeSideBar::sidebar( 'entry-content', '<div class="wrap-side sidebar-entry-content">', '</div>' );

echo '</div></div><div class="-side entry-double-foot"><div class="-wrap">';

gThemeContent::content();

gThemeEditorial::bookMetaSummary( [
	'before' => '<div class="clearfix"></div><div class="entry-after after-meta-summary after-rows">',
	'after'  => '</div>',
] );

gThemeSideBar::sidebar( 'entry-side', '<div class="wrap-side sidebar-entry-side">', '</div>' );

echo '</div></div></div><div class="entry-double-bottom"><div class="-wrap">';

gThemeEditorial::tabsPostTabs();

gThemeSideBar::sidebar( 'entry-after', '<div class="wrap-side sidebar-entry-after">', '</div>' );

gThemeNavigation::content( 'singular', TRUE, 'publication_subject' );

echo '</div></div>';
