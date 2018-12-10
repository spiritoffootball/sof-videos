<?php /*
--------------------------------------------------------------------------------
Plugin Name: SOF Videos
Plugin URI: http://spiritoffootball.com
Description: Provides a "Video" post type which can be embedded in a standard post and has its own navigable archives.
Author: Christian Wach
Version: 0.1
Author URI: http://haystack.co.uk
--------------------------------------------------------------------------------
*/



// set our version here
define( 'SOF_VIDEOS_VERSION', '0.1' );

// store reference to this file
if ( ! defined( 'SOF_VIDEOS_FILE' ) ) {
	define( 'SOF_VIDEOS_FILE', __FILE__ );
}

// store URL to this plugin's directory
if ( ! defined( 'SOF_VIDEOS_URL' ) ) {
	define( 'SOF_VIDEOS_URL', plugin_dir_url( SOF_VIDEOS_FILE ) );
}

// store PATH to this plugin's directory
if ( ! defined( 'SOF_VIDEOS_PATH' ) ) {
	define( 'SOF_VIDEOS_PATH', plugin_dir_path( SOF_VIDEOS_FILE ) );
}



/**
 * SOF Videos Class
 *
 * A class that encapsulates the functionality of this plugin.
 *
 * @package WordPress
 * @subpackage SOF
 */
class Spirit_Of_Football_Videos {



	/**
	 * Custom Post Type object
	 *
	 * @since 0.1
	 * @access public
	 * @var object $cpt The Custom Post Type object
	 */
	public $cpt;



	/**
	 * Metaboxes object
	 *
	 * @since 0.1
	 * @access public
	 * @var object $metaboxes The Metaboxes object
	 */
	public $metaboxes;



	/**
	 * Shortcodes object
	 *
	 * @since 0.1
	 * @access public
	 * @var object $shortcodes The Shortcodes object
	 */
	public $shortcodes;



	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// include files
		$this->include_files();

		// setup globals
		$this->setup_globals();

		// register hooks
		$this->register_hooks();

	}



	/**
	 * Include files
	 *
	 * @since 0.1
	 */
	public function include_files() {

		// include CPT class
		include_once SOF_VIDEOS_PATH . 'includes/sof-videos-cpt.php';

		// include Metaboxes class
		include_once SOF_VIDEOS_PATH . 'includes/sof-videos-metaboxes.php';

		// include Shortcodes class
		include_once SOF_VIDEOS_PATH . 'includes/sof-videos-shortcodes.php';

	}



	/**
	 * Set up objects
	 *
	 * @since 0.1
	 */
	public function setup_globals() {

		// init CPT object
		$this->cpt = new Spirit_Of_Football_Videos_CPT;

		// init Metaboxes object
		$this->metaboxes = new Spirit_Of_Football_Videos_Metaboxes;

		// init Shortcodes object
		$this->shortcodes = new Spirit_Of_Football_Videos_Shortcodes;

	}



	/**
	 * Register WordPress hooks
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// use translation
		add_action( 'plugins_loaded', array( $this, 'translation' ) );

		// hooks that always need to be present
		$this->cpt->register_hooks();
		$this->metaboxes->register_hooks();
		$this->shortcodes->register_hooks();

	}



	/**
	 * Actions to perform on plugin activation
	 *
	 * @since 0.1
	 */
	public function activate() {

		// pass through
		$this->cpt->activate();

	}



	/**
	 * Actions to perform on plugin deactivation (NOT deletion)
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// pass through
		$this->cpt->deactivate();

	}



	/**
	 * Loads translation, if present
	 *
	 * @since 0.1
	 */
	function translation() {

		// only use, if we have it...
		if ( function_exists( 'load_plugin_textdomain' ) ) {

			// not used, as there are no translations as yet
			load_plugin_textdomain(

				// unique name
				'sof-videos',

				// deprecated argument
				false,

				// relative path to directory containing translation files
				dirname( plugin_basename( SOF_VIDEOS_FILE ) ) . '/languages/'

			);

		}

	}



} // class Spirit_Of_Football_Videos ends



// Instantiate the class
global $sof_videos_plugin;
$sof_videos_plugin = new Spirit_Of_Football_Videos();

// activation
register_activation_hook( __FILE__, array( $sof_videos_plugin, 'activate' ) );

// deactivation
register_deactivation_hook( __FILE__, array( $sof_videos_plugin, 'deactivate' ) );



