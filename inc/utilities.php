<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeUtilities extends gThemeModuleCore 
{
	public static function dump( $var, $htmlSafe = true ) 
	{
		$result = var_export( $var, true ); 
		echo '<pre dir="ltr" style="text-align:left;direction:ltr;">'.( $htmlSafe ? htmlspecialchars( $result ) : $result).'</pre>'; 
	}
	
	// http://davidwalsh.name/word-wrap-mootools-php
	public static function word_wrap( $text, $min = 2 ) 
	{
		$return = $text;
		$arr = explode( ' ', $text );
		
		if ( count( $arr ) >= $min ) {
			$arr[count( $arr ) - 2] .= '&nbsp;'.$arr[count( $arr ) - 1];
			array_pop( $arr );
			$return = implode( ' ', $arr );
		}
		
		return $return;
	} 
	
	// http://bavotasan.com/2012/trim-characters-using-php/
	public static function trim_characters( $text, $length = 45, $append = '&hellip;' ) 
	{
	
		$length = (int) $length;
		$text = trim( strip_tags( $text ) );
		
		if ( strlen( $text ) > $length ) {
			$text = substr( $text, 0, $length + 1 );
			$words = preg_split( "/[\s]|&nbsp;/", $text, -1, PREG_SPLIT_NO_EMPTY );
			preg_match( "/[\s]|&nbsp;/", $text, $lastchar, 0, $length );
			if ( empty( $lastchar ) )
				array_pop( $words );
			
			$text = implode( ' ', $words ) . $append;
		}
		
		return $text;
	}
	
	public static function get_uri_length( $url, $default = '' ) 
	{
		if ( $headers = wp_get_http_headers( $url ) )
			return (int) $headers['content-length'];
			
		return $default;
	}
	
	// debug on production env
	public static function is_debug() 
	{
		if ( WP_DEBUG && WP_DEBUG_DISPLAY && ! self::is_dev() )
			return true;
		
		return false;
	}
	
	// debug on developmnet env
	public static function is_dev()
	{
		if ( defined( 'GTHEME_DEV_ENVIRONMENT' ) && constant( 'GTHEME_DEV_ENVIRONMENT' ) ) 
			return true;
			
		if ( defined( 'WP_STAGE' ) && 'development' == constant( 'WP_STAGE' ) ) 
			return true;
			
		// TODO : check stage production and debug constant then true
		
		return false;
	}
	
	public static function is_print()
	{
		return ( isset( $_GET['print'] ) && $_GET['print'] == 'print' ) ? true : false;
	}
	
	public static function is_rtl() 
	{ 
		return gtheme_get_info( 'rtl', is_rtl() ); 
	}
	
	public static function home() 
	{ 
		return gtheme_get_info( 'home_url_override', esc_url( home_url( '/' ) ) ); 
	}
	
		
	public static function sanitize_sep( $sep = 'def', $context = 'default_sep', $def = ' ' )
	{
		if ( 'def' == $sep )
			return gtheme_get_info( $context, $def );
			
		if ( false === $sep )
			return ' ';
			
		return $sep;
	}
	
	// http://teleogistic.net/2013/05/a-recursive-sorta-version-of-wp_parse_args/
	// https://gist.github.com/boonebgorges/5510970
	/**
	* Recursive argument parsing
	*
	* This acts like a multi-dimensional version of wp_parse_args() (minus
	* the querystring parsing - you must pass arrays).
	*
	* Values from $a override those from $b; keys in $b that don't exist
	* in $a are passed through.
	*
	* This is different from array_merge_recursive(), both because of the
	* order of preference ($a overrides $b) and because of the fact that
	* array_merge_recursive() combines arrays deep in the tree, rather
	* than overwriting the b array with the a array.
	*
	* The implementation of this function is specific to the needs of
	* BP_Group_Extension, where we know that arrays will always be
	* associative, and that an argument under a given key in one array
	* will be matched by a value of identical depth in the other one. The
	* function is NOT designed for general use, and will probably result
	* in unexpected results when used with data in the wild. See, eg,
	* http://core.trac.wordpress.org/ticket/19888
	*
	* @since BuddyPress (1.8)
	* @arg array $a
	* @arg array $b
	* @return array
	*/
	public static function parse_args_r( &$a, $b ) 
	{
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;
		 
		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = self::parse_args_r( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}
		 
		return $r;
	}
	
	public static function update_count_callback( $terms, $taxonomy )
	{
		global $wpdb;
		foreach ( (array) $terms as $term ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	}
	
	private static function _tag_open( $tag, $atts, $content = true )
	{
		$html = '<'.$tag;
		foreach( $atts as $key => $att ) {
			
			if ( is_array( $att ) && count( $att ) )
				$att = implode( ' ', array_unique( $att ) );
			
			if ( 'selected' == $key )	
				$att = ( $att ? 'selected' : false );
				
			if ( 'checked' == $key )	
				$att = ( $att ? 'checked' : false );
				
			if ( 'readonly' == $key )	
				$att = ( $att ? 'readonly' : false );
				
			if ( 'disabled' == $key )	
				$att = ( $att ? 'disabled' : false );
			
			if ( false === $att )
				continue;
			
			if ( 'class' == $key )
				//$att = sanitize_html_class( $att, false );
				$att = $att;
			else if ( 'href' == $key || 'src' == $key )
				$att = esc_url( $att );
			//else if ( 'input' == $tag && 'value' == $key )
				//$att = $att;
			else 
				$att = esc_attr( $att );
			
			$html .= ' '.$key.'="'.trim( $att ).'"';
		}
		
		if ( false === $content )
			return $html.' />';
			
		return $html.'>';
	}
	
	public static function html( $tag, $atts = array(), $content = false, $sep = '' ) 
	{
		$html = self::_tag_open( $tag, $atts, $content );
		
		if ( false === $content )
			return $html.$sep;
			
		if ( is_null( $content ) )
			return $html.'</'.$tag.'>'.$sep;
			
		return $html.$content.'</'.$tag.'>'.$sep;
	}
	
	public static function link_stylesheet( $url, $attr = 'media="all"' )
	{
		echo "\t".'<link rel="stylesheet" href="'.esc_url( $url ).'" type="text/css" '.$attr.' />'."\n"; 
	}
	
	// http://stackoverflow.com/a/9241873
	public static function json_merge( $first, $second )
	{
		return json_encode( 
			array_merge_recursive( 
				json_decode( $first, true ),
				json_decode( $second, true )
			) 
		);
	}
	
	public static function notice( $notice, $class = 'updated fade', $echo = true ) 
	{
		$html = sprintf( '<div id="message" class="%s"><p>%s</p></div>', $class, $notice );
		if ( ! $echo )
			return $html;
		echo $html;
	}	
	
	public static function headerNav( $settings_uri = '', $active = '', $sub_pages = array(), $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( ! count( $sub_pages ) )
			return;
		
		$html = '';
		
		foreach ( $sub_pages as $page_slug => $sub_page )
			$html .= self::html( 'a', array(
				'class' => 'nav-tab '.$class_prefix.$page_slug.( $page_slug == $active ? ' nav-tab-active' : '' ),
				'href' => add_query_arg( 'sub', $page_slug, $settings_uri ),
			), esc_html( $sub_page ) );
		
		echo self::html( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}
	
}