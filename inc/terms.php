<?php defined( 'ABSPATH' ) or die( header( 'HTTP/1.0 403 Forbidden' ) );

class gThemeTerms extends gThemeModuleCore
{

	protected $ajax = TRUE;

	public function setup_actions( $args = [] )
	{
		extract( self::atts( [
			'system_tags' => FALSE,
			'p2p'         => FALSE, // DEPRECATED: use gEditorial Connected
			'admin'       => FALSE,
		], $args ) );

		if ( $system_tags ) {

			add_action( 'init', [ $this, 'register_taxonomies' ] );

			if ( is_admin() ) {

				add_action( 'admin_menu', [ $this, 'admin_menu_system_tags' ] );
				add_action( 'load-edit-tags.php', [ $this, 'load_edit_tags' ] );
				add_filter( 'geditorial_tweaks_taxonomy_info', [ $this, 'tweaks_taxonomy_info' ], 10, 3 );

				// remote: tax bulk actions with gNetwork: Taxonomy
				add_filter( 'gnetwork_taxonomy_bulk_actions', [ $this, 'taxonomy_bulk_actions' ], 12, 2 );
				add_filter( 'gnetwork_taxonomy_bulk_callback', [ $this, 'taxonomy_bulk_callback' ], 12, 3 );

			} else {

				add_filter( 'post_class', [ $this, 'post_class' ], 10, 3 );
			}
		}

		if ( $p2p )
			add_action( 'p2p_init', [ $this, 'p2p_init' ] );

		if ( $admin && is_admin() ) {
			add_filter( 'gtheme_settings_subs', [ $this, 'subs' ], 5 );
			add_action( 'gtheme_settings_load', [ $this, 'load' ] );
		}
	}

	public function subs( $subs )
	{
		return array_merge( $subs, [ 'primaryterms' => _x( 'Primary Terms', 'Modules: Menu Name', 'gtheme' ) ] );
	}

	public function load( $sub )
	{
		if ( 'primaryterms' == $sub ) {

			if ( ! empty( $_POST )
				&& wp_verify_nonce( @$_POST['_gtheme_primaryterms'], 'gtheme-primaryterms' ) ) {

				if ( ! empty( $_POST['create-default-primaryterms'] ) ) {

					$defaults = gThemeOptions::info( 'primary_terms_defaults', [] );
					$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );

					if ( count( $defaults ) ) {

						$result = self::insertDefaults( $taxonomy, $defaults );

						gThemeWordPress::redirectReferer( $result ? 'updated' : 'error' );
					}

				} else if ( ! empty( $_POST['gtheme_primaryterms'] ) ) {

					$terms = $unordered = [];

					foreach ( $_POST['gtheme_primaryterms'] as $term_id => $term_args ) {

						$order = isset( $term_args['order'] ) && trim( $term_args['order'] ) ? (int) $term_args['order'] : FALSE;

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

					gThemeWordPress::redirectReferer( $result ? 'updated' : 'error' );
				}
			}

			add_action( 'gtheme_settings_sub_primaryterms', [ $this, 'settings_sub_html_primaryterms' ], 10, 2 );
		}
	}

	// FIXME: [Display A Category Checklist In WordPress](https://paulund.co.uk/display-category-checklist-wordpress)
	public function settings_sub_html_primaryterms( $uri, $sub = 'general' )
	{
		$legend   = gThemeOptions::info( 'primary_terms_legend', FALSE );
		$taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );
		$options  = gThemeOptions::getOption( 'terms', [] );

		echo '<form method="post" action="">';

			if ( $legend )
				echo $legend;

			echo '<table class="form-table">';
				echo '<tr><th scope="row">'._x( 'Primary Terms', 'Modules: Terms', 'gtheme' ).'</th><td>';

