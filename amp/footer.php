<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

echo '<footer class="amp-wp-footer"><div>';
	// echo '<h2>'.esc_html( wptexturize( $this->get( 'blog_name' ) ) ).'</h2>'; // copyright notice usually have the name of the site
	gThemeTemplate::copyrightAMP();
	echo '<a href="#top" class="back-to-top">'.esc_html__( 'Back to top', 'gtheme' ).'</a>';
echo '</div></footer>';
