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
 * Plugin class
 *
 * @package WPMovieLibrary
 * @author  Charlie MERLAND <charlie.merland@gmail.com>
 */
class WPMovieLibrary {

	/**
	 * Plugin name
	 *
	 * @since   1.0.0
	 * @var     string
	 */
	const NAME = 'WPMovieLibrary';

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin Settings Revision
	 * 
	 * @since    1.0.0
	 * 
	 * @var      int
	 */
	const SETTINGS_REVISION = 1;

	/**
	 * Plugin Settings var
	 * 
	 * @since    1.0.0
	 * 
	 * @var      string
	 */
	protected $plugin_settings = 'wpml_settings';

	/**
	 * Plugin Settings
	 * 
	 * @since    1.0.0
	 * @var      array
	 */
	protected $wpml_settings = null;

	/**
	 * Plugin slug
	 * 
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'wpml';

	/**
	 * Plugin URL
	 * 
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_url = '';

	/**
	 * Plugin URL
	 * 
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_path = '';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$this->plugin_url  = plugins_url( WPMovieLibrary::NAME );
		$this->plugin_path = plugin_dir_path( __FILE__ );

		$this->wpml_settings = array(
			'settings_revision' => WPMovieLibrary::SETTINGS_REVISION,
			'wpml' => array(
				'settings' => array(
					'meta_in_posts'    => 'posts_only',
					'details_in_posts' => 'posts_only',
					'details_as_icons' => 1,
					'default_movie_meta' => array(
						'director',
						'genres',
						'runtime',
						'overview',
						'rating'
					),
					'default_movie_details' => array(
						'movie_media',
						'movie_status'
					),
					'show_in_home'          => 1,
					'enable_collection'     => 1,
					'enable_actor'          => 1,
					'enable_genre'          => 1,
					'taxonomy_autocomplete' => 1,
					'deactivate' => array(
						'movies'      => 'conserve',
						'collections' => 'conserve',
						'genres'      => 'conserve',
						'actors'      => 'conserve',
						'cache'       => 'empty'
					),
					'uninstall' => array(
						'movies'      => 'convert',
						'collections' => 'convert',
						'genres'      => 'convert',
						'actors'      => 'convert',
						'cache'       => 'empty'
					)
				)
			),
			'tmdb' => array(
				'settings' => array(
					'APIKey'          => '',
					'dummy'           => 1,
					'lang'            => 'en',
					'scheme'          => 'https',
					'caching'         => 1,
					'caching_time'    => 15,
					'poster_size'     => 'original',
					'poster_featured' => 1,
					'images_size'     => 'original',
					'images_max'      => 12,
				)
			),
		);

		$this->wpml_movie_details = array(
			'movie_media'   => array(
				'title' => __( 'Media', 'wpml' ),
				'options' => array(
					'dvd'     => __( 'DVD', 'wpml' ),
					'bluray'  => __( 'BluRay', 'wpml' ),
					'vod'     => __( 'VOD', 'wpml' ),
					'vhs'     => __( 'VHS', 'wpml' ),
					'theater' => __( 'Theater', 'wpml' ),
					'other'   => __( 'Other', 'wpml' ),
				),
				'default' => array(
					'dvd'   => __( 'DVD', 'wpml' ),
				),
			),
			'movie_status'  => array(
				'title' => __( 'Status', 'wpml' ),
				'options' => array(
					'available' => __( 'Available', 'wpml' ),
					'loaned'    => __( 'Loaned', 'wpml' ),
					'scheduled' => __( 'Scheduled', 'wpml' ),
				),
				'default' => array(
					'available' => __( 'Available', 'wpml' ),
				)
			)
		);

		$this->wpml_movie_meta = array(
			'meta' => array(
				'type' => __( 'Type', 'wpml' ),
				'value' => __( 'Value', 'wpml' ),
				'data' => array(
					'title' => array(
						'title' => __( 'Title', 'wpml' ),
						'type' => 'text'
					),
					'original_title' => array(
						'title' => __( 'Original Title', 'wpml' ),
						'type' => 'text'
					),
					'overview' => array(
						'title' => __( 'Overview', 'wpml' ),
						'type' => 'textarea'
					),
					'production_companies' => array(
						'title' => __( 'Production', 'wpml' ),
						'type' => 'text'
					),
					'production_countries' => array(
						'title' => __( 'Country', 'wpml' ),
						'type' => 'text'
					),
					'spoken_languages' => array(
						'title' => __( 'Languages', 'wpml' ),
						'type' => 'text'
					),
					'runtime' => array(
						'title' => __( 'Runtime', 'wpml' ),
						'type' => 'text'
					),
					'genres' => array(
						'title' => __( 'Genres', 'wpml' ),
						'type' => 'text'
					),
					'release_date' => array(
						'title' => __( 'Release Date', 'wpml' ),
						'type' => 'text'
					)
				)
			),
			'crew' => array(
				'type' => __( 'Job', 'wpml' ),
				'value' => __( 'Name(s)', 'wpml' ),
				'data' => array(
					'director' => array(
						'title' => __( 'Director', 'wpml' ),
						'type' => 'text'
					),
					'producer' => array(
						'title' => __( 'Producer', 'wpml' ),
						'type' => 'text'
					),
					'photography' => array(
						'title' => __( 'Director of Photography', 'wpml' ),
						'type' => 'text'
					),
					'composer' => array(
						'title' => __( 'Original Music Composer', 'wpml' ),
						'type' => 'text'
					),
					'author' => array(
						'title' => __( 'Author', 'wpml' ),
						'type' => 'text'
					),
					'writer' => array(
						'title' => __( 'Writer', 'wpml' ),
						'type' => 'text'
					),
					'cast' => array(
						'title' => __( 'Actors', 'wpml' ),
						'type' => 'textarea'
					)
				)
			)
		);

		// Load settings or register new ones
		add_action( 'init', array( $this, 'wpml_default_settings' ) );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load movie post type, taxonomies
		add_action( 'init', array( $this, 'wpml_register_post_type' ) );
		add_action( 'init', array( $this, 'wpml_register_taxonomy' ) );

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add link to WP Admin Bar
		add_action( 'wp_before_admin_bar_render', array( $this, 'wpml_admin_bar_menu' ), 999 );

		// Order Taxonomies by term_order
		add_filter( 'get_the_terms', array( $this, 'wpml_get_the_terms' ), 10, 3 );
		add_filter( 'wp_get_object_terms', array( $this, 'wpml_get_ordered_object_terms' ), 10, 4 );

		// Load Movies as well as Posts in the Loop
		add_action( 'pre_get_posts', array( $this, 'wpml_show_movies_in_home_page' ) );

		// Movie content
		add_filter( 'the_content', array( $this, 'wpml_movie_content' ) );

		// Internal Hooks
		add_filter( 'wpml_get_movies_from_media', array( $this, 'wpml_get_movies_from_media' ), 10, 1 );
		add_filter( 'wpml_get_movies_from_status', array( $this, 'wpml_get_movies_from_status' ), 10, 1 );

		add_filter( 'wpml_format_widget_lists', array( $this, 'wpml_format_widget_lists' ), 10, 4 );
		add_filter( 'wpml_format_widget_lists_thumbnails', array( $this, 'wpml_format_widget_lists_thumbnails' ), 10, 1 );

		add_filter( 'wpml_check_for_existing_images', array( $this, 'wpml_check_for_existing_images' ), 10, 3 );

		add_filter( 'wpml_stringify_array', array( $this, 'wpml_stringify_array' ), 10, 3 );
		add_filter( 'wpml_filter_empty_array', array( $this, 'wpml_filter_empty_array' ), 10, 1 );
		add_filter( 'wpml_filter_undimension_array', array( $this, 'wpml_filter_undimension_array' ), 10, 1 );

		add_filter( 'wpml_filter_meta_data', array( $this, 'wpml_filter_meta_data' ), 10, 1 );
		add_filter( 'wpml_filter_crew_data', array( $this, 'wpml_filter_crew_data' ), 10, 1 );
		add_filter( 'wpml_filter_cast_data', array( $this, 'wpml_filter_cast_data' ), 10, 1 );

		add_action( 'wpml_list_default_movie_media', array( $this, 'wpml_list_default_movie_media' ), 10, 3 );
		add_action( 'wpml_restore_default_settings', array( $this, 'wpml_restore_default_settings' ), 10, 0 );

	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                            Plugin instance
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                           Plugin Accessors
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Return the default Movie Media
	 *
	 * @since    1.0.0
	 *
	 * @return   array    WPML Default Movie Media.
	 */
	public function wpml_get_default_movie_media() {
		$default = $this->wpml_movie_details['movie_media']['default'];
		return $default;
	}

