<?php
/**
 * Shortcode to embed audio player for LearnDash audio completion.
 *
 * @package WDM-learndash-audio-completion
 */

if ( ! class_exists( 'WDM_LDAC_Shortcode' ) ) {
	class WDM_LDAC_Shortcode {
		public function __construct() {
			add_shortcode( 'wdm_ld_audio_completion', array( $this, 'render_audio_shortcode' ) );
		}

		public function render_audio_shortcode( $atts ) {
			if ( ! is_singular( 'sfwd-topic' ) ) {
				return '';
			}

			$atts = shortcode_atts(
				array(
					'url' => '',
				),
				$atts,
				'wdm_ld_audio_completion'
			);

			if ( empty( $atts['url'] ) || ! filter_var( $atts['url'], FILTER_VALIDATE_URL ) ) {
				return '<p>Error: Valid audio URL is required.</p>';
			}

			$audio_id = 'ld-audio-player-' . uniqid();

			$output = '<audio id="' . esc_attr( $audio_id ) . '" class="ld-audio-player" controls>';
			$output .= '<source src="' . esc_url( $atts['url'] ) . '" type="audio/mpeg">';
			$output .= 'Your browser does not support the audio element.';
			$output .= '</audio>';

			return $output;
		}
	}

	new WDM_LDAC_Shortcode();
} 