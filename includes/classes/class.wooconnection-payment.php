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
<?php
	//Wordpress hook : This action is triggered to add new payment method......
	add_filter( 'woocommerce_payment_gateways', 'add_payment_gateway_class' );
	
	//Function Definiation : add_payment_gateway_class
	function add_payment_gateway_class( $methods ) {
	    $methods[] = 'WC_Gateway_Infusionsoft'; 
	    return $methods;
	}

	class WC_Gateway_Infusionsoft extends WC_Payment_Gateway {
    	public function __construct() {
 			global $woocommerce;
 			$this->id = 'infusionsoft_keap';
	        $this->icon = apply_filters('woocommerce_is_kp_icon', ''.WOOCONNECTION_PLUGIN_URL.'assets/images/infusion.jpg');
	        $this->has_fields = true;
	        $this->method_title = __('Infusionsoft', 'woocommerce-gateway-infusionsoft-keap');
	        $this->method_description = "Lorem Ipsum has been the industry's standard dummy text ever since the 1500s";
	      	$this->supports = array( 'subscriptions', 'products' );        
	        
	        // Load the infusionsoft/keap form fields.
	        $this->init_form_fields(); 
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

			//Wordpress hook : This action is triggered when user try update the infusionsoft/keap payment settings....
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));   
 		}

 		//Function Definition : init_form_fields
 		public function init_form_fields(){
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
					'default'     => __( 'Credit Card (Infusionsoft)', 'woocommerce-gateway-infusionsoft-keap' ),
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
	                'title' 	  => __('Infusionsoft Merchant ID', 'woocommerce-gateway-infusionsoft-keap'),
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
				'wc_subscriptions' => array(
					'title' 	   => __('Woocommerce Subscriptions', 'woocommerce-gateway-infusionsoft-keap'),
					'label' 	   => __( '<span class="slider round"></span>', 'woocommerce-gateway-infusionsoft-keap' ),
					'type'  	   => 'checkbox',
					'description' => __("Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap'),
					'default'      => 'no',
					'class' 	   => 'subscriptionEnable',
				)
			);
		}
	}

    // Create global so you can use this variable beyond initial creation.
	global $custom_payment_gateway;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_payment_gateway = new WC_Gateway_Infusionsoft();
?>
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