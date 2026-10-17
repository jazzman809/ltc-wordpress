<?php
/**
 * HTML output shared by the blocks, the shortcodes and the widget.
 *
 * Markup lives in templates/, and a theme can override any of them by copying
 * the file into `yourtheme/lotalytics/` (the convention WooCommerce uses).
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders results and next-draw markup.
 */
class Renderer {

	const STYLE = 'lotalytics-results';

	/**
	 * Register the stylesheet; it's enqueued only where something renders.
	 */
	public static function register_assets() {
		wp_register_style( self::STYLE, LOTALYTICS_RESULTS_URL . 'assets/css/lotalytics.css', array(), LOTALYTICS_RESULTS_VERSION );
	}

	/**
	 * Normalise attributes from a block, shortcode or widget.
	 *
	 * @param array $args Raw arguments.
	 * @return array
	 */
	public static function args( $args ) {
		$args = wp_parse_args(
			(array) $args,
			array(
				'game'       => Settings::default_game(),
				'draws'      => 5,
				'compact'    => false,
				'show_title' => true,
				'class'      => '',
			)
		);
		$args['game']       = sanitize_key( $args['game'] );
		$args['draws']      = max( 1, min( Api_Client::MAX_DRAW, (int) $args['draws'] ) );
		$args['compact']    = filter_var( $args['compact'], FILTER_VALIDATE_BOOLEAN );
		$args['show_title'] = filter_var( $args['show_title'], FILTER_VALIDATE_BOOLEAN );
		$args['class']      = trim( preg_replace( '/[^A-Za-z0-9_\- ]/', '', (string) $args['class'] ) );
		return $args;
	}

	/**
	 * The latest draws of a game.
	 *
	 * @param array  $args    See args().
	 * @param string $wrapper Extra attributes for the outer element (blocks pass theirs).
	 * @return string HTML.
	 */
	public static function latest_results( $args, $wrapper = '' ) {
		$args = self::args( $args );
		$data = Api_Client::results( $args['game'], $args['draws'] );
		/**
		 * Filters the results payload before it is rendered.
		 *
		 * @param array|null $data The API payload (game, drawings, results_url, next_draw).
		 * @param array      $args The render arguments.
		 */
		$data = apply_filters( 'lotalytics_results_data', $data, $args );
		if ( empty( $data['drawings'] ) || empty( $data['game'] ) ) {
			return self::unavailable();
		}
		$data['drawings'] = array_slice( (array) $data['drawings'], 0, $args['draws'] );
		return self::template( 'latest-results.php', compact( 'data', 'args', 'wrapper' ) );
	}

	/**
	 * The next scheduled draw of a game.
	 *
	 * @param array  $args    See args().
	 * @param string $wrapper Extra attributes for the outer element.
	 * @return string HTML.
	 */
	public static function next_draw( $args, $wrapper = '' ) {
		$args = self::args( $args );
		$data = apply_filters( 'lotalytics_results_data', Api_Client::results( $args['game'], 1 ), $args );
		if ( empty( $data['game'] ) || empty( $data['next_draw'] ) ) {
			return self::unavailable();
		}
		return self::template( 'next-draw.php', compact( 'data', 'args', 'wrapper' ) );
	}

	/**
	 * One drawn number.
	 *
	 * @param int  $number The number.
	 * @param bool $bonus  Whether it's a bonus ball.
	 * @return string HTML.
	 */
	public static function ball( $number, $bonus = false ) {
		$html = sprintf(
			'<li class="ltc-ball%s">%s</li>',
			$bonus ? ' ltc-ball--bonus' : '',
			esc_html( (string) (int) $number )
		);
		/**
		 * Filters the markup of one ball.
		 *
		 * @param string $html   The <li>.
		 * @param int    $number The number.
		 * @param bool   $bonus  Whether it's a bonus ball.
		 */
		return apply_filters( 'lotalytics_ball_html', $html, (int) $number, (bool) $bonus );
	}

	/**
	 * A draw date (YYYY-MM-DD) in the site's date format and language.
	 *
	 * @param string $ymd Date.
	 * @return string
	 */
	public static function date( $ymd ) {
		$ts = strtotime( $ymd . ' 12:00:00 UTC' );
		return $ts ? wp_date( get_option( 'date_format' ), $ts, new \DateTimeZone( 'UTC' ) ) : (string) $ymd;
	}

	/**
	 * The "Results by Lotalytics" credit, only when the site owner opted in
	 * (Settings → Lotalytics). Never shown otherwise.
	 *
	 * @param array $data Payload.
	 * @return string HTML.
	 */
	public static function credit( $data ) {
		if ( ! Settings::credit_enabled() ) {
			return '';
		}
		$url  = ! empty( $data['results_url'] ) ? $data['results_url'] : 'https://lotalytics.com/';
		$html = sprintf(
			'<p class="ltc-credit"><a href="%1$s">%2$s</a></p>',
			esc_url( $url ),
			/* translators: %s: lottery game name, e.g. "Powerball". */
			esc_html( sprintf( __( '%s results by Lotalytics', 'lotalytics-lottery-results' ), $data['game']['name'] ) )
		);
		/**
		 * Filters the credit markup (shown only when the site owner enabled it).
		 *
		 * @param string $html The paragraph.
		 * @param array  $data The payload.
		 */
		return apply_filters( 'lotalytics_credit_html', $html, $data );
	}

	/**
	 * What renders when there's nothing to show: nothing for visitors, a hint for editors.
	 *
	 * @return string HTML.
	 */
	private static function unavailable() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}
		return '<p class="ltc-unavailable">' . esc_html__( 'Lottery results are unavailable right now. Check the game in the block settings, or try again in a few minutes.', 'lotalytics-lottery-results' ) . '</p>';
	}

	/**
	 * Render a template, preferring the theme's copy.
	 *
	 * @param string $file Template file name.
	 * @param array  $vars Variables for the template.
	 * @return string HTML.
	 */
	private static function template( $file, $vars ) {
		if ( ! Settings::css_disabled() ) {
			wp_enqueue_style( self::STYLE );
		}
		$path = locate_template( array( 'lotalytics/' . $file ) );
		if ( ! $path ) {
			$path = LOTALYTICS_RESULTS_DIR . 'templates/' . $file;
		}
		$data    = $vars['data'];
		$args    = $vars['args'];
		$wrapper = $vars['wrapper'];
		ob_start();
		include $path;
		return (string) ob_get_clean();
	}
}
