<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeSocial extends gThemeModuleCore
{

	// @REF: [The Open Graph protocol](http://ogp.me/)
	// @SEE: http://scotch.io/quick-tips/all-search-and-social-media-meta-tags-starter-template
	// @SEE: https://blog.logrocket.com/open-graph-sharable-social-media-previews/
	public static function doHead()
	{
		if ( defined( 'GTHEME_IS_SYSTEM_PAGE' )
			&& GTHEME_IS_SYSTEM_PAGE )
				return;

		if ( defined( 'GTHEME_SOCIAL_META_DISABLED' )
			&& GTHEME_SOCIAL_META_DISABLED )
				return;

		echo '<meta property="og:locale" content="'.esc_attr( gThemeOptions::info( 'locale', get_locale() ) ).'" />'."\n";
		echo '<meta property="og:site_name" content="'.esc_attr( gThemeOptions::info( 'blog_name' ) ).'" />'."\n";

		self::meta( 'type', [
			'<meta property="og:type" content="',
		], '" />'."\n" , 'esc_attr' );

		self::meta( 'url', [
			'<meta property="og:url" content="',
			'<meta name="twitter:url" content="',
		], '" />'."\n" , 'esc_url' );

		$image = self::meta( 'image', [
			'<meta itemprop="image" content="',
			'<meta property="og:image" content="',
			'<meta name="twitter:image" content="',
			'<meta name="thumbnail" content="',
			'<link rel="image_src" href="',
		], '" />'."\n" , 'esc_url' );

		self::meta( 'title', [
			'<meta itemprop="name" content="',
			'<meta property="og:title" content="',
		], '" />'."\n" , 'esc_attr' );

		self::meta( 'description', [
			'<meta itemprop="description" content="',
			'<meta property="og:description" content="',
			'<meta name="description" content="',
			'<meta name="twitter:description" content="',
		], '" />'."\n" , 'esc_attr' );

		echo '<meta name="twitter:card" content="'.( $image ? 'summary_large_image' : 'summary' ).'" />'."\n";

		if ( $twitter = gThemeOptions::info( 'twitter_site', FALSE ) )
			echo '<meta name="twitter:site" content="'.gThemeThird::getTwitter( $twitter ).'" />'."\n";

		self::author();
	}

	public static function meta( $scope, $before = '', $after = '', $filter = FALSE )
	{
		global $post;

		$output = FALSE;

		switch ( $scope ) {

			case 'type':

				if ( is_home() || is_front_page() )
					$output = 'website';

				else if ( is_singular( 'page' ) )
					$output = FALSE;

				else if ( is_singular() )
					$output = 'article';

			break;
			case 'url':

				if ( is_home() || is_front_page() )
					$output = FALSE;

				else if ( is_singular() || is_single() )
					$output = get_permalink();

			break;
			case 'image':

				$size  = gThemeOptions::info( 'meta_image_size', 'single' );
				$image = gThemeOptions::info( 'meta_image_all', TRUE );

				if ( $image || is_home() || is_front_page() )
					$output = gThemeOptions::info( 'default_image_src', FALSE );

				if ( is_singular() || is_single() )
					$output = gThemeImage::getImage( [
						'tag'   => $size,
						'url'   => TRUE,
						'empty' => $output,
					] );

				else if ( is_tax() || is_tag() || is_category() )
					$output = gThemeImage::termImage( [
						'tag'   => $size,
						'url'   => TRUE,
						'empty' => $output,
					] );

			break;
			case 'title':

				if ( is_home() || is_front_page() ) {

					$output = gThemeOptions::info( 'frontpage_title', FALSE );

				} else if ( is_singular() || is_single() ) {

					if ( $title = single_post_title( '', FALSE ) )
						$output = $title
							.gThemeOptions::info( 'title_sep', _x( ' &raquo; ', 'Options: Separator: Title', 'gtheme' ) )
							.gThemeOptions::info( 'site_title' );

				} else if ( is_tax() || is_tag() || is_category() ) {

					if ( $title = single_term_title( '', FALSE ) )
						$output = $title
							.gThemeOptions::info( 'title_sep', _x( ' &raquo; ', 'Options: Separator: Title', 'gtheme' ) )
							.gThemeOptions::info( 'site_title' );
				}

			break;
			case 'description':

				if ( ( is_home() || is_front_page() )
					&& ! $output = gThemeOptions::info( 'frontpage_desc', FALSE ) )
						$output = FALSE;

				else if ( ( is_singular() || is_single() ) && has_excerpt() && ! post_password_required( $post ) )
					$output = strip_tags( wp_trim_excerpt( $post->post_excerpt ) );
		}

		$output = apply_filters( 'gtheme_social_meta', $output, $scope, $before, $after, $filter );

		if ( FALSE === $output )
			return $output;

		if ( is_callable( $filter ) )
			$output = call_user_func_array( $filter, [ $output ] );

		if ( is_array( $before ) ) {

			foreach ( $before as $key => $value )
				echo $value.$output.( is_array( $after ) ? $after[$key] : $after );

		} else {

			echo $before.$output.$after;
		}

		return TRUE;
	}

	public static function author()
	{
		if ( ! ( is_singular() || is_single() ) )
			return;

		if ( ! $post = get_queried_object() )
			return;

		// dummy post
		if ( empty( $post->ID ) )
			return;

		if ( $post->post_author == gThemeOptions::getOption( 'default_user', 0 ) )
			return;

		if ( $plus = get_user_meta( $post->post_author, 'googleplus', TRUE ) )
			echo '<link href="'.esc_url( untrailingslashit( $plus ).'?rel=author' ).'" rel="author" />'."\n";

		if ( $twitter = get_user_meta( $post->post_author, 'twitter', TRUE ) )
			echo '<meta name="twitter:creator" content="'.gThemeThird::getTwitter( $twitter ).'" />'."\n";
	}
}
