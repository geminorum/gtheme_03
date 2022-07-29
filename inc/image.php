<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeImage extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'core_post_thumbnails'   => TRUE, // WordPress core thumbnail for posts
			'amp_post_thumbnails'    => defined( 'AMP__VERSION' ), // filters amp featured image
			'image_size_tags'        => TRUE, // registers theme's image sizes
			'image_attachment_tags'  => FALSE, // displays ui for theme's image sizes
			'image_attachment_terms' => FALSE, // image for terms on admin media editor
			'responsive_class'       => FALSE, // extracts and appends css class into content images
			'media_object_sizes'     => TRUE, // tells gnetwork to not generate default image sizes
			'no_filter_content_tags' => TRUE, // removes core filter for srcset/sizes/loading
		], $args ) );

		if ( $core_post_thumbnails )
			add_theme_support( 'post-thumbnails', gThemeOptions::info( 'core_post_thumbnails', [ 'post' ] ) );

		if ( $amp_post_thumbnails && class_exists( 'AMP_Content_Sanitizer' ) )
			add_filter( 'amp_post_template_data', [ $this, 'amp_post_template_data' ], 99, 2 );

		if ( $image_size_tags ) {
			add_action( 'init', [ $this, 'init' ] );
			add_filter( 'intermediate_image_sizes_advanced', [ $this, 'intermediate_image_sizes_advanced' ], 8, 2 );
			add_filter( 'image_size_names_choose', [ $this, 'image_size_names_choose' ] );
		}

		add_filter( 'get_image_tag_class', [ $this, 'get_image_tag_class' ], 10, 4 );
		// add_filter( 'wp_get_attachment_image_attributes', [ $this, 'wp_get_attachment_image_attributes' ], 10, 2 ); // FIXME: we can remove this

		if ( $media_object_sizes )
			add_filter( 'gnetwork_media_object_sizes', '__return_true' );

		if ( $responsive_class )
			add_filter( 'the_content', [ $this, 'the_content_responsive_class' ], 100 );

		add_filter( 'get_image_tag', [ $this, 'get_image_tag' ], 5, 6 );
		// add_filter( 'post_thumbnail_html', [ $this, 'strip_width_height' ], 10 ); // FIXME: we can remove this

		add_filter( 'pre_option_image_default_link_type', [ $this, 'pre_option_image_default_link_type' ], 10 );
		add_filter( 'pre_option_image_default_align', [ $this, 'pre_option_image_default_align' ], 10 );
		add_filter( 'pre_option_image_default_size', [ $this, 'pre_option_image_default_size' ], 10 );
		add_filter( 'jpeg_quality', [ $this, 'jpeg_quality' ], 10, 2 );
		add_filter( 'wp_editor_set_quality', [ $this, 'wp_editor_set_quality' ], 10, 2 );

		if ( $image_attachment_tags ) {

			add_filter( 'attachment_fields_to_edit', [ $this, 'tags_attachment_fields_to_edit' ], 10, 2 );
			add_filter( 'attachment_fields_to_save', [ $this, 'tags_attachment_fields_to_save' ], 10, 2 );

		} else if ( $core_post_thumbnails && gThemeOptions::info( 'image_convert_tags_into_thumbnail', FALSE ) ) {

			add_action( 'post_updated', [ $this, 'post_updated_convert_image_tags' ], 9, 3 );
		}

		if ( $image_attachment_terms ) {
			add_filter( 'attachment_fields_to_edit', [ $this, 'terms_attachment_fields_to_edit' ], 9, 2 );
			add_filter( 'attachment_fields_to_save', [ $this, 'terms_attachment_fields_to_save' ], 9, 2 );
		}

		if ( $no_filter_content_tags ) {
			remove_filter( 'the_content', 'wp_make_content_images_responsive' );
			remove_filter( 'the_content', 'wp_filter_content_tags' );
			remove_filter( 'the_excerpt', 'wp_filter_content_tags' );
			remove_filter( 'widget_text_content', 'wp_filter_content_tags' );
			remove_filter( 'widget_block_content', 'wp_filter_content_tags' );
		}

		if ( ! is_admin() )
			return;

		// also for fallbacks
		add_filter( 'geditorial_tweaks_column_thumb', [ $this, 'tweaks_column_thumb' ], 12, 3 );
	}

	public function init()
	{
		foreach ( gThemeOptions::info( 'images', [] ) as $name => $size )
			self::registerImageSize( $name, $size );
	}

	public function tweaks_column_thumb( $html, $post_id, $size )
	{
		// only if it's empty
		if ( ! empty( $html ) )
			return $html;

		if ( ! $post = get_post( $post_id ) )
			return $html;

		if ( 'post' != $post->post_type )
			return $html;

		$size = gThemeOptions::info( 'thumbnail_image_size', 'single' );

		if ( ! $post_thumbnail_id = self::getThumbnailID( $size, $post_id ) )
			return $html;

		if ( ! $post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, $size ) )
			return $html;

		return gThemeHTML::tag( 'a', [
			'href'   => wp_get_attachment_url( $post_thumbnail_id ),
			'title'  => get_the_title( $post_thumbnail_id ),
			'class'  => 'thickbox',
			'target' => '_blank',
		], gThemeHTML::img( $post_thumbnail_img[0] ) );
	}

	// core dup with posttype/taxonomy/title
	// @REF: `add_image_size()`
	public static function registerImageSize( $name, $atts = [] )
	{
		global $_wp_additional_image_sizes;

		$args = self::atts( [
			'n' => __( 'Untitled' ),
			'w' => 0,
			'h' => 0,
			'c' => 0,
			'p' => [ 'post' ], // posttype: TRUE: all/array: posttypes/FALSE: none
			't' => FALSE, // taxonomy: TRUE: all/array: taxes/FALSE: none
			'f' => empty( $atts['s'] ) ? FALSE : $atts['s'], // featured
		], $atts );

		$_wp_additional_image_sizes[$name] = [
			'width'     => absint( $args['w'] ),
			'height'    => absint( $args['h'] ),
			'crop'      => $args['c'],
			'post_type' => $args['p'],
			'taxonomy'  => $args['t'],
			'title'     => $args['n'],
			'thumbnail' => $args['f'],
		];
	}

	public function intermediate_image_sizes_advanced( $sizes, $metadata )
	{
		// removing standard image sizes
		unset(
			$sizes['thumbnail'],
			$sizes['medium'],
			$sizes['medium_large'],
			$sizes['large']
		);

		return $sizes;
	}

	// http://stackoverflow.com/a/20499803
	// https://gist.github.com/DavidCWebs/bb2df8a868a362510dc1
	// TODO: test this
	public function the_content_responsive_class( $content )
	{
		if ( empty( $content ) )
			return $content;

		$document = new \DOMDocument();
		libxml_use_internal_errors( TRUE );

		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		$document->loadHTML( utf8_decode( $content ) );
		$images = $document->getElementsByTagName( 'img' );

		foreach ( $images as $image )
			$image->setAttribute( 'class', 'img-responsive' ); // FIXME: this will replace all classes!!

		$html = $document->saveHTML();
		return $html;
	}

	public function get_image_tag_class( $class, $id, $align, $size )
	{
		return self::cssClass( $class );
	}

	public function wp_get_attachment_image_attributes( $attr, $attachment )
	{
		// unset( $attr['title'] ); // removed from core
		$attr['class'] = self::cssClass( $attr['class'] );
		return $attr;
	}

	public function get_image_tag( $html, $id, $alt, $title, $align, $size )
	{
		list( $src, $width, $height ) = image_downsize( $id, $size );

		return gThemeHTML::tag( 'img', [
			'src'      => $src,
			'alt'      => $alt ?: FALSE,
			'class'    => apply_filters( 'get_image_tag_class', 'align'.$align, $id, $align, $size ),
			'loading'  => 'lazy',
			'decoding' => 'async',
			'data'     => [
				'class'  => 'wp-image-'.$id, // WP core need this
				'width'  => $width, // need this for `image_add_caption()`
				// 'height' => $height,
				'id'     => $id,
				'size'   => $size,
				// 'align'  => $align,
			],
		] );
	}

	public function strip_width_height( $html )
	{
		return preg_replace( '/(width|height)="\d*"\s/', '', $html );
	}

	public function pre_option_image_default_link_type( $option )
	{
		return gThemeOptions::info( 'editor_image_default_link_type', 'file' );
	}

	public function pre_option_image_default_align( $option )
	{
		return gThemeOptions::info( 'editor_image_default_align', 'center' );
	}

	public function pre_option_image_default_size( $option )
	{
		return gThemeOptions::info( 'editor_image_default_size', 'content' );
	}

	public function jpeg_quality( $quality, $context )
	{
		return gThemeOptions::info( 'jpeg_quality', $quality );
	}

	public function wp_editor_set_quality( $default_quality, $mime_type )
	{
		return gThemeOptions::info( 'wp_editor_set_quality', $default_quality );
	}

	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////
	// IMAGE TAGS ON ADMIN MEDIA EDITOR

	// filters the sizes on admin insert media page
	public function image_size_names_choose( $size_names )
	{
		$sizes = gThemeOptions::info( 'images', [] );

		if ( empty( $sizes ) )
			return $size_names;

		if ( isset( $_REQUEST['post_id'] )
			&& $post_id = absint( $_REQUEST['post_id'] ) ) {

			$post_type = get_post( $post_id )->post_type;

		} else if ( isset( $_REQUEST['post'] )
			&& $post_id = absint( $_REQUEST['post'] ) ) {

			$post_type = get_post( $post_id )->post_type;

		} else if ( isset( $_REQUEST['post_type'] ) ) {

			$post_type = $_REQUEST['post_type'];

		} else if ( $current = self::getCurrentPostType() ) {

			$post_type = $current;

		} else {

			// $post_type = 'post';
			return $size_names; // bailing
		}

		$new_sizes = [];

		foreach ( $sizes as $name => $size )
			if ( $size['i'] && ( TRUE === $size['p'] || in_array( $post_type, $size['p'] ) ) )
				$new_sizes[$name] = $size['n'];

		// if ( gThemeWordPress::isDev() )
		// 	error_log( print_r( compact( 'post_type', 'new_sizes', 'size_names' ), TRUE ) );

		return array_merge( $size_names, $new_sizes );
	}

	// tries to convert image tags into core's thumbnails
	public function post_updated_convert_image_tags( $post_id, $post_after, $post_before )
	{
		if ( ! $images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE ) )
			return;

		if ( ! get_post_thumbnail_id( $post_id ) ) {

			$thumbnail = FALSE;
			$size      = gThemeOptions::info( 'thumbnail_image_size', 'single' );

			if ( isset( $images[$size] ) )
				$thumbnail = $images[$size];

			else if ( isset( $images['raw'] ) )
				$thumbnail = $images['raw'];

			// check if attachment exists
			if ( $thumbnail && ! get_post( $thumbnail ) )
				$thumbnail = FALSE;

			if ( $thumbnail )
				update_post_meta( $post_id, '_thumbnail_id', $thumbnail );
		}

		// removes the old data
		delete_post_meta( $post_id, GTHEME_IMAGES_META );
	}

	public function tags_attachment_fields_to_edit( $fields, $post )
	{
		if ( ! $post_id = absint( @$_REQUEST['post_id'] ) )
			return $fields;

		$sizes = gThemeOptions::info( 'images', [] );

		if ( empty( $sizes ) )
			return $fields;

		$posttype = get_post_type( $post_id );
		$images   = self::getMetaImages( $post_id );

		$html = $checked = '';

		foreach ( $sizes as $name => $size ) {

			if ( $size['s'] && ( TRUE === $size['p'] || in_array( $posttype, $size['p'] ) ) ) {

				$id      = 'attachments-'.$post->ID.'-gtheme-size-'.$name;
				$checked = ( isset( $images[$name] ) && $images[$name] == $post->ID ) ? ' checked="checked"' : '';
				/* translators: %1$s: size name, %2$s: size height, %3$s: size width */
				$label   = sprintf( _x( '%1$s <small>(%2$s&times;%3$s)</small>', 'Image Module: Media Tag Checkbox Label', 'gtheme' ),
					$size['n'], number_format_i18n( $size['h'] ), number_format_i18n( $size['w'] ) );

				$html.= '<li><label for="'.$id.'"><input style="width:10px;vertical-align:bottom;"'
					.' type="checkbox" value="'.$name.'" id="'.$id.'" name="gtheme_size_'.$name
					.'" '.$checked.' />'.$label.'</label></li>';
			}
		}

		if ( ! empty( $html ) ) {
			$html = '<ul style="margin:0;">'.$html.'</ul>'.
					'<input type="hidden" name="gtheme-image-sizes" value="modal" />'.
					'<input type="hidden" name="attachments['.$post->ID.']" value="dummy" />';

			$fields['gtheme_image_sizes'] = [
				'label' => __( 'Media Tags', 'gtheme' ),
				'input' => 'html',
				'html'  => $html,
			];
		}

		return $fields;
	}

	public function tags_attachment_fields_to_save( $post, $attachment )
	{
		if ( ! isset( $_REQUEST['gtheme-image-sizes'] )
			|| 'modal' != $_REQUEST['gtheme-image-sizes'] )
				return $post;

		if ( ! $post_id = absint( $_REQUEST['post_id'] ) )
			return $post;

		$sizes = gThemeOptions::info( 'images', [] );

		if ( empty( $sizes ) )
			return $post;

		$images = $striped = [];
		$saved  = self::getMetaImages( $post_id );

		foreach ( $sizes as $name => $size )
			if ( isset( $_REQUEST['gtheme_size_'.$name] ) && $name == $_REQUEST['gtheme_size_'.$name] )
				$images[$name] = $post['ID'];

		foreach ( $saved as $saved_size => $saved_id )
			if ( $post['ID'] != $saved_id )
				$striped[$saved_size] = $saved_id;

		self::setMetaImages( array_merge( $striped, $images ), $post_id );

		return $post;
	}

	public function terms_attachment_fields_to_edit( $form_fields, $post )
	{
		if ( empty( $_REQUEST['post_id'] ) ) {

			if ( empty( $post->post_parent ) )
				return $form_fields;

			else
				$parent_id = $post->post_parent;

		} else {
			$parent_id = absint( $_REQUEST['post_id'] );
		}

		$post_type = get_post_type( $parent_id );

		if ( ! in_array( $post_type, gThemeOptions::info( 'support_images_terms', [] ) ) )
			return $form_fields;

		$saved_terms = get_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, TRUE );

		if ( ! is_array( $saved_terms ) )
			$saved_terms = [];

		$selected = array_search( $post->ID, $saved_terms );

		$id = 'attachments-'.$post->ID.'-gtheme_images_terms';

		$dropdown = wp_dropdown_categories( [
			'taxonomy'         => gThemeOptions::info( 'support_images_terms_taxonomy', 'category' ),
			'selected'         => ( FALSE === $selected ? 0 : $selected ),
			'show_option_none' => __( '&mdash; Select a Term &mdash;', 'gtheme' ),
			'name'             => 'attachments['.$post->ID.'][gtheme_images_terms]',
			'id'               => $id,
			'show_count'       => 0,
			'hide_empty'       => 0,
			'hierarchical'     => 1,
			'echo'             => 0,
		] );

		$form_fields['gtheme_images_terms']['tr'] = '<tr><th class="label" valign="top" scope="row"><label for="'.$id.'"><span>'
			.__( 'Assign for', 'gtheme' ).'</span></label></th><td class="field">'
			.$dropdown.'</td></tr>';

		return $form_fields;
	}

	public function terms_attachment_fields_to_save( $post, $attachment )
	{
		if ( ! $parent_id = absint( $_REQUEST['post_id'] ) ) {

			if ( empty( $post['post_parent'] ) )
				return $post;

			else
				$parent_id = $post['post_parent'];
		}

		$post_type = get_post_type( $parent_id );

		if ( ! in_array( $post_type, gThemeOptions::info( 'support_images_terms', [] ) ) )
			return $post;

		if ( isset( $attachment['gtheme_images_terms'] ) ) {

			$saved_terms = get_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, TRUE );

			if ( ! is_array( $saved_terms ) )
				$saved_terms = [];

			$selected = array_search( $post['ID'], $saved_terms );

			unset( $saved_terms[$selected] );

			if ( '-1' != $attachment['gtheme_images_terms'] )
				$saved_terms[$attachment['gtheme_images_terms']] = $post['ID'];

			update_post_meta( $parent_id, GTHEME_IMAGES_TERMS_META, $saved_terms );
		}

		return $post;
	}

	// @REF: `AMP_Post_Template::build_post_featured_image()`
	public function amp_post_template_data( $data, $post )
	{
		$size = gThemeOptions::info( 'amp_image_size', 'single' );

		if ( ! $featured_id = self::getThumbnailID( $size, $post->ID ) )
			return $data;

		$featured_html = self::getImage( [
			'post_id'           => $post->ID,
			'post_thumbnail_id' => $featured_id,
			'empty'             => FALSE,
			'link'              => FALSE,
		] );

		if ( ! $featured_html )
			return $data;

		$featured_image = get_post( $featured_id );

		list( $sanitized_html, $featured_scripts, $featured_styles ) = AMP_Content_Sanitizer::sanitize(
			$featured_html,
			[ 'AMP_Img_Sanitizer' => [] ],
			[ 'content_max_width' => $data['content_max_width'] ]
		);

		// FIXME: workround for adding: `$featured_scripts`, `$featured_styles`

		$data['featured_image'] = [
			'amp_html' => $sanitized_html,
			'caption'  => $featured_image->post_excerpt,
		];

		return $data;
	}

	// wrapper for WP_Image class
	// SEE : https://github.com/markoheijnen/WP_Image
	// Last Updated : 20150615
	public static function attachment( $attachment_id )
	{
		if ( ! class_exists( 'WP_Image' ) )
			include_once( GTHEME_DIR.'/libs/wp-image/wp-image.php' );

		return new WP_Image( $attachment_id );
	}

	////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////
	/// STATIC METHODS

	public static function cssClass( $extra = '' )
	{
		$class = gThemeOptions::info( 'image-class', 'the-img img-responsive img-fluid' );
		return $extra ? gThemeHTML::prepClass( $class, $extra ) : $class;
	}

	public static function getMetaImages( $post_id = NULL, $check = FALSE )
	{
		global $gThemeImagesMeta;

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		if ( empty( $gThemeImagesMeta ) )
			$gThemeImagesMeta = [];

		if ( isset( $gThemeImagesMeta[$post_id] ) )
			return $gThemeImagesMeta[$post_id];

		if ( $images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE ) )
			$gThemeImagesMeta[$post_id] = $images;

		else
			$gThemeImagesMeta[$post_id] = [];

		if ( $check && $thumbnail = get_post_thumbnail_id( $post_id ) ) {

			if ( ! isset( $gThemeImagesMeta[$post_id]['raw'] ) )
				$gThemeImagesMeta[$post_id]['raw'] = $thumbnail;

			delete_post_meta( $post_id, '_thumbnail_id' );
		}

		return $gThemeImagesMeta[$post_id];
	}

	public static function setMetaImages( $images, $post_id = NULL, $check = FALSE )
	{
		global $gThemeImagesMeta;

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		if ( count( $images ) )
			update_post_meta( $post_id, GTHEME_IMAGES_META, $images );
		else
			delete_post_meta( $post_id, GTHEME_IMAGES_META );

		if ( $check )
			delete_post_meta( $post_id, '_thumbnail_id' );

		$gThemeImagesMeta[$post_id] = $images;
	}

	// FIXME: DEPRECATED
	public static function getThumbID( $tag = 'raw', $post_id = NULL )
	{
		self::_dep( 'gThemeImage::getThumbnailID()' );
		return self::getThumbnailID( $tag, $post_id );
	}

	// ANCESTOR : gtheme_get_image_id()
	public static function getThumbnailID( $tag = 'raw', $post_id = NULL )
	{
		$thumbnail_id = FALSE;

		if ( is_null( $post_id ) )
			$post_id = get_the_ID();

		$images = get_post_meta( $post_id, GTHEME_IMAGES_META, TRUE );

		if ( isset( $images[$tag] ) )
			$thumbnail_id = $images[$tag];

		else if ( isset( $images['raw'] ) )
			$thumbnail_id = $images['raw'];

		// check if attachment exists
		if ( $thumbnail_id && ! get_post( $thumbnail_id ) )
			$thumbnail_id = FALSE;

		if ( ! $thumbnail_id && gThemeOptions::info( 'post_thumbnail_fallback', TRUE ) )
			$thumbnail_id = get_post_thumbnail_id( $post_id );

		return apply_filters( 'gtheme_image_get_thumbnail_id', $thumbnail_id, $post_id );
	}

	public static function update_cache( $size = 'raw', $wp_query = NULL )
	{
		if ( ! $wp_query )
			$wp_query = $GLOBALS['wp_query'];

		if ( $wp_query->thumbnails_cached )
			return;

		$thumb_ids = [];

		foreach ( $wp_query->posts as $post )
			if ( $id = self::getThumbnailID( $size, $post->ID ) )
				$thumb_ids[] = $id;

		if ( ! empty( $thumb_ids ) )
			_prime_post_caches( $thumb_ids, FALSE, TRUE );

		$wp_query->thumbnails_cached = TRUE;
	}

	// FIXME: DEPRECATED
	public static function get_image( $atts = [] )
	{
		self::_dep( 'gThemeImage::getImage()' );
		return self::getImage( $atts );
	}

	// @REF: `wp_get_attachment_image()`, `wp_get_attachment_image_src()`
	public static function getImageHTML( $attachment_id, $size = 'thumbnail', $attr = '' )
	{
		if ( ! $image = apply_filters( 'wp_get_attachment_image_src', image_downsize( $attachment_id, $size ), $attachment_id, $size, FALSE ) )
			return '';

		list( $src, $width, $height ) = $image;

		$defaults = [
			'src'      => $src,
			'alt'      => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) ),
			'class'    => self::cssClass(),
			'loading'  => 'lazy',
			'decoding' => 'async',
			'data-url' => wp_get_attachment_url( $attachment_id ),
		];

		$attr = self::args( $attr, $defaults );

		// skipping `srcset`!

		return gThemeHTML::tag( 'img', apply_filters( 'wp_get_attachment_image_attributes', $attr, get_post( $attachment_id ), $size ) );
	}

	// @OLD: `gtheme_get_the_image()`, `gtheme_get_image_caption()`
	public static function getImage( $atts = [] )
	{
		$args = self::atts( [
			'tag'               => 'raw',
			'post_id'           => NULL,
			'post_thumbnail_id' => FALSE,
			'attr'              => '',
			'link'              => 'parent',
			'link_label'        => NULL,
			'empty'             => '',
			'url'               => FALSE,
			'caption'           => FALSE,
			'default_caption'   => '',
			'default_title'     => '', // data-title attr
		], $atts );

		if ( is_null( $args['post_id'] ) )
			$args['post_id'] = get_the_ID();

		if ( ! $args['post_thumbnail_id'] )
			$args['post_thumbnail_id'] = self::getThumbnailID( $args['tag'], $args['post_id'] );

		$args['tag'] = apply_filters( 'post_thumbnail_size', $args['tag'], $args['post_id'] );

		if ( ! $args['post_thumbnail_id'] )
			return apply_filters( 'post_thumbnail_html', $args['empty'], $args['post_id'], $args['post_thumbnail_id'], $args['tag'], $args['attr'] );

		if ( $args['url'] ) {

			$url = wp_get_attachment_url( $args['post_thumbnail_id'] );
			return FALSE === $url ? $args['empty'] : $url;
		}

		if ( ! $attachment = get_post( $args['post_thumbnail_id'] ) )
			return apply_filters( 'post_thumbnail_html', $args['empty'], $args['post_id'], $args['post_thumbnail_id'], $args['tag'], $args['attr'] );

		// do_action( 'begin_fetch_post_thumbnail_html', $args['post_id'], $attachment->ID, $args['tag'] );

		if ( in_the_loop() )
			self::update_cache( $args['tag'] );

		$html = self::getImageHTML( $attachment->ID, $args['tag'], $args['attr'] );

		// do_action( 'end_fetch_post_thumbnail_html', $args['post_id'], $attachment->ID, $args['tag'] );

		if ( $html ) {

			if ( ! empty( $args['link'] ) ) {

				// link to another size of image
				if ( is_array( $args['link'] ) ) {

					foreach ( $args['link'] as $link_size ) {
						if ( $link_size_thumbnail_id = self::getThumbnailID( $link_size, $args['post_id'] ) ) {
							if ( $link_attachment = get_post( $link_size_thumbnail_id ) ) {
								$args['link'] = get_attachment_link( $link_attachment );
								break;
							}
						}
					}

					// another size not found
					if ( is_array( $args['link'] ) )
						$args['link'] = get_attachment_link( $attachment );

				} else if ( 'parent' == $args['link'] ) {

					$args['link'] = get_permalink( $args['post_id'] );

					if ( is_null( $args['link_label'] ) )
						/* translators: %s: post title */
						$args['link_label'] = sprintf( _x( 'Read more about %s', 'Modules: Image: Link Label', 'gtheme' ), get_the_title( $args['post_id'] ) );

				} else if ( 'attachment' == $args['link'] ) {

					$args['link'] = get_attachment_link( $attachment );

				} else if ( 'url' == $args['link'] ) {

					$args['link'] = wp_get_attachment_url( $attachment->ID );
				}
			}

			if ( TRUE === $args['caption'] ) {

				$caption = apply_filters( 'wp_get_attachment_caption', $attachment->post_excerpt, $attachment->ID );

				if ( ! $caption )
					$caption = $args['default_caption'];

			} else if ( $args['caption'] ) {

				$caption = $args['caption'];

			} else {

				$caption = '';
			}

			if ( $args['link'] ) {

				$template = gThemeOptions::info( 'template_image_link', '<a href="%2$s" class="%3$s" data-title="%4$s" data-caption="%5$s" aria-label="%6$s">%1$s</a>' );

				$html = vsprintf( $template, [
					$html,
					esc_url( $args['link'] ),
					'-thumbnail-link',
					$args['default_title'] ?: gThemeUtilities::prepTitle( $attachment->post_title ),
					$caption ?: apply_filters( 'wp_get_attachment_caption', $attachment->post_excerpt, $attachment->ID ),
					$args['link_label'] ?: '',
				] );
			}

			if ( $caption ) {

				$template = gThemeOptions::info( 'template_image_caption', '<div class="%3$s">%1$s<p class="%4$s">%2$s</p></div>' );

				if ( $caption = gThemeAttachment::normalizeCaption( $caption ) )
					$html = sprintf( $template, $html, $caption, '-caption', '-caption-text' );
			}
		}

		return apply_filters( 'post_thumbnail_html', $html, $args['post_id'], $args['post_thumbnail_id'], $args['tag'], $args['attr'] );
	}

	// ANCESTOR : gtheme_image(), gtheme_image_caption()
	public static function image( $atts = [] )
	{
		if ( ! gThemeOptions::info( 'image_support', TRUE ) )
			return '';

		$args = self::atts( [
			'tag'             => 'raw',
			'post_id'         => NULL,
			'link'            => 'parent',
			'attr'            => '',
			'check_single'    => TRUE, // checks if post has `hide-image-single` system tag
			'empty'           => self::holder( ( isset( $atts['tag'] ) ? $atts['tag'] : 'raw' ), ( isset( $atts['class'] ) ? $atts['class'] : self::cssClass() ) ),
			'url'             => FALSE,
			'caption'         => FALSE,
			'default_caption' => '',
			'echo'            => TRUE,
			'class'           => self::cssClass(),
			'before'          => '<div class="entry-image'.( isset( $atts['tag'] ) ? ' image-'.$atts['tag'] : '' ).'">',
			'after'           => '</div>',
			'context'         => NULL,
		], $atts );

		if ( $args['check_single'] && ( is_singular() || is_single() ) && gThemeTerms::has( 'hide-image-single', $args['post_id'] ) )
			return '';

		if ( $args['class'] ) {
			if ( $args['attr'] ) {

				if ( ! is_array( $args['attr'] ) )
					$args['attr'] = wp_parse_args( $args['attr'], [] ); // FIXME: MAYBE WE HAVE PROBLEM!

				$args['attr']['class'] = $args['class'];
			} else {
				$args['attr'] = [ 'class' => $args['class'] ];
			}
		}

		$html = self::getImage( $args );

		if ( ! $args['echo'] )
			return $args['before'].$html.$args['after'];

		if ( ! empty( $html ) )
			echo $args['before'].$html.$args['after'];

		else if ( ! empty( $args['empty'] ) )
			echo $args['before'].$args['empty'].$args['after'];
	}

	public static function getPlaceHolder( $atts = [] )
	{
		if ( ! $src = gThemeOptions::info( 'image_placeholder' ) )
			return FALSE;

		return gThemeHTML::tag( 'img', [
			'src'   => $src,
			'alt'   => gThemeOptions::info( 'blog_name', '' ),
			'class' => '-placeholder',
		] );
	}

	public static function imageWithPlaceHolder( $atts = [], $ratio = NULL )
	{
		if ( is_null( $ratio ) )
			$ratio = gThemeOptions::info( 'image_aspect_ratio', '5:3' );

		if ( ! $ratio )
			return self::image( $atts );

		if ( ! array_key_exists( 'empty', $atts ) && ( $empty = self::getPlaceHolder( $atts ) ) )
			$atts['empty'] = $empty;

		$verbose = array_key_exists( 'echo', $atts ) ? $atts['echo'] : TRUE;

		$atts['echo'] = FALSE;

		$before = '<div class="entry-image-placeholder">';
		$before.= '<svg viewBox="0 0 '.str_replace( ':', ' ', $ratio ).'" />';
		$after  = '</div>';

		$html = $before.self::image( $atts ).$after;

		if ( ! $verbose )
			return $html;

		echo $html;
	}

	// ANCESTOR: gtheme_empty_image()
	public static function holder( $tag = 'raw', $extra_class = '', $force = FALSE )
	{
		if ( ! $force && ! current_user_can( 'edit_others_posts' ) )
			return '';

		return '<div class="gtheme-image-holder holder-image-'
			.$tag.( gThemeWordPress::isDev() ? ' isdev ' : ' ' )
			.$extra_class
			.'"></div>';
	}

	public static function holderJS( $width = 100, $height = 100, $atts = [] )
	{
		return '<img class="'.self::cssClass( '-holder' ).'" data-src="holder.js/'.$width.'x'.$height.'">';
	}

	// FIXME: working draft
	public static function termImage( $atts = [] )
	{
		$args = self::atts( [
			'tag'     => 'raw',
			'term_id' => get_queried_object_id(),
			'url'     => FALSE,
			'alt'     => '',
			'empty'   => '',
			'before'  => '',
			'after'   => '',
		], $atts );

		if ( ! $args['term_id'] )
			return $args['empty'];

		if ( ! $attach_id = get_term_meta( $args['term_id'], 'image', TRUE ) )
			return $args['empty'];

		$image = wp_get_attachment_image_src( $attach_id, $args['tag'] );

		if ( empty( $image[0] ) )
			return $args['empty'];

		if ( $args['url'] )
			return $image[0];

		return $args['before'].gThemeHTML::tag( 'img', [
			'src'      => $image[0],
			'alt'      => $args['alt'],
			'class'    => self::cssClass( '-featured' ),
			'loading'  => 'lazy',
			'decoding' => 'async',
		] ).$args['after'];
	}
}
