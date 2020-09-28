<?php
/**
 * WooConnection Uninstall
 *
 * Uninstalling WooConnection delete tables and options.
 *
 */
// if uninstall.php is not called by WordPress, die...
if (!defined( 'WP_UNINSTALL_PLUGIN' ) || !WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) )) {
	status_header( 404 );
	exit;
}
//Custom Query :  drop a custom  database table "wp_wooconnection_campaign_goals"
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_campaign_goals" );
?>
