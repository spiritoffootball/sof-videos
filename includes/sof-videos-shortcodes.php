<?php

/**
 * SOF Videos Custom Shortcodes Class
 *
 * A class that encapsulates all Shortcodes for Videos
 *
 * @package WordPress
 * @subpackage SOF
 */
class Spirit_Of_Football_Videos_Shortcodes {



	/**
	 * Video meta key
	 *
	 * @since 0.1
	 * @access public
	 * @var str $video_meta_key The meta key for sticky videos
	 */
	public $video_meta_key = 'sofvm_video';



	/**
	 * Linked Blog Post meta key
	 *
	 * @since 0.1
	 * @access public
	 * @var str $blog_meta_key The meta key for sticky videos
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

		// register shortcodes
		add_shortcode( 'sofvideo', array( $this, 'video_shortcode' ) );

		// modify the content
		add_filter( 'the_content', array( $this, 'the_content' ), 20, 1 );

	}




	// #########################################################################




	/**
	 * Add a video to a page/post via a shortcode
	 *
	 * @since 0.1
	 * @param array $attr The saved shortcode attributes
	 * @param str $content The enclosed content of the shortcode
	 * @return str $content The HTML-formatted video custom post type
	 */
	public function video_shortcode( $attr, $content = null ) {

		// get params
		extract( shortcode_atts( array(
			'id'	=> '',
			'align'	=> 'none'
		), $attr ) );

		// kick out if there's anything amiss
		if ( $id == '' ) return;

		// return something else for feeds
		if ( is_feed() ) {
			return '<p>' . __( 'Visit the site to see the video', 'sof-videos' ) . '</p>';
		}

		// get the video post
		$video_post = get_post( $id );

		// check we got one
		if ( is_object( $video_post ) ) {

			// set it up
			setup_postdata( $video_post );

			// parse content
			$content = apply_filters( 'the_content', get_the_content() );

			// we need to manually apply our content filter because $post is the
			// object for the post into which the video has been embedded

			// get embed code
			$embed = $this->_get_embed( $video_post );

			// get link to video post
			$link = $this->_get_link_to_video_post( $video_post );

			// prepend
			$content = $embed . $content . $link;

			// give alignment class to div
			switch( $align ) {

				case 'right': $class = 'alignright'; break;
				case 'left': $class = 'alignleft'; break;
				case 'none': $class = 'alignnone'; break;
				default: $class = 'alignnone';

			}

			// give it an alignment
			$content = '<div class="sofvm_embed ' . $class . '">' . $content . '</div>';

			// reset just in case
			wp_reset_postdata();

		}

		// --<
		return $content;

	}




	/**
	 * Prepend video to content
	 *
	 * @param str $content The existing content
	 * @return str $content The modified content
	 */
	public function the_content( $content ) {

		// reference our post
		global $post;

		// only filter our custom post type
		if ( $post->post_type == 'sofvm_video' ) {

			// get embed code
			$embed_code = $this->_get_embed( $post );

			// get link to blog post
			$link = $this->_get_context_link( $post );

			// prepend
			$content = $embed_code . $content . $link;

		}

		// --<
		return $content;

	}



	// #########################################################################




	/**
	 * Get the video embed for a post.
	 *
	 * @param object $post The post on which the video will be embedded
	 * @return str $embed_code The HTML-formatted video
	 */
	private function _get_embed( $post ) {

		// intro text
		$intro = __( 'This video is missing because we are in the process of uploading all our videos to YouTube. Please be patient: the video will appear at some point! In the meantime, visit ', 'sof-videos' );

		// link text
		$follow = __( 'The Ball on YouTube', 'sof-videos' );

		// init embed code
		$embed_code = '<div class="sofvm_video"><p><strong>' . $intro . '<a href="http://www.youtube.com/sofcic">' . $follow . '</a>.</strong></p></div>' . "\n\n";

		// get DB key
		$db_key = '_' . $this->video_meta_key;

		// if the video custom field has a value
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {

			// get it
			$video_url = get_post_meta( $post->ID, $db_key, true );

			// get embed
			$embed_code = wp_oembed_get( $video_url, array(
				'width' => apply_filters( 'sofvm_video_width', 640 ),
				'height' => apply_filters( 'sofvm_video_height', 360 ),
			) );

			// wrap embed in a div for styling options
			$embed_code = '<div class="sofvm_video">' . $embed_code . '</div>' . "\n\n";

		}

		// --<
		return $embed_code;

	}



	/**
	 * Get the link to where the video is embedded.
	 *
	 * @param object $post The video post object
	 * @return str $link The link to the blog post
	 */
	private function _get_context_link( $post ) {

		// init link
		$link = '';

		// get DB key
		$db_key = '_' . $this->blog_meta_key;

		// if the post custom field has a value
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {

			// get it
			$id = get_post_meta( $post->ID, $db_key, true );

			// get permalink to the target post
			$permalink = get_permalink( $id );

			// construct link
			$link = '<p><a href="' . $permalink . '">' . __( 'See this video in context', 'sof-videos' ) . '</a></p>';

			// wrap link in a div for styling options
			$link = '<div class="sofvm_context_link">' . $link . '</div>' . "\n\n";

		}

		// --<
		return $link;

	}



	/**
	 * Get the link to the original video post.
	 *
	 * @param object $post The post on which the video is embedded
	 * @return str $link The link to the video post
	 */
	private function _get_link_to_video_post( $post ) {

		// get permalink to the target post
		$permalink = get_permalink( $post->ID );

		// construct link
		$link = '<p><a href="' . $permalink . '#respond">' . __( 'Comment on this video', 'sof-videos' ) . '</a></p>';

		// wrap link in a div for styling options
		$link = '<div class="sofvm_video_post_link">' . $link . '</div>' . "\n\n";

		// --<
		return $link;

	}



} // class Spirit_Of_Football_Videos_Shortcodes ends



