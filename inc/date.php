<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeDate extends gThemeModuleCore
{

	// post date once per day
	// @REF: `the_date()`, `is_new_day()`
	public static function once( $atts = [] )
	{
		global $currentday, $previousday;

		if ( $currentday === $previousday )
			return '';

		$previousday = $currentday;

		$atts['link'] = FALSE; // no link for date archive

		return self::date( $atts );
	}

	// ANCESTOR: `gtheme_the_date()`
	public static function date( $atts = [] )
	{
		$args = self::atts( [
			'post'      => NULL,
			'before'    => '',
			'after'     => '',
			'context'   => 'single',
			'prefix'    => 'entry',
			'format'    => gThemeOptions::info( 'date_format_byline', _x( 'j M Y', 'Options: Defaults: Date Format: Byline', 'gtheme' ) ),
			'template'  => gThemeOptions::info( 'template_the_date', '<span class="date"><a href="%1$s"%2$s><time class="%5$s-time do-timeago" datetime="%3$s">%4$s</time></a></span>' ),
			'shortlink' => TRUE,
			'title'     => NULL,
			'text'      => NULL, // override text
			'timeago'   => TRUE, // enqueue time ago script
			'meta'      => TRUE,
			'link'      => TRUE, // custom or disable
			'echo'      => TRUE,
		], $atts );

		if ( ! $post = get_post( $args['post'] ) )
			return '';

		if ( ! in_array( $post->post_type, (array) gThemeOptions::info( 'date_posttypes', [ 'post', 'entry' ] ) ) )
			return '';

		if ( ! $args['link'] )
			$link = FALSE;

		else if ( TRUE === $args['link'] )
			$link = $args['shortlink']
				? wp_get_shortlink( $post->ID )
				: apply_filters( 'the_permalink', get_permalink( $post ), $post );

		else
			$link = $args['link'];

		$override = apply_filters( 'gtheme_date_override_the_date', NULL, $post, $link, $args );

		if ( FALSE === $override )
			return '';

		if ( is_null ( $override ) )
			$html = vsprintf( $args['template'], [
				$link ? esc_url( $link ) : '#',
				// self::context( $post, 'y/n/j' ),
				$args['shortlink'] ? ' rel="shortlink"' : '',
				esc_attr( get_the_date( 'c', $post ) ),
				esc_html( get_the_date( $args['format'], $post ) ),
				$args['prefix'],
			] );

		else
			$html = $override;

		// only if not overrided
		if ( $args['timeago'] && is_null ( $override ) )
			gThemeUtilities::enqueueTimeAgo();

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	// FIXME: DRAFT: NOT WORKING
	public static function context( $post = NULL, $format = 'j F Y' )
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
