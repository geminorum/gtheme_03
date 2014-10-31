<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeImage extends gThemeModuleCore {

	function setup_actions( $args = array() )
	{
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'intermediate_image_sizes_advanced', array( $this, 'intermediate_image_sizes_advanced' ) );

		add_filter( 'get_image_tag_class', array( $this, 'get_image_tag_class' ), 10, 4 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'wp_get_attachment_image_attributes' ), 10, 2 );
		
		add_filter( 'post_thumbnail_html', array( $this, 'strip_width_height' ), 10 );
		add_filter( 'image_send_to_editor', array( $this, 'strip_width_height' ), 10 );
		add_filter( 'image_send_to_editor', array( $this, 'image_send_to_editor' ), 12, 8 );

		add_filter( 'pre_option_image_default_link_type', array( $this, 'pre_option_image_default_link_type' ), 10 );
		add_filter( 'pre_option_image_default_align', array( $this, 'pre_option_image_default_align' ), 10 );
		add_filter( 'pre_option_image_default_size', array( $this, 'pre_option_image_default_size' ), 10 );
		add_filter( 'jpeg_quality', array( $this, 'jpeg_quality' ), 10, 2 );

		add_filter( 'image_size_names_choose', array( $this, 'image_size_names_choose' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'tags_attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'tags_attachment_fields_to_save' ), 10, 2 );
	
		// image for terms on admin media editor
		add_filter( 'attachment_fields_to_edit', array( $this, 'terms_attachment_fields_to_edit' ), 9, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'terms_attachment_fields_to_save' ), 9, 2 );
	}
	
	function init()
	{
		$gtheme_info = gtheme_get_info();
		foreach( $gtheme_info['images'] as $name => $size )
			add_image_size( $name, $size['w'], $size['h'], $size['c'] );
		
		//global $_wp_additional_image_sizes;
		//gtheme_dump( $_wp_additional_image_sizes );	
	}
	
	function intermediate_image_sizes_advanced( $sizes )
	{
		// removing standard image sizes
		unset( 
			$sizes['thumbnail'],
			$sizes['medium'],
			$sizes['large']
		);
		
		return $sizes;
	}
	
	function wp_get_attachment_image_attributes( $attr, $attachment ) 
	{
		unset( $attr['title'] );
		$attr['class'] = $attr['class'].' '.gtheme_get_info( 'image-class', 'the-img img-responsive' );
		return $attr;
	}

	function get_image_tag_class( $class, $id, $align, $size )
	{
		return $class.' '.gtheme_get_info( 'image-class', 'the-img img-responsive' );
	}
	
	// http://css-tricks.com/snippets/wordpress/remove-width-and-height-attributes-from-inserted-images/
	// remove width and height attributes from inserted images
	function strip_width_height( $html ) 
	{
		return preg_replace( '/(width|height)="\d*"\s/', '', $html );
	}
	
	function image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt ) 
	{
		// http://css-tricks.com/snippets/wordpress/insert-images-within-figure-element-from-media-uploader/
		//$html = '<figure id="post-'.$id.' media-'.$id.'" class="align-'.$align.'"><img src="'.$url.'" alt="'.$title.'" />';
		$html = '<figure id="post-'.$id.'-media-'.$id.'" class="align-'.$align.'"'.( empty( $title ) ? '' : ' title="'.$title.'"' ).'>'.$html;
		
		if ( $caption )
			$html .= '<figcaption>'.$caption.'</figcaption>';
		
		return $html.'</figure>';
	} 
	
	function pre_option_image_default_link_type( $option ) 
	{ 
		return gtheme_get_info( 'editor_image_default_link_type', 'file' ); 
	}
	
	function pre_option_image_default_align( $option ) 
	{ 
		return gtheme_get_info( 'editor_image_default_align', 'center' ); 
	}
	
	function pre_option_image_default_size( $option ) 
	{ 
		return gtheme_get_info( 'editor_image_default_size', 'content' ); 
	}
	
	function jpeg_quality( $quality, $context ) 
	{
		return gtheme_get_info( 'jpeg_quality', $quality ); 
	}
	
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	// IMAGE TAGS ON ADMIN MEDIA EDITOR
	
	// filters the sizes on admin insert media page
	function image_size_names_choose( $size_names ) 
	{
		if ( $post_id = absint( @ $_REQUEST['post_id'] ) ) {
			$post = get_post( $post_id );	
			$post_type = $post->post_type;
		} else if ( $post_id = absint( @ $_REQUEST['post'] ) ) {
			$post = get_post( $post_id );	
			$post_type = $post->post_type;
		} else if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			$post_type = 'post';
		}
		
		$new_size_names = array();
		$images = (array) gtheme_get_info( 'images', array() );
		
		foreach( $images as $name => $size )
			if ( $size['i'] && in_array( $post_type ,$size['p'] ) )
				$new_size_names[$name] = $size['n'];
		
		return apply_filters( 'gtheme_images_sizenames', ( $new_size_names + $size_names ), $new_size_names );
	} 

	function tags_attachment_fields_to_edit( $fields, $post )
	{
		if ( ! $post_id = @ absint( $_REQUEST['post_id'] ) )
			return $fields;
		
		$post_type = get_post_type( $post_id );
		$images = get_post_meta( $post_id, '_gtheme_images', true );
		if ( ! is_array( $images ) ) 
			$images = array();
		
		$html = $checked = ''; 		
		$gtheme_images = (array) gtheme_get_info( 'images', array() );
		
		foreach( $gtheme_images as $name => $size ) {
			if ( $size['t'] && in_array( $post_type ,$size['p'] ) ) {
				$checked = ( isset( $images[$name] ) && $images[$name] == $post->ID ) ? ' checked="checked"' : '';
				$label = sprintf( _x( '%1$s (%2$s&nbsp;&times;&nbsp;%3$s)', 'Media Tag Checkbox Label', GTHEME_TEXTDOMAIN ), $size['n'], number_format_i18n( $size['w'] ), number_format_i18n( $size['h'] ) );
				$id = 'attachments-'.$post->ID.'-gtheme-size-'.$name;
				$html .= '<li><label for="'.$id.'"><input style="width:10px;vertical-align:bottom;" type="checkbox" value="'.$name.'" id="gtheme_size_'.$name.'" name="gtheme_size_'.$name.'" '.$checked.' /> '.esc_html( $label ).'</label></li>';
			}
		}
		
		if ( ! empty( $html ) ) {
			$html = '<ul style="margin:0;">'.$html.'</ul>'.
					'<input type="hidden" name="gtheme-image-sizes" value="modal" />'.
					'<input type="hidden" name="attachments['.$post->ID.']" value="dummy" />'; 
				
			$fields['gtheme_image_sizes'] = array(
				'label' => __( 'Media Tags', GTHEME_TEXTDOMAIN ), 
				'input' => 'html', 
				'html' => $html,
			);
		}
		return $fields;
	} 	

	function tags_attachment_fields_to_save( $post, $attachment ) 
	{
		if ( ! isset( $_REQUEST['gtheme-image-sizes'] ) 
			|| 'modal' != $_REQUEST['gtheme-image-sizes'] )
				return $post;
			
		if ( ! $post_id = absint( $_REQUEST['post_id'] ) )
			return $post;

		$images = $striped = array();
		$sizes = (array) gtheme_get_info( 'images', array() );
		$saved_images = get_post_meta( $post_id, '_gtheme_images', true );
		if ( ! is_array( $saved_images ) )
			$saved_images = array();
		
		foreach( $sizes as $name => $size )
			if ( isset( $_REQUEST['gtheme_size_'.$name] ) && $name == $_REQUEST['gtheme_size_'.$name] )
				$images[$name] = $post['ID'];
		
		foreach( $saved_images as $saved_size => $saved_id )
			if ( $post['ID'] != $saved_id )
				$striped[$saved_size] = $saved_id;
				
		$final = array_merge( $striped, $images );
				
		if ( count( $final ) )
			update_post_meta( $post_id, '_gtheme_images', $final );
		else
			delete_post_meta( $post_id, '_gtheme_images' );
			
		return $post;
	} 

	function terms_attachment_fields_to_edit( $form_fields, $post ) 
	{
		if ( ! $parent_id = @ absint( $_REQUEST['post_id'] ) ) {
			if ( empty ( $post->post_parent ) )
				return $form_fields;
			else
				$parent_id = $post->post_parent;
		}
		
		$post_type = get_post_type( $parent_id );
		if ( ! in_array( $post_type, gtheme_get_info( 'support_images_terms', array() ) ) )
			return $form_fields;

		$saved_terms = get_post_meta( $parent_id, '_gtheme_images_terms', true );
		if ( ! is_array( $saved_terms ) )
			$saved_terms = array();
		$selected = array_search( $post->ID, $saved_terms );
		$dropdown = wp_dropdown_categories( array(
			'taxonomy' => gtheme_get_info( 'support_images_terms_taxonomy', 'category' ),
			'selected' => ( false === $selected ? 0 : $selected ),
			'show_option_none' => __( '&mdash; Select a Term &mdash;', GTHEME_TEXTDOMAIN ),
			'name' => 'attachments['.$post->ID.'][gtheme_images_terms]',
			'id' => 'attachments-'.$post->ID.'-gtheme_images_terms',
			'show_count' => 0,
			'hide_empty' => 0,
			'hierarchical' => 1,
			'echo' => 0,
		) );

		$form_fields['gtheme_images_terms']['tr'] = '<tr><th class="label" valign="top" scope="row"><label for="attachments-'.$post->ID.'-gtheme_images_terms"><span>'
			.__( 'Assign for', GTHEME_TEXTDOMAIN ).'</span></label></th><td class="field">'
			.$dropdown.'</td></tr>';
			
		return $form_fields;			
	}
	
	function terms_attachment_fields_to_save( $post, $attachment ) 
	{
		if ( ! $parent_id = absint( $_REQUEST['post_id'] ) ) {
			if ( empty ( $post['post_parent'] ) )
				return $post;
			else
				$parent_id = $post['post_parent'];	
		}
		
		$post_type = get_post_type( $parent_id );
		if ( ! in_array( $post_type, gtheme_get_info( 'support_images_terms', array() ) ) )
			return $post;
		
		if( isset( $attachment['gtheme_images_terms'] ) ) {
			$saved_terms = get_post_meta( $parent_id, '_gtheme_images_terms', true );
			if ( ! is_array( $saved_terms ) )
				$saved_terms = array();	
			$selected = array_search( $post['ID'], $saved_terms );
			unset( $saved_terms[$selected] );
			if ( '-1' != $attachment['gtheme_images_terms'] )
				$saved_terms[$attachment['gtheme_images_terms']] = $post['ID'];
			update_post_meta( $parent_id, '_gtheme_images_terms', $saved_terms );
		}
		
		return $post;
	}
	
	// wrapper for WP_Image class
	// SEE : https://github.com/markoheijnen/WP_Image
	// Last Updated : 21141101 
	public static function image( $attachment_id )
	{
		if ( ! class_exists( 'WP_Image' ) )
			include_once( GTHEME_DIR.'/libs/wp-image/wp-image.php' );
			
		return new WP_Image( $attachment_id );
	}
}


// https://github.com/mattheu/wordpress-picturefill
// http://scottjehl.github.io/picturefill/
// https://github.com/scottjehl/picturefill

// http://www.wpbeginner.com/wp-tutorials/how-to-grayscale-images-in-wordpress/
// http://bavotasan.com/2011/create-black-white-thumbnail-wordpress/

// https://github.com/billerickson/Image-Override/blob/master/image-override.php
// http://bavotasan.com/2012/add-a-copyright-field-to-the-media-uploader-in-wordpress/

// http://wp.tutsplus.com/tutorials/theme-development/using-custom-image-sizes-in-your-theme-and-resizing-existing-images-to-the-new-sizes/

// https://gist.github.com/franz-josef-kaiser/1853928
// https://gist.github.com/kovshenin/1984363

// http://wp-snippet.com/snippets/allow-contributors-upload-images/

// $size_names = apply_filters( 'image_size_names_choose', array('thumbnail' => __('Thumbnail'), 'medium' => __('Medium'), 'large' => __('Large'), 'full' => __('Full Size')) );
