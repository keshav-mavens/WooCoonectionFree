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
global $wpdb;
//Custom Query :  drop a custom  database table "wp_wooconnection_campaign_goals"
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_campaign_goals" );
$optionName = 'wc_plugin_details';
delete_option($optionName);
delete_option('wc_pro_version_activated');
//Custom Query :  drop a custom  database table "wp_wooconnection_countries"
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_countries" );
//Custom Query : drop a custom database table "wooconnection_custom_field_groups"..
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_custom_field_groups");
//Custom Query : drop a custom database table "wooconnection_custom_fields"...
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_custom_fields");
//Custom Query : drop a custom database table "wooconnection_standard_custom_field_mapping"...
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_standard_custom_field_mapping");
//Custom Query : drop a custom database table "wooconnection_thankyou_overrides".....
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_thankyou_overrides");
//Custom Query : drop a custom database table "wooconnection_thankyou_override_related_products"...
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_thankyou_override_related_products");
//Custom Query : drop a custom database table "wooconnection_thankyou_override_related_categories"...
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wooconnection_thankyou_override_related_categories");
?>
