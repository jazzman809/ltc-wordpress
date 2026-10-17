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
		if ( Api_Client::has_key() ) {
			$tests['direct']['lotalytics_key'] = array(
				'label' => __( 'Lotalytics API key', 'lotalytics-lottery-results' ),
				'test'  => array( __CLASS__, 'test_key' ),
			);
		}
		return $tests;
	}

	/**
	 * The key: accepted, and not near its quota.
	 *
	 * @return array
	 */
	public static function test_key() {
		$a = Api_Client::account( true );
		if ( ! is_array( $a ) ) {
			$status = 'critical';
			$label  = __( 'The Lotalytics API key was not accepted', 'lotalytics-lottery-results' );
			$desc   = __( 'Check the key under Settings → Lotalytics. Until it works, the archive and the draw pages are off; the free results blocks keep working.', 'lotalytics-lottery-results' );
		} elseif ( Api_Client::quota_low() ) {
			$status = 'recommended';
			$label  = __( 'The Lotalytics API key is close to its request limit', 'lotalytics-lottery-results' );
			$desc   = __( 'Results are cached for longer until the limit resets. A bigger plan raises the limit.', 'lotalytics-lottery-results' );
		} else {
			$status = 'good';
			$label  = __( 'The Lotalytics API key works', 'lotalytics-lottery-results' );
			/* translators: %s: plan name. */
			$desc = sprintf( __( 'Plan: %s.', 'lotalytics-lottery-results' ), ! empty( $a['plan']['name'] ) ? $a['plan']['name'] : '—' );
		}
		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Lotalytics', 'lotalytics-lottery-results' ),
				'color' => 'good' === $status ? 'blue' : 'orange',
			),
			'description' => '<p>' . esc_html( $desc ) . '</p>',
			'test'        => 'lotalytics_key',
		);
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
