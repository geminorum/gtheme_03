<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

gThemeWrap::wrapperOpen( 'navbar', FALSE, '-nav', 'bg-primary-subtle' );

	gThemeBootstrap::navbarOpen();
	gThemeBootstrap::navbarNav( 'primary', 'navbar', 'yamm text-primary-emphasis' );
	gThemeBootstrap::navbarWooCommerce( 'mx-2' );
	gThemeBootstrap::navbarForm();
	gThemeBootstrap::navbarClose();

gThemeWrap::wrapperClose( 'navbar', 2 );
