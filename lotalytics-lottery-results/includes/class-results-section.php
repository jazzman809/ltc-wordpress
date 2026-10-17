<?php
/**
 * A results section on the site itself, for keys whose plan includes it:
 *
 *   /lottery/                      the games
 *   /lottery/<game>/               the game's history, paginated (/page/2/ …)
 *   /lottery/<game>/<YYYY-MM-DD>/  one draw
 *
 * Virtual pages: nothing is written to the posts table. They render inside the
 * theme (classic and block themes), carry their own title, description and
 * canonical link, and are listed in the WordPress sitemap. The base ("lottery")
 * is a setting. Without the feature, these URLs are ordinary 404s.
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrite rules, the request, the wrapper template, titles and the sitemap.
 */
class Results_Section {

	const FLUSH = 'lotalytics_flush_rules';

	/**
	 * What the current request is showing, once resolved.
	 *
	 * @var array|null
	 */
	private static $view = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'rules' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'resolve' ) );
		add_filter( 'template_include', array( __CLASS__, 'template' ), 99 );
		add_filter( 'document_title_parts', array( __CLASS__, 'title_parts' ) );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		add_action( 'init', array( __CLASS__, 'sitemap' ), 30 );
	}

	/**
	 * Is the section on (setting) and does the key's plan include draw pages?
	 *
	 * @return bool
	 */
	public static function enabled() {
		return Settings::section_enabled() && Api_Client::has_key() && Api_Client::features()['draw_pages'];
	}

	/**
	 * The URL of the section, a game, or one draw.
	 *
	 * @param string|null $game Game slug.
	 * @param string|null $ymd  Draw date.
	 * @param int         $page Archive page.
	 * @return string
	 */
	public static function url( $game = null, $ymd = null, $page = 1 ) {
		$path = Settings::section_base() . '/';
		if ( $game ) {
			$path .= sanitize_key( $game ) . '/';
			if ( $ymd ) {
				$path .= $ymd . '/';
			} elseif ( $page > 1 ) {
				$path .= 'page/' . (int) $page . '/';
			}
		}
		return home_url( user_trailingslashit( $path ) );
	}

	/**
	 * Rewrite rules (registered whenever the setting is on; flushed when it changes).
	 */
	public static function rules() {
		if ( Settings::section_enabled() ) {
			$base = preg_quote( Settings::section_base(), '#' );
			add_rewrite_rule( "^{$base}/?$", 'index.php?lotalytics_section=1', 'top' );
			add_rewrite_rule( "^{$base}/([a-z0-9-]+)/page/([0-9]+)/?$", 'index.php?lotalytics_section=1&lotalytics_game=$matches[1]&lotalytics_paged=$matches[2]', 'top' );
			add_rewrite_rule( "^{$base}/([a-z0-9-]+)/([0-9]{4}-[0-9]{2}-[0-9]{2})/?$", 'index.php?lotalytics_section=1&lotalytics_game=$matches[1]&lotalytics_date=$matches[2]', 'top' );
			add_rewrite_rule( "^{$base}/([a-z0-9-]+)/?$", 'index.php?lotalytics_section=1&lotalytics_game=$matches[1]', 'top' );
		}
		if ( get_option( self::FLUSH ) ) {
			delete_option( self::FLUSH );
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Ask for a rules flush on the next request (setting changed, activation).
	 */
	public static function request_flush() {
		update_option( self::FLUSH, 1, false );
	}

	/**
	 * Our query vars.
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		return array_merge( $vars, array( 'lotalytics_section', 'lotalytics_game', 'lotalytics_date', 'lotalytics_paged' ) );
	}

	/**
	 * Work out what this request shows, or make it a 404.
	 */
	public static function resolve() {
		if ( ! get_query_var( 'lotalytics_section' ) ) {
			return;
		}
		$game = sanitize_key( (string) get_query_var( 'lotalytics_game' ) );
		$ymd  = (string) get_query_var( 'lotalytics_date' );
		$page = max( 1, (int) get_query_var( 'lotalytics_paged' ) );
		$view = null;
		if ( self::enabled() ) {
			if ( '' === $game ) {
				$view = array( 'type' => 'index' );
			} elseif ( '' !== $ymd ) {
				$data = Api_Client::drawing( $game, $ymd );
				$view = $data ? array(
					'type' => 'draw',
					'data' => $data,
				) : null;
			} else {
				$data = Api_Client::drawings( $game, $page, Settings::per_page() );
				$view = ( $data && ! empty( $data['drawings'] ) ) ? array(
					'type' => 'game',
					'data' => $data,
					'page' => $page,
				) : null;
			}
		}
		if ( null === $view ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			return;
		}
		self::$view = $view;
		// Our wrapper prints the content after wp_head(), so the stylesheet has
		// to be queued now (a block theme renders its content before the head).
		add_action( 'wp_enqueue_scripts', array( Renderer::class, 'enqueue_style' ) );
		global $wp_query;
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		status_header( 200 );
	}

	/**
	 * Our wrapper template for section requests.
	 *
	 * @param string $template Template WordPress chose.
	 * @return string
	 */
	public static function template( $template ) {
		return self::$view ? LOTALYTICS_RESULTS_DIR . 'templates/section.php' : $template;
	}

	/**
	 * The page body for the current request (called by templates/section.php).
	 *
	 * @return string HTML.
	 */
	public static function content() {
		$v = self::$view;
		if ( ! $v ) {
			return '';
		}
		if ( 'index' === $v['type'] ) {
			return Renderer::section_index( self::games() );
		}
		if ( 'draw' === $v['type'] ) {
			return Renderer::draw_page( $v['data'] );
		}
		return Renderer::archive( $v['data'], $v['page'], true );
	}

	/**
	 * The games the section lists: the key's games, or every game.
	 *
	 * @return array List of ['slug', 'name'].
	 */
	public static function games() {
		$all     = Api_Client::games();
		$allowed = Api_Client::allowed_games();
		if ( null === $allowed ) {
			return $all;
		}
		return array_values(
			array_filter(
				$all,
				static function ( $g ) use ( $allowed ) {
					return in_array( $g['slug'], $allowed, true );
				}
			)
		);
	}

	/**
	 * The current page's title.
	 *
	 * @return string
	 */
	private static function title() {
		$v = self::$view;
		if ( 'index' === $v['type'] ) {
			return __( 'Lottery results', 'lotalytics-lottery-results' );
		}
		$name = isset( $v['data']['name'] ) ? $v['data']['name'] : '';
		if ( 'draw' === $v['type'] ) {
			/* translators: 1: lottery game name, 2: draw date. */
			return sprintf( __( '%1$s numbers for %2$s', 'lotalytics-lottery-results' ), $name, Renderer::date( $v['data']['drawing']['date'] ) );
		}
		/* translators: %s: lottery game name. */
		$t = sprintf( __( '%s results and winning numbers', 'lotalytics-lottery-results' ), $name );
		if ( $v['page'] > 1 ) {
			/* translators: %d: page number. */
			$t .= ' – ' . sprintf( __( 'Page %d', 'lotalytics-lottery-results' ), $v['page'] );
		}
		return $t;
	}

	/**
	 * The <title>.
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public static function title_parts( $parts ) {
		if ( self::$view ) {
			$parts['title'] = self::title();
		}
		return $parts;
	}

	/**
	 * Description and canonical link in <head>.
	 */
	public static function head() {
		$v = self::$view;
		if ( ! $v ) {
			return;
		}
		if ( 'draw' === $v['type'] ) {
			$d    = $v['data']['drawing'];
			$nums = implode( '-', array_map( 'intval', (array) $d['numbers'] ) );
			if ( ! empty( $d['bonus_numbers'] ) ) {
				$nums .= ' + ' . implode( '-', array_map( 'intval', (array) $d['bonus_numbers'] ) );
			}
			/* translators: 1: lottery game name, 2: draw date, 3: the winning numbers. */
			$desc  = sprintf( __( '%1$s winning numbers for %2$s: %3$s.', 'lotalytics-lottery-results' ), $v['data']['name'], Renderer::date( $d['date'] ), $nums );
			$canon = self::url( $v['data']['game'], $d['date'] );
		} elseif ( 'game' === $v['type'] ) {
			/* translators: %s: lottery game name. */
			$desc  = sprintf( __( 'The latest %s results and winning numbers, draw by draw.', 'lotalytics-lottery-results' ), $v['data']['name'] );
			$canon = self::url( $v['data']['game'], null, $v['page'] );
		} else {
			$desc  = __( 'Latest lottery results and winning numbers.', 'lotalytics-lottery-results' );
			$canon = self::url();
		}
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canon ) );
	}

	/**
	 * Add the section to the WordPress sitemap (wp-sitemap.xml).
	 */
	public static function sitemap() {
		if ( ! function_exists( 'wp_register_sitemap_provider' ) || ! Settings::section_enabled() ) {
			return;
		}
		require_once LOTALYTICS_RESULTS_DIR . 'includes/class-sitemap-provider.php';
		wp_register_sitemap_provider( 'lotalytics', new Sitemap_Provider() );
	}
}
