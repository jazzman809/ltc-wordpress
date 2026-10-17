<?php
/**
 * Settings → Lotalytics.
 *
 * The credit link is opt-in and off by default (wordpress.org guideline 10):
 * the plugin asks, the Lotalytics terms decide what the free plan requires.
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The plugin's single settings screen.
 */
class Settings {

	const OPTION = 'lotalytics_settings';
	const KEY    = 'lotalytics_api_key';
	const PAGE   = 'lotalytics-results';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_post_lotalytics_clear_cache', array( __CLASS__, 'clear_cache' ) );
		add_action( 'admin_post_lotalytics_register_site', array( __CLASS__, 'register_site' ) );
		add_action( 'update_option_' . self::OPTION, array( __CLASS__, 'settings_changed' ), 10, 2 );
		add_action( 'add_option_' . self::KEY, array( __CLASS__, 'key_changed' ) );
		add_action( 'update_option_' . self::KEY, array( __CLASS__, 'key_changed' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( LOTALYTICS_RESULTS_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Bundled translations (es, fr, pt, de, it); translate.wordpress.org ones take precedence.
	 */
	public static function load_textdomain() {
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- ships translations for six languages before translate.wordpress.org has any.
		load_plugin_textdomain( 'lotalytics-lottery-results', false, dirname( plugin_basename( LOTALYTICS_RESULTS_FILE ) ) . '/languages' );
	}

	/**
	 * Current settings with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		return wp_parse_args(
			(array) get_option( self::OPTION, array() ),
			array(
				'credit'       => 0,
				'default_game' => 'powerball',
				'disable_css'  => 0,
				'section'      => 1,
				'section_base' => 'lottery',
				'per_page'     => 20,
			)
		);
	}

	/**
	 * Did the site owner opt in to the credit link?
	 *
	 * @return bool
	 */
	public static function credit_enabled() {
		return (bool) self::get()['credit'];
	}

	/**
	 * Has the site owner turned the plugin's CSS off?
	 *
	 * @return bool
	 */
	public static function css_disabled() {
		return (bool) self::get()['disable_css'];
	}

	/**
	 * The game blocks and shortcodes use when none is given.
	 *
	 * @return string
	 */
	public static function default_game() {
		return sanitize_key( self::get()['default_game'] );
	}

	/**
	 * Is the results section switched on? (It also needs a key whose plan includes it.)
	 *
	 * @return bool
	 */
	public static function section_enabled() {
		return (bool) self::get()['section'];
	}

	/**
	 * The results section's base path, e.g. "lottery".
	 *
	 * @return string
	 */
	public static function section_base() {
		$base = sanitize_title( self::get()['section_base'] );
		return '' !== $base ? $base : 'lottery';
	}

	/**
	 * Draws per archive page.
	 *
	 * @return int
	 */
	public static function per_page() {
		return max( 5, min( 100, (int) self::get()['per_page'] ) );
	}

	/**
	 * The section's rules depend on two settings; flush them when either changes.
	 *
	 * @param mixed $old Old value.
	 * @param mixed $new New value.
	 */
	public static function settings_changed( $old, $new ) {
		$old = (array) $old;
		$new = (array) $new;
		foreach ( array( 'section', 'section_base' ) as $k ) {
			if ( ( isset( $old[ $k ] ) ? $old[ $k ] : null ) !== ( isset( $new[ $k ] ) ? $new[ $k ] : null ) ) {
				Results_Section::request_flush();
				return;
			}
		}
	}

	/**
	 * A new key: fresh account details, and rules for the section.
	 */
	public static function key_changed() {
		Api_Client::account( true );
		Results_Section::request_flush();
	}

	/**
	 * Add the page.
	 */
	public static function menu() {
		add_options_page(
			__( 'Lotalytics Lottery Results', 'lotalytics-lottery-results' ),
			__( 'Lotalytics', 'lotalytics-lottery-results' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * A "Settings" link on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ), esc_html__( 'Settings', 'lotalytics-lottery-results' ) )
		);
		return $links;
	}

	/**
	 * Register the option and its fields (Settings API).
	 */
	public static function register() {
		register_setting(
			self::PAGE,
			self::KEY,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_key_field' ),
				'default'           => '',
			)
		);
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
		add_settings_section( 'lotalytics_main', '', '__return_false', self::PAGE );
		add_settings_field( 'api_key', __( 'API key', 'lotalytics-lottery-results' ), array( __CLASS__, 'field_key' ), self::PAGE, 'lotalytics_main' );
		add_settings_field( 'section', __( 'Results section', 'lotalytics-lottery-results' ), array( __CLASS__, 'field_section' ), self::PAGE, 'lotalytics_main' );
		add_settings_field( 'credit', __( 'Credit link', 'lotalytics-lottery-results' ), array( __CLASS__, 'field_credit' ), self::PAGE, 'lotalytics_main' );
		add_settings_field( 'default_game', __( 'Default game', 'lotalytics-lottery-results' ), array( __CLASS__, 'field_game' ), self::PAGE, 'lotalytics_main' );
		add_settings_field( 'disable_css', __( 'Styling', 'lotalytics-lottery-results' ), array( __CLASS__, 'field_css' ), self::PAGE, 'lotalytics_main' );
	}

	/**
	 * Sanitize the submitted settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$base = isset( $input['section_base'] ) ? sanitize_title( $input['section_base'] ) : 'lottery';
		return array(
			'credit'       => empty( $input['credit'] ) ? 0 : 1,
			'default_game' => isset( $input['default_game'] ) ? sanitize_key( $input['default_game'] ) : 'powerball',
			'disable_css'  => empty( $input['disable_css'] ) ? 0 : 1,
			'section'      => empty( $input['section'] ) ? 0 : 1,
			'section_base' => '' !== $base ? $base : 'lottery',
			'per_page'     => isset( $input['per_page'] ) ? max( 5, min( 100, absint( $input['per_page'] ) ) ) : 20,
		);
	}

	/**
	 * The key: kept when the field is left blank (it's never printed back), cleared with "-".
	 *
	 * @param mixed $value Submitted value.
	 * @return string
	 */
	public static function sanitize_key_field( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return (string) get_option( self::KEY, '' );
		}
		if ( '-' === $value ) {
			return '';
		}
		if ( ! preg_match( '/^ltc_[A-Za-z0-9_\-]{10,}$/', $value ) ) {
			add_settings_error( self::KEY, 'lotalytics_bad_key', __( 'That doesn\'t look like a Lotalytics API key (they start with "ltc_"). The previous key was kept.', 'lotalytics-lottery-results' ) );
			return (string) get_option( self::KEY, '' );
		}
		return $value;
	}

