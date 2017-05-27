<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEditorial extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'word_wrap' => FALSE,
		), $args ) );

		if ( $word_wrap )
			add_filter( 'gmeta_meta', array( $this, 'gmeta_meta' ), 12, 2 ); // FIXME: DEPRICATED on editorial meta
	}

	public function gmeta_meta( $meta, $field )
	{
		if ( $meta && in_array( $field, array( 'ot', 'st', 'over-title', 'sub-title' ) ) )
			return gThemeUtilities::wordWrap( $meta, 2 );

		return $meta;
	}

	public static function series( $atts = array() )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'series' ) )
			return FALSE;

		echo gEditorial()->series->series_shortcode( $atts );
	}

	public static function siteModified( $atts = array() )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'modified' ) )
			return FALSE;

		echo gEditorial()->modified->site_modified_shortcode( $atts );
	}

	public static function postModified( $atts = array() )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'modified' ) )
			return FALSE;

		echo gEditorial()->modified->post_modified_shortcode( $atts );
	}

	public static function label( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Meta', 'metaLabel' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaLabel( $atts );
	}

	public static function source( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Meta', 'metaLink' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaLink( $atts );
	}

	public static function author( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Meta', 'metaAuthor' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaAuthor( $atts );
	}

	public static function lead( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Meta', 'metaLead' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaLead( $atts );
	}

	public static function meta( $field, $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Meta', 'getMetaField' ) ) )
			return FALSE;

		$args = self::atts( array(
			'id'     => isset( $atts['post_id'] ) ? $atts['post_id'] : NULL,
			'filter' => FALSE,
		), $atts );

		if ( ! $html = \geminorum\gEditorial\Templates\Meta::getMetaField( $field, $args ) )
			return FALSE;

		$args = self::atts( array(
			'before' => '',
			'after'  => '',
			'echo'   => TRUE,
		), $atts );

		if ( in_array( $field, array( 'ot', 'st', 'over-title', 'sub-title' ) ) )
			$html = gThemeUtilities::wordWrap( $html, 2 );

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
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
		if ( class_exists( 'geminorum\\gEditorial\\Templates\\Magazine' ) ) {

			$args = self::atts( array(
				'before' => '',
				'after'  => '',
				'cb'     => array( __CLASS__, 'issueRowCallback' ),
				'echo'   => TRUE,
			), $atts );

			$html = \geminorum\gEditorial\Templates\Magazine::issue_shortcode( $args );

			if ( $html ) {
				if ( ! $args['echo'] )
					return $args['before'].$html.$args['after'];

				echo $args['before'].$html.$args['after'];
				return TRUE;
			}
		}

		return FALSE;
	}

	public static function issue( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Magazine', 'theIssue' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Magazine::theIssue( $atts );
	}

	public static function issueMeta( $field, $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Magazine', 'theIssueMeta' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Magazine::theIssueMeta( $field, $atts );
	}

	public static function issueCover( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Magazine', 'cover' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Magazine::cover( $atts );
	}

	public static function bookCover( $atts = array() )
	{
		if ( ! is_callable( array( 'geminorum\\gEditorial\\Templates\\Book', 'cover' ) ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Book::cover( $atts );
	}

	// FIXME: use `gNetwork()`
	public static function refList( $atts = array() )
	{
		global $gNetwork;

		if ( ! is_object( $gNetwork )
			|| ! isset( $gNetwork->shortcodes ) )
				return;

		if ( ! method_exists( $gNetwork->shortcodes, 'shortcode_reflist' ) )
			return;

		$args = self::atts( array(
			'context'      => 'single',
			'number'       => TRUE,
			'after_number' => '. ',
			'back'         => '[^]', //'[&#8617;]', // TODO: add theme option for this
		), $atts );

		$html = $gNetwork->shortcodes->shortcode_reflist( $args, NULL, 'reflist' );

		if ( $html ) {

			$args = self::atts( array(
				'before' => '',
				'after'  => '',
				'echo'   => TRUE,
				'title'  => '',
			), $atts );

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
		self::__dep( 'gThemeEditorial::source()' );
		return self::source( $atts );
	}
}
