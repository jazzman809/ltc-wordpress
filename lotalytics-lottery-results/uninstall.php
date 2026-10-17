<?php
/**
 * Remove everything the plugin stored: settings, the key, cached results, the
 * last good copies, the quota snapshot, the last error and the cron event.
 *
 * @package Lotalytics\Results
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$lotalytics_index = (array) get_option( 'lotalytics_cache_index', array() );
$lotalytics_keys  = array_merge( array_keys( $lotalytics_index ), (array) get_option( 'lotalytics_cache_keys', array() ) );
foreach ( $lotalytics_keys as $lotalytics_key ) {
	delete_transient( $lotalytics_key );
	delete_option( str_replace( 'lotalytics_', 'lotalytics_stale_', $lotalytics_key ) );
}
foreach ( array( 'lotalytics_settings', 'lotalytics_api_key', 'lotalytics_cache_index', 'lotalytics_cache_keys', 'lotalytics_last_error', 'lotalytics_quota', 'lotalytics_flush_rules' ) as $lotalytics_option ) {
	delete_option( $lotalytics_option );
}
wp_clear_scheduled_hook( 'lotalytics_prefetch' );
