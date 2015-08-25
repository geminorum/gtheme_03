<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEditorial extends gThemeModuleCore
{

	// FIXME: add theme classes / before / after
	public static function series( $atts = array() )
	{
		if ( class_exists( 'gEditorialSeriesTemplates' ) )
			echo gEditorialSeriesTemplates::shortcode_series( $atts );
	}

	// old: gmeta_lead()
	public static function label( $atts = array() )
	{
		if ( class_exists( 'gEditorialMetaTemplates' ) )
			echo gEditorialMetaTemplates::metaLabel( $atts );
	}

	public static function meta( $field, $atts = array() )
	{
		if ( class_exists( 'gEditorialMetaTemplates' ) ) {

			$args = self::atts( array(
				'before'  => '',
				'after'   => '',
				'filter'  => FALSE,
				'post_id' => NULL,
				'echo'    => TRUE,
			), $atts );

			$atts['echo'] = FALSE;
			$html = gEditorialMetaTemplates::meta( $field, $args['before'], $args['after'], $args['filter'], $args['post_id'], $atts );

			if ( $html ) {
				if ( ! $args['echo'] )
					return $html;

				echo $html;
			}
		}
	}

	public static function lead( $atts = array() )
	{
		if ( class_exists( 'gEditorialMetaTemplates' ) ) {

			$args = self::atts( array(
				'before'  => '',
				'after'   => '',
				'filter'  => FALSE,
				'post_id' => NULL,
				'echo'    => TRUE,
			), $atts );

			$atts['echo'] = FALSE;
			$html = gEditorialMetaTemplates::gmeta_lead( $args['before'], $args['after'], $args['filter'], $atts );

			if ( $html ) {
				if ( ! $args['echo'] )
					return $html;

				echo $html;
			}
		}
	}

	public static function refList( $atts = array() )
	{
		global $gNetwork;

		if ( ! is_object( $gNetwork )
			|| ! isset( $gNetwork->shortcodes ) )
				return;

		if ( ! method_exists( $gNetwork->shortcodes, 'shortcode_reflist' ) )
			return;

		$args = self::atts( array(
			'before'       => '',
			'after'        => '',
			'echo'         => TRUE,
			'context'      => 'single',
			'number'       => TRUE,
			'after_number' => '. ',
			'back'         => '[^]', //'[&#8617;]', // TODO: add theme option for this
			'title' => '',
		), $atts );

		$html = $gNetwork->shortcodes->shortcode_reflist( $args, NULL, 'reflist' );

		if ( $html ) {
			$html = $args['before'].$args['title'].apply_filters( 'the_content', $html ).$args['after'];

			if ( ! $args['echo'] )
				return $html;

			echo $html;
		}
	}
}
