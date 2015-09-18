<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeDate extends gThemeModuleCore
{

	// FIXME: UNFINISHED
	//ANCESTOR: gtheme_the_date()
	public static function date( $atts = array() )
	{
		$args = self::atts( array(
			'before'      => '',
			'after'       => '',
			'context'     => 'single',
			'prefix'      => 'entry',
			'format'      => gThemeOptions::info( 'the_date_format', 'j M Y' ),
			'onceperdate' => FALSE,
			'shortlink'   => TRUE,
			'title'       => NULL,
			'text'        => NULL, // override text
			'meta'        => TRUE,
			'link'        => TRUE, // disable linking compeletly
			'echo'        => TRUE, // disable linking compeletly
		), $atts );

		global $post;

		$date = sprintf( '<span class="date"><a href="%1$s" title="%2$s" rel="shortlink"><time class="entry-date" datetime="%3$s">%4$s</time></a></span>',
			esc_url( wp_get_shortlink( $post->ID ) ),
			// the_context_time( 'y/n/j', false ),
			'',
			//gThemePost::titleAttr( false, null, true ),
			//esc_attr( sprintf( __( 'Permalink to %s', 'twentythirteen' ), the_title_attribute( 'echo=0' ) ) ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( ( $args['onceperdate'] ? the_date( $args['format'], '', '', false ) : get_the_date( $args['format'] ) ) )
		);

		if ( ! $args['echo'] )
			return $args['before'].$date.$args['after'];

		echo $args['before'].$date.$args['after'];
	}

	// DRAFT: WORKING
	// http://php.net/manual/en/function.date.php
	public static function entryDate()
	{
		$date = get_the_date( 'Y/m/j' );
		echo '<div class="entry-date hidden-print" title="'.esc_attr( $date ).'">';
			echo '<div class="entry-day"><a href="'.wp_get_shortlink( 0, 'query' ).'">'.get_the_date( 'j' ).'</a></div>';
			//echo '<div class="entry-month"><a href="'.get_month_link( '', '' ).'">'.get_the_date( 'F' ).'</a></div>';
			echo '<div class="entry-month">'.get_the_date( 'F' ).'</div>';
		echo '</div>';
		echo '<div class="visible-print-inline-block">'.$date.'</div>';
	}

	// DRAFT: WORKING
	public static function arabicDate( $format = 'j F Y' )
	{
		if ( class_exists( 'gPersianDateDate' ) )
			echo gPersianDateDate::toHijri( $format );
			// echo gPersianDateDate::to( $format );
	}
}
