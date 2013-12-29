<?php 
/**
 * Plugin Name: Post Prompts
 * Author: Kelly Dwan
 * Author URI: http://redradar.net
 * Version: 1.0
 * Description: Set up posts with prompts for daily writing.
 */

class KD_PostPrompts {

	/**
	 * Singleton
	 */
	private static $instance;

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
		add_action( 'welcome_panel', array( $this, 'welcome_panel' ) );
		add_filter( 'default_content', array( $this, 'default_content' ), 10, 2 );
	}

	/**
	 * Add buttons to the welcome panel.
	 */
	function welcome_panel() { 
		$url = admin_url( 'post-new.php' );
		$examen = add_query_arg( array( 'prompt' => 'examen' ), $url );
		$daily = add_query_arg( array( 'prompt' => 'daily-prompt' ), $url );
		?>
		<div style="margin: 0 -10px; padding: 10px 20px 20px; border-top: 1px solid #eee;">
		<h3 style="margin-bottom: 10px;">Prompts</h3>
		<a class="button" href="<?php echo $examen; ?>">Daily Reflection</a> &nbsp;
		<a class="button" href="<?php echo $daily; ?>">WP.com Daily Prompt</a>
		</div>
	<?php }

	/**
	 * Replace the default post content with the selected prompt
	 */
	function default_content( $post_content, $post ) {
		if ( 'examen' == $_GET['prompt'] ){

			$post_content = "<h1>What was today’s low point? What was the worst part of your day? What was your biggest struggle today, or when did you feel sad, helpless or angry?</h1><h1>What was today’s high point? What was the best part of your day? What did you feel good about today?</h1>";

		} elseif ( 'daily-prompt' == $_GET['prompt'] ) {

			$url = 'https://public-api.wordpress.com/rest/v1/sites/dailypost.wordpress.com/posts/';
			$url = add_query_arg( array(
				'category' => 'daily-prompts',
				'number' => 1,
			), $url );

			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {

				$body = json_decode( wp_remote_retrieve_body( $response ) );

				if ( is_object( $body ) && ! empty( $body->posts ) ) {

					$post_content = sprintf( '<h1><a href="%s">%s</a></h1>%s',
						$body->posts[0]->URL,
						$body->posts[0]->title,
						$body->posts[0]->content
					);

				}

			}

		}
		return $post_content;
	}
}
KD_PostPrompts::instance();