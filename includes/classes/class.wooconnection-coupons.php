<?php
//If file file accessed directly then exit;
if(!defined('ABSPATH')){
	exit;
}
/**
* create new class "WC_Subscription_Referral_Coupons" to add/modify the coupon functionality..... 
*/
class WC_Subscription_Referral_Coupons extends WC_Coupon
{
	public function __construct(){
	 	//Define the woocommerce gloabal varaiable .....
	 	global $woocommerce;
	 	//Wordpress Hook : This action is trigger to add new fields so user should be able to store 
	 	add_action('woocommerce_coupon_options',[$this,'add_referral_partner_related_custom_fields'],10,2);	
		//include js coupons admin panel.....
		add_action('admin_enqueue_scripts',[$this,'enqueue_script_coupon_admin'],10,2);
		//Wordpress Hook : This action is trigger to save the referral partner related fields data.....
		add_action('woocommerce_coupon_options_save',[$this,'save_referral_partner_fields_data'],10,2);
		//Wordpress Hook : This action is trigger to set the affiliate id in contact if exist with coupon code....
        add_action('woocommerce_before_calculate_totals',[$this,'checkout_set_affiliate'],10,2);
        //check if affiliate id exist in cookie then needs to call the hook "woocommerce_removed_coupon" it.....
      	if(isset($_COOKIE['affiliateId'])){
    		//Wordpress Hook : This action is trigger to unset the affiliate id in contact if exist with coupon code....
        	add_action('woocommerce_removed_coupon',[$this,'checkout_unset_affiliate'],10,2);  	
      	}
    }

	//Function Definition : add_referral_partner_related_custom_fields 
	public function add_referral_partner_related_custom_fields($current_coupon_id, $coupon){
		//get post meta whether a associate with affiliate is enable or not......
		$currentCouponAssAffiliate = get_post_meta($current_coupon_id,'coupon_assoicate_with_affiliate',true);
		//define empty variable....
		$associateEnable = '';
		//check associate with affiliate is enable or not........
		if(isset($currentCouponAssAffiliate) && !empty($currentCouponAssAffiliate)){
			$associateEnable = $currentCouponAssAffiliate;
		}
		//create the checkbox.....
		woocommerce_wp_checkbox(
				array('id'=>'linked_with_affiliate',
					'label'=>__('Associate Contact With Affiliate','woocommerce'),
					'description'=>sprintf( __('Check this box if contact associate with affiliate after applying this coupon.', 'woocommerce')),
				    'value'=>$associateEnable)
				);
		//create the input field to get the referral partner id from the user.....
		woocommerce_wp_text_input(
		  		array('id'=>'referral_partner_id','label'=>__('Referral Partner Id','woocommerce'),'class' => 'referral_partner','desc_tip'=>'','description'=>sprintf( __('Enter the valid referral partner id here to associate with contact.Only Numeric value is allowed in this field.', 'woocommerce'))
		  			)
		  		);
	}

