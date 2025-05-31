<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

/**
 * If your theme has `woocommerce.php`, you will be unable to override
 * `woocommerce/archive-product.php` custom template in your theme, as
 * `woocommerce.php` has priority over other template files. This is
 * intended to prevent display issues.
 *
 * @source https://developer.woocommerce.com/docs/theming/theme-development/template-structure
 */

if ( have_posts() ) {

	gThemeTemplate::wrapOpen( 'woocommerce' );
		// gThemeNavigation::breadcrumb( [ 'home' => 'home' ] );

		do_action( 'woocommerce_before_main_content' );

		woocommerce_content(); // @REF: https://developer.woocommerce.com/docs/theming/theme-development/classic-theme-developer-handbook

		do_action( 'woocommerce_after_main_content' );

		// gThemeNavigation::content( 'woocommerce' );
	gThemeTemplate::wrapClose( 'woocommerce' );

} else {

	gThemeTemplate::wrapOpen( 'notfound' );

		gThemeContent::notFound();

	gThemeTemplate::wrapClose( 'notfound' );
}

gThemeTemplate::sidebar( 'woocommerce' );
