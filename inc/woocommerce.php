<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWooCommerce extends gThemeModuleCore
{

	// https://developer.woocommerce.com/docs/classic-theme-development-handbook/
	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'product_gallery' => TRUE,
			'disable_thumbs'  => TRUE,
			'disable_styles'  => TRUE,
		], $args ) );

		if ( ! gThemeWordPress::isPluginActive( 'woocommerce/woocommerce.php' ) )
			return FALSE;

		$this->filter( 'body_class' );

		if ( $product_gallery ) {

			// @REF: https://developer.woocommerce.com/2017/02/28/adding-support-for-woocommerce-2-7s-new-gallery-feature-to-your-theme/

			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}

		if ( $disable_thumbs ) {
			add_filter( 'woocommerce_resize_images', '__return_false' );
			add_filter( 'woocommerce_background_image_regeneration', '__return_false' );
		}

		if ( $disable_styles )
			add_filter( 'woocommerce_enqueue_styles', '__return_false' );
	}

	// @REF: https://github.com/woocommerce/woocommerce/wiki/Declaring-WooCommerce-support-in-themes
	public static function defaults( $extra = [] )
	{
		return array_merge( [

			// @REF: https://developer.woocommerce.com/2017/12/11/wc-3-3-image-size-improvements/
			// https://developer.woocommerce.com/docs/image-sizing-for-theme-developers/
			'thumbnail_image_width'         => 150,
			'gallery_thumbnail_image_width' => 100,
			'single_image_width'            => 300,

			// @REF: https://developer.woocommerce.com/2017/12/09/wc-3-3-will-look-great-on-all-the-themes/
			'product_grid' => [
				'default_rows' => 3,
				'min_rows'     => 2,
				'max_rows'     => 8,

				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 5,
			],
		], $extra );
	}

	public function body_class( $classes )
	{
		if ( $product = wc_get_product() )
			$classes[] = sprintf( 'product-type-%s', $product->get_type() );

		return $classes;
	}

	// @REF: https://developer.woocommerce.com/docs/conditional-tags-in-woocommerce/
	// @SEE: https://www.businessbloomer.com/woocommerce-conditional-logic-ultimate-php-guide/
	public static function isPage()
	{
		if ( ! function_exists( 'WC' ) )
			return FALSE;

		if ( function_exists( 'is_checkout' ) && is_checkout() )
			return TRUE;

		if ( function_exists( 'is_account_page' ) && is_account_page() )
			return TRUE;

		// FIXME: more checks!
		// @SEE: https://www.businessbloomer.com/woocommerce-get-cart-checkout-account-urls/

		return FALSE;
	}
}
