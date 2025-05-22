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

// Enqueue JavaScript for audio completion tracking.
add_action( 'wp_enqueue_scripts', 'wdm_ld_audio_completion_scripts' );
/**
 * Enqueue and inject JavaScript for audio completion tracking on LearnDash topic pages.
 */
function wdm_ld_audio_completion_scripts() {
	if ( is_singular( 'sfwd-topic' ) ) {
		// Enqueue jQuery.
		wp_enqueue_script( 'jquery' );

		// Get next topic URL using LearnDash function.
		$next_topic_url = '';
		if ( function_exists( 'learndash_next_post_link' ) ) {
			$next_topic_url = learndash_next_post_link( '', true );
		}

		// Inline JavaScript.
		$script = '
			jQuery(document).ready(function($) {
				var audioPlayers = $(".ld-audio-player");
				var completed = new Set();
				var total = audioPlayers.length;
				var nextTopicUrl = "' . esc_js( $next_topic_url ) . '";

				// Hide the mark complete button
				$(".learndash_mark_complete_button, #learndash_mark_complete_button").hide();

				//Hide the next topic button 
				$(".next-link").hide();

				audioPlayers.each(function(index) {
					var audioPlayer = this;
					$(audioPlayer).data("audio-index", index);
					audioPlayer.addEventListener("ended", function() {
						completed.add(index);
						if (completed.size === total) {
							$.ajax({
								url: "' . admin_url( 'admin-ajax.php' ) . '",
								type: "POST",
								data: {
									action: "wdm_ld_mark_topic_complete",
									topic_id: ' . get_the_ID() . ',
									nonce: "' . wp_create_nonce( 'wdm_ld_audio_completion_nonce' ) . '"
								},
								success: function(response) {
									if (response.success) {
										if (nextTopicUrl) {
											window.location.href = nextTopicUrl;
										}
									}
								},
								error: function() {
									console.error("AJAX request failed.");
								}
							});
						}
					});
				});
			});
		';
		// Add inline script.
		wp_add_inline_script( 'jquery', $script );
	}
}


// Shortcode to embed audio player.
add_shortcode( 'wdm_ld_audio_completion', 'wdm_ld_audio_completion_shortcode' );
/**
 * Shortcode to embed audio player for LearnDash audio completion.
 *
 * @param array $atts Shortcode attributes.
 * @return string Audio player HTML or error message.
 */
function wdm_ld_audio_completion_shortcode( $atts ) {
	// Only process on LearnDash topic pages.
	if ( ! is_singular( 'sfwd-topic' ) ) {
		return '';
	}

	// Shortcode attributes.
	$atts = shortcode_atts(
		array(
			'url' => '',
		),
		$atts,
		'wdm_ld_audio_completion'
	);

	// Validate audio URL.
	if ( empty( $atts['url'] ) || ! filter_var( $atts['url'], FILTER_VALIDATE_URL ) ) {
		return '<p>Error: Valid audio URL is required.</p>';
	}

	// Generate unique ID for audio player to support multiple instances.
	$audio_id = 'ld-audio-player-' . uniqid();

	// Return audio player HTML.
	$output = '<audio id="' . esc_attr( $audio_id ) . '" class="ld-audio-player" controls>';
	$output .= '<source src="' . esc_url( $atts['url'] ) . '" type="audio/mpeg">';
	$output .= 'Your browser does not support the audio element.';
	$output .= '</audio>';

	return $output;
}

// AJAX handler to mark topic as complete.
add_action( 'wp_ajax_wdm_ld_mark_topic_complete', 'wdm_ld_mark_topic_complete_callback' );
/**
 * AJAX handler to mark LearnDash topic as complete when all audio players have finished.
 */
function wdm_ld_mark_topic_complete_callback() {
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
