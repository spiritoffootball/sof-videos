<?php
/**
 * Custom Shortcodes Class.
 *
 * Handles Shortcodes for Videos.
 *
 * @since 0.1
 *
 * @package Spirit_Of_Football_Videos
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Custom Shortcodes Class.
 *
 * A class that encapsulates all Shortcodes for Videos.
 *
 * @since 0.1
 */
class Spirit_Of_Football_Videos_Shortcodes {

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

		// Register Shortcodes.
		add_shortcode( 'sofvideo', [ $this, 'video_shortcode' ] );

		// Modify the content.
		add_filter( 'the_content', [ $this, 'the_content' ], 20, 1 );

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Add a video to a page/post via a Shortcode.
	 *
	 * @since 0.1
	 *
	 * @param array $attr The saved shortcode attributes.
	 * @param str   $content The enclosed content of the shortcode.
	 * @return str $content The HTML-formatted video custom post type.
	 */
	public function video_shortcode( $attr, $content = '' ) {

		// Set the shortcode defaults.
		$defaults = [
			'id'    => '',
			'align' => 'none',
		];

		// Get our shortcode attributes.
		$atts = shortcode_atts( $defaults, $attr );

		// Bail if there's anything amiss.
		if ( empty( $atts['id'] ) ) {
			return;
		}

		// Return something else for feeds.
		if ( is_feed() ) {
			return '<p>' . esc_html__( 'Visit the site to see the video.', 'sof-videos' ) . '</p>';
		}

		// Get the video post.
		$video_post = get_post( (int) $atts['id'] );

		// Check we got one.
		if ( is_object( $video_post ) ) {

			// Set it up.
			setup_postdata( $video_post );

			// Parse content.
			$content = apply_filters( 'the_content', get_the_content() );

			/*
			 * We need to manually apply our content filter because $post is the
			 * object for the post into which the video has been embedded.
			 */

			// Get embed code.
			$embed = $this->get_embed( $video_post );

			// Get link to video post.
			$link = $this->get_link_to_video_post( $video_post );

			// Prepend.
			$content = $embed . $content . $link;

			// Give alignment class to div.
			switch ( $atts['align'] ) {
				case 'right':
					$class = 'alignright';
					break;
				case 'left':
					$class = 'alignleft';
					break;
				case 'none':
					$class = 'alignnone';
					break;
				default:
					$class = 'alignnone';
			}

			// Give it an alignment.
			$content = '<div class="sofvm_embed ' . esc_attr( $class ) . '">' . $content . '</div>';

			// Reset just in case.
			wp_reset_postdata();

		}

		// --<
		return $content;

	}


	/**
	 * Prepend video to content.
	 *
	 * @since 0.1
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	public function the_content( $content ) {

		// Reference our post.
		global $post;

		// Only filter our custom post type.
		if ( 'sofvm_video' === $post->post_type ) {

			// Get embed code.
			$embed_code = $this->get_embed( $post );

			// Get link to blog post.
			$link = $this->get_context_link( $post );

			// Prepend.
			$content = $embed_code . $content . $link;

		}

		// --<
		return $content;

	}

	// -----------------------------------------------------------------------------------

	/**
	 * Get the video embed for a post.
	 *
	 * @since 0.1
	 *
	 * @param object $post The post on which the video will be embedded.
	 * @return str $embed_code The HTML-formatted video.
	 */
	private function get_embed( $post ) {

		// Intro text.
		$intro = __( 'This video is missing because we are in the process of uploading all our videos to YouTube. Please be patient: the video will appear at some point! In the meantime, visit ', 'sof-videos' );

		// Link text.
		$follow = __( 'The Ball on YouTube', 'sof-videos' );

		// Init embed code.
		$embed_code = '<div class="sofvm_video">' .
			'<p><strong>' . esc_html( $intro ) . '<a href="https://www.youtube.com/@spirit-of-football">' . esc_html( $follow ) . '</a>.</strong></p>' .
		'</div>' . "\n\n";

		// Get prefixed meta key.
		$db_key = '_' . $this->video_meta_key;

		// If the video custom field has a value.
		$video_url = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $video_url ) ) {

			/**
			 * Filter the default video width.
			 *
			 * @since 0.1
			 *
			 * @param int $width The default video width.
			 */
			$width = apply_filters( 'sofvm_video_width', 640 );

			/**
			 * Filter the default video height.
			 *
			 * @since 0.1
			 *
			 * @param int $height The default video height.
			 */
			$height = apply_filters( 'sofvm_video_height', 360 );

			// Build args.
			$args = [
				'width'  => $width,
				'height' => $height,
			];

			// Get embed.
			$embed_code = wp_oembed_get( $video_url, $args );

			// Wrap embed in a div for styling options.
			$embed_code = '<div class="sofvm_video">' . $embed_code . '</div>' . "\n\n";

		}

		// --<
		return $embed_code;

	}

	/**
	 * Get the link to where the video is embedded.
	 *
	 * @since 0.1
	 *
	 * @param object $post The video post object.
	 * @return str $link The link to the blog post.
	 */
	private function get_context_link( $post ) {

		// Init link.
		$link = '';

		// Get DB key.
		$db_key = '_' . $this->blog_meta_key;

		// If the post custom field has a value.
		$existing = get_post_meta( $post->ID, $db_key, true );
		if ( ! empty( $existing ) ) {

			// Get it.
			$id = get_post_meta( $post->ID, $db_key, true );

			// Get permalink to the target post.
			$permalink = get_permalink( $id );

			// Construct link.
			$link = '<p><a href="' . esc_url( $permalink ) . '">' . esc_html__( 'See this video in context', 'sof-videos' ) . '</a></p>';

			// Wrap link in a div for styling options.
			$link = '<div class="sofvm_context_link">' . $link . '</div>' . "\n\n";

		}

		// --<
		return $link;

	}

	/**
	 * Get the link to the original video post.
	 *
	 * @since 0.1
	 *
	 * @param object $post The post on which the video is embedded.
	 * @return str $link The link to the video post.
	 */
	private function get_link_to_video_post( $post ) {

		// Get permalink to the target post.
		$permalink = get_permalink( $post->ID );

		// Construct link.
		$link = '<p><a href="' . esc_url( $permalink ) . '#respond">' . esc_html__( 'Comment on this video', 'sof-videos' ) . '</a></p>';

		// Wrap link in a div for styling options.
		$link = '<div class="sofvm_video_post_link">' . $link . '</div>' . "\n\n";

		// --<
		return $link;

	}

}