	/**
	 * The key field and what the key gives.
	 */
	public static function field_key() {
		$key = Api_Client::key();
		if ( defined( 'LOTALYTICS_API_KEY' ) ) {
			echo '<p>' . esc_html__( 'Set in wp-config.php (LOTALYTICS_API_KEY).', 'lotalytics-lottery-results' ) . '</p>';
		} else {
			printf(
				'<input type="password" name="%1$s" value="" class="regular-text" autocomplete="off" placeholder="%2$s"><p class="description">%3$s</p>',
				esc_attr( self::KEY ),
				esc_attr( $key ? substr( $key, 0, 12 ) . '…' : 'ltc_…' ),
				esc_html__( 'Optional. Without a key the free results blocks work as they are. A key on a paid plan adds the results archive and a page per draw on your site. Leave blank to keep the current key; enter - to remove it.', 'lotalytics-lottery-results' )
			);
		}
		printf(
			'<p><a href="https://lotalytics.com/app/developer" target="_blank" rel="noopener noreferrer">%s</a></p>',
			esc_html__( 'Get or manage a key on lotalytics.com', 'lotalytics-lottery-results' )
		);
		if ( '' === $key ) {
			return;
		}
		$a = Api_Client::account();
		if ( ! is_array( $a ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Lotalytics didn\'t accept this key. Check it on the Developer page, or remove it.', 'lotalytics-lottery-results' ) . '</p></div>';
			return;
		}
		self::account_panel( $a );
	}

