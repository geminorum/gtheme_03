<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( have_posts() ) {

	gThemeTemplate::wrapOpen( 'woocommerce' );
		// gThemeNavigation::breadcrumb( [ 'home' => 'home' ] );

		woocommerce_content(); // @REF: https://developer.woocommerce.com/docs/classic-theme-development-handbook/

		// gThemeNavigation::content( 'woocommerce' );
	gThemeTemplate::wrapClose( 'woocommerce' );

} else {

	gThemeTemplate::wrapOpen( 'notfound' );

		gThemeContent::notFound();

	gThemeTemplate::wrapClose( 'notfound' );
}

gThemeTemplate::sidebar( 'woocommerce' );
