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
			$gatewayImage = 'infusion-payment.jpg';
			if(isset($configurationType) && !empty($configurationType)){
				if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
					$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
					$gatewayImage = 'infusion-payment.jpg';
				}else if ($configurationType == APPLICATION_TYPE_KEAP) {
					$type = APPLICATION_TYPE_KEAP_LABEL;
					$gatewayImage = 'keap-payment.jpg';
				}
			}

			//Get the application lable to display.....
			$applicationLabel = applicationLabel($type);
			$this->id = 'infusionsoft_keap';
	        $this->icon = apply_filters('woocommerce_is_kp_icon', ''.WOOCONNECTION_PLUGIN_URL.'assets/images/'.$gatewayImage);
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
				$subArray['wc_subscriptions'] = array('title'=>__('Woocommerce Subscriptions', 'woocommerce-gateway-infusionsoft-keap'),'label'=>__( '<span class="slider round"></span>', 'woocommerce-gateway-infusionsoft-keap'),'type'=>'checkbox','description'=>__("Lorem Ipsum has been the industry's standard dummy text ever since the 1500s", 'woocommerce-gateway-infusionsoft-keap'),'default'=>'no','class'=>'subscriptionEnable');
			}
			//merge subscription array element to form fields array.....
			$this->form_fields = array_merge($this->form_fields, $subArray);
		}

		//Function Definition : payment_fields
 		public function payment_fields() {
           	//enqueue the statndard woocommerce script to auto set form fields as a woocommerce payment gateway fields.....
           	wp_enqueue_script( 'wc-credit-card-form' );
			//check payment method description is exist from backend then proceed next.....
			if ( $this->description ) {
				//check test mode enable.....
				if ( $this->testmode ) {
					//if enable the append static text in description.......
					$this->description .= ' TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 with any CVC and a valid expiration date.';
					$this->description  = trim( $this->description );
				}
				// display the description with <p> tags etc.
				echo wpautop( wp_kses_post( $this->description ) );
			}
		 	//start html of payment fields form...
			echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
		 	//call the hook to start the custom payment gateway......
			do_action( 'woocommerce_credit_card_form_start', $this->id );
		 	//create the html of basic credit card fields.....
			echo '<div class="form-row form-row-wide"><label>Card Number <span class="required">*</span></label>
				<input class="wc-credit-card-form-card-number" id="' . esc_attr( $this->id ) . '_cnumber" name="' . esc_attr( $this->id ) . '_cnumber" type="tel" maxlength="19" placeholder="**** **** **** ****"></div><div class="form-row form-row-first"><label>Expiry Date <span class="required">*</span></label><input class="wc-credit-card-form-card-expiry" id="' . esc_attr( $this->id ) . '_exppdate" name="' . esc_attr( $this->id ) . '_exppdate" maxlength="7" type="tel" placeholder="MM / YY"></div><div class="form-row form-row-last"><label>Card Code (CVC) <span class="required">*</span></label><input class="wc-credit-card-form-card-cvc" id="' . esc_attr( $this->id ) . '_cardcvv" name="' . esc_attr( $this->id ) . '_cardcvv" type="tel" placeholder="CVC"></div><div class="clear"></div>';
		 	//call the hook to end the custom payment gateway......
			do_action( 'woocommerce_credit_card_form_end', $this->id );
			//end html of payment fields form...
		 	echo '<div class="clear"></div></fieldset>';
		}


		//Function Definition : validate_fields
        public function validate_fields(){
            
            //define empty variables and empty array........
            $credit_card_number = '';
            $credit_card_cvv = '';
            $expiry_date_array = array('','');
            $expiry_date_month = '';
            $expiry_date_year = '';
            $cardFirstName = '';
			$cardLastName = '';
            
            //check and set card number......
            if(isset($_POST['infusionsoft_keap_cnumber']) && !empty($_POST['infusionsoft_keap_cnumber'])){
            	$credit_card_number = str_replace( array(' ', '-' ), '', $_POST['infusionsoft_keap_cnumber']); 
            }
		    
		    //check and set expiry date......
            if(isset($_POST['infusionsoft_keap_exppdate']) && !empty($_POST['infusionsoft_keap_exppdate'])){
            	$expiry_date_array = explode ("/", $_POST['infusionsoft_keap_exppdate']);
            	$expiry_date_month =  str_replace( array(' ', '-' ), '', $expiry_date_array[0]); 
		    	$expiry_date_year =  str_replace( array(' ', '-' ), '', $expiry_date_array[1]); 
            }

		    //check and set card cvv......
            if(isset($_POST['infusionsoft_keap_cardcvv']) && !empty($_POST['infusionsoft_keap_cardcvv'])){
            	$credit_card_cvv = $_POST ['infusionsoft_keap_cardcvv']; 
            }
		    
		    // Check card number exist or not....
		    if(empty($credit_card_number) || !ctype_digit($credit_card_number)) { 
		        wc_add_notice('Card number is required.', 'error'); 
		        return false; 
		    }
		    //check card number length.....
		    else if(strlen($credit_card_number) < 15 || strlen($credit_card_number) > 16){
		    	wc_add_notice("Card number length does not match.", 'error');
		        return false; 
		    }
		    //check card number type.....
		    else{
		    	switch($credit_card_number) {
		            case(preg_match ('/^4/', $credit_card_number) >= 1):
		                $creditCardType = 'Visa';
		                break;
		            case(preg_match ('/^5[1-5]/', $credit_card_number) >= 1):
		                $creditCardType = 'MasterCard';
		            	break;
		            case(preg_match ('/^3[47]/', $credit_card_number) >= 1):
		                $creditCardType = 'American Express';
		            	break;
		            case(preg_match ('/^6(?:011|5)/', $credit_card_number) >= 1):
		                $creditCardType = 'Discover';
		            	break;
		            default:
		               wc_add_notice("Could not determine the credit card type.", 'error');
		               return false; 
		        }
		    }

		    //check if complete expiry date is empty.....
		    if(empty($expiry_date_month) && empty($expiry_date_month)) {
		    	wc_add_notice('Card expiration date is required.', 'error'); 
		        return false; 
		    }
			
			//check expiry month is not empty and then validate it....
			if(empty($expiry_date_month)) { 
		        wc_add_notice('Card expiration month is required.', 'error'); 
		        return false; 
		    }else{ 
		        if((int)$expiry_date_month>12 || (int)$expiry_date_month<1) { 
		            wc_add_notice('Card expiration month is invalid.', 'error'); 
		            return false; 
		        } 
		    } 
		 	
		 	//check expiry year is not empty and then validate it....
		 	if(empty($expiry_date_year)) { 
		        wc_add_notice('Card expiration year is required.', 'error'); 
		        return false; 
		    }else{ 
		        if(strlen($expiry_date_year)==1 ||strlen($expiry_date_year)==3||strlen($expiry_date_year)>4) { 
		            wc_add_notice('Card expiration year is invalid.', 'error'); 
		            return false; 
		        } 
		 		if(strlen($expiry_date_year)==2) { 
		            if((int)$expiry_date_year < (int)substr(date('Y'), -2)) { 
		                wc_add_notice('Card expiration year is invalid.', 'error'); 
		                return false; 
		            } 
		        } 
		 
		        if(strlen($expiry_date_year)==4) { 
		            if((int)$expiry_date_year < (int)date('Y')) { 
		                wc_add_notice('Card expiration year is invalid.', 'error'); 
		                return false; 
		            } 
		        } 
		    }
		 	
		 	//Check and set card security code.....
		    if(empty($credit_card_cvv)) { 
		        wc_add_notice('Card security code is required.', 'error'); 
		        return false; 
		    }
		    if(!ctype_digit($credit_card_cvv)) { 
		        wc_add_notice('Card security code is invalid.', 'error'); 
		        return false; 
		    } 
		    if(strlen($credit_card_cvv) <3) { 
		        wc_add_notice('Card security code, invalid length.', 'error'); 
		        return false; 
		    }

		    if(isset($_POST['billing_email']) && !empty($_POST['billing_email'])){
		    	// Create instance of our wooconnection logger class to use off the whole things.
			    $wcLogger = new WC_Logger();
			    
			    //Concate a error message to store the logs...
			    $callbackPurpose = 'Infusionsoft/Keap Process Payment : Process infusionsoft/keap payment add contact';

		    	//get the application authentication details ......
			    $applicationAuthenticationDetails = getAuthenticationDetails();

			    //Stop the below process if not authentication done with infusionsoft/keap application..
			    if(empty($applicationAuthenticationDetails) || empty($applicationAuthenticationDetails[0]->user_access_token))
			    {
			        $addLogs = addLogsAuthentication($callback_purpose);
			        return false;
			    }

			    //get the access token....
			    $access_token = '';
			    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
			        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
			    }

		    	$payment_email = $_POST['billing_email'];

		    	// Validate email is in valid format or not 
			    validate_email($payment_email,$callbackPurpose,$wcLogger);

			    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
			    $paymentContactId = checkAddContactApp($access_token,$payment_email,$callbackPurpose);
		    
			    //get last four digits of credit card.....
			    $last_four_digits = substr($credit_card_number,-4);

			    //check contact id exist or not.....
			    if(isset($paymentContactId) && !empty($paymentContactId)){
			    	//define empty array.....
			    	$cardDetails = array();
			    	//assign values to array....
			    	$cardDetails['CardType'] = $creditCardType;
			    	$cardDetails['ContactId'] = $paymentContactId;
			    	$cardDetails['CardNumber'] = $credit_card_number;
			    	$cardDetails['ExpirationMonth'] = $expiry_date_month;
			    	$cardDetails['ExpirationYear'] = $expiry_date_year;
			    	$cardDetails['CVV2'] = $credit_card_cvv;
			    	
			    	//Validate the credit card first.......
			        $creditCardResult = validateCreditCard($access_token,$cardDetails);
			        
			        //If the credit card details is invalid then stop the process to proceed next.......
			        if (!empty($creditCardResult) && $creditCardResult['Valid'] == 'false') {
			            $failurereason = 'Cedit Card Details are not valid';
			            wc_add_notice('Process payment failed due to '.$failurereason, 'error'); 
		        		return false; 
			        }else{
		        		//then first check mercent id exist in custom payment gateway settings.....
		        		if (empty($this->is_merchant_id)) {
			                wc_add_notice("To process payment with ".$this->title." is failed because Merchant ID is not set in settings. Please do it first.", 'error'); 
	        			 	return false; 
			         	}

			         	$responseContactCc = checkContactCardExist($access_token,$paymentContactId,$last_four_digits);
			        	//check and set card first name......
			            if(isset($_POST['billing_first_name']) && !empty($_POST['billing_first_name'])){
			            	$cardFirstName = trim($_POST['billing_first_name']); 
			            }
			            //check and set card last name......
			            if(isset($_POST['billing_last_name']) && !empty($_POST['billing_last_name'])){
			            	$cardLastName = trim($_POST['billing_last_name']); 
			            }
			            //set the addtional values in 
			        	$cardDetails['NameOnCard'] = ucfirst($cardFirstName) . " " . ucfirst($cardLastName);
			         	$cardDetails['FirstName'] = $cardFirstName;
			         	$cardDetails['LastName'] = $cardLastName;
			         	$cardDetails['BillAddress1'] = isset($_POST['billing_address_1']) ? $_POST['billing_address_1'] : '';
			         	$cardDetails['BillAddress2'] = isset($_POST['billing_address_2']) ? $_POST['billing_address_2'] : '';
			         	$cardDetails['BillCity'] = isset($_POST['billing_city']) ? $_POST['billing_city'] : '';
			         	$cardDetails['BillState'] = isset($_POST['billing_state']) ? $_POST['billing_state'] : '';
			         	$cardDetails['BillCountry'] = isset($_POST['billing_country']) ? $_POST['billing_country'] : '';
			         	$cardDetails['BillZip'] = isset($_POST['billing_postcode']) ? $_POST['billing_postcode'] : '';
			        	if(!empty($responseContactCc)){
			        		$appCreditCardId = updateExistingCreditCard($access_token,$responseContactCc,$cardDetails);
			        		if(isset($appCreditCardId) && !empty($appCreditCardId)){
			        			$appCreditCardId = $appCreditCardId;
			        		}else{
			        			$appCreditCardId = $responseContactCc;
			        		}
			        	}else{
			        		$appCreditCardId = addNewCreditCard($access_token,$cardDetails);
			        	}
			        	$_POST['custom_payment_gateway_card_id'] = $appCreditCardId;
			        	$_POST['custom_payment_gateway_contact_id'] = $paymentContactId;
			        }
			    }
		    }
		    return true; 
        }

		//Function Definition : process_payment
        public function process_payment($order_id)
		{
		 	global $woocommerce;
		 	$orderData = new WC_Order($order_id);//get the order details by order id.......
		 	$contactId = $_POST['custom_payment_gateway_contact_id'];//get the contact id from post data.....
		 	$creditCardId = $_POST['custom_payment_gateway_card_id'];//get the contact id from post data.....
			$orderTax = (float) $orderData->get_total_tax();//get the tax for order
		    $orderDiscountDetails  = (int) $orderData->get_total_discount();//get the discount on order.....
		    $orderCoupons = $orderData->get_used_coupons();//get the list of used coupons.....
		    $orderDiscountDesc = "Order Discount";//Set order discount disc....
		    //Append list of discount coupon codes in string....
		    if(!empty($orderCoupons)){
		        $orderDiscountDesc = implode(",", $orderCoupons);
		        $orderDiscountDesc = "Discount generated from coupons ".$orderDiscountDesc;
		    }

		    //get the application authentication details ......
		    $applicationAuthenticationDetails = getAuthenticationDetails();

		    //Stop the below process if not authentication done with infusionsoft/keap application..
		    if(empty($applicationAuthenticationDetails) || empty($applicationAuthenticationDetails[0]->user_access_token))
		    {
		        $addLogs = addLogsAuthentication($callback_purpose);
		        return false;
		    }

		    //get the access token....
		    $access_token = '';
		    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
		        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
		    }

		    //Get the order items from order then execute loop to create the order items array....
            if ( sizeof( $orderProductsItems = $orderData->get_items() ) > 0 ) {
                foreach($orderProductsItems as $itemId => $item)
                {
                    $parentProduct = '';
                    if(!empty($item->get_variation_id())){
                        $orderProductId = $item->get_variation_id();    
                        $parentProduct = $item->get_product_id();
                    }else{
                        $orderProductId = $item->get_product_id(); 
                    }
                    $productData = wc_get_product($orderProductId);//get the prouct details...
                    $orderProductDesc = $productData->get_description();//product description..
                    $orderProductPrice = round($productData->get_price(),2);//get product price....
                    $orderProductQuan = $item['quantity']; // Get the item quantity....
                    $orderProductIdCheck = checkAddProductIsKp($access_token,$productData,$parentProduct);//get the related  product id on the basis of relation with infusionsoft/keap application product...
                    $productTitle = $productData->get_title();//get product title..
                    //push product details into array/......
                    $itemsDetailsArray[] = array('description'=>$orderProductDesc,'price'=>$orderProductPrice,'product_id'=>$orderProductIdCheck,'quantity'=>$orderProductQuan);
                }
                $jsonOrderItemsData = json_encode($itemsDetailsArray);//create order items json....
                //create order in infusionsoft/keap application.....
                $applicationorderId = createOrder($order_id,$contactId,$jsonOrderItemsData,$access_token);
                //update order relation between woocommerce order and infusionsoft/keap application order.....
                if(!empty($applicationorderId)){
                    //Update relation .....
                    update_post_meta($order_id, 'is_kp_order_relation', $applicationorderId);
                    //Check of tax exist with current order....
                    if(isset($orderTax) && !empty($orderTax)){
                        //Call the common function to add order itema as a tax....
                        addOrderItems($access_token,$applicationorderId, NON_PRODUCT_ID, ITEM_TYPE_TAX, $orderTax, ORDER_ITEM_QUANTITY, 'Order Tax',ITEM_TAX_NOTES);
                    }
                    //Check discount on order.....
                    if(isset($orderDiscountDetails) && !empty($orderDiscountDetails)){
                        $discountDetected = $orderDiscountDetails;
                        $discountDetected *= -1;
                        //Call the common function to add order itema as a discount....
                        addOrderItems($access_token,$applicationorderId, NON_PRODUCT_ID, ITEM_TYPE_DISCOUNT, $discountDetected, ORDER_ITEM_QUANTITY, $orderDiscountDesc, ITEM_DISCOUNT_NOTES);
                    }
                    //$order->payment_complete();
                }
            }

            //check if test mode is enable.....
            if ($this->testmode == 'yes') {
		        wc_add_notice('At this point you have passed all forms of verification and your order should have been added to IS with the ID of ' . $applicationorderId . '. Take the IS module out of test mode to make real transactions.', 'error'); 
		        return false;
		    }
		  //   if (!empty($this->is_merchant_id)) {
				// $results = createOrderPayment($inv_id,"Online Shopping Cart", $creditCardId, $merchant, false);
		  //   }

			return array('result' => 'success', 'redirect' => $this->get_return_url($orderData));
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