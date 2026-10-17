<?php
/**
 * Remove everything the plugin stored: settings, cached results, the last
 * good copies, and the last error.
 *
 * @package Lotalytics\Results
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$lotalytics_keys = (array) get_option( 'lotalytics_cache_keys', array() );
foreach ( $lotalytics_keys as $lotalytics_key ) {
	delete_transient( $lotalytics_key );
	delete_option( str_replace( 'lotalytics_', 'lotalytics_stale_', $lotalytics_key ) );
}
foreach ( array( 'lotalytics_settings', 'lotalytics_cache_keys', 'lotalytics_last_error' ) as $lotalytics_option ) {
	delete_option( $lotalytics_option );
}
