<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeHTML extends gThemeBaseCore
{

	public static function rtl()
	{
		return function_exists( 'is_rtl' ) ? is_rtl() : FALSE;
	}

	public static function link( $html, $link = '#', $target_blank = FALSE )
	{
		if ( is_null( $html ) ) $html = $link;
		return self::tag( 'a', array( 'href' => $link, 'class' => '-link', 'target' => ( $target_blank ? '_blank' : FALSE ) ), $html );
	}

	public static function mailto( $email, $title = NULL, $wrap = FALSE )
	{
		$title = $title ? $title : self::wrapLTR( trim( $email ) );
		$link  = '<a class="-mailto" href="mailto:'.trim( $email ).'">'.$title.'</a>';
		return $wrap ? self::tag( $wrap, $link ) : $link;
	}

	public static function tel( $number, $title = FALSE, $content = NULL )
	{
		if ( is_null( $content ) )
			$content = apply_filters( 'number_format_i18n', $number );

		return '<a class="-tel" href="'.self::sanitizePhoneNumber( $number )
				.'"'.( $title ? ' data-toggle="tooltip" title="'.self::escape( $title ).'"' : '' )
				.' data-tel-number="'.self::escape( $number ).'">'
				.self::wrapLTR( $content ).'</a>';
	}

	public static function scroll( $html, $to, $title = '' )
	{
		return '<a class="scroll" title="'.$title.'" href="#'.$to.'">'.$html.'</a>';
	}

	// @REF: https://web.dev/native-lazy-loading/
	// @SEE: https://www.smashingmagazine.com/2021/04/humble-img-element-core-web-vitals/
	public static function img( $src, $class = '', $alt = '' )
	{
		return $src ? '<img src="'.$src.'" class="'.self::prepClass( $class ).'" alt="'.$alt.'" decoding="async" loading="lazy" />' : '';
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

	public static function desc( $string, $block = TRUE, $class = '', $nl2br = TRUE )
	{
		if ( is_array( $string ) ) {

			$assoc = gThemeArraay::isAssoc( $string );

			foreach ( $string as $desc_class => $desc_html )
				self::desc( $desc_html, $block, $assoc ? $desc_class : $class, $nl2br );

			return;
		}

		if ( ! $string = trim( $string ) )
			return;

		$tag = $block ? 'p' : 'span';

		if ( gThemeText::start( $string, [ '<ul', '<ol', '<h3', '<h4', '<h5', '<h6' ] ) )
			$tag = 'div';

		echo '<'.$tag.' class="'.self::prepClass( 'description', '-description', $class ).'">'
			// .gThemeText::wordWrap( $nl2br ? nl2br( $string ) : $string ) // FIXME: messes with html attrs
			.( $nl2br ? nl2br( $string ) : $string )
		.'</'.$tag.'>';
	}

	public static function label( $input, $for = FALSE, $wrap = 'p' )
	{
		$html = self::tag( 'label', [ 'for' => $for ], $input );
		echo $wrap ? self::tag( $wrap, $html ) : $html;
	}

	public static function wrap( $html, $class = '', $block = TRUE )
	{
		if ( ! $html )
			return '';

		return $block
			? '<div class="'.self::prepClass( '-wrap', $class ).'">'.$html.'</div>'
			: '<span class="'.self::prepClass( '-wrap', $class ).'">'.$html.'</span>';
	}

	public static function wrapLTR( $content )
	{
		return '&#8206;'.$content.'&#8207;';
	}

	public static function inputHidden( $name, $value = '' )
	{
		echo '<input type="hidden" name="'.self::escape( $name ).'" value="'.self::escape( $value ).'" />';
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
		return count( $items ) ? ( $before.implode( $sep, $items ).$after ) : '';
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

	public static function prepClass()
	{
		$classes = func_get_args();

		if ( TRUE === $classes[0] )
			return '';

		return implode( ' ', array_unique( array_filter( call_user_func_array( array( __CLASS__, 'attrClass' ), $classes ), array( __CLASS__, 'sanitizeClass' ) ) ) );
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
	// @ref: `esc_html()`, `esc_attr()`
	public static function escape( $text )
	{
		$safe_text = wp_check_invalid_utf8( $text );
		$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );

		return $safe_text;
	}

	// FIXME: DEPRECATED
	public static function escapeAttr( $text )
	{
		return self::escape( $text );
	}

	public static function escapeURL( $url )
	{
		return esc_url( $url );
	}

	// like WP core but without filter and fallback
	// @source `sanitize_html_class()`
	public static function sanitizeClass( $class )
	{
		// strip out any % encoded octets
		$sanitized = preg_replace( '/%[a-fA-F0-9][a-fA-F0-9]/', '', $class );

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

	public static function linkStyleSheet( $url, $version = NULL, $media = 'all', $verbose = TRUE )
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

		if ( ! $verbose )
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
	public static function notice( $notice, $class = 'notice-success fade', $verbose = TRUE )
	{
		$html = sprintf( '<div class="notice %s is-dismissible"><p>%s</p></div>', $class, $notice );

		if ( ! $verbose )
			return $html;

		echo $html;
	}

	public static function error( $message, $verbose = FALSE )
	{
		return self::notice( $message, 'notice-error fade', $verbose );
	}

	public static function success( $message, $verbose = FALSE )
	{
		return self::notice( $message, 'notice-success fade', $verbose );
	}

	public static function warning( $message, $verbose = FALSE )
	{
		return self::notice( $message, 'notice-warning fade', $verbose );
	}

	public static function info( $message, $verbose = FALSE )
	{
		return self::notice( $message, 'notice-info fade', $verbose );
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
			'id'         => FALSE,
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
			'data'       => array(),
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
			'data'     => $args['data'],
		), $html );
	}

	public static function renderList( $items, $keys = FALSE, $list = 'ul' )
	{
		return $items ? self::tag( $list, '<li>'.implode( '</li><li>', $keys ? array_keys( $items ) : $items ).'</li>' ) : '';
	}
}
