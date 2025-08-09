<?php
/**
 * Metaboxes Class.
 *
 * Handles Metaboxes for Videos.
 *
 * @since 0.1
 *
 * @package Spirit_Of_Football_Videos
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Metaboxes Class
 *
 * A class that encapsulates all Metaboxes for Videos.
 *
 * @since 0.1
 */
class Spirit_Of_Football_Videos_Metaboxes {

	/**
	 * The meta key for the video URL.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $video_meta_key = 'sofvm_video';

	/**
	 * The meta key for the linked blog post.
	 *
	 * @since 0.1
	 * @access public
	 * @var string
	 */
	public $blog_meta_key = 'sofvm_post';

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

		// Add meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Intercept save.
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Adds meta boxes to admin screens.
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// Add our YouTube URL meta box.
		add_meta_box(
			'sof_video_options',
			__( 'Video URL', 'sof-videos' ),
			[ $this, 'video_box' ],
			'sofvm_video',
			'normal',
			'high'
		);

		// Add our Blog Post meta box.
		add_meta_box(
			'sof_video_blog_options',
			__( 'Blog Post', 'sof-videos' ),
			[ $this, 'blog_box' ],
			'sofvm_video',
			'normal',
			'high'
		);

	}

	/**
	 * Adds a "Video URL" meta box to Video edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function video_box( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'sof_video_url_settings', 'sof_video_url_nonce' );

		// Set key.
		$db_key = '_' . $this->video_meta_key;

		// Get value if if the custom field already has one.
		$val      = '';
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$val = get_post_meta( $post->ID, $db_key, true );
		}

		// Heading.
		echo '<p><strong>' . esc_html__( 'YouTube URL', 'sof-videos' ) . '</strong></p>' . "\n";

		// Label.
		echo '<label class="screen-reader-text" for="' . esc_attr( $this->video_meta_key ) . '">' . esc_html__( 'YouTube URL', 'sof-videos' ) . '</label>' . "\n";

		// Input.
		echo '<input id="' . esc_attr( $this->video_meta_key ) . '" name="' . esc_attr( $this->video_meta_key ) . '" type="text"  value="' . esc_attr( $val ) . '" />';

	}

	/**
	 * Adds a "Blog Post" meta box to Video edit screens.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The object for the current post/page.
	 */
	public function blog_box( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'sof_video_blog_id_settings', 'sof_video_blog_id_nonce' );

		// Get prefixed key.
		$db_key = '_' . $this->blog_meta_key;

		// Get value if if the custom field already has one.
		$val      = '';
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$val = get_post_meta( $post->ID, $db_key, true );
		}

		// Heading.
		echo '<p><strong>' . esc_html__( 'Blog Post ID', 'sof-videos' ) . '</strong></p>' . "\n";

		// Label.
		echo '<label class="screen-reader-text" for="' . esc_attr( $this->blog_meta_key ) . '">' . esc_html__( 'Blog Post ID', 'sof-videos' ) . '</label>' . "\n";

		// Input.
		echo '<input id="' . esc_attr( $this->blog_meta_key ) . '" name="' . esc_attr( $this->blog_meta_key ) . '" type="text"  value="' . esc_attr( $val ) . '" />';

	}

	/**
	 * Stores our additional params.
	 *
	 * @since 0.1
	 *
	 * @param integer $post_id the ID of the post or revision.
	 * @param integer $post the post object.
	 */
	public function save_post( $post_id, $post ) {

		// Store our video URL.
		$this->save_video_meta( $post );

		// Store our blog post ID.
		$this->save_blog_meta( $post );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * When a page is saved, this also saves the video URL.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the post or revision.
	 */
	private function save_video_meta( $post_obj ) {

		// Bail if no post.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = isset( $_POST['sof_video_url_nonce'] ) ? wp_unslash( $_POST['sof_video_url_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'sof_video_url_settings' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not video post type.
		if ( 'sofvm_video' !== $post->post_type ) {
			return;
		}

		// ---------------------------------------------------------------------
		// Okay, we're through...
		// ---------------------------------------------------------------------

		// Define prefixed key.
		$db_key = '_' . $this->video_meta_key;

		// Get video URL value.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$video_url = isset( $_POST[ $this->video_meta_key ] ) ? wp_unslash( $_POST[ $this->video_meta_key ] ) : '';

		// Save for this post.
		$this->save_meta( $post, $db_key, esc_url_raw( $video_url ) );

	}

	/**
	 * When a page is saved, this also saves the blog post ID.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post_obj The object for the post or revision.
	 */
	private function save_blog_meta( $post_obj ) {

		// Bail if no post.
		if ( ! $post_obj ) {
			return;
		}

		// Authenticate.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce = isset( $_POST['sof_video_blog_id_nonce'] ) ? wp_unslash( $_POST['sof_video_blog_id_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'sof_video_blog_id_settings' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) {
			return;
		}

		// Check for revision.
		if ( 'revision' === $post_obj->post_type ) {

			// Get parent.
			if ( 0 !== (int) $post_obj->post_parent ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not video post type.
		if ( 'sofvm_video' !== $post->post_type ) {
			return;
		}

		// ---------------------------------------------------------------------
		// Okay, we're through...
		// ---------------------------------------------------------------------

		// Define prefixed key.
		$db_key = '_' . $this->blog_meta_key;

		// Get blog post ID.
		$blog_post_id = ( isset( $_POST[ $this->blog_meta_key ] ) ) ? esc_sql( (int) $_POST[ $this->blog_meta_key ] ) : '';

		// Save for this post.
		$this->save_meta( $post, $db_key, $blog_post_id );

	}

	/**
	 * Utility to automate meta data saving.
	 *
	 * @since 0.1
	 *
	 * @param WP_Post $post The WordPress post object.
	 * @param string  $key The meta key.
	 * @param mixed   $data The data to be saved.
	 * @return mixed $data The data that was saved.
	 */
	private function save_meta( $post, $key, $data = '' ) {

		// If the custom field already has a value.
		$existing = get_post_meta( $post->ID, $key, true );
		if ( ! empty( $existing ) ) {

			// Update the data.
			update_post_meta( $post->ID, $key, $data );

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, $data );

		}

		// --<
		return $data;

	}

}
