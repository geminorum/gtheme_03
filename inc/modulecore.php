<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeModuleCore
{
	var $_option_base = 'gtheme';
	var $_option_key  = '';
	var $_ajax        = false;
	var $_args        = array();

	function __construct( $args = array() )
	{
		if ( ( ! $this->_ajax && self::isAJAX() )
			|| ( defined( 'WP_INSTALLING' ) && constant( 'WP_INSTALLING' ) ) )
			return;

		$this->_args = $args;
		$this->setup_actions( $args );
	}

	public function setup_actions( $args = array() ) {}

	public static function isAJAX()
	{
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	// helper
	// ANCESTOR : shortcode_atts()
	public static function atts( $pairs, $atts )
	{
		$atts = (array) $atts;
		$out = array();

		foreach( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $atts ) )
				$out[$name] = $atts[$name];
			else
				$out[$name] = $default;
		}

		return $out;
	}

	// helper
	public static function getUsers()
	{
		$users = array( 0 => __( '&mdash; Select &mdash;', GTHEME_TEXTDOMAIN ) );
		foreach ( get_users( array( 'orderby' => 'display_name' ) ) as $user )
			$users[$user->ID] = $user->display_name;
		return $users;
	}

	// used by module settings pages
	public function field_debug()
	{
		gThemeUtilities::dump( $this->options );
	}

	// default setting sub html
	public function settings_sub_html( $settings_uri, $sub = 'general' )
	{
		echo '<form method="post" action="">';
			settings_fields( 'gtheme_'.$sub );
			do_settings_sections( 'gtheme_'.$sub );
			submit_button();
		echo '</form>';
	}

	public function do_settings_field( $atts = array(), $wrap = false )
	{
		$args = shortcode_atts( array(
			'title'        => '',
			'label_for'    => '',
			'type'         => 'enabled',
			'field'        => false,
			'values'       => array(),
			'filter'       => false, // will use via sanitize
			'dir'          => false,
			'default'      => '',
			'desc'         => '',
			'class'        => '',
			'option_group' => $this->_option_key,
		), $atts );

		if ( $wrap ) {
			if ( ! empty( $args['label_for'] ) )
				echo '<tr><th scope="row"><label for="'.esc_attr( $args['label_for'] ).'">'.$args['title'].'</label></th><td>';
			else
				echo '<tr><th scope="row">'.$args['title'].'</th><td>';
		}

		if ( ! $args['field'] )
			return;

		$name = $this->_option_base.'_'.$args['option_group'].'['.esc_attr( $args['field'] ).']';
		$id = $this->_option_base.'-'.$args['option_group'].'-'.esc_attr( $args['field'] );
		$value = isset( $this->options[$args['field']] ) ? $this->options[$args['field']] : $args['default'];

		switch ( $args['type'] ) {
			case 'enabled' :

				$html = gThemeUtilities::html( 'option', array(
					'value'    => '0',
					'selected' => '0' == $value,
				), esc_html__( 'Disabled' ) );

				$html .= gThemeUtilities::html( 'option', array(
					'value'    => '1',
					'selected' => '1' == $value,
				), esc_html__( 'Enabled' ) );

				echo gThemeUtilities::html( 'select', array(
					'class' => $args['class'],
					'name'  => $name,
					'id'    => $id,
				), $html );

			break;

			case 'text' :
				if ( ! $args['class'] )
					$args['class'] = 'regular-text';
				echo gThemeUtilities::html( 'input', array(
					'type'  => 'text',
					'class' => $args['class'],
					'name'  => $name,
					'id'    => $id,
					'value' => $value,
					'dir'   => $args['dir'],
				) );

			break;

			case 'checkbox' :
				if ( count( $args['values'] ) ) {
					foreach( $args['values'] as $value_name => $value_title ) {
						$html = gThemeUtilities::html( 'input', array(
							'type'    => 'checkbox',
							'class'   => $args['class'],
							'name'    => $name.'['.$value_name.']',
							'id'      => $id.'-'.$value_name,
							'value'   => '1',
							'checked' => in_array( $value_name, ( array ) $value ),
							'dir'     => $args['dir'],
						) );

						echo '<p>'.gThemeUtilities::html( 'label', array(
							'for' => $id.'-'.$value_name,
						), $html.'&nbsp;'.esc_html( $value_title ) ).'</p>';
					}
				} else {
					$html = gThemeUtilities::html( 'input', array(
						'type'    => 'checkbox',
						'class'   => $args['class'],
						'name'    => $name,
						'id'      => $id,
						'value'   => '1',
						'checked' => $value,
						'dir'     => $args['dir'],
					) );

					echo '<p>'.gThemeUtilities::html( 'label', array(
						'for' => $id,
					), $html.'&nbsp;'.esc_html( $value_title ) ).'</p>';
				}

			break;

			case 'select' :

				if ( false !== $args['values'] ) { // alow hiding
					$html = '';
					foreach ( $args['values'] as $value_name => $value_title )
						$html .= gThemeUtilities::html( 'option', array(
							'value'    => $value_name,
							'selected' => $value_name == $value,
						), esc_html( $value_title ) );

					echo gThemeUtilities::html( 'select', array(
						'class' => $args['class'],
						'name'  => $name,
						'id'    => $id,
					), $html );

				}

			break;

			case 'textarea' :

				echo gThemeUtilities::html( 'textarea', array(
					'class' => array( 'large-text', 'textarea-autosize', $args['class'] ),
					'name'  => $name,
					'id'    => $id,
					'rows'  => 5,
					'cols'  => 45,
				), esc_textarea( $value ) );

			break;

			case 'page' :

				if ( ! $args['values'] )
					$args['values'] = 'page';

				wp_dropdown_pages( array(
					'post_type'        => $args['values'],
					'selected'         => $value,
					'name'             => $name,
					'id'               => $id,
					'class'            => $args['class'],
					'show_option_none' => __( '&mdash; Select Page &mdash;', GTHEME_TEXTDOMAIN ),
					'sort_column'      => 'menu_order',
					'sort_order'       => 'asc',
					'post_status'      => 'publish,private,draft',
				));

			break;

			default :
				echo 'Error: setting type\'s not defind';
		}

		if ( $args['desc'] )
			echo gThemeUtilities::html( 'p', array(
				'class' => 'description',
			), $args['desc'] );


		if ( $wrap )
			echo '</td></tr>';
	}

	var $_counter = 0;

	public function selector( $prefix = 'theme-selector-%d' )
	{
		if ( false === strpos( $prefix, '%d' ) )
			$selector = $prefix.$this->_counter;
		else
			$selector = sprintf( $prefix, $this->_counter );

		$this->_counter++;
		return $selector;
	}

	public static function getTerms( $taxonomy = 'category', $post_id = FALSE, $object = FALSE, $key = 'term_id' )
	{
		$the_terms = array();

		if ( FALSE === $post_id ) {
			$terms = get_terms( $taxonomy, array(
				'hide_empty' => FALSE,
				'orderby'    => 'name',
				'order'      => 'ASC'
			) );
		} else {
			$terms = get_the_terms( $post_id, $taxonomy );
		}

		if ( is_wp_error( $terms ) || FALSE === $terms )
			return $the_terms;

		$the_list = wp_list_pluck( $terms, $key );
		$terms    = array_combine( $the_list, $terms );

		if ( $object )
			return $terms;

		foreach ( $terms as $term )
			$the_terms[] = $term->term_id;

		return $the_terms;
	}

	public static function getPostTypes( $builtin = NULL )
	{
		$list = array();
		$args = array( 'public' => TRUE );

		if ( ! is_null( $builtin ) )
			$args['_builtin'] = $builtin;

		$post_types = get_post_types( $args, 'objects' );

		foreach ( $post_types as $post_type => $post_type_obj )
			$list[$post_type] = $post_type_obj->labels->name;

		return $list;
	}

	public static function getTaxonomies( $with_post_type = FALSE )
	{
		$list = array();

		$taxonomies = get_taxonomies( array(
			'public'   => TRUE,
			// '_builtin' => TRUE,
		), 'objects' );

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! empty( $taxonomy->labels->menu_name )  ) {
					if ( $with_post_type ) {
						$list[$taxonomy->name] = $taxonomy->labels->menu_name.' ('.implode( __( ', ', GTHEME_TEXTDOMAIN ), $taxonomy->object_type ).')';
					} else {
						$list[$taxonomy->name] = $taxonomy->labels->menu_name;
					}
				}
			}
		}

		return $list;
	}
}
