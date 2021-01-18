<?php 
	//get the authentication details first....
	$checkAuthenticationStatus = applicationAuthenticationStatus();
	//if empty it means authentication done....
	if(empty($checkAuthenticationStatus)){
		//then call the hook to available payment method......
		//Wordpress hook : This action is triggered to add new payment method......
		add_filter( 'woocommerce_payment_gateways', 'add_payment_gateway_class' );
		//Function Definiation : add_payment_gateway_class
		function add_payment_gateway_class( $methods ) {
		    $methods[] = 'WC_Gateway_Infusionsoft'; 
		    return $methods;
		}
	}
	
	//Define the class WC_Gateway_Infusionsoft.....
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
	        //add some new elements in array to show payment method on checkout page with all cases etc subscription with trial,multiple subscriptions,subscription date changes etc..
	      	$this->supports = array('products','add_payment_method','subscriptions','subscription_amount_changes','subsctiption_date_changes','subscription_payment_method_change','subscription_payment_method_change_customers','subscription_payment_method_change_admin','multiple_subscriptions','pre_orders');      
	        
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
			
			//include custom js and css for woocommerce payment settings admin panel.....
            add_action('admin_enqueue_scripts', array($this,'enqueue_script_payment_admin'));
			
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

		    $currentYear = date("Y");//get the current year.....
		    $currentMonth = date("m");//get the current month.....

		    //first check if exp year and exp month exist then ...
		    if(!empty($expiry_date_year) && !empty($expiry_date_month)){
		    	//check if exp year is equal to current year......
		    	if($expiry_date_year == substr($currentYear, -2)){
		    		//check if exp month is less then current month then show the error.......
		    		if($expiry_date_month < $currentMonth){
		    			wc_add_notice('Card expiration date needs to set of future.', 'error'); 
		        		return false;
		    		}
		    	}
		    }
		    
		    //first check billing email is exist or not........
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
			        	//set contact and card id post data.......
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
		 	$wooconnectionLogger = new WC_Logger();
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
		 	
		 	//set the mercent id....
		    $merchantId = $this->is_merchant_id;

		    //define empty array for subscription details.....
		    $subscriptionDetailsArray = array();
		    //execute loop on recurring cart items......
		 	foreach (WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart) {
	            //recurring loop on cart content.....
	            foreach ($recurring_cart->get_cart() as $cart_item_key => $cart_item) {
	             	//get total discount....
	             	$discount = $cart_item['line_subtotal'] - $cart_item['line_total'];
	             	//get each line item discount....
	             	$eachItemDis = $discount/$cart_item['quantity'];
	             	//get price after deducting disocunt....
	             	$priceAfterDis = $cart_item['line_subtotal']/$cart_item['quantity'] - $eachItemDis;
	             	//get each line item tax.....
	             	$tax = $cart_item['line_tax']/$cart_item['quantity'];
	             	//get final price after adding tax....
	             	$finalProductPrice = $priceAfterDis + $tax;
	             	//create array with key value is a product id....
	             	$subscriptionDetailsArray[$cart_item['product_id']] = array('subscription_quantity'=>$cart_item['quantity'],'subscription_price'=>$finalProductPrice);
	            }
         	}

		    //Get the order items from order then execute loop to create the order items array....
            if ( sizeof( $orderProductsItems = $orderData->get_items() ) > 0 ) {
                foreach($orderProductsItems as $itemId => $item)
                {
                    $typeItem = ITEM_TYPE_PRODUCT;
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
                   	$checkProductAsSub = get_post_meta($orderProductId,'_product_sold_subscription',true);
                   	if($checkProductAsSub == 'yes'){
                   		$typeItem = ITEM_TYPE_SUBSCRIPTION;
                   	}
                   	$orderProductIdCheck = checkAddProductIsKp($access_token,$productData,$parentProduct,$typeItem);//get the related  product id on the basis of relation with infusionsoft/keap application product...
               		if($typeItem == ITEM_TYPE_SUBSCRIPTION && !empty($orderProductIdCheck)){
       					//get the subscription plan interval.......
       					$subPlanInterval = get_post_meta($orderProductId,'_subscription_period_interval',true);
       					//get the subscription plan period....
       					$subPlanPeriod = get_post_meta($orderProductId,'_subscription_period',true);
            			//get the subscription plan cycle....
            			$subPlanLength = get_post_meta($orderProductId,'_subscription_length',true);
            			//create json array to add subscription plan....
            			$planJsonArray = '{"active":true,"cycle_type":"'.strtoupper($subPlanPeriod).'","frequency":'.$subPlanInterval.',"number_of_cycles":'.$subPlanLength.',"plan_price":'.$orderProductPrice.',"subscription_plan_index":0}';
            			//add subscription plan in particular application product....
            			$createdSubscriptionPlanId = addSubscriptionPlan($access_token,$orderProductIdCheck,$planJsonArray,$wooconnectionLogger);
           				//get order product price....
           				$orderProductPrice = round($item['line_subtotal'],2);
            			
            			//check if product/item id exist in subscription products array then merge the subscription plan id in existing subscription details array...
            			if(array_key_exists($orderProductId, $subscriptionDetailsArray)){
            				$subscriptionDetailsArray[$orderProductId]['subscriptionPlanId']=$createdSubscriptionPlanId;
            			}
					}
                    //push product details into array......
                    $itemsDetailsArray[] = array('description'=>$orderProductDesc,'price'=>$orderProductPrice,'product_id'=>$orderProductIdCheck,'quantity'=>$orderProductQuan);
                }
               	
               	//check items array is exist or not.....
                if(isset($itemsDetailsArray) && !empty($itemsDetailsArray)){
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
	                }
                }
            }
           	
           	//check application order id exist or not.....
           	if(isset($applicationorderId) && !empty($applicationorderId)){
            	//get the due amount of order by id....
            	$amountOwned = getOrderAmountOwned($access_token,$applicationorderId,$wooconnectionLogger);
            	//set payment mode on the basis of condition....
            	if($amountOwned <= 0){
            		$paymentMode = PAYMENT_MODE_SKIPPED;
            	}else if($this->testmode == 'yes'){
            		$paymentMode = PAYMENT_MODE_TEST;
            	}else{
            		$paymentMode = PAYMENT_MODE_LIVE;
      			}

      			//check payment mode if it is test or null payment then do manual payment....
            	if($paymentMode == PAYMENT_MODE_TEST || $paymentMode == PAYMENT_MODE_SKIPPED){
            		$manualPayment = chargePaymentManual($access_token,$applicationorderId,$amountOwned,$this->title,$paymentMode,$wooconnectionLogger);
            	}else{
            		if (!empty($merchantId)) {
						$orderPaymentResults = createOrderPayment($access_token,$applicationorderId,$creditCardId,$merchantId);
				 	} else {
			            wc_add_notice("To process payment with ".$this->title." is failed because Merchant ID is not set in settings. Please do it first.", 'error');
			            return false;
			        }
            	}
            }else{
            	wc_add_notice("To Process Payment is failed due to order is not created in authenticate application", 'error'); 
	 			return false; 
            }

            //check if order payment results if empty the it means something is miss like application order id , merchant id or access token.....
            if($paymentMode != PAYMENT_MODE_TEST && $paymentMode != PAYMENT_MODE_SKIPPED && ($orderPaymentResults['Successful'] != true)){
            	wc_add_notice("To Process Payment is failed due to ".$orderPaymentResults['Message'], 'error'); 
	 			return false; 
            }else{
            	//check whether needs to add subscription for contact or not....
	            if(isset($subscriptionDetailsArray) && !empty($subscriptionDetailsArray)){
		           	//set default trial days....
		            $trialDays = 0;
		            //check coupons exist with order or not....
		            if(isset($orderCoupons) && !empty($orderCoupons)){
		                //execute loop on applied coupons......
		                foreach ($orderCoupons as $appliedCouponCode) {
		                    //get the details on applied coupon by coupon code....
		                    $appliedCouponDetails = new WC_Coupon($appliedCouponCode);
		                    //get the discount type of coupon.....
		                    $appliedCouponDiscountType = wcs_get_coupon_property($appliedCouponDetails,'discount_type');
		                    //get the coupon id....
		                    $appliedCouponId = wcs_get_coupon_property($appliedCouponDetails,'id');
		                    //check if coupon type is custom....
		                    if($appliedCouponDiscountType == 'custom_subscription_managed'){
		                        //get the coupon trial duration.......
		                        $appliedCouponTrialDuration = get_post_meta($appliedCouponId,'custom_free_coupon_trial_duration',true);
		                        //get the coupon trial period.......
		                        $appliedCouponTrialPeriod = get_post_meta($appliedCouponId,'custom_free_coupon_trial_period',true);
		                        //check trial period and and trial duration exist,,,,
		                        if(!empty($appliedCouponTrialDuration) && !empty($appliedCouponTrialPeriod)){
		                            //execute switch statement on the basis of trial duration period to calculate the trial
		                            switch ($appliedCouponTrialPeriod) {
		                                 case DURATION_TYPE_DAY:
		                                 $trialDays  = $appliedCouponTrialDuration*1;
		                                 break;
		                                 case DURATION_TYPE_WEEK:
		                                 $trialDays = $appliedCouponTrialDuration*7;
		                                 break;
		                                 case DURATION_TYPE_MONTH:
		                                 $trialDays = $appliedCouponTrialDuration*30;
		                                 break;
		                                 case DURATION_TYPE_YEAR:
		                                 $trialDays = $appliedCouponTrialDuration*366;
		                                 default:
		                                 $trialDays  = $appliedCouponTrialDuration*1;
		                                 break;
		                            }
		                        }
		                        break;
		                    }
		                 }
		            }

		            //rotate loop to add multiple subscripitons.....
		            foreach ($subscriptionDetailsArray as $key => $value) {
		            	//check subscription plan id and quantity exist in array or not.....
		            	if(!empty($value['subscriptionPlanId']) && !empty($value['subscription_quantity'])){

		            		//check if trial days is empty.....
		            		if($trialDays == 0){
		            			//get the subscripitiondetails by subscription plan id....
				            	$subscriptionPlanDetails= getSubscriptionPlanDetails($access_token,$value['subscriptionPlanId'],$wooconnectionLogger);
				            	//check if plan cycle and plan frequency exist then proceed next to set the subscription trial days.....
				            	if(!empty($subscriptionPlanDetails['DefaultCycle']) && !empty($subscriptionPlanDetails['DefaultFrequency'])){
				            		//create trial days on the basis of default cycle and frequency......
				            		switch ($subscriptionPlanDetails['DefaultCycle']) {
		                                 case 6:
		                                 $trialDays  = $subscriptionPlanDetails['DefaultFrequency']*1;
		                                 break;
		                                 case 3:
		                                 $trialDays = $subscriptionPlanDetails['DefaultFrequency']*7;
		                                 break;
		                                 case 2:
		                                 $trialDays = $subscriptionPlanDetails['DefaultFrequency']*30;
		                                 break;
		                                 case 1:
		                                 $trialDays = $subscriptionPlanDetails['DefaultFrequency']*366;
		                            }
				            	}
				            }

		                    //create subscription xml....
		                    $contactSubcriptionXml = '<methodCall><methodName>InvoiceService.addRecurringOrder</methodName><params><param><value><string></string></value></param><param><value><int>'.$contactId.'</int></value></param><param><value><boolean>1</boolean></value></param><param><value><int>'.$value['subscriptionPlanId'].'</int></value></param><param><value><int>'.$value['subscription_quantity'].'</int></value></param><param><value><double>'.$value['subscription_price'].'</double></value></param><param><value><boolean>1</boolean></value></param><param><value><int>'.$merchantId.'</int></value></param><param><value><int>'.$creditCardId.'</int></value></param><param><value><int>0</int></value></param><param><value><int>'.$trialDays.'</int></value></param></params></methodCall>';
		                    
		                    //create contact subscription.....
		                    $contactSub = createContactSub($access_token,$contactSubcriptionXml,$wooconnectionLogger);
		            	}
		            }	
	            }
            }
          	
          	//add order notes.......
     	 	$orderData->add_order_note(__('Payment accepted via '.$orderData->payment_method_title.' gateway - Order Successful', 'woocommerce'));
         	
         	//get or set the merchant reference number...
         	$reference_number = 0;
         	if(isset($orderPaymentResults['RefNum']) && !empty($orderPaymentResults['RefNum'])){
         		$reference_number = $orderPaymentResults['RefNum'];	
         	}
         	
         	//update order for future with merchant reference number......
			update_post_meta($order_id, 'is_kp_order_merchant_reference_number', $reference_number); 

			//get the payment method to proceed next........
            $payment_method = $orderData->get_payment_method();
			update_post_meta($order_id, 'Custom Payment Gateway', $payment_method);			 
			
			//mark order payment as a complete........
			$orderData->payment_complete();

			//check if something exist woocommerce cart then empty the cart.......
         	if($woocommerce->cart) {
         		$woocommerce->cart->empty_cart();
         	}
			
			//return sucess.....
			return array('result' => 'success', 'redirect' => $this->get_return_url($orderData));
		}

    	//Function Definiation : enqueue_script_coupon_admin
	    public function enqueue_script_payment_admin(){
	    	// deregisters the default WordPress jQuery  
    		wp_deregister_script( 'jquery' );
            //Wooconnection Scripts : Resgister the wooconnection scripts..
            wp_register_script('jquery', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.min.js'),WOOCONNECTION_VERSION, true);
            //Wooconnection Scripts : Enqueue the wooconnection scripts..
            wp_enqueue_script('jquery');

            //Wooconnection Scripts : Enqueue the wooconnection scripts for payment settings admin screen........
            wp_enqueue_script( 'custom-gateway-js', WOOCONNECTION_PLUGIN_URL.'assets/js/custom-gateway.js', array('jquery'), WOOCONNECTION_VERSION, true );

        	//Wooconnection Styles : Resgister the wooconnection styles..
            wp_register_style( 'wooconnection_custom_gateway_style', WOOCONNECTION_PLUGIN_URL.'assets/css/custom-gateway.css', array(), WOOCONNECTION_VERSION );
            //Wooconnection Styles : Enqueue the wooconnection styles..
            wp_enqueue_style('wooconnection_custom_gateway_style');
	    }
	}

	// Create global so you can use this variable beyond initial creation.
	global $custom_payment_gateway;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_payment_gateway = new WC_Gateway_Infusionsoft();
?>