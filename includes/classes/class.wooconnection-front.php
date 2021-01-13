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
		add_action('init', array($this, 'wooconnection_leadsource_handling'));
	}


    //Function Definition : wooconnection_include_frontend_files for front end functionality like custom fields, dynamic thank you page..
    public function wooconnection_include_frontend_files(){
    	//Trigger Files : Include the register triggers file...
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/generalTriggers/wooconnection-general-register-triggers.php');
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/generalTriggers/wooconnection-general-order-triggers.php');
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/dynamicCheckoutFields/wooconnection-dynamic-checkout-custom-fields.php');
		require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/thankyouoverrides/wooconnection-thank-you-overrides.php');
	}

    //Function Definition : wooconnection_leadsource_handling is used to handle the leadsource funtionality.....
    public function wooconnection_leadsource_handling(){
    	$authenticateAppdetails = getAuthenticationDetails();
        //define empty variables.....
        $authenticate_application_edition = "";
        //check authenticate details....
        if(isset($authenticateAppdetails) && !empty($authenticateAppdetails)){
            //check authenticate edition is exist......
            if(isset($authenticateAppdetails[0]->user_application_edition)){
                $authenticate_application_edition = $authenticateAppdetails[0]->user_application_edition;
            }   
        }

        //check authenticate application edition is infusionsoft, if yes then proceed next to process the lead source feature....
    	if($authenticate_application_edition == APPLICATION_TYPE_INFUSIONSOFT){
    		//first check lead source id exit in query string.....
	    	if(!empty($_GET['ls'])) {
				$lsId = $_GET['ls'];//get or set the lead source id....
				//set cookie....
				if (!headers_sent()) {
					$cookieName = "leadsourceId";
					$cookieValue = $lsId;
					setcookie($cookieName, $cookieValue, time() + 3600, "/", $_SERVER['SERVER_NAME']);
				}
			}else{//then check if utm parameters exist in query string...
				//define empty variables....
				$lscategory = '';
				$lsmedium ='';
				$lsvendor = '';
				$lsmessage = '';

				//check "utm_source" exist in query string.....
				if(!empty($_GET['utm_source'])) {
					$lscategory = $_GET['utm_source'];				
				}else if (!empty($_COOKIE['lscategory'])) {
					$lscategory = $_COOKIE['lscategory'];
				}

				//check "utm_medium" exist in query string.....
				if(!empty($_GET['utm_medium'])) {
					$lsmedium = $_GET['utm_medium'];
				}else if (!empty($_COOKIE['lsmedium'])) {
					$lsmedium = $_COOKIE['lsmedium'];
				}

				//check "utm_campaign" exist in query string.....
				if(!empty($_GET['utm_campaign'])) {
					$lsvendor = $_GET['utm_campaign'];
				}else if (!empty($_COOKIE['lsvendor'])) {
					$lsvendor = $_COOKIE['lsvendor'];
				}

				//check "utm_content" exist in query string.....
				if(!empty($_GET['utm_content'])) {
					$lsmessage = $_GET['utm_content'];
				}else if (!empty($_COOKIE['lsmessage'])) {
					$lsmessage = $_COOKIE['lsmessage'];
				}

				//set utm parameters in cookie.....
				if (!headers_sent()) {
					setcookie('lscategory', $lscategory, time() + 3600, "/", $_SERVER['SERVER_NAME']); 
					setcookie('lsmedium', $lsmedium, time() + 3600, "/", $_SERVER['SERVER_NAME']); 
					setcookie('lsvendor', $lsvendor, time() + 3600, "/", $_SERVER['SERVER_NAME']); 
					setcookie('lsmessage', $lsmessage, time() + 3600, "/", $_SERVER['SERVER_NAME']); 
				}
			}
    	}
    }
}
	
// Create global so you can use this variable beyond initial creation.
global $wooconnectionfront;

// Create instance of our wooconnection front class to use off the whole things.
$wooconnectionfront = new WooConnection_Front();
?>