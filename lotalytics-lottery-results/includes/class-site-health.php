<?php
/**
 * Tools → Site Health: can this site reach the Lotalytics API?
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Health test.
 */
class Site_Health {

	/**
	 * Add the test.
	 *
	 * @param array $tests Tests.
	 * @return array
	 */
	public static function register( $tests ) {
		$tests['direct']['lotalytics_api'] = array(
			'label' => __( 'Lotalytics API', 'lotalytics-lottery-results' ),
			'test'  => array( __CLASS__, 'test' ),
		);
		return $tests;
	}

	/**
	 * Run it.
	 *
	 * @return array
	 */
	public static function test() {
		$games = Api_Client::games();
		$error = get_option( Api_Client::PREFIX . 'last_error' );
		$ok    = ! empty( $games ) && ! $error;
		return array(
			'label'       => $ok
				? __( 'Lottery results can be loaded from Lotalytics', 'lotalytics-lottery-results' )
				: __( 'Lottery results could not be loaded from Lotalytics', 'lotalytics-lottery-results' ),
			'status'      => $ok ? 'good' : 'recommended',
			'badge'       => array(
				'label' => __( 'Lotalytics', 'lotalytics-lottery-results' ),
				'color' => $ok ? 'blue' : 'orange',
			),
			'description' => '<p>' . esc_html(
				$ok
					? __( 'The plugin reached api.lotalytics.com. Results are cached, and the last good copy is shown if the service is briefly unavailable.', 'lotalytics-lottery-results' )
					: ( $error ? $error : __( 'The game list came back empty.', 'lotalytics-lottery-results' ) )
			) . '</p>',
			'test'        => 'lotalytics_api',
		);
	}
}
