<?php
/**
 * The only class that talks to Lotalytics.
 *
 * Every call is made server-side from the WordPress host, so visitors' browsers
 * never contact Lotalytics and the API key never reaches a browser. Responses
 * are cached in transients (the object cache when the host has one), the last
 * good copy of each response is kept so an outage serves slightly old results,
 * and failures are remembered for a few minutes so a bad game name doesn't call
 * the API on every page view.
 *
 * Two families of endpoints:
 *  - public (`/public/*`, no key): the latest five draws, the game list. Used
 *    for the latest-results and next-draw blocks with or without a key, so they
 *    never spend the key's quota.
 *  - the Data API (`/v1/*`, the key): the archive, a page per draw, the
 *    account (plan, features, usage).
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP client with caching.
 */
class Api_Client {

	const BASE     = 'https://api.lotalytics.com';
	const PREFIX   = 'lotalytics_';
	const LOCALES  = array( 'en', 'es', 'fr', 'pt', 'de', 'it' );
	const MAX_DRAW = 5;
	const INDEX    = 'lotalytics_cache_index';

	/**
	 * HTTP status of the last request this process made (0 = none / network error).
	 *
	 * @var int
	 */
	public static $last_status = 0;

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
	 * The API key: the `LOTALYTICS_API_KEY` constant (wp-config.php) or the saved setting.
	 *
	 * @return string Empty when there is none.
	 */
	public static function key() {
		if ( defined( 'LOTALYTICS_API_KEY' ) ) {
			return trim( (string) LOTALYTICS_API_KEY );
		}
		return trim( (string) get_option( self::PREFIX . 'api_key', '' ) );
	}

