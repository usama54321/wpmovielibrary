<?php
/**
 * WPMovieLibrary
 *
 * @package   WPMovieLibrary
 * @author    Charlie MERLAND <charlie.merland@gmail.com>
 * @license   GPL-3.0
 * @link      http://www.caercam.org/
 * @copyright 2014 Charlie MERLAND
 */

/**
 * Plugin Admin class.
 *
 * @package WPMovieLibrary_Admin
 * @author  Charlie MERLAND <charlie.merland@gmail.com>
 */
class WPMovieLibrary_Admin extends WPMovieLibrary {

	/**
	 * Self.
	 * 
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * WPMovieLibrary instance.
	 * 
	 * @since    1.0.0
	 * @var      object
	 */
	protected $wpml = null;

	/**
	 * Plugin Admin URL
	 * 
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_admin_url = '';

	/**
	 * Plugin Admin URL
	 * 
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_admin_path = '';

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * TMDb API.
	 *
	 * @since    1.0.0
	 * @var      object
	 */
	protected $tmdb = null;

	/**
	 * Message display.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	public $msg_settings = '';

	/**
	 * Initialize WPMovieLibrary.
	 * 
	 * i18n calls are absolutely useless here since _*() functions are called
	 * in views, but the calls are kept here to generate working pot files.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$this->wpml = WPMovieLibrary::get_instance();

		$this->plugin_admin_url  = plugins_url( WPMovieLibrary::NAME );
		$this->plugin_admin_path = plugin_dir_path( __FILE__ );

		require_once $this->plugin_admin_path . 'includes/class-wpmltmdb.php';
		require_once $this->plugin_admin_path . 'includes/class-wpmllisttable.php';

		$this->plugin_screen_hook_suffix = array(
			'movie_page_import', 'movie_page_settings', 'edit-movie', 'movie', 'plugins'
		);

		// Load TMDb API Class
		$this->tmdb = new WPML_TMDb( $this->wpml_o('tmdb-settings') );

		// Movie poster in admin movies list
		add_filter('manage_movie_posts_columns', array( $this, 'wpml_movies_columns_head' ) );
		add_action('manage_movie_posts_custom_column', array( $this, 'wpml_movies_columns_content' ), 10, 2 );

		// Add Movies Details to Quick/Bulk Edit
		add_action('quick_edit_custom_box', array( $this, 'wpml_quick_edit_movies' ), 10, 2);
		add_action('bulk_edit_custom_box', array( $this, 'wpml_bulk_edit_movies' ), 10, 2);

		// Load QuickEdit values
		add_filter('post_row_actions', array( $this, 'wpml_expand_quick_edit_link' ), 10, 2);

		// Notice missing API key
		add_action( 'admin_notices', array( $this, 'wpml_activate_notice' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'wpml_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// New Movie metaboxes
		add_action( 'add_meta_boxes', array( $this, 'wpml_metaboxes' ) );

		// Post Thumbnail metabox: Import all Posters
		add_action( 'admin_post_thumbnail_html', array( $this, 'wpml_load_posters' ), 10, 2 );

		// Movie save
		add_action( 'save_post_movie', array( $this, 'wpml_save_tmdb_data' ) );

		// register widgets
		// add_action( 'widgets_init', array( $this, 'wpml_widgets' ) );

		// Ajax callbacks
		add_action( 'wp_ajax_wpml_save_details', array( $this, 'wpml_save_details_callback' ) );
		add_action( 'wp_ajax_wpml_delete_movie', array( $this, 'wpml_delete_movie_callback' ) );
		add_action( 'wp_ajax_tmdb_save_image', array( $this, 'wpml_save_image_callback' ) );
		add_action( 'wp_ajax_tmdb_set_featured', array( $this, 'wpml_set_featured_image_callback' ) );

		add_action( 'wp_ajax_tmdb_search', array( $this->tmdb, 'wpml_tmdb_search_callback' ) );
		add_action( 'wp_ajax_tmdb_api_key_check', array( $this->tmdb, 'wpml_tmdb_api_key_check_callback' ) );
		add_action( 'wp_ajax_tmdb_load_images', array( $this->tmdb, 'wpml_tmdb_load_images_callback' ) );

		add_action( 'load-movie_page_import', array( $this, 'wpml_import_movie_list_add_options' ) );
		add_filter( 'set-screen-option', array( $this, 'wpml_import_movie_list_set_option' ), 10, 3 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_slug .'-admin-common', plugins_url( 'assets/css/admin-common.css', __FILE__ ), array(), WPMovieLibrary_Admin::VERSION );

		$screen = get_current_screen();
		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) )
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), WPMovieLibrary_Admin::VERSION );

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix ) ) {

			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'jquery-ui-tabs' );


			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-progressbar', 'jquery-ui-tabs' ), WPMovieLibrary_Admin::VERSION );
			wp_localize_script(
				$this->plugin_slug . '-admin-script', 'ajax_object',
				array(
					'ajax_url'           => admin_url( 'admin-ajax.php' ),
					'wpml_check'         => wp_create_nonce( 'wpml-callbacks-nonce' ),
					'images_added'       => __( 'Images uploaded!', 'wpml' ),
					'base_url_xxsmall'   => $this->tmdb->wpml_tmdb_get_base_url( 'poster', 'xx-small' ),
					'base_url_xsmall'    => $this->tmdb->wpml_tmdb_get_base_url( 'poster', 'x-small' ),
					'base_url_small'     => $this->tmdb->wpml_tmdb_get_base_url( 'image', 'small' ),
					'base_url_medium'    => $this->tmdb->wpml_tmdb_get_base_url( 'image', 'medium' ),
					'base_url_full'      => $this->tmdb->wpml_tmdb_get_base_url( 'image', 'full' ),
					'base_url_original'  => $this->tmdb->wpml_tmdb_get_base_url( 'image', 'original' ),
					'search_movie_title' => __( 'Searching movie', 'wpml' ),
					'search_movie'       => __( 'Fetching movie data', 'wpml' ),
					'set_featured'       => __( 'Setting featured image…', 'wpml' ),
					'images_added'       => __( 'Images added!', 'wpml' ),
					'image_from'         => __( 'Image from', 'wpml' ),
					'load_images'        => __( 'Load Images', 'wpml' ),
					'load_more'          => __( 'Load More', 'wpml' ),
					'loading_images'     => __( 'Loading Images…', 'wpml' ),
					'save_image'         => __( 'Saving Images…', 'wpml' ),
					'poster'             => __( 'Poster', 'wpml' ),
					'done'               => __( 'Done!', 'wpml' ),
					'see_more'           => __( 'see more', 'wpml' ),
					'see_less'           => __( 'see no more', 'wpml' ),
					'oops'               => __( 'Oops… Did something went wrong?', 'wpml' )
				)
			);
		}

	}

	/**
	 * Load TMDb Class
	 *
	 * @since    1.0.0
	 */
	public function wpml_init_tmdb() {

		$dummy = ( 1 == $this->wpml_o( 'tmdb-settings-dummy' ) ? true : false );
		$tmdb  = new WPML_TMDb( $this->wpml_o('tmdb-settings'), $dummy );

		return $tmdb;
	}

