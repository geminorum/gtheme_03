<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

/*
* https://gist.github.com/markjaquith/2653957
* Usage:
	$frag = new gThemeFragmentCache( 'unique-key', 3600, gtheme_wp_cache_disabled(), false ); // Second param is TTL
	if ( !$frag->output() ) { // NOTE, testing for a return of false
	functions_that_do_stuff_live();
	these_should_echo();
	// IMPORTANT
	$frag->store();
	// YOU CANNOT FORGET THIS. If you do, the site will break.
	}
*/

class gThemeFragmentCache
{

	var $key;
	var $ttl;

	public function __construct( $key, $ttl = GTHEME_CACHETTL, $transient = NULL, $site = FALSE )
	{
		$this->key       = $key;
		$this->ttl       = $ttl;
		$this->transient = is_null( $transient ) ? gtheme_wp_cache_disabled() : $transient;
		$this->site      = is_multisite() && $site;
		
		if ( GTHEME_FLUSH )
			$this->__flush();
	}
	
	protected function __flush()
	{
		if ( $this->transient ) {
			if ( $this->site ) {
				delete_site_transient( GTHEME_FRAGMENTCACHE.'_'.$this->key );
			} else {
				delete_transient( GTHEME_FRAGMENTCACHE.'_'.$this->key );
			}
		} else {
			wp_cache_delete( $this->key, GTHEME_FRAGMENTCACHE );
		}
	}

	public function output()
	{
		if ( gThemeUtilities::isDev() )
			return FALSE;

		if ( GTHEME_FLUSH ) {
			$output = '';
		} else {			
			if ( $this->transient ) {
				if ( $this->site )
					$output = get_site_transient( GTHEME_FRAGMENTCACHE.'_'.$this->key );
				else
					$output = get_transient( GTHEME_FRAGMENTCACHE.'_'.$this->key );
			} else {
				$output = wp_cache_get( $this->key, GTHEME_FRAGMENTCACHE );
			}
		}

		if ( ! empty( $output ) ) {
			echo $output;
			return TRUE;
		} else {
			ob_start();
			return FALSE;
		}
	}

	public function store( $notice = 'manage_network' )
	{
		if ( gThemeUtilities::isDev() )
			return;

		$output = ob_get_flush();

		if ( $this->transient ) {
			if ( $this->site ) {
				set_site_transient( GTHEME_FRAGMENTCACHE.'_'.$this->key, $output, $this->ttl );
			} else {
				set_transient( GTHEME_FRAGMENTCACHE.'_'.$this->key, $output, $this->ttl );
			}
		} else {
			wp_cache_add( $this->key, $output, GTHEME_FRAGMENTCACHE, $this->ttl );
		}

		if ( $notice && current_user_can( $notice ) && ! gThemeUtilities::isDev() )
			gThemeUtilities::notice( __( 'Refreshed!', GTHEME_TEXTDOMAIN ) );
	}

	public function discard()
	{
		if ( gThemeUtilities::isDev() )
			return;

		$output = ob_get_flush();
	}

	// DEPRECATED
	public static function flush( $key, $transient = null, $site = false )
	{
		if ( is_null( $transient ) )
			$transient = gtheme_wp_cache_disabled();

		if ( $site )
			delete_site_transient( GTHEME_FRAGMENTCACHE.'_'.$key );
		else
			delete_transient( GTHEME_FRAGMENTCACHE.'_'.$key );
	}
}

function gtheme_wp_cache_disabled() {
	global $_wp_using_ext_object_cache;

	if ( $_wp_using_ext_object_cache )
		return FALSE;

	return TRUE;
}
