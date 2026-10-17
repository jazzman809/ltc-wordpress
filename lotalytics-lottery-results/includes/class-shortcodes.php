<?php
/**
 * Shortcodes, for classic themes and page builders:
 *
 *   [lotalytics_results game="powerball" draws="5" compact="0" show_title="1"]
 *   [lotalytics_next_draw game="powerball"]
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode registration.
 */
class Shortcodes {

	/**
	 * Register both shortcodes.
	 */
	public static function register() {
		add_shortcode( 'lotalytics_results', array( __CLASS__, 'results' ) );
		add_shortcode( 'lotalytics_next_draw', array( __CLASS__, 'next_draw' ) );
	}

	/**
	 * [lotalytics_results].
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public static function results( $atts ) {
		$atts = shortcode_atts(
			array(
				'game'       => Settings::default_game(),
				'draws'      => 5,
				'compact'    => '0',
				'show_title' => '1',
				'class'      => '',
			),
			$atts,
			'lotalytics_results'
		);
		return Renderer::latest_results( $atts );
	}

	/**
	 * [lotalytics_next_draw].
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public static function next_draw( $atts ) {
		$atts = shortcode_atts(
			array(
				'game'  => Settings::default_game(),
				'class' => '',
			),
			$atts,
			'lotalytics_next_draw'
		);
		return Renderer::next_draw( $atts );
	}
}
