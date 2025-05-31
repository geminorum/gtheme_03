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
			'wrapping'        => FALSE, // NOTE: If have `woocommerce.php` on theme then no need to wrap the content
			'fragments'       => TRUE,
			'meta_fields'     => TRUE,
			'placeholders'    => FALSE,
			'shortcodes'      => TRUE,
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
			// add_filter( 'woocommerce_show_page_title', '__return_false' );
			add_action( 'woocommerce_before_main_content', [ __CLASS__, 'before_main_content' ], -999 );
			add_action( 'woocommerce_after_main_content', [ __CLASS__, 'after_main_content' ], 999 );
		}

		if ( $fragments ) {
			add_filter( 'woocommerce_add_to_cart_fragments', [ __CLASS__, 'add_to_cart_fragments' ] );
		}

		if ( $meta_fields ) {
			add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'single_product_summary_before' ], 4 );  // title is on `5`
			add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'single_product_summary_after'  ], 6 );  // title is on `5`
			add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'single_product_summary_byline' ], 8 );  // title is on `5`
			add_action( 'woocommerce_shop_loop_item_title',   [ __CLASS__, 'shop_loop_item_title' ], 15 );
		}

		if ( $placeholders ) {
			add_filter( 'woocommerce_placeholder_img', [ __CLASS__, 'placeholder_img' ], 8, 3 );
			add_filter( 'woocommerce_placeholder_img_src', [ __CLASS__, 'placeholder_img_src' ], 8, 1 );
		}

		if ( $shortcodes ) {
			// @REF: https://woocommerce.com/document/woocommerce-shortcodes/
			// @REF: https://www.uncannyowl.com/knowledge-base/woocommerce-shortcodes/
			add_filter( 'shortcode_atts_product', [ __CLASS__, 'shortcode_atts' ], 12, 4 );
			add_filter( 'shortcode_atts_products', [ __CLASS__, 'shortcode_atts' ], 12, 4 );
			add_filter( 'shortcode_atts_product_category', [ __CLASS__, 'shortcode_atts' ], 12, 4 );
			add_filter( 'shortcode_atts_product_categories', [ __CLASS__, 'shortcode_atts' ], 12, 4 ); // WTF: does not apply `class`
			add_filter( 'shortcode_atts_add_to_cart', [ __CLASS__, 'shortcode_atts' ], 12, 4 );
			add_filter( 'shortcode_atts_product_add_to_cart', [ __CLASS__, 'shortcode_atts' ], 12, 4 );
		}
	}

	public static function available()
	{
		if ( ! function_exists( 'WC' ) )
			return FALSE;

		$woo = WC();

		return $woo instanceof \WooCommerce;
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

	/**
	 * Returns true if on a page which uses WooCommerce.
	 *
	 * @ref https://developer.woocommerce.com/docs/theming/theme-development/conditional-tags
	 * @ref https://www.businessbloomer.com/woocommerce-conditional-logic-ultimate-php-guide/
	 *
	 * @return false|string
	 */
	public static function isPage()
	{
		if ( ! self::available() )
			return FALSE;

		// checks for `is_shop()`/`is_product_taxonomy()`/`is_product()`
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
			return 'is_woocommerce';

		if ( function_exists( 'is_cart' ) && is_cart() )
			return 'is_cart';

		if ( function_exists( 'is_checkout' ) && is_checkout() )
			return 'is_checkout';

		if ( function_exists( 'is_account_page' ) && is_account_page() )
			return 'is_account_page';

		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url() )
			return 'is_wc_endpoint';

		return FALSE;
	}

	public static function accountDropdown( $class = '', $menuname = NULL, $before = '', $after = '' )
	{
		if ( ! self::available() )
			return FALSE;

		$dropdown = is_user_logged_in();

		echo $before.'<div class="dropdown -account-wrap '.$class.'">';

			echo '<a href="'.wc_get_account_endpoint_url( '' ).'" class="gtheme-account-link -account-link';
			echo ( $dropdown ? ' dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside"' : '"' ).'>';
				echo gThemeOptions::info( 'woocommerce_accountlink_text', _x( 'Your Account', 'Modules: WooCommerce: Account', 'gtheme' ) );
			echo '</a>';

			if ( $dropdown )
				gThemeMenu::nav(
					$menuname ?? 'tertiary',
					[
						'class' => '-navigation -account-menu dropdown-menu',
					]
				);

		echo '</div>'.$after;
	}

	// @SEE: https://wordpress.org/plugins/woocommerce-menu-bar-cart/
	public static function cartDropdown( $class = '', $before = '', $after = '' )
	{
		if ( ! self::available() )
			return FALSE;

		$dropdown = ! is_checkout() && ! is_cart();

		echo $before.'<div class="dropdown -cart-wrap '.$class.'">';

			self::cartLink( $dropdown );

			if ( $dropdown ) {
				echo '<div class="dropdown-menu -cart-items">';
					the_widget( 'WC_Widget_Cart', 'title=' );
				echo '</div>';
			}

		echo '</div>'.$after;
	}

	// @REF: https://woocommerce.com/document/show-cart-contents-total/
	public static function cartLink( $dropdown = TRUE )
	{
		if ( ! self::available() )
			return FALSE;

		echo '<a href="'.wc_get_cart_url().'" title="'.esc_attr( strip_tags( WC()->cart->get_cart_total() ) );
		echo '" class="gtheme-cart-link -cart-link'.( $dropdown ? ' dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside"' : '"' ).'>';

			echo gThemeOptions::info( 'woocommerce_cartlink_text', _x( 'Your Cart', 'Modules: WooCommerce: CartLink', 'gtheme' ) );

			if ( $count = WC()->cart->get_cart_contents_count() ) {
				echo '<span class="badge -cart-count">';
					echo $count;
					echo '<span class="visually-hidden">'._nx( 'item', 'items', $count, 'Modules: WooCommerce: CartLink', 'gtheme' ).'</span>';
				echo '</span>';
			}

		echo '</a>';
	}

	public static function add_to_cart_fragments( $fragments )
	{
		ob_start();
		self::cartLink();
		$fragments['a.gtheme-cart-link'] = ob_get_clean();

		return $fragments;
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

			case 'select':

				$args['input_class'][] = 'form-select';
				$args['label_class'][] = 'form-label';

				break;

			default:

				$args['class'][]       = 'form-group'; // NOTE: `.form-group` is DEPRECATED as BS5
				$args['input_class'][] = 'form-control';
				$args['label_class'][] = 'form-label';
		}

		// NOTE: sometimes the type is `text`
		// `jQuery.fn.select2.defaults.set( "selectionCssClass", ":all:" );`
		// `jQuery.fn.select2.defaults.set( "theme", "bootstrap-5" );`
		if ( in_array( $args['type'], [ 'state', 'country', 'select' ], TRUE )
			|| in_array( $args['id'], [ 'billing_city', 'shipping_city', 'billing_state', 'shipping_state' ], TRUE ) ) {
				// @REF: https://apalfrey.github.io/select2-bootstrap-5-theme/
				$args['custom_attributes']['data-theme'] = 'bootstrap-5';
				$args['input_class'][] = 'form-select';
			}

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

		gThemeContent::byline(
			$product->get_id(),
			'<div class="-byline product-byline byline-woocommerce">',
			'</div>',
			TRUE,
			FALSE
		);
	}

	// NOTE: must strip links!
	public static function shop_loop_item_title()
	{
		global $product;

		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) )
			return;

		$allowed = [ 'p', 'span', 'strong', 'b', 'i' ];

		if ( $sub = gThemeEditorial::meta( 'sub_title', [ 'id' => $product->get_id(), 'echo' => FALSE ] ) )
			printf(
				'<%2$s class="-subtitle subtitle product-subtitle">%1$s</%2$s>',
				strip_tags( $sub, $allowed ),
				gThemeOptions::info( 'woocommerce_single_meta_tag', 'h3' )
			);

		if ( $byline = gThemeContent::byline( $product->get_id(), '', '', FALSE, FALSE ) )
			printf(
				'<%2$s class="-byline product-byline byline-woocommerce">%1$s</%2$s>',
				strip_tags( $byline, $allowed ),
				'div'
			);
	}

	public static function placeholder_img_src( $src )
	{
		return gThemeOptions::info( 'woocommerce_image_placeholder_src', FALSE ) ?: $src;
	}

	// @REF: `gThemeImage::imageWithPlaceHolder()`
	public static function placeholder_img( $image_html, $size, $dimensions )
	{
		if ( ! $ratio = gThemeOptions::info( 'woocommerce_image_aspect_ratio', NULL ) )
			return $image_html;

		if ( ! $placeholder = gThemeImage::getPlaceHolder( [], 'woocommerce_image_placeholder' ) )
			return $image_html;

		$before = '<div class="theme-product-placeholder">';
		$before.= '<svg viewBox="0 0 '.str_replace( ':', ' ', $ratio ).'" />';
		$before.= '<div class="-inner-wrap">';
		$after  = '</div></div>';

		return $before.$placeholder.$after;
	}

	// NOTE: for all woo-commerce short-codes
	public static function shortcode_atts( $out, $pairs, $atts, $shortcode )
	{
		if ( empty( $out['class'] ) )
			$out['class'] = '-wrap gtheme-wrap-shortcode shortcode-'.$shortcode;

		else
			$out['class'] = '-wrap gtheme-wrap-shortcode shortcode-'.$shortcode.' '.( (string) $out['class'] );

		switch ( $shortcode ) {
			case 'product_add_to_cart': $out['style'] = ''; break; // override default style
		}

		return $out;
	}
}
