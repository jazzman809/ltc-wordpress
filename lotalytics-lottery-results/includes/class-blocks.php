<?php
/**
 * The two blocks: "Lottery results" and "Next lottery draw".
 *
 * Dynamic and server-rendered, so the numbers are in the page HTML (good for
 * search engines and for visitors without JavaScript). The editor script is
 * plain JavaScript on WordPress's own globals: no build step, nothing bundled.
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block registration and rendering.
 */
class Blocks {

	const EDITOR = 'lotalytics-results-editor';

	/**
	 * Register the editor script and both blocks.
	 */
	public static function register() {
		wp_register_script(
			self::EDITOR,
			LOTALYTICS_RESULTS_URL . 'assets/js/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
			LOTALYTICS_RESULTS_VERSION,
			true
		);
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor_data' ) );

		register_block_type(
			LOTALYTICS_RESULTS_DIR . 'blocks/latest-results',
			array( 'render_callback' => array( __CLASS__, 'render_results' ) )
		);
		register_block_type(
			LOTALYTICS_RESULTS_DIR . 'blocks/next-draw',
			array( 'render_callback' => array( __CLASS__, 'render_next' ) )
		);
		register_block_type(
			LOTALYTICS_RESULTS_DIR . 'blocks/results-archive',
			array( 'render_callback' => array( __CLASS__, 'render_archive' ) )
		);
	}

	/**
	 * The game list and the editor's strings, translated here in PHP.
	 */
	public static function editor_data() {
		$data = array(
			'games'       => Api_Client::games(),
			'defaultGame' => Settings::default_game(),
			'strings'     => array(
				'settings'  => __( 'Settings', 'lotalytics-lottery-results' ),
				'game'      => __( 'Game', 'lotalytics-lottery-results' ),
				'draws'     => __( 'Number of draws', 'lotalytics-lottery-results' ),
				'compact'   => __( 'Compact (hide the next draw date)', 'lotalytics-lottery-results' ),
				'showTitle' => __( 'Show the title', 'lotalytics-lottery-results' ),
				'noGames'   => __( 'The game list could not be loaded. Type a game slug, for example "powerball".', 'lotalytics-lottery-results' ),
				'slug'      => __( 'Game slug', 'lotalytics-lottery-results' ),
				'perPage'   => __( 'Draws per page', 'lotalytics-lottery-results' ),
			),
			'hasArchive'  => Api_Client::has_key() && Api_Client::features()['archive'],
		);
		wp_add_inline_script( self::EDITOR, 'window.lotalyticsEditor = ' . wp_json_encode( $data ) . ';', 'before' );
	}

	/**
	 * Render "Lottery results".
	 *
	 * @param array $attrs Block attributes.
	 * @return string HTML.
	 */
	public static function render_results( $attrs ) {
		$args = array(
			'game'       => ! empty( $attrs['game'] ) ? $attrs['game'] : Settings::default_game(),
			'draws'      => isset( $attrs['draws'] ) ? $attrs['draws'] : 5,
			'compact'    => ! empty( $attrs['compact'] ),
			'show_title' => ! isset( $attrs['showTitle'] ) || $attrs['showTitle'],
		);
		$class = 'ltc-results' . ( $args['compact'] ? ' ltc-results--compact' : '' );
		return Renderer::latest_results( $args, get_block_wrapper_attributes( array( 'class' => $class ) ) );
	}

	/**
	 * Render "Lottery results archive".
	 *
	 * @param array $attrs Block attributes.
	 * @return string HTML.
	 */
	public static function render_archive( $attrs ) {
		$args = array(
			'game'     => ! empty( $attrs['game'] ) ? $attrs['game'] : Settings::default_game(),
			'per_page' => isset( $attrs['perPage'] ) ? $attrs['perPage'] : Settings::per_page(),
		);
		return Renderer::archive_block( $args, get_block_wrapper_attributes( array( 'class' => 'ltc-results ltc-archive' ) ) );
	}

	/**
	 * Render "Next lottery draw".
	 *
	 * @param array $attrs Block attributes.
	 * @return string HTML.
	 */
	public static function render_next( $attrs ) {
		$args = array( 'game' => ! empty( $attrs['game'] ) ? $attrs['game'] : Settings::default_game() );
		return Renderer::next_draw( $args, get_block_wrapper_attributes( array( 'class' => 'ltc-next-draw' ) ) );
	}
}