	/**
	 * Plan, features, games, usage and (free keys) registered sites.
	 *
	 * @param array $a /v1/account.
	 */
	private static function account_panel( $a ) {
		$yes  = esc_html__( 'yes', 'lotalytics-lottery-results' );
		$no   = esc_html__( 'no', 'lotalytics-lottery-results' );
		$f    = (array) $a['features'];
		$u    = (array) $a['usage'];
		$plan = ! empty( $a['plan']['name'] ) ? $a['plan']['name'] : __( 'Custom', 'lotalytics-lottery-results' );
		echo '<table class="widefat striped" style="max-width:40em;margin-top:.5em"><tbody>';
		$row = static function ( $label, $value ) {
			printf( '<tr><th scope="row" style="width:45%%">%s</th><td>%s</td></tr>', esc_html( $label ), wp_kses_post( $value ) );
		};
		$row( __( 'Plan', 'lotalytics-lottery-results' ), esc_html( $plan ) . ' <code>' . esc_html( $a['key']['prefix'] ) . '…</code>' );
		$row( __( 'Results archive and draw pages', 'lotalytics-lottery-results' ), ! empty( $f['archive'] ) ? $yes : $no );
		$row( __( 'Statistics', 'lotalytics-lottery-results' ), ! empty( $f['stats'] ) ? $yes : $no );
		$games = isset( $a['games']['allowed'] ) && null !== $a['games']['allowed']
			? ( $a['games']['allowed'] ? esc_html( implode( ', ', $a['games']['allowed'] ) ) : esc_html__( 'none chosen yet (choose them on the Developer page)', 'lotalytics-lottery-results' ) )
			: esc_html__( 'all', 'lotalytics-lottery-results' );
		$row( __( 'Games', 'lotalytics-lottery-results' ), $games );
		if ( ! empty( $u['monthly_limit'] ) ) {
			/* translators: 1: requests used, 2: the limit. */
			$row( __( 'Requests this month', 'lotalytics-lottery-results' ), esc_html( sprintf( __( '%1$s of %2$s', 'lotalytics-lottery-results' ), number_format_i18n( $u['monthly_used'] ), number_format_i18n( $u['monthly_limit'] ) ) ) );
		}
		if ( ! empty( $u['daily_limit'] ) ) {
			/* translators: 1: requests used, 2: the limit. */
			$row( __( 'Requests today', 'lotalytics-lottery-results' ), esc_html( sprintf( __( '%1$s of %2$s', 'lotalytics-lottery-results' ), number_format_i18n( (int) $u['daily_used'] ), number_format_i18n( $u['daily_limit'] ) ) ) );
		}
		echo '</tbody></table>';
		if ( Api_Client::quota_low() ) {
			echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'This key is close to its request limit. The plugin is caching results for longer until the limit resets.', 'lotalytics-lottery-results' ) . '</p></div>';
		}
		if ( ! empty( $a['attribution'] ) ) {
			self::attribution_panel( (array) $a['attribution'] );
		}
	}

	/**
	 * A free key's credit-link status and the "register this site" button.
	 *
	 * @param array $att Attribution view.
	 */
	private static function attribution_panel( $att ) {
		$domain  = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		$domain  = preg_replace( '/^www\./', '', $domain );
		$sites   = isset( $att['sites'] ) ? (array) $att['sites'] : array();
		$here    = array_filter(
			$sites,
			static function ( $s ) use ( $domain ) {
				return isset( $s['domain'] ) && $s['domain'] === $domain;
			}
		);
		$states  = array(
			'grace'    => __( 'Grace period: add the credit link soon.', 'lotalytics-lottery-results' ),
			'verified' => __( 'Credit link found.', 'lotalytics-lottery-results' ),
			'missing'  => __( 'Credit link missing: the key will be limited soon.', 'lotalytics-lottery-results' ),
			'trickle'  => __( 'Credit link missing: the key is limited to a few requests a day.', 'lotalytics-lottery-results' ),
		);
		$state = isset( $att['state'], $states[ $att['state'] ] ) ? $states[ $att['state'] ] : '';
		echo '<p><strong>' . esc_html__( 'Free plan credit link', 'lotalytics-lottery-results' ) . ':</strong> ' . esc_html( $state ) . '</p>';
		if ( $here ) {
			$s = reset( $here );
			/* translators: 1: site domain, 2: status of the credit-link check. */
			echo '<p>' . esc_html( sprintf( __( 'This site (%1$s) is registered; last check: %2$s.', 'lotalytics-lottery-results' ), $domain, isset( $s['status'] ) && $s['status'] ? $s['status'] : __( 'pending', 'lotalytics-lottery-results' ) ) ) . '</p>';
			echo '<p class="description">' . esc_html__( 'Turn on the credit link below so the check finds it.', 'lotalytics-lottery-results' ) . '</p>';
			return;
		}
		echo '<p class="description">' . esc_html__( 'Register this site so Lotalytics can find the credit link here.', 'lotalytics-lottery-results' ) . '</p>';
		printf(
			'<p><a class="button" href="%1$s">%2$s</a></p>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=lotalytics_register_site' ), 'lotalytics_register_site' ) ),
			esc_html__( 'Register this site', 'lotalytics-lottery-results' )
		);
	}

	/**
	 * The results-section switch, its base and the page size.
	 */
	public static function field_section() {
		$s     = self::get();
		$avail = Api_Client::has_key() && Api_Client::features()['draw_pages'];
		printf(
			'<label><input type="checkbox" name="%1$s[section]" value="1" %2$s> %3$s</label>',
			esc_attr( self::OPTION ),
			checked( 1, (int) $s['section'], false ),
			esc_html__( 'Publish a results section on this site', 'lotalytics-lottery-results' )
		);
		printf(
			'<p><label>%1$s <code>%2$s/</code><input type="text" name="%3$s[section_base]" value="%4$s" class="small-text" style="width:8em"> <code>/powerball/</code></label></p>',
			esc_html__( 'Address:', 'lotalytics-lottery-results' ),
			esc_html( untrailingslashit( home_url() ) ),
			esc_attr( self::OPTION ),
			esc_attr( self::section_base() )
		);
		printf(
			'<p><label>%1$s <input type="number" min="5" max="100" name="%2$s[per_page]" value="%3$d" class="small-text"></label></p>',
			esc_html__( 'Draws per page:', 'lotalytics-lottery-results' ),
			esc_attr( self::OPTION ),
			(int) self::per_page()
		);
		if ( $avail && $s['section'] ) {
			printf( '<p class="description"><a href="%1$s" target="_blank">%2$s</a></p>', esc_url( Results_Section::url() ), esc_html__( 'View the results section', 'lotalytics-lottery-results' ) );
		} elseif ( ! $avail ) {
			echo '<p class="description">' . esc_html__( 'Needs an API key whose plan includes the results archive and draw pages.', 'lotalytics-lottery-results' ) . '</p>';
		}
	}

	/**
	 * The credit opt-in.
	 */
	public static function field_credit() {
		$s = self::get();
		printf(
			'<label><input type="checkbox" name="%1$s[credit]" value="1" %2$s> %3$s</label><p class="description">%4$s</p>',
			esc_attr( self::OPTION ),
			checked( 1, (int) $s['credit'], false ),
			esc_html__( 'Show "Results by Lotalytics" under the results', 'lotalytics-lottery-results' ),
			esc_html__( 'Off unless you turn it on. The free Lotalytics service asks sites that use it to show this credit; see the terms linked below.', 'lotalytics-lottery-results' )
		);
	}

	/**
	 * The default game.
	 */
	public static function field_game() {
		$s     = self::get();
		$games = Api_Client::games();
		if ( ! $games ) {
			printf(
				'<input type="text" name="%1$s[default_game]" value="%2$s" class="regular-text"><p class="description">%3$s</p>',
				esc_attr( self::OPTION ),
				esc_attr( $s['default_game'] ),
				esc_html__( 'The game list could not be loaded. Enter a game slug, for example "powerball".', 'lotalytics-lottery-results' )
			);
			return;
		}
		printf( '<select name="%s[default_game]">', esc_attr( self::OPTION ) );
		foreach ( $games as $g ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $g['slug'] ), selected( $s['default_game'], $g['slug'], false ), esc_html( $g['name'] ) );
		}
		echo '</select>';
	}

	/**
	 * The CSS switch.
	 */
	public static function field_css() {
		$s = self::get();
		printf(
			'<label><input type="checkbox" name="%1$s[disable_css]" value="1" %2$s> %3$s</label><p class="description">%4$s</p>',
			esc_attr( self::OPTION ),
			checked( 1, (int) $s['disable_css'], false ),
			esc_html__( 'Disable the plugin stylesheet', 'lotalytics-lottery-results' ),
			esc_html__( 'For themes that style the results themselves. The markup uses ltc- class names.', 'lotalytics-lottery-results' )
		);
	}

	/**
	 * The screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Lotalytics Lottery Results', 'lotalytics-lottery-results' ); ?></h1>
			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag set by our own redirect.
			if ( isset( $_GET['lotalytics_cleared'] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache cleared.', 'lotalytics-lottery-results' ) . '</p></div>';
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only flag set by our own redirect.
			if ( isset( $_GET['lotalytics_registered'] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Site registered.', 'lotalytics-lottery-results' ) . '</p></div>';
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only message from our own redirect.
			$lotalytics_error = isset( $_GET['lotalytics_error'] ) ? sanitize_text_field( wp_unslash( $_GET['lotalytics_error'] ) ) : '';
			if ( '' !== $lotalytics_error ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $lotalytics_error ) . '</p></div>';
			}
			settings_errors( self::KEY );
			?>
			<p><?php esc_html_e( 'Add the "Lottery results" or "Next lottery draw" block to any page, or use a shortcode:', 'lotalytics-lottery-results' ); ?></p>
			<p><code>[lotalytics_results game="powerball" draws="5"]</code> &nbsp; <code>[lotalytics_next_draw game="powerball"]</code> &nbsp; <code>[lotalytics_archive game="powerball"]</code></p>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="lotalytics_clear_cache">
				<?php wp_nonce_field( 'lotalytics_clear_cache' ); ?>
				<?php submit_button( __( 'Clear cached results', 'lotalytics-lottery-results' ), 'secondary', 'submit', false ); ?>
			</form>
			<h2><?php esc_html_e( 'About the data', 'lotalytics-lottery-results' ); ?></h2>
			<p>
				<?php esc_html_e( 'This plugin is a front end for the Lotalytics service. Your server requests results from api.lotalytics.com and caches them; your visitors\' browsers never contact Lotalytics. Each request sends the game, the number of draws, the page language and your site\'s address. Nothing about your visitors is sent.', 'lotalytics-lottery-results' ); ?>
			</p>
			<p>
				<a href="https://lotalytics.com/terms" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms of service', 'lotalytics-lottery-results' ); ?></a>
				&middot;
				<a href="https://lotalytics.com/privacy" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy policy', 'lotalytics-lottery-results' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Register this site for the key's credit-link check (admin-post, nonce-checked).
	 */
	public static function register_site() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'lotalytics-lottery-results' ) );
		}
		check_admin_referer( 'lotalytics_register_site' );
		$result = Api_Client::register_site( home_url( '/' ) );
		$arg    = is_wp_error( $result ) ? array( 'lotalytics_error' => rawurlencode( $result->get_error_message() ) ) : array( 'lotalytics_registered' => '1' );
		wp_safe_redirect( add_query_arg( $arg, admin_url( 'options-general.php?page=' . self::PAGE ) ) );
		exit;
	}

	/**
	 * Clear the cache (admin-post, nonce-checked).
	 */
	public static function clear_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do that.', 'lotalytics-lottery-results' ) );
		}
		check_admin_referer( 'lotalytics_clear_cache' );
		Api_Client::clear_cache();
		wp_safe_redirect( add_query_arg( 'lotalytics_cleared', '1', admin_url( 'options-general.php?page=' . self::PAGE ) ) );
		exit;
	}
}
