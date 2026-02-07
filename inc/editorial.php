<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeEditorial extends gThemeModuleCore
{

	public function setup_actions( $args = [], $childless = NULL )
	{
		extract( self::atts( [
			'insert_toc'       => FALSE,
			'insert_embed'     => FALSE,
			'insert_media'     => FALSE,
			'insert_action'    => TRUE,
			'insert_source'    => TRUE,
			'insert_likes'     => TRUE,
			'insert_supported' => TRUE,
			'date_override'    => TRUE,
			'reflist_toc'      => TRUE,
		], $args ) );

		if ( $insert_toc )
			add_action( 'gtheme_content_before', [ $this, 'content_before_toc' ], 20 );

		if ( $insert_embed )
			add_action( 'gtheme_content_before', [ $this, 'content_before_embed' ], 50 );

		if ( $insert_media ) {
			add_action( 'gtheme_content_before', [ $this, 'content_before_media' ], 80 );
			add_action( 'gtheme_content_after', [ $this, 'content_after_media' ], 8 );
		}

		if ( $insert_action )
			add_action( 'gtheme_content_after', [ $this, 'content_after_action' ], 14 );

		if ( $insert_source )
			add_action( 'gtheme_content_after', [ $this, 'content_after_source' ], 18 );

		if ( $insert_likes )
			add_action( 'gtheme_content_after', [ $this, 'content_after_likes' ], 22 );

		if ( $insert_supported )
			add_action( 'gtheme_content_wrap_after', [ $this, 'content_wrap_after_supported' ], 8 );

		if ( $date_override )
			add_filter( 'gtheme_date_override_the_date', [ $this, 'date_override_the_date' ], 20, 4 );

		if ( $reflist_toc )
			add_filter( 'gnetwork_shortcodes_reflist_toc', [ $this, 'shortcodes_reflist_toc' ], 10, 2 );

		add_filter( 'geditorial_shortcode_attachement_download', [ $this, 'attachement_download' ], 9, 2 );
		add_filter( 'geditorial_wc_terms_term_listassigned_args', [ $this, 'wc_terms_term_listassigned_args' ], 9, 2 );
		add_filter( 'geditorial_wc_connected_product_listconnected_args', [ $this, 'wc_connected_product_listconnected_args' ], 9, 2 );
	}

	public function content_before_toc( $content )
	{
		if ( ! gThemeUtilities::isPrint()
			&& is_singular( gThemeOptions::info( 'headings_posttypes', [ 'entry', 'lesson' ] ) ) )
				self::headingsTOC();
	}

	public function content_before_embed( $content )
	{
		if ( ! is_singular() )
			return;

		if ( $embed = self::getMeta( 'content_embed_url', [ 'fallback' => 'video_embed_url' ] ) )
			echo gThemeHTML::wrap( $embed, '-embed' );
	}

	public function content_before_media( $content )
	{
		if ( ! is_singular() )
			return;

		if ( $video = self::getMeta( 'video_source_url' ) )
			echo gThemeHTML::wrap( $video, '-video -video-source' );

		if ( $audio = self::getMeta( 'audio_source_url' ) )
			echo gThemeHTML::wrap( $audio, '-audio -audio-source' );
	}

	public function content_after_media( $content )
	{
		if ( ! is_singular() )
			return;

		if ( $text = self::getMeta( 'text_source_url' ) )
			echo gThemeHTML::wrap( $text, '-text -text-source' );
	}

	public function content_after_action()
	{
		if ( ! is_singular() )
			return;

		self::theAction( [
			'before'     => '<div class="entry-after after-single after-action d-grid gap-2">',
			'after'      => '</div>',
			'link_class' => 'btn btn-lg btn-outline-primary',
		] );
	}

	public function content_after_source()
	{
		if ( ! is_singular() )
			return;

		self::theSource( [
			'before' => '<div class="entry-after after-single after-source text-end">'.
				gThemeOptions::info( 'source_before', '' ),
			'after'  => '</div>',
		] );
	}

	public function content_after_likes()
	{
		if ( ! is_singular() )
			return;

		self::postLikeButton( [
			'before' => '<div class="entry-after after-single after-like my-2">',
			'after'  => '</div>',
		] );
	}

	public function content_wrap_after_supported()
	{
		if ( ! is_singular() )
			return;

		switch ( get_post_type() ) {

			case 'issue':

				self::magazineSupported( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-issue after-rows">',
					'after'  => '</div>',
					'wrap'   => FALSE,
					'title'  => FALSE,
					'future' => FALSE,
				] );

				break;

			case 'dossier':

				self::dossierSupported( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-dossier after-rows">',
					'after'  => '</div>',
					'wrap'   => FALSE,
					'title'  => FALSE,
					'future' => FALSE,
				] );

				break;

			case 'course':

				self::courseLessons( [
					'before' => '<div class="clearfix"></div><div class="entry-after after-course-lessons after-rows">',
					'after'  => '</div>',
					'order'  => 'DESC',
					'wrap'   => FALSE,
					'title'  => FALSE,
					'future' => FALSE,
				] );

				break;
		}
	}

	public function date_override_the_date( $override, $post, $link, $args )
	{
		// already filtered!
		if ( ! is_null( $override ) )
			return $override;

		// avoid overriding with custom dates
		if ( in_array( $args['context'], [ 'once' ], TRUE ) )
			return $override;

		if ( ! $datestring = self::metaPublished( $post, [ 'echo' => FALSE ] ) )
			return $override;

		return vsprintf( $args['template'], [
			$link ? sprintf( '<a href="%s">', esc_url( $link ) ) : '',
			$link ? '</a>' : '',
			'',
			esc_html( $datestring ),
			gThemeHTML::prepClass( sprintf( '%s-time time-%s', $args['prefix'], $args['context'] ) ),
		] );
	}

	public function shortcodes_reflist_toc( $item, $toc )
	{
		if ( ! $title = gThemeOptions::info( 'reflist_title', FALSE ) )
			return $item;

		$item['slug']  = 'footnotes';
		$item['niche'] = '4';
		$item['title'] = strip_tags( $title );

		return $item;
	}

	public function attachement_download( $filename, $post )
	{
		return gThemeOptions::info( 'attachment_download_prefix', '' ).$filename;
	}

	// MAYBE: context must be: `woocommerce` instead of `listassigned`
	public function wc_terms_term_listassigned_args( $atts, $term )
	{
		return array_merge( $atts, [
			'item_cb'    => [ __CLASS__, 'wcTermsListAssignedRowCallback' ],
			'list_tag'   => 'div',
			'list_class' => gThemeOptions::info( 'listassigned_wrap_class', gThemeTemplate::defaultWrapClass( 'listassigned' ) ),
			'item_class' => gThemeOptions::info( 'listassigned_item_class', gThemeTemplate::defaultItemClass( 'listassigned' ) ),
		] );
	}

	// MAYBE: context must be: `woocommerce` instead of `listassigned`
	public static function wcTermsListAssignedRowCallback( $post, $args, $ref )
	{
		ob_start();

		printf( '<div class="%s -listassigned-item">', $args['item_class'] );

		gThemeContent::partial( 'listassigned' );

		echo '</div>';

		return ob_get_clean();
	}

	// MAYBE: context must be: `woocommerce` instead of `listconnected`
	public function wc_connected_product_listconnected_args( $atts, $product )
	{
		return array_merge( $atts, [
			'item_cb'    => [ __CLASS__, 'wcConnectedListConnectedRowCallback' ],
			'list_tag'   => 'div',
			'list_class' => gThemeOptions::info( 'listconnected_wrap_class', gThemeTemplate::defaultWrapClass( 'listconnected' ) ),
			'item_class' => gThemeOptions::info( 'listconnected_item_class', gThemeTemplate::defaultItemClass( 'listconnected' ) ),
		] );
	}

	// MAYBE: context must be: `woocommerce` instead of `listconnected`
	public static function wcConnectedListConnectedRowCallback( $post, $args, $ref )
	{
		ob_start();

		printf( '<div class="%s -listconnected-item">', $args['item_class'] );
			gThemeContent::partial( 'listconnected' );
		echo '</div>';

		return ob_get_clean();
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

	public static function socialite( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'socialite' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->socialite, 'main_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->socialite->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function series( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'series' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->series, 'main_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->series->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	// FIXME: DEPRECATED
	public static function attachments( $atts = [], $verbose = TRUE )
	{
		self::_dep( 'gThemeEditorial::listAttachments()' );
		return self::listAttachments( $atts, $verbose );
	}

	// FIXME: DEPRECATED
	public static function publications( $atts = [], $verbose = TRUE )
	{
		self::_dep( 'gThemeEditorial::publication()' );
		return self::publication( $atts, $verbose );
	}

	public static function publication( $atts = [], $verbose = TRUE, $tabs_check = TRUE )
	{
		if ( $tabs_check && self::availableEditorial( 'tabs' ) )
			return NULL;

		if ( ! self::availableEditorial( 'book' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->book, 'main_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->book->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function courseLessonRowCallback( $post, $args, $term )
	{
		ob_start();

		echo '<li>';
			get_template_part( 'row', 'lesson' );
			echo '<span class="-dummy"></span>';
		echo '</li>';

		return ob_get_clean();
	}

	public static function courseLessons( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'course' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->course, 'main_shortcode' ] ) )
			return NULL;

		if ( ! array_key_exists( 'item_cb', $atts ) )
			$atts['item_cb'] = [ __CLASS__, 'courseLessonRowCallback' ];

		$html = gEditorial()->course->main_shortcode( $atts );

		if ( ! $verbose )
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

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Course\\ModuleTemplate', 'theCourse' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Course\ModuleTemplate::theCourse( $atts );
	}

	public static function byline( $atts = [], $post = NULL )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'byline' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Byline\\ModuleTemplate', 'renderDefault' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Byline\ModuleTemplate::renderDefault( $atts, $post );
	}

	public static function bylineFeatured( $atts = [], $post = NULL )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'byline' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Byline\\ModuleTemplate', 'renderFeatured' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Byline\ModuleTemplate::renderFeatured( $atts, $post );
	}

	public static function postLikeButton( $atts = [], $check_systemtags = 'disable-like-button' )
	{
		$args = self::atts( [
			'post'     => NULL,
			'before'   => '',
			'after'    => '',
			'echo'     => TRUE,
			'default'  => FALSE,
			'lastpage' => TRUE,
		], $atts );

		// maybe check for `is_singular()`
		if ( self::const( 'GTHEME_EDITORIAL_LIKES_DISPLAYED' ) )
			return $args['default'];

		if ( $args['lastpage'] && ! gThemeUtilities::contentLastPage() )
			return $args['default'];

		if ( ! self::availableEditorial( 'like' ) )
			return $args['default'];

		if ( ! $post = get_post( $args['post'] ) )
			return $args['default'];

		if ( $check_systemtags && gThemeTerms::has( $check_systemtags, $post ) )
			return $args['default'];

		if ( ! $html = gEditorial()->like->get_button( $post->ID ) )
			return $args['default'];

		self::define( 'GTHEME_EDITORIAL_LIKES_DISPLAYED', get_the_ID() );

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function siteModified( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'modified' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->modified, 'site_modified_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->modified->site_modified_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function postModified( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'modified' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->modified, 'post_modified_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->modified->post_modified_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function listAttachments( $atts = [], $verbose = TRUE, $tabs_check = TRUE )
	{
		if ( $tabs_check && self::availableEditorial( 'tabs' ) )
			return NULL;

		if ( ! self::availableEditorial( 'attachments' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->attachments, 'main_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->attachments->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function addendumAppendages( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'addendum' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->addendum, 'main_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->module( 'addendum' )->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function venuePlace( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'venue' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->venue, 'main_shortcode' ] ) )
			return NULL;

		$html = gEditorial()->venue->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	public static function venueMap( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'venue' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Venue\\ModuleTemplate', 'map' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Venue\ModuleTemplate::map( $atts );
	}

	public static function label( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Meta\\ModuleTemplate', 'metaLabel' ] ) )
			return $atts['default'];

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = array_key_exists( 'post_id', $atts ) ? $atts['post_id'] : NULL;

		return \geminorum\gEditorial\Modules\Meta\ModuleTemplate::metaLabel( $atts );
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

		// maybe check for `is_singular()`
		if ( self::const( 'GTHEME_EDITORIAL_SOURCE_DISPLAYED' ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Meta\\ModuleTemplate', 'metaSource' ] ) )
			return $atts['default'];

		if ( $html = \geminorum\gEditorial\Modules\Meta\ModuleTemplate::metaSource( $atts ) )
			self::define( 'GTHEME_EDITORIAL_SOURCE_DISPLAYED', get_the_ID() );

		return $html;
	}

	public static function theAction( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		// maybe check for `is_singular()`
		if ( self::const( 'GTHEME_EDITORIAL_ACTION_DISPLAYED' ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Meta\\ModuleTemplate', 'metaAction' ] ) )
			return $atts['default'];

		if ( $html = \geminorum\gEditorial\Modules\Meta\ModuleTemplate::metaAction( $atts ) )
			self::define( 'GTHEME_EDITORIAL_ACTION_DISPLAYED', get_the_ID() );

		return $html;
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

		if ( ! $html = gEditorial()->estimated->get_estimated_for_post( $post, $args['prefix'] ) )
			return $args['default'];

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function calendarLink( $atts = [] )
	{
		$args = self::atts( [
			'post'    => NULL,
			'before'  => '',
			'after'   => '',
			'echo'    => TRUE,
			'title'   => FALSE,
			'text'    => NULL,
			'context' => NULL,
			'default' => FALSE,
		], $atts );

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Services\\Calendars', 'linkPostCalendar' ] ) )
			return $args['default'];

		if ( ! $post = get_post( $args['post'] ) )
			return $args['default'];

		if ( ! in_array( $post->post_type, (array) gThemeOptions::info( 'ical_posttypes', [ 'event', 'day', 'course', 'lesson' ] ), TRUE ) )
			return $args['default'];

		if ( ! $link = \geminorum\gEditorial\Services\Calendars::linkPostCalendar( $post, $args['context'] ) )
			return $args['default'];

		$html = $args['before'].gThemeHTML::tag( 'a', [
			'href'  => $link,
			'title' => $args['title'] ?: FALSE,
			'rel'   => 'calendar',
			'data'  => [
				'bs-toggle' => 'tooltip',
				'id'        => $post->ID,
			],
		], $args['text'] ?? _x( 'iCal', 'Calendar Link', 'gtheme' ) ).$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	// NOTE: DEPRECATED
	public static function author( $atts = [] )
	{
		// self::_dep( 'gThemeContent::byline()' );

		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Meta\\ModuleTemplate', 'metaAuthor' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Meta\ModuleTemplate::metaAuthor( $atts );
	}

	// NOTE: DEPRECATED
	public static function lead( $atts = [] )
	{
		self::_dev_dep( 'gThemeEditorial::metaHTML( \'lead\' )' );

		return self::metaHTML( 'lead', $atts );
	}

	// NOTE: DEPRECATED
	public static function highlight( $atts = [] )
	{
		self::_dev_dep( 'gThemeEditorial::metaHTML( \'highlight\' )' );

		return self::metaHTML( 'highlight', $atts );
	}

	// for all HTML fields, like: `lead`, `highlight`, `dashboard`
	public static function metaHTML( $field, $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'meta' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Meta\\ModuleTemplate', 'metaFieldHTML' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Meta\ModuleTemplate::metaFieldHTML( $field, $atts );
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

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Meta\\ModuleTemplate', 'getMetaField' ] ) )
			return $atts['default'];

		$args = self::atts( [
			'id'      => isset( $atts['post_id'] ) ? $atts['post_id'] : NULL,
			'filter'  => FALSE,
			'trim'    => FALSE,
			'default' => FALSE,
		], $atts );

		if ( ! $html = \geminorum\gEditorial\Modules\Meta\ModuleTemplate::getMetaField( $field, $args ) )
			return FALSE;

		$args = self::atts( [
			'before'    => '',
			'after'     => '',
			'echo'      => TRUE,
			'word_wrap' => FALSE,
		], $atts );

		if ( $args['word_wrap'] )
			$html = gThemeText::wordWrap( $html, 2 );

		$html = $args['before'].$html.$args['after'];

		if ( ! $args['echo'] )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function metaByline( $post = NULL, $atts = [], $fallback = 'byline' )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! $post = gThemeContent::getPost( $post ) )
			return $atts['default'];

		$defaults = [
			'post'        => 'byline',
			'page'        => FALSE,
			'video'       => 'featured_people',
			'publication' => 'publication_byline',
			'product'     => 'byline',
		];

		$map   = array_merge( $defaults, (array) gThemeOptions::info( 'editorial_byline_map', [] ) );
		$field = array_key_exists( $post->post_type, $map ) ? $map[$post->post_type] : $fallback;

		if ( ! $field )
			return $atts['default'];

		if ( ! array_key_exists( 'post_id', $atts ) )
			$atts['post_id'] = $post;

		// if ( ! array_key_exists( 'word_wrap', $atts ) )
		// 	$atts['word_wrap'] = TRUE;

		return self::meta( $field, $atts );
	}

	public static function metaPublished( $post = NULL, $atts = [], $fallback = 'published' )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! $post = gThemeContent::getPost( $post ) )
			return $atts['default'];

		$defaults = [
			'post'        => 'published',
			'page'        => FALSE,
			'video'       => 'creation_date',
			'publication' => 'publication_date',
			'product'     => 'production_date',
		];

		$map   = array_merge( $defaults, (array) gThemeOptions::info( 'editorial_datestring_map', [] ) );
		$field = array_key_exists( $post->post_type, $map ) ? $map[$post->post_type] : $fallback;

		if ( ! $field )
			return $atts['default'];

		if ( ! array_key_exists( 'post_id', $atts ) )
			$atts['post_id'] = $post;

		if ( ! array_key_exists( 'word_wrap', $atts ) )
			$atts['word_wrap'] = TRUE;

		return self::meta( $field, $atts );
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
			'publication' => 'publication_tagline',
			'product'     => 'tagline',  // @SEE: `gThemeWooCommerce::single_product_summary_before()`
		];

		$map   = array_merge( $defaults, (array) gThemeOptions::info( 'editorial_overtitle_map', [] ) );
		$field = array_key_exists( $post->post_type, $map ) ? $map[$post->post_type] : $fallback;

		if ( ! $field )
			return $atts['default'];

		if ( ! array_key_exists( 'post_id', $atts ) )
			$atts['post_id'] = $post;

		if ( ! array_key_exists( 'word_wrap', $atts ) )
			$atts['word_wrap'] = TRUE;

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
			'product'     => 'sub_title', // @SEE: `gThemeWooCommerce::single_product_summary_after()`
		];

		$map   = array_merge( $defaults, (array) gThemeOptions::info( 'editorial_subtitle_map', [] ) );
		$field = array_key_exists( $post->post_type, $map ) ? $map[$post->post_type] : $fallback;

		if ( ! $field )
			return $atts['default'];

		if ( ! array_key_exists( 'post_id', $atts ) )
			$atts['post_id'] = $post;

		if ( ! array_key_exists( 'word_wrap', $atts ) )
			$atts['word_wrap'] = TRUE;

		return self::meta( $field, $atts );
	}

	public static function dossierSupported( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'dossier' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->dossier, 'main_shortcode' ] ) )
			return NULL;

		if ( ! array_key_exists( 'item_cb', $atts ) )
			$atts['item_cb'] = [ __CLASS__, 'dossierRowCallback' ];

		$html = gEditorial()->module( 'dossier' )->main_shortcode( $atts );

		if ( ! $verbose )
			return $html;

		echo $html;
		return TRUE;
	}

	public static function dossierRowCallback( $post, $args, $term )
	{
		ob_start();

		echo '<li>';
			get_template_part( 'row', 'dossier' );
			echo '<span class="-dummy"></span>';
		echo '</li>';

		return ob_get_clean();
	}

	public static function issueRowCallback( $post, $args, $term )
	{
		ob_start();

		echo '<li>';
			get_template_part( 'row', 'issue' );
			echo '<span class="-dummy"></span>';
		echo '</li>';

		return ob_get_clean();
	}

	// NOTE: DEPRECATED
	public static function issuePosts( $atts = [], $verbose = TRUE )
	{
		self::_dep( 'gThemeEditorial::magazineSupported()' );
		return self::magazineSupported( $atts, $verbose );
	}

	public static function magazineSupported( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableEditorial( 'magazine' ) )
			return NULL;

		if ( ! is_callable( [ gEditorial()->magazine, 'main_shortcode' ] ) )
			return NULL;

		if ( ! array_key_exists( 'item_cb', $atts ) )
			$atts['item_cb'] = [ __CLASS__, 'issueRowCallback' ];

		$html = gEditorial()->magazine->main_shortcode( $atts );

		if ( ! $verbose )
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

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Magazine\\ModuleTemplate', 'theIssue' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Magazine\ModuleTemplate::theIssue( $atts );
	}

	// NOTE: DEPRECATED
	public static function issueMeta( $field, $atts = [] )
	{
		self::_dep( 'gThemeEditorial::getMeta()' );

		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'magazine' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Magazine\\ModuleTemplate', 'theIssueMeta' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Magazine\ModuleTemplate::theIssueMeta( $field, $atts );
	}

	public static function theCover( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! function_exists( 'gEditorial' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Template', 'postImage' ] ) )
			return $atts['default'];

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['default'] = NULL;

		return \geminorum\gEditorial\Template::postImage( $atts );
	}

	// NOTE: not equivalent to `gThemeEditorial::theCover()`
	public static function issueCover( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'magazine' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Magazine\\ModuleTemplate', 'cover' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Magazine\ModuleTemplate::cover( $atts );
	}

	public static function bookCover( $atts = [], $check = FALSE )
	{
		self::_dep( 'gThemeEditorial::theCover()' );

		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( $check && ( 'publication' != get_post_type( $atts['id'] ) ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'book' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Book\\ModuleTemplate', 'cover' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Book\ModuleTemplate::cover( $atts );
	}

	// NOTE: DEPRECATED
	public static function bookBarcodeISBN( $atts = [], $check = FALSE )
	{
		self::_dep( 'gThemeEditorial::isbnBarcode()' );
		return self::isbnBarcode( $atts );
	}

	public static function isbnBarcode( $atts = [] )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( ! self::availableEditorial( 'isbn' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Isbn\\ModuleTemplate', 'barcode' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Isbn\ModuleTemplate::barcode( $atts );
	}

	public static function bookMetaSummary( $atts = [], $check = FALSE, $tabs_check = TRUE )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( $check && ( 'publication' != get_post_type( $atts['id'] ) ) )
			return $atts['default'];

		if ( $tabs_check && self::availableEditorial( 'tabs' ) )
			return NULL;

		if ( ! self::availableEditorial( 'book' ) )
			return $atts['default'];

		if ( ! array_key_exists( 'fields', $atts ) )
			$atts['fields'] = apply_filters( 'gtheme_editorial_book_summary_fields', NULL, $atts );

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Book\\ModuleTemplate', 'summary' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Book\ModuleTemplate::summary( $atts );
	}

	public static function venueMetaSummary( $atts = [], $check = FALSE )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! array_key_exists( 'id', $atts ) )
			$atts['id'] = NULL;

		if ( $check && ( 'place' != get_post_type( $atts['id'] ) ) )
			return $atts['default'];

		if ( ! self::availableEditorial( 'venue' ) )
			return $atts['default'];

		if ( ! array_key_exists( 'fields', $atts ) )
			$atts['fields'] = apply_filters( 'gtheme_editorial_venue_summary_fields', NULL, $atts );

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Venue\\ModuleTemplate', 'summary' ] ) )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Venue\ModuleTemplate::summary( $atts );
	}

	public static function tabsPostTabs( $post = NULL )
	{
		if ( ! self::availableEditorial( 'tabs' ) )
			return FALSE;

		return gEditorial()->module( 'tabs' )->render_post_tabs( $post );
	}

	public static function refList( $atts = [], $verbose = TRUE )
	{
		if ( ! self::availableNetwork( 'shortcodes' ) )
			return NULL;

		$html = gNetwork()->shortcodes->shortcode_reflist( array_merge( $atts, [ 'context' => 'single' ] ), NULL, 'reflist' );

		if ( ! $verbose )
			return $html;

		echo $html;

		return TRUE;
	}

	// NOTE: DEPRECATED
	public static function reshareSource( $atts = [] )
	{
		self::_dep( 'gThemeEditorial::theSource()' );
		return self::theSource( $atts );
	}

	// CAUTION: not working outside of `the_content` filter
	public static function headingsTOC( $title = NULL, $class = '' )
	{
		if ( ! self::availableEditorial( 'headings' ) )
			return FALSE;

		gEditorial()->headings->render_headings( $title, $class );
	}

	public static function personPicture( $atts = [], $post = NULL )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'terms' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Terms\\ModuleTemplate', 'termImage' ] ) )
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

		return \geminorum\gEditorial\Modules\Terms\ModuleTemplate::termImage( $atts );
	}

	public static function termIntro( $atts = [], $term = NULL )
	{
		if ( ! array_key_exists( 'default', $atts ) )
			$atts['default'] = FALSE;

		if ( ! self::availableEditorial( 'terms' ) )
			return $atts['default'];

		if ( ! is_callable( [ 'geminorum\\gEditorial\\Modules\\Terms\\ModuleTemplate', 'renderTermIntro' ] ) )
			return $atts['default'];

		if ( ! $term = $term ?? get_queried_object() )
			return $atts['default'];

		return \geminorum\gEditorial\Modules\Terms\ModuleTemplate::renderTermIntro( $term, $atts );
	}

	// TODO: must move to the actual module templates
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

	// NOTE: general use
	public static function callNetwork( $module, $callback, $args = [], $fallback = '' )
	{
		if ( ! self::availableNetwork( $module ) )
			return $fallback;

		if ( ! is_callable( $callback ) )
			return $fallback;

		return call_user_func_array( $callback, $args );
	}

	// NOTE: general use
	public static function callEditorial( $module, $callback, $args = [], $fallback = '' )
	{
		if ( ! self::availableEditorial( $module ) )
			return $fallback;

		if ( ! is_callable( $callback ) )
			return $fallback;

		return call_user_func_array( $callback, $args );
	}
}
