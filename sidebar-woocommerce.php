<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

if ( is_singular( 'product' ) )
	gThemeSideBar::sidebar( 'side-product', '<div class="%s wrap-side sidebar-woocommerce sidebar-product">', '</div>' );
else
	gThemeSideBar::sidebar( 'side-shop', '<div class="%s wrap-side sidebar-woocommerce sidebar-shop">', '</div>' );
