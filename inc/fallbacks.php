<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

// gMeta & gEditorialMeta
if ( ! function_exists( 'gmeta' ) ) : function gmeta(){} endif; // DEPRECATED: use gThemeEditorial::meta()
if ( ! function_exists( 'gmeta_label' ) ) : function gmeta_label() {} endif; // DEPRECATED: use gThemeEditorial::label()
if ( ! function_exists( 'gmeta_lead' ) ) : function gmeta_lead() {} endif; // DEPRECATED: use gThemeEditorial::lead()
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
// gPeople
if ( ! function_exists( 'gpeople_byline' ) ) : function gpeople_byline() {} endif;
