<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( ! class_exists( 'gThemeChildCore' ) ) { class gThemeChildCore
{
	public function __construct()
	{
		$this->setup_actions();
	}

	public function setup_actions() {}
} }
