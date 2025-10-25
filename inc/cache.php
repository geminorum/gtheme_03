<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

// @REF: https://gist.github.com/markjaquith/2653957
class gThemeFragmentCache extends gThemeBaseCore
{
	protected $key;
	protected $ttl;

	protected $transient;
	protected $network;
	protected $buffer;

	public function __construct( $key, $ttl = GTHEME_CACHETTL, $transient = NULL, $network = FALSE )
	{
		$this->key = $key;
		$this->ttl = $ttl;

		$this->transient = is_null( $transient ) ? ! wp_using_ext_object_cache() : $transient;
		$this->network   = is_multisite() && $network;
		$this->buffer    = FALSE;

		if ( gThemeWordPress::isFlush() )
			$this->__flush();
	}

	protected function key()
	{
		return GTHEME_FRAGMENTCACHE.'_'.$this->key;
	}

	protected function __skip()
	{
		if ( gThemeWordPress::isDev() )
			return TRUE;

		if ( defined( 'WP_CACHE' ) && WP_CACHE )
			return TRUE;

		return FALSE;
	}

	protected function __flush()
	{
		if ( $this->transient ) {

			if ( $this->network )
				delete_site_transient( $this->key() );

			else
				delete_transient( $this->key() );

		} else {

			wp_cache_delete( $this->key, GTHEME_FRAGMENTCACHE );
		}
	}

	public function output()
	{
		if ( $this->__skip() )
			return FALSE;

		if ( gThemeWordPress::isFlush() ) {

			$output = '';

		} else {

			if ( $this->transient )
				$output = $this->network
					? get_site_transient( $this->key() )
					: get_transient( $this->key() );

			else
				$output = wp_cache_get( $this->key, GTHEME_FRAGMENTCACHE );
		}

		if ( ! empty( $output ) ) {

			echo $output;
			return TRUE;

		} else {

			ob_start();
			$this->buffer = TRUE;
			return FALSE;
		}
	}

	public function store()
	{
		if ( $this->__skip() )
			return TRUE;

		$output = ob_get_flush();

		if ( $this->transient ) {

			if ( $this->network )
				set_site_transient( $this->key(), $output, $this->ttl );

			else
				set_transient( $this->key(), $output, $this->ttl );

		} else {

			wp_cache_add( $this->key, $output, GTHEME_FRAGMENTCACHE, $this->ttl );
		}

		return TRUE;
	}

	public function discard( $closing = '' )
	{
		if ( $this->__skip() ) {
			echo $closing;
			return FALSE;
		}

		if ( $this->buffer )
			ob_get_flush();

		echo $closing;
		return FALSE;
	}
}
