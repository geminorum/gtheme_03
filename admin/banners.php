<form method="post" action="">
	<h3><?php _e( 'Theme Banner Tiles Settings', GTHEME_TEXTDOMAIN ); ?></h3>

	<?php if ( $banners_legend = gThemeOptions::info( 'banners_legend', false ) ) { ?>
		<table class="form-table"><tbody><tr valign="top">
			<th scope="row"><label><?php _e( 'Legend', GTHEME_TEXTDOMAIN ); ?></label></th>
			<td><?php echo $banners_legend; ?><br />
				<span class="description"><?php _e( 'Your theme extra information', GTHEME_TEXTDOMAIN ); ?></span>
			</td>
		</tr></tbody></table>
	<?php } ?>

	<table id="repeatable-fieldset-one" width="100%">
		<thead><tr>
			<th width="10%"><?php _e( 'Group', GTHEME_TEXTDOMAIN ); ?></th>
			<th width="2%"><?php _e( 'Ord.', GTHEME_TEXTDOMAIN ); ?></th>
			<th width="18%"><?php _e( 'Title', GTHEME_TEXTDOMAIN ); ?></th>
			<th width="18%"><?php _e( 'URL', GTHEME_TEXTDOMAIN ); ?></th>
			<th w2idth="35%"><?php _e( 'Image', GTHEME_TEXTDOMAIN ); ?></th>
			<th width="8%"></th>
		</tr></thead>
		<tbody>


	<?php
	$banner_groups = gThemeOptions::info( 'banner_groups', array() );
	$banners = gThemeOptions::get_option( 'banners', array() );
	if ( count( $banners ) ) {
		foreach ( $banners as $banner ) {
			?><tr>
				<td><select name="gtheme-banners-group[]" style="width:100%;">
					<?php foreach ( $banner_groups as $value => $label ) { ?>
						<option value="<?php echo $value; ?>"<?php selected( $banner['group'], $value ); ?>><?php echo $label; ?></option>
					<?php } ?>
				</select></td>
				<td><input type="text" class="widefat" name="gtheme-banners-order[]" value="<?php echo isset( $banner['order'] ) ? esc_attr( $banner['order'] ) : ''; ?>" /></td>
				<td><input type="text" class="widefat" name="gtheme-banners-title[]" value="<?php echo isset( $banner['title'] ) ? esc_attr( $banner['title'] ) : ''; ?>" /></td>
				<td><input type="text" class="widefat" name="gtheme-banners-url[]" value="<?php echo isset( $banner['url'] ) && $banner['url'] ? esc_attr( $banner['url'] ) : 'http://'; ?>" dir="ltr" /></td>
				<td><input type="text" class="widefat" name="gtheme-banners-image[]" value="<?php echo isset( $banner['image'] ) && $banner['image'] ? esc_attr( $banner['image'] ) : 'http://'; ?>" dir="ltr" /></td>
				<td><a class="button remove-row" href="#"><?php _e( 'Remove', GTHEME_TEXTDOMAIN ); ?></a></td>
			</tr><?php
		}
	} else {
		?><tr>
			<td><select name="gtheme-banners-group[]" style="width:100%;">
				<?php foreach ( $banner_groups as $value => $label ) { ?>
					<option value="<?php echo $value; ?>"><?php echo $label; ?></option>
				<?php } ?>
			</select></td>
			<td><input type="text" class="widefat" name="gtheme-banners-order[]" /></td>
			<td><input type="text" class="widefat" name="gtheme-banners-title[]" /></td>
			<td><input type="text" class="widefat" name="gtheme-banners-url[]" value="http://" dir="ltr" /></td>
			<td><input type="text" class="widefat" name="gtheme-banners-image[]" value="http://" dir="ltr" /></td>
			<td><a class="button remove-row" href="#"><?php _e( 'Remove', GTHEME_TEXTDOMAIN ); ?></a></td>
		</tr><?php
	}
	?><tr class="empty-row screen-reader-text">
		<td><select name="gtheme-banners-group[]" style="width:100%;">
			<?php foreach ( $banner_groups as $value => $label ) : ?>
				<option value="<?php echo $value; ?>"><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select></td>
		<td><input type="text" class="widefat" name="gtheme-banners-order[]" /></td>
		<td><input type="text" class="widefat" name="gtheme-banners-title[]" /></td>
		<td><input type="text" class="widefat" name="gtheme-banners-url[]" value="http://" dir="ltr" /></td>
		<td><input type="text" class="widefat" name="gtheme-banners-image[]" value="http://" dir="ltr" /></td>
		<td><a class="button remove-row" href="#"><?php _e( 'Remove', GTHEME_TEXTDOMAIN ); ?></a></td>
	</tr>
	</tbody></table>

	<p class="submit">
		<a id="add-row" class="button" href="#"><?php
			_e( 'Add Another', GTHEME_TEXTDOMAIN );
		?></a>
		<input type="submit" class="button-primary" name="submitform" value="&nbsp;&nbsp;<?php _e( 'Save' ); ?>&nbsp;&nbsp;" />
	</p>

	<?php wp_nonce_field( 'gtheme-banners', '_gtheme_banners' ); ?>
</form>

<script type="text/javascript">
	jQuery(document).ready(function( $ ){
		$( '#add-row' ).on('click', function() {
			var row = $( '.empty-row.screen-reader-text' ).clone(true);
			row.removeClass( 'empty-row screen-reader-text' );
			row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			return false;
		});

		$( '.remove-row' ).on('click', function() {
			$(this).parents('tr').remove();
			return false;
		});
	});
</script>