	//Function Definition : enqueue_script_coupon_admin
	public function enqueue_script_coupon_admin(){
		// de-registers/remove the default WordPress jQuery
		wp_deregister_script('jquery');
		//Wooconnection Scripts : Resgister the wooconnection scripts..
        wp_register_script('jquery', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.min.js'),WOOCONNECTION_VERSION, true);
        //Wooconnection Scripts : Enqueue the wooconnection scripts..
        wp_enqueue_script('jquery'); 
		
		//Wooconnection Scripts : Enqueue the wooconnection script for coupons admin screen...
        wp_enqueue_script('coupon-js',(WOOCONNECTION_PLUGIN_URL.'assets/js/coupon.js'),WOOCONNECTION_VERSION,true);
	}

	//Function Definition : save_referral_partner_fields_data
	public function save_referral_partner_fields_data($post_id,$coupon){
		//update post meta of contact is associated with contact or not.....
		update_post_meta($post_id,'coupon_assoicate_with_affiliate',$_POST['linked_with_affiliate']);
		
		//check referral partner id value is exist in post data or not....
		if(isset($_POST['referral_partner_id']) && !empty($_POST['referral_partner_id'])){
			//if exist then update post meta "referral_partner_id" by its value.....
			update_post_meta($post_id,'referral_partner_id',$_POST['referral_partner_id']);
		}
		
	}

	//Function Definition : checkout_set_affiliate
	public function checkout_set_affiliate($cart_object){
		//Define the woocommerce global variable to access the cart objects......
		global $woocommerce;
		//first check coupons applied or not in cart or not....
		if(!empty($woocommerce->cart->get_applied_coupons())){
			//set coupons codes in variable......
			$appliedCartCoupons = $woocommerce->cart->get_applied_coupons();
			//execute loop on coupons to get the coupon details.....
			foreach ($appliedCartCoupons as $key => $value) {
				//get or set coupon name.....
				$appliedCouponName = $value;
				//check coupon name is exist or ont....
				if(isset($appliedCouponName) && !empty($appliedCouponName)){
					//get the coupon details by coupon name....
					$appliedCouponData = new WC_Coupon($appliedCouponName);
					//check coupon data is exist or not....
					if(isset($appliedCouponData) && !empty($appliedCouponData)){
						//get the coupon id from coupon data......
						$appliedCouponId = $appliedCouponData->get_id();
						//check coupon id is exist or not....
						if(isset($appliedCouponId) && !empty($appliedCouponId)){
							//get the coupon is associated with referral partner..........
		        			$enableReferralInAppliedCoupon = get_post_meta($appliedCouponId,'coupon_assoicate_with_affiliate',true);
		        			//get the referral partner id.......
		        			$associatedRefIdWithAppliedCoupon = get_post_meta($couponIds,'referral_partner_id',true);
							if(!empty($enableReferralInAppliedCoupon) && !empty($associatedRefIdWithAppliedCoupon) && !headers_sent()){
								$cookieName = "affiliateId";
			                    $cookieValue = $associatedRefId;
			                    //set cookie with name......
			                    setcookie($cookieName, $cookieValue, time() + 3600, "/", $_SERVER['SERVER_NAME']);
			                    break;
							}
						}	
					}
				}
			}
		}
	}

	//Function Definition : checkout_unset_affiliate
	public function checkout_unset_affiliate( $remove_coupon_code ) { 
	    //first check the remove coupon code is exist and not empty.....
	    if(isset($remove_coupon_code) && !empty($remove_coupon_code)){
	    	//get the remove coupon details by coupon name....
			$removeCouponData = new WC_Coupon($remove_coupon_code);
			//get the remove coupon id from coupon data......
			$removeCouponId = $removeCouponData->get_id();
	    	//get the remove coupon is associated with referral partner..........
			$enableReferralInRemoveCoupon = get_post_meta($removeCouponId,'coupon_assoicate_with_affiliate',true);
			//get the remove coupon referral partner id.......
			$associatedRefIdWithRemoveCoupon = get_post_meta($removeCouponId,'referral_partner_id',true);
	    	//check enable with referral and value of referral partner id is exist and equal to cookie value.....
	    	if(!empty($enableReferralInRemoveCoupon) && !empty($associatedRefIdWithRemoveCoupon) && $associatedRefIdWithRemoveCoupon == $_COOKIE['affiliateId']){
	    		//then empty the affiiate cookie value....
	    		setcookie( 'affiliateId', '', time() - 999999, '/', $_SERVER['SERVER_NAME'] );
	    	}
	    }
	}
}

//Get the application type/edition first, 
$applicationEdition = applicationType();
//check applicaiton edition exist and equal to infusionsoft then proceed next or call the class...
if(isset($applicationEdition) && !empty($applicationEdition) && $applicationEdition == APPLICATION_TYPE_INFUSIONSOFT){
	// Create global so you can use this variable beyond initial creation.
	global $custom_subscription_referral_coupons;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_subscription_referral_coupons = new WC_Subscription_Referral_Coupons();
}
?>