	/**
	 * Get the movie's featured image.
	 * If a poster was uploaded and set as featured image for the moive's
	 * post, return the image URL. If no featured image is set, return the
	 * default poster.
	 *
	 * @since     1.0.0
	 * 
	 * @param     int       $post_id The movie's post ID
	 *
	 * @return    string    Featured image URL
	 */
	public function wpml_get_featured_image( $post_id, $size = 'thumbnail' ) {
		$_id = get_post_thumbnail_id( $post_id );
		$img = ( $_id ? wp_get_attachment_image_src( $_id, $size ) : array( $this->plugin_admin_url . '/admin/assets/img/no_poster.png' ) );
		return $img[0];
	}

	/**
	 * Add a custom column to Movies WP_List_Table list.
	 * Insert a simple 'Poster' column to Movies list table to display
	 * movies' poster set as featured image if available.
	 * 
	 * @since     1.0.0
	 * 
	 * @param     array    Default WP_List_Table header columns
	 * 
	 * @return    array    Default columns with new poster column
	 */
	public function wpml_movies_columns_head( $defaults ) {

		$title = array_search( 'title', array_keys( $defaults ) );
		$comments = array_search( 'comments', array_keys( $defaults ) ) - 1;

		$defaults = array_merge(
			array_slice( $defaults, 0, $title, true ),
			array( 'poster' => __( 'Poster', 'wpml' ) ),
			array_slice( $defaults, $title, $comments, true ),
			array( 'movie_status' => __( 'Status', 'wpml' ) ),
			array( 'movie_media' => __( 'Media', 'wpml' ) ),
			array( 'movie_rating' => __( 'Rating', 'wpml' ) ),
			array_slice( $defaults, $comments, count( $defaults ), true )
		);

		unset( $defaults['author'] );
		return $defaults;
	}

	/**
	 * Add a custom column to Movies WP_List_Table list.
	 * Insert movies' poster set as featured image if available.
	 * 
	 * @since     1.0.0
	 * 
	 * @param     string   $column_name The column name
	 * @param     int      $post_id current movie's post ID
	 */
	public function wpml_movies_columns_content( $column_name, $post_id ) {

		switch ( $column_name ) {
			case 'poster':
				$html = '<img src="'.$this->wpml_get_featured_image( $post_id ).'" alt="" />';
				break;
			case 'movie_status':
			case 'movie_media':
				$meta = get_post_meta( $post_id, '_wpml_' . $column_name, true );
				if ( isset( $this->wpml->wpml_movie_details[ $column_name ]['options'][ $meta ] ) )
					$html = $this->wpml->wpml_movie_details[ $column_name ]['options'][ $meta ];
				else
					$html = '&mdash;';
				break;
			case 'movie_rating':
				$meta = get_post_meta( $post_id, '_wpml_movie_rating', true );
				if ( '' != $meta )
					$html = '<div id="movie-rating-display" class="stars_' . str_replace( '.', '_', $meta ) . '"></div>';
				else
					$html = '<div id="movie-rating-display" class="stars_0_0"></div>';
				break;
			default:
				$html = '';
				break;
		}

		echo $html;
	}

