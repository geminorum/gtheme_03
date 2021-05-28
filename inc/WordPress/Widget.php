<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeWidget extends WP_Widget
{

	const BASE   = 'gtheme';
	const MODULE = FALSE;

	public function __construct()
	{
		$args = gThemeBaseCore::atts( [
			'name'    => FALSE,
			'class'   => '',
			'title'   => '',
			'desc'    => '',
			'control' => [],
			'flush'   => [],
		], $this->setup() );

		if ( ! $args['name'] )
			return FALSE;

		parent::__construct( static::BASE.'_'.$args['name'], $args['title'], [
			'description' => $args['desc'],
			'classname'   => '{GTHEME_WIDGET_CLASSNAME} '.'widget-'.static::BASE.'-'.$args['class'],
		], $args['control'] );

		$this->alt_option_name = 'widget_'.static::BASE.'_'.$args['name'];

		foreach ( $args['flush'] as $action )
			add_action( $action, [ $this, 'flush_widget_cache' ] );
	}

	public static function setup()
	{
		return [
			'name'  => '',
			'class' => '',
			'title' => '',
			'desc'  => '',
			'flush' => [
				'save_post',
				'deleted_post',
				'switch_theme',
			],
		];
	}

	// override this to bypass caching
	public function widget( $args, $instance )
	{
		// TODO: add general action here
		$this->widget_cache( $args, $instance );
		// TODO: add general action here
	}

	// override this for diffrent types of caching
	protected function widget_cache_key( $instance = [] )
	{
		return $this->alt_option_name;
	}

	public function widget_cache( $args, $instance, $prefix = '' )
	{
		if ( $this->is_preview() )
			return $this->widget_html( $args, $instance );

		$key = $this->widget_cache_key( $instance );

		if ( gThemeWordPress::isFlush() )
			delete_transient( $key );

		if ( FALSE === ( $cache = get_transient( $key ) ) )
			$cache = [];

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[$args['widget_id'].$prefix] ) )
			return print $cache[$args['widget_id'].$prefix];

		ob_start();

		if ( $this->widget_html( $args, $instance ) )
			$cache[$args['widget_id'].$prefix] = ob_get_flush();

		else
			return ob_end_flush();

		set_transient( $key, $cache, GTHEME_CACHETTL );
	}

	// FIXME: DROP THIS
	public function widget_cache_OLD( $args, $instance, $prefix = '' )
	{
		$key   = $this->widget_cache_key( $instance );
		$cache = $this->is_preview() ? [] : wp_cache_get( $key, 'widget' );

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( is_array( $cache ) && isset( $cache[$args['widget_id'].$prefix] ) ) {
			echo $cache[$args['widget_id'].$prefix];
			return;
		}

		ob_start();

		if ( $this->widget_html( $args, $instance ) ) {
			if ( ! $this->is_preview() ) {
				$cache[$args['widget_id'].$prefix] = ob_get_flush();
				wp_cache_set( $key, $cache, 'widget' );
			} else {
				ob_end_flush();
			}
		} else {
			ob_end_flush();
		}
	}

	public function widget_html( $args, $instance )
	{
		return FALSE;
	}

	public function before_widget( $args, $instance, $echo = TRUE, $extra = '' )
	{
		$classes = [];

		if ( ! empty( $instance['context'] ) )
			$classes[] = 'context-'.$instance['context'];

		if ( ! empty( $instance['class'] ) )
			$classes[] = $instance['class'];

		if ( ! empty( $instance['title_image'] ) )
			$classes[] = '-has-title-image';

		$html = preg_replace( '%{GTHEME_WIDGET_CLASSNAME}%', gThemeHTML::prepClass( $classes, $extra ), $args['before_widget'] );

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function after_widget( $args, $instance, $echo = TRUE )
	{
		if ( ! $echo )
			return $args['after_widget'];

		echo $args['after_widget'];
	}

	public function widget_title( $args, $instance, $echo = TRUE, $default = '' )
	{
		$title = apply_filters( 'widget_title',
			empty( $instance['title'] ) ? $default : $instance['title'],
			$instance,
			$this->id_base
		);

		if ( ! $title )
			return '';

		if ( ! empty( $instance['title_image'] ) )
			$title = gThemeHTML::img( $instance['title_image'], '-title-image', $title );

		if ( ! empty( $instance['title_link'] ) )
			$title = gThemeHTML::link( $title, $instance['title_link'] );

		$html = $args['before_title'].$title.$args['after_title'];

		if ( ! $echo )
			return $html;

		echo $html;
	}

	// NOTE: may not flush properly with no instance info
	public function flush_widget_cache()
	{
		$key = $this->widget_cache_key();
		// wp_cache_delete( $key, 'widget' );
		delete_transient( $key );
	}

	public function before_form( $instance, $echo = TRUE )
	{
		$classes = [ static::BASE.'-wrap', '-wrap', '-admin-widgetform' ];

		if ( self::MODULE )
			$classes[] = '-'.self::MODULE;

		$html = '<div class="'.gThemeHTML::prepClass( $classes ).'">';

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function after_form( $instance, $echo = TRUE )
	{
		$html = '</div>';

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function form_content( $instance, $default = '', $field = 'content', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Custom HTML:', 'Widget: Setting', 'gtheme' );

		echo '<p>';

		gThemeHTML::label( $label, $this->get_field_id( $field ), FALSE );

		echo gThemeHTML::tag( 'textarea', [
			'rows'  => '3',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'class' => 'widefat code textarea-autosize',
		], isset( $instance[$field] ) ? $instance[$field] : $default );

		echo '</p>';
	}

	public function form_number( $instance, $default = '10', $field = 'number', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Number of posts to show:', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'  => 'number',
			'size'  => 3,
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		gThemeHTML::label( $label.' '.$html, $this->get_field_id( $field ) );
	}

	public function form_context( $instance, $default = '', $field = 'context' )
	{
		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		] );

		gThemeHTML::label( _x( 'Context:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_class( $instance, $default = '', $field = 'class' )
	{
		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		] );

		gThemeHTML::label( _x( 'CSS Class:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_post_type( $instance, $default = 'post', $field = 'post_type', $any = TRUE )
	{
		$html = '';
		$type = isset( $instance[$field] ) ? $instance[$field] : $default;

		if ( $any )
			$html.= gThemeHTML::tag( 'option', [
				'value'    => 'any',
				'selected' => $type == 'any',
			], _x( '&ndash; (Any)', 'Widget: Setting', 'gtheme' ) );

		foreach ( gThemeWordPress::getPostTypes() as $name => $title )
			$html.= gThemeHTML::tag( 'option', [
				'value'    => $name,
				'selected' => $type == $name,
			], $title );

		$html = gThemeHTML::tag( 'select', [
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		], $html );

		gThemeHTML::label( _x( 'PostType:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_taxonomy( $instance, $default = 'all', $field = 'taxonomy', $post_type_field = 'post_type', $post_type_default = 'any', $option_all = 'all' )
	{
		$html      = '';
		$post_type = isset( $instance[$post_type_field] ) ? $instance[$post_type_field] : $post_type_default;
		$taxonomy  = isset( $instance[$field] ) ? $instance[$field] : $default;

		if ( $option_all )
			$html.= gThemeHTML::tag( 'option', [
				'value' => $option_all,
			], _x( '&mdash; All Taxonomies &mdash;', 'Widget: Setting', 'gtheme' ) );

		foreach ( gThemeWordPress::getTaxonomies( 0, [], $post_type ) as $name => $title )
			$html.= gThemeHTML::tag( 'option', [
				'value'    => $name,
				'selected' => $taxonomy == $name,
			], $title );

		$html = gThemeHTML::tag( 'select', [
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		], $html );

		gThemeHTML::label( _x( 'Taxonomy:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_title( $instance, $default = '', $field = 'title' )
	{
		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		gThemeHTML::label( _x( 'Title:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_title_link( $instance, $default = '', $field = 'title_link' )
	{
		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		] );

		gThemeHTML::label( _x( 'Title Link:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_title_image( $instance, $default = '', $field = 'title_image' )
	{
		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		] );

		gThemeHTML::label( _x( 'Title Image:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_custom_link( $instance, $default = '', $field = 'custom_link', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Custom Link:', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'  => 'url',
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		] );

		gThemeHTML::label( $label.$html, $this->get_field_id( $field ) );
	}

	public function form_custom_code( $instance, $default = '', $field = 'custom_code', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Custom Code:', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => [ 'widefat', 'code' ],
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
			'dir'   => 'ltr',
		] );

		gThemeHTML::label( $label.$html, $this->get_field_id( $field ) );
	}

	public function form_custom_empty( $instance, $default = '', $field = 'empty', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Empty Message:', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => [ 'widefat' ],
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		gThemeHTML::label( $label.$html, $this->get_field_id( $field ) );
	}

	public function form_avatar_size( $instance, $default = '32', $field = 'avatar_size' )
	{
		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'size'  => 3,
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		gThemeHTML::label( _x( 'Avatar Size:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_image_size( $instance, $default = 'thumbnail', $field = 'image_size', $post_type = 'post' )
	{
		$sizes = [];

		foreach ( wp_get_additional_image_sizes() as $name => $size )
			$sizes[$name] = ( isset( $size['title'] ) ? $size['title'] : $name )
				.' ('.number_format_i18n( $size['width'] )
				.'&nbsp;&times;&nbsp;'
				.number_format_i18n( $size['height'] ).')';

		if ( empty( $sizes ) )
			return gThemeHTML::desc( '<br />'._x( 'No Image Size Available!', 'Widget: Setting', 'gtheme' ), TRUE, '-empty' );

		$selected = isset( $instance[$field] ) ? $instance[$field] : $default;
		$html     = '';

		foreach ( $sizes as $size => $title )
			$html.= gThemeHTML::tag( 'option', [
				'value'    => $size,
				'selected' => $selected == $size,
			], $title );

		$html = gThemeHTML::tag( 'select', [
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		], $html );

		gThemeHTML::label( _x( 'Image Size:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_dropdown( $instance, $values, $default = '', $field = 'selected', $label = NULL )
	{
		$selected = isset( $instance[$field] ) ? $instance[$field] : $default;

		if ( is_null( $label ) )
			$label = '';

		$html = gThemeHTML::dropdown( $values, [
			'id'         => $this->get_field_id( $field ),
			'name'       => $this->get_field_name( $field ),
			'none_title' => __( '&mdash; Select &mdash;', 'gtheme' ),
			'class'      => 'widefat',
			'selected'   => $selected,
		] );

		gThemeHTML::label( $label.$html, $this->get_field_id( $field ) );
	}

	public function form_checkbox( $instance, $default = FALSE, $field = 'checked', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Checked', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'    => 'checkbox',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'checked' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		gThemeHTML::label( $html.'&nbsp;'.$label, $this->get_field_id( $field ) );
	}

	// only works on hierarchical
	public function form_page_id( $instance, $default = '0', $field = 'page_id', $post_type_field = 'posttype', $post_type_default = 'page', $label = NULL )
	{
		$post_type = isset( $instance[$post_type_field] ) ? $instance[$post_type_field] : $post_type_default;
		$page_id   = isset( $instance[$field] ) ? $instance[$field] : $default;

		if ( is_null( $label ) )
			$label = _x( 'Page:', 'Widget: Setting', 'gtheme' );

		$html = wp_dropdown_pages( [
			'post_type'        => $post_type,
			'selected'         => $page_id,
			'name'             => $this->get_field_name( $field ),
			'id'               => $this->get_field_id( $field ),
			'class'            => 'widefat',
			'show_option_none' => __( '&mdash; Select &mdash;', 'gtheme' ),
			'sort_column'      => 'menu_order, post_title',
			'echo'             => FALSE,
		] );

		if ( ! $html ) {
			$html = '<br /><code>N/A</code>';
			gThemeHTML::inputHidden( $this->get_field_name( $field ), $page_id );
		}

		gThemeHTML::label( $label.$html, $this->get_field_id( $field ) );
	}

	public function form_term_id( $instance, $default = '0', $field = 'term_id', $taxonomy_field = 'taxonomy', $taxonomy_default = 'post_tag' )
	{
		$taxonomy = isset( $instance[$taxonomy_field] ) ? $instance[$taxonomy_field] : $taxonomy_default;
		$term_id  = isset( $instance[$field] ) ? $instance[$field] : $default;

		if ( 'all' == $taxonomy )
			return gThemeHTML::desc( '<br />'._x( 'Select taxonomy first!', 'Widget: Setting', 'gtheme' ), TRUE, '-empty' );

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => FALSE,
		] );

		if ( is_wp_error( $terms ) )
			return gThemeHTML::desc( '<br />'._x( 'The taxonomy is not available!', 'Widget: Setting', 'gtheme' ), TRUE, '-empty' );

		if ( empty( $terms ) )
			return gThemeHTML::desc( '<br />'._x( 'No terms available!', 'Widget: Setting', 'gtheme' ), TRUE, '-empty' );

		$html = gThemeHTML::tag( 'option', [
			'value'    => '0',
			'selected' => $term_id == '0',
		], __( '&mdash; Select &mdash;', 'gtheme' ) );

		foreach ( $terms as $term )
			$html.= gThemeHTML::tag( 'option', [
				'value'    => $term->term_id,
				'selected' => $term_id == $term->term_id,
			], $term->name );

		$html = gThemeHTML::tag( 'select', [
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		], $html );

		gThemeHTML::label( _x( 'Term:', 'Widget: Setting', 'gtheme' ).$html, $this->get_field_id( $field ) );
	}

	public function form_has_thumbnail( $instance, $default = FALSE, $field = 'has_thumbnail', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Must has Thumbnail Image', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'    => 'checkbox',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'checked' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		gThemeHTML::label( $html.'&nbsp;'.$label, $this->get_field_id( $field ) );
	}
}
