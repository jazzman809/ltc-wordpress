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
	const PAGE   = 'lotalytics-results';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_post_lotalytics_clear_cache', array( __CLASS__, 'clear_cache' ) );
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
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
		add_settings_section( 'lotalytics_main', '', '__return_false', self::PAGE );
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
		return array(
			'credit'       => empty( $input['credit'] ) ? 0 : 1,
			'default_game' => isset( $input['default_game'] ) ? sanitize_key( $input['default_game'] ) : 'powerball',
			'disable_css'  => empty( $input['disable_css'] ) ? 0 : 1,
		);
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
			?>
			<p><?php esc_html_e( 'Add the "Lottery results" or "Next lottery draw" block to any page, or use a shortcode:', 'lotalytics-lottery-results' ); ?></p>
			<p><code>[lotalytics_results game="powerball" draws="5"]</code> &nbsp; <code>[lotalytics_next_draw game="powerball"]</code></p>
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
