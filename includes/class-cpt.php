<?php
/**
 * Custom Post Type Class.
 *
 * Handles the Custom Post Type for Videos.
 *
 * @since 0.1
 *
 * @package Spirit_Of_Football_Videos
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Custom Post Type Class.
 *
 * A class that encapsulates a Custom Post Type for Videos.
 *
 * @since 0.1
 */
class Spirit_Of_Football_Videos_CPT {

	/**
	 * Custom Post Type name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $post_type_name = 'sofvm_video';

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Initialise when plugin is loaded.
		add_action( 'sof_videos/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 1.0.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Registers hook callbacks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Activation and deactivation.
		add_action( 'sof_videos/activated', [ $this, 'activate' ] );
		add_action( 'sof_videos/deactivated', [ $this, 'deactivate' ] );

		// Always create post types.
		add_action( 'init', [ $this, 'post_type_create' ] );

		// Make sure our feedback is appropriate.
		add_filter( 'post_updated_messages', [ $this, 'post_type_messages' ] );

	}

	/**
	 * Actions to perform on plugin activation.
	 *
	 * @since 0.1
	 */
	public function activate() {

		// Pass through.
		$this->post_type_create();

		// Go ahead and flush.
		flush_rewrite_rules();

	}

	/**
	 * Actions to perform on plugin deactivation NOT deletion.
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// Flush rules to reset.
		flush_rewrite_rules();

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Creates our Custom Post Type.
	 *
	 * @since 1.0.0
	 */
	public function post_type_create() {

		// Only do this once.
		static $registered;
		if ( $registered ) {
			return;
		}

		// Define labels.
		$labels = [
			'name'                     => __( 'Videos', 'sof-videos' ),
			'singular_name'            => __( 'Video', 'sof-videos' ),
			'add_new'                  => _x( 'Add New', 'post type add new item label', 'sof-videos' ),
			'add_new_item'             => __( 'Add New Video', 'sof-videos' ),
			'edit_item'                => __( 'Edit Video', 'sof-videos' ),
			'new_item'                 => __( 'New Video', 'sof-videos' ),
			'all_items'                => __( 'All Videos', 'sof-videos' ),
			'view_item'                => __( 'View Video', 'sof-videos' ),
			'item_published'           => __( 'Video published.', 'sof-videos' ),
			'item_published_privately' => __( 'Video published privately.', 'sof-videos' ),
			'item_reverted_to_draft'   => __( 'Video reverted to draft.', 'sof-videos' ),
			'item_scheduled'           => __( 'Video scheduled.', 'sof-videos' ),
			'item_updated'             => __( 'Video updated.', 'sof-videos' ),
			'search_items'             => __( 'Search Videos', 'sof-videos' ),
			'not_found'                => __( 'No Videos found', 'sof-videos' ),
			'not_found_in_trash'       => __( 'No Videos found in Trash', 'sof-videos' ),
			'parent_item_colon'        => '',
			'menu_name'                => __( 'Videos', 'sof-videos' ),
		];

		// Build Post Type args.
		$args = [

			'labels'              => $labels,

			// Defaults.
			'menu_icon'           => 'dashicons-video-alt3',
			'description'         => __( 'A videoblogging post type', 'sof-videos' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'has_archive'         => true,
			'query_var'           => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_position'       => 54,
			'map_meta_cap'        => true,

			// Rewrite.
			'rewrite'             => [
				'slug'       => 'videos',
				'with_front' => false,
			],

			// Taxonomy.
			'taxonomies'          => [
				'category',
			],

			// Supports.
			'supports'            => [
				'title',
				'editor',
				'author',
				'thumbnail',
				'comments',
				'revisions',
			],

		];

		// Set up the post type called "Video".
		register_post_type( $this->post_type_name, $args );

		// Set flag.
		$registered = true;

	}

	/**
	 * Overrides messages for a Custom Post Type.
	 *
	 * @since 0.1
	 *
	 * @param array $messages The existing messages.
	 * @return array $messages The modified messages.
	 */
	public function post_type_messages( $messages ) {

		// Access relevant globals.
		global $post, $post_ID;

		// Define custom messages for our custom post type.
		$messages[ $this->post_type_name ] = [

			// Unused - messages start at index 1.
			0  => '',

			// Item updated.
			1  => sprintf(
				/* translators: %s: Post permalink URL. */
				__( 'Video updated. <a href="%s">View Video</a>', 'sof-videos' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Custom fields.
			2  => __( 'Custom field updated.', 'sof-videos' ),
			3  => __( 'Custom field deleted.', 'sof-videos' ),
			4  => __( 'Video updated.', 'sof-videos' ),

			// Item restored to a revision.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			5  => isset( $_GET['revision'] ) ?

				// Revision text.
				sprintf(
					/* translators: %s: Title of the revision. */
					__( 'Video restored to revision from %s', 'sof-videos' ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					wp_post_revision_title( (int) $_GET['revision'], false )
				) :

				// No revision.
				false,

			// Item published.
			6  => sprintf(
				/* translators: %s: Post permalink URL. */
				__( 'Video published. <a href="%s">View Video</a>', 'sof-videos' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Item saved.
			7  => __( 'Video saved.', 'sof-videos' ),

			// Item submitted.
			8  => sprintf(
				/* translators: %s: Post preview URL. */
				__( 'Video submitted. <a target="_blank" href="%s">Preview Video</a>', 'sof-videos' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

			// Item scheduled.
			9  => sprintf(
				/* translators: 1: Publish box date format, see http://php.net/date, 2: Post date, 3: Post permalink. */
				__( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Video</a>', 'sof-videos' ),
				/* translators: Publish box date format, see http://php.net/date */
				date_i18n( __( 'M j, Y @ G:i', 'sof-videos' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),

			// Draft updated.
			10 => sprintf(
				/* translators: %s: Post preview URL. */
				__( 'Video draft updated. <a target="_blank" href="%s">Preview Video</a>', 'sof-videos' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

		];

		// --<
		return $messages;

	}

}
