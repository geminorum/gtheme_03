<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEditorial extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( shortcode_atts( array(
			'word_wrap' => TRUE,
		), $args ) );

		if ( $word_wrap )
			add_filter( 'gmeta_meta', array( &$this, 'gmeta_meta' ), 12, 2 );
	}

	public function gmeta_meta( $meta, $field )
	{
		if ( $meta && in_array( $field, array( 'ot', 'st', 'over-title', 'sub-title' ) ) )
			return gThemeUtilities::wordWrap( $meta, 2 );

		return $meta;
	}

	// FIXME: add theme classes / before / after : is's a shortcode!!
	public static function series( $atts = array() )
	{
		if ( class_exists( 'gEditorialSeriesTemplates' ) ) {
			echo gEditorialSeriesTemplates::shortcode_series( $atts );
		} else {
			return FALSE;
		}
	}

	public static function label( $atts = array() )
	{
		if ( class_exists( 'gEditorialMetaTemplates' ) )
			return gEditorialMetaTemplates::metaLabel( $atts );

		return FALSE;
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
				return TRUE;
			}
		}

		return FALSE;
	}

	public static function issueRowCallback( $post, $args )
	{
		ob_start();
			echo '<li>';

				// NOTE: the shortcode will setup postdata already
				get_template_part( 'row', 'issue' );

			echo '</li>';
		return ob_get_clean();
	}

	public static function issuePosts( $atts = array() )
	{
		if ( class_exists( 'gEditorialMagazineTemplates' ) ) {

			$args = self::atts( array(
				'before' => '',
				'after'  => '',
				'cb'     => array( __CLASS__, 'issueRowCallback' ),
				'echo'   => TRUE,
			), $atts );

			$html = gEditorialMagazineTemplates::issue_shortcode( $args );

			if ( $html ) {
				if ( ! $args['echo'] )
					return $args['before'].$html.$args['after'];

				echo $args['before'].$html.$args['after'];
				return TRUE;
			}
		}

		return FALSE;
	}

	public static function issueCover( $atts = array() )
	{
		if ( class_exists( 'gEditorialMagazineTemplates' ) ) {

			$args = self::atts( array(
				'before' => '',
				'after'  => '',
				'id'     => 'issue',
				'size'   => 'raw',
				'link'   => 'parent',
				'echo'   => TRUE,
			), $atts );

			$atts['echo'] = FALSE;
			$atts['id'] = $args['id'];
			$html = gEditorialMagazineTemplates::issue_cover( $args['before'], $args['after'], $args['size'], $args['link'], $atts );

			if ( $html ) {
				if ( ! $args['echo'] )
					return $html;

				echo $html;
				return TRUE;
			}
		}

		return FALSE;
	}

	// ANCESTOR: gmeta_lead()
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
				return TRUE;
			}
		}

		return FALSE;
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
			// FIXME: messing up html!
			// $html = $args['before']$args['title'].apply_filters( 'the_content', $html ).$args['after'];
			$html = $args['before'].$args['title'].$html.$args['after'];

			if ( ! $args['echo'] )
				return $html;

			echo $html;
			return TRUE;
		}

		return FALSE;
	}

	public static function reshareSource( $atts = array() )
	{
		if ( class_exists( 'gEditorialReshareTemplates' ) )
			return gEditorialReshareTemplates::source( $atts );

		return FALSE;
	}
}
