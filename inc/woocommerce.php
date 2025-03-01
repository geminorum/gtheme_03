<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWooCommerce extends gThemeModuleCore
{

	// https://developer.woocommerce.com/docs/classic-theme-development-handbook/
	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'product_gallery' => TRUE,
			'disable_thumbs'  => TRUE,
			'disable_styles'  => FALSE,
			'bootstrap'       => FALSE,
			'wrapping'        => TRUE,
			'meta_fields'     => TRUE,
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

		if ( $disable_styles ) {
			// @REF: https://woocommerce.com/document/disable-the-default-stylesheet/
			add_filter( 'woocommerce_enqueue_styles', '__return_empty_array', 9999 );
			add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 9999 );
		}

		if ( $bootstrap ) {
			add_filter( 'woocommerce_form_field_args', [ $this, 'form_field_args' ], 99, 3 );
			add_filter( 'woocommerce_quantity_input_args', [ $this, 'quantity_input_args' ], 99, 2 );
			add_filter( 'woocommerce_breadcrumb_defaults', [ $this, 'breadcrumb_defaults' ], 99, 1 );
			// add_filter( 'woocommerce_checkout_fields', [ $this, 'checkout_fields' ], 99, 1 );
		}

		if ( $wrapping ) {
			add_action( 'woocommerce_before_main_content', [ __CLASS__, 'before_main_content' ], -999 );
			add_action( 'woocommerce_after_main_content', [ __CLASS__, 'after_main_content' ], 999 );
		}

		if ( $meta_fields ) {
			add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'single_product_summary_before' ], 4 );  // title is on `5`
			add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'single_product_summary_after'  ], 6 );  // title is on `5`
			add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'single_product_summary_byline' ], 8 );  // title is on `5`
		}
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

	public function breadcrumb_defaults( $defaults )
	{
		$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="breadcrumb"><ol class="breadcrumb">';
		$defaults['wrap_after']  = '</ol></nav>';
		$defaults['before']      = '<li class="breadcrumb-item">';
		$defaults['after']       = '</li>';
		$defaults['delimiter']   = '';

		return $defaults;
	}

	public function quantity_input_args( $args, $product )
	{
		$args['classes'][] = 'form-control';

		return $args;
	}

	// @REF: https://rudrastyh.com/woocommerce/woocommerce_form_field.html
	public function form_field_args( $args, $key, $value )
	{
		if ( in_array( $args['type'], [ 'hidden' ], TRUE ) )
			return $args;

		switch ( $args['type'] ) {

			case 'radio':
			case 'checkbox':

				$args['class'][]       = 'form-check';
				$args['input_class'][] = 'form-check-input';
				$args['label_class'][] = 'form-check-label';

				break;

			default:

				$args['class'][]       = 'form-group'; // NOTE: `.form-group` is DEPRECATED as BS5
				$args['input_class'][] = 'form-control';
				$args['label_class'][] = 'form-label';
		}

		// NOTE: sometimes the type is `text`
		if ( in_array( $args['type'], [ 'state', 'country' ], TRUE )
			|| in_array( $args['id'], [ 'billing_city', 'shipping_city', 'billing_state', 'shipping_state' ], TRUE ) )
				// @REF: https://apalfrey.github.io/select2-bootstrap-5-theme/
				$args['custom_attributes']['data-theme'] = 'bootstrap-5';

		return $args;
	}

	// NO NEED
	public function checkout_fields( $fields )
	{
		$groups = [
			'account',
			'billing',
			'shipping',
			'order',
		];

		foreach ( $groups as $group ) {
			foreach ( $fields[$group] as $field => $args ) {
				$fields[$group][$field]['class'][]       = 'form-group';
				$fields[$group][$field]['input_class'][] = 'form-control';
				$fields[$group][$field]['label_class'][] = 'form-label';
			}
		}

		return $fields;
	}

	public function wp_enqueue_scripts()
	{
		$list = [
			'wc-blocks-style',  // Woo-Commerce Blocks
			'brands-styles'  ,  // Woo-Commerce Brands
		];

		foreach ( $list as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

	public static function before_main_content()
	{
		gThemeTemplate::wrapOpen( 'woocommerce' );
	}

	public static function after_main_content()
	{
		gThemeTemplate::wrapClose( 'woocommerce' );
	}

	public static function single_product_summary_before()
	{
		global $product;

		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) )
			return;

		$tag = gThemeOptions::info( 'woocommerce_single_meta_tag', 'h3' );

		gThemeEditorial::meta( 'tagline', [
			'id'     => $product->get_id(),
			'before' => '<'.$tag.' class="-overtitle overtitle product-overtitle">',
			'after'  => '</'.$tag.'>',
		] );
	}

	public static function single_product_summary_after()
	{
		global $product;

		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) )
			return;

		$tag = gThemeOptions::info( 'woocommerce_single_meta_tag', 'h3' );

		gThemeEditorial::meta( 'sub_title', [
			'id'     => $product->get_id(),
			'before' => '<'.$tag.' class="-subtitle subtitle product-subtitle">',
			'after'  => '</'.$tag.'>',
		] );
	}

	public static function single_product_summary_byline()
	{
		global $product;

		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) )
			return;

		gThemeEditorial::author( [
			'id'     => $product->get_id(),
			'before' => '<div class="-byline product-byline byline-woocommerce">',
			'after'  => '</div>',
		] );
	}
}
