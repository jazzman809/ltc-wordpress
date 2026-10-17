<?php
/**
 * The only class that talks to Lotalytics.
 *
 * Every call is made server-side from the WordPress host, so visitors' browsers
 * never contact Lotalytics. Responses are cached in transients (the object cache
 * when the host has one), and the last good copy of each response is kept, so
 * an API outage serves slightly old results instead of breaking the page.
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP client with caching for the public Lotalytics endpoints.
 */
class Api_Client {

	const BASE     = 'https://api.lotalytics.com';
	const PREFIX   = 'lotalytics_';
	const LOCALES  = array( 'en', 'es', 'fr', 'pt', 'de', 'it' );
	const MAX_DRAW = 5;

	/**
	 * The API's base URL. Production unless overridden for testing, either with
	 * `define( 'LOTALYTICS_API_BASE', 'http://localhost:18080' );` in wp-config.php
	 * or the `lotalytics_api_base` filter.
	 *
	 * @return string No trailing slash.
	 */
	public static function base() {
		$base = defined( 'LOTALYTICS_API_BASE' ) ? (string) LOTALYTICS_API_BASE : self::BASE;
		/**
		 * Filters the Lotalytics API base URL (for staging and local testing).
		 *
		 * @param string $base The base URL, no trailing slash.
		 */
		$base = (string) apply_filters( 'lotalytics_api_base', $base );
		return untrailingslashit( esc_url_raw( $base ) );
	}

	/**
	 * The data language for the current request: the site or user locale, or English.
	 *
	 * @return string Two-letter code Lotalytics serves.
	 */
	public static function locale() {
		$code = substr( (string) determine_locale(), 0, 2 );
		return in_array( $code, self::LOCALES, true ) ? $code : 'en';
	}

	/**
	 * The latest draws of one game.
	 *
	 * @param string $slug  Game slug, e.g. "powerball".
	 * @param int    $draws How many draws (1-5).
	 * @return array|null   Decoded payload, or null when nothing is available.
	 */
	public static function results( $slug, $draws = 5 ) {
		$slug  = sanitize_key( $slug );
		$draws = max( 1, min( self::MAX_DRAW, (int) $draws ) );
		if ( '' === $slug ) {
			return null;
		}
		$locale = self::locale();
		$url    = add_query_arg(
			array(
				'n'      => $draws,
				'locale' => $locale,
				'page'   => home_url( '/' ),
			),
			self::base() . '/public/embed/' . rawurlencode( $slug )
		);
		return self::cached( self::base() . '|r_' . $slug . '_' . $draws . '_' . $locale, $url, array( __CLASS__, 'results_ttl' ) );
	}

	/**
	 * Every game Lotalytics publishes, for the pickers.
	 *
	 * @return array List of ['slug' => ..., 'name' => ..., 'country' => ...].
	 */
	public static function games() {
		$data = self::cached(
			self::base() . '|games',
			self::base() . '/public/games',
			static function () {
				return DAY_IN_SECONDS;
			}
		);
		$out = array();
		foreach ( (array) $data as $game ) {
			if ( ! empty( $game['slug'] ) && ! empty( $game['name'] ) ) {
				$out[] = array(
					'slug'    => sanitize_key( $game['slug'] ),
					'name'    => sanitize_text_field( $game['name'] ),
					'country' => isset( $game['country'] ) ? sanitize_text_field( $game['country'] ) : '',
				);
			}
		}
		usort(
			$out,
			static function ( $a, $b ) {
				return strcasecmp( $a['name'], $b['name'] );
			}
		);
		return $out;
	}

	/**
	 * Cache lifetime for a results payload: short around a draw, longer between draws.
	 *
	 * @param array $data Decoded payload.
	 * @return int Seconds.
	 */
	public static function results_ttl( $data ) {
		$ttl = 30 * MINUTE_IN_SECONDS;
		if ( ! empty( $data['next_draw'] ) ) {
			$next = strtotime( $data['next_draw'] . ' 00:00:00 UTC' );
			if ( $next ) {
				$until = $next - time();
				// Results usually land in the hours after the draw date starts (UTC):
				// refresh often from then on, and sleep until then otherwise.
				$ttl = $until > 0 ? min( 6 * HOUR_IN_SECONDS, max( 15 * MINUTE_IN_SECONDS, $until ) ) : 15 * MINUTE_IN_SECONDS;
			}
		}
		/**
		 * Filters how long a game's results are cached, in seconds.
		 *
		 * @param int   $ttl  Seconds.
		 * @param array $data The payload being cached.
		 */
		return (int) apply_filters( 'lotalytics_cache_ttl', $ttl, $data );
	}

