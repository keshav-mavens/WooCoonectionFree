<?php
	function add_your_gateway_class( $methods ) {
	    $methods[] = 'WC_Gateway_Infusionsoft'; 
	    return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_your_gateway_class' );
	
	class WC_Gateway_Infusionsoft extends WC_Payment_Gateway {
    	/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
 			global $woocommerce;
 			$this->id = 'infusionsoft';
	        $this->icon == apply_filters('woocommerce_is_kp_icon', plugins_url('infusion.jpg', __FILE__));
	        $this->has_fields = true;
	        $this->method_title = __('Infusionsoft', 'woocommerce');
	        $this->method_description = 'Description of Infusionsoft/Keap payment gateway'; // will be displayed on the options page
	      	$this->supports = array( 'subscriptions', 'products' );        
	        
	        $this->init_form_fields(); 
	        $this->init_settings();
	        //include custom css and js.....
            $this->includeCustomCssJs(); 
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));   
 		}

 		public function init_form_fields(){
			$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable Infusionsoft',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
					'class' => 'methodEnable',
				),
				'title' => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'Credit card (Infusionsoft)',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => 'Pay with your credit card via our super-cool payment gateway.',
				),
				'testmode' => array(
					'title'       => 'Test mode',
					'label'       => 'Enable Test Mode',
					'type'        => 'checkbox',
					'description' => 'Place the payment gateway in test mode using test API keys.',
					'default'     => 'yes',
					'desc_tip'    => true,
				),
				'is_merchant_id' => array(
	                'title' => __('Infusionsoft Merchant ID', 'woocommerce'),
	                'type' => 'text',
	                'description' => __('Merchant Account ID <a target="_blank" href="https://help.infusionsoft.com/help/how-to-locate-your-merchant-account-id">Click here to get your merchant account ID.</a>', 'woocommerce'),
	                'default' => '',
	            	'class' => 'merchantClass',
	            ),
	            'process_credit_card' => array(
					'title'       => 'Process Credit Card',
					'label'       => 'Enable/Disable Process Credit Card',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
					'class' => 'processCreditCardEnable',
				),
				'wc_subscriptions' => array(
					'title'       => 'Woocommerce Subscriptions',
					'label'       => 'Enable/Disable Woocommerce Subscriptions',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
					'class' => 'subscriptionEnable',
				)
			);
		}
		
		public function includeCustomCssJs(){
			?>
	            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	            <script type="text/javascript">
	                $( document ).ready(function() {
	                    //check if option is enable or not on the basis of that show/hide the merchant id field....
	                    if($(".methodEnable").prop('checked') == true){
						    $(".merchantClass").closest("tr").show();
						}else{
							$(".merchantClass").closest("tr").hide();
						}
						
						//on change of enable/disable show hide the merchant id field....
	                    $('.methodEnable').change(function() {
					        if(this.checked) {
					            $(".merchantClass").closest("tr").show();
					            $(".subscriptionEnable").closest("tr").hide();
					        	$(".processCreditCardEnable").closest("tr").show();	
					        }else{
					        	$(".merchantClass").closest("tr").hide();
					        	
					        	$(".processCreditCardEnable").closest("tr").hide();	
					        	$(".subscriptionEnable").closest("tr").hide();

					        	$(".subscriptionEnable").prop('checked', false);
					        	$(".processCreditCardEnable").prop('checked', false);
					        }
					    });

					    //check if option to process credit cards option is enable or not on the basis of that show/hide the woocommerce subscription field....
	                    if($(".processCreditCardEnable").prop('checked') == true){
						    $(".subscriptionEnable").closest("tr").show();
						}else{
							$(".subscriptionEnable").closest("tr").hide();
						}
						
						//on change of process credit cards option show hide the woocommerce subscription field....
	                    $('.processCreditCardEnable').change(function() {
					        if(this.checked) {
					            $(".subscriptionEnable").closest("tr").show();
					        }else{
					        	$(".subscriptionEnable").closest("tr").hide();	
					        }
					    });
	                });
	            </script>
	        <?php
		}
    }

    // Create global so you can use this variable beyond initial creation.
	global $custom_payment_gateway;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_payment_gateway = new WC_Gateway_Infusionsoft();
?>