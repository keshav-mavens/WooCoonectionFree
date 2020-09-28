<?php
/**
 * Plugin Name: WooConnection
 * Description: Automatically sync your WooCommerce orders with your Infusionsoft or Keap account.
 * Version: 16
 * Author: Fullstackmarketing.co
 * Author URI: http://www.informationstreet.com
 * Plugin URI: https://www.fullstackmarketing.co
 */

define( 'WOOCONNECTION_VERSION', '16' );//Version Entity
define( 'WOOCONNECTION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );//Directory Path Entity
define( 'WOOCONNECTION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );//Directory Url Entity
class WooConnection {

  	public function __construct() {
		//Call the hook plugin_loaded at the time of plugin initialization..
		add_action("plugins_loaded", array($this, "wooconnection_plugin_initialization"));
        //Call the hook register_activation_hook at the time of plugin activation and create the table in database for campaign goals management..
        register_activation_hook( __FILE__, array($this, 'create_campaign_goals_database_table' ) );
        //Call the hook register_activation_hook to insert records in table..
        register_activation_hook( __FILE__, array($this, 'insert_campaign_goals_database_table' ) );
    }

    
	//Function Definition : wooconnection_plugin_initialization
    public function wooconnection_plugin_initialization(){
    	if (!class_exists('WC_Integration')) {
			add_action('admin_notices', array($this, 'woocommerce_plugin_necessary'));
			return;
		}
        require_once( WOOCONNECTION_PLUGIN_DIR . 'includes/core/wooconnection-entities.php' );
		require_once( WOOCONNECTION_PLUGIN_DIR . 'includes/classes/class.wooconnection-admin.php' );
    }

    //Function Definition : woocommerce_plugin_necessary
    public function woocommerce_plugin_necessary(){
    	$class = 'notice notice-error';
		$message = __( 'WooConnection plugin requires the WooCommerce plugin to be installed and active.', 'error-text-plugin' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }

    //Function Definition : create_campaign_goals_database_table
    public function create_campaign_goals_database_table()
    {
        global $table_prefix, $wpdb;

        $table_name = 'wooconnection_campaign_goals';
        $wp_table_name = $table_prefix . "$table_name";
        //Check Table : First need to check whether the table is exist or not if not exist then create new table with name $wp_table_name..
        if($wpdb->get_var( "show tables like '$wp_table_name'" ) != $wp_table_name) 
        {
            $sql = "CREATE TABLE `". $wp_table_name . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `wc_goal_name`  varchar(255)   NOT NULL, ";
            $sql .= "  `wc_integration_name`  varchar(255)   NOT NULL, ";
            $sql .= "  `wc_call_name`  varchar(255)   NOT NULL, ";
            $sql .= "  `wc_trigger_type`  tinyint(4) DEFAULT 1 COMMENT '1-trigger_type_general,2-trigger_type_cart,3-trigger_type_order', ";
            $sql .= "  `wc_trigger_verison`  tinyint(4) DEFAULT 1 COMMENT '1-trigger_version_free,2-trigger_version_pro', ";
            $sql .= "  `created`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
            $sql .= "  `modified`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
    }


    //Function Definition : insert_campaign_goals_database_table
    public function insert_campaign_goals_database_table()
    {
        global $table_prefix, $wpdb;
        $table_name = 'wooconnection_campaign_goals';
        $wp_table_name = $table_prefix . "$table_name";
        //Check Table Records : First need to check whether the table records is exist or not if not exist then create new table records..
        $checkTableRecords = $wpdb->get_results('SELECT * FROM '.$wp_table_name.'');
        if(empty($checkTableRecords)){
            $wpdb->query("INSERT INTO ".$wp_table_name."
                (`wc_goal_name`,`wc_integration_name`,`wc_call_name`,`wc_trigger_type`,`wc_trigger_verison`)
                VALUES
                ('New User Registration','wooconnection','registered',1,1),
                ('Order Successful','wooconnection','successfulorder',1,1),
                ('Order Failed','wooconnection','failedorder',1,1),
                ('Card Declined','wooconnection','declinedcard',1,1)");
        }
    }
}

// Create global so you can use this variable beyond initial creation.
global $wooconnection;

// Create instance of our wooconnection class to use off the whole things.
$wooconnection = new WooConnection();
?>