<?php

/**
 * SOF Videos Custom Post Type Class
 *
 * A class that encapsulates a Custom Post Types for Videos
 *
 * @package WordPress
 * @subpackage SOF
 */
class Spirit_Of_Football_Videos_CPT {



	/**
	 * Constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// nothing

	}



	/**
	 * Register WordPress hooks
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// always create post types
		add_action( 'init', array( $this, 'create_post_type' ) );

		// make sure our feedback is appropriate
		add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

	}




	/**
	 * Actions to perform on plugin activation
	 *
	 * @since 0.1
	 */
	public function activate() {

		// pass through
		$this->create_post_type();

		// go ahead and flush
		flush_rewrite_rules();

	}



	/**
	 * Actions to perform on plugin deactivation (NOT deletion)
	 *
	 * @since 0.1
	 */
	public function deactivate() {

		// flush rules to reset
		flush_rewrite_rules();

	}



	// #########################################################################



	/**
	 * Create our Custom Post Type
	 *
	 * @since 0.1
	 */
	public function create_post_type() {

		// only call this once
		static $registered;

		// bail if already done
		if ( $registered ) return;

		// set up the post type called "Video"
		register_post_type( 'sofvm_video', array(

			// labels
			'labels' => array(
				'name' => __( 'Videos', 'sof-videos' ),
				'singular_name' => __( 'Video', 'sof-videos' ),
				'add_new' => _x( 'Add New', 'sofvm_video', 'sof-videos' ),
				'add_new_item' => __( 'Add New Video', 'sof-videos' ),
				'edit_item' => __( 'Edit Video', 'sof-videos' ),
				'new_item' => __( 'New Video', 'sof-videos' ),
				'all_items' => __( 'All Videos', 'sof-videos' ),
				'view_item' => __( 'View Video', 'sof-videos' ),
				'item_published' => __( 'Video published.', 'sof-videos' ),
				'item_published_privately' => __( 'Video published privately.', 'sof-videos' ),
				'item_reverted_to_draft' => __( 'Video reverted to draft.', 'sof-videos' ),
				'item_scheduled' => __( 'Video scheduled.', 'sof-videos' ),
				'item_updated' => __( 'Video updated.', 'sof-videos' ),
				'search_items' => __( 'Search Videos', 'sof-videos' ),
				'not_found' =>  __( 'No videos found', 'sof-videos' ),
				'not_found_in_trash' => __( 'No videos found in Trash', 'sof-videos' ),
				'parent_item_colon' => '',
				'menu_name' => 'Videos'
			),

			// defaults
			'description' => __( 'A videoblogging post type', 'sof-videos' ),
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'has_archive' => true,
			'query_var' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 20,
			'map_meta_cap' => true,

			// rewrite
			'rewrite' => array(
				'slug' => 'videos',
				'with_front' => false
			),

			// taxonomy
			'taxonomies' => array(
				'category'
			),

			// supports
			'supports' => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'comments',
				'revisions',
			)

		) );

		//flush_rewrite_rules();

		// flag
		$registered = true;

	}



	/**
	 * Override messages for a custom post type
	 *
	 * @param array $messages The existing messages
	 * @return array $messages The modified messages
	 */
	public function updated_messages( $messages ) {

		// access relevant globals
		global $post, $post_ID;

		// define custom messages for our custom post type
		$messages['sofvm_video'] = array(

			// unused - messages start at index 1
			0 => '',

			// item updated
			1 => sprintf(
				__( 'Video updated. <a href="%s">View video</a>', 'sof-videos' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// custom fields
			2 => __( 'Custom field updated.', 'sof-videos' ),
			3 => __( 'Custom field deleted.', 'sof-videos' ),
			4 => __( 'Video updated.', 'sof-videos' ),

			// item restored to a revision
			5 => isset( $_GET['revision'] ) ?

					// revision text
					sprintf(
						// translators: %s: date and time of the revision
						__( 'Video restored to revision from %s', 'sof-videos' ),
						wp_post_revision_title( (int) $_GET['revision'], false )
					) :

					// no revision
					false,

			// item published
			6 => sprintf(
				__( 'Video published. <a href="%s">View video</a>', 'sof-videos' ),
				esc_url( get_permalink( $post_ID ) )
			),

			// item saved
			7 => __( 'Video saved.', 'sof-videos' ),

			// item submitted
			8 => sprintf(
				__( 'Video submitted. <a target="_blank" href="%s">Preview video</a>', 'sof-videos' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			),

			// item scheduled
			9 => sprintf(
				__( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview video</a>', 'sof-videos' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ),
				strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) )
			),

			// draft updated
			10 => sprintf(
				__( 'Video draft updated. <a target="_blank" href="%s">Preview video</a>', 'sof-videos' ),
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) )
			)

		);

		// --<
		return $messages;

	}



} // class Spirit_Of_Football_Videos_CPT ends