	/**
	 * Return the default Movie Status
	 *
	 * @since    1.0.0
	 *
	 * @return   array    WPML Default Movie Status.
	 */
	public function wpml_get_default_movie_status() {
		$default = $this->wpml_movie_details['movie_status']['default'];
		return $default;
	}

	/**
	 * Return available Movie Media
	 *
	 * @since    1.0.0
	 *
	 * @return   array    WPML Default Movie Media.
	 */
	public function wpml_get_available_movie_media() {
		$media = array();
		$items = $this->wpml_movie_details['movie_media']['options'];
		foreach ( $items as $slug => $title )
			$media[ $slug ] = $title;
		return $media;
	}

	/**
	 * Return available Movie Status
	 *
	 * @since    1.0.0
	 *
	 * @return   array    WPML Available Movie Status.
	 */
	public function wpml_get_available_movie_status() {
		$statuses = array();
		$items = $this->wpml_movie_details['movie_status']['options'];
		foreach ( $items as $slug => $title )
			$statuses[ $slug ] = $title;
		return $statuses;
	}

	/**
	 * Return all supported Movie Meta fields
	 *
	 * @since    1.0.0
	 *
	 * @return   array    WPML Supported Movie Meta fields.
	 */
	public function wpml_get_supported_movie_meta() {
		return array_merge( $this->wpml_movie_meta['meta']['data'], $this->wpml_movie_meta['crew']['data'] );
	}

	/**
	 * Return Movie's stored TMDb data.
	 * 
	 * @uses wpml_get_movie_postmeta()
	 *
	 * @since    1.0.0
	 *
	 * @return   array|string    WPML Movie TMDb data if stored, empty string else.
	 */
	public function wpml_get_movie_data( $post_id = null ) {
		return $this->wpml_get_movie_postmeta( 'data', $post_id );
	}

	/**
	 * Return Movie's Status.
	 * 
	 * @uses wpml_get_movie_postmeta()
	 *
	 * @since    1.0.0
	 *
	 * @return   array|string    WPML Movie Status if stored, empty string else.
	 */
	public function wpml_get_movie_status( $post_id = null ) {
		return $this->wpml_get_movie_postmeta( 'status', $post_id );
	}

	/**
	 * Return Movie's Media.
	 * 
	 * @uses wpml_get_movie_postmeta()
	 *
	 * @since    1.0.0
	 *
	 * @return   array|string    WPML Movie Media if stored, empty string else.
	 */
	public function wpml_get_movie_media( $post_id = null ) {
		return $this->wpml_get_movie_postmeta( 'media', $post_id );
	}

	/**
	 * Return Movie's Rating.
	 * 
	 * @uses wpml_get_movie_postmeta()
	 *
	 * @since    1.0.0
	 *
	 * @return   array|string    WPML Movie Rating if stored, empty string else.
	 */
	public function wpml_get_movie_rating( $post_id = null ) {
		return $this->wpml_get_movie_postmeta( 'rating', $post_id );
	}

	/**
	 * Return various Movie's Post Meta. Possible meta: status, media, rating
	 * and data.
	 *
	 * @since    1.0.0
	 *
	 * @return   array|string    WPML Movie Meta if available, empty string else.
	 */
	private function wpml_get_movie_postmeta( $meta, $post_id = null ) {

		$allowed_meta = array( 'data', 'status', 'media', 'rating' );

		if ( is_null( $post_id ) )
			$post_id =  get_the_ID();

		if ( ! $post = get_post( $post_id ) || 'movie' != get_post_type( $post_id ) || ! in_array( $meta, $allowed_meta ) )
			return false;

		return get_post_meta( $post_id, "_wpml_movie_{$meta}", true );
	}

	/**
	 * Return the WPML Collection Taxonomy option status: enabled of not.
	 *
	 * @since    1.0.0
	 *
	 * @return   boolean    Taxonomy status: true if enabled, false if not.
	 */
	public function wpml_can_use_collection() {
		return (boolean) ( 1 == $this->wpml_o( 'wpml-settings-enable_collection' ) );
	}

	/**
	 * Return the WPML Genre Taxonomy option status: enabled of not.
	 *
	 * @since    1.0.0
	 *
	 * @return   boolean    Taxonomy status: true if enabled, false if not.
	 */
	public function wpml_can_use_genre() {
		return (boolean) ( 1 == $this->wpml_o( 'wpml-settings-enable_genre' ) );
	}

