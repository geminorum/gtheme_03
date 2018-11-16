<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeEditorial extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'word_wrap' => FALSE,
		], $args ) );

		if ( $word_wrap )
			add_filter( 'gmeta_meta', [ $this, 'gmeta_meta' ], 12, 2 ); // FIXME: DEPRECATED on editorial meta

		add_filter( 'geditorial_shortcode_attachement_download', [ $this, 'attachement_download' ], 9, 2 );
	}

	public function gmeta_meta( $meta, $field )
	{
		if ( $meta && in_array( $field, [ 'ot', 'st', 'over-title', 'sub-title' ] ) )
			return gThemeUtilities::wordWrap( $meta, 2 );

		return $meta;
	}

	public function attachement_download( $filename, $post )
	{
		return gThemeOptions::info( 'attachment_download_prefix', '' ).$filename;
	}

	public static function series( $atts = [], $echo = TRUE )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'series' ) )
			return FALSE;

		$html = gEditorial()->series->series_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function attachments( $atts = [], $echo = TRUE )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'attachments' ) )
			return FALSE;

		$html = gEditorial()->attachments->attachments_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function publications( $atts = [], $echo = TRUE )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'book' ) )
			return FALSE;

		$html = gEditorial()->book->publications_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function postLikeButton( $atts = [] )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'like' ) )
			return FALSE;

		$args = self::atts( [
			'post'   => NULL,
			'before' => '',
			'after'  => '',
			'echo'   => TRUE,
		], $atts );

		if ( ! $post = get_post( $args['post'] ) )
			return FALSE;

		if ( ! $html = gEditorial()->like->get_button( $post->ID ) )
			return FALSE;

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function siteModified( $atts = [], $echo = TRUE )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'modified' ) )
			return FALSE;

		$html = gEditorial()->modified->site_modified_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function postModified( $atts = [], $echo = TRUE )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'modified' ) )
			return FALSE;

		$html = gEditorial()->modified->post_modified_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function label( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaLabel' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaLabel( $atts );
	}

	public static function source( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaSource' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaSource( $atts );
	}

	public static function estimated( $atts = [] )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'estimated' ) )
			return FALSE;

		$args = self::atts( [
			'post'   => NULL,
			'before' => '',
			'after'  => '',
			'echo'   => TRUE,
		], $atts );

		if ( ! $post = get_post( $args['post'] ) )
			return FALSE;

		if ( ! $html = gEditorial()->estimated->get_estimated( $post->ID ) )
			return FALSE;

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function author( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaAuthor' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaAuthor( $atts );
	}

	public static function lead( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaLead' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Meta::metaLead( $atts );
	}

	public static function meta( $field, $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'getMetaField' ] ) )
			return FALSE;

		$args = self::atts( [
			'id'     => isset( $atts['post_id'] ) ? $atts['post_id'] : NULL,
			'filter' => FALSE,
		], $atts );

		if ( ! $html = \geminorum\gEditorial\Templates\Meta::getMetaField( $field, $args ) )
			return FALSE;

		$args = self::atts( [
			'before' => '',
			'after'  => '',
			'echo'   => TRUE,
		], $atts );

		if ( in_array( $field, [ 'ot', 'st', 'over-title', 'sub-title' ] ) )
			$html = gThemeUtilities::wordWrap( $html, 2 );

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function issueRowCallback( $post, $args, $term )
	{
		// @REF: https://developer.wordpress.org/?p=2837#comment-874
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		ob_start();

		echo '<li>';
			get_template_part( 'row', 'issue' );
		echo '</li>';

		return ob_get_clean();
	}

	public static function issuePosts( $atts = [], $echo = TRUE )
	{
		if ( ! function_exists( 'gEditorial' ) )
			return FALSE;

		if ( ! gEditorial()->enabled( 'magazine' ) )
			return FALSE;

		if ( ! array_key_exists( 'item_cb', $atts ) )
			$atts['item_cb'] = [ __CLASS__, 'issueRowCallback' ];

		$html = gEditorial()->magazine->issue_shortcode( $atts );

		wp_reset_postdata(); // since callback used setup post data

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function issue( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Magazine', 'theIssue' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Magazine::theIssue( $atts );
	}

	public static function issueMeta( $field, $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Magazine', 'theIssueMeta' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Magazine::theIssueMeta( $field, $atts );
	}

	public static function issueCover( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Magazine', 'cover' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Magazine::cover( $atts );
	}

	public static function bookCover( $atts = [] )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Book', 'cover' ] ) )
			return FALSE;

		return \geminorum\gEditorial\Templates\Book::cover( $atts );
	}

	public static function refList( $atts = [] )
	{
		if ( ! function_exists( 'gNetwork' ) )
			return FALSE;

		if ( ! gNetwork()->module( 'shortcodes' ) )
			return FALSE;

		echo gNetwork()->shortcodes->shortcode_reflist( array_merge( $atts, [ 'context' => 'single' ] ), NULL, 'reflist' );

		return TRUE;
	}

	public static function reshareSource( $atts = [] )
	{
		self::_dep( 'gThemeEditorial::source()' );
		return self::source( $atts );
	}

	public static function personPicture( $atts = [], $post = NULL )
	{
		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Terms', 'termImage' ] ) )
			return FALSE;

		if ( ! array_key_exists( 'taxonomy', $atts ) )
			$atts['taxonomy'] = 'people';

		if ( ! array_key_exists( 'wrap', $atts ) )
			$atts['wrap'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) && ( is_singular() || is_single() ) ) {

			// the order applied via filter
			$people = get_the_terms( $post, 'people' );

			if ( ! $people || is_wp_error( $people ) )
				return FALSE;

			$person = array_shift( $people );

			$atts['id'] = $person->term_id;

			if ( ! array_key_exists( 'figure', $atts ) )
				$atts['figure'] = TRUE; // only on singular
		}

		return \geminorum\gEditorial\Templates\Terms::termImage( $atts );
	}
}
