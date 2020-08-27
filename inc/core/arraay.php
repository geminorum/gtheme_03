<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeArraay extends gThemeBaseCore
{

	public static function reKey( $list, $key )
	{
		if ( ! empty( $list ) ) {
			$ids  = wp_list_pluck( $list, $key );
			$list = array_combine( $ids, $list );
		}

		return $list;
	}

	// is associative or sequential?
	// @REF: https://stackoverflow.com/a/173479
	public static function isAssoc( $array )
	{
		if ( $array === array() )
			return FALSE;

		return array_keys( $array ) !== range( 0, count( $array ) - 1 );
	}
}
