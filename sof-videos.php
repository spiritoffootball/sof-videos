<?php
/**
 * SOF Videos
 *
 * Plugin Name:       SOF Videos
 * Description:       Provides a "Video" post type which can be embedded in a standard post and has its own navigable archives.
 * Version:           1.0.0a
 * Plugin URI:        https://github.com/spiritoffootball/sof-videos
 * GitHub Plugin URI: https://github.com/spiritoffootball/sof-videos
 * Author:            Christian Wach
 * Author URI:        https://haystack.co.uk
 * Text Domain:       sof-videos
 * Domain Path:       /languages
 *
 * @package Spirit_Of_Football_Videos
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set our version here.
define( 'SOF_VIDEOS_VERSION', '1.0.0a' );

// Store reference to this file.
if ( ! defined( 'SOF_VIDEOS_FILE' ) ) {
	define( 'SOF_VIDEOS_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'SOF_VIDEOS_URL' ) ) {
	define( 'SOF_VIDEOS_URL', plugin_dir_url( SOF_VIDEOS_FILE ) );
}

// Store PATH to this plugin's directory.
if ( ! defined( 'SOF_VIDEOS_PATH' ) ) {
	define( 'SOF_VIDEOS_PATH', plugin_dir_path( SOF_VIDEOS_FILE ) );
}

/**
 * SOF Videos Class.
 *
 * A class that encapsulates the functionality of this plugin.
 *
 * @since 0.1
 */
class Spirit_Of_Football_Videos {

	/**
	 * Custom Post Type object.
	 *
	 * @since 0.1
	 * @access public
	 * @var Spirit_Of_Football_Videos_CPT
	 */
	public $cpt;

	/**
	 * Metaboxes object.
	 *
	 * @since 0.1
	 * @access public
	 * @var Spirit_Of_Football_Videos_Metaboxes
	 */
	public $metaboxes;

	/**
	 * Shortcodes object.
	 *
	 * @since 0.1
	 * @access public
	 * @var Spirit_Of_Football_Videos_Shortcodes
	 */
	public $shortcodes;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Initialise this plugin.
		add_action( 'plugins_loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises the plugin.
	 *
	 * @since 1.0.0
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap plugin.
		$this->include_files();
		$this->setup_globals();
		$this->register_hooks();

		/**
		 * Fires when this plugin has fully loaded.
		 *
		 * This action is used internally by this plugin to initialise its objects
		 * and ensures that all includes and setup has occurred beforehand.
		 *
		 * @since 1.0.0
		 */
		do_action( 'sof_videos/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Include files.
	 *
	 * @since 0.1
	 */
	private function include_files() {

		// Include class files.
		require SOF_VIDEOS_PATH . 'includes/class-cpt.php';
		require SOF_VIDEOS_PATH . 'includes/class-metaboxes.php';
		require SOF_VIDEOS_PATH . 'includes/class-shortcodes.php';

	}

	/**
	 * Set up objects.
	 *
	 * @since 0.1
	 */
	private function setup_globals() {

		// Instantiate objects.
		$this->cpt        = new Spirit_Of_Football_Videos_CPT();
		$this->metaboxes  = new Spirit_Of_Football_Videos_Metaboxes();
		$this->shortcodes = new Spirit_Of_Football_Videos_Shortcodes();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	private function register_hooks() {

		// Use translation.
		add_action( 'init', [ $this, 'translation' ] );

	}

	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Make sure plugin is bootstrapped.
		$this->initialise();

		/**
		 * Fires when this plugin has been activated.
		 *
		 * Used internally by:
		 *
		 * * Spirit_Of_Football_Videos_CPT::activate() (Priority: 10)
		 *
		 * @since 1.0.0
		 */
		do_action( 'sof_videos/activated' );

	}

	/**
	 * Actions to perform on plugin deactivation NOT deletion.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Make sure plugin is bootstrapped.
		$this->initialise();

		/**
		 * Fires when this plugin has been deactivated.
		 *
		 * Used internally by:
		 *
		 * * Spirit_Of_Football_Videos_CPT::deactivate() (Priority: 10)
		 *
		 * @since 1.0.0
		 */
		do_action( 'sof_videos/deactivated' );

	}

	/**
	 * Loads translations.
	 *
	 * @since 0.1
	 */
	public function translation() {

		// Load translations.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			'sof-videos', // Unique name.
			false, // Deprecated argument.
			dirname( plugin_basename( SOF_VIDEOS_FILE ) ) . '/languages/' // Relative path to files.
		);

	}

}

/**
 * Utility to get a reference to this plugin.
 *
 * @since 0.1
 *
 * @return Spirit_Of_Football_Videos $plugin The plugin reference.
 */
function spirit_of_football_videos() {

	// Store instance in static variable.
	static $plugin = false;

	// Maybe return instance.
	if ( false === $plugin ) {
		$plugin = new Spirit_Of_Football_Videos();
	}

	// --<
	return $plugin;

}

// Initialise plugin now.
spirit_of_football_videos();

// Activation.
register_activation_hook( __FILE__, [ spirit_of_football_videos(), 'activate' ] );

// Deactivation.
register_deactivation_hook( __FILE__, [ spirit_of_football_videos(), 'deactivate' ] );