	/**
	 * Is a key configured?
	 *
	 * @return bool
	 */
	public static function has_key() {
		return '' !== self::key();
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

	// ── Public endpoints (no key) ───────────────────────────────────────────

	/**
	 * The latest draws of one game (public, never spends the key's quota).
	 *
	 * @param string $slug  Game slug, e.g. "powerball".
	 * @param int    $draws How many draws (1-5).
	 * @return array|null Decoded payload, or null when nothing is available.
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
		return self::cached( 'r_' . $slug . '_' . $draws . '_' . $locale, $url, array( __CLASS__, 'results_ttl' ), false );
	}

	/**
	 * Every game Lotalytics publishes, for the pickers.
	 *
	 * @return array List of ['slug' => ..., 'name' => ..., 'country' => ...].
	 */
	public static function games() {
		$data = self::cached(
			'games',
			self::base() . '/public/games',
			static function () {
				return DAY_IN_SECONDS;
			},
			false
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

	// ── The Data API (the key) ──────────────────────────────────────────────

	/**
	 * The key's plan, features, games and usage (`/v1/account`, never counted
	 * against the quota). Cached ten minutes.
	 *
	 * @param bool $fresh Skip the cache (the settings page after saving a key).
	 * @return array|null Null without a key or when the key doesn't work.
	 */
	public static function account( $fresh = false ) {
		if ( ! self::has_key() ) {
			return null;
		}
		$name = 'account';
		if ( $fresh ) {
			delete_transient( self::tkey( $name, true ) );
			delete_transient( self::mkey( $name, true ) );
		}
		return self::cached(
			$name,
			self::base() . '/v1/account',
			static function () {
				return 10 * MINUTE_IN_SECONDS;
			},
			true
		);
	}

	/**
	 * What the key unlocks, from the account. All false without a working key.
	 *
	 * @return array{archive: bool, draw_pages: bool, stats: bool}
	 */
	public static function features() {
		$a = self::account();
		$f = ( is_array( $a ) && ! empty( $a['features'] ) ) ? (array) $a['features'] : array();
		return array(
			'archive'    => ! empty( $f['archive'] ),
			'draw_pages' => ! empty( $f['draw_pages'] ),
			'stats'      => ! empty( $f['stats'] ),
		);
	}

	/**
	 * Games the key may read (slugs), or null when it may read every game.
	 *
	 * @return string[]|null
	 */
	public static function allowed_games() {
		$a = self::account();
		if ( ! is_array( $a ) || empty( $a['games'] ) || ! array_key_exists( 'allowed', $a['games'] ) ) {
			return null;
		}
		return null === $a['games']['allowed'] ? null : array_map( 'sanitize_key', (array) $a['games']['allowed'] );
	}

	/**
	 * A page of a game's history, newest first (`/v1/games/{game}/drawings`).
	 *
	 * @param string $slug     Game slug.
	 * @param int    $page     1-based page.
	 * @param int    $per_page Draws per page (the API clamps to the plan).
	 * @return array|null
	 */
	public static function drawings( $slug, $page = 1, $per_page = 20 ) {
		$slug = sanitize_key( $slug );
		if ( '' === $slug || ! self::has_key() ) {
			return null;
		}
		$page     = max( 1, (int) $page );
		$per_page = max( 1, min( 100, (int) $per_page ) );
		$locale   = self::locale();
		$url      = add_query_arg(
			array(
				'page'      => $page,
				'page_size' => $per_page,
				'locale'    => $locale,
			),
			self::base() . '/v1/games/' . rawurlencode( $slug ) . '/drawings'
		);
		return self::cached( "d_{$slug}_{$page}_{$per_page}_{$locale}", $url, array( __CLASS__, 'results_ttl' ), true );
	}

	/**
	 * One draw with its neighbours (`/v1/games/{game}/drawings/{date}`).
	 *
	 * @param string $slug Game slug.
	 * @param string $ymd  Date, YYYY-MM-DD.
	 * @return array|null
	 */
	public static function drawing( $slug, $ymd ) {
		$slug = sanitize_key( $slug );
		if ( '' === $slug || ! self::has_key() || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $ymd ) ) {
			return null;
		}
		$locale = self::locale();
		$url    = add_query_arg( array( 'locale' => $locale ), self::base() . '/v1/games/' . rawurlencode( $slug ) . '/drawings/' . $ymd );
		return self::cached(
			"one_{$slug}_{$ymd}_{$locale}",
			$url,
			static function ( $data ) use ( $ymd ) {
				// A draw a few days old is settled; the newest one can still get a "next" link.
				$age = time() - (int) strtotime( $ymd . ' 12:00:00 UTC' );
				return $age > 3 * DAY_IN_SECONDS ? WEEK_IN_SECONDS : Api_Client::results_ttl( $data );
			},
			true
		);
	}

	/**
	 * Register a page where this key's results appear (free keys: where the
	 * credit link is checked). Not cached; refreshes the account.
	 *
	 * @param string $page_url Page URL.
	 * @return array|\WP_Error The account, or why it failed.
	 */
	public static function register_site( $page_url ) {
		if ( ! self::has_key() ) {
			return new \WP_Error( 'lotalytics_no_key', __( 'Add an API key first.', 'lotalytics-lottery-results' ) );
		}
		$response = wp_remote_post(
			self::base() . '/v1/account/sites',
			array(
				// The API checks the page for the credit link before answering:
				// robots.txt and the page itself, up to 10 s each on a slow site.
				'timeout'    => 45,
				'user-agent' => self::user_agent(),
				'headers'    => array(
					'Accept'        => 'application/json',
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . self::key(),
				),
				'body'       => wp_json_encode( array( 'page_url' => $page_url ) ),
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $code < 200 || $code > 299 ) {
			$msg = is_array( $body ) && isset( $body['detail'] ) ? $body['detail'] : '';
			if ( is_array( $msg ) ) {
				$msg = isset( $msg['message'] ) ? $msg['message'] : '';
			}
			/* translators: %d: HTTP status code. */
			return new \WP_Error( 'lotalytics_register_failed', $msg ? (string) $msg : sprintf( __( 'The Lotalytics API answered HTTP %d.', 'lotalytics-lottery-results' ), $code ) );
		}
		self::account( true );
		return is_array( $body ) ? $body : array();
	}

	/**
	 * Is the key close to its daily or monthly quota? Then caches are stretched.
	 *
	 * @return bool
	 */
	public static function quota_low() {
		$q = (array) get_option( self::PREFIX . 'quota', array() );
		foreach ( array( 'daily', 'monthly' ) as $p ) {
			if ( ! empty( $q[ $p . '_limit' ] ) && isset( $q[ $p . '_remaining' ] ) && (int) $q[ $p . '_remaining' ] < 0.1 * (int) $q[ $p . '_limit' ] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Cache lifetime for a results payload: short around a draw, longer between
	 * draws, four times longer while the key is near its quota.
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
		if ( self::quota_low() ) {
			$ttl *= 4;
		}
		/**
		 * Filters how long a game's results are cached, in seconds.
		 *
		 * @param int   $ttl  Seconds.
		 * @param array $data The payload being cached.
		 */
		return (int) apply_filters( 'lotalytics_cache_ttl', $ttl, $data );
	}

	// ── Cache ───────────────────────────────────────────────────────────────

	/**
	 * Transient name for a cache entry. Keyed requests include a hash of the
	 * key, so changing plans never mixes results; every name includes the base.
	 *
	 * @param string $name Logical name.
	 * @param bool   $auth Keyed request.
	 * @return string
	 */
	private static function tkey( $name, $auth ) {
		return self::PREFIX . md5( self::base() . '|' . ( $auth ? md5( self::key() ) . '|' : '' ) . $name );
	}

	/**
	 * Transient name of the "recently failed" marker.
	 *
	 * @param string $name Logical name.
	 * @param bool   $auth Keyed request.
	 * @return string
	 */
	private static function mkey( $name, $auth ) {
		return str_replace( self::PREFIX, self::PREFIX . 'miss_', self::tkey( $name, $auth ) );
	}

	/**
	 * GET a JSON URL through the cache, keeping the last good copy.
	 *
	 * @param string   $name   Logical cache name.
	 * @param string   $url    URL.
	 * @param callable $ttl_fn Seconds to cache a fresh payload.
	 * @param bool     $auth   Send the key.
	 * @return array|null
	 */
	private static function cached( $name, $url, $ttl_fn, $auth ) {
		$tkey  = self::tkey( $name, $auth );
		$fresh = get_transient( $tkey );
		if ( is_array( $fresh ) ) {
			return $fresh;
		}
		// A recent failure with nothing to fall back on (an unknown game, a game
		// the plan doesn't include, an outage on a new install): don't ask again
		// on every page view.
		$mkey = self::mkey( $name, $auth );
		if ( get_transient( $mkey ) ) {
			return null;
		}
		$data = self::fetch( $url, $auth );
		if ( is_array( $data ) ) {
			self::store( $tkey, $data, (int) call_user_func( $ttl_fn, $data ), $url, $auth );
			return $data;
		}
		// The API didn't answer: serve the last good copy, and try again in five minutes.
		$stale = get_option( str_replace( self::PREFIX, self::PREFIX . 'stale_', $tkey ) );
		if ( is_array( $stale ) ) {
			set_transient( $tkey, $stale, 5 * MINUTE_IN_SECONDS );
			self::remember( $tkey, $url, $auth );
			return $stale;
		}
		set_transient( $mkey, 1, 5 * MINUTE_IN_SECONDS );
		self::remember( $mkey, '', false );
		return null;
	}

	/**
	 * Save a fresh payload: the transient, the last good copy, the index.
	 *
	 * @param string $tkey Transient.
	 * @param array  $data Payload.
	 * @param int    $ttl  Seconds.
	 * @param string $url  URL it came from (for pre-fetching).
	 * @param bool   $auth Keyed request.
	 */
	private static function store( $tkey, $data, $ttl, $url, $auth ) {
		set_transient( $tkey, $data, max( MINUTE_IN_SECONDS, $ttl ) );
		update_option( str_replace( self::PREFIX, self::PREFIX . 'stale_', $tkey ), $data, false );
		self::remember( $tkey, $url, $auth );
	}

	/**
	 * Refresh cached results whose game has drawn since they were fetched, so a
	 * visitor never waits on the API after a draw (hourly, WP-Cron).
	 *
	 * @return int Entries refreshed.
	 */
	public static function prefetch() {
		$done  = 0;
		$today = gmdate( 'Y-m-d' );
		foreach ( (array) get_option( self::INDEX, array() ) as $tkey => $entry ) {
			if ( empty( $entry['url'] ) || $done >= 20 ) {
				continue;
			}
			$stale = get_option( str_replace( self::PREFIX, self::PREFIX . 'stale_', $tkey ) );
			if ( ! is_array( $stale ) || empty( $stale['next_draw'] ) || $stale['next_draw'] > $today ) {
				continue;          // not a results payload, or its game hasn't drawn yet
			}
			$auth = ! empty( $entry['auth'] );
			$data = self::fetch( $entry['url'], $auth );
			if ( is_array( $data ) ) {
				self::store( $tkey, $data, (int) self::results_ttl( $data ), $entry['url'], $auth );
				++$done;
			}
		}
		return $done;
	}

	/**
	 * One HTTP GET, decoded. Never throws; null on any failure.
	 *
	 * @param string $url  URL.
	 * @param bool   $auth Send the key.
	 * @return array|null
	 */
	private static function fetch( $url, $auth = false ) {
		$headers = array( 'Accept' => 'application/json' );
		if ( $auth ) {
			if ( ! self::has_key() ) {
				return null;
			}
			$headers['Authorization'] = 'Bearer ' . self::key();
		}
		// A cold page that needs several games can trip the plan's per-second
		// limit (1/s on the free plan): wait as asked, briefly, and try again.
		// A daily or monthly quota (a long Retry-After) is never waited out.
		for ( $attempt = 0; ; $attempt++ ) {
			$response          = wp_remote_get(
				$url,
				array(
					'timeout'    => 10,
					'user-agent' => self::user_agent(),
					'headers'    => $headers,
				)
			);
			self::$last_status = 0;
			if ( is_wp_error( $response ) ) {
				update_option( self::PREFIX . 'last_error', $response->get_error_message(), false );
				return null;
			}
			$code  = (int) wp_remote_retrieve_response_code( $response );
			$after = (int) wp_remote_retrieve_header( $response, 'retry-after' );
			if ( 429 !== $code || $attempt >= 2 || $after < 1 || $after > 3 ) {
				break;
			}
			sleep( $after );
		}
		self::$last_status = $code;
		if ( $auth ) {
			self::note_quota( $response );
		}
		if ( 200 !== $code ) {
			// A game the plan doesn't include (403) or a date with no draw (404) is
			// the page's business, not an API problem worth flagging in Site Health.
			if ( ! in_array( $code, array( 403, 404, 429 ), true ) || ! $auth ) {
				/* translators: %d: HTTP status code. */
				update_option( self::PREFIX . 'last_error', sprintf( __( 'The Lotalytics API answered HTTP %d.', 'lotalytics-lottery-results' ), $code ), false );
			}
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
	 * Remember the quota headers of a keyed response.
	 *
	 * @param array $response wp_remote_* response.
	 */
	private static function note_quota( $response ) {
		$map = array(
			'daily_limit'       => 'x-quota-daily-limit',
			'daily_remaining'   => 'x-quota-daily-remaining',
			'monthly_limit'     => 'x-quota-monthly-limit',
			'monthly_remaining' => 'x-quota-monthly-remaining',
		);
		$q   = array();
		foreach ( $map as $k => $h ) {
			$v = wp_remote_retrieve_header( $response, $h );
			if ( '' !== $v && null !== $v ) {
				$q[ $k ] = (int) $v;
			}
		}
		$att = wp_remote_retrieve_header( $response, 'x-lotalytics-attribution' );
		if ( $att ) {
			$q['attribution'] = sanitize_key( $att );
		}
		if ( $q ) {
			$q['at'] = time();
			update_option( self::PREFIX . 'quota', $q, false );
		}
	}

	/**
	 * The User-Agent: the plugin, its version, the site.
	 *
	 * @return string
	 */
	private static function user_agent() {
		return 'LotalyticsWP/' . LOTALYTICS_RESULTS_VERSION . '; ' . home_url( '/' );
	}

	/**
	 * Delete every cached response (the last good copies stay as a safety net).
	 */
	public static function clear_cache() {
		foreach ( array_keys( (array) get_option( self::INDEX, array() ) ) as $tkey ) {
			delete_transient( $tkey );
		}
		update_option( self::INDEX, array(), false );
	}

	/**
	 * Remember a transient (and the URL it came from) so it can be cleared and
	 * pre-fetched; works with object caches, where transients can't be found by prefix.
	 *
	 * @param string $tkey Transient key.
	 * @param string $url  Source URL ('' for markers).
	 * @param bool   $auth Keyed request.
	 */
	private static function remember( $tkey, $url, $auth ) {
		$index = (array) get_option( self::INDEX, array() );
		$entry = array(
			'url'  => (string) $url,
			'auth' => (bool) $auth,
		);
		if ( ! isset( $index[ $tkey ] ) || $index[ $tkey ] !== $entry ) {
			$index[ $tkey ] = $entry;
			update_option( self::INDEX, array_slice( $index, -500, null, true ), false );
		}
	}
}
