<?php
	//Define the class WC_Subscription_Implementation.....
	class WC_Subscription_Implementation {
		
		public function __construct() {
 			global $woocommerce;
 			//Wordpress hook : This action is triggered to add new tab in product data section....
 			add_action( 'woocommerce_product_write_panel_tabs', [$this, 'wc_custom_tab_head'],10,1 );
 			//Wordpress hook : This action is triggered to add custom option in custom infusionsoft tab....
 			add_action( 'woocommerce_product_data_panels', [$this, 'wc_tab_manager_product_tabs_panel_content'],10,1 );
        }

        public function wc_custom_tab_head()
	    {
	    	echo '<li class="product_tabs_tab"><a style="cursor:pointer" href="#woocommerce_custom_infusionsoft_tab">Infusionsoft tab</a></li>';
	    }

        public function wc_tab_manager_product_tabs_panel_content() {
	        ?>
	        <div id="woocommerce_custom_infusionsoft_tab" class="panel wc-metaboxes-wrapper">
	            <div class="woocommerce_product_tabs wc-metaboxes">
	                <div class="options_group">
                		<p class="form-field">
							<label for="_select">Product Type</label>
							<select style="" id="_select" name="_select" class="select short">
								<option value="Product">Product</option>
								<option value="Subscription">Subscription</option>
							</select>
							<?php echo wc_help_tip("Select product type as a subscription if you are intersted this product as a subscription."); ?>
						</p>
					</div>
	            </div>
	        </div>
	    <?php 
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
?>