<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

/*
* https://gist.github.com/markjaquith/2653957
* Usage:

	$frag = new gThemeFragmentCache( 'unique-key', 3600, ! wp_using_ext_object_cache(), false ); // Second param is TTL

	if ( ! $frag->output() ) { // testing for a return of false

		functions_that_do_stuff_live();
		these_should_echo();

		// IMPORTANT
		$frag->store(); // YOU CANNOT FORGET THIS. If you do, the site will break.

	}
*/

class gThemeFragmentCache extends gThemeBaseCore
{
	protected $key;
	protected $ttl;

	public function __construct( $key, $ttl = GTHEME_CACHETTL, $transient = NULL, $site = FALSE )
	{
		$this->key       = $key;
		$this->ttl       = $ttl;
		$this->transient = is_null( $transient ) ? ! wp_using_ext_object_cache() : $transient;
		$this->site      = is_multisite() && $site;
		$this->ob        = FALSE;

		if ( gThemeWordPress::isFlush() )
			$this->__flush();
	}

	protected function key()
	{
		return GTHEME_FRAGMENTCACHE.'_'.$this->key;
	}

	protected function __flush()
	{
		if ( $this->transient ) {

			if ( $this->site )
				delete_site_transient( $this->key() );

			else
				delete_transient( $this->key() );

		} else {
			wp_cache_delete( $this->key, GTHEME_FRAGMENTCACHE );
		}
	}

	public function output()
	{
		if ( gThemeWordPress::isDev() )
			return FALSE;

		if ( gThemeWordPress::isFlush() ) {
			$output = '';

		} else {

			if ( $this->transient )
				$output = $this->site ? get_site_transient( $this->key() ) : get_transient( $this->key() );

			else
				$output = wp_cache_get( $this->key, GTHEME_FRAGMENTCACHE );

		}

		if ( ! empty( $output ) ) {
			echo $output;
			return TRUE;
		} else {
			ob_start();
			$this->ob = TRUE;
			return FALSE;
		}
	}

	public function store( $notice = 'manage_network' )
	{
		if ( gThemeWordPress::isDev() )
			return TRUE;

		$output = ob_get_flush();

		if ( $this->transient ) {

			if ( $this->site )
				set_site_transient( $this->key(), $output, $this->ttl );

			else
				set_transient( $this->key(), $output, $this->ttl );

		} else {
			wp_cache_add( $this->key, $output, GTHEME_FRAGMENTCACHE, $this->ttl );
		}

		if ( $notice && current_user_can( $notice ) && ! gThemeWordPress::isDev() )
			gThemeHTML::notice( __( 'Refreshed!', GTHEME_TEXTDOMAIN ) );

		return TRUE;
	}

	public function discard()
	{
		if ( gThemeWordPress::isDev() )
			return FALSE;

		if ( $this->ob )
			ob_get_flush();

		return FALSE;
	}

	// DEPRECATED
	public static function flush( $key, $transient = NULL, $site = FALSE )
	{
		if ( is_null( $transient ) )
			$transient = ! wp_using_ext_object_cache();

		if ( $site )
			delete_site_transient( GTHEME_FRAGMENTCACHE.'_'.$key );
		else
			delete_transient( GTHEME_FRAGMENTCACHE.'_'.$key );
	}
}
