<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeModuleCore extends gThemeBaseCore
{

	protected $base = 'gtheme';
	protected $key  = NULL;

	// protected $option_base = 'gtheme'; // DEPRECATED
	// protected $option_key  = ''; // DEPRECATED
	protected $ajax        = FALSE;
	protected $args        = [];
	protected $counter     = 0;

	public function __construct( $args = [] )
	{
		if ( ! $this->ajax && gThemeWordPress::isAJAX() )
			throw new Exception( 'Not on AJAX Calls!' );

		if ( wp_installing() && 'wp-activate.php' !== gThemeWordPress::pageNow() )
			throw new Exception( 'Not while WP is Installing!' );

		$this->args = $args;
		$this->setup_actions( $args );
	}

	public function setup_actions( $args = [] ) {}

	public function shortcodes( $shortcodes = [] )
	{
		foreach ( $shortcodes as $shortcode => $method ) {
			remove_shortcode( $shortcode );
			add_shortcode( $shortcode, [ $this, $method ] );
		}
	}

	protected function action( $hook, $args = 1, $priority = 10, $suffix = FALSE )
	{
		if ( $method = self::sanitize_hook( ( $suffix ? $hook.'_'.$suffix : $hook ) ) )
			add_action( $hook, [ $this, $method ], $priority, $args );
	}

	protected function filter( $hook, $args = 1, $priority = 10, $suffix = FALSE )
	{
		if ( $method = self::sanitize_hook( ( $suffix ? $hook.'_'.$suffix : $hook ) ) )
			add_filter( $hook, [ $this, $method ], $priority, $args );
	}

	protected function remove_filter( $list )
	{
		foreach ( $list as $filter ) {

			list( $tag, $function_to_remove, $priority ) = $filter;

			if ( is_null( $priority ) )
				$priority = 10;

			remove_filter( $tag, $function_to_remove, $priority );
		}
	}

	protected static function sanitize_hook( $hook )
	{
		return trim( str_ireplace( [ '-', '.', '/' ], '_', $hook ) );
	}

	protected static function sanitize_base( $hook )
	{
		return trim( str_ireplace( [ '_', '.' ], '-', $hook ) );
	}

	protected function hook()
	{
		$suffix = '';

		foreach ( func_get_args() as $arg )
			if ( $arg )
				$suffix.= '_'.strtolower( self::sanitize_hook( $arg ) );

		return $this->base.'_'.$this->key.$suffix;
	}

	protected function classs()
	{
		$suffix = '';

		foreach ( func_get_args() as $arg )
			if ( $arg )
				$suffix.= '-'.strtolower( self::sanitize_base( $arg ) );

		return $this->base.'-'.self::sanitize_base( $this->key ).$suffix;
	}

	protected function hash()
	{
		$suffix = '';

		foreach ( func_get_args() as $arg )
			$suffix.= maybe_serialize( $arg );

		return md5( $this->base.$this->key.$suffix );
	}

	protected function hashwithsalt()
	{
		$suffix = '';

		foreach ( func_get_args() as $arg )
			$suffix.= maybe_serialize( $arg );

		return wp_hash( $this->base.$this->key.$suffix );
	}

	protected function actions()
	{
		$args = func_get_args();

		if ( count( $args ) < 1 )
			return FALSE;

		$args[0] = $this->hook( $args[0] );

		call_user_func_array( 'do_action', $args );

		return has_action( $args[0] );
	}

	protected function filters()
	{
		$args = func_get_args();

		if ( count( $args ) < 2 )
			return FALSE;

		$args[0] = $this->hook( $args[0] );

		return call_user_func_array( 'apply_filters', $args );
	}

	// USAGE: add_filter( 'body_class', self::__array_append( 'foo' ) );
	protected static function __array_append( $item )
	{
		return function( $array ) use ( $item ) {
			$array[] = $item;
			return $array;
		};
	}

	// USAGE: add_filter( 'shortcode_atts_gallery', self::__array_set( 'columns', 4 ) );
	protected static function __array_set( $key, $value )
	{
		return function( $array ) use ( $key, $value ) {
			$array[$key] = $value;
			return $array;
		};
	}

	// HELPER: wrapper for current_user_can()
	public static function cuc( $cap, $none = TRUE )
	{
		if ( 'none' == $cap || '0' == $cap )
			return $none;

		return current_user_can( $cap );
	}

	// HELPER
	public static function log( $error = '{NO Error Code}', $data = [], $wp_error = NULL )
	{
		if ( ! WP_DEBUG_LOG )
			return;

		$log = array_merge( [
			'error'   => $error,
			'time'    => current_time( 'mysql' ),
			'ip'      => gThemeUtilities::IP(),
			'message' => ( is_null( $wp_error ) ? '{NO WP_Error Object}' : $wp_error->get_error_message() ),
		], $data );

		error_log( print_r( $log, TRUE ) );
	}

	// HELPER
	// FIXME: DEPRECATED: USE: `gThemeHTML::error()`
	public static function error( $message )
	{
		return gThemeUtilities::notice( $message, 'error fade', FALSE );
	}

	// HELPER
	// FIXME: DEPRECATED: USE: `gThemeHTML::success()`
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
		$users = [ 0 => __( '&mdash; Select &mdash;', 'gtheme' ) ];

		foreach ( get_users( [ 'orderby' => 'display_name' ] ) as $user )
			$users[$user->ID] = $user->display_name;

		return $users;
	}

	// HELPER: used by module settings pages
	public function field_debug()
	{
		self::dump( $this->options );
	}

	// DEFAULT METHOD: setting sub html
	public function settings_sub_html( $uri, $sub = 'general' )
	{
		echo '<form method="post" action="">';
			settings_fields( 'gtheme_'.$sub );
			do_settings_sections( 'gtheme_'.$sub );
			submit_button();
		echo '</form>';
	}

	// FIXME: temporarly: see gNetwork module core!
	protected function settings_buttons( $sub = NULL, $wrap = '' )
	{
		if ( FALSE !== $wrap )
			echo '<p class="submit gtheme-settings-buttons '.$wrap.'">';

		echo get_submit_button( _x( 'Save Changes', 'Module Core', 'gtheme' ), 'primary', 'submit', FALSE, [ 'default' => 'default' ] ).'&nbsp;&nbsp;';

		// FIXME: working but also needs the action
		// echo get_submit_button( _x( 'Reset Settings', 'Module Core', 'gtheme' ), 'secondary', 'reset', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';

		if ( FALSE !== $wrap )
			echo '</p>';
	}

	public static function getButtonConfirm( $message = NULL )
	{
		if ( is_null( $message ) )
			$message = _x( 'Are you sure? This operation can not be undone.', 'Module Core', 'gtheme' );

		return [ 'onclick' => sprintf( 'return confirm(\'%s\')', esc_attr( $message ) ) ];
	}

	public function do_settings_field( $atts = [], $wrap = FALSE )
	{
		$args = self::atts( [
			'title'        => '',
			'label_for'    => '',
			'type'         => 'enabled',
			'field'        => FALSE,
			'values'       => [],
			'exclude'      => '',
			'none_title'   => NULL, // select option none title
			'none_value'   => NULL, // select option none value
			'filter'       => FALSE, // will use via sanitize
			'callback'     => FALSE, // callable for `callback` type
			'dir'          => FALSE,
			'default'      => '',
			'description'  => isset( $atts['desc'] ) ? $atts['desc'] : '',
			'before'       => '', // html to print before field
			'after'        => '', // html to print after field
			'field_class'  => '', // formally just class!
			'class'        => '', // now used on wrapper
			'option_group' => $this->key,
			'network'      => NULL, // FIXME: WTF?
			'disabled'     => FALSE,
			'name_attr'    => FALSE, // override
			'id_attr'      => FALSE, // override
			'placeholder'  => FALSE,
			'constant'     => FALSE, // override value if constant defined & disabling
			'data'         => [], // data attr
		], $atts );

		if ( $wrap ) {
			if ( ! empty( $args['label_for'] ) )
				echo '<tr><th scope="row"><label for="'.esc_attr( $args['label_for'] ).'">'.$args['title'].'</label></th><td>';
			else
				echo '<tr class="'.$args['class'].'"><th scope="row">'.$args['title'].'</th><td>';
		}

		if ( ! $args['field'] )
			return;

		$html    = '';
		$name    = $args['name_attr'] ? $args['name_attr'] : $this->base.'_'.$args['option_group'].'['.esc_attr( $args['field'] ).']';
		$id      = $args['id_attr'] ? $args['id_attr'] : $this->base.'-'.$args['option_group'].'-'.esc_attr( $args['field'] );
		$value   = isset( $this->options[$args['field']] ) ? $this->options[$args['field']] : $args['default'];
		$exclude = $args['exclude'] && ! is_array( $args['exclude'] ) ? array_filter( explode( ',', $args['exclude'] ) ) : [];

		if ( $args['before'] )
			echo $args['before'].'&nbsp;';

		switch ( $args['type'] ) {

			case 'hidden':

				echo gThemeHTML::tag( 'input', [
					'type'  => 'hidden',
					'name'  => $name,
					'id'    => $id,
					'value' => $value,
				] );

				$args['description'] = FALSE;

			break;
			case 'enabled':

				$html.= gThemeHTML::tag( 'option', [
					'value'    => '0',
					'selected' => '0' == $value,
				], esc_html__( 'Disabled' ) );

				$html.= gThemeHTML::tag( 'option', [
					'value'    => '1',
					'selected' => '1' == $value,
				], esc_html__( 'Enabled' ) );

				echo gThemeHTML::tag( 'select', [
					'class' => $args['field_class'],
					'name'  => $name,
					'id'    => $id,
				], $html );

			break;
			case 'text':

				if ( ! $args['field_class'] )
					$args['field_class'] = 'regular-text';

				echo gThemeHTML::tag( 'input', [
					'type'        => 'text',
					'class'       => $args['field_class'],
					'name'        => $name,
					'id'          => $id,
					'value'       => $value,
					'dir'         => $args['dir'],
					'placeholder' => $args['placeholder'],
				] );

			break;
			case 'number':

				if ( ! $args['field_class'] )
					$args['field_class'] = 'small-text';

				if ( ! $args['dir'] )
					$args['dir'] = 'ltr';

				echo gThemeHTML::tag( 'input', [
					'type'        => 'number',
					'class'       => $args['field_class'],
					'name'        => $name,
					'id'          => $id,
					'value'       => $value,
					'step'        => '1', // FIXME: get from args
					'min'         => '0', // FIXME: get from args
					'dir'         => $args['dir'],
					'placeholder' => $args['placeholder'],
				] );

			break;
			case 'checkbox':

				if ( count( $args['values'] ) ) {
					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html.= gThemeHTML::tag( 'input', [
							'type'    => 'checkbox',
							'class'   => $args['field_class'],
							'name'    => $name.'['.$value_name.']',
							'id'      => $id.'-'.$value_name,
							'value'   => '1',
							'checked' => in_array( $value_name, ( array ) $value ),
							'dir'     => $args['dir'],
						] );

						echo '<p>'.gThemeHTML::tag( 'label', [
							'for' => $id.'-'.$value_name,
						], $html.'&nbsp;'.esc_html( $value_title ) ).'</p>';
					}

				} else {

					$html.= gThemeHTML::tag( 'input', [
						'type'    => 'checkbox',
						'class'   => $args['field_class'],
						'name'    => $name,
						'id'      => $id,
						'value'   => '1',
						'checked' => $value,
						'dir'     => $args['dir'],
					] );

					echo '<p>'.gThemeHTML::tag( 'label', [
						'for' => $id,
					], $html.'&nbsp;'.$args['description'] ).'</p>';

					$args['description'] = FALSE;
				}

			break;
			case 'select':

				if ( FALSE !== $args['values'] ) {

					foreach ( $args['values'] as $value_name => $value_title ) {

						if ( in_array( $value_name, $exclude ) )
							continue;

						$html.= gThemeHTML::tag( 'option', [
							'value'    => $value_name,
							'selected' => $value_name == $value,
						], esc_html( $value_title ) );
					}

					echo gThemeHTML::tag( 'select', [
						'class' => $args['field_class'],
						'name'  => $name,
						'id'    => $id,
					], $html );
				}

			break;
			case 'textarea':

				echo gThemeHTML::tag( 'textarea', [
					'rows'        => 5,
					'cols'        => 45,
					'name'        => $name,
					'id'          => $id,
					'placeholder' => $args['placeholder'],
					'class'       => [
						'regular-text',
						// 'textarea-autosize',
						$args['field_class'],
					],
				], $value );

			break;
			case 'page':

				if ( ! $args['values'] )
					$args['values'] = 'page';

				wp_dropdown_pages( [
					'post_type'        => $args['values'],
					'selected'         => $value,
					'name'             => $name,
					'id'               => $id,
					'class'            => $args['field_class'],
					'exclude'          => implode( ',', $exclude ),
					'show_option_none' => __( '&mdash; Select Page &mdash;', 'gtheme' ),
					'sort_column'      => 'menu_order',
					'sort_order'       => 'asc',
					'post_status'      => [ 'publish', 'future', 'draft' ],
				] );

			break;
			case 'button':

				echo get_submit_button(
					$value,
					( empty( $args['field_class'] ) ? 'secondary' : $args['field_class'] ),
					$args['field'], // $id,
					FALSE,
					$args['values']
				);

			break;
			case 'file':

				echo gThemeHTML::tag( 'input', [
					'type'     => 'file',
					'id'       => $id,
					'name'     => $id,
					'class'    => $args['field_class'],
					'disabled' => $args['disabled'],
					'dir'      => $args['dir'],
					'data'     => $args['data'],
				] );

			break;
			case 'custom':

				if ( ! is_array( $args['values'] ) )
					echo $args['values'];
				else
					echo $value;

			break;
			case 'action_hook':

				if ( ! empty( $args['values'] ) )
					do_action( $args['values'], $args );

			break;
			case 'callback':

				if ( is_callable( $args['values'] ) )
					call_user_func_array( $args['values'], [ $args ] );

			break;
			case 'debug':

				self::dump( $this->options );

			break;
			default:

				_e( 'Error: settings type undefined.', 'gtheme' );
		}

		if ( $args['after'] )
			echo '&nbsp;'.$args['after'];

		if ( $args['description'] && FALSE !== $args['values'] )
			echo gThemeHTML::tag( 'p', [
				'class' => 'description',
			], $args['description'] );

		if ( $wrap )
			echo '</td></tr>';
	}

	protected function selector( $prefix = 'theme-selector-%d' )
	{
		if ( FALSE === strpos( $prefix, '%d' ) )
			$selector = $prefix.$this->counter;
		else
			$selector = sprintf( $prefix, $this->counter );

		$this->counter++;
		return $selector;
	}

	public static function shortcodeWrap( $html, $suffix = FALSE, $args = [], $block = TRUE, $extra = [] )
	{
		if ( is_null( $html ) )
			return $html;

		$before = empty( $args['before'] ) ? '' : $args['before'];
		$after  = empty( $args['after'] )  ? '' : $args['after'];

		if ( empty( $args['wrap'] ) )
			return $before.$html.$after;

		$classes = [ '-wrap', 'gtheme-wrap-shortcode' ];

		if ( $suffix )
			$classes[] = 'shortcode-'.$suffix;

		if ( isset( $args['context'] ) && $args['context'] )
			$classes[] = 'context-'.$args['context'];

		if ( ! empty( $args['class'] ) )
			$classes[] = $args['class'];

		if ( $after )
			return $before.gThemeHTML::tag( $block ? 'div' : 'span', array_merge( [ 'class' => $classes ], $extra ), $html ).$after;

		return gThemeHTML::tag( $block ? 'div' : 'span', array_merge( [ 'class' => $classes ], $extra ), $before.$html );
	}
}
