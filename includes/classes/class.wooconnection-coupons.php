<?php 
	
	//Define the class WC_Subscription_Coupons.....
	class WC_Subscription_Coupons extends WC_Coupon {
		
		private static $removed_coupons_array = array();

		/** 
	     * Coupon constructor. Loads coupon data. 
	     * @param mixed $data Coupon data, object, ID or code. 
	     */ 
		public function __construct() {
 			global $woocommerce;
 			$this->subscription_avail = 'no';
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
							$this->subscription_avail = 'yes';
				 			//Wordpress hook : This action is triggered to add new discount type in coupons codes admin panel......
				 			add_filter( 'woocommerce_coupon_discount_types', [$this, 'create_custom_discount_type'], 10, 1);
				        	//Wordpress hook : This action is triggered to add new discount type related fields......
				        	add_action( 'woocommerce_coupon_options', [$this, 'add_custom_discount_type_fields'], 10, 2 );
				        	//Wordpress hook : This action is triggered to save custom fields related data...........
				        	add_action( 'woocommerce_coupon_options_save', [$this, 'save_custom_discount_coupon_fields'], 10, 2 );  
				 			//include js coupons admin panel.....
				            add_action('admin_enqueue_scripts', array($this,'enqueue_script_coupon_admin'));
				        }
				    }
				}   
            add_filter("woocommerce_coupon_is_valid",[$this,"custom_subscription_coupon_validation"],15,2);
            //Wordpress hook : This action is triggered to save custom fields related data...........
            add_action( 'woocommerce_before_calculate_totals', [$this, 'checkout_calculate_total_after_free_trial'], 10, 1 );
            //Wordpress Hook : This filter is used to validate the custom coupons of custom discount type....
            add_filter('woocommerce_subscriptions_validate_coupon_type',[$this,'implement_custom_coupon_type_validation'],10,3);
            //Wordpress Hook : This action is trigger to add coupon discount for recurring section...
            add_action('woocommerce_before_calculate_totals',[$this,'remove_coupons_custom_discount_type'],10,1);
           	//Worpress Hook : This action is trigeer to validate the custom coupon.....
           	add_filter('woocommerce_coupon_is_valid_for_product',[$this,'validate_custom_subscription_coupons'],10,4);
           	//Wordpress Hook : This action is trigger to get the discount amount on the basis of discount type....
           	add_filter('woocommerce_coupon_get_discount_amount',[$this,'subscription_coupon_get_discount_amount'],10,5);
        }

 		//Function Definiation : create_custom_discount_type
 		public function create_custom_discount_type($discountTypes){
	        	$discountTypes['custom_subscription_managed'] =__( 'Managed Subscriptions', 'woocommerce' );
	            return $discountTypes;
	    }

	    //Function Definiation : add_custom_discount_type_fields
		public function add_custom_discount_type_fields($coupon_get_id, $coupon){
				$trialDuration = get_post_meta($coupon_get_id,'custom_free_coupon_trial_duration',true);//get the free trial duration......
	        	$trialPeriod = get_post_meta($coupon_get_id,'custom_free_coupon_trial_period',true);//get the free trial period.....
	        	$subDiscountDuration = get_post_meta($coupon_get_id,'subscription_discount_duration',true);//get the subscription discount duration....
	        	$subDiscountPeriod = get_post_meta($coupon_get_id,'subscription_discount_period',true);//get the subscription discount period....	
	        	$subDiscountType = get_post_meta($coupon_get_id,'subscription_discount_type',true);//get the subscription discount type i.e fixed amount or discount in percent.....
	        	$subDiscountAmount = get_post_meta($coupon_get_id,'coupon_amount',true);//get the discount amount.....
	        	//check and set the default discount amount......
	        	if(isset($subDiscountAmount) && !empty($subDiscountAmount)){
	        		$subDiscountAmount = $subDiscountAmount;
	        	}else{
	        		$subDiscountAmount = 0;
	        	}
	        	?>
	            <p class="form-field custom_free_trial_coupon_field" style="display: none;">
	            	<label for="free_coupon_trial_duration">Free Trial Period</label>
	            	<input type="number" id="free_coupon_trial_duration" name="free_coupon_trial_duration" class="" value="<?php echo $trialDuration; ?>" />
	            	<select id="free_coupon_trial_period" name="free_coupon_trial_period" class="" style="margin-left:5px; ">
                        <option value="<?php echo DURATION_TYPE_DAY ?>" <?php if (isset($trialPeriod) && $trialPeriod == DURATION_TYPE_DAY) echo ' selected="selected"'; ?>>days</option>
                        <option value="<?php echo DURATION_TYPE_WEEK ?>" <?php if (isset($trialPeriod) && $trialPeriod == DURATION_TYPE_WEEK) echo ' selected="selected"'; ?>>weeks</option>
                        <option value="<?php echo DURATION_TYPE_MONTH ?>" <?php if (isset($trialPeriod) && $trialPeriod == DURATION_TYPE_MONTH) echo ' selected="selected"'; ?>>months</option>
                        <option value="<?php echo DURATION_TYPE_YEAR ?>" <?php if (isset($trialPeriod) && $trialPeriod == DURATION_TYPE_YEAR) echo ' selected="selected"'; ?>>years</option>
                    </select>
	            	<?php echo wc_help_tip("First recurring payment will be charged after the duration mention in field."); ?>
	            </p>
	        	<?php
	        	//add dropdown to select the subscription discount type whether it is in fixed amount or percent....
				woocommerce_wp_select(
					array('id'=>'sub_discount_type',
						  'label'=>__('Discount Amount Type','woocommerce'),
						  'options'=>array(SUBSCRIPTION_DISCOUNT_TYPE_AMOUNT => __('Subscription Amount Discount','woocommerce'),
						  					SUBSCRIPTION_DISCOUNT_TYPE_PERCENT=>__('Subscription % Discount')),
						  'description'=>__('Select discount amount type whether its in percent or static amount.','woocommerce'),
						  'desc_tip'=>'true',
						  'value'=>$subDiscountType)
					);

				//add input field to input the discount amount.....
				woocommerce_wp_text_input(
					array('id'=>'sub_discount_amount',
					  'label'=>__('Discount Amount','woocommerce'),
					  'description'=>__('Amount of the discount.','woocommerce'),
					  'desc_tip'=>'true',
					  'value'=>$subDiscountAmount)
					);

				?>
					<p class="form-field subscription_discount_validity_field">
						<label for="sub_discount_duration">Discount Time Period</label>
						<input type="text" name="sub_discount_duration" id="sub_discount_duration" class="" value="<?php echo $subDiscountDuration; ?>">
						<select id="sub_discount_period" name="sub_discount_period" class="" style="margin-left: 5px;">
							<option value="<?php echo DURATION_TYPE_DAY ?>" <?php if (isset($subDiscountPeriod) && $subDiscountPeriod == DURATION_TYPE_DAY) echo 'selected="selected"'; ?>>days</option>
							<option value="<?php echo DURATION_TYPE_WEEK ?>" <?php if (isset($subDiscountPeriod) && $subDiscountPeriod == DURATION_TYPE_WEEK) echo 'selected="selected"'; ?>>weeks</option>
							<option value="<?php echo DURATION_TYPE_MONTH ?>" <?php if (isset($subDiscountPeriod) && $subDiscountPeriod == DURATION_TYPE_MONTH) echo 'selected="selected"'; ?>>months</option>
							<option value="<?php echo DURATION_TYPE_YEAR ?>" <?php if (isset($subDiscountPeriod) && $subDiscountPeriod == DURATION_TYPE_YEAR) echo 'selected="selected"'; ?>>years</option>
						</select>
						<?php echo wc_help_tip("Discount will be available till duration mention in this field.") ?>
					</p>
				<?php
		}

	    //Function Definiation : save_custom_discount_coupon_fields
	    public function save_custom_discount_coupon_fields($coupon_post_id, $coupon){
	    	//get the discount type.....
	    	$disType = $coupon->get_discount_type();
	    	//then check discount type is "custom_subscription_managed"
	    	if($disType == 'custom_subscription_managed'){
	    		//update the custom coupon type information by using the function "update_post_meta" with post id...
		    	if(isset($_POST['free_coupon_trial_duration'])){update_post_meta( $coupon_post_id, 'custom_free_coupon_trial_duration', $_POST['free_coupon_trial_duration'] );}
		    	if(isset($_POST['free_coupon_trial_period'])){update_post_meta( $coupon_post_id, 'custom_free_coupon_trial_period', $_POST['free_coupon_trial_period'] );}
		    	if(isset($_POST['sub_discount_type'])){update_post_meta($coupon_post_id,'subscription_discount_type',$_POST['sub_discount_type']);}
		    	if(isset($_POST['sub_discount_amount'])){update_post_meta($coupon_post_id,'coupon_amount',$_POST['sub_discount_amount']);}
		    	if(isset($_POST['sub_discount_duration'])){update_post_meta($coupon_post_id,'subscription_discount_duration',$_POST['sub_discount_duration']);}
		    	if(isset($_POST['sub_discount_period'])){update_post_meta($coupon_post_id,'subscription_discount_period',$_POST['sub_discount_period']);}
	    	}
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
	        		if($couponDiscountType == 'custom_subscription_managed'){
	        			//get the coupon trial duration.......
	        			$couponTrialDuration = get_post_meta($couponIds,'custom_free_coupon_trial_duration',true);
	        			//get the coupon trial period.......
	        			$couponTrialPeriod = get_post_meta($couponIds,'custom_free_coupon_trial_period',true);
	        			if($couponTrialPeriod == DURATION_TYPE_DAY){
	        				$period = 'day';
	        			}else if ($couponTrialPeriod == DURATION_TYPE_WEEK) {
	        				$period = 'week';
	        			}else if ($couponTrialPeriod == DURATION_TYPE_MONTH) {
	        				$period = 'month';
	        			}else if ($couponTrialPeriod == DURATION_TYPE_YEAR) {
	        				$period = 'year';
	        			}
	        			//check if coupon trial duration and trial period exist.....
	        			if(!empty($couponTrialDuration) && !empty($couponTrialPeriod)){
	        				//execute loop to check the product type is a subscription or variable subscription.....
	        				foreach ( $cart_object->get_cart() as $cartItem ){
						        //check product type is subsciption or variable subscription........
						        if ( is_a( $cartItem['data'], 'WC_Product_Subscription' ) || is_a( $cartItem['data'], 'WC_Product_Subscription_Variation' ) ) {
						        	//then update subscription meta data.....
						           	$cartItem['data']->update_meta_data('_subscription_trial_length', $couponTrialDuration,true);
                            		$cartItem['data']->update_meta_data('_subscription_trial_period', $period,true);
                  				}
						    }
	        			}
	        		}
	        	}
	        }
	    }

	    //Function Definition : implement_custom_coupon_type_validation
	    public function implement_custom_coupon_type_validation($firstArg,$coupondata,$validate){
	    	//first check coupon type is "custom_subscription_managed" then proceed next....
	    	if($coupondata->is_type('custom_subscription_managed')){
	    		//get the coupon trial duration.....
	    		$sub_trial_coupon_duration = $coupondata->get_meta('custom_free_coupon_trial_duration');
	    		//get the coupon trial period.....
	    		$sub_trial_coupon_period = $coupondata->get_meta('custom_free_coupon_trial_period');
	    		//if both values of coupon duration and coupon code is exist it means coupon is validated and stop the process to validate by woocommercer subscription addone.....
	    		if(!empty($sub_trial_coupon_duration) && !empty($sub_trial_coupon_period)){
	    			return false;
	    		}
	    	}
	    	//else return true.....
	    	return true;
	    }

	    //Function Definition : custom_subscription_coupon_validation.....
	    public function custom_subscription_coupon_validation($isvalid,$coupon){
	    	//check if coupon discount type is custom......
	    	if($coupon->get_discount_type() === 'custom_subscription_managed'){
	    		//then check 
	    		if($this->subscription_avail == 'no'){
	    			return false;
	    		}
	    	}
	    	return $isvalid;
	    }

	    //Function Definition : remove_coupons_custom_discount_type(this function is used to trigger the )
	    public function remove_coupons_custom_discount_type($cartData){
	    	//Remove original coupon triggers of wc subsction coupon class.....
	    	remove_action('woocommerce_before_calculate_totals','WC_Subscriptions_Coupon::remove_coupons',10);

	    	//get the calculation type from woocommerce subscription cart.....
	    	$payment_calculation_type = WC_Subscriptions_Cart::get_calculation_type();

	    	//first check calculation is none or cart does not contain subscription product or page is checkout,cart etc....
	    	if(! WC_Subscriptions_Cart::cart_contains_subscription() || $payment_calculation_type == 'none' || (!is_checkout() && !is_cart() && !defined('WOOCOMMERCE_CART') && !defined('WOOCOMMERCE_CHECKOUT')) ){
	    		return;
	    	}
	    	
	    	//get the list of applied coupons.....
	    	$cartDataCoupons = $cartData->get_applied_coupons();
	    	
	    	//check applied coupons exist or not......
	    	if(isset($cartDataCoupons) && !empty($cartDataCoupons)){
	    		//intialize the empty array to applied some coupon directly......
	    		$reapplied_coupons_array = array();

	    		//execute loop on applied coupons.....
	    		foreach ($cartDataCoupons as $couponCode) {
	    			//get the coupon details by coupon code.....
	    			$couponData = new WC_Coupon($couponCode);
	    			//get the coupon discount type.....
	    			$couponDiscountType =  wcs_get_coupon_property($couponData,'discount_type');
	    			//set the value in array on the basis of condition....
	    			if(in_array($couponDiscountType, array('recurring_fee','recurring_percent','custom_subscription_managed'))){
	    				if($payment_calculation_type == 'recurring_total'){
	    					$reapplied_coupons_array[] = $couponCode;
	    				}else if($payment_calculation_type == 'none'){
	    					$reapplied_coupons_array[] = $couponCode; 
	    				}else{
	    					self::$removed_coupons_array[] = $couponCode;
	    				}
	    			}else if(($payment_calculation_type == 'none') && !in_array($couponDiscountType, array('recurring_fee','recurring_percent'))){
	    				$reapplied_coupons_array[] = $couponCode;
	    			}else{
	    				self::$removed_coupons_array[] = $couponCode;
	    			}
	    		}

	    		//hit the default function to remove coupons......
	    		$cartData->remove_coupons();

	    		//set the applied coupon......
                $cartData->applied_coupons = $reapplied_coupons_array;
                
               	if ( isset( $cartData->coupons ) ) {
                    $cartData->coupons = $cartData->get_coupons();
                }
	    	}
	    }	
	    
	    //Function Definition : validate_custom_subscription_coupons....
	    public function validate_custom_subscription_coupons($couponValid,$product,$couponData,$values){
	    	//get the coupon discount type....
	    	$couponDiscountType = wcs_get_coupon_property($couponData,'discount_type');
	    	//check if coupon discount type is not equal to 'custom_subscription_managed'.....
	    	if($couponDiscountType != 'custom_subscription_managed'){
	    		return $couponValid;
	    	}

	    	$product_cats = wp_get_post_terms( $product->id, 'product_cat', array( "fields" => "ids" ) );
    
		    // SPECIFIC PRODUCTS ARE DISCOUNTED
		    if ( sizeof( $couponData->product_ids ) > 0 ) {
		        if ( in_array( $product->id, $couponData->product_ids ) || ( isset( $product->variation_id ) && in_array( $product->variation_id, $couponData->product_ids ) ) || in_array( $product->get_parent(), $couponData->product_ids ) ) {
		            $couponValid = true;
		        }
		    }

		    // CATEGORY DISCOUNTS
		    if ( sizeof( $couponData->product_categories ) > 0 ) {
		        if ( sizeof( array_intersect( $product_cats, $couponData->product_categories ) ) > 0 ) {
		            $couponValid = true;
		        }
		    }

		    // IF ALL ITEMS ARE DISCOUNTED
		    if ( ! sizeof( $couponData->product_ids ) && ! sizeof( $couponData->product_categories ) ) {            
		        $couponValid = true;
		    }
		    
		    // SPECIFIC PRODUCT IDs EXLCUDED FROM DISCOUNT
		    if ( sizeof( $couponData->exclude_product_ids ) > 0 ) {
		        if ( in_array( $product->id, $couponData->exclude_product_ids ) || ( isset( $product->variation_id ) && in_array( $product->variation_id, $couponData->exclude_product_ids ) ) || in_array( $product->get_parent(), $couponData->exclude_product_ids ) ) {
		            $couponValid = false;
		        }
		    }
		    
		    // SPECIFIC CATEGORIES EXLCUDED FROM THE DISCOUNT
		    if ( sizeof( $couponData->exclude_product_categories ) > 0 ) {
		        if ( sizeof( array_intersect( $product_cats, $couponData->exclude_product_categories ) ) > 0 ) {
		            $couponValid = false;
		        }
		    }

		    // SALE ITEMS EXCLUDED FROM DISCOUNT
		    if ( $couponData->exclude_sale_items == 'yes' ) {
		        $product_ids_on_sale = wc_get_product_ids_on_sale();

		        if ( isset( $product->variation_id ) ) {
		            if ( in_array( $product->variation_id, $product_ids_on_sale, true ) ) {
		                $couponValid = false;
		            }
		        } elseif ( in_array( $product->id, $product_ids_on_sale, true ) ) {
		            $couponValid = false;
		        }
		    }

		    //return $valid;


	    	//set coupon is valid......
	    	$couponValid = true;
		    return $couponValid;//return....
	    }
	    
	    //Function Definition : subscription_coupon_get_discount_amount.....
    	public function subscription_coupon_get_discount_amount($discountData,$discountingAmount,$cartItem,$singleitem,$couponDetails){
    		//get the coupon discount type.....
    		$couponDisType = wcs_get_coupon_property($couponDetails,'discount_type');
    		//get the coupon id
    		$couponId = wcs_get_coupon_property($couponDetails,'id');
    		//then check whether the coupon discount type is 'custom_subscription_managed'.....
    		if($couponDisType == 'custom_subscription_managed'){
    			//then check the coupon id.....
    			if(!empty($couponId)){
    				//get the subscription coupon discount type whether is in percent or fixed amount....
    				$subCouponDisType = SUBSCRIPTION_DISCOUNT_TYPE_PERCENT;
    				//get_post_meta($couponId,'sub_discount_type',true)
    				//check if subscription amount in fixed amount..
    				if($subCouponDisType == SUBSCRIPTION_DISCOUNT_TYPE_AMOUNT){
    					$discountData = min($couponDetails->get_amount(),$discountingAmount);
    					//check product is single or not....
    					if($singleitem){
    						$discountData = $discountData;
    					}else{
    						//get the quantity....
    						$itemsQuantity = $cartItem->get_quantity();
    						$discountData = $discountData * $itemsQuantity;
    					}
    				}
    				//another case discount amount is in percent....
    				else{
    					$discountData  = (float) $couponDetails->get_amount()*($discountingAmount/100);
    				}
    			}	
    		}else{
    			$discountData = $discountData;
    		}
    		//return discount amount......
    		return  $discountData;
    	}
	}

	// Create global so you can use this variable beyond initial creation.
	global $custom_subscription_coupons;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_subscription_coupons = new WC_Subscription_Coupons();
?>