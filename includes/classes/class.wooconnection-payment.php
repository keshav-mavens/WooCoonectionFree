<?php 
	$checkAuthenticationStatus = applicationAuthenticationStatus();
	if(empty($checkAuthenticationStatus)){
		//Wordpress hook : This action is triggered to add new payment method......
		add_filter( 'woocommerce_payment_gateways', 'add_payment_gateway_class' );
		//Function Definiation : add_payment_gateway_class
		function add_payment_gateway_class( $methods ) {
		    $methods[] = 'WC_Gateway_Infusionsoft'; 
		    return $methods;
		}
	}
	
	class WC_Gateway_Infusionsoft extends WC_Payment_Gateway {
		
		public function __construct() {
 			global $woocommerce;
 			//Get the application type so that application type selected from dropdown.....
			$configurationType = applicationType();
			$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
			if(isset($configurationType) && !empty($configurationType)){
				if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
					$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
				}else if ($configurationType == APPLICATION_TYPE_KEAP) {
					$type = APPLICATION_TYPE_KEAP_LABEL;
				}
			}

			//Get the application lable to display.....
			$applicationLabel = applicationLabel($type);
			$this->id = 'infusionsoft_keap';
	        $this->icon = apply_filters('woocommerce_is_kp_icon', ''.WOOCONNECTION_PLUGIN_URL.'assets/images/infusion-payment.jpg');
	        $this->has_fields = true;
	        $this->method_title = __($applicationLabel, 'woocommerce-gateway-infusionsoft-keap');
	        $this->method_description = "Lorem Ipsum has been the industry's standard dummy text ever since the 1500s";
	      	$this->supports = array( 'subscriptions', 'products' );        
	        
	        // Load the infusionsoft/keap form fields.
	        $this->initPaymentFormFields($type,$applicationLabel); 
	        // Load the infusionsoft/keap settings.
	        $this->init_settings();

	        // Get infusionsoft/keap setting values.
			$this->title                = $this->get_option( 'title' );
			$this->description          = $this->get_option( 'description' );
			$this->enabled              = $this->get_option( 'enabled' );
			$this->testmode             = 'yes' === $this->get_option( 'testmode' );
			$this->is_merchant_id 		= $this->get_option('is_merchant_id'); //defaults to empty
			$this->process_credit_card  = 'no' === $this->get_option( 'process_credit_card' );
			$this->wc_subscriptions  	= 'no' === $this->get_option( 'wc_subscriptions' );
			//include custom css and js for another pages of wp-admin.....
            $this->includeCustomCss();
			
			//Wordpress hook : This action is triggered when user try update the infusionsoft/keap payment settings....
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));   
 		}

 		//Function Definition : init_form_fields
 		public function initPaymentFormFields($type,$applicationLabel){
			$this->form_fields = array(
				'enabled' 	=> array(
					'title'       => __( 'Enable/Disable', 'woocommerce-gateway-infusionsoft-keap' ),
					'label'       => __( '<span class="slider round"></span>', 'woocommerce-gateway-infusionsoft-keap' ),
					'type'        => 'checkbox',
					'description' => __( "Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap' ),
					'default'     => 'no',
					'class' 	  => 'methodEnable',
				),
				'title' => array(
					'title'       => __( 'Title', 'woocommerce-gateway-infusionsoft-keap' ),
					'type'        => 'text',
					'description' => __( "Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap' ),
					'default'     => __( 'Credit Card ('.$applicationLabel.')', 'woocommerce-gateway-infusionsoft-keap' ),
					'desc_tip'    => false,
				),
				'description' => array(
					'title'       => __( 'Description', 'woocommerce-gateway-infusionsoft-keap' ),
					'type'        => 'textarea',
					'description' => __( "Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap' ),
					'default'     => __( 'Pay with your credit card via our super-cool payment gateway.', 'woocommerce-gateway-infusionsoft-keap' ),
				),
				'testmode' => array(
					'title'       => __( 'Test mode', 'woocommerce-gateway-infusionsoft-keap' ),
					'label'       => __( '<span class="slider round"></span>', 'woocommerce-gateway-infusionsoft-keap' ),
					'type'        => 'checkbox',
					'description' => __( "Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap' ),
					'default'     => 'yes',
					'desc_tip'    => false,
					'class' 	  => 'testmodeEnable',
				),
				'is_merchant_id' => array(
	                'title' 	  => __($applicationLabel.' Merchant ID', 'woocommerce-gateway-infusionsoft-keap'),
	                'type' 		  => 'text',
	                'description' => __('Merchant Account ID <a target="_blank" href="https://help.infusionsoft.com/help/how-to-locate-your-merchant-account-id">Click here to get your merchant account ID.</a>', 'woocommerce-gateway-infusionsoft-keap'),
	                'default' 	  => '',
	            	'class' 	  => 'merchantClass',
	            ),
	            'process_credit_card' => array(
					'title' 	  => __('Process Credit Card', 'woocommerce-gateway-infusionsoft-keap'),
					'label'       => __( '<span class="slider round"></span>', 'woocommerce-gateway-infusionsoft-keap' ),
					'type'        => 'checkbox',
					'description' => __("Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap'),
					'default'     => 'no',
					'class' 	  => 'processCreditCardEnable',
				),
			);
			
			//define empty array......
			$subArray = array();
			//check if application type is infusionsoft.....
			if($type == APPLICATION_TYPE_INFUSIONSOFT_LABEL){
				$subArray['wc_subscriptions'] = array(
													'title' 	   => __('Woocommerce Subscriptions', 'woocommerce-gateway-infusionsoft-keap'),
													'label' 	   => __( '<span class="slider round"></span>', 'woocommerce-gateway-infusionsoft-keap' ),
													'type'  	   => 'checkbox',
													'description' => __("Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap'),
													'default'      => 'no',
													'class' 	   => 'subscriptionEnable',
												);
			}
			//merge subscription array element to form fields array.....
			$this->form_fields = array_merge($this->form_fields, $subArray);
		}

		//Function Definition : payment_fields
 		public function payment_fields() {
           	wp_enqueue_script( 'wc-credit-card-form' );

            // ok, let's display some description before the payment form
			if ( $this->description ) {
				// you can instructions for test mode, I mean test card numbers etc.
				if ( $this->testmode ) {
					$this->description .= ' TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 with any CVC and a valid expiration date.';
					$this->description  = trim( $this->description );
				}
				// display the description with <p> tags etc.
				echo wpautop( wp_kses_post( $this->description ) );
			}
		 
			// I will echo() the form, but you can close PHP tags and print it directly in HTML
			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
		 
			// Add this action hook if you want your custom payment gateway to support it
			do_action( 'woocommerce_credit_card_form_start', $this->id );
		 
			// I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
			echo '<div class="form-row form-row-wide"><label>Card Number <span class="required">*</span></label>
				<input class="wc-credit-card-form-card-number" id="' . esc_attr( $this->id ) . '_cnumber" name="' . esc_attr( $this->id ) . '_cnumber" type="tel" maxlength="19" placeholder="**** **** **** ****">
				</div>
				<div class="form-row form-row-first">
					<label>Expiry Date <span class="required">*</span></label>
					<input class="wc-credit-card-form-card-expiry" id="' . esc_attr( $this->id ) . '_expdate" name="' . esc_attr( $this->id ) . '_expdate" maxlength="7" type="tel" placeholder="MM / YY">
				</div>
				<div class="form-row form-row-last">
					<label>Card Code (CVC) <span class="required">*</span></label>
					<input class="wc-credit-card-form-card-cvc" id="' . esc_attr( $this->id ) . '_cvv" name="' . esc_attr( $this->id ) . '_cvv" type="tel" placeholder="CVC">
				</div>
				<div class="clear"></div>';
		 
			do_action( 'woocommerce_credit_card_form_end', $this->id );
		 
			echo '<div class="clear"></div></fieldset>';
		}


		//Function Definition : validate_fields
        public function validate_fields(){
           	if(isset($_POST) && !empty($_POST)){

           	}
        }

    	//Function Definition : includeCustomCss
	    public function includeCustomCss(){
	        if(isset($_GET['page']) && $_GET['page'] == 'wc-settings'){
			ob_start();
			?>
	            <style type="text/css">
	                label[for="woocommerce_infusionsoft_keap_enabled"],label[for="woocommerce_infusionsoft_keap_testmode"],label[for="woocommerce_infusionsoft_keap_process_credit_card"],label[for="woocommerce_infusionsoft_keap_wc_subscriptions"] {
						  position: relative !important;
						  display: inline-block  !important;
						  width: 60px  !important;
						  height: 34px  !important;
						}
						input.methodEnable,input.testmodeEnable,input.processCreditCardEnable,input.subscriptionEnable {
						  opacity: 0;width: 0;height: 0;
						}
						.slider {
						  position: absolute;cursor: pointer;top: 0;left: 0;right: 0;bottom: 0;background-color: #ccc !important;-webkit-transition: .4s;transition: .4s;
						}
						.slider:before {
						  position: absolute;content: "";height: 26px;width: 26px;left: 4px;bottom: 4px;background-color: white;-webkit-transition: .4s;
						  transition: .4s;
						}
						input:checked + .slider {
						  background-color: #2196F3 !important;
						}
						input:focus + .slider {
						  box-shadow: 0 0 1px #2196F3 !important;
						}
						input:checked + .slider:before {
						  -webkit-transform: translateX(26px);
						  -ms-transform: translateX(26px);
						  transform: translateX(26px);
						}
						.slider.round {
						  border-radius: 34px !important;
						}
						.slider.round:before {
						  border-radius: 50% !important;
						}
	            </style>
	            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	            <script type="text/javascript">
					$( document ).ready(function() {
					    //check if option is enable or not on the basis of that show/hide the merchant id field....
					    if($(".methodEnable").prop('checked') == true){
						    $(".merchantClass").closest("tr").show();
							$(".processCreditCardEnable").closest("tr").show();	
						}else{
							$(".merchantClass").closest("tr").hide();
							$(".processCreditCardEnable").closest("tr").hide();	
				        	$(".subscriptionEnable").closest("tr").hide();

				        	$(".subscriptionEnable").prop('checked', false);
				        	$(".processCreditCardEnable").prop('checked', false);
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
							$(".subscriptionEnable").prop('checked', false);
						}
						
						//on change of process credit cards option show hide the woocommerce subscription field....
					    $('.processCreditCardEnable').change(function() {
					        if(this.checked) {
					            $(".subscriptionEnable").closest("tr").show();
					        }else{
					        	$(".subscriptionEnable").closest("tr").hide();
					        	$(".subscriptionEnable").prop('checked', false);	
					        }
					    });
					});
				</script>
	        <?php
	    	}
	    }
	}

	// Create global so you can use this variable beyond initial creation.
	global $custom_payment_gateway;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_payment_gateway = new WC_Gateway_Infusionsoft();
?>