	/**
	 * GET a JSON URL through the cache, keeping the last good copy.
	 *
	 * @param string   $key    Cache key suffix.
	 * @param string   $url    URL.
	 * @param callable $ttl_fn Seconds to cache a fresh payload.
	 * @return array|null
	 */
	private static function cached( $key, $url, $ttl_fn ) {
		$tkey  = self::PREFIX . md5( $key );
		$fresh = get_transient( $tkey );
		if ( is_array( $fresh ) ) {
			return $fresh;
		}
		// A recent failure with nothing to fall back on (an unknown game, an
		// outage on a new install): don't ask again on every page view.
		$mkey = self::PREFIX . 'miss_' . md5( $key );
		if ( get_transient( $mkey ) ) {
			return null;
		}
		$data = self::fetch( $url );
		if ( is_array( $data ) ) {
			set_transient( $tkey, $data, max( MINUTE_IN_SECONDS, (int) call_user_func( $ttl_fn, $data ) ) );
			update_option( self::PREFIX . 'stale_' . md5( $key ), $data, false );
			self::remember( $tkey );
			return $data;
		}
		// The API didn't answer: serve the last good copy, and try again in five minutes.
		$stale = get_option( self::PREFIX . 'stale_' . md5( $key ) );
		if ( is_array( $stale ) ) {
			set_transient( $tkey, $stale, 5 * MINUTE_IN_SECONDS );
			self::remember( $tkey );
			return $stale;
		}
		set_transient( $mkey, 1, 5 * MINUTE_IN_SECONDS );
		self::remember( $mkey );
		return null;
	}

	/**
	 * One HTTP GET, decoded. Never throws; null on any failure.
	 *
	 * @param string $url URL.
	 * @return array|null
	 */
	private static function fetch( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'LotalyticsWP/' . LOTALYTICS_RESULTS_VERSION . '; ' . home_url( '/' ),
				'headers'    => array( 'Accept' => 'application/json' ),
			)
		);
		if ( is_wp_error( $response ) ) {
			update_option( self::PREFIX . 'last_error', $response->get_error_message(), false );
			return null;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			/* translators: %d: HTTP status code. */
			update_option( self::PREFIX . 'last_error', sprintf( __( 'The Lotalytics API answered HTTP %d.', 'lotalytics-lottery-results' ), $code ), false );
			return null;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) ) {
			return null;
		}
		delete_option( self::PREFIX . 'last_error' );
		return $data;
	}

	/**
	 * Delete every cached response (the stale copies stay as a safety net).
	 */
	public static function clear_cache() {
		foreach ( (array) get_option( self::PREFIX . 'cache_keys', array() ) as $tkey ) {
			delete_transient( $tkey );
		}
		update_option( self::PREFIX . 'cache_keys', array(), false );
	}

	/**
	 * Every option and transient this plugin stores, for uninstall.php.
	 *
	 * @return array{transients: string[], options: string[]}
	 */
	public static function stored_keys() {
		$transients = (array) get_option( self::PREFIX . 'cache_keys', array() );
		$options    = array();
		foreach ( $transients as $tkey ) {
			if ( 0 !== strpos( $tkey, self::PREFIX . 'miss_' ) ) {
				$options[] = str_replace( self::PREFIX, self::PREFIX . 'stale_', $tkey );
			}
		}
		return array(
			'transients' => $transients,
			'options'    => $options,
		);
	}

	/**
	 * Remember a transient key so it can be cleared (works with object caches,
	 * where transients aren't rows that can be found by prefix).
	 *
	 * @param string $tkey Transient key.
	 */
	private static function remember( $tkey ) {
		$keys = (array) get_option( self::PREFIX . 'cache_keys', array() );
		if ( ! in_array( $tkey, $keys, true ) ) {
			$keys[] = $tkey;
			update_option( self::PREFIX . 'cache_keys', array_slice( $keys, -500 ), false );
		}
	}
}
