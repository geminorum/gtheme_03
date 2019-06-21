<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTaxonomy extends gThemeBaseCore
{

	// @REF: https://developer.wordpress.org/?p=22286
	public static function listTerms( $taxonomy, $fields = NULL, $extra = array() )
	{
		$query = new \WP_Term_Query( array_merge( array(
			'taxonomy'   => (array) $taxonomy,
			'order'      => 'ASC',
			'orderby'    => 'meta_value_num', // 'name',
			'meta_query' => [
				// @REF: https://core.trac.wordpress.org/ticket/34996
				'relation' => 'OR',
				[
					'key'     => 'order',
					'compare' => 'NOT EXISTS'
				],
				[
					'key'     => 'order',
					'value'   => 0,
					'compare' => '>=',
				],
			],
			'fields'     => is_null( $fields ) ? 'id=>name' : $fields,
			'hide_empty' => FALSE,
		), $extra ) );

		if ( empty( $query->terms ) )
			return array();

		return $query->terms;
	}
}
