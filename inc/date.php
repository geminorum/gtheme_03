<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeDate extends gThemeModuleCore
{

	// FIXME: UNFINISHED
	// ANCESTOR: gtheme_the_date()
	public static function date( $atts = [] )
	{
		$post_id  = get_the_ID();
		$template = '<span class="date"><a href="%1$s" title="%2$s" rel="shortlink"><time class="%5$s-date" datetime="%3$s">%4$s</time></a></span>';

		$args = self::atts( [
			'before'      => '',
			'after'       => '',
			'context'     => 'single',
			'prefix'      => 'entry',
			'post_id'     => $post_id,
			'format'      => gThemeOptions::info( 'date_format_byline', _x( 'j M Y', 'Options: Defaults: Date Format: Byline', GTHEME_TEXTDOMAIN ) ),
			'template'    => gThemeOptions::info( 'template_the_date', $template ),
			'onceperdate' => FALSE,
			'shortlink'   => TRUE,
			'title'       => NULL,
			'text'        => NULL, // override text
			'meta'        => TRUE,
			'link'        => TRUE, // disable linking compeletly
			'echo'        => TRUE, // disable linking compeletly
		], $atts );

		if ( $args['onceperdate'] ) {
			$args['post_id'] = $post_id; // NOTE: must be global b/c we also use the_date()
			$date = the_date( $args['format'], '', '', FALSE );
		} else {
			$date = get_the_date( $args['format'], $args['post_id'] );
		}

		$link = $args['shortlink']
			? wp_get_shortlink( $args['post_id'] )
			: get_the_permalink( $args['post_id'] );

		$html = vsprintf( $args['template'], [
			esc_url( $link ),

			// self::context( $args['post_id'], 'y/n/j' ),
			'',

			// gThemePost::titleAttr( FALSE, NULL, TRUE ),
			// esc_attr( sprintf( __( 'Permalink to %s', GTHEME_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ) ),
			esc_attr( get_the_date( 'c', $args['post_id'] ) ), // FIXME: must be % ago

			esc_html( $date ),

			$args['prefix'],
		] );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	// FIXME: DRAFT: NOT WORKING
	public static function context( $id = NULL, $format = 'j F Y' )
	{
		// if ( class_exists( 'gPersianDate' ) )
		// 	echo gPersianDate::the_context_time( $format, FALSE );
	}

	// FIXME: DRAFT: WORKING
	// http://php.net/manual/en/function.date.php
	public static function entryDate()
	{
		$date = get_the_date( 'Y/m/j' );
		echo '<div class="entry-date hidden-print" title="'.esc_attr( $date ).'">';
			echo '<div class="entry-day"><a href="'.wp_get_shortlink( 0, 'query' ).'">'.get_the_date( 'j' ).'</a></div>';
			// echo '<div class="entry-month"><a href="'.get_month_link( '', '' ).'">'.get_the_date( 'F' ).'</a></div>';
			echo '<div class="entry-month">'.get_the_date( 'F' ).'</div>';
		echo '</div>';
		echo '<div class="visible-print-inline-block">'.$date.'</div>';
	}

	// FIXME: DRAFT: WORKING
	public static function arabicDate( $format = 'j F Y' )
	{
		if ( class_exists( 'gPersianDateDate' ) )
			echo gPersianDateDate::toHijri( $format );
			// echo gPersianDateDate::to( $format );
	}

	// FIXME: DRAFT: WORKING
	public static function doubleArchive()
	{
		if ( ! class_exists( 'gPersianDateArchives' ) )
			return;

		echo gPersianDateArchives::getCompact( [
			'post_type'   => [ 'post', 'reshare', 'issue' ],
			'link_anchor' => TRUE,
		] );

		echo gPersianDateArchives::getClean( [
			'post_type'     => [ 'post', 'reshare', 'issue' ],
			'comment_count' => TRUE,
			'row_context'   => 'latest',
			// 'css_class'     => 'entry-after after-latest after-rows',
		] );

		// echo '<ul>';
		// gPersianDateArchives::get( [ 'type' => 'daily' ] );
		// echo '</ul>';
	}
}
