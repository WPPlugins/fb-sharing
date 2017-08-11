<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://jba-development.fr
 * @since      1.0.0
 *
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Facebook_Sharing
 * @subpackage Facebook_Sharing/includes
 * @author     Jean-Baptiste Aramendy <plugins@jba-development.fr>
 */
class Facebook_Sharing {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Facebook_Sharing_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'facebook-sharing';
		$this->version = '1.0.10';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Facebook_Sharing_Loader. Orchestrates the hooks of the plugin.
	 * - Facebook_Sharing_i18n. Defines internationalization functionality.
	 * - Facebook_Sharing_Admin. Defines all hooks for the admin area.
	 * - Facebook_Sharing_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebook-sharing-loader.php';
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebook-sharing-i18n.php';
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-facebook-sharing-admin.php';
		 
		$this->loader = new Facebook_Sharing_Loader();
	}
	
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Facebook_Sharing_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Facebook_Sharing_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}
		
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Facebook_Sharing_Admin( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_pages' );
		$this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'fs_submit_box' );
		$this->loader->add_action( 'publish_post', $plugin_admin, 'fs_publish', 10, 2 );
		$this->loader->add_action( 'future_post', $plugin_admin, 'fs_publish', 10, 2 );
		$this->loader->add_action( 'delete_post', $plugin_admin, 'fs_delete_post' );
		$this->loader->add_action( 'admin_head-post-new.php', $plugin_admin, 'fs_post_context_help' );
		$this->loader->add_action( 'admin_head-post.php', $plugin_admin, 'fs_post_context_help' );
		
		$this->loader->add_action( 'plugins_loaded', $this, 'fs_fb_version_check' );
	}	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}
	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Facebook_Sharing_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * Made some changes when update plugin
	 *
	 * @since     1.0.5
	 */
	public function fs_fb_version_check() {
		$last_version = get_option( 'fs_options_version' );
		
		// Modifications for versions before 1.0.4
		if( $last_version != $this->version && intval( substr( $last_version, 0, 1 ) ) == 1 && intval( substr( $last_version, 2, 1 ) ) == 0 && intval( substr( $last_version, 4 ) ) <= 4 ) {

			// Update array of published posts
			$published_posts = get_option( 'fs_published_posts' );
			foreach( $published_posts as $key => $value ) {
				if( !array_key_exists( 'fb_page_id', $value ) ) {
					$value[ 'fb_page_id' ] = 'unknow';
					$published_posts[ $key ] = $value;
				}
			}
			update_option( 'fs_published_posts', $published_posts );			
			
			// Upgrade array of Facebook pages
			$fb_pages = get_option( 'fs_options_fb_pages' );
			update_option( 'fs_options_fb_page', $fb_pages[ get_option( 'fs_options_fb_page' ) ][ 'id' ] );
			foreach( $fb_pages as $key => $value ) {
				$fb_pages[ $value[ 'id' ] ] = $value;
				unset( $fb_pages[ $key ] );
			}
			update_option( 'fs_options_fb_pages', $fb_pages );
		}
		
		if( $last_version != $this->version && intval( substr( $last_version, 0, 1 ) ) == 1 && intval( substr( $last_version, 2, 1 ) ) == 0 && intval( substr( $last_version, 4 ) ) <= 9 ) {
			$published_posts = get_option( 'fs_published_posts' );
			foreach( $published_posts as $key => $value ) {
				if( !isset( $published_posts[ $key ][ 'state' ] ) ) {
					if( !empty( $published_posts[ $key ][ 'fb_post_id' ] ) ) {
						$published_posts[ $key ][ 'state' ] = 'published';
					} else {
						$published_posts[ $key ][ 'state' ] = 'not-published';
					}
				}
			}
			update_option( 'fs_published_posts', $published_posts );
		}
		
		if( $last_version != $this->version ) {
			// Update version number
			update_option( 'fs_options_version', $this->version );
		}
	}

	/**
	 * GET request to Facebook Graph API
	 *
	 * @param $get 		string 	Target of the request
	 * @param $params 	array 	Parameters of the request
	 * @return $result 	array 	Array of the result
	 * @since 1.0.0
	 */
	public static function fs_fb_get_request( $get, $params, $return = 'array' ) {
		$request_url = 'https://graph.facebook.com/' . FS_FB_GRAPH_VERSION . '/' . $get;
		
		$compt = 0;
		foreach( $params as $key => $value ) {
			if( $compt == 0 ) {
				$request_url .= '?' . $key . '=' . $value;
			} else {
				$request_url .= '&' . $key . '=' . $value;
			}
			$compt += 1;
		}
				
		$response_json = wp_remote_get( $request_url );
		
		if( is_array( $response_json ) )	{				
			if( isset( $response_json[ 'body' ] ) ) {
				$result_array = json_decode( $response_json[ 'body' ], true );
			}
		}
		
		if( $return != 'array' ) {
			$result = $result_array[ $return ];
		} else {
			$result = $result_array;
		}
		
		return $result;
	}
	
	/**
	 * POST request to Facebook Graph API
	 *
	 * @since 1.0.0
	 */
	public static function fs_fb_post_request ($post, $params, $return = 'array' ) {
		$request_url = 'https://graph.facebook.com/' . FS_FB_GRAPH_VERSION . '/' . $post;
		
		$post_params = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => $params,
			'cookies'     => array()
		);

		$response_json = wp_remote_post( $request_url, $post_params );
		
		if( is_array( $response_json ) )	{				
			if( isset( $response_json[ 'body' ] ) ) {
				$result_array = json_decode( $response_json[ 'body' ], true );
			}
		}
		
		if( $return != 'array' ) {
			$result = $result_array[ $return ];
		} else {
			$result = $result_array;
		}
		
		return $result;
	}
}