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
	 * Video meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $video_meta_key The meta key for the video URL.
	 */
	public $video_meta_key = 'sofvm_video';

	/**
	 * Linked Blog Post meta key.
	 *
	 * @since 0.1
	 * @access public
	 * @var str $blog_meta_key The meta key for the linked blog post.
	 */
	public $blog_meta_key = 'sofvm_post';

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Nothing.

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 0.1
	 */
	public function register_hooks() {

		// Add meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Intercept save.
		add_action( 'save_post', [ $this, 'save_post' ], 1, 2 );

	}

	// -------------------------------------------------------------------------

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
		$val = '';
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$val = get_post_meta( $post->ID, $db_key, true );
		}

		// Heading.
		echo '<p><strong>' . __( 'YouTube URL', 'sof-videos' ) . '</strong></p>' . "\n";

		// Label.
		echo '<label class="screen-reader-text" for="' . $this->video_meta_key . '">' . __( 'YouTube URL', 'sof-videos' ) . '</label>' . "\n";

		// Input.
		echo '<input id="' . $this->video_meta_key . '" name="' . $this->video_meta_key . '" type="text"  value="' . $val . '" />';

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
		$val = '';
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$val = get_post_meta( $post->ID, $db_key, true );
		}

		// Heading.
		echo '<p><strong>' . __( 'Blog Post ID', 'sof-videos' ) . '</strong></p>' . "\n";

		// Label.
		echo '<label class="screen-reader-text" for="' . $this->blog_meta_key . '">' . __( 'Blog Post ID', 'sof-videos' ) . '</label>' . "\n";

		// Input.
		echo '<input id="' . $this->blog_meta_key . '" name="' . $this->blog_meta_key . '" type="text"  value="' . $val . '" />';

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

	// -------------------------------------------------------------------------

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
		$nonce = isset( $_POST['sof_video_url_nonce'] ) ? $_POST['sof_video_url_nonce'] : '';
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
		if ( $post_obj->post_type == 'revision' ) {

			// Get parent.
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not video post type.
		if ( $post->post_type != 'sofvm_video' ) {
			return;
		}

		// ---------------------------------------------------------------------
		// Okay, we're through...
		// ---------------------------------------------------------------------

		// Define prefixed key.
		$db_key = '_' . $this->video_meta_key;

		// Get video url value.
		$video_url = ( isset( $_POST[ $this->video_meta_key ] ) ) ? esc_sql( $_POST[ $this->video_meta_key ] ) : '';

		// Save for this post.
		$this->save_meta( $post, $db_key, $video_url );

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
		$nonce = isset( $_POST['sof_video_blog_id_nonce'] ) ? $_POST['sof_video_blog_id_nonce'] : '';
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
		if ( $post_obj->post_type == 'revision' ) {

			// Get parent.
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Bail if not video post type.
		if ( $post->post_type != 'sofvm_video' ) {
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
	 * @param string $key The meta key.
	 * @param mixed $data The data to be saved.
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
