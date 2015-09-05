<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSocial extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		add_action( 'wp_head', array( &$this, 'wp_head' ) );
	}

	public function wp_head()
	{
		echo "\t".'<meta name="twitter:card" content="summary" />'."\n";
		echo "\t".'<meta property="og:locale" content="'.esc_attr( gtheme_get_info( 'locale', get_locale() ) ).'" />'."\n";
		echo "\t".'<meta property="og:site_name" content="'.esc_attr( get_bloginfo( 'name' ) ).'" />'."\n";

		self::meta( 'type', array(
			"\t".'<meta property="og:type" content="',
		), '" />'."\n" , 'esc_attr' );

		self::meta( 'url', array(
			"\t".'<meta property="og:url" content="',
			"\t".'<meta name="twitter:url" content="',
		), '" />'."\n" , 'esc_url' );

		self::meta( 'image', array(
			"\t".'<meta itemprop="image" content="',
			"\t".'<meta property="og:image" content="',
			"\t".'<meta name="twitter:image" content="',
			"\t".'<meta name="thumbnail" content="',
			"\t".'<link rel="image_src" href="',
		), '" />'."\n" , 'esc_url' );

		self::meta( 'title', array(
			"\t".'<meta itemprop="name" content="',
			"\t".'<meta property="og:title" content="',
		), '" />'."\n" , 'esc_attr' );

		self::meta( 'description', array(
			"\t".'<meta itemprop="description" content="',
			"\t".'<meta property="og:description" content="',
			"\t".'<meta name="description" content="',
			"\t".'<meta name="twitter:description" content="',
		), '" />'."\n" , 'esc_attr' );

		$publisher = gtheme_get_info( 'rel_publisher', FALSE );
		if ( $publisher )
			echo "\t".'<link href="'.esc_url( $publisher ).'" rel="publisher" />'."\n";

		$twitter_site = gtheme_get_info( 'twitter_site', FALSE );
		if ( $twitter_site )
			echo "\t".'<meta name="twitter:site" content="@'.$twitter_site.'" />'."\n";

		self::author();

	}

	public static function meta( $scope, $b = '', $a ='', $f = FALSE )
	{
		global $post;
		$output = FALSE;

		switch ( $scope ) {
			case 'type' : {
				if ( is_home() || is_front_page() ) {
					//$output = get_permalink();
				} elseif ( is_single() ){
					$output = 'article';
				}
			} break;
			case 'url' : {
				if ( is_home() || is_front_page() ) {
					//$output = get_permalink();
				} elseif ( is_single() ){
					$output = get_permalink();
				}
			} break;
			case 'image' : {
				$output = gtheme_get_info( 'default_image_src', FALSE );
				if ( is_single() )
					$output = gThemeImage::get_image( array(
						'tag'   => gtheme_get_info( 'meta_image_size', 'single' ),
						'url'   => TRUE,
						'empty' => FALSE,
					) );
			} break;
			case 'title' : {
				if ( is_home() || is_front_page() ) {
					$output = gtheme_get_info( 'frontpage_title', FALSE );
				} elseif ( is_single() ){
					$output = get_the_title().gtheme_get_info( 'title_sep', ' &raquo; ' ).get_bloginfo( 'name' );
				}
			} break;
			case 'description' : {
				if ( is_home() || is_front_page() ) {
					$output = gtheme_get_info( 'frontpage_desc', FALSE );
					if ( empty( $output ) )
						$output = FALSE;
				} elseif ( is_single() ) {
					// gmeta lead
					// $output = get_gmeta( 'le', array( 'id' => FALSE, 'def' => FALSE ) );
					// if ( $output ) break; else $output = FALSE; // fallback returns empty

					if ( has_excerpt() && ! post_password_required( $post ) )
						$output = strip_tags( wp_trim_excerpt( $post->post_excerpt ) );
				}
			} break;
		}

		if ( FALSE !== $output ) {
			if ( is_array( $b ) ) {
				foreach ( $b as $key => $before )
					echo $before.( $f ? $f( $output ) : $output ).( is_array( $a ) ? $a[$key] : $a );
			} else {
				echo $b.( $f ? $f( $output ) : $output ).$a;
			}
		} else {
			return FALSE;
		}
	}

	public static function author()
	{
		if ( is_single() ) {
			$the_post = get_queried_object();
			if ( ! $the_post )
				return;

			$default_user = gtheme_get_option( 'default_user', 0 );
			if ( $the_post->post_author == $default_user )
				return;

			$plus_url = get_user_meta( $the_post->post_author, 'googleplus', TRUE );
			if ( $plus_url && ! empty( $plus_url ) )
				echo "\t".'<link href="'.esc_url( untrailingslashit( $plus_url ).'?rel=author' ).'" rel="author" />'."\n";

			// INFO : use gtheme_sanitize_twitter(  ) to display
			$twitter = get_user_meta( $the_post->post_author, 'twitter', TRUE );
			if ( $twitter && ! empty( $twitter ) )
				echo "\t".'<meta name="twitter:creator" content="@'.$twitter.'" />'."\n";
		}
	}
}

// http://scotch.io/quick-tips/all-search-and-social-media-meta-tags-starter-template
