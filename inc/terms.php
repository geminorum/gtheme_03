<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

class gThemeTerms extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = array() )
	{
		extract( self::atts( array(
			'system_tags' => FALSE,
			'p2p'         => FALSE,
			'admin'       => FALSE,
		), $args ) );

		if ( $system_tags ) {
			add_action( 'init', array( $this, 'register_taxonomies' ) );

			if ( is_admin() ) {

				add_action( 'load-edit-tags.php', array( $this, 'load_edit_tags' ) );
				add_filter( 'geditorial_tweaks_taxonomy_info', array( $this, 'tweaks_taxonomy_info' ), 10, 3 );

				// remote: tax bulk actions with gNetworkTaxonomy
				add_filter( 'gnetwork_taxonomy_bulk_actions', array( $this, 'taxonomy_bulk_actions' ), 12, 2 );
				add_filter( 'gnetwork_taxonomy_bulk_callback', array( $this, 'taxonomy_bulk_callback' ), 12, 3 );

			} else {
				add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );
			}
		}

		if ( $p2p )
			add_action( 'p2p_init', array( $this, 'p2p_init' ) );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', array( $this, 'subs' ), 5 );
			add_action( 'gtheme_settings_load', array( $this, 'load' ) );
		}
	}

	public function subs( $subs )
	{
		return array_merge( $subs, array( 'primaryterms' => _x( 'Primary Terms', 'Modules: Menu Name', GTHEME_TEXTDOMAIN ) ) );
	}

	public function load( $sub )
	{
		if ( 'primaryterms' == $sub ) {

			if ( ! empty( $_POST )
				&& wp_verify_nonce( @$_POST['_gtheme_primaryterms'], 'gtheme-primaryterms' ) ) {

				if ( ! empty( $_POST['create-default-primaryterms'] ) ) {

					$defaults = gThemeOptions::info( 'primary_terms_defaults', array() );
					$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );

					if ( count( $defaults ) ) {

						$result = self::insertDefaults( $taxonomy, $defaults );
						wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
						exit();
					}

				} else if ( ! empty( $_POST['gtheme_primaryterms'] ) ) {

					$terms = $unordered = array();

					foreach ( $_POST['gtheme_primaryterms'] as $term_id => $term_args ) {

						$order = isset( $term_args['order'] ) && trim( $term_args['order'] ) ? intval( $term_args['order'] ) : FALSE;

						if ( isset( $term_args['checked'] ) ) {

							if ( FALSE !== $order )
								$terms[$order] = $term_id;
							else
								$unordered[(count($terms)+1)*100] = $term_id;

						} else if ( FALSE !== $order ) {
							$terms[$order] = $term_id;
						}
					}

					$options = $terms + $unordered;
					ksort( $options, SORT_NUMERIC );

					$result = gThemeOptions::update_option( 'terms', $options );
					wp_redirect( add_query_arg( array( 'message' => ( $result ? 'updated' : 'error' ) ), wp_get_referer() ) );
					exit();
				}
			}

			add_action( 'gtheme_settings_sub_primaryterms', array( $this, 'settings_sub_html_primaryterms' ), 10, 2 );
		}
	}

	// FIXME: [Display A Category Checklist In WordPress](https://paulund.co.uk/display-category-checklist-wordpress)
	public function settings_sub_html_primaryterms( $uri, $sub = 'general' )
	{
		$legend   = gThemeOptions::info( 'primary_terms_legend', FALSE );
		$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );
		$options  = gThemeOptions::getOption( 'terms', array() );

		echo '<form method="post" action="">';

			if ( $legend )
				echo $legend;

			echo '<table class="form-table">';
				echo '<tr><th scope="row">'._x( 'Primary Terms', 'Terms Module', GTHEME_TEXTDOMAIN ).'</th><td>';

				foreach ( self::getTerms( $taxonomy, FALSE, TRUE ) as $term ) {

					echo '<p>'.gThemeHTML::tag( 'input', array(
						'type'    => 'checkbox',
						'name'    => 'gtheme_primaryterms['.$term->term_id.'][checked]',
						'id'      => 'gtheme_primaryterms-'.$term->term_id.'-checked',
						'checked' => in_array( intval( $term->term_id ), $options ),
					) );

					$order = array_search( $term->term_id, $options );

					echo ' '.gThemeHTML::tag( 'input', array(
						'type'         => 'number',
						'step'         => '1',
						'autocomplete' => 'off',
						'class'        => 'small-text',
						'name'         => 'gtheme_primaryterms['.$term->term_id.'][order]',
						'id'           => 'gtheme_primaryterms-'.$term->term_id.'-order',
						'value'        => ( FALSE === $order ? '' : $order ),
						'style'        => 'vertical-align:middle',
					) );

					echo ' '.gThemeHTML::tag( 'label', array(
						'for'   => 'gtheme_primaryterms-'.$term->term_id.'-checked',
						'title' => $term->slug,
					), esc_html( $term->name ).' ('.number_format_i18n( $term->count ).')' );

					echo '</p>';
				}

				echo '</td></tr>';
			echo '</table>';
			echo '<p class="submit">';

				$this->settings_buttons( 'primaryterms', FALSE );
				echo get_submit_button( _x( 'Create Default Primary Terms', 'Terms Module', GTHEME_TEXTDOMAIN ), 'secondary', 'create-default-primaryterms', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';

			echo '</p>';
			wp_nonce_field( 'gtheme-primaryterms', '_gtheme_primaryterms' );
		echo '</form>';
	}

	public function register_taxonomies()
	{
		$cpt = gThemeOptions::info( 'system_tags_cpt', array( 'post' ) );
		$cap = gThemeOptions::info( 'settings_access', 'edit_theme_options' );

		register_taxonomy( GTHEME_SYSTEMTAGS, $cpt, array(
			'labels'                => $this->get_systemtags_labels(),
			'public'                => FALSE,
			'show_in_nav_menus'     => FALSE,
			'show_ui'               => TRUE,
			'show_tagcloud'         => FALSE,
			'hierarchical'          => TRUE,
			'meta_box_cb'           => array( 'gThemeTerms', 'checklistTerms' ),
			'update_count_callback' => array( 'gThemeUtilities', 'update_count_callback' ),
			'rewrite'               => FALSE,
			'query_var'             => FALSE,
			'capabilities'          => array(
				'manage_terms' => $cap,
				'edit_terms'   => $cap,
				'delete_terms' => $cap,
				'assign_terms' => 'edit_posts',
			)
		) );
	}

	private function get_systemtags_labels()
	{
		if ( ! current_user_can( 'edit_posts' ) )
			return array();

		return array(
			'name'              => _x( 'System Tags', 'System Tag Tax Labels: Name', GTHEME_TEXTDOMAIN ),
			'menu_name'         => _x( 'System Tags', 'System Tag Tax Labels: Menu Name', GTHEME_TEXTDOMAIN ),
			'singular_name'     => _x( 'System Tag', 'System Tag Tax Labels: Singular Name', GTHEME_TEXTDOMAIN ),
			'search_items'      => _x( 'Search System Tags', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'all_items'         => _x( 'All System Tags', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'parent_item'       => _x( 'Parent System Tag', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'parent_item_colon' => _x( 'Parent System Tag:', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'edit_item'         => _x( 'Edit System Tag', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'view_item'         => _x( 'View System Tag', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'update_item'       => _x( 'Update System Tag', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'add_new_item'      => _x( 'Add New System Tag', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'new_item_name'     => _x( 'New System Tag Name', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'not_found'         => _x( 'No system tags found.', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'no_terms'          => _x( 'No system tags', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'pagination'        => _x( 'System Tags list navigation', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
			'list'              => _x( 'System Tags list', 'System Tag Tax Labels', GTHEME_TEXTDOMAIN ),
		);
	}

	// system tags to post_classess
	public function post_class( $classes, $class, $post_ID )
	{
		$system_tags = get_the_terms( $post_ID, GTHEME_SYSTEMTAGS );

		if ( $system_tags && ! is_wp_error( $system_tags ) )
			foreach ( $system_tags as $system_tag )
				$classes[] = 'systemtag-'.$system_tag->slug;

		return $classes;
	}

	public function tweaks_taxonomy_info( $info, $object, $post_type )
	{
		if ( GTHEME_SYSTEMTAGS != $object->name )
			return $info;

		return array(
			'icon'  => 'admin-generic',
			'title' => _x( 'System Tags', 'System Tag Tax Labels: Menu Name', GTHEME_TEXTDOMAIN ),
			'edit'  => NULL,
		);
	}

	public function load_edit_tags()
	{
		if ( empty( $_REQUEST['taxonomy'] ) )
			return;

		if ( GTHEME_SYSTEMTAGS == $_REQUEST['taxonomy'] ) {
			$this->system_tags_table_action( 'gtheme_action' );
			add_action( 'after-'.GTHEME_SYSTEMTAGS.'-table', array( $this, 'after_system_tags_table' ) );
		}
	}

	private function system_tags_table_action( $name )
	{
		if ( empty( $_REQUEST[$name] ) )
			return FALSE;

		if ( 'install_systemtags' == $_REQUEST[$name] ) {

			$defaults = gThemeOptions::info( 'system_tags_defaults', array() );
			$taxonomy = GTHEME_SYSTEMTAGS;

		} else {
			return FALSE;
		}

		if ( ! count( $defaults ) )
			return FALSE;

		$action = self::insertDefaults( $taxonomy, $defaults ) ? 'added_'.$taxonomy : $action = 'error_'.$taxonomy;

		wp_redirect( add_query_arg( $name, $action ) );
		exit;
	}

	public function after_system_tags_table( $taxonomy )
	{
		$name   = 'gtheme_action';
		$title  = _x( 'Install Default System Tags', 'Terms Module', GTHEME_TEXTDOMAIN );
		$action = add_query_arg( $name, 'install_systemtags' );

		if ( isset( $_GET[$name] ) ) {

			if ( 'error_systemtags' == $_GET[$name] )
				$title = _x( 'Error while adding default system tags.', 'Terms Module', GTHEME_TEXTDOMAIN );

			else if ( 'added_systemtags' == $_GET[$name] )
				$title = _x( 'Default system tags added.', 'Terms Module', GTHEME_TEXTDOMAIN );
		}

		echo '<div class="form-field"><p>';
			echo '<a href="'.esc_url( $action ).'" class="button">'.$title.'</a>';
		echo '</p></div>';
	}

	public function taxonomy_bulk_actions( $actions, $taxonomy )
	{
		if ( $taxonomy != GTHEME_SYSTEMTAGS )
			return $actions;

		return array_merge( $actions, array( 'empty_lastmonth' => _x( 'Empty Before Last Month', 'Terms Module', GTHEME_TEXTDOMAIN ) ) );
	}

	public function taxonomy_bulk_callback( $callback, $action, $taxonomy )
	{

		if ( $taxonomy == GTHEME_SYSTEMTAGS && 'empty_lastmonth' == $action )
			return array( $this, 'bulk_empty_lastmonth' );

		return $callback;
	}

	public function bulk_empty_lastmonth( $term_ids, $taxonomy )
	{
		if ( $taxonomy != GTHEME_SYSTEMTAGS )
			return FALSE;

		$cpt = gThemeOptions::info( 'system_tags_cpt', array( 'post' ) );

		$args = array(
			'fields'         => 'ids',
			'post_type'      => $cpt,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'tax_query'      => array( array(
				'taxonomy' => $taxonomy,
				'terms'    => array_filter( $term_ids, 'intval' ),
				// 'operator' => 'EXISTS',
			) ),
			'date_query' => array(
				'column' => 'post_date_gmt',
				'before' => '30 days ago',
			),
			'suppress_filters'       => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		);

		$query = new \WP_Query;
		$posts = $query->query( $args );
		$count = 0;

		foreach ( $posts as $post )
			foreach ( $term_ids as $term_id )
				if ( TRUE === wp_remove_object_terms( $post, intval( $term_id ), $taxonomy ) )
					$count++;

		return $count;
	}

	public function p2p_init()
	{
		p2p_register_connection_type( array(
			'name'       => 'posts_to_posts',
			'from'       => 'post',
			'to'         => 'post',
			'reciprocal' => TRUE,
			'title'      => __( 'Connected Posts', GTHEME_TEXTDOMAIN ),
		) );
	}

	// helper for settings page
	public static function insertDefaults( $taxonomy, $defaults )
	{
		if ( ! taxonomy_exists( $taxonomy ) )
			return FALSE;

		foreach ( $defaults as $term_slug => $term_name )
			if ( ! term_exists( $term_slug, $taxonomy ) )
				wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );

		return TRUE;
	}

	// FIXME: DEPRECATED
	public static function get( $taxonomy = 'category', $post_id = FALSE, $object = FALSE, $key = 'term_id' )
	{
		self::__dep( 'gThemeTerms::getTerms()' );
		return self::getTerms( $taxonomy, $post_id, $object, $key );
	}

	// callback for meta box for choose only tax
	// CAUTION: tax must be cat (hierarchical)
	// @SOURCE: `post_categories_meta_box()`
	public static function checklistTerms( $post, $box )
	{
		$args = self::atts( array(
			'taxonomy' => 'category',
			'edit_url' => NULL,
		), empty( $box['args'] ) ? array() : $box['args'] );

		$tax_name = esc_attr( $args['taxonomy'] );
		$taxonomy = get_taxonomy( $args['taxonomy'] );

		$html = wp_terms_checklist( $post->ID, array(
			'taxonomy'      => $tax_name,
			'checked_ontop' => FALSE,
			'echo'          => FALSE,
		) );

		echo '<div id="taxonomy-'.$tax_name.'" class="geditorial-admin-wrap-metabox choose-tax">';
			if ( $html ) {
				echo '<div class="field-wrap-list"><ul>'.$html.'</ul></div>';
				// allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
				echo '<input type="hidden" name="tax_input['.$tax_name.'][]" value="0" />';
			} else {
				echo '<div class="field-wrap field-wrap-empty">';
					echo '<span>'.$taxonomy->labels->not_found.'</span>';
				echo '</div>';
			}
		echo '</div>';
	}

	public static function has( $term = '', $post = NULL, $taxonomy = GTHEME_SYSTEMTAGS )
	{
		return has_term( $term, $taxonomy, $post );
	}

	public static function linkPrimary( $before = '', $after = '', $post = NULL, $title = '' )
	{
		if ( ! $post = get_post( $post ) )
			return FALSE;

		$taxonomy  = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );
		$primaries = gThemeOptions::getOption( 'terms', array() );

		if ( ! count( $primaries ) )
			return FALSE;

		$terms = get_terms( array(
			'object_ids'             => array( $post->ID ),
			'taxonomy'               => array( $taxonomy ),
			'include'                => $primaries,
			'number'                 => 1,
			'update_term_meta_cache' => FALSE,
		) );

		if ( ! count( $terms ) )
			return FALSE;

		echo $before;
			echo '<a href="'
				.get_term_link( $terms[0], $terms[0]->taxonomy )
				.'" data-toggle="tooltip" title="'.$title.'">'
					.esc_html( $terms[0]->name )
				.'</a>';
		echo $after;

		return TRUE;
	}

	public static function theList( $taxonomy, $before = '', $after = '', $post = NULL, $title = '' )
	{
		$terms = get_the_terms( $post, $taxonomy );

		if ( is_wp_error( $terms ) )
			return FALSE;

		if ( ! $terms )
			return FALSE;

		foreach ( $terms as $term )
			echo $before.gThemeHTML::tag( 'a', array(
				'href'  => get_term_link( $term, $taxonomy ),
				'class' => array( 'taxonomy-'.$taxonomy, 'term-'.$term->slug ),
			), esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ) ) ).$after;

		return TRUE;
	}
}
