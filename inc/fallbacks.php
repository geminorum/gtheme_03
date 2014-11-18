<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// gMeta & gEditorialMeta
if ( ! function_exists( 'gmeta' ) ) : function gmeta(){} endif;
if ( ! function_exists( 'gmeta_label' ) ) : function gmeta_label() {} endif;
if ( ! function_exists( 'gmeta_lead' ) ) : function gmeta_lead() {} endif;
if ( ! function_exists( 'gmeta_author' ) ) : 
	function gmeta_author( $b = '', $a = '', $f = false, $args = array() ) {
		$author = get_the_author();
		if ( empty( $author ) )
			return ( isset( $args['def'] ) ? $args['def'] : false );
		$html = $b.( $f ? $f( $author ) : $author ).$a;
		if ( isset( $args['echo'] ) && ! $args['echo'] )
			return $html;
		echo $html;
	}
endif;
