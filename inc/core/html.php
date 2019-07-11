<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeHTML extends gThemeBaseCore
{

	public static function rtl()
	{
		return function_exists( 'is_rtl' ) ? is_rtl() : FALSE;
	}

	public static function link( $html, $link = '#', $target_blank = FALSE )
	{
		return self::tag( 'a', array( 'href' => $link, 'class' => '-link', 'target' => ( $target_blank ? '_blank' : FALSE ) ), $html );
	}

	public static function mailto( $email, $title = NULL )
	{
		return '<a class="-mailto" href="mailto:'.trim( $email ).'">'.( $title ? $title : trim( $email ) ).'</a>';
	}

	public static function scroll( $html, $to, $title = '' )
	{
		return '<a class="scroll" title="'.$title.'" href="#'.$to.'">'.$html.'</a>';
	}

	public static function img( $src, $class = '', $alt = '' )
	{
		return '<img src="'.$src.'" class="'.$class.'" alt="'.$alt.'" />';
	}

	public static function h1( $html, $class = FALSE, $link = FALSE )
	{
		if ( $html ) echo self::tag( 'h1', array( 'class' => $class ), ( $link ? self::link( $html, $link ) : $html ) );
	}

	public static function h2( $html, $class = FALSE, $link = FALSE )
	{
		if ( $html ) echo self::tag( 'h2', array( 'class' => $class ), ( $link ? self::link( $html, $link ) : $html ) );
	}

	public static function h3( $html, $class = FALSE, $link = FALSE )
	{
		if ( $html ) echo self::tag( 'h3', array( 'class' => $class ), ( $link ? self::link( $html, $link ) : $html ) );
	}

	public static function desc( $html, $block = TRUE, $class = '' )
	{
		if ( $html ) echo $block ? '<p class="description -description '.$class.'">'.$html.'</p>' : '<span class="description -description '.$class.'">'.$html.'</span>';
	}

	public static function wrap( $html, $class = '', $block = TRUE )
	{
		return $block ? '<div class="-wrap '.$class.'">'.$html.'</div>' : '<span class="-wrap '.$class.'">'.$html.'</span>';
	}

	public static function inputHidden( $name, $value = '' )
	{
		echo '<input type="hidden" name="'.self::escapeAttr( $name ).'" value="'.self::escapeAttr( $value ).'" />';
	}

	// @REF: https://gist.github.com/eric1234/5802030
	// useful when you want to pass on a complex data structure via a form
	public static function inputHiddenArray( $array, $prefix = '' )
	{
		if ( (bool) count( array_filter( array_keys( $array ), 'is_string' ) ) ) {

			foreach ( $array as $key => $value ) {
				$name = empty( $prefix ) ? $key : $prefix.'['.$key.']';

				if ( is_array( $value ) )
					self::inputHiddenArray( $value, $name );
				else
					self::inputHidden( $name, $value );
			}

		} else {

			foreach ( $array as $item ) {
				if ( is_array( $item ) )
					self::inputHiddenArray( $item, $prefix.'[]' );
				else
					self::inputHidden( $prefix.'[]', $item );
			}
		}
	}

	public static function joined( $items, $before = '', $after = '', $sep = '|' )
	{
		return count( $items ) ? ( $before.join( $sep, $items ).$after ) : '';
	}

	public static function tag( $tag, $atts = array(), $content = FALSE, $sep = '' )
	{
		$tag = self::sanitizeTag( $tag );

		if ( is_array( $atts ) )
			$html = self::_tag_open( $tag, $atts, $content );
		else
			return '<'.$tag.'>'.$atts.'</'.$tag.'>'.$sep;

		if ( FALSE === $content )
			return $html.$sep;

		if ( is_null( $content ) )
			return $html.'</'.$tag.'>'.$sep;

		return $html.$content.'</'.$tag.'>'.$sep;
	}

	public static function attrClass()
	{
		$classes = array();

		foreach ( func_get_args() as $arg )

			if ( is_array( $arg ) )
				$classes = array_merge( $classes, $arg );

			else if ( $arg )
				$classes = array_merge( $classes, preg_split( '#\s+#', $arg ) );

		return array_unique( array_filter( $classes, 'trim' ) );
	}

	private static function _tag_open( $tag, $atts, $content = TRUE )
	{
		$html = '<'.$tag;

		foreach ( $atts as $key => $att ) {

			$sanitized = FALSE;

			if ( is_array( $att ) ) {

				if ( empty( $att ) )
					continue;

				if ( 'data' == $key ) {

					foreach ( $att as $data_key => $data_val ) {

						if ( is_array( $data_val ) )
							$html.= ' data-'.$data_key.'=\''.wp_json_encode( $data_val ).'\'';

						else if ( FALSE === $data_val )
							continue;

						else
							$html.= ' data-'.$data_key.'="'.esc_attr( $data_val ).'"';
					}

					continue;

				} else if ( 'class' == $key ) {
					$att = implode( ' ', array_unique( array_filter( $att, array( __CLASS__, 'sanitizeClass' ) ) ) );

				} else {
					$att = implode( ' ', array_unique( array_filter( $att, 'trim' ) ) );
				}

				$sanitized = TRUE;
			}

			if ( 'selected' == $key )
				$att = ( $att ? 'selected' : FALSE );

			if ( 'checked' == $key )
				$att = ( $att ? 'checked' : FALSE );

			if ( 'readonly' == $key )
				$att = ( $att ? 'readonly' : FALSE );

			if ( 'disabled' == $key )
				$att = ( $att ? 'disabled' : FALSE );

			if ( 'required' == $key )
				$att = ( $att ? 'required' : FALSE );

			if ( FALSE === $att )
				continue;

			if ( 'class' == $key && ! $sanitized )
				$att = implode( ' ', array_unique( array_filter( explode( ' ', $att ), array( __CLASS__, 'sanitizeClass' ) ) ) );

			else if ( 'class' == $key )
				$att = $att;

			else if ( 'href' == $key && '#' != $att )
				$att = self::escapeURL( $att );

			else if ( 'src' == $key && FALSE === strpos( $att, 'data:image' ) )
				$att = self::escapeURL( $att );

			else
				$att = esc_attr( $att );

			$html.= ' '.$key.'="'.trim( $att ).'"';
		}

		if ( FALSE === $content )
			return $html.' />';

		return $html.'>';
	}

	// like WP core but without filter
	// @SOURCE: `esc_attr()`
	public static function escapeAttr( $text )
	{
		$safe_text = wp_check_invalid_utf8( $text );
		$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );

		return $safe_text;
	}

	public static function escapeURL( $url )
	{
		return esc_url( $url );
	}

	// like WP core but without filter and fallback
	// ANCESTOR: sanitize_html_class()
	public static function sanitizeClass( $class )
	{
		// strip out any % encoded octets
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

		// limit to A-Z,a-z,0-9,_,-
		$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );

		return $sanitized;
	}

	// like WP core but without filter
	// ANCESTOR: tag_escape()
	public static function sanitizeTag( $tag )
	{
		return strtolower( preg_replace('/[^a-zA-Z0-9_:]/', '', $tag ) );
	}

	// @SOURCE: http://www.billerickson.net/code/phone-number-url/
	public static function sanitizePhoneNumber( $number )
	{
		return self::escapeURL( 'tel:'.str_replace( array( '(', ')', '-', '.', '|', ' ' ), '', $number ) );
	}

	// FIXME: DEPRECATED
	public static function getAtts( $string, $expecting = array() )
	{
		self::_dev_dep( 'gThemeHTML::parseAtts()' );

		return self::parseAtts( $string, $expecting );
	}

	public static function parseAtts( $string, $expecting = array() )
	{
		foreach ( $expecting as $attr => $default ) {

			preg_match( "#".$attr."=\"(.*?)\"#s", $string, $matches );

			if ( isset( $matches[1] ) )
				$expecting[$attr] = trim( $matches[1] );
		}

		return $expecting;
	}

	public static function linkStyleSheet( $url, $version = NULL, $media = 'all', $echo = TRUE )
	{
		if ( is_array( $version ) )
			$url = add_query_arg( $version, $url );

		else if ( $version )
			$url = add_query_arg( 'ver', $version, $url );

		$html = self::tag( 'link', array(
			'rel'   => 'stylesheet',
			'href'  => $url,
			'type'  => 'text/css',
			'media' => $media,
		) )."\n";

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public static function headerNav( $uri = '', $active = '', $subs = array(), $prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( empty( $subs ) )
			return;

		$html = '';

		foreach ( $subs as $slug => $page )
			$html.= self::tag( 'a', array(
				'class' => 'nav-tab '.$prefix.$slug.( $slug == $active ? ' nav-tab-active' : '' ),
				'href'  => add_query_arg( 'sub', $slug, $uri ),
			), $page );

		echo self::tag( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	// @REF: https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	// CLASSES: notice-error, notice-warning, notice-success, notice-info, is-dismissible
	public static function notice( $notice, $class = 'notice-success fade', $echo = TRUE )
	{
		$html = sprintf( '<div class="notice %s is-dismissible"><p>%s</p></div>', $class, $notice );

		if ( ! $echo )
			return $html;

		echo $html;
	}

	public static function error( $message, $echo = FALSE )
	{
		return self::notice( $message, 'notice-error fade', $echo );
	}

	public static function success( $message, $echo = FALSE )
	{
		return self::notice( $message, 'notice-success fade', $echo );
	}

	public static function warning( $message, $echo = FALSE )
	{
		return self::notice( $message, 'notice-warning fade', $echo );
	}

	public static function info( $message, $echo = FALSE )
	{
		return self::notice( $message, 'notice-info fade', $echo );
	}

	public static function tableCode( $array, $reverse = FALSE, $caption = FALSE )
	{
		if ( ! $array )
			return;

		if ( $reverse )
			$row = '<tr><td class="-val"><code>%1$s</code></td><td class="-var" valign="top">%2$s</td></tr>';
		else
			$row = '<tr><td class="-var" valign="top">%1$s</td><td class="-val"><code>%2$s</code></td></tr>';

		echo '<table class="base-table-code'.( $reverse ? ' -reverse' : '' ).'">';

		if ( $caption )
			echo '<caption>'.$caption.'</caption>';

		echo '<tbody>';

		foreach ( (array) $array as $key => $val ) {

			if ( is_null( $val ) )
				$val = 'NULL';

			else if ( is_bool( $val ) )
				$val = $val ? 'TRUE' : 'FALSE';

			else if ( is_array( $val ) || is_object( $val ) )
				$val = json_encode( $val );

			else if ( empty( $val ) )
				$val = 'EMPTY';

			else
				$val = nl2br( $val );

			printf( $row, $key, $val );
		}

		echo '</tbody></table>';
	}

	public static function getDashicon( $icon = 'wordpress-alt', $tag = 'span', $title = FALSE )
	{
		return self::tag( $tag, array(
			'title' => $title,
			'class' => array(
				'dashicons',
				'dashicons-'.$icon,
			),
		), NULL );
	}

	public static function dropdown( $list, $atts = array() )
	{
		$args = self::atts( array(
			'id'         => '',
			'name'       => '',
			'none_title' => NULL,
			'none_value' => 0,
			'class'      => FALSE,
			'selected'   => 0,
			'disabled'   => FALSE,
			'dir'        => FALSE,
			'prop'       => FALSE,
			'value'      => FALSE,
			'exclude'    => array(),
		), $atts );

		$html = '';

		if ( FALSE === $list ) // alow hiding
			return $html;

		if ( ! is_null( $args['none_title'] ) )
			$html.= self::tag( 'option', array(
				'value'    => $args['none_value'],
				'selected' => $args['selected'] == $args['none_value'],
			), $args['none_title'] );

		foreach ( $list as $offset => $value ) {

			if ( $args['value'] )
				$key = is_object( $value ) ? $value->{$args['value']} : $value[$args['value']];

			else
				$key = $offset;

			if ( in_array( $key, (array) $args['exclude'] ) )
				continue;

			if ( $args['prop'] )
				$title = is_object( $value ) ? $value->{$args['prop']} : $value[$args['prop']];

			else
				$title = $value;

			$html.= self::tag( 'option', array(
				'value'    => $key,
				'selected' => $args['selected'] == $key,
			), $title );
		}

		return self::tag( 'select', array(
			'name'     => $args['name'],
			'id'       => $args['id'],
			'class'    => $args['class'],
			'disabled' => $args['disabled'],
			'dir'      => $args['dir'],
		), $html );
	}
}
