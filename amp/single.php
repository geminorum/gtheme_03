<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

$this->load_parts( [ 'html-start' ] );
$this->load_parts( [ 'header' ] );

echo '<article class="amp-wp-article">';

	gThemeContent::header( [
		'title'  => $this->get( 'post_title' ),
		'prefix' => 'amp',
		'link'   => FALSE,
	] );

	echo '<div class="amp-wp-article-header">';
		$this->load_parts( apply_filters( 'amp_post_article_header_meta', [ 'meta-author', 'meta-time' ] ) );
	echo '</div>';

	$this->load_parts( [ 'featured-image' ] );

	echo '<div class="amp-wp-article-content">';

		gThemeEditorial::lead( [
			'before' => '<div class="-lead">',
			'after'  => '</div>',
		] );

		echo $this->get( 'post_amp_content' );
	echo '</div>';

	echo '<footer class="amp-wp-article-footer">';
		$this->load_parts( apply_filters( 'amp_post_article_footer_meta', [ 'meta-taxonomy', 'meta-comments-link' ] ) );
	echo '</footer>';

echo '</article>';

$this->load_parts( [ 'footer' ] );
$this->load_parts( [ 'html-end' ] );
