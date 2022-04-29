<?php
/**
 * Plugin Name: SOF Videos
 * Plugin URI: https://github.com/spiritoffootball/sof-videos
 * Description: Provides a Video post type which can be embedded in a standard post and has its own navigable archives.
 * Author: Christian Wach
 * Version: 0.1
 * Author URI: https://haystack.co.uk
 * Text Domain: sof-videos
 * Domain Path: /languages
 *
 * @package Spirit_Of_Football_Videos
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set our version here.
define( 'SOF_VIDEOS_VERSION', '0.1' );

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
	 * @var object $cpt The Custom Post Type object.
	 */
	public $cpt;

	/**
	 * Metaboxes object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $metaboxes The Metaboxes object.
	 */
	public $metaboxes;

	/**
	 * Shortcodes object.
	 *
	 * @since 0.1
	 * @access public
	 * @var object $shortcodes The Shortcodes object.
	 */
	public $shortcodes;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Include files.
		$this->include_files();

		// Setup globals.
		$this->setup_globals();

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Include files.
	 *
	 * @since 0.1
	 */
	public function include_files() {

		// Include class files.
		include_once SOF_VIDEOS_PATH . 'includes/sof-videos-cpt.php';
		include_once SOF_VIDEOS_PATH . 'includes/sof-videos-metaboxes.php';
		include_once SOF_VIDEOS_PATH . 'includes/sof-videos-shortcodes.php';

	}

	/**
	 * Set up objects.
	 *
	 * @since 0.1
	 */
	public function setup_globals() {

		// Instantiate objects.
		$this->cpt = new Spirit_Of_Football_Videos_CPT();
		$this->metaboxes = new Spirit_Of_Football_Videos_Metaboxes();
		$this->shortcodes = new Spirit_Of_Football_Videos_Shortcodes();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Use translation.
		add_action( 'plugins_loaded', [ $this, 'translation' ] );

		// Hooks that always need to be present.
		$this->cpt->register_hooks();
		$this->metaboxes->register_hooks();
		$this->shortcodes->register_hooks();

	}

	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Pass through.
		$this->cpt->activate();

	}

	/**
	 * Actions to perform on plugin deactivation NOT deletion.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Pass through.
		$this->cpt->deactivate();

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
