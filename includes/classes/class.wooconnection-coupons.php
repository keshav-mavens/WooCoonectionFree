<?php 
	
	//Define the class WC_Subscription_Coupons.....
	class WC_Subscription_Coupons extends WC_Coupon {
		
		/** 
	     * Coupon constructor. Loads coupon data. 
	     * @param mixed $data Coupon data, object, ID or code. 
	     */ 
		public function __construct() {
 			global $woocommerce;
 			//Wordpress hook : This action is triggered to add new discount type in coupons codes admin panel......
 			add_filter( 'woocommerce_coupon_discount_types', [$this, 'create_custom_discount_type'], 10, 1);
        	//Wordpress hook : This action is triggered to add new discount type related fields......
        	add_action( 'woocommerce_coupon_options', [$this, 'add_custom_discount_type_fields'], 10, 2 );
        	//Wordpress hook : This action is triggered to save custom fields related data...........
        	add_action( 'woocommerce_coupon_options_save', [$this, 'save_custom_discount_coupon_fields'], 10, 2 );  
 			//include js coupons admin panel.....
            add_action('admin_enqueue_scripts', array($this,'enqueue_script_coupon_admin'));
            //Wordpress hook : This action is triggered to save custom fields related data...........
            add_action( 'woocommerce_before_calculate_totals', [$this, 'checkout_calculate_total_after_free_trial'], 10, 1 );
        }

 		//Function Definiation : create_custom_discount_type
 		public function create_custom_discount_type($discountTypes){
	        	$discountTypes['custom_subscription_free_trial'] =__( 'Subscription Free Trial', 'woocommerce' );
	            return $discountTypes;
	    }

	    //Function Definiation : add_custom_discount_type_fields
		public function add_custom_discount_type_fields($coupon_get_id, $coupon){
				$trialDuration = get_post_meta($coupon_get_id,'custom_free_coupon_trial_duration',true);
	        	$trialPeriod = get_post_meta($coupon_get_id,'custom_free_coupon_trial_period',true);
	        	?>
	            <p class="form-field custom_free_trial_coupon_field" style="display: none;">
	            	<label for="free_coupon_trial_duration">Free Trial Validity</label>
	            	<input type="number" id="free_coupon_trial_duration" name="free_coupon_trial_duration" class="" value="<?php echo $trialDuration; ?>" />
	            	<select id="free_coupon_trial_period" name="free_coupon_trial_period" class="" style="margin-left:5px; ">
                        <option value="day" <?php if (isset($trialPeriod) && $trialPeriod == 'day') echo ' selected="selected"'; ?>>days</option>
                        <option value="week" <?php if (isset($trialPeriod) && $trialPeriod == 'week') echo ' selected="selected"'; ?>>weeks</option>
                        <option value="month" <?php if (isset($trialPeriod) && $trialPeriod == 'month') echo ' selected="selected"'; ?>>months</option>
                        <option value="year" <?php if (isset($trialPeriod) && $trialPeriod == 'year') echo ' selected="selected"'; ?>>years</option>
                    </select>
	            	<?php echo wc_help_tip("First recurring payment will be charged after the duration mention in field."); ?>
	            </p>
	        	<?php
	    }

	    //Function Definiation : save_custom_discount_coupon_fields
	    public function save_custom_discount_coupon_fields($post_id, $coupon){
	    	update_post_meta( $post_id, 'custom_free_coupon_trial_duration', $_POST['free_coupon_trial_duration'] );
	    	update_post_meta( $post_id, 'custom_free_coupon_trial_period', $_POST['free_coupon_trial_period'] );
	    }

	    //Function Definiation : enqueue_script_coupon_admin
	    public function enqueue_script_coupon_admin(){
    		// deregisters the default WordPress jQuery  
    		wp_deregister_script( 'jquery' );
            //Wooconnection Scripts : Resgister the wooconnection scripts..
            wp_register_script('jquery', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.min.js'),WOOCONNECTION_VERSION, true);
            //Wooconnection Scripts : Enqueue the wooconnection scripts..
            wp_enqueue_script('jquery');

             //Wooconnection Scripts : Enqueue the wooconnection scripts for coupon admin screen........
            wp_enqueue_script( 'coupon-js', WOOCONNECTION_PLUGIN_URL.'assets/js/coupon.js', array('jquery'), WOOCONNECTION_VERSION, true );
	    }

	    //Function Definition : checkout_calculate_total_after_free_trial
	    public function checkout_calculate_total_after_free_trial($cart_object)
	    {   
	        global $woocommerce;
	        //first check coupons applied or not
	        if(!empty($woocommerce->cart->get_applied_coupons())){
	        	//if applied then get the first coupon......
	        	$couponName = $woocommerce->cart->get_applied_coupons()[0];
	        	//get coupon details by coupon name.....
	        	$couponData = new WC_Coupon( $couponName );
	        	//get the coupon id from coupon data......
	        	$couponIds = $couponData->get_id();
	        	//check if coupon id exist then proceed next.....
	        	if(isset($couponIds) && !empty($couponIds)){
	        		//then check coupon type......
	        		$couponDiscountType = $couponData->get_discount_type();
	        		//if coupon type is custom coupon....
	        		if($couponDiscountType == 'custom_subscription_free_trial'){
	        			//get the coupon trial duration.......
	        			$couponTrialDuration = get_post_meta($couponIds,'custom_free_coupon_trial_duration',true);
	        			//get the coupon trial period.......
	        			$couponTrialPeriod = get_post_meta($couponIds,'custom_free_coupon_trial_period',true);
	        			//check if coupon trial duration and trial period exist.....
	        			if(!empty($couponTrialDuration) && !empty($couponTrialPeriod)){
	        				//execute loop to check the product type is a subscription or variable subscription.....
	        				foreach ( $cart_object->get_cart() as $cartItem ){
						        //check product type is subsciption or variable subscription........
						        if ( is_a( $cartItem['data'], 'WC_Product_Subscription' ) || is_a( $cartItem['data'], 'WC_Product_Subscription_Variation' ) ) {
						        	//then update subscription meta data.....
						           	$cartItem['data']->update_meta_data('_subscription_trial_length', $couponTrialDuration);
                            		$cartItem['data']->update_meta_data('_subscription_trial_period', $couponTrialPeriod); 
						        }
						    }
	        			}
	        		}
	        	}
	        }
	    }
	}

	//get the custom payment gateway settings.......
	$settingOptions = get_option('woocommerce_infusionsoft_keap_settings');
	//Get the application type so that application type selected from dropdown.....
	$applicationEdition = applicationType();
	//check settings is exist or not........
	if(isset($settingOptions) && !empty($settingOptions)){
		//then check custom gateway is enabled for payments......
		if($settingOptions['enabled'] == 'yes'){
			//then check subscriptions are enable or not if enable then call the class to give feature of trial subscription coupons......
			if(isset($settingOptions['wc_subscriptions']) && !empty($settingOptions['wc_subscriptions']) && $settingOptions['wc_subscriptions'] == 'yes' && !empty($applicationEdition) && $applicationEdition == APPLICATION_TYPE_INFUSIONSOFT){
				// Create global so you can use this variable beyond initial creation.
				global $custom_subscription_coupons;

				// Create instance of our wooconnection class to use off the whole things.
				$custom_subscription_coupons = new WC_Subscription_Coupons();
			}
		}
	}
?>