	/**
	 * Add new fields to Movies' Quick Edit form in Movies Lists to edit
	 * Movie Details directly from the list.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    $column_name WP List Table Column name
	 * @param    string    $post_type Post type
	 */
	public function wpml_quick_edit_movies( $column_name, $post_type ) {

		if ( 'movie' != $post_type || 'poster' != $column_name || 1 !== did_action( 'quick_edit_custom_box' ) )
			return false;

		$this->wpml_quickbulk_edit( 'quick' );
	}

	/**
	 * Add new fields to Movies' Bulk Edit form in Movies Lists.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    $column_name WP List Table Column name
	 * @param    string    $post_type Post type
	 */
	public function wpml_bulk_edit_movies( $column_name, $post_type ) {

		if ( 'movie' != $post_type || 'poster' != $column_name || 1 !== did_action( 'bulk_edit_custom_box' ) )
			return false;

		$this->wpml_quickbulk_edit( 'bulk' );
	}

	/**
	 * Generic function to show WPML Quick/Bulk Edit form.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    $type Form type, 'quick' or 'bulk'.
	 */
	private function wpml_quickbulk_edit( $type ) {

		if ( ! in_array( $type, array( 'quick', 'bulk' ) ) )
			return false;

		$default_movie_media = $this->wpml->wpml_get_available_movie_media();
		$default_movie_status = $this->wpml->wpml_get_available_movie_status();

		$check = 'is_' . $type . 'edit';

		$nonce_name = 'wpml_' . $type . 'edit_movie_details_nonce';
		$nonce = wp_create_nonce( '_wpml_' . $type . 'edit_movie_details' );

		include( 'views/quick-edit.php' );
	}

	/**
	 * Alter the Quick Edit link in Movies Lists to update the Movie Details
	 * current values.
	 * 
	 * TODO: group Details in a single, cached query.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array     $actions List of current actions
	 * @param    object    $post Current Post object
	 * 
	 * @return   string    Edited Post Actions
	 */
	public function wpml_expand_quick_edit_link( $actions, $post ) {

		global $current_screen;

		if ( isset( $current_screen ) && ( ( $current_screen->id != 'edit-movie' ) || ( $current_screen->post_type != 'movie' ) ) )
			return $actions;

		$nonce = wp_create_nonce( '_wpml_movie_details' );

		$details = '{';
		$details .= 'movie_id: ' . $post->ID . ',';
		$details .= 'movie_media: \'' . get_post_meta( $post->ID, '_wpml_movie_media', TRUE ) . '\',';
		$details .= 'movie_status: \'' . get_post_meta( $post->ID, '_wpml_movie_status', TRUE ) . '\',';
		$details .= 'movie_rating: \'' . get_post_meta( $post->ID, '_wpml_movie_rating', TRUE ) . '\'';
		$details .= '}';

		$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';
		$actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '" ';
		$actions['inline hide-if-no-js'] .= " onclick=\"wpml.movie.populate_quick_edit({$details}, '{$nonce}')\">"; 
		$actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit' );
		$actions['inline hide-if-no-js'] .= '</a>';

		return $actions;
	}

	/**
	 * Add a Screen Option panel on Movie Import Page.
	 *
	 * @since     1.0.0
	 */
	public function wpml_import_movie_list_add_options() {

		$option = 'per_page';
		$args = array(
			'label'   => __( 'Import Drafts', 'wpml' ),
			'default' => 30,
			'option'  => 'drafts_per_page'
		);

		add_screen_option( $option, $args );
	}

