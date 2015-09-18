<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeModuleCore
{
	var $_option_base = 'gtheme';
	var $_option_key  = '';
	var $_ajax        = FALSE;
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

		foreach ( $pairs as $name => $default ) {
			if ( array_key_exists( $name, $atts ) )
				$out[$name] = $atts[$name];
			else
				$out[$name] = $default;
		}

		return $out;
	}

	// helper
	// current user can
	public static function cuc( $cap, $none = TRUE )
	{
		if ( 'none' == $cap || '0' == $cap )
			return $none;

		return current_user_can( $cap );
	}

	public static function log( $error = '{NO Error Code}', $data = array(), $wp_error = NULL )
	{
		$log = array_merge( array(
			'error'   => $error,
			'time'    => current_time( 'mysql' ),
			'ip'      => gThemeUtilities::IP(),
			'message' => ( is_null( $wp_error ) ? '{NO WP_Error Object}' : $wp_error->get_error_message() ),
		), $data );

		// FIXME: we need to check WP_DEBUG_DISPLAY ?
		error_log( print_r( $log, TRUE ) );
	}

	public static function getCurrentPostType()
	{
		global $post, $typenow, $pagenow, $current_screen;

		if ( $post && $post->post_type )
			return $post->post_type;

		if ( $typenow )
			return $typenow;

		if ( $current_screen && isset( $current_screen->post_type ) )
			return $current_screen->post_type;

		if ( isset( $_REQUEST['post_type'] ) )
			return sanitize_key( $_REQUEST['post_type'] );

		return NULL;
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

	public function do_settings_field( $atts = array(), $wrap = FALSE )
	{
		// workaround to recent changes on WP 4.3
		if ( isset( $atts['class'] ) && ! isset( $atts['field_class'] ) ) {
			$atts['field_class'] = $atts['class'];
			unset( $atts['class'] );
		}

		$args = shortcode_atts( array(
			'title'        => '',
			'label_for'    => '',
			'type'         => 'enabled',
			'field'        => FALSE,
			'values'       => array(),
			'exclude'      => '',
			'filter'       => FALSE, // will use via sanitize
			'dir'          => FALSE,
			'default'      => '',
			'description'  => isset( $atts['desc'] ) ? $atts['desc'] : '',
			'before'       => '', // html to print before field
			'after'        => '', // html to print after field
			'field_class'  => '', // formally just class!
			'class'        => '', // now used on wrapper
			'option_group' => $this->_option_key,
			'name_attr'    => FALSE, // override
			'id_attr'      => FALSE, // override
		), $atts );

		if ( $wrap ) {
			if ( ! empty( $args['label_for'] ) )
				echo '<tr><th scope="row"><label for="'.esc_attr( $args['label_for'] ).'">'.$args['title'].'</label></th><td>';
			else
				echo '<tr class="'.$args['class'].'"><th scope="row">'.$args['title'].'</th><td>';
		}

		if ( ! $args['field'] )
			return;

		$name    = $args['name_attr'] ? $args['name_attr'] : $this->_option_base.'_'.$args['option_group'].'['.esc_attr( $args['field'] ).']';
		$id      = $args['id_attr'] ? $args['id_attr'] : $this->_option_base.'-'.$args['option_group'].'-'.esc_attr( $args['field'] );
		$value   = isset( $this->options[$args['field']] ) ? $this->options[$args['field']] : $args['default'];
		$exclude = $args['exclude'] && ! is_array( $args['exclude'] ) ? array_filter( explode( ',', $args['exclude'] ) ) : array();

		if ( $args['before'] )
			echo $args['before'].'&nbsp;';

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
					'class' => $args['field_class'],
					'name'  => $name,
					'id'    => $id,
				), $html );

			break;
			case 'text' :

				if ( ! $args['class'] )
					$args['class'] = 'regular-text';
				echo gThemeUtilities::html( 'input', array(
					'type'  => 'text',
					'class' => $args['field_class'],
					'name'  => $name,
					'id'    => $id,
					'value' => $value,
					'dir'   => $args['dir'],
				) );

			break;
			case 'checkbox' :

				if ( count( $args['values'] ) ) {
					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html = gThemeUtilities::html( 'input', array(
							'type'    => 'checkbox',
							'class'   => $args['field_class'],
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
						'class'   => $args['field_class'],
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

				if ( FALSE !== $args['values'] ) { // alow hiding
					$html = '';
					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html .= gThemeUtilities::html( 'option', array(
							'value'    => $value_name,
							'selected' => $value_name == $value,
						), esc_html( $value_title ) );
					}

					echo gThemeUtilities::html( 'select', array(
						'class' => $args['field_class'],
						'name'  => $name,
						'id'    => $id,
					), $html );
				}

			break;
			case 'textarea' :

				echo gThemeUtilities::html( 'textarea', array(
					'class' => array(
						'large-text',
						// 'textarea-autosize',
						$args['field_class'],
					),
					'name'  => $name,
					'id'    => $id,
					'rows'  => 5,
					'cols'  => 45,
				// ), esc_textarea( $value ) );
				), $value );

			break;
			case 'page' :

				if ( ! $args['values'] )
					$args['values'] = 'page';

				wp_dropdown_pages( array(
					'post_type'        => $args['values'],
					'selected'         => $value,
					'name'             => $name,
					'id'               => $id,
					'class'            => $args['field_class'],
					'exclude'          => implode( ',', $exclude ),
					'show_option_none' => __( '&mdash; Select Page &mdash;', GTHEME_TEXTDOMAIN ),
					'sort_column'      => 'menu_order',
					'sort_order'       => 'asc',
					'post_status'      => 'publish,private,draft',
				));

			break;
			case 'button' :

				submit_button(
					$value,
					( empty( $args['field_class'] ) ? 'secondary' : $args['field_class'] ),
					$id,
					FALSE
				);

			break;
			case 'file' :

				echo gThemeUtilities::html( 'input', array(
					'type'  => 'file',
					'class' => $args['field_class'],
					'name'  => $id, // $name,
					'id'    => $id,
					// 'value' => $value,
					'dir'   => $args['dir'],
				) );

			break;
			case 'custom' :

				if ( ! is_array( $args['values'] ) )
					echo $args['values'];
				else
					echo $value;

			break;
			case 'debug' :

				gThemeUtilities::dump( $this->options );

			break;
			default :

				_e( 'Error: settings type undefined.', GTHEME_TEXTDOMAIN );
		}

		if ( $args['after'] )
			echo '&nbsp;'.$args['after'];

		if ( $args['description'] && FALSE !== $args['values'] )
			echo gThemeUtilities::html( 'p', array(
				'class' => 'description',
			), $args['description'] );

		if ( $wrap )
			echo '</td></tr>';
	}

	var $_counter = 0;

	public function selector( $prefix = 'theme-selector-%d' )
	{
		if ( FALSE === strpos( $prefix, '%d' ) )
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
			// 'public'   => TRUE,
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
