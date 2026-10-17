<?php
/**
 * Plugin Name:       Lotalytics – Lottery Results
 * Plugin URI:        https://lotalytics.com/widgets
 * Description:       Show the latest lottery results and the next draw date for 50+ lotteries, as blocks, a shortcode or a widget. With a Lotalytics API key: a results archive and a page per draw on your site.
 * Version:           0.2.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Lotalytics
 * Author URI:        https://lotalytics.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lotalytics-lottery-results
 * Domain Path:       /languages
 *
 * @package Lotalytics\Results
 */

namespace Lotalytics\Results;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LOTALYTICS_RESULTS_VERSION', '0.2.0' );
define( 'LOTALYTICS_RESULTS_FILE', __FILE__ );
define( 'LOTALYTICS_RESULTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'LOTALYTICS_RESULTS_URL', plugin_dir_url( __FILE__ ) );

require_once LOTALYTICS_RESULTS_DIR . 'includes/class-api-client.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-renderer.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-settings.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-blocks.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-shortcodes.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-widget.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-site-health.php';
require_once LOTALYTICS_RESULTS_DIR . 'includes/class-results-section.php';

add_action( 'plugins_loaded', array( Settings::class, 'init' ) );
add_action( 'init', array( Renderer::class, 'register_assets' ) );
add_action( 'init', array( Blocks::class, 'register' ) );
add_action( 'init', array( Shortcodes::class, 'register' ) );
add_action( 'widgets_init', array( Widget::class, 'register' ) );
add_filter( 'site_status_tests', array( Site_Health::class, 'register' ) );
Results_Section::init();

// Hourly: refresh cached results whose game has drawn, so visitors never wait on the API.
const PREFETCH_HOOK = 'lotalytics_prefetch';
add_action( PREFETCH_HOOK, array( Api_Client::class, 'prefetch' ) );
add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( PREFETCH_HOOK ) ) {
			wp_schedule_event( time() + 300, 'hourly', PREFETCH_HOOK );
		}
	}
);

register_activation_hook(
	__FILE__,
	static function () {
		Results_Section::request_flush();
	}
);
register_deactivation_hook(
	__FILE__,
	static function () {
		wp_clear_scheduled_hook( PREFETCH_HOOK );
		flush_rewrite_rules( false );
	}
);