				foreach ( gThemeWordPress::getTerms( $taxonomy, FALSE, TRUE ) as $term ) {

					echo '<p>'.gThemeHTML::tag( 'input', [
						'type'    => 'checkbox',
						'name'    => 'gtheme_primaryterms['.$term->term_id.'][checked]',
						'id'      => 'gtheme_primaryterms-'.$term->term_id.'-checked',
						'checked' => in_array( (int) $term->term_id, $options ),
					] );

					$order = array_search( $term->term_id, $options );

					echo ' '.gThemeHTML::tag( 'input', [
						'type'         => 'number',
						'step'         => '1',
						'autocomplete' => 'off',
						'class'        => 'small-text',
						'name'         => 'gtheme_primaryterms['.$term->term_id.'][order]',
						'id'           => 'gtheme_primaryterms-'.$term->term_id.'-order',
						'value'        => ( FALSE === $order ? '' : $order ),
						'style'        => 'vertical-align:middle',
					] );

					echo ' '.gThemeHTML::tag( 'label', [
						'for'   => 'gtheme_primaryterms-'.$term->term_id.'-checked',
						'title' => $term->slug,
					], esc_html( $term->name ).' ('.number_format_i18n( $term->count ).')' );

					echo '</p>';
				}

				echo '</td></tr>';
			echo '</table>';
			echo '<p class="submit">';

				$this->settings_buttons( 'primaryterms', FALSE );
				echo get_submit_button( _x( 'Create Default Primary Terms', 'Modules: Terms', 'gtheme' ), 'secondary', 'create-default-primaryterms', FALSE, self::getButtonConfirm() ).'&nbsp;&nbsp;';

