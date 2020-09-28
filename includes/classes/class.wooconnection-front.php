<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WooConnection_Front {
	/**
	 * @var WooConnection_Front
	 */
	public function __construct() {
		//Call the hook init at the time of plugin initialization..
		add_action('init', array($this, 'wooconnection_include_frontend_files'));
	}


    //Function Definition : wooconnection_include_frontend_files for front end functionality like custom fields, dynamic thank you page..
    public function wooconnection_include_frontend_files(){
    	//Trigger Files : Include the register triggers file...
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/generalTriggers/wooconnection-general-register-triggers.php');
    }
}
	
// Create global so you can use this variable beyond initial creation.
global $wooconnectionfront;

// Create instance of our wooconnection front class to use off the whole things.
$wooconnectionfront = new WooConnection_Front();
?>