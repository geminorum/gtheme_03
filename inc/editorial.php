<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeEditorial extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		add_filter( 'geditorial_shortcode_attachement_download', [ $this, 'attachement_download' ], 9, 2 );
	}

	public function attachement_download( $filename, $post )
	{
		return gThemeOptions::info( 'attachment_download_prefix', '' ).$filename;
	}

	public static function availableNetwork( $module )
	{
		if ( function_exists( 'gNetwork' ) )
			return (bool) gNetwork()->module( $module );

		return FALSE;
	}

	public static function availableEditorial( $module )
	{
		if ( function_exists( 'gEditorial' ) )
			return gEditorial()->enabled( $module );

		return FALSE;
	}

	public static function series( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'series' ) )
			return NULL;

		$html = gEditorial()->series->series_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function attachments( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'attachments' ) )
			return NULL;

		$html = gEditorial()->attachments->attachments_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	// FIXME: DEPRECATED
	public static function publications( $atts = [], $echo = TRUE )
	{
		self::_dep( 'gThemeEditorial::publication()' );
		return self::publication( $atts, $echo );
	}

	public static function publication( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'book' ) )
			return NULL;

		$html = gEditorial()->book->publication_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function courseLessonRowCallback( $post, $args, $term )
	{
		// @REF: https://developer.wordpress.org/?p=2837#comment-874
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		ob_start();

		echo '<li>';
			get_template_part( 'row', 'lesson' );
			echo '<span class="-dummy"></span>';
		echo '</li>';

		return ob_get_clean();
	}

	public static function courseLessons( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'course' ) )
			return NULL;

		if ( ! array_key_exists( 'item_cb', $atts ) )
			$atts['item_cb'] = [ __CLASS__, 'courseLessonRowCallback' ];

		$html = gEditorial()->course->course_shortcode( $atts );

		wp_reset_postdata(); // since callback used setup post data

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function course( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'course' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Course', 'theCourse' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Course::theCourse( $atts );
	}

	public static function postLikeButton( $atts = [], $check_systemtags = 'disable-like-button' )
	{
		$args = self::atts( [
			'post'    => NULL,
			'before'  => '',
			'after'   => '',
			'echo'    => TRUE,
			'default' => FALSE,
		], $atts );

		if ( ! self::availableEditorial( 'like' ) )
			return $args['default'];

		if ( ! $post = get_post( $args['post'] ) )
			return $args['default'];

		if ( $check_systemtags && gThemeTerms::has( $check_systemtags, $post ) )
			return $args['default'];

		if ( ! $html = gEditorial()->like->get_button( $post->ID ) )
			return $args['default'];

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function siteModified( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'modified' ) )
			return NULL;

		$html = gEditorial()->modified->site_modified_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function postModified( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'modified' ) )
			return NULL;

		$html = gEditorial()->modified->post_modified_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function listAttachments( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'attachments' ) )
			return NULL;

		$html = gEditorial()->attachments->attachments_shortcode( $atts );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function label( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaLabel' ] ) )
			return $atts['default'];

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = array_key_exists( 'post_id', $atts ) ? $atts['post_id'] : NULL;

		return \geminorum\gEditorial\Templates\Meta::metaLabel( $atts );
	}

	// FIXME: DEPRECATED
	public static function source( $atts = [] )
	{
		self::_dep( 'gThemeEditorial::theSource()' );
		return self::theSource( $atts );
	}

	public static function theSource( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaSource' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Meta::metaSource( $atts );
	}

	public static function estimated( $atts = [] )
	{
		$args = self::atts( [
			'post'    => NULL,
			'before'  => '',
			'after'   => '',
			'echo'    => TRUE,
			'prefix'  => NULL,
			'default' => FALSE,
		], $atts );

		if ( ! self::availableEditorial( 'estimated' ) )
			return $args['default'];

		if ( ! $post = get_post( $args['post'] ) )
			return $args['default'];

		// check if it's is_callable
		if ( ! $html = gEditorial()->estimated->get_estimated( $post->ID, $args['prefix'] ) )
			return $args['default'];

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function author( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaAuthor' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Meta::metaAuthor( $atts );
	}

	// FIXME: DEPRECATED
	public static function lead( $atts = [] )
	{
		self::_dev_dep( 'gThemeEditorial::metaHTML( \'lead\' )' );

		return self::metaHTML( 'lead', $atts );
	}

	// FIXME: DEPRECATED
	public static function highlight( $atts = [] )
	{
		self::_dev_dep( 'gThemeEditorial::metaHTML( \'highlight\' )' );

		return self::metaHTML( 'highlight', $atts );
	}

	// for all html fields, like: `lead`, `highlight`, `dashboard`
	public static function metaHTML( $field, $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'metaFieldHTML' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Meta::metaFieldHTML( $field, $atts );
	}

	public static function getMeta( $field, $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = '';

		if ( ! array_key_exists( 'echo', $atts ) )
			$atts['echo'] = FALSE;

		if ( ! $meta = self::meta( $field, $atts ) )
			return $atts['default'];

		return $meta;
	}

	public static function meta( $field, $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Meta', 'getMetaField' ] ) )
			return $atts['default'];

		$args = self::atts( [
			'id'      => isset( $atts['post_id'] ) ? $atts['post_id'] : NULL,
			'filter'  => FALSE,
			'trim'    => FALSE,
			'default' => FALSE,
		], $atts );

		if ( ! $html = \geminorum\gEditorial\Templates\Meta::getMetaField( $field, $args ) )
			return FALSE;

		$args = self::atts( [
			'before'   => '',
			'after'    => '',
			'echo'     => TRUE,
			'wordwrap' => FALSE,
		], $atts );

		if ( $args['wordwrap'] )
			$html = gThemeText::wordWrap( $html, 2 );

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function metaOverTitle( $post = NULL, $atts = [], $fallback = 'over_title' )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! $post = gThemeContent::getPost( $post ) )
			return $atts['default'];

		$defaults = [
			'post'        => 'over_title',
			'page'        => 'over_title',
			'issue'       => 'over_title',
			'entry'       => 'over_title',
			'course'      => FALSE,
			'lesson'      => 'over_title',
			'place'       => 'parent_complex',
			'video'       => 'over_title',
			'channel'     => FALSE,
			'collection'  => 'over_title',
			'publication' => 'collection',
		];

		$map   = array_merge( $defaults, (array) gThemeOptions::info( 'editorial_overtitle_map', [] ) );
		$field = array_key_exists( $post->post_type, $map ) ? $map[$post->post_type] : $fallback;

		if ( ! $field )
			return $atts['default'];

		if ( ! array_key_exists( 'wordwrap', $atts ) )
			$atts['wordwrap'] = TRUE;

		return self::meta( $field, $atts );
	}

	public static function metaSubTitle( $post = NULL, $atts = [], $fallback = 'sub_title' )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! $post = gThemeContent::getPost( $post ) )
			return $atts['default'];

		$defaults = [
			'post'        => 'sub_title',
			'page'        => 'sub_title',
			'issue'       => 'sub_title',
			'entry'       => 'sub_title',
			'course'      => 'sub_title',
			'lesson'      => 'sub_title',
			'place'       => 'full_title',
			'video'       => 'sub_title',
			'channel'     => FALSE,
			'collection'  => 'sub_title',
			'publication' => 'sub_title',
		];

		$map   = array_merge( $defaults, (array) gThemeOptions::info( 'editorial_subtitle_map', [] ) );
		$field = array_key_exists( $post->post_type, $map ) ? $map[$post->post_type] : $fallback;

		if ( ! $field )
			return $atts['default'];

		if ( ! array_key_exists( 'wordwrap', $atts ) )
			$atts['wordwrap'] = TRUE;

		return self::meta( $field, $atts );
	}

	public static function issueRowCallback( $post, $args, $term )
	{
		// @REF: https://developer.wordpress.org/?p=2837#comment-874
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		ob_start();

		echo '<li>';
			get_template_part( 'row', 'issue' );
			echo '<span class="-dummy"></span>';
		echo '</li>';

		return ob_get_clean();
	}

	public static function issuePosts( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableEditorial( 'magazine' ) )
			return NULL;

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
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'magazine' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Magazine', 'theIssue' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Magazine::theIssue( $atts );
	}

	// FIXME: DEPRECATED
	public static function issueMeta( $field, $atts = [] )
	{
		self::_dep( 'gThemeEditorial::getMeta()' );

		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'magazine' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Magazine', 'theIssueMeta' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Magazine::theIssueMeta( $field, $atts );
	}

	public static function issueCover( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'magazine' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Magazine', 'cover' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Magazine::cover( $atts );
	}

	public static function bookCover( $atts = [], $check = FALSE )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( $check && ( 'publication' != get_post_type( $atts['id'] ) ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'book' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Book', 'cover' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Book::cover( $atts );
	}

	public static function bookBarcodeISBN( $atts = [], $check = FALSE )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( $check && ( 'publication' != get_post_type( $atts['id'] ) ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'book' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Book', 'barcodeISBN' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Book::barcodeISBN( $atts );
	}

	public static function bookMetaSummary( $atts = [], $check = FALSE )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( $check && ( 'publication' != get_post_type( $atts['id'] ) ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'book' ) )
			return $atts['default'];

		if ( ! array_key_exists( 'fields', $atts ) )
			$atts['fields'] = apply_filters( 'gtheme_editorial_book_summary_fields', NULL, $atts );

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Book', 'summary' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Templates\Book::summary( $atts );
	}

	public static function refList( $atts = [], $echo = TRUE )
	{
		if ( ! self::availableNetwork( 'shortcodes' ) )
			return NULL;

		$html = gNetwork()->shortcodes->shortcode_reflist( array_merge( $atts, [ 'context' => 'single' ] ), NULL, 'reflist' );

		if ( ! $echo )
			return $html;

		echo $html;

		return TRUE;
	}

	// FIXME: DEPRECATED
	public static function reshareSource( $atts = [] )
	{
		self::_dep( 'gThemeEditorial::source()' );
		return self::source( $atts );
	}

	public static function personPicture( $atts = [], $post = NULL )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'terms' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Templates\\Terms', 'termImage' ] ) )
			return $atts['default'];

		if ( ! array_key_exists( 'taxonomy', $atts ) )
			$atts['taxonomy'] = GTHEME_PEOPLE_TAXONOMY;

		// if ( ! array_key_exists( 'wrap', $atts ) )
		// 	$atts['wrap'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) && ( is_singular() || is_single() ) ) {

			// the order applied via filter
			$people = get_the_terms( $post, $atts['taxonomy'] );

			if ( ! $people || is_wp_error( $people ) )
				return $atts['default'];

			$person = array_shift( $people );

			$atts['id'] = $person->term_id;

			if ( ! array_key_exists( 'figure', $atts ) )
				$atts['figure'] = TRUE; // only on singular
		}

		return \geminorum\gEditorial\Templates\Terms::termImage( $atts );
	}

	// FIXME: must move to the actual module templates
	public static function inquireQuestion( $post = NULL, $check = FALSE )
	{
		if ( $check && ( 'inquiry' != get_post_type( $post ) ) )
			return;

		$question = trim( get_the_excerpt( $post ) );

		if ( ! $question )
			return;

		echo '<div class="entry-summary inquiry-question">';
			echo wpautop( $question );
		echo '</div>';
	}
}