	/**
	 * Return the WPML Actor Taxonomy option status: enabled of not.
	 *
	 * @since    1.0.0
	 *
	 * @return   boolean    Taxonomy status: true if enabled, false if not.
	 */
	public function wpml_can_use_actor() {
		return (boolean) ( 1 == $this->wpml_o( 'wpml-settings-enable_actor' ) );
	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                     Plugin  Activate/Deactivate
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Fired when the plugin is activated.
	 * 
	 * Restore previously converted contents. If WPML was previously
	 * deactivated or uninstalled using the 'convert' option, Movies and
	 * Custom Taxonomies should still be in the database. If they are, we
	 * convert them back to WPML contents.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		global $wpdb;

		$contents = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'meta_key'       => '_wpml_content_type',
				'meta_value'     => 'movie'
			)
		);

		foreach ( $contents->posts as $post ) {
			set_post_type( $post->ID, 'movie' );
			delete_post_meta( $post->ID, '_wpml_content_type', 'movie' );
		}

		$contents = $wpdb->get_results( 'SELECT term_id, slug FROM ' . $wpdb->terms . ' WHERE slug LIKE "wpml_%"' );

		$collections = array();
		$genres      = array();
		$actors      = array();

		foreach ( $contents as $term ) {
			if ( false !== strpos( $term->slug, 'wpml_collection' ) ) {
				$collections[] = $term->term_id;
			}
			else if ( false !== strpos( $term->slug, 'wpml_genre' ) ) {
				$genres[] = $term->term_id;
			}
			else if ( false !== strpos( $term->slug, 'wpml_actor' ) ) {
				$actors[] = $term->term_id;
			}
		}

		if ( ! empty( $collections ) )
			$wpdb->query( 'UPDATE ' . $wpdb->term_taxonomy . ' SET taxonomy = "collection" WHERE term_id IN (' . implode( ',', $collections ) . ')' );

		if ( ! empty( $genres ) )
			$wpdb->query( 'UPDATE ' . $wpdb->term_taxonomy . ' SET taxonomy = "genre" WHERE term_id IN (' . implode( ',', $genres ) . ')' );

		if ( ! empty( $actors ) )
			$wpdb->query( 'UPDATE ' . $wpdb->term_taxonomy . ' SET taxonomy = "actor" WHERE term_id IN (' . implode( ',', $actors ) . ')' );

		$wpdb->query(
			'UPDATE ' . $wpdb->terms . '
			 SET slug = REPLACE(slug, "wpml_collection-", ""),
			     slug = REPLACE(slug, "wpml_genre-", ""),
			     slug = REPLACE(slug, "wpml_actor-", "")'
		);

	}

	/**
	 * Fired when the plugin is deactivated.
	 * 
	 * When deactivatin/uninstalling WPML, adopt different behaviors depending
	 * on user options. Movies and Taxonomies can be kept as they are,
	 * converted to WordPress standars or removed. Default is conserve on
	 * deactivation, convert on uninstall.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		global $wpdb;

		$o           = get_option( 'wpml_settings' );
		$movies      = $o['wpml']['settings']['deactivate']['movies'];
		$collections = $o['wpml']['settings']['deactivate']['collections'];
		$genres      = $o['wpml']['settings']['deactivate']['genres'];
		$actors      = $o['wpml']['settings']['deactivate']['actors'];
		$cache       = $o['wpml']['settings']['deactivate']['cache'];

		// Handling Movie Custom Post Type on WPML deactivation

		$contents = new WP_Query(
			array(
				'post_type'      => 'movie',
				'posts_per_page' => -1
			)
		);

		if ( 'convert' == $movies ) {
			foreach ( $contents->posts as $post ) {
				set_post_type( $post->ID, 'post' );
				add_post_meta( $post->ID, '_wpml_content_type', 'movie', true );
			}
		}
		else if ( 'remove' == $movies ) {
			foreach ( $contents->posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}
		else if ( 'delete' == $movies ) {
			foreach ( $contents->posts as $post ) {
				wp_delete_post( $post->ID, true );
				$attachments = get_children( array( 'post_parent' => $post->ID ) );
				foreach ( $attachments as $a ) {
					wp_delete_post( $a->ID, true );
				}
			}
		}

		// Handling Custom Category-like Taxonomies on WPML deactivation

		$contents = get_terms( array( 'collection' ), array() );

		if ( 'convert' == $collections ) {
			foreach ( $contents as $term ) {
				wp_update_term( $term->term_id, 'collection', array( 'slug' => 'wpml_collection-' . $term->slug ) );
				$wpdb->update(
					$wpdb->term_taxonomy,
					array( 'taxonomy' => 'category' ),
					array( 'taxonomy' => 'collection' ),
					array( '%s' )
				);
			}
		}
		else if ( 'remove' == $collections || 'delete' == $collections ) {
			foreach ( $contents as $term ) {
				wp_delete_term( $term->term_id, 'collection' );
			}
		}

		// Handling Genres Taxonomies on WPML deactivation

		$contents = get_terms( array( 'genre' ), array() );

		if ( 'convert' == $genres ) {
			foreach ( $contents as $term ) {
				wp_update_term( $term->term_id, 'genre', array( 'slug' => 'wpml_genre-' . $term->slug ) );
				$wpdb->update(
					$wpdb->term_taxonomy,
					array( 'taxonomy' => 'post_tag' ),
					array( 'taxonomy' => 'genre' ),
					array( '%s' )
				);
			}
		}
		else if ( 'remove' == $genres || 'delete' == $genres ) {
			foreach ( $contents as $term ) {
				wp_delete_term( $term->term_id, 'genre' );
			}
		}

		// Handling Actors Taxonomies on WPML deactivation

		$contents = get_terms( array( 'actor' ), array() );

		if ( 'convert' == $actors ) {
			foreach ( $contents as $term ) {
				wp_update_term( $term->term_id, 'actor', array( 'slug' => 'wpml_actor-' . $term->slug ) );
				$wpdb->update(
					$wpdb->term_taxonomy,
					array( 'taxonomy' => 'post_tag' ),
					array( 'taxonomy' => 'actor' ),
					array( '%s' )
				);
			}
		}
		else if ( 'remove' == $actors || 'delete' == $actors ) {
			foreach ( $contents as $term ) {
				wp_delete_term( $term->term_id, 'actor' );
			}
		}

		// Handling Cache cleanup on WPML deactivation
		// Adapted from Sébastien Corne's "purge-transient" snippet

		global $_wp_using_ext_object_cache;

		if ( ! $_wp_using_ext_object_cache && 'empty' == $cache ) {

			$sql = "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE \"_transient_%_movies_%\"";
			$transients = $wpdb->get_col( $sql );

			foreach ( $transients as $transient )
				$result = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE \"{$transient}\"" );

			$wpdb->query( 'OPTIMIZE TABLE ' . $wpdb->options );
		}

	}

	/**
	 * Missing API Key notification. Display a message on plugins and
	 * Movie Settings pages reminding to save a valid API Key.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                      "Network Deactivate" action,
	 *                                       false if WPMU is disabled or
	 *                                       plugin is deactivated on an
	 *                                       individual blog.
	 */
	public function wpml_activate_notice( $network_wide, $force = false ) {

		global $hook_suffix;

		if ( ! $force && ( 'plugins.php' != $hook_suffix || false !== $this->wpml_get_api_key() ) )
			return false;

		echo '<div class="updated wpml"><p>';
		printf( __( 'Congratulation, you successfully installed WPMovieLibrary. You need a valid <acronym title="TheMovieDB">TMDb</acronym> API key to start adding your movies. Go to the <a href="%s">WPMovieLibrary Settings page</a> to add your API key.', 'wpml' ), admin_url( 'edit.php?post_type=movie&page=settings' ) );
		echo '</p></div>';
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_slug, plugins_url( 'assets/css/public.css', __FILE__ ), array(), WPMovieLibrary::VERSION );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_slug, plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), WPMovieLibrary::VERSION, true );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                       Action and Filter Hooks
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Restore default settings.
	 * 
	 * Action Hook to restore the Plugin's default settings.
	 * 
	 * @since    1.0.0
	 */
	public function wpml_restore_default_settings() {
		$this->wpml_default_settings( $force = true );
	}

	/**
	 * Add a New Movie link to WP Admin Bar.
	 * 
	 * WordPress 3.8 introduces Dashicons, for older versions we use a PNG
	 * icon instead.
	 *
	 * @since    1.0.0
	 */
	public function wpml_admin_bar_menu() {

		global $wp_admin_bar;

		$args = array(
			'id'    => 'wpmovielibrary',
			'title' => __( 'New Movie', 'wpml' ),
			'href'  => admin_url( 'post-new.php?post_type=movie' ),
			'meta'  => array(
				'title' => __( 'New Movie', 'wpml' )
			)
		);

		// Dashicons or PNG
		if ( version_compare( get_bloginfo( 'version' ), '3.8', '<' ) ) {
			$args['title'] = '<img src="' . $this->plugin_url . '/admin/assets/img/icon-movie.png" alt="" />' . $args['title'];
		}
		else {
			$args['meta']['class'] = 'haz-dashicon';
		}

		$wp_admin_bar->add_menu( $args );
	}

	/**
	 * Check for previously imported images to avoid duplicates.
	 * 
	 * If any attachment has one or more postmeta matching the current
	 * Movie's TMDb ID, we don't want to import the image again, so we return
	 * the last found image's ID to be used instead.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    $tmdb_id    The Movie's TMDb ID.
	 * @param    string    $image_type Optional. Which type of image we're
	 *                                 dealing with, simple image or poster.
	 * 
	 * @return   string|boolean        Return the last found image's ID if
	 *                                 any, false if no matching image was
	 *                                 found.
	 */
	public function wpml_check_for_existing_images( $tmdb_id, $image_type = 'image', $image = null ) {

		if ( ! isset( $tmdb_id ) || '' == $tmdb_id )
			return false;

		if ( ! in_array( $image_type, array( 'image', 'poster' ) ) )
			$image_type = 'image';

		$check = get_posts(
			array(
				'post_type' => 'attachment',
				'meta_query' => array(
					array(
						'key'     => '_wpml_' . $image_type . '_related_tmdb_id',
						'value'   => $tmdb_id,
					)
				)
			)
		);

		if ( ! is_null( $image ) ) {
			foreach ( $check as $c ) {
				$try = get_attached_file( $c->ID );
				if ( $image == basename ( $try ) ) {
					return $try;
				}
			}
		}
		else if ( ! empty( $check ) )
			return $check;

		return false;
	}
 
	/**
	 * Show movies in default home page post list.
	 * 
	 * Add action on pre_get_posts hook to add movie to the list of
	 * queryable post_types.
	 *
	 * @since     1.0.0
	 * 
	 * @param     int       $query the WP_Query Object object to alter
	 *
	 * @return    WP_Query    Query Object
	 */
	public function wpml_show_movies_in_home_page( $query ) {

		$post_type = array( 'post', 'movie' );
		$post_status = array( 'publish', 'private' );

		if ( 1 == $this->wpml_o( 'wpml-settings-show_in_home' ) && is_home() && $query->is_main_query() ) {

			if ( '' != $query->get( 'post_type' ) )
				$post_type = array_unique( array_merge( $query->get( 'post_type' ), $post_type ) );

			if ( '' != $query->get( 'post_status' ) )
				$post_status = array_unique( array_merge( $query->get( 'post_status' ), $post_status ) );

			$query->set( 'post_type', $post_type );
			$query->set( 'post_status', $post_status );
		}

		return $query;
	}

	/**
	 * Show some info about movies in post view.
	 * 
	 * Add a filter on the_content hook to display infos selected in options
	 * about the movie: note, director, overview, actors…
	 *
	 * @since     1.0.0
	 * 
	 * @param     string      $content The original post content
	 *
	 * @return    string      The filtered content containing original
	 *                        content plus movie infos if available, the
	 *                        untouched original content else.
	 */
	public function wpml_movie_content( $content ) {

		if ( 'movie' != get_post_type() )
			return $content;

		$details  = $this->wpml_movie_details();
		$metadata = $this->wpml_movie_metadata();

		$content = $details . $metadata . $content;

		return $content;
	}

	/**
	 * Generate current movie's details list.
	 *
	 * @since     1.0.0
	 *
	 * @return    null|string    The current movie's metadata list
	 */
	private function wpml_movie_details() {

		if ( 'nowhere' == $this->wpml_o( 'wpml-settings-details_in_posts' ) || ( 'posts_only' == $this->wpml_o( 'wpml-settings-details_in_posts' ) && ! is_singular() ) )
			return null;

		$fields = $this->wpml_o( 'wpml-settings-default_movie_details' );

		if ( empty( $fields ) )
			return null;

		$html = '<div class="wpml_movie_detail">';

		foreach ( $fields as $field ) {

			switch ( $field ) {
				case 'movie_media':
				case 'movie_status':
					$meta = call_user_func_array( array( $this, "wpml_get_{$field}" ), array( get_the_ID() ) );
					if ( '' != $meta ) {
						if ( 1 ==  $this->wpml_o( 'wpml-settings-details_as_icons' ) )
							$html .= '<div class="wpml_' . $field . ' ' . $meta . ' wpml_detail_icon"></div>';
						else
							$html .= '<div class="wpml_' . $field . ' ' . $meta . ' wpml_detail_label"><span class="wpml_movie_detail_item">' . $this->wpml_movie_details[ $field ]['options'][ $meta ] . '</span></div>';
					}
					break;
				case 'rating':
					$html .= sprintf( $default_format, $field, __( 'Movie rating', 'wpml' ), $field, sprintf( '<div class="movie_rating_display stars_%s"></div>', ( '' == $movie_rating ? '0_0' : str_replace( '.', '_', $movie_rating ) ) ) );
					break;
				default:
					
					break;
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Generate current movie's metadata list.
	 *
	 * @since     1.0.0
	 *
	 * @return    null|string    The current movie's metadata list
	 */
	private function wpml_movie_metadata() {

		if ( 'nowhere' == $this->wpml_o( 'wpml-settings-meta_in_posts' ) || ( 'posts_only' == $this->wpml_o( 'wpml-settings-meta_in_posts' ) && ! is_singular() ) )
			return null;

		$tmdb_data = $this->wpml_get_movie_data();
		$tmdb_data = apply_filters( 'wpml_filter_undimension_array', $tmdb_data );

		$fields = $this->wpml_o( 'wpml-settings-default_movie_meta' );
		$default_format = '<dt class="wpml_%s_field_title">%s</dt><dd class="wpml_%s_field_value">%s</dd>';
		$default_fields = $this->wpml_get_supported_movie_meta();

		if ( '' == $tmdb_data || empty( $fields ) )
			return null;

		$html = '<dl class="wpml_movie">';

		foreach ( $fields as $field ) {

			switch ( $field ) {
				case 'genres':
					$genres = $this->wpml_can_use_genre() ? get_the_term_list( get_the_ID(), 'genre', '', ', ', '' ) : $tmdb_data[ $field ];
					$html .= sprintf( $default_format, $field, $default_fields[ $field ]['title'], $field, $genres );
					break;
				case 'cast':
					$actors = $this->wpml_can_use_genre() ? get_the_term_list( get_the_ID(), 'actor', '', ', ', '' ) : $tmdb_data[ $field ];
					$html .= sprintf( $default_format, $field, __( 'Staring', 'wpml' ), $field, $actors );
					break;
				case 'release_date':
					$html .= sprintf( $default_format, $field, $default_fields[ $field ]['title'], $field, date_i18n( get_option( 'date_format' ), strtotime( $tmdb_data[ $field ] ) ) );
					break;
				case 'runtime':
					$html .= sprintf( $default_format, $field, $default_fields[ $field ]['title'], $field, date_i18n( get_option( 'time_format' ), mktime( 0, $tmdb_data[ $field ] ) ) );
					break;
				case 'director':
					$term = get_term_by( 'name', $tmdb_data[ $field ], 'collection' );
					$collection = ( $term && ! is_wp_error( $link = get_term_link( $term, 'collection' ) ) ) ? '<a href="' . $link . '">' . $tmdb_data[ $field ] . '</a>' : $tmdb_data[ $field ];
					$html .= sprintf( $default_format, $field, __( 'Directed by', 'wpml' ), $field, $collection );
					break;
				default:
					if ( in_array( $field, $fields ) && isset( $tmdb_data[ $field ] ) && '' != $tmdb_data[ $field ] )
						$html .= sprintf( $default_format, $field, $default_fields[ $field ]['title'], $field, $tmdb_data[ $field ] );
					break;
			}
		}

		$html .= '</dl>';

		return $html;
	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                 Internal Action and Filter Hooks
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Filter Hook
	 * 
	 * Used to get a list of Movies depending on their Media
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    Media slug
	 * 
	 * @return   array     Array of Post objects
	 * 
	 * @since    1.0.0
	 * 
	 */
	public function wpml_get_movies_from_media( $media = null ) {

		$media = esc_attr( $media );

		$default = $this->wpml_get_available_movie_media();
		$allowed = array_keys( $default );

		if ( is_null( $media ) || ! in_array( $media, $allowed ) )
			$media = $this->wpml_get_default_movie_media();

		$args = array(
			'post_type' => 'movie',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key'   => '_wpml_movie_media',
					'value' => $media
				)
			)
		);
		
		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Filter Hook
	 * 
	 * Used to get a list of Movies depending on their Status
	 * 
	 * @since    1.0.0
	 * 
	 * @param    string    Status slug
	 * 
	 * @return   array     Array of Post objects
	 */
	public function wpml_get_movies_from_status( $status = null ) {

		$status = esc_attr( $status );

		$default = $this->wpml_get_available_movie_status();
		$allowed = array_keys( $default );

		if ( is_null( $status ) || ! in_array( $status, $allowed ) )
			$status = $this->wpml_get_default_movie_status();

		$args = array(
			'post_type' => 'movie',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key'   => '_wpml_movie_status',
					'value' => $status
				)
			)
		);
		
		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Filter Hook
	 * 
	 * Used to generate Movies dropdown or classic lists.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array      $items Array of Movies objects
	 * @param    boolean    $dropdown Whether to return a dropdown or a regular list
	 * @param    boolean    $styling Add custom styling or not
	 * @param    string     $title First Option content if dropdown
	 * 
	 * @return   string     HTML string List of movies
	 */
	public function wpml_format_widget_lists( $items, $dropdown = false, $styling = false, $title = null ) {

		if ( ! is_array( $items ) || empty( $items ) )
			return null;

		$html = array();
		$style = 'wpml-list';
		$first = '';

		if ( false !== $styling )
			$style = 'wpml-list custom';

		if ( ! is_null( $title ) )
			$first = sprintf( '<option value="">%s</option>', esc_attr( $title ) );

		foreach ( $items as $item ) {
			if ( $dropdown )
				$html[] = '<option value="' . esc_url( $item['link'] ) . '">' . esc_attr( $item['title'] ) . '</option>';
			else
				$html[] = '<li><a href="' . esc_url( $item['link'] ) . '" title="' . esc_attr( $item['attr_title'] ) . '">' . esc_attr( $item['title'] ) . '</a></li>';
		}

		if ( false !== $dropdown )
			$html = '<select class="' . $style . '">' . $first . join( $html ) . '</select>';
		else
			$html = '<ul>' . join( $html ) . '</ul>';

		return $html;
	}

	/**
	 * Filter Hook
	 * 
	 * Used to generate Movies lists including Poster.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $items Array of Movies objects
	 * 
	 * @return   string   HTML string of movies' links and Posters
	 */
	public function wpml_format_widget_lists_thumbnails( $items ) {

		if ( ! is_array( $items ) || empty( $items ) )
			return null;

		$html = array();

		foreach ( $items as $item ) {
			$html[] = '<a href="' . esc_url( $item['link'] ) . '" title="' . esc_attr( $item['attr_title'] ) . '">';
			$html[] = '<figure class="widget-movie">';
			$html[] = get_the_post_thumbnail( $item['ID'], 'thumbnail' );
			$html[] = '</figure>';
			$html[] = '</a>';
		}

		$html = '<div class="widget-movies">' . implode( "\n", $html ) . '</div>';

		return $html;
	}

	/**
	 * Filter a Movie's Metadata to extract only supported data.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $array Movie metadata
	 * 
	 * @return   array    Filtered Metadata
	 */
	public function wpml_filter_meta_data( $data ) {

		if ( ! is_array( $data ) || empty( $data ) )
			return $data;

		$filter = array();
		$_data = array();

		foreach ( $this->wpml_movie_meta['meta']['data'] as $slug => $f ) {
			$filter[] = $slug;
			$_data[ $slug ] = '';
		}

		foreach ( $data as $slug => $d ) {
			if ( in_array( $slug, $filter ) ) {
				if ( is_array( $d ) ) {
					foreach ( $d as $_d ) {
						if ( is_array( $_d ) && isset( $_d['name'] ) ) {
							$_data[ $slug ][] = $_d['name'];
						}
					}
				}
				else {
					$_data[ $slug ] = $d;
				}
			}
		}

		return $_data;
	}

	/**
	 * Filter a Movie's Crew to extract only supported data.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $array Movie Crew
	 * 
	 * @return   array    Filtered Crew
	 */
	public function wpml_filter_crew_data( $data ) {

		if ( ! is_array( $data ) || empty( $data ) || ! isset( $data['crew'] ) )
			return $data;

		$filter = array();
		$_data = array();

		$cast = apply_filters( 'wpml_filter_cast_data', $data['cast'] );
		$data = $data['crew'];

		foreach ( $this->wpml_movie_meta['crew']['data'] as $slug => $f ) {
			$filter[ $slug ] = $f['title'];
			$_data[ $slug ] = '';
		}

		foreach ( $data as $i => $d )
			if ( isset( $d['job'] ) && false !== ( $key = array_search( $d['job'], $filter ) ) && isset( $_data[ $key ] ) )
				$_data[ $key ][] = $d['name'];

		$_data['cast'] = $cast;

		return $_data;
	}

	/**
	 * Filter a Movie's Cast to extract only supported data.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $array Movie Cast
	 * 
	 * @return   array    Filtered Cast
	 */
	public function wpml_filter_cast_data( $data ) {

		if ( ! is_array( $data ) || empty( $data ) )
			return $data;

		foreach ( $data as $i => $d )
			$data[ $i ] = $d['name'];

		return $data;
	}

	/**
	 * Convert an Array shaped list to a separated string.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $array Array shaped list
	 * @param    string   $subrow optional subrow to select in subitems
	 * @param    string   $separator Separator string to use to implode the list
	 * 
	 * @return   string   Separated list
	 */
	public function wpml_stringify_array( $array, $subrow = 'name', $separator = ', ' ) {

		if ( ! is_array( $array ) || empty( $array ) )
			return $array;

		foreach ( $array as $i => $row ) {
			if ( ! is_array( $row ) )
				$array[ $i ] = $row;
			else if ( false === $subrow || ! is_array( $row ) )
				$array[ $i ] = $this->wpml_stringify_array( $row, $subrow, $separator );
			else if ( is_array( $row ) && isset( $row[ $subrow ] ) )
				$array[ $i ] = $row[ $subrow ];
			else if ( is_array( $row ) )
				$array[ $i ] = implode( $separator, $row );
		}

		$array = implode( $separator, $array );

		return $array;
	}

	/**
	 * Filter an array to detect empty associative arrays.
	 * Uses wpml_stringify_array to stringify the array and check its length.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $array Array to check
	 * 
	 * @return   array    Original array plus and notification row if empty
	 */
	public function wpml_filter_empty_array( $array ) {

		if ( ! is_array( $array ) || empty( $array ) )
			return $array;

		$_array = apply_filters( 'wpml_stringify_array', $array, false, '' );

		return strlen( $_array ) > 0 ? $array : array_merge( array( '_empty' => true ), $array );
	}

	/**
	 * Filter an array to remove any sub-array, reducing multidimensionnal
	 * arrays.
	 * 
	 * @since    1.0.0
	 * 
	 * @param    array    $array Array to check
	 * 
	 * @return   array    Reduced array
	 */
	public function wpml_filter_undimension_array( $array ) {

		if ( ! is_array( $array ) || empty( $array ) )
			return $array;

		$_array = array();

		foreach ( $array as $key => $row ) {
			if ( is_array( $row ) )
				$_array = array_merge( $_array, $this->wpml_filter_undimension_array( $row ) );
			else
				$_array[ $key ] = $row;
		}

		return $_array;
	}

	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *               Custom Post Types, Status & Taxonomy
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Register a 'movie' custom post type and 'import-draft' post status
	 *
	 * @since    1.0.0
	 */
	public function wpml_register_post_type() {

		$labels = array(
			'name'               => __( 'Movies', 'wpml' ),
			'singular_name'      => __( 'Movie', 'wpml' ),
			'add_new'            => __( 'Add New', 'wpml' ),
			'add_new_item'       => __( 'Add New Movie', 'wpml' ),
			'edit_item'          => __( 'Edit Movie', 'wpml' ),
			'new_item'           => __( 'New Movie', 'wpml' ),
			'all_items'          => __( 'All Movies', 'wpml' ),
			'view_item'          => __( 'View Movie', 'wpml' ),
			'search_items'       => __( 'Search Movies', 'wpml' ),
			'not_found'          => __( 'No movies found', 'wpml' ),
			'not_found_in_trash' => __( 'No movies found in Trash', 'wpml' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Movies', 'wpml' )
		);

		$args = array(
			'labels'             => $labels,
			'rewrite'            => array(
				'slug'       => 'movies'
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'has_archive'        => true,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments' ),
			'menu_position'      => 5
		);

		// Dashicons or PNG
		$args['menu_icon'] = ( version_compare( get_bloginfo( 'version' ), '3.8', '>=' ) ? 'dashicons-format-video' : $this->plugin_url . '/admin/assets/img/icon-movie.png' );

		register_post_type( 'movie', $args );

		register_post_status( 'import-draft', array(
			'label'                     => _x( 'Imported Draft', 'wpml' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false,
			'label_count'               => _n_noop( 'Imported Draft <span class="count">(%s)</span>', 'Imported Draft <span class="count">(%s)</span>' ),
		) );
	}

	/**
	 * Register a 'Collections' custom taxonomy to aggregate movies
	 * 
	 * Collections are Category-like taxonomies: hierarchical, no tagcloud.
	 * Genres and Actors are Tag-like taxonomies: not-hierarchical, tagcloud.
	 * 
	 * Collections and Genres are registered with 'show_admin_column' set to
	 * true whereas Actors are displayed by a custom way. This is meant to
	 * override WordPress default ordering of Taxonomies.
	 * 
	 * @see https://github.com/Askelon/WPMovieLibrary/issues/7
	 * 
	 * @see wpml_movies_columns_head()
	 * @see wpml_movies_columns_content()
	 *
	 * @since    1.0.0
	 */
	public function wpml_register_taxonomy() {

		if ( 1 == $this->wpml_o( 'wpml-settings-enable_collection' ) ) {
			register_taxonomy(
				'collection',
				'movie',
				array(
					'labels'   => array(
						'name'          => __( 'Collections', 'wpml' ),
						'add_new_item'  => __( 'New Movie Collection', 'wpml' )
					),
					'show_ui'           => true,
					'show_tagcloud'     => false,
					'show_admin_column' => true,
					'hierarchical'      => true,
					'query_var'         => true,
					'sort'              => true,
					'rewrite'           => array( 'slug' => 'collection' )
				)
			);
		}

		if ( 1 == $this->wpml_o( 'wpml-settings-enable_actor' ) ) {
			register_taxonomy(
				'actor',
				'movie',
				array(
					'labels'   => array(
						'name'          => __( 'Actors', 'wpml' ),
						'add_new_item'  => __( 'New Actor', 'wpml' )
					),
					'show_ui'           => true,
					'show_tagcloud'     => true,
					'show_admin_column' => true,
					'hierarchical'      => false,
					'query_var'         => true,
					'sort'              => true,
					'rewrite'           => array( 'slug' => 'actor' )
				)
			);
		}

		if ( 1 == $this->wpml_o( 'wpml-settings-enable_genre' ) ) {
			register_taxonomy(
				'genre',
				'movie',
				array(
					'labels'   => array(
						'name'          => __( 'Genres', 'wpml' ),
						'add_new_item'  => __( 'New Genre', 'wpml' )
					),
					'show_ui'           => true,
					'show_tagcloud'     => true,
					'show_admin_column' => true,
					'hierarchical'      => false,
					'query_var'         => true,
					'sort'              => true,
					'rewrite'           => array( 'slug' => 'genre' )
				)
			);
		}

	}

	/**
	 * Sort Taxonomies by term_order.
	 * 
	 * Code from Luke Gedeon, see https://core.trac.wordpress.org/ticket/9547#comment:7
	 *
	 * @since    1.0.0
	 *
	 * @param    array      $terms array of objects to be replaced with sorted list
	 * @param    integer    $id post id
	 * @param    string     $taxonomy only 'post_tag' is changed.
	 * 
	 * @return   array      Terms array of objects
	 */
	function wpml_get_the_terms( $terms, $id, $taxonomy ) {

		if ( ! in_array( $taxonomy, array( 'collection', 'genre', 'actor' ) ) )
			return $terms;

		$terms = wp_cache_get( $id, "{$taxonomy}_relationships_sorted" );
		if ( false === $terms ) {
			$terms = wp_get_object_terms( $id, $taxonomy, array( 'orderby' => 'term_order' ) );
			wp_cache_add( $id, $terms, $taxonomy . '_relationships_sorted' );
		}

		return $terms;
	}

	/**
	 * Retrieves the terms associated with the given object(s), in the
	 * supplied taxonomies.
	 *
	 * This is a copy of WordPress' wp_get_object_terms function with a bunch
	 * of edits to use term_order as a default sorting param.
	 *
	 * @since 1.0.0
	 *
	 * @param    int|array       $object_ids The ID(s) of the object(s) to retrieve.
	 * @param    string|array    $taxonomies The taxonomies to retrieve terms from.
	 * @param    array|string    $args Change what is returned
	 * 
	 * @return   array|WP_Error  The requested term data or empty array if no
	 *                           terms found. WP_Error if any of the $taxonomies
	 *                           don't exist.
	 */
	function wpml_get_ordered_object_terms( $terms, $object_ids, $taxonomies, $args ) {

		global $wpdb;

		$taxonomies = explode( ', ', str_replace( "'", "", $taxonomies ) );

		if ( empty( $object_ids ) || ( $taxonomies != "'collection', 'actor', 'genre'" && ( ! in_array( 'collection', $taxonomies ) && ! in_array( 'actor', $taxonomies ) && ! in_array( 'genre', $taxonomies ) ) ) )
			return $terms;

		foreach ( (array) $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) )
				return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy' ) );
		}

		if ( ! is_array( $object_ids ) )
			$object_ids = array( $object_ids );
		$object_ids = array_map( 'intval', $object_ids );

		$defaults = array('orderby' => 'term_order', 'order' => 'ASC', 'fields' => 'all');
		$args = wp_parse_args( $args, $defaults );

		$terms = array();
		if ( count($taxonomies) > 1 ) {
			foreach ( $taxonomies as $index => $taxonomy ) {
				$t = get_taxonomy($taxonomy);
				if ( isset($t->args) && is_array($t->args) && $args != array_merge($args, $t->args) ) {
					unset($taxonomies[$index]);
					$terms = array_merge($terms, $this->wpml_get_ordered_object_terms($object_ids, $taxonomy, array_merge($args, $t->args)));
				}
			}
		} else {
			$t = get_taxonomy($taxonomies[0]);
			if ( isset($t->args) && is_array($t->args) )
				$args = array_merge($args, $t->args);
		}

		extract($args, EXTR_SKIP);

		$orderby = "ORDER BY term_order";
		$order = 'ASC';

		$taxonomies = "'" . implode("', '", $taxonomies) . "'";
		$object_ids = implode(', ', $object_ids);

		$select_this = '';
		if ( 'all' == $fields )
			$select_this = 't.*, tt.*';
		else if ( 'ids' == $fields )
			$select_this = 't.term_id';
		else if ( 'names' == $fields )
			$select_this = 't.name';
		else if ( 'slugs' == $fields )
			$select_this = 't.slug';
		else if ( 'all_with_object_id' == $fields )
			$select_this = 't.*, tt.*, tr.object_id';

		$query = "SELECT $select_this FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies) AND tr.object_id IN ($object_ids) $orderby $order";

		if ( 'all' == $fields || 'all_with_object_id' == $fields ) {
			$_terms = $wpdb->get_results( $query );
			foreach ( $_terms as $key => $term ) {
				$_terms[$key] = sanitize_term( $term, $taxonomy, 'raw' );
			}
			$terms = array_merge( $terms, $_terms );
			update_term_cache( $terms );
		} else if ( 'ids' == $fields || 'names' == $fields || 'slugs' == $fields ) {
			$_terms = $wpdb->get_col( $query );
			$_field = ( 'ids' == $fields ) ? 'term_id' : 'name';
			foreach ( $_terms as $key => $term ) {
				$_terms[$key] = sanitize_term_field( $_field, $term, $term, $taxonomy, 'raw' );
			}
			$terms = array_merge( $terms, $_terms );
		} else if ( 'tt_ids' == $fields ) {
			$terms = $wpdb->get_col("SELECT tr.term_taxonomy_id FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.object_id IN ($object_ids) AND tt.taxonomy IN ($taxonomies) $orderby $order");
			foreach ( $terms as $key => $tt_id ) {
				$terms[$key] = sanitize_term_field( 'term_taxonomy_id', $tt_id, 0, $taxonomy, 'raw' ); // 0 should be the term id, however is not needed when using raw context.
			}
		}

		if ( ! $terms )
			$terms = array();

		return $terms;
	}


	/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 *                              Options
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * Load WPML default settings if no current settings can be found. Match
	 * the existing settings against the default settings to check their
	 * validity; if the revision is outdated, update the revision field and
	 * add possible missing options.
	 * 
	 * @since    1.0.0
	 *
	 * @param    boolean    $force Force to restore the default settings
	 *
	 * @return   boolean    True if settings were successfully added/updated
	 *                      False if anything went wrong.
	 */
	public function wpml_default_settings( $force = false ) {

		$options = get_option( $this->plugin_settings );
		$status  = false;

		if ( ( false === $options || ! is_array( $options ) ) || true == $force ) {
			delete_option( $this->plugin_settings );
			$status = add_option( $this->plugin_settings, $this->wpml_settings );
		}
		else if ( ! isset( $options['settings_revision'] ) || WPMovieLibrary::SETTINGS_REVISION > $options['settings_revision'] ) {
			if ( ! empty( $updated_options = $this->wpml_update_settings( $this->wpml_settings, $this->wpml_o() ) ) ) {
				$updated_options['settings_revision'] = WPMovieLibrary::SETTINGS_REVISION;
				$status = update_option( $this->plugin_settings, $updated_options );
			}
		}

		return $status;
	}

	/**
	 * Update plugin settings.
	 * 
	 * Compare the current settings with the default settings array to find
	 * newly added options and update the exiting settings. If default settings
	 * differ from the currently stored settings, add the new options to the
	 * latter.
	 *
	 * @since    1.0.0
	 * 
	 * @param    array    $default Default Plugin Settings to be compared to
	 *                             currently stored settings.
	 * @param    array    $options Currently stored settings, supposedly out
	 *                             of date.
	 * 
	 * @return   array             Updated and possibly unchanged settings
	 *                             array if everything went right, empty array
	 *                             if something bad happened.
	 */
	private function wpml_update_settings( $default, $options ) {

		if ( ! is_array( $default ) || ! is_array( $options ) )
			return array();

		foreach ( $default as $key => $value ) {
			if ( isset( $options[ $key ] ) && is_array( $value ) )
				$options[ $key ] = $this->wpml_update_settings( $value, $default[ $key ] );
			else if ( ! isset( $options[ $key ] ) ) {
				$a = array_search( $key, array_keys( $default ) );
				$options = array_merge(
					array_slice( $options, 0, $a ),
					array( $key => $value ),
					array_slice( $options, $a )
				);
			}
		}

		return $options;
	}

	/**
	 * Get TMDb API if available
	 *
	 * @since    1.0.0
	 */
	public function wpml_get_api_key() {
		$api_key = $this->wpml_o('tmdb-settings-APIKey');
		return ( '' != $api_key ? $api_key : false );
	}

	/**
	 * Are we on TMDb dummy mode?
	 *
	 * @since    1.0.0
	 */
	public function wpml_is_dummy() {
		$dummy = ( 1 == $this->wpml_o('tmdb-settings-dummy') ? true : false );
		return $dummy;
	}

	/**
	 * Built-in option finder/modifier
	 * Default behavior with no empty search and value params results in
	 * returning the complete WPML options' list.
	 * 
	 * If a search query is specified, navigate through the options'
	 * array and return the asked option if existing, empty string if it
	 * doesn't exist.
	 * 
	 * If a replacement value is specified and the search query is valid,
	 * update WPML options with new value.
	 * 
	 * Return can be string, boolean or array. If search, return array or
	 * string depending on search result. If value, return boolean true on
	 *  success, false on failure.
	 * 
	 * @param    string        Search query for the option: 'aaa-bb-c'. Default none.
	 * @param    string        Replacement value for the option. Default none.
	 * 
	 * @return   string|boolean|array        option array of string, boolean on update.
	 *
	 * @since    1.0.0
	 */
	public function wpml_o( $search = '', $value = null ) {

		$options = get_option( $this->plugin_settings, $this->wpml_settings );

		if ( '' != $search && is_null( $value ) ) {
			$s = explode( '-', $search );
			$o = $options;
			while ( count( $s ) ) {
				$k = array_shift( $s );
				if ( isset( $o[ $k ] ) )
					$o = $o[ $k ];
				else
					$o = '';
			}
		}
		else if ( '' != $search && ! is_null( $value ) ) {
			$s = explode( '-', $search );
			$this->wpml_o_( $options, $s, $value );
			$o = update_option( $this->plugin_settings, $options );
		}
		else {
			$o = $options;
		}

		return $o;
	}

	/**
	 * Built-in option modifier
	 * Navigate through WPML options to find a matching option and update
	 * its value.
	 * 
	 * @param    array         Options array passed by reference
	 * @param    string        key list to match the specified option
	 * @param    string        Replacement value for the option. Default none
	 *
	 * @since    1.0.0
	 */
	private function wpml_o_( &$array, $key, $value = '' ) {
		$a = &$array;
		foreach ( $key as $k )
			$a = &$a[ $k ];
		$a = $value;
	}

}
