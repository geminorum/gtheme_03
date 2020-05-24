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
			'classname'   => '{GTHEME_WIDGET_CLASSNAME}'.'widget-'.static::BASE.'-'.$args['class'],
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
		$this->widget_cache( $args, $instance );
	}

	public function widget_cache( $args, $instance, $prefix = '' )
	{
		if ( $this->is_preview() )
			return $this->widget_html( $args, $instance );

		if ( gThemeWordPress::isFlush() )
			delete_transient( $this->alt_option_name );

		if ( FALSE === ( $cache = get_transient( $this->alt_option_name ) ) )
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

		set_transient( $this->alt_option_name, $cache, GTHEME_CACHETTL );
	}

	// FIXME: DROP THIS
	public function widget_cache_OLD( $args, $instance, $prefix = '' )
	{
		$cache = $this->is_preview() ? [] : wp_cache_get( $this->alt_option_name, 'widget' );

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
				wp_cache_set( $this->alt_option_name, $cache, 'widget' );
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

	public function before_widget( $args, $instance, $extra_class = '', $echo = TRUE )
	{
		$classes = isset( $instance['context'] ) && $instance['context'] ? 'context-'.sanitize_html_class( $instance['context'], 'general' ).' ' : '';
		$classes.= isset( $instance['class'] ) && $instance['class'] ? $instance['class'].' ' : '';
		$classes.= $extra_class.' ';

		$html = preg_replace( '%{GTHEME_WIDGET_CLASSNAME}%', $classes, $args['before_widget'] );

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

	public function widget_title( $args, $instance, $default = '', $echo = TRUE )
	{
		$title = apply_filters( 'widget_title',
			empty( $instance['title'] ) ? $default : $instance['title'],
			$instance,
			$this->id_base
		);

		if ( ! $title )
			return '';

		if ( ! empty( $instance['title_link'] ) )
			$title = gThemeHTML::link( $title, $instance['title_link'] );

		$html = $args['before_title'].$title.$args['after_title'];

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public function flush_widget_cache()
	{
		// wp_cache_delete( $this->alt_option_name, 'widget' );
		delete_transient( $this->alt_option_name );
	}

	public function before_form( $instance, $echo = TRUE )
	{
		$classes = [ static::BASE.'-wrap', '-wrap', '-admin-widgetform' ];

		if ( self::MODULE )
			$classes[] = '-'.self::MODULE;

		$html = '<div class="'.join( ' ', $classes ).'">';

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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label );

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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label.' '.$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'Context:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'CSS Class:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'PostType:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'Taxonomy:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'Title:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'Title Link:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label.$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label.$html ).'</p>';
	}

	public function form_custom_empty( $instance, $default = '', $field = 'empty', $label = NULL )
	{
		if ( is_null( $label ) )
			$label = _x( 'Empty Message:', 'Widget: Setting', 'gtheme' );

		$html = gThemeHTML::tag( 'input', [
			'type'  => 'text',
			'class' => [ 'widefat', 'code' ],
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
			'value' => isset( $instance[$field] ) ? $instance[$field] : $default,
		] );

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label.$html ).'</p>';
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

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'Avatar Size:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
	}

	public function form_image_size( $instance, $default = 'thumbnail', $field = 'image_size', $post_type = 'post' )
	{
		$sizes = [];

		foreach ( gThemeOptions::info( 'images', [] ) as $name => $size )
			if ( isset( $size['p'] ) && in_array( $post_type, $size['p'] ) )
				$sizes[$name] = $size['n'].' ('.number_format_i18n( $size['w'] ).'&nbsp;&times;&nbsp;'.number_format_i18n( $size['h'] ).')';

		if ( count( $sizes ) ) {

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

			echo '<p>'.gThemeHTML::tag( 'label', [
				'for' => $this->get_field_id( $field ),
			], _x( 'Image Size:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';

		} else {
			echo '<p>'._x( 'No Image Size Available!', 'Widget: Setting', 'gtheme' ).'</p>';
		}
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

		echo '<p>'.$html.'&nbsp;'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label ).'</p>';
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

		if ( ! $html )
			$html = '<br /><code>N/A</code>';

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], $label.$html ).'</p>';
	}

	public function form_term_id( $instance, $default = '0', $field = 'term_id', $taxonomy_field = 'taxonomy', $taxonomy_default = 'post_tag' )
	{
		$taxonomy = isset( $instance[$taxonomy_field] ) ? $instance[$taxonomy_field] : $taxonomy_default;
		$term_id  = isset( $instance[$field] ) ? $instance[$field] : $default;

		$html = gThemeHTML::tag( 'option', [
			'value'    => '0',
			'selected' => $term_id == '0',
		], __( '&mdash; Select &mdash;', 'gtheme' ) );

		foreach ( get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => FALSE ] ) as $term )
			$html.= gThemeHTML::tag( 'option', [
				'value'    => $term->term_id,
				'selected' => $term_id == $term->term_id,
			], $term->name );

		$html = gThemeHTML::tag( 'select', [
			'class' => 'widefat',
			'name'  => $this->get_field_name( $field ),
			'id'    => $this->get_field_id( $field ),
		], $html );

		echo '<p>'.gThemeHTML::tag( 'label', [
			'for' => $this->get_field_id( $field ),
		], _x( 'Term:', 'Widget: Setting', 'gtheme' ).$html ).'</p>';
	}
}
