<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeModuleCore
{

	protected $option_base = 'gtheme';
	protected $option_key  = '';
	protected $ajax        = FALSE;
	protected $args        = array();
	protected $counter     = 0;

	function __construct( $args = array() )
	{
		if ( ( ! $this->ajax && self::isAJAX() )
			|| ( defined( 'WP_INSTALLING' ) && constant( 'WP_INSTALLING' ) ) )
			return;

		$this->args = $args;
		$this->setup_actions( $args );
	}

	public function setup_actions( $args = array() ) {}

	public static function isAJAX()
	{
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	// INTERNAL: used on anything deprecated
	protected static function __dep( $use = FALSE )
	{
		if ( ! WP_DEBUG_LOG )
			return;

		$trace = debug_backtrace();

		$log = 'DEP: ';

		if ( isset( $trace[1]['class'] ) )
			$log .= $trace[1]['class'].'::';

		$log .= $trace[1]['function'].'()';

		if ( isset( $trace[2]['function'] ) ) {
			$log .= '|FROM: ';
			if ( isset( $trace[2]['class'] ) )
				$log .= $trace[2]['class'].'::';
			$log .= $trace[2]['function'].'()';
		}

		if ( $use )
			$log .= '|USE: '.$use;

		error_log( $log );
	}

	// TODO: DRAFT: not tested
	// http://stackoverflow.com/a/9934684
	// SEE: http://xdebug.org/docs/install
	protected function __callee()
	{
		return sprintf("callee() called @ %s: %s from %s::%s",
			xdebug_call_file(),
			xdebug_call_line(),
			xdebug_call_class(),
			xdebug_call_function()
		);
	}

	// HELPER
	// ANCESTOR: shortcode_atts()
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

	// HELPER: wrapper for current_user_can()
	public static function cuc( $cap, $none = TRUE )
	{
		if ( 'none' == $cap || '0' == $cap )
			return $none;

		return current_user_can( $cap );
	}

	// HELPER
	public static function log( $error = '{NO Error Code}', $data = array(), $wp_error = NULL )
	{
		if ( ! WP_DEBUG_LOG )
			return;

		$log = array_merge( array(
			'error'   => $error,
			'time'    => current_time( 'mysql' ),
			'ip'      => gThemeUtilities::IP(),
			'message' => ( is_null( $wp_error ) ? '{NO WP_Error Object}' : $wp_error->get_error_message() ),
		), $data );

		error_log( print_r( $log, TRUE ) );
	}

	// HELPER
	public static function error( $message )
	{
		return gThemeUtilities::notice( $message, 'error fade', FALSE );
	}

	// HELPER
	public static function updated( $message )
	{
		return gThemeUtilities::notice( $message, 'updated fade', FALSE );
	}

	// HELPER
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

	// HELPER
	public static function getUsers()
	{
		$users = array( 0 => __( '&mdash; Select &mdash;', GTHEME_TEXTDOMAIN ) );
		foreach ( get_users( array( 'orderby' => 'display_name' ) ) as $user )
			$users[$user->ID] = $user->display_name;
		return $users;
	}

	// HELPER: used by module settings pages
	public function field_debug()
	{
		gThemeUtilities::dump( $this->options );
	}

	// DEFAULT METHOD: setting sub html
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
		$args = self::atts( array(
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
			'option_group' => $this->option_key,
			'name_attr'    => FALSE, // override
			'id_attr'      => FALSE, // override
			'placeholder'  => FALSE,
		), $atts );

		if ( $wrap ) {
			if ( ! empty( $args['label_for'] ) )
				echo '<tr><th scope="row"><label for="'.esc_attr( $args['label_for'] ).'">'.$args['title'].'</label></th><td>';
			else
				echo '<tr class="'.$args['class'].'"><th scope="row">'.$args['title'].'</th><td>';
		}

		if ( ! $args['field'] )
			return;

		$html    = '';
		$name    = $args['name_attr'] ? $args['name_attr'] : $this->option_base.'_'.$args['option_group'].'['.esc_attr( $args['field'] ).']';
		$id      = $args['id_attr'] ? $args['id_attr'] : $this->option_base.'-'.$args['option_group'].'-'.esc_attr( $args['field'] );
		$value   = isset( $this->options[$args['field']] ) ? $this->options[$args['field']] : $args['default'];
		$exclude = $args['exclude'] && ! is_array( $args['exclude'] ) ? array_filter( explode( ',', $args['exclude'] ) ) : array();

		if ( $args['before'] )
			echo $args['before'].'&nbsp;';

		switch ( $args['type'] ) {

			case 'hidden' :

				echo gThemeUtilities::html( 'input', array(
					'type'  => 'hidden',
					'name'  => $name,
					'id'    => $id,
					'value' => $value,
				) );

				$args['description'] = FALSE;

			break;
			case 'enabled' :

				$html .= gThemeUtilities::html( 'option', array(
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

				if ( ! $args['field_class'] )
					$args['field_class'] = 'regular-text';

				echo gThemeUtilities::html( 'input', array(
					'type'        => 'text',
					'class'       => $args['field_class'],
					'name'        => $name,
					'id'          => $id,
					'value'       => $value,
					'dir'         => $args['dir'],
					'placeholder' => $args['placeholder'],
				) );

			break;
			case 'number' :

				if ( ! $args['field_class'] )
					$args['field_class'] = 'small-text';

				if ( ! $args['dir'] )
					$args['dir'] = 'ltr';

				echo gThemeUtilities::html( 'input', array(
					'type'        => 'number',
					'class'       => $args['field_class'],
					'name'        => $name,
					'id'          => $id,
					'value'       => $value,
					'step'        => '1', // FIXME: get from args
					'min'         => '0', // FIXME: get from args
					'dir'         => $args['dir'],
					'placeholder' => $args['placeholder'],
				) );

			break;
			case 'checkbox' :

				if ( count( $args['values'] ) ) {
					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html .= gThemeUtilities::html( 'input', array(
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

					$html .= gThemeUtilities::html( 'input', array(
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
					), $html.'&nbsp;'.$args['description'] ).'</p>';

					$args['description'] = FALSE;
				}

			break;
			case 'select' :

				if ( FALSE !== $args['values'] ) {

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
					'rows'        => 5,
					'cols'        => 45,
					'name'        => $name,
					'id'          => $id,
					'placeholder' => $args['placeholder'],
					'class'       => array(
						'large-text',
						// 'textarea-autosize',
						$args['field_class'],
					),
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
					'type'        => 'file',
					'class'       => $args['field_class'],
					'name'        => $id, // $name,
					'id'          => $id,
					// 'value'       => $value,
					'dir'         => $args['dir'],
					'placeholder' => $args['placeholder'],
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

	public function selector( $prefix = 'theme-selector-%d' )
	{
		if ( FALSE === strpos( $prefix, '%d' ) )
			$selector = $prefix.$this->counter;
		else
			$selector = sprintf( $prefix, $this->counter );

		$this->counter++;
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

	// HELPER
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

	// HELPER
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
