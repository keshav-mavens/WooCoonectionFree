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
        //Call the hook wp_enqueue_scripts at the time of front end site loading..
		add_action( 'wp_enqueue_scripts', array($this, 'remove_affiliate_page_menu'));
        //get the referral partner tracking status......
        $checkReferralTrackingStatus = get_option('referral_partner_tracking_status',true);
        //check if referral partner tracking in enable....
        if($checkReferralTrackingStatus == 'On'){
            //Call the hook template_redirect to control the affiliate redirection process..
            add_action('template_redirect', array($this, 'affiliate_redirection_control'));
            //Call the hook init to control the referral partner process....
            add_action('init', array($this, 'wooconnection_referral_partner_handling'));
        }
    }


    //Function Definition : wooconnection_include_frontend_files for front end functionality like custom fields, dynamic thank you page..
    public function wooconnection_include_frontend_files(){
    	//Trigger Files : Include the register triggers file...
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/generalTriggers/wooconnection-general-register-triggers.php');
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/generalTriggers/wooconnection-general-order-triggers.php');
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/dynamicCheckoutFields/wooconnection-dynamic-checkout-custom-fields.php');
		require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/thankyouoverrides/wooconnection-thank-you-overrides.php');
		require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/userCartTriggers/wooconnection-login-usercheckout-triggers.php');
    	require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/modules/userCartTriggers/wooconnection-login-usercart-triggers.php');
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

    //Function Definition : remove_affiliate_page_menu this function is basically used to hide the affiliate page menu from front end.....
    public function remove_affiliate_page_menu() {
        $affiliate_redirect_page_id = get_option('affiliate_redirect_page_id');
        if(isset($affiliate_redirect_page_id) && !empty($affiliate_redirect_page_id)){
            $affiliate_menu_class = "page-item-".$affiliate_redirect_page_id;
            ?>
                <script type="text/javascript">
                    window.onload = function() {
                        var affiliate_class_name = '<?php echo $affiliate_menu_class; ?>';
                        if(affiliate_class_name != ""){
                            document.getElementsByClassName(affiliate_class_name)[0].style.visibility='hidden';
                        }   
                    }
                </script>
            <?php
        }
    }

    //Function Definition : affiliate_redirection_control this function is basically used to control the affiliate redirection process....
    public function affiliate_redirection_control() {
        $affiliate_redirect_page_id = get_option('affiliate_redirect_page_id');
        if(isset($affiliate_redirect_page_id) && !empty($affiliate_redirect_page_id)){
            if(is_page($affiliate_redirect_page_id)){
                //get the authenticate application details first.....
                $authenticateAppdetails = getAuthenticationDetails();
                //define empty variables.....
                $authenticate_application_name = "";
                //check authenticate details....
                if(isset($authenticateAppdetails) && !empty($authenticateAppdetails)){
                    //check authenticate  name is exist......
                    if(isset($authenticateAppdetails[0]->user_authorize_application)){
                        $authenticate_application_name = $authenticateAppdetails[0]->user_authorize_application;
                    }   
                }

                //check application name is exist then proceed next....
                if(!empty($authenticate_application_name)){
                    ?>
                    <script type="text/javascript">
                        var authenticate_application_name = '<?php echo $authenticate_application_name; ?>';
                        var myCrm = "https://"+authenticate_application_name+".infusionsoft.com";
                     
                        var code = getQueryVariable("w") + "/";
                        var affiliate = getQueryVariable("p") + "/";
                        var ad = getQueryVariable("a");
                     
                        if (ad == "not found") {
                            ad = "";
                        } else {
                            ad = ad + "/";
                        }
                     
                        window.location = myCrm + "/go/" + code + affiliate + ad;
                     
                        function getQueryVariable(variable) {
                            var query = window.location.search.substring(1);
                            var vars = query.split("&");
                            var returnVal;
                            var found = "false";
                     
                            for (var i = 0; i < vars.length; i++) {
                                var pair = vars[i].split("=");
                                if (pair[0] == variable) {
                                    returnVal = pair[1];
                                    found = "true";
                                    break;
                                }
                            }
                            if (found == "false") {
                                returnVal = "not found";
                            }
                     
                            return returnVal;
                        }
                     
                    </script>
                    <?php
                } 
            }
        }
    }

    //Function Definition : wooconnection_leadsource_handling is used to handle the referral partner funtionality.....
    public function wooconnection_referral_partner_handling(){
        $affiliate_redirect_page_id = get_option('affiliate_redirect_page_id');
        if(isset($affiliate_redirect_page_id) && !empty($affiliate_redirect_page_id)){
            //first check affiliate id is exit in query string.....
            if(!empty($_GET['aff'])) {
                $affiliateId = $_GET['aff'];//get or set the affiliate id....
            }else if(!empty($_GET['affiliate'])){
                $affiliateId = $_GET['affiliate'];//get or set the affiliate id....
            }
            //set cookie....
            if (!headers_sent()) {
                if(!empty($affiliateId)){
                    $cookieName = "affiliateId";
                    $cookieValue = $affiliateId;
                    setcookie($cookieName, $cookieValue, time() + 3600, "/", $_SERVER['SERVER_NAME']);
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