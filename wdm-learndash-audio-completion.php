<?php
/**
 * Plugin Name: WDM LearnDash Audio Completion
 * Description: A shortcode to embed audio in LearnDash topics and mark them as complete when the audio finishes. [wdm_ld_audio_completion url="file-path.mp3"]
 * Version: 1.2
 * Author: Wisdmlabs
 * License: GPL2
 *
 * @package WDM-learndash-audio-completion
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WDM_LearnDash_Audio_Completion' ) ) {
	class WDM_LearnDash_Audio_Completion {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			$this->define_constants();
			$this->includes();
			$this->hooks();
		}

		private function define_constants() {
			define( 'WDM_LDAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			define( 'WDM_LDAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		private function includes() {
			require_once WDM_LDAC_PLUGIN_DIR . 'includes/class-wdm-ldac-shortcode.php';
			require_once WDM_LDAC_PLUGIN_DIR . 'includes/class-wdm-ldac-ajax.php';
		}

		private function hooks() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		public function enqueue_scripts() {
			if ( is_singular( 'sfwd-topic' ) ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script(
					'wdm-ld-audio-completion',
					WDM_LDAC_PLUGIN_URL . 'assets/js/audio-completion.js',
					array( 'jquery' ),
					'1.0.0',
					true
				);
				$next_topic_url = '';
				if ( function_exists( 'learndash_next_post_link' ) ) {
					$next_topic_url = learndash_next_post_link( '', true );
				}
				wp_localize_script( 'wdm-ld-audio-completion', 'wdmLdAudioCompletion', array(
					'nextTopicUrl' => esc_js( $next_topic_url ),
					'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
					'topicId'      => get_the_ID(),
					'nonce'        => wp_create_nonce( 'wdm_ld_audio_completion_nonce' ),
				));
			}
		}
	}
}

WDM_LearnDash_Audio_Completion::get_instance();
