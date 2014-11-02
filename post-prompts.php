<?php
/**
 * Plugin Name: Post Prompts
 * Author: Kelly Dwan
 * Author URI: http://redradar.net
 * Version: 0.1.0
 * Text Domain: kd_prompts
 * Domain Path: /languages/
 * Description: Set up posts with prompts for daily writing.
 */

class KD_PostPrompts {

	/**
	 * Singleton
	 */
	private static $instance;

	/**
	 * Store our prompt
	 */
	private static $prompt;

	/**
	 * Silence is golden!
	 */
	private function __construct() {}

	/**
	 * Returns the main instance.
	 *
	 * @return  self
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self;
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Initiate the main actions and filters for theme option related functionality.
	 *
	 * @return  void
	 */
	public function setup() {
		add_filter( 'default_content', array( $this, 'default_content' ), 10 );
		add_filter( 'default_title', array( $this, 'default_title' ), 10 );
		add_action( 'wp_dashboard_setup', array( $this, 'add_to_dashboard' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 99 );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return  void
	 */
	function load_textdomain() {
		load_plugin_textdomain( 'kd_prompts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add plugin-specific styles.
	 *
	 * @return  void
	 */
	public function styles(){
		wp_enqueue_style( 'dailyprompt', plugins_url( 'style.css', __FILE__ ) );
	}

	/**
	 * Add the dashboard widget.
	 *
	 * @return  void
	 */
	public function add_to_dashboard(){
		add_meta_box( 'kd-daily-prompt', __( 'Prompt', 'kd_prompts' ), array( $this, 'dashboard_widget' ), 'dashboard', 'side', 'high' );
	}

	/**
	 * Display the prompt and button on the Dashboard widget.
	 *
	 * @return  void
	 */
	public function dashboard_widget() {
		$url = admin_url( 'post-new.php' );
		$post = self::get_daily_prompt();

		echo '<p>'. sprintf( esc_html__( "Need a blog topic? Here's the latest daily prompt from %s.", 'kd_prompts' ), '<a href="http://href.li/?http://dailypost.wordpress.com/">WordPress.com Daily Post</a>' ) .'</p>';

		if ( is_array( $post ) ){
			printf( '<blockquote>%1$s<cite><a href="%2$s">%3$s</a></cite></blockquote>',
				wp_kses_post( $post['content'] ),
				esc_url( 'http://href.li/?' . $post['URL'] ),
				esc_html( $post['title'] )
			);
		} else {
			// Could be better.
			esc_html_e( 'There was an error getting the prompt', 'kd_prompts' );
		}

		printf( '<p><a class="button" href="%s">%s</a></p>',
			esc_url( add_query_arg( array( 'prompt' => 'daily-prompt' ), $url ) ),
			esc_html__( 'Start a post with this prompt', 'kd_prompts' )
		);
	}

	/**
	 * Replace the default post content with the selected prompt, if the URL parameter is set.
	 *
	 * @param   string   $post_content  The default post content
	 * @return  string  The default post content, with today's prompt if available.
	 */
	public function default_content( $post_content ) {
		if ( ! isset( $_GET['prompt'] ) )
			return $post_content;

		if ( 'daily-prompt' == $_GET['prompt'] ) {

			$prompt = self::get_daily_prompt();

			if ( is_array( $prompt ) ){
				$post_content = sprintf( '<blockquote>%1$s<cite><a href="%2$s">%3$s</a></cite></blockquote> &nbsp;',
					wp_kses_post( $prompt['content'] ),
					esc_url( $prompt['URL'] ),
					esc_html( $prompt['title'] )
				);
			}
		}

		return $post_content;
	}

	/**
	 * Replace the default post title with the selected prompt, if the URL parameter is set.
	 *
	 * @param   string   $post_title  The default post title
	 * @return  string  The default post title, with today's prompt name if available.
	 */
	public function default_title( $post_title ) {
		if ( isset( $_GET['prompt'] ) && ( 'daily-prompt' == $_GET['prompt'] ) ) {
			$prompt = self::get_daily_prompt();
			$post_title = sprintf( __( 'Prompt: %s', 'kd_prompts' ), $prompt['title'] );
		}

		return $post_title;
	}

	/**
	 * Get the Daily Prompt from WordPress.com (dailypost.wordpress.com)
	 *
	 * @return  Object|bool  Post-like object (http://developer.wordpress.com/docs/api/1/get/sites/%24site/posts/),
	 *                       or false if no post returned.
	 */
	public function get_daily_prompt() {
		// Check if we've already pulled down the prompt, and return it if so. Bypasses 2nd feed read.
		if ( isset( self::$prompt ) && is_array( self::$prompt ) ) {
			return self::$prompt;
		}

		$url = 'http://dailypost.wordpress.com/dp_prompt/feed/';
		$rss = fetch_feed( $url );

		if ( ! is_wp_error( $rss ) ) {

			$item = $rss->get_item();

			$prompt = array();
			$prompt['URL']     = $item->get_permalink();
			$prompt['title']   = $item->get_title();
			$prompt['content'] = wpautop( strip_tags( $item->get_content() ) );

			self::$prompt = $prompt;
			return $prompt;
		}

		return false;
	}

}

KD_PostPrompts::instance();
