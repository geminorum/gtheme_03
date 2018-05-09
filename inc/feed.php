<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeFeed extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'prepare'    => TRUE,
			'exclude'    => TRUE,
			'enclosures' => TRUE, // adding post image as rss enclosure
			'paged'      => FALSE,
		], $args ) );

		if ( $prepare )
			add_filter( 'the_content_feed', [ $this, 'the_content_feed' ], 12, 2 );

		if ( $exclude && ! is_admin() )
			add_filter( 'pre_get_posts', [ $this, 'pre_get_posts' ], 12 );

		if ( $enclosures )
			add_action( 'rss2_item', [ $this, 'rss2_item' ] );

		if ( $paged )
			add_filter( 'posts_where', [ $this, 'posts_where' ], 10, 2 );
	}

	public function the_content_feed( $content, $feed_type )
	{
		$for = self::req( 'for' );

		if ( 'twitter' == $for ) {

			// preparing feed for twitter
			// /feed?for=twitter

			return gThemeContent::getHeader(
				get_the_title_rss(),
				gThemeUtilities::sanitize_sep( 'def', 'feed_sep', '; ' )
			);

		} else if ( 'list' == $for ) {

			// preparing feed for list
			// /feed?for=list

			return '';
		}

		if ( gThemeOptions::info( 'restricted_content', FALSE ) ) {

			$GLOBALS['more'] = 0;
			$content = get_the_content( FALSE );

			// // manually apply `the_content` default filters to avoid infinity!
			// $content = wptexturize( $content );
			// $content = wpautop( $content );
			// $content = shortcode_unautop( $content );
			// $content = do_shortcode( $content );

			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
		}

		$header = gThemeOptions::info( 'feed_content_header_before', '' );
		$footer = gThemeOptions::info( 'feed_content_footer_before', '' );

		ob_start();
			gThemeEditorial::label( [ 'before' => '<div class="label">', 'after' => '</div>' ] );
			gThemeEditorial::meta( 'over-title', [ 'before' => '<h4>', 'after' => '</h4>' ] );
			if ( $title = get_the_title_rss() ) echo '<h2>'.$title.'</h2>';
			gThemeEditorial::meta( 'sub-title', [ 'before' => '<h4>', 'after' => '</h4>' ] );
			gThemeContent::byline( NULL, '<h4>', '</h4>' );
		$header.= ob_get_clean();

		$header.= gThemeOptions::info( 'feed_content_header_after', '' );
		$footer.= gThemeOptions::info( 'feed_content_footer_after', '' );

		$lead = gThemeEditorial::lead( [
			'before' => '<div class="lead">',
			'after'  => '</div>',
			'echo'   => FALSE,
		] );

		if ( $lead )
			$header.= $lead;

		$replaces = gThemeOptions::info( 'feed_str_replace', self::defaultReplace() );

		if ( count( $replaces ) ) {
			if ( ! empty( $header ) )
				$header = str_replace( array_keys( $replaces ), $replaces, $header );

			if ( ! empty( $content ) )
				$content = str_replace( array_keys( $replaces ), $replaces, $content );

			if ( ! empty( $footer ) )
				$footer = str_replace( array_keys( $replaces ), $replaces, $footer );
		}

		// TODO: add shortlink
		// TODO: add comment link

		if ( gThemeUtilities::isRTL() )
			return '<div style="direction:rtl !important;text-align:right !important;">'.$header.$content.$footer.'</div>';

		return $header.$content.$footer;
	}

	// FIXME: make ltr compatible
	public static function defaultReplace( $extra = [] )
	{
		return array_merge( [
			'<p>'                            => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
			'<p style="text-align: right;">' => '<p style="direction:rtl;font-family:tahoma;line-height:22px;font-size:14px !important;">',
			'<blockquote>'                   => '<blockquote style="direction:rtl;float:left;width:45%;maegin:20px 20px 20px 0;font-family:tahoma;line-height:22px;font-weight:bold;font-size:14px !important;">',
			'class="alignleft"'              => 'style="float:left;margin-right:15px;"',
			'class="alignright"'             => 'style="float:right;margin-left:15px;"',
			'class="aligncenter"'            => 'style="margin-left:auto;margin-right:auto;text-align:center;"',
			'<h3>'                           => '<h3 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
			'<h4>'                           => '<h4 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
			'<h5>'                           => '<h5 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
			'<h6>'                           => '<h6 style="font-family:arial,verdana,sans-serif !important;font-weight:bold;">',
			'<div class="lead">'             => '<div style="color:#ccc;">',
			'<div class="label">'            => '<div style="float:left;color:#333;">',
		], $extra );
	}

	public function pre_get_posts( &$query )
	{
		if ( $query->is_feed() ) {

			if ( $excludes = gThemeOptions::info( 'system_tags_excludes', FALSE ) )
				$query->set( 'tax_query', [ [
					'taxonomy' => GTHEME_SYSTEMTAGS,
					'field'    => 'slug',
					'terms'    => $excludes,
					'operator' => 'NOT IN',
				] ] );
		}

		return $query;
	}

	public function rss2_item()
	{
		if ( $size = gThemeOptions::info( 'enclosure_image_size', 'single' ) ) {

			if ( $id = gThemeImage::getThumbID( $size ) ) {

				if ( $image = wp_get_attachment_image_src( $id, $size ) ) {

					$post = get_post( $id );

					echo "\t".'<enclosure url="'.trim( htmlspecialchars( $image[0] ) )
						.'" length="'.trim( gThemeUtilities::getURILength( $image[0] ) )
						.'" type="'.$post->post_mime_type.'" />'."\n";
				}
			}
		}
	}

	// paginated rss feeds / 'page' sets the page number
	// @SOURCE: https://gist.github.com/danielbachhuber/6557916
	public function posts_where( $where, $query )
	{
		if ( ! is_feed() )
			return;

		$page = ( empty( $_GET['page'] ) ? 1 : (int) $_GET['page'] );

		$query->set( 'nopaging', FALSE );
		$query->set( 'paged', $page );

		return $where;
	}
}
