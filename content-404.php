<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

gThemeContent::wrapOpen( '404' );

	gThemeContent::header( array(
		'context' => '404',
		'title'   => __( 'Oops! That page can&rsquo;t be found.', GTHEME_TEXTDOMAIN ),
		'link'    => FALSE,
		'meta'    => FALSE,
		'anchor'  => FALSE,
	) );

	echo '<div class="entry-content entry-404"><p>';
		_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', GTHEME_TEXTDOMAIN );
		echo '</p>';

		gThemeSearch::form( '404' );

		do_action( 'gtheme_content_404' );
		/*
			EXAMPLES :
				the_widget( 'WP_Widget_Recent_Posts' );
				the_widget( 'WP_Widget_Tag_Cloud' );
				wp_list_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'show_count' => 1, 'title_li' => '', 'number' => 10 ) );
		*/

		// http://themeshaper.com/2012/11/02/the-wordpress-theme-single-post-post-attachment-404-templates/

	echo '</div>';

gThemeContent::wrapClose();
