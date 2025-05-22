<?php
/**
 * AJAX handler to mark LearnDash topic as complete when all audio players have finished.
 *
 * @package WDM-learndash-audio-completion
 */

if ( ! class_exists( 'WDM_LDAC_Ajax' ) ) {
	class WDM_LDAC_Ajax {
		public function __construct() {
			add_action( 'wp_ajax_wdm_ld_mark_topic_complete', array( $this, 'mark_topic_complete' ) );
		}

		public function mark_topic_complete() {
			check_ajax_referer( 'wdm_ld_audio_completion_nonce', 'nonce' );

			$topic_id = isset( $_POST['topic_id'] ) ? intval( $_POST['topic_id'] ) : 0;
			$user_id = get_current_user_id();

			if ( 0 !== $topic_id && 0 !== $user_id ) {
				$topic = get_post( $topic_id );
				if ( $topic && 'sfwd-topic' === $topic->post_type ) {
					learndash_process_mark_complete( $user_id, $topic_id );
					wp_send_json_success( 'Topic marked as complete.' );
				} else {
					wp_send_json_error( 'Invalid topic ID.' );
				}
			} else {
				wp_send_json_error( 'Missing topic ID or user not logged in.' );
			}
		}
	}

	new WDM_LDAC_Ajax();
} 