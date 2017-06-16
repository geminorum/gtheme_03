<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeEmbed extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'site_title'   => TRUE,
			'content_meta' => FALSE,
		), $args ) );

		if ( $site_title )
			add_filter( 'embed_site_title_html', array( $this, 'embed_site_title_html' ) );

		if ( $content_meta )
			add_action( 'embed_content_meta', array( $this, 'embed_content_meta' ) );
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

	public function embed_content_meta()
	{
		// FIXME
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
