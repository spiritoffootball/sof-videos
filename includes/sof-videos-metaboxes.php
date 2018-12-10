<?php

/**
 * SOF Videos Metaboxes Class
 *
 * A class that encapsulates all Metaboxes for Videos
 *
 * @package WordPress
 * @subpackage SOF
 */
class Spirit_Of_Football_Videos_Metaboxes {



	/**
	 * Video meta key
	 *
	 * @since 0.1
	 * @access public
	 * @var str $video_meta_key The meta key for the video URL
	 */
	public $video_meta_key = 'sofvm_video';



	/**
	 * Linked Blog Post meta key
	 *
	 * @since 0.1
	 * @access public
	 * @var str $blog_meta_key The meta key for the linked blog post
	 */
	public $blog_meta_key = 'sofvm_post';



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

		// exclude from SOF eV for now...
		//if ( 'sofev' == sof_get_site() ) return;

		// add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// intercept save
		add_action( 'save_post', array( $this, 'save_post' ), 1, 2 );

	}




	// #########################################################################



	/**
	 * Adds meta boxes to admin screens
	 *
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		// add our YouTube URL meta box
		add_meta_box(
			'sof_video_options',
			__( 'Video URL', 'sof-videos' ),
			array( $this, 'video_box' ),
			'sofvm_video',
			'normal',
			'high'
		);

		// add our Blog Post meta box
		add_meta_box(
			'sof_video_blog_options',
			__( 'Blog Post', 'sof-videos' ),
			array( $this, 'blog_box' ),
			'sofvm_video',
			'normal',
			'high'
		);

	}



	/**
	 * Adds a "Video URL" meta box to Video edit screens
	 *
	 * @since 0.1
	 * @param WP_Post $post The object for the current post/page
	 */
	public function video_box( $post ) {

		// Use nonce for verification
		wp_nonce_field( 'sof_video_url_settings', 'sof_video_url_nonce' );

		// set key
		$db_key = '_' . $this->video_meta_key;

		// get value if if the custom field already has one
		$val = '';
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$val = get_post_meta( $post->ID, $db_key, true );
		}

		// heading
		echo '<p><strong>' . __( 'YouTube URL', 'sof-videos' ) . '</strong></p>' . "\n";

		// label
		echo '<label class="screen-reader-text" for="' . $this->video_meta_key . '">' . __( 'YouTube URL', 'sof-videos' ) . '</label>' . "\n";

		// input
		echo '<input id="' . $this->video_meta_key . '" name="' . $this->video_meta_key . '" type="text"  value="' . $val . '" />';

	}



	/**
	 * Adds a "Blog Post" meta box to Video edit screens
	 *
	 * @since 0.1
	 * @param WP_Post $post The object for the current post/page
	 */
	public function blog_box( $post ) {

		// Use nonce for verification
		wp_nonce_field( 'sof_video_blog_id_settings', 'sof_video_blog_id_nonce' );

		// set key
		$db_key = '_' . $this->blog_meta_key;

		// get value if if the custom field already has one
		$val = '';
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {
			$val = get_post_meta( $post->ID, $db_key, true );
		}

		// heading
		echo '<p><strong>' . __( 'Blog Post ID', 'sof-videos' ) . '</strong></p>' . "\n";

		// label
		echo '<label class="screen-reader-text" for="' . $this->blog_meta_key . '">' . __( 'Blog Post ID', 'sof-videos' ) . '</label>' . "\n";

		// input
		echo '<input id="' . $this->blog_meta_key . '" name="' . $this->blog_meta_key . '" type="text"  value="' . $val . '" />';

	}



	/**
	 * Stores our additional params
	 *
	 * @since 0.1
	 * @param integer $post_id the ID of the post (or revision)
	 * @param integer $post the post object
	 */
	public function save_post( $post_id, $post ) {

		// store our video URL
		$this->_save_video_meta( $post );

		// store our blog post ID
		$this->_save_blog_meta( $post );

	}



	// #########################################################################



	/**
	 * When a page is saved, this also saves the video URL
	 *
	 * @since 0.1
	 * @param WP_Post $post_obj The object for the post (or revision)
	 */
	private function _save_video_meta( $post_obj ) {

		// if no post, kick out
		if ( ! $post_obj ) return;

		// authenticate
		$nonce = isset( $_POST['sof_video_url_nonce'] ) ? $_POST['sof_video_url_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'sof_video_url_settings' ) ) return;

		// is this an auto save routine?
		if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) return;

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) return;

		// check for revision
		if ( $post_obj->post_type == 'revision' ) {

			// get parent
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// bail if not video post type
		if ( $post->post_type != 'sofvm_video' ) return;

		// ---------------------------------------------------------------------
		// okay, we're through...
		// ---------------------------------------------------------------------

		// define key
		$db_key = '_' . $this->video_meta_key;

		// get video url value
		$video_url = ( isset( $_POST[$this->video_meta_key] ) ) ? esc_sql( $_POST[$this->video_meta_key] ) : '';

		// save for this post
		$this->_save_meta( $post, $db_key, $video_url );

	}



	/**
	 * When a page is saved, this also saves the blog post ID
	 *
	 * @since 0.1
	 * @param WP_Post $post_obj The object for the post (or revision)
	 */
	private function _save_blog_meta( $post_obj ) {

		// if no post, kick out
		if ( ! $post_obj ) return;

		// authenticate
		$nonce = isset( $_POST['sof_video_blog_id_nonce'] ) ? $_POST['sof_video_blog_id_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'sof_video_blog_id_settings' ) ) return;

		// is this an auto save routine?
		if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) return;

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_obj->ID ) ) return;

		// check for revision
		if ( $post_obj->post_type == 'revision' ) {

			// get parent
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// bail if not video post type
		if ( $post->post_type != 'sofvm_video' ) return;

		// ---------------------------------------------------------------------
		// okay, we're through...
		// ---------------------------------------------------------------------

		// define key
		$db_key = '_' . $this->blog_meta_key;

		// get blog post ID
		$blog_post_id = ( isset( $_POST[$this->blog_meta_key] ) ) ? esc_sql( (int) $_POST[$this->blog_meta_key] ) : '';

		// save for this post
		$this->_save_meta( $post, $db_key, $blog_post_id );

	}



	/**
	 * Utility to automate meta data saving
	 *
	 * @since 0.1
	 * @param WP_Post $post_obj The WordPress post object
	 * @param string $key The meta key
	 * @param mixed $data The data to be saved
	 * @return mixed $data The data that was saved
	 */
	private function _save_meta( $post, $key, $data = '' ) {

		// if the custom field already has a value
		$existing = get_post_meta( $post->ID, $key, true );
		if ( ! empty( $existing ) ) {

			// update the data
			update_post_meta( $post->ID, $key, $data );

		} else {

			// add the data
			add_post_meta( $post->ID, $key, $data );

		}

		// --<
		return $data;

	}




} // class Spirit_Of_Football_Videos_Metaboxes ends



