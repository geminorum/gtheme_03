<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gTheme_Walker_Page extends Walker_Page
{

	public function start_el( &$output, $page, $depth = 0, $args = [], $current_page = 0 )
	{
		$css_class = [ 'list-group-item', 'page-item-'.$page->ID ];

		if ( isset( $args['pages_with_children'][$page->ID] ) )
			$css_class[] = 'page_item_has_children';

		if ( ! empty( $current_page ) ) {

			$_current_page = get_post( $current_page );

			if ( $_current_page && in_array( $page->ID, $_current_page->ancestors ) )
				$css_class[] = 'current_page_ancestor';

			if ( $page->ID == $current_page ) {
				$css_class[] = 'active';
				$css_class[] = 'current_page_item';

			} else if ( $_current_page && $page->ID == $_current_page->post_parent ) {
				$css_class[] = 'current_page_parent';
			}

		} else if ( $page->ID == get_option('page_for_posts') ) {
			$css_class[] = 'current_page_parent';
		}

		$css_classes = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

		if ( '' === $page->post_title )
			$page->post_title = sprintf( __( '#%d (no title)' ), $page->ID );

		if ( isset( $args['excerpt'] ) && $args['excerpt'] && ! empty( $page->post_excerpt ) ) {

			$output.= sprintf(
				'<a class="%s" href="%s"><h4 class="list-group-item-heading">%s</h4><p class="list-group-item-text">%s</p></a>',
				$css_classes,
				get_permalink( $page->ID ),
				gThemeUtilities::prepTitle( $page->post_title, $page->ID ),
				gThemeUtilities::prepDescription( $page->post_excerpt, FALSE, FALSE )
			);

		} else {

			$output.= sprintf(
				'<a class="%s" href="%s">%s</a>',
				$css_classes,
				get_permalink( $page->ID ),
				gThemeUtilities::prepTitle( $page->post_title, $page->ID )
			);
		}

		/*
		if ( ! empty( $args['show_date'] ) ) {
			if ( 'modified' == $args['show_date'] ) {
				$time = $page->post_modified;
			} else {
				$time = $page->post_date;
			}

			$date_format = empty( $args['date_format'] ) ? '' : $args['date_format'];
			$output.= " ".mysql2date( $date_format, $time );
		}
		*/
	}

	public function end_el( &$output, $page, $depth = 0, $args = [] )
	{
		$output.= "\n";
	}
}
