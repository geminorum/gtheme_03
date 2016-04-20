<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeBaseCore
{

	/**
	* @source https://gist.github.com/boonebgorges/5510970
	*
	* recursive argument parsing
	*
	* Values from $a override those from $b; keys in $b that don't exist
	* in $a are passed through.
	*
	* This is different from array_merge_recursive(), both because of the
	* order of preference ($a overrides $b) and because of the fact that
	* array_merge_recursive() combines arrays deep in the tree, rather
	* than overwriting the b array with the a array.
	*/
	public static function recursiveParseArgs( &$a, $b )
	{
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;

		foreach ( $a as $k => &$v )
			if ( is_array( $v ) && isset( $r[$k] ) )
				$r[$k] = self::recursiveParseArgs( $v, $r[$k] );
			else
				$r[$k] = $v;

		return $r;
	}
}