			echo '</p>';
			wp_nonce_field( 'gtheme-primaryterms', '_gtheme_primaryterms' );
		echo '</form>';
	}

	public function register_taxonomies()
	{
		$posttypes = gThemeOptions::info( 'system_tags_cpt', [ 'post', 'entry' ] );
		$manage    = gThemeOptions::info( 'settings_access', 'edit_theme_options' );
		$assign    = gThemeOptions::info( 'system_tags_access', 'edit_others_posts' );
		$can       = current_user_can( $assign );

		register_taxonomy( GTHEME_SYSTEMTAGS, $posttypes, [
			'labels'                => $can ? $this->get_systemtags_labels() : [],
			'public'                => FALSE,
			'show_ui'               => TRUE,
			'show_in_menu'          => FALSE,
			'show_in_quick_edit'    => $can,
			'show_in_nav_menus'     => FALSE,
			'show_tagcloud'         => FALSE,
			'hierarchical'          => TRUE,
			'meta_box_cb'           => $can ? [ 'gThemeTerms', 'checklistTerms' ] : FALSE,
			'update_count_callback' => '_update_generic_term_count', // [ 'gThemeUtilities', 'update_count_callback' ],
			'rewrite'               => FALSE,
			'query_var'             => FALSE,
			'show_in_rest'          => TRUE, // for block editor
			'rest_base'             => str_replace( '_', '-', GTHEME_SYSTEMTAGS ),
			'capabilities'          => [
				'manage_terms' => $manage,
				'edit_terms'   => $manage,
				'delete_terms' => $manage,
				'assign_terms' => $assign,
			],
		] );
	}

	private function get_systemtags_labels()
	{
		return [
			'name'                  => _x( 'System Tags', 'System Tag Tax Labels: Name', 'gtheme' ),
			'menu_name'             => _x( 'System Tags', 'System Tag Tax Labels: Menu Name', 'gtheme' ),
			'singular_name'         => _x( 'System Tag', 'System Tag Tax Labels: Singular Name', 'gtheme' ),
			'search_items'          => _x( 'Search System Tags', 'System Tag Tax Labels', 'gtheme' ),
			'all_items'             => _x( 'All System Tags', 'System Tag Tax Labels', 'gtheme' ),
			'parent_item'           => _x( 'Parent System Tag', 'System Tag Tax Labels', 'gtheme' ),
			'parent_item_colon'     => _x( 'Parent System Tag:', 'System Tag Tax Labels', 'gtheme' ),
			'edit_item'             => _x( 'Edit System Tag', 'System Tag Tax Labels', 'gtheme' ),
			'view_item'             => _x( 'View System Tag', 'System Tag Tax Labels', 'gtheme' ),
			'update_item'           => _x( 'Update System Tag', 'System Tag Tax Labels', 'gtheme' ),
			'add_new_item'          => _x( 'Add New System Tag', 'System Tag Tax Labels', 'gtheme' ),
			'new_item_name'         => _x( 'New System Tag Name', 'System Tag Tax Labels', 'gtheme' ),
			'not_found'             => _x( 'No system tags found.', 'System Tag Tax Labels', 'gtheme' ),
			'no_terms'              => _x( 'No system tags', 'System Tag Tax Labels', 'gtheme' ),
			'items_list_navigation' => _x( 'System Tags list navigation', 'System Tag Tax Labels', 'gtheme' ),
			'items_list'            => _x( 'System Tags list', 'System Tag Tax Labels', 'gtheme' ),
			'back_to_items'         => _x( '&larr; Back to System Tags', 'System Tag Tax Labels', 'gtheme' ),
		];
	}

	public static function defaults( $extra = [] )
	{
		return array_merge( [
			'dashboard'           => _x( 'Dashboard', 'System Tags Defaults', 'gtheme' ),
			'featured'            => _x( 'Featured', 'System Tags Defaults', 'gtheme' ),
			'latest'              => _x( 'Latest', 'System Tags Defaults', 'gtheme' ),
			'tile'                => _x( 'Tile', 'System Tags Defaults', 'gtheme' ),
			'full-article'        => _x( 'Full Article', 'System Tags Defaults', 'gtheme' ),
			'poster'              => _x( 'Poster Entry', 'System Tags Defaults', 'gtheme' ),
			'hide-image-single'   => _x( 'Hide Single Image', 'System Tags Defaults', 'gtheme' ),
			'disable-like-button' => _x( 'Disable Like Button', 'System Tags Defaults', 'gtheme' ),
			'no-front'            => _x( 'Not on FrontPage', 'System Tags Defaults', 'gtheme' ),
			'no-related'          => _x( 'Not on Related', 'System Tags Defaults', 'gtheme' ),
			'no-feed'             => _x( 'Not on Feed', 'System Tags Defaults', 'gtheme' ),
			'insert-people'       => _x( 'Insert People', 'System Tags Defaults', 'gtheme' ),
			'must-read'           => _x( 'Must Read!', 'System Tags Defaults', 'gtheme' ), // @SEE: https://wordpress.org/plugins/must-read/
		], $extra );
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

		return [
			'icon'  => 'admin-generic',
			'title' => _x( 'System Tags', 'System Tag Tax Labels: Menu Name', 'gtheme' ),
			'edit'  => NULL,
		];
	}

	public function admin_menu_system_tags()
	{
		if ( ! $taxonomy = get_taxonomy( GTHEME_SYSTEMTAGS ) )
			return FALSE;

		add_submenu_page(
			current_user_can( 'edit_theme_options' ) ? 'themes.php' : 'index.php',
			gThemeHTML::escape( $taxonomy->labels->name ),
			gThemeHTML::escape( $taxonomy->labels->menu_name ),
			$taxonomy->cap->manage_terms,
			'edit-tags.php?taxonomy='.$taxonomy->name
		);
	}

	public function load_edit_tags()
	{
		if ( empty( $_REQUEST['taxonomy'] ) )
			return;

		if ( GTHEME_SYSTEMTAGS == $_REQUEST['taxonomy'] ) {
			$this->system_tags_table_action( 'gtheme_action' );
			add_action( 'after-'.GTHEME_SYSTEMTAGS.'-table', [ $this, 'after_system_tags_table' ] );
		}

		$primary_taxonomy = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );

		if ( $_REQUEST['taxonomy'] == $primary_taxonomy ) {
			add_action( 'after-'.$primary_taxonomy.'-table', [ $this, 'after_category_table' ], 8 );
			add_filter( 'term_name', [ $this, 'term_name' ], 12, 2 );
		}
	}

	public function after_category_table( $taxonomy )
	{
		$primaries = gThemeOptions::getOption( 'terms', [] );

		if ( empty( $primaries ) ) {

			$desc = _x( 'Currently no primary terms are defined.', 'Modules: Terms', 'gtheme' );

		} else {

			$list = gThemeTaxonomy::listTerms( $taxonomy, NULL, [ 'include' => $primaries ] );
			/* translators: %s: terms list */
			$desc = sprintf( _x( 'Primary Terms are: %s', 'Modules: Terms', 'gtheme' ), gThemeUtilities::joinItems( $list ) );
		}

		echo '<br />';
		gThemeHTML::desc( $desc );
	}

	// FIXME: the filter is not good enough, it must be outside of the link
	public function term_name( $name, $term )
	{
		if ( ! is_object( $term ) )
			return $name;

		$primaries = gThemeOptions::getOption( 'terms', [] );

		if ( empty( $primaries ) )
			return $name;

		if ( in_array( $term->term_id, $primaries ) )
			$name.= sprintf( ' — [%s]', _x( 'Primary Term', 'Modules: Terms', 'gtheme' ) );

		return $name;
	}

	private function system_tags_table_action( $name )
	{
		if ( empty( $_REQUEST[$name] ) )
			return FALSE;

		if ( 'install_systemtags' == $_REQUEST[$name] ) {

			$defaults = gThemeOptions::info( 'system_tags_defaults', self::defaults() );
			$taxonomy = GTHEME_SYSTEMTAGS;

		} else {
			return FALSE;
		}

		if ( empty( $defaults ) )
			return FALSE;

		$action = self::insertDefaults( $taxonomy, $defaults ) ? 'added_'.$taxonomy : $action = 'error_'.$taxonomy;

		wp_redirect( add_query_arg( $name, $action ) );
		exit;
	}

	public function after_system_tags_table( $taxonomy )
	{
		$name   = 'gtheme_action';
		$title  = _x( 'Install Default System Tags', 'Modules: Terms', 'gtheme' );
		$action = add_query_arg( $name, 'install_systemtags' );

		if ( isset( $_GET[$name] ) ) {

			if ( 'error_systemtags' == $_GET[$name] )
				$title = _x( 'Error while adding default system tags.', 'Modules: Terms', 'gtheme' );

			else if ( 'added_systemtags' == $_GET[$name] )
				$title = _x( 'Default system tags added.', 'Modules: Terms', 'gtheme' );
		}

		echo '<div class="form-field"><p>';
			echo '<a href="'.esc_url( $action ).'" class="button">'.$title.'</a>';
		echo '</p></div>';
	}

	public function taxonomy_bulk_actions( $actions, $taxonomy )
	{
		if ( $taxonomy != GTHEME_SYSTEMTAGS )
			return $actions;

		return array_merge( $actions, [ 'empty_lastmonth' => _x( 'Empty Before Last Month', 'Modules: Terms', 'gtheme' ) ] );
	}

	public function taxonomy_bulk_callback( $callback, $action, $taxonomy )
	{
		if ( $taxonomy == GTHEME_SYSTEMTAGS && 'empty_lastmonth' == $action )
			return [ $this, 'bulk_empty_lastmonth' ];

		return $callback;
	}

	public function bulk_empty_lastmonth( $term_ids, $taxonomy )
	{
		if ( $taxonomy != GTHEME_SYSTEMTAGS )
			return FALSE;

		$cpt = gThemeOptions::info( 'system_tags_cpt', [ 'post' ] );

		$args = [
			'fields'         => 'ids',
			'post_type'      => $cpt,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'tax_query'      => [ [
				'taxonomy' => $taxonomy,
				'terms'    => array_filter( $term_ids, 'intval' ),
				// 'operator' => 'EXISTS',
			] ],
			'date_query' => [
				'column' => 'post_date_gmt',
				'before' => '30 days ago',
			],
			'suppress_filters'       => TRUE,
			'no_found_rows'          => TRUE,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
		];

		$query = new \WP_Query;
		$posts = $query->query( $args );
		$count = 0;

		foreach ( $posts as $post )
			foreach ( $term_ids as $term_id )
				if ( TRUE === wp_remove_object_terms( $post, (int) $term_id, $taxonomy ) )
					$count++;

		return $count;
	}

	public function p2p_init()
	{
		p2p_register_connection_type( [
			'name'       => 'posts_to_posts',
			'from'       => 'post',
			'to'         => 'post',
			'reciprocal' => TRUE,
			'title'      => __( 'Connected Posts', 'gtheme' ),
		] );
	}

	// helper for settings page
	public static function insertDefaults( $taxonomy, $defaults )
	{
		if ( ! taxonomy_exists( $taxonomy ) )
			return FALSE;

		foreach ( $defaults as $term_slug => $term_name )
			if ( ! term_exists( $term_slug, $taxonomy ) )
				wp_insert_term( $term_name, $taxonomy, [ 'slug' => $term_slug ] );

		return TRUE;
	}

	// FIXME: DEPRECATED
	public static function get( $taxonomy = 'category', $post_id = FALSE, $object = FALSE, $key = 'term_id' )
	{
		self::_dep( 'gThemeWordPress::getTerms()' );
		return gThemeWordPress::getTerms( $taxonomy, $post_id, $object, $key );
	}

	// callback for meta box for choose only tax
	// CAUTION: tax must be cat (hierarchical)
	// @SOURCE: `post_categories_meta_box()`
	public static function checklistTerms( $post, $box )
	{
		$args = self::atts( [
			'taxonomy' => 'category',
			'edit_url' => NULL,
		], empty( $box['args'] ) ? [] : $box['args'] );

		$tax  = esc_attr( $args['taxonomy'] );
		$html = wp_terms_checklist( $post->ID, [
			'taxonomy'      => $tax,
			'checked_ontop' => FALSE,
			'echo'          => FALSE,
		] );

		echo '<div id="taxonomy-'.$tax.'" class="choose-tax">';
			if ( $html ) {
				echo '<div class="wp-tab-panel"><ul>'.$html.'</ul></div>';
				// allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
				echo '<input type="hidden" name="tax_input['.$tax.'][]" value="0" />';
			} else {
				$taxonomy = get_taxonomy( $args['taxonomy'] );
				echo '<div class="field-wrap field-wrap-empty">';
					echo '<span>'.$taxonomy->labels->not_found.'</span>';
				echo '</div>';
			}
		echo '</div>';
	}

	// if no terms are given, determines if post has any terms
	public static function has( $term = '', $post = NULL, $taxonomy = GTHEME_SYSTEMTAGS )
	{
		return has_term( $term, $taxonomy, $post );
	}

	public static function getTermLink( $term, $before = '', $after = '', $text = NULL, $title = FALSE, $class = [] )
	{
		if ( is_null( $text ) )
			$text = sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' );

		return $before.gThemeHTML::tag( 'a', [
			'href'           => get_term_link( $term ),
			'class'          => gThemeHTML::attrClass( '-term-link', 'taxonomy-'.$term->taxonomy, $class ),
			'title'          => $title,
			'data-toggle'    => $title ? 'tooltip' : FALSE,
			'data-bs-toggle' => $title ? 'tooltip' : FALSE,
			'data-slug'      => $term->slug,
		], $text ).$after;
	}

	public static function getPrimary( $post = NULL )
	{
		if ( ! $post = get_post( $post ) )
			return FALSE;

		// dummy post
		if ( ! $post->ID )
			return FALSE;

		$taxonomy  = gThemeOptions::info( 'primary_terms_taxonomy', 'category' );
		$primaries = gThemeOptions::getOption( 'terms', [] );

		if ( empty( $primaries ) )
			return FALSE;

		$terms = get_terms( [
			'object_ids'             => [ $post->ID ],
			'taxonomy'               => [ $taxonomy ],
			'include'                => $primaries,
			'number'                 => 1,
			'update_term_meta_cache' => FALSE,
		] );

		if ( empty( $terms ) )
			return FALSE;

		return $terms[0];
	}

	public static function linkPrimary( $before = '', $after = '', $post = NULL, $title = '', $verbose = TRUE )
	{
		if ( ! $term = self::getPrimary( $post ) )
			return FALSE;

		$link = self::getTermLink( $term, $before, $after, NULL, $title );

		if ( ! $verbose )
			return $link;

		echo $link;

		return TRUE;
	}

	public static function imagePrimary( $before = '', $after = '', $post = NULL, $title = 'name', $verbose = TRUE )
	{
		if ( ! $term = self::getPrimary( $post ) )
			return FALSE;

		$image = gThemeImage::termImage( [ 'term_id' => $term->term_id ] );

		if ( ! $image )
			return FALSE;

		if ( 'name' == $title )
			$title = sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' );

		$link = self::getTermLink( $term, $before, $after, $image, $title );

		if ( ! $verbose )
			return $link;

		echo $link;

		return TRUE;
	}

	public static function theList( $taxonomy, $before = '', $after = '', $post = NULL, $parents_only = FALSE, $exclude_default = TRUE )
	{
		$terms = get_the_terms( $post, $taxonomy );

		if ( is_wp_error( $terms ) )
			return FALSE;

		if ( ! $terms )
			return FALSE;

		$default = gThemeTaxonomy::getDefaultTermID( $taxonomy );

		foreach ( $terms as $term ) {

			if ( $exclude_default && $default && ( $term->term_id === (int) $default ) )
				continue;

			if ( $parents_only && $term->parent )
				continue;

			echo self::getTermLink( $term, $before, $after );
		}

		return TRUE;
	}

	public static function getWithParents( $taxonomy, $post = NULL, $exclude_default = FALSE )
	{
		if ( ! $post = get_post( $post ) )
			return [];

		if ( ! is_object_in_taxonomy( $post, $taxonomy ) )
			return [];

		$terms = get_the_terms( $post, $taxonomy );

		if ( ! $terms || is_wp_error( $terms ) )
			return [];

		if ( $exclude_default && ( $default = gThemeTaxonomy::getDefaultTermID( $taxonomy ) ) ) {

			$terms = gThemeArraay::reKey( $terms, 'term_id' );
			unset( $terms[$default] );

			if ( empty( $terms ) )
				return [];
		}

		return self::getWithParentsCallback( reset( $terms ), $taxonomy );
	}

	public static function getWithParentsCallback( $parent, $taxonomy, $parents = [] )
	{
		$terms = [];

		$term = get_term( $parent, $taxonomy );

		if ( $term->parent
			&& $term->parent != $term->term_id
			&& ! in_array( $term->parent, $parents ) ) {

			$parents[] = $term->parent;
			$terms = array_merge( $terms, self::getWithParentsCallback( $term->parent, $taxonomy, $parents ) );
		}

		return array_merge( $terms, (array) self::getTermLink( $term ) );
	}

	public static function getMainTaxonomies()
	{
		$defaults = [
			'page'        => FALSE,
			'post'        => 'category',
			'entry'       => 'entry_section',
			'quote'       => 'quote_topic',
			'course'      => 'course_category',
			'place'       => 'place_category',
			'video'       => 'video_category',
			'channel'     => 'channel_category',
			'collection'  => 'collection_group',
			'publication' => 'publication_subject',
			'product'     => 'product_cat',
		];

		return array_merge( $defaults, (array) gThemeOptions::info( 'post_main_taxonomy_map', [] ) );
	}

	public static function getMainTaxonomy( $posttype, $fallback = 'category' )
	{
		if ( ! $posttype )
			return $fallback;

		$map = self::getMainTaxonomies();

		return array_key_exists( $posttype, $map ) ? $map[$posttype] : $fallback;
	}
}
