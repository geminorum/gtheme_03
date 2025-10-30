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

		$atts['context'] = 'once';  // @see: `date_override_the_date` on Editorial Module
		$atts['link']    = NULL;    // archive link for date archive

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
			'template'  => gThemeOptions::info( 'template_the_date', '<span class="date">%1$s<time datetime="%3$s" class="%5$s">%4$s</time>%2$s</span>' ),
			'shortlink' => TRUE,
			'title'     => NULL, // WTF?!
			'text'      => NULL, // override text
			'timeago'   => NULL, // enqueue time ago script
			'meta'      => TRUE,
			'link'      => TRUE, // `NULL`: post-date archives, `FALSE`: disable, `{string}`: custom links
			'echo'      => TRUE,
		], $atts );

		if ( ! $post = get_post( $args['post'] ) )
			return '';

		if ( ! in_array( $post->post_type, (array) gThemeOptions::info( 'date_posttypes', [ 'post', 'entry' ] ) ) )
			return '';

		if ( is_null( $args['link'] ) )
			$link = self::getDayLink( $post );

		else if ( ! $args['link'] )
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

		if ( is_null( $override ) )
			$html = vsprintf( $args['template'], [
				$link ? sprintf( '<a href="%s" %s>', esc_url( $link ), $args['shortlink'] ? ' rel="shortlink"' : '' ) : '',
				$link ? '</a>' : '',
				esc_attr( get_post_time( \DATE_W3C, FALSE, $post, FALSE ) ),
				esc_html( get_the_date( $args['format'], $post ) ),
				gThemeHTML::prepClass(
					sprintf( '%s-time time-%s', $args['prefix'], $args['context'] ),
					FALSE !== $args['timeago'] ? 'do-timeago' : ''
				),
			] );

		else
			$html = $override;

		// only if not overrides
		if ( FALSE !== $args['timeago'] && is_null( $override ) )
			gThemeUtilities::enqueueTimeAgo();

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		echo $args['before'].$html.$args['after'];
	}

	public static function getDayLink( $post, $context = NULL )
	{
		return get_day_link(
			gThemeNumber::intval( get_post_time( 'Y', FALSE, $post, TRUE ), FALSE ),
			gThemeNumber::intval( get_post_time( 'm', FALSE, $post, TRUE ), FALSE ),
			gThemeNumber::intval( get_post_time( 'd', FALSE, $post, TRUE ), FALSE )
		);
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