	/**
	 * Save newly set Movie Drafts number in Movie Import Page.
	 *
	 * @since     1.0.0
	 */
	public function wpml_import_movie_list_set_option( $status, $option, $value ) {
		return $value;
	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                             Callbacks
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Delete movie
	 * 
	 * Remove imported movies draft and attachment from database
	 *
	 * @since     1.0.0
	 * 
	 * @return     boolean     deletion status
	 */
	public function wpml_delete_movie_callback() {

		check_ajax_referer( 'wpml-callbacks-nonce', 'wpml_check' );

		$post_id = ( isset( $_GET['post_id'] ) && '' != $_GET['post_id'] ? $_GET['post_id'] : '' );

		echo $this->wpml_delete_movie( $post_id );
		die();
	}

	/**
	 * Save movie details: media, status, rating.
	 * 
	 * Although values are submitted as array each value is stored in a
	 * dedicated post meta.
	 *
	 * @since     1.0.0
	 */
	public function wpml_save_details_callback() {

		check_ajax_referer( 'wpml-callbacks-nonce', 'wpml_check' );

		$post_id      = ( isset( $_POST['post_id'] )      && '' != $_POST['post_id']      ? $_POST['post_id']      : '' );
		$wpml_details = ( isset( $_POST['wpml_details'] ) && '' != $_POST['wpml_details'] ? $_POST['wpml_details'] : '' );

		if ( '' == $post_id || '' == $wpml_details )
			return false;

		$post = get_post( $post_id );
		if ( 'movie' != get_post_type( $post ) )
			return false;

		update_post_meta( $post_id, '_wpml_movie_media', $wpml_details['media'] );
		update_post_meta( $post_id, '_wpml_movie_status', $wpml_details['status'] );
		update_post_meta( $post_id, '_wpml_movie_rating', $wpml_details['rating'] );
	}

	/**
	 * Upload a movie image.
	 * 
	 * Extract params from $_POST values. Image URL and post ID are
	 * required, title is optional. If no title is submitted file's
	 * basename will be used as image name.
	 *
	 * @since     1.0.0
	 * 
	 * @param string $image Image url
	 * @param int $post_id ID of the post the image will be attached to
	 * @param string $title Post title to use as image title to avoir crappy TMDb images names.
	 *
	 * @return    string    Uploaded image ID
	 */
	public function wpml_save_image_callback() {

		check_ajax_referer( 'wpml-callbacks-nonce', 'wpml_check' );

		$image   = ( isset( $_GET['image'] )   && '' != $_GET['image']   ? $_GET['image']   : '' );
		$post_id = ( isset( $_GET['post_id'] ) && '' != $_GET['post_id'] ? $_GET['post_id'] : '' );
		$title   = ( isset( $_GET['title'] )   && '' != $_GET['title']   ? $_GET['title']   : '' );
		$tmdb_id = ( isset( $_GET['tmdb_id'] ) && '' != $_GET['tmdb_id'] ? $_GET['tmdb_id'] : '' );

		if ( ! is_array( $image ) || '' == $post_id )
			return false;

		echo $this->wpml_image_upload( $image['file_path'], $post_id, $tmdb_id, $title, $image );
		die();
	}

	/**
	 * Upload an image and set it as featured image of the submitted post.
	 * 
	 * Extract params from $_POST values. Image URL and post ID are
	 * required, title is optional. If no title is submitted file's
	 * basename will be used as image name.
	 * 
	 * Return the uploaded image ID to updated featured image preview in
	 * editor.
	 *
	 * @since     1.0.0
	 * 
	 * @param string $image Image url
	 * @param int $post_id ID of the post the image will be attached to
	 * @param string $title Post title to use as image title to avoir crappy TMDb images names.
	 *
	 * @return    string    Uploaded image ID
	 */
	public function wpml_set_featured_image_callback() {

		check_ajax_referer( 'wpml-callbacks-nonce', 'wpml_check' );

		$image   = ( isset( $_GET['image'] )   && '' != $_GET['image']   ? $_GET['image']   : '' );
		$post_id = ( isset( $_GET['post_id'] ) && '' != $_GET['post_id'] ? $_GET['post_id'] : '' );
		$title   = ( isset( $_GET['title'] )   && '' != $_GET['title']   ? $_GET['title']   : '' );
		$tmdb_id = ( isset( $_GET['tmdb_id'] ) && '' != $_GET['tmdb_id'] ? $_GET['tmdb_id'] : '' );

		if ( '' == $image || '' == $post_id || 1 != $this->wpml_o('tmdb-settings-poster_featured') )
			return false;

		echo $this->wpml_set_image_as_featured( $image, $post_id, $tmdb_id, $title );
		die();
	}


	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                             Meta Boxes
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Register WPML Metaboxes
	 * 
	 * @since    1.0.0
	 */
	public function wpml_metaboxes() {
		add_meta_box( 'tmdbstuff', __( 'TMDb − The Movie Database', 'wpml' ), array( $this, 'wpml_metabox_tmdb' ), 'movie', 'normal', 'high', null );
		add_meta_box( 'wpml', __( 'Movie Library − Details', 'wpml' ), array( $this, 'wpml_metabox_details' ), 'movie', 'side', 'default', null );
	}

	/**
	 * Main Metabox: TMDb API results.
	 * Display a large Metabox below post editor to fetch and edit movie
	 * informations using the TMDb API.
	 * 
	 * @since    1.0.0
	 */
	public function wpml_metabox_tmdb( $post, $metabox ) {

		$value = get_post_meta( $post->ID, '_wpml_movie_data', true );
		$value = apply_filters( 'wpml_filter_empty_array', $value );

		if ( isset( $_REQUEST['wpml_auto_fetch'] ) && ( empty( $value ) || isset( $value['_empty'] ) ) )
			$value = $this->tmdb->_wpml_get_movie_by_title( $post->post_title, $this->wpml_o( 'tmdb-settings-lang' ) );

		include_once( 'views/metabox-tmdb.php' );
	}

	/**
	 * Left side Metabox: Movie details.
	 * Used to handle Movies-related details.
	 * 
	 * @since    1.0.0
	 */
	public function wpml_metabox_details( $post, $metabox ) {

		$v = get_post_meta( $post->ID, '_wpml_movie_status', true );
		$movie_status = ( isset( $v ) && '' != $v ? $v : key( $this->wpml->wpml_movie_details['movie_status']['default'] ) );

		$v = get_post_meta( $post->ID, '_wpml_movie_media', true );
		$movie_media  = ( isset( $v ) && '' != $v ? $v : key( $this->wpml->wpml_movie_details['movie_media']['default'] ) );

		$v = get_post_meta( $post->ID, '_wpml_movie_rating', true );
		$movie_rating = ( isset( $v ) && '' != $v ? number_format( $v, 1 ) : 0.0 );
		$movie_rating_str = str_replace( '.', '_', $movie_rating );

		include_once( 'views/metabox-details.php' );
	}

	/**
	 * Add a link to the current Post's Featured Image Metabox to trigger
	 * a Modal window. This will be used by the future Movie Posters
	 * selection Modal, yet to be implemented.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    $content Current Post's Featured Image Metabox
	 *                              content, ready to be edited.
	 * @param    string    $post_id Current Post's ID (unused at that point)
	 * 
	 * @return   string    Updated $content
	 */
	public function wpml_load_posters( $content, $post_id ) {
		//return $content . '<a id="tmdb_load_posters" href="http://wpthemes/wp-admin/media-upload.php?post_id=3272&amp;type=image&amp;TB_iframe=1" class="thickbox">' . __( 'Load available Movie Posters', 'wpml' ) . '</a>';
		return $content;
	}


	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                     Settings, Import/Export Pages
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Register the administration menu for this plugin into the WordPress
	 * Dashboard menu.
	 * 
	 * TODO: export support
	 *
	 * @since    1.0.0
	 */
	public function wpml_admin_menu() {

		add_submenu_page(
			'edit.php?post_type=movie',
			__( 'Import Movies', 'wpml' ),
			__( 'Import Movies', 'wpml' ),
			'manage_options',
			'import',
			array( $this, 'wpml_import_page' )
		);
		/*add_submenu_page(
			'edit.php?post_type=movie',
			__( 'Export Movies', 'wpml' ),
			__( 'Export Movies', 'wpml' ),
			'manage_options',
			'export',
			array( $this, 'wpml_export_page' )
		);*/
		add_submenu_page(
			'edit.php?post_type=movie',
			__( 'Options', 'wpml' ),
			__( 'Options', 'wpml' ),
			'manage_options',
			'settings',
			array( $this, 'wpml_admin_page' )
		);
	}

	/**
	 * Render options page.
	 *
	 * @since    1.0.0
	 */
	public function wpml_admin_page() {

		$errors = array();
		$_section = '';

		if ( isset( $_POST['restore_default'] ) && '' != $_POST['restore_default'] ) {

			check_admin_referer('wpml-admin');

			if ( 0 === did_action( 'wpml_restore_default_settings' ) )
				do_action( 'wpml_restore_default_settings' );
			$this->msg_settings = __( 'Default Settings have been restored.', 'wpml' );
		}

		if ( isset( $_POST['submit'] ) && '' != $_POST['submit'] ) {

			check_admin_referer('wpml-admin');

			if ( isset( $_POST['tmdb_data']['wpml'] ) && '' != $_POST['tmdb_data']['wpml'] ) {

				$supported = array_keys( $this->wpml_o( 'wpml-settings' ) );
				foreach ( $_POST['tmdb_data']['wpml'] as $key => $setting ) {
					if ( in_array( $key, $supported ) ) {
						if ( is_array( $setting ) )
							$this->wpml_o( 'wpml-settings-'.esc_attr( $key ), $setting );
						else
							$this->wpml_o( 'wpml-settings-'.esc_attr( $key ), esc_attr( $setting ) );
					}
				}
			}

			if ( isset( $_POST['tmdb_data']['tmdb'] ) && '' != $_POST['tmdb_data']['tmdb'] ) {

				$supported = array_keys( $this->wpml_o( 'tmdb-settings' ) );
				foreach ( $_POST['tmdb_data']['tmdb'] as $key => $setting ) {
					if ( in_array( $key, $supported ) ) {
						$this->wpml_o( 'tmdb-settings-'.esc_attr( $key ), esc_attr( $setting ) );
					}
				}
			}

			if ( empty( $errors ) )
				$this->msg_settings = __( 'Settings saved.', 'wpml' );

		}

		if ( isset( $_REQUEST['wpml_section'] ) && in_array( $_REQUEST['wpml_section'], array( 'tmdb', 'wpml', 'uninstall', 'restore' ) ) )
			$_section =  $_REQUEST['wpml_section'];

		include_once( 'views/admin.php' );
	}

	/**
	 * Render movie import page
	 *
	 * @since    1.0.0
	 */
	public function wpml_import_page() {

		$errors = array();

		if ( isset( $_POST['wpml_save_imported'] ) && '' != $_POST['wpml_save_imported'] && isset( $_POST['tmdb'] ) && count( $_POST['tmdb'] ) ) {

			check_admin_referer('wpml-movie-save-import');

			foreach ( $_POST['tmdb'] as $tmdb_data ) {
				if ( 0 != $tmdb_data['tmdb_id'] ) {
					$this->wpml_save_tmdb_data( $tmdb_data['post_id'], $tmdb_data );
				}
			}

			if ( empty( $errors ) )
				$this->msg_settings = sprintf( __( '%d Movies imported successfully!', 'wpml' ), count( $_POST['tmdb'] ) );
		}

		include_once( 'views/import.php' );
	}

	/**
	 * Render movie export page
	 *
	 * @since    1.0.0
	 */
	public function wpml_export_page() {
		// TODO: implement export
		// include_once( 'views/export.php' );
	}


	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                             Methods
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Get number of existing Collections.
	 * 
	 * @since    1.0.0
	 * 
	 * @return   int    Total count of Collections
	 */
	public function wpml_get_collection_count() {
		$c = get_terms( array( 'collection' ) );
		return ( isset( $c[0]->count ) && '' != $c[0]->count ? $c[0]->count : 0 );
	}

	/**
	 * Get number of existing Movies.
	 * 
	 * @since    1.0.0
	 * 
	 * @return   int    Total count of Movies
	 */
	public function wpml_get_movie_count() {
		$c = get_posts( array( 'posts_per_page' => -1, 'post_type' => 'movie' ) );
		return count( $c );
	}

	/**
	 * Get all available movies.
	 * 
	 * @since    1.0.0
	 * 
	 * @return   array    Movie list
	 */
	public function wpml_get_movies() {

		$movies = array();

		query_posts( array(
			'posts_per_page' => -1,
			'post_type'   => 'movie'
		) );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$movie = array(
					'id'     => get_the_ID(),
					'title'  => get_the_title(),
					'url'    => get_permalink(),
					'poster' => $this->wpml_get_featured_image( get_the_ID(), 'medium' )
				);

				$tmdb_data = get_post_meta( get_the_ID(), '_wpml_movie_data', true );
				if ( '' != $tmdb_data ) {
					$movie['genres']   = $tmdb_data['genres'];
					$movie['runtime']  = $tmdb_data['runtime'];
					$movie['overview'] = $tmdb_data['overview'];
				}

				$movies[] = $movie;
			}
		}

