<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeEmbed extends gThemeModuleCore
{

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'site_title'    => TRUE,
			'lead_excerpt'  => TRUE,
			'content_meta'  => FALSE,
			'response_data' => TRUE,
		], $args ) );

		if ( $site_title )
			add_filter( 'embed_site_title_html', [ $this, 'embed_site_title_html' ] );

		if ( $lead_excerpt )
			add_filter( 'the_excerpt_embed', [ $this, 'the_excerpt_embed' ], 9 );

		if ( $content_meta )
			add_action( 'embed_content_meta', [ $this, 'embed_content_meta' ] );

		if ( $content_meta )
			add_filter( 'oembed_response_data', [ $this, 'oembed_response_data' ], 11, 4 );
	}

	public function embed_site_title_html( $site_title )
	{
		$html = sprintf(
			'<a href="%s" target="_top"><img src="%s" width="32" height="32" alt="" class="wp-embed-site-icon"/><span>%s</span></a>',
			esc_url( gThemeUtilities::home() ),
			// esc_url( get_site_icon_url( 32, admin_url( 'images/w-logo-blue.png' ) ) ),
			esc_url( GTHEME_CHILD_URL.'/images/logo-embed.png' ),
			esc_html( gThemeOptions::info( 'frontpage_title', get_bloginfo( 'name' ) ) )
		);

		return '<div class="wp-embed-site-title">'.$html.'</div>';
	}

	public function the_excerpt_embed( $output )
	{
		return gThemeEditorial::lead( [ 'echo' => FALSE, 'default' => $output ] );
	}

	public function embed_content_meta()
	{
		// FIXME
	}

	public function oembed_response_data( $data, $post, $width, $height )
	{
		$data['provider_name'] = gThemeOptions::info( 'frontpage_title', get_bloginfo( 'name' ) );

		$data['title'] = gThemeContent::getHeader( $post->post_title, gThemeOptions::info( 'embed_sep', _x( '; ', 'Options: Separator: Embed', GTHEME_TEXTDOMAIN ) ), FALSE );

		$data['author_name'] = strip_tags( gThemeContent::byline( $post, '', '', FALSE ) );
		$data['author_url']  = $data['provider_url']; // FIXME: WTF?!

		if ( $thumbnail_id = gThemeImage::getThumbID( gThemeOptions::info( 'embed_image_size', 'single' ), $post->ID ) ) {

			list( $thumbnail_url, $thumbnail_width, $thumbnail_height ) = wp_get_attachment_image_src( $thumbnail_id, [ $width, 99999 ] );
			$data['thumbnail_url']    = $thumbnail_url;
			$data['thumbnail_width']  = $thumbnail_width;
			$data['thumbnail_height'] = $thumbnail_height;

		} else {
			unset( $data['thumbnail_url'], $data['thumbnail_width'], $data['thumbnail_height'] );
		}

		return $data;
	}

	// NOT USED YET
	// @REF: `WP_Embed::delete_oembed_caches()`
	public static function has( $post = NULL )
	{
		if ( ! $post = get_post( $post ) )
			return FALSE;

		foreach ( get_post_custom_keys( $post->ID ) as $key )
			if ( '_oembed_' == substr( $key, 0, 8 ) )
				return TRUE;

		return FALSE;
	}
}
