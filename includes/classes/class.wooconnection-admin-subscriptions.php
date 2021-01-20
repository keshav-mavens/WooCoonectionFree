<?php 
	//Define the class WC_Subscription_Implementation.....
	class WC_Subscription_Implementation {
		
		public function __construct() {
 			global $woocommerce;
 			//Wordpress hook : This action is triggered to add new tab in product data section....
 			add_action( 'woocommerce_product_write_panel_tabs', [$this, 'wc_custom_tab_head'],10,1 );
 			//Wordpress hook : This action is triggered to add custom option in custom infusionsoft tab....
 			add_action( 'woocommerce_product_data_panels', [$this, 'wc_tab_manager_product_tabs_panel_content'],10,1 );
 			//Wordpress Hook : This action is triggered to add field related to mark product sold by infusionsoft...
			add_action('woocommerce_process_product_meta', [$this,'product_custom_fields_save'],10,1);
			//Wordpress Hook : This action is triggeres to remove all payment methods if custom  coupon applied.....
			add_filter( 'woocommerce_available_payment_gateways', [$this,'remove_custom_payment_gateways'], 10, 2 );
   //      	function do_this_in_an_hour() {
 
			//     // do something
			// }
			// add_action( 'my_new_event','do_this_in_an_hour' );
		}

        //Function Definition : wc_custom_tab_head
        public function wc_custom_tab_head()
	    {
	    	echo '<li class="product_tabs_tab"><a style="cursor:pointer" href="#woocommerce_custom_infusionsoft_tab"><span>Infusionsoft</span></a></li>';
	    }

	    //Function Definition : wc_tab_manager_product_tabs_panel_content..
        public function wc_tab_manager_product_tabs_panel_content() {
	        global $post;
	       	$productId = $post->ID;
	       	$checkSoldAsSub = 'no';
	       	if(isset($productId) && !empty($productId)){
	       		$checkSoldAsSub = get_post_meta($productId,'_product_sold_subscription',true);
	       	}
	       	?>
			<div id="woocommerce_custom_infusionsoft_tab" class="panel wc-metaboxes-wrapper">
	            <div class="woocommerce_product_tabs wc-metaboxes">
	                <p class="form-field _manage_stock_field show_if_simple show_if_variable" style="padding: 5px 20px 5px 162px!important">
	                	<label for="_product_subscription" style="float:left;width:150px;margin:0 0 0 -150px;">Sold as subscription?</label>
	                	<input type="checkbox" class="checkbox" style="" name="_product_subscription" id="_product_subscription" value="<?php echo $checkSoldAsSub ?>" <?php if ($checkSoldAsSub == 'yes') echo "checked='checked'"; ?>> <span class="description">Enable prdouct sold a subscription managed by infusionsoft.</span>
	                </p>
	            </div>
	        </div>
	    <?php 
	    }

	    //Function Definition : product_custom_fields_save..
		public function product_custom_fields_save($postId){
		    if(isset($postId) && !empty($postId)){
			    $soldAsSubscription = 'no';
			    if(isset($_POST['_product_subscription']) && !empty($_POST['_product_subscription'])){
			    	$soldAsSubscription = 'yes';
			    }
			    update_post_meta($postId, '_product_sold_subscription', esc_attr($soldAsSubscription));
		    }
		}
		
		//Function Definition : remove_custom_payment_gateways
		public function remove_custom_payment_gateways($gateWays)
		{
			$checkProductAsSubscription = array();
			//execute the loop on cart items to check wether the product is exist in array which is sold as a subscription managed by infusionsoft.....
			foreach(WC()->cart->get_cart() as $cartItems){
				$product_id = $cartItems['product_id'];
				if(!empty($product_id)){
					$soldAsSubscription = get_post_meta($product_id,'_product_sold_subscription',true);
					//if any product exist with sold as a subscription  then set the id of product in array..
					if($soldAsSubscription == 'yes'){
						$checkProductAsSubscription['productId'] = $product_id;
					}
				}
			}
			//check array is not empty it means product exist in cart which is sold as a subscription managed by infusionsoft....
			if(!empty($checkProductAsSubscription)){
				//execute loop on payment gateways to disable all payment methods except infusionsoft...
				foreach ($gateWays as $key => $value) {
					//check if payment method id is not equal to infusionsoft_keap then need to unset another payment gateways......
					if($key != 'infusionsoft_keap'){
						unset($gateWays[$key]);
					}
				}
			}
			//return all the gateways after set/unset....
			return $gateWays;
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
				global $custom_subscription_implementation;

				// Create instance of our wooconnection class to use off the whole things.
				$custom_subscription_implementation = new WC_Subscription_Implementation();
			}
		}
	}

	wp_schedule_single_event( time() + 3600, 'my_new_event' );
?>