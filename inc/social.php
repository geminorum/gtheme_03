<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeSocial extends gThemeModuleCore
{

	public function setup_actions( $args = array() )
	{
		add_action( 'wp_head', array( $this, 'wp_head' ) );
	}

	// @REF: [The Open Graph protocol](http://ogp.me/)
	// @SEE: http://scotch.io/quick-tips/all-search-and-social-media-meta-tags-starter-template
	public function wp_head()
	{
		echo "\t".'<meta property="og:locale" content="'.esc_attr( gThemeOptions::info( 'locale', get_locale() ) ).'" />'."\n";
		echo "\t".'<meta property="og:site_name" content="'.esc_attr( gThemeOptions::info( 'blog_name' ) ).'" />'."\n";

		self::meta( 'type', array(
			"\t".'<meta property="og:type" content="',
		), '" />'."\n" , 'esc_attr' );

		self::meta( 'url', array(
			"\t".'<meta property="og:url" content="',
			"\t".'<meta name="twitter:url" content="',
		), '" />'."\n" , 'esc_url' );

		$image = self::meta( 'image', array(
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

		echo "\t".'<meta name="twitter:card" content="'.( $image ? 'summary_large_image' : 'summary' ).'" />'."\n";

		if ( $publisher = gThemeOptions::info( 'rel_publisher', FALSE ) )
			echo "\t".'<link href="'.esc_url( $publisher ).'" rel="publisher" />'."\n";

		if ( $twitter = gThemeOptions::info( 'twitter_site', FALSE ) )
			echo "\t".'<meta name="twitter:site" content="@'.$twitter.'" />'."\n";

		self::author();
	}

	public static function meta( $scope, $b = '', $a ='', $f = FALSE )
	{
		global $post;

		$output = FALSE;

		switch ( $scope ) {

			case 'type':

				if ( is_home() || is_front_page() )
					$output = 'website';

				else if ( is_single() )
					$output = 'article';

			break;
			case 'url':

				if ( is_home() || is_front_page() )
					$output = FALSE;

				else if ( is_single() )
					$output = get_permalink();

			break;
			case 'image':

				$size   = gThemeOptions::info( 'meta_image_size', 'single' );
				$output = gThemeOptions::info( 'default_image_src', FALSE );

				if ( is_single() )
					$output = gThemeImage::getImage( array(
						'tag'   => $size,
						'url'   => TRUE,
						'empty' => $output,
					) );

				else if ( is_tax() || is_tag() || is_category() )
					$output = gThemeImage::termImage( array(
						'tag'   => $size,
						'url'   => TRUE,
						'empty' => $output,
					) );

			break;
			case 'title':

				if ( is_home() || is_front_page() )
					$output = gThemeOptions::info( 'frontpage_title', FALSE );

				else if ( is_single() )
					$output = single_post_title( '', FALSE ).gThemeOptions::info( 'title_sep', ' &raquo; ' ).gThemeOptions::info( 'blog_title' );

				else if ( is_tax() || is_tag() || is_category() )
					$output = single_term_title( '', FALSE ).gThemeOptions::info( 'title_sep', ' &raquo; ' ).gThemeOptions::info( 'blog_title' );

			break;
			case 'description':

				if ( ( is_home() || is_front_page() )
					&& ! $output = gThemeOptions::info( 'frontpage_desc', FALSE ) )
						$output = FALSE;

				else if ( is_single() && has_excerpt() && ! post_password_required( $post ) )
					$output = strip_tags( wp_trim_excerpt( $post->post_excerpt ) );
		}

		if ( FALSE === $output )
			return $output;

		if ( is_array( $b ) )
			foreach ( $b as $key => $before )
				echo $before.( $f ? $f( $output ) : $output ).( is_array( $a ) ? $a[$key] : $a );
		else
			echo $b.( $f ? $f( $output ) : $output ).$a;

		return TRUE;
	}

	public static function author()
	{
		if ( is_single() ) {

			$the_post = get_queried_object();
			if ( ! $the_post )
				return;

			if ( $the_post->post_author == gThemeOptions::getOption( 'default_user', 0 ) )
				return;

			if ( $plus = get_user_meta( $the_post->post_author, 'googleplus', TRUE ) )
				echo "\t".'<link href="'.esc_url( untrailingslashit( $plus ).'?rel=author' ).'" rel="author" />'."\n";

			// FIXME: use gtheme_sanitize_twitter() to display
			if ( $twitter = get_user_meta( $the_post->post_author, 'twitter', TRUE ) )
				echo "\t".'<meta name="twitter:creator" content="@'.$twitter.'" />'."\n";
		}
	}
}