		return $movies;

	}

	/**
	 * Get all the imported images related to current movie and format them
	 * to be showed in the Movie Edit page. Featured image (most likely the
	 * movie poster) is excluded from the list.
	 * 
	 * @since    1.0.0
	 * 
	 * @return   array    Movie list
	 */
	public function wpml_get_movie_imported_images() {

		global $post;

		if ( 'movie' != get_post_type() )
			return false;

		$html = '';

		$args = array(
			'post_type'   => 'attachment',
			'orderby'     => 'title',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => get_the_ID(),
			'exclude'     => get_post_thumbnail_id()
		);

		$attachments = get_posts( $args );

		if ( $attachments )
			foreach ( $attachments as $attachment )
				$html .= '<div class="tmdb_movie_images tmdb_movie_imported_image"><a href="' . get_edit_post_link( $attachment->ID ) . '">' . wp_get_attachment_image( $attachment->ID, 'medium' ) . '</a></div>';

		return $html;
	}

	/**
	 * Set the image as featured image.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    int    $image The ID of the image to set as featured
	 * @param    int    $post_id The post ID the image is to be associated with
	 * 
	 * @return   string|WP_Error Populated HTML img tag on success
	 */
	private function wpml_set_image_as_featured( $image, $post_id, $tmdb_id, $title ) {

		$size = $this->wpml_o('tmdb-settings-poster_size');
		$file = $this->tmdb->config['poster_url'][ $size ] . $image;

		$existing = apply_filters( 'wpml_check_for_existing_images', $tmdb_id, 'poster' );

		if ( false !== $existing )
			return $existing;

		$image = $this->wpml_image_upload( $file, $post_id, $tmdb_id, $title, 'poster' );

		if ( is_object( $image ) )
			return false;
		else
			return $image;
	}

	/**
	 * Media Sideload Image revisited
	 * This is basically an override function for WP media_sideload_image
	 * modified to return the uploaded attachment ID instead of HTML img
	 * tag.
	 * 
	 * @see http://codex.wordpress.org/Function_Reference/media_sideload_image
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    $file The URL of the image to download
	 * @param    int       $post_id The post ID the media is to be associated with
	 * @param    string    $title Optional. Title of the image
	 * 
	 * @return   string|WP_Error Populated HTML img tag on success
	 */
	private function wpml_image_upload( $file, $post_id, $tmdb_id, $title, $image_type = 'image', $data = null ) {

	        if ( empty( $file ) )
			return false;

		if ( ! in_array( $image_type, array( 'image', 'poster' ) ) )
			$image_type = 'image';

		$size = $this->wpml_o('tmdb-settings-images_size');
		$path = $this->tmdb->config["{$image_type}_url"][ $size ];

		if ( is_array( $file ) ) {
			$data = $file;
			$file = $path . $file['file_path'];
			$image = $file['file_path'];
		}
		else {
			$image = $file;
			$file = $path . $file;
		}

		$image = substr( $image, 1 );

		$existing = $this->wpml_check_for_existing_images( $tmdb_id, $image_type, $image );

		if ( false !== $existing )
			return $existing;

		$tmp = download_url( $file );

		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
		$file_array['name'] = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;

		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}

		$id = media_handle_sideload( $file_array, $post_id, $title );
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return print_r( $id, true );
		}

		update_post_meta( $id, '_wpml_' . $image_type . '_related_tmdb_id', $tmdb_id );
		update_post_meta( $id, '_wpml_' . $image_type . '_related_tmdb_data', $data );

		return $id;
	}

	/**
	 * Import movies
	 *
	 * @since     1.0.0
	 * 
	 * @return    array      Movies and related Meta
	 */
	public function wpml_import_movie_list() {

		$this->wpml_import_movies();

		$movies = $this->wpml_get_imported_movies();
		$meta   = $this->wpml->wpml_movie_meta;

		return array( 'movies' => $movies, 'meta' => $meta );
	}

	/**
	 * Display a custom WP_List_Table of imported movies
	 *
	 * @since     1.0.0
	 * 
	 * @param     array     $movies Array of imported movies
	 * @param     array     $meta Array of imported movies' metadata
	 */
	public function wpml_display_import_movie_list() {

		$movies = $this->wpml_import_movie_list();

		$list = new WPML_List_Table( $movies['movies'], $movies['meta'] );
		$list->prepare_items();
?>
			<form method="post">
				<input type="hidden" name="page" value="import" />

<?php
		$list->search_box('search', 'search_id'); 
		$list->display();

?>
			</form>
<?php
	}

	/**
	 * Process the submitted movie list
	 *
	 * @since     1.0.0
	 * 
	 * @return     boolean     false on failure, true else
	 */
	public function wpml_import_movies() {

		$errors = array();

		if ( ! isset( $_POST['wpml_import_list'] ) || '' == $_POST['wpml_import_list'] )
			return false;

		check_admin_referer('wpml-movie-import');

		$movies = explode( ',', $_POST['wpml_import_list'] );
		$movies = array_map( array( $this, 'wpml_prepare_movie_import' ), $movies );

		foreach ( $movies as $i => $movie ) {
			$import = $this->wpml_import_movie( $movie['movietitle'] );
			if ( is_string( $import ) ) {
				$errors[] = $import;
			}
		}

		// @TODO: i18n plural
		if ( empty( $errors ) )
			$msg = sprintf( __( '%d Movie%s added successfully.', 'wpml' ), count( $movies ), ( count( $movies ) > 1 ? 's' : '' ) );
		else if ( ! empty( $errors ) )
			$msg = sprintf( '<strong>%s</strong> <ul>%s</ul>', __( 'The following error(s) occured:', 'wpml' ), implode( '', array_map( create_function( '&$e', 'return "<li>$e</li>";' ), $errors ) ) );

		$this->msg_settings = $msg;

		return true;
	}

	/**
	 * Save a temporary 'movie' post type for submitted title.
	 * 
	 * This is used to save movies submitted from a list before any
	 * alteration is made by user. Posts will be kept as 'import-draft'
	 * for 24 hours and then destroyed on the next plugin init.
	 *
	 * @since     1.0.0
	 * 
	 * @param     string     $title Movie title.
	 * 
	 * @return    int        Newly created post ID if everything worked, 0 if no post created.
	 */
	private function wpml_import_movie( $title ) {

		$post_date     = current_time('mysql');
		$post_date     = wp_checkdate( substr( $post_date, 5, 2 ), substr( $post_date, 8, 2 ), substr( $post_date, 0, 4 ), $post_date );
		$post_date_gmt = get_gmt_from_date( $post_date );
		$post_author   = get_current_user_id();
		$post_content  = null;
		$post_title    = apply_filters( 'the_title', $title );

		$page = get_page_by_title( $post_title, OBJECT, 'movie' );

		if ( ! is_null( $page ) ) {

			return sprintf(
				'%s − <span class="edit"><a href="%s">%s</a> |</span> <span class="view"><a href="%s">%s</a></span>',
				sprintf( __( 'Movie "%s" already imported.', 'wpml' ), "<em>" . get_the_title( $page->ID ) . "</em>" ),
				get_edit_post_link( $page->ID ),
				__( 'Edit', 'wpml' ),
				get_permalink( $page->ID ),
				__( 'View', 'wpml' )
			);
		}
		else {
			$_ID = '';
		}

		$_post = array(
			'ID'             => $_ID,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => $post_author,
			'post_content'   => $post_content,
			'post_date'      => $post_date,
			'post_date_gmt'  => $post_date_gmt,
			'post_name'      => sanitize_title( $post_title ),
			'post_status'    => 'import-draft',
			'post_title'     => $post_title,
			'post_type'      => 'movie'
		);

		$id = wp_insert_post( $_post, true );

		if ( is_wp_error( $id ) )
			return $id->get_error_message();
		else
			return $id;
	}

	/**
	 * Delete imported movie
	 * 
	 * Triggered by the 'Delete' link on imported movies WP_List_Table.
	 * Delete the specified movie from the list of movie set for further
	 * import. Automatically delete attached images such as featured image.
	 *
	 * @since     1.0.0
	 * 
	 * @param     int    $post_id    Movie's post ID.
	 * 
	 * @return    string    Error status if post/attachment delete failed
	 */
	public function wpml_delete_movie( $post_id ) {

		if ( false === wp_delete_post( $post_id, true ) )
			return vsprintf( __( 'An error occured trying to delete Post #%s', 'wpml' ), $post_id );

		$thumb_id = get_post_thumbnail_id( $post_id );

		if ( '' != $thumb_id )
			if ( false === wp_delete_attachment( $thumb_id ) )
				return vsprintf( __( 'An error occured trying to delete Attachment #%s', 'wpml' ), $thumb_id );

		return true;
	}

	/**
	 * Set the default values for imported movies list
	 *
	 * @since     1.0.0
	 * 
	 * @param     string    $title    Movie title
	 * 
	 * @return    array    Default movie values
	 */
	public function wpml_prepare_movie_import( $title ) {
		return array(
			'ID'         => 0,
			'poster'     => '--',
			'movietitle' => $title,
			'director'   => '--',
			'tmdb_id'    => '--'
		);
	}

	/**
	 * Get previously imported movies.
	 * 
	 * Fetch all posts with 'import-draft' status and 'movie' post type
	 *
	 * @since     1.0.0
	 * 
	 * @param     string    $title    Movie title
	 * 
	 * @return    array    Default movie values
	 */
	public function wpml_get_imported_movies() {

		$columns = array();

		$args = array(
			'posts_per_page' => -1,
			'post_type'   => 'movie',
			'post_status' => 'import-draft'
		);

		query_posts( $args );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				if ( 'import-draft' == get_post_status() ) {
					$columns[ get_the_ID() ] = array(
						'ID'         => get_the_ID(),
						'poster'     => get_post_meta( get_the_ID(), '_wp_attached_file', true ),
						'movietitle' => get_the_title(),
						'director'   => get_post_meta( get_the_ID(), '_wpml_tmdb_director', true ),
						'tmdb_id'    => get_post_meta( get_the_ID(), '_wpml_tmdb_id', true )
					);
				}
			}
		}

		//array_unique( $columns );

		return $columns;
	}

	/**
	 * Clean movie title prior to search.
	 * 
	 * Remove non-alphanumerical characters.
	 *
	 * @since     1.0.0
	 * 
	 * @param     string     $query movie title to clean up
	 * 
	 * @return     string     cleaned up movie title
	 */
	public function wpml_clean_search_title( $query ) {
		$s = trim( $query );
		$s = preg_replace( '/[^\p{L}\p{N}\s]/u', '', $s );
		return $s;
	}

	/**
	 * Save TMDb fetched data.
	 *
	 * @since     1.0.0
	 */
	public function wpml_save_tmdb_data( $post_id, $tmdb_data = null ) {

		if ( ! $post = get_post( $post_id ) || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || 'movie' != get_post_type( $post ) || ! current_user_can( 'edit_post', $post_id ) )
			return false;

		if ( ! is_null( $tmdb_data ) && count( $tmdb_data ) ) {

			$tmdb_data = apply_filters( 'wpml_filter_empty_array', $tmdb_data );

			// Save TMDb data
			update_post_meta( $post_id, '_wpml_movie_data', $tmdb_data );

			// Set poster as featured image
			$id = $this->wpml_set_image_as_featured( $tmdb_data['poster'], $post_id, $tmdb_data['tmdb_id'], $tmdb_data['meta']['title'] );
			update_post_meta( $post_id, '_thumbnail_id', $id );

			// Switch status from import draft to published
			if ( 'import-draft' == get_post_status( $post_id ) ) {
				$update = wp_update_post( array(
					'ID' => $post_id,
					'post_name'   => sanitize_title_with_dashes( $tmdb_data['meta']['title'] ),
					'post_status' => 'publish',
					'post_title'  => $tmdb_data['meta']['title'],
					'post_date'   => current_time( 'mysql' )
				) );
			}

			// Autofilling Taxonomy
			if ( 1 == $this->wpml_o( 'wpml-settings-taxonomy_autocomplete' ) ) {

				if ( 1 == $this->wpml_o( 'wpml-settings-enable_actor' ) ) {
					$actors = explode( ',', $tmdb_data['crew']['cast'] );
					$actors = wp_set_object_terms( $post_id, $actors, 'actor', false );
				}

				if ( 1 == $this->wpml_o( 'wpml-settings-enable_genre' ) ) {
					$genres = explode( ',', $tmdb_data['meta']['genres'] );
					$genres = wp_set_object_terms( $post_id, $genres, 'genre', false );
				}

				if ( 1 == $this->wpml_o( 'wpml-settings-enable_collection' ) ) {
					$collections = explode( ',', $tmdb_data['crew']['director'] );
					$collections = wp_set_object_terms( $post_id, $collections, 'collection', false );
				}
			}
		}
		else if ( isset( $_REQUEST['tmdb_data'] ) && '' != $_REQUEST['tmdb_data'] ) {
			update_post_meta( $post_id, '_wpml_movie_data', $_REQUEST['tmdb_data'] );
		}

		if ( isset( $_REQUEST['wpml_details'] ) && ! is_null( $_REQUEST['wpml_details'] ) ) {

			if ( isset( $_REQUEST['is_quickedit'] ) )
				check_admin_referer( '_wpml_quickedit_movie_details', 'wpml_quickedit_movie_details_nonce' );
			else if ( isset( $_REQUEST['is_bulkedit'] ) )
				check_admin_referer( '_wpml_bulkedit_movie_details', 'wpml_bulkedit_movie_details_nonce' );

			$wpml_d = $_REQUEST['wpml_details'];

			if ( isset( $wpml_d['movie_status'] ) && ! is_null( $wpml_d['movie_status'] ) )
				update_post_meta( $post_id, '_wpml_movie_status', $wpml_d['movie_status'] );

			if ( isset( $wpml_d['movie_media'] ) && ! is_null( $wpml_d['movie_media'] ) )
				update_post_meta( $post_id, '_wpml_movie_media', $wpml_d['movie_media'] );

			if ( isset( $wpml_d['movie_rating'] ) && ! is_null( $wpml_d['movie_rating'] ) )
				update_post_meta( $post_id, '_wpml_movie_rating', number_format( $wpml_d['movie_rating'], 1 ) );
		}
	}

}
