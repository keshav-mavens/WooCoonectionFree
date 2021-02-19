<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

//Woocommerce hook : This action is triggered when a status mark as completed.
add_action('woocommerce_order_status_completed', 'wooconnection_trigger_status_complete_hook', 10, 3);

//Woocommerce hook : This action is triggered when a payment is done.
add_action('woocommerce_payment_complete', 'wooconnection_trigger_status_complete_hook');

//Function Definiation : wooconnection_trigger_status_complete_hook
function wooconnection_trigger_status_complete_hook($orderid){
    // Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    
    //Concate a error message to store the logs...
    $callback_purpose = 'Wooconnection Successful order : Process of successful order trigger';
    
    $applicationAuthenticationDetails = getAuthenticationDetails();

    //Stop the below process if not authentication done with infusionsoft/keap application..
    if(empty($applicationAuthenticationDetails) || empty($applicationAuthenticationDetails[0]->user_access_token))
    {
        $addLogs = addLogsAuthentication($callback_purpose);
        return false;
    }

    //get the access token....
    $access_token = '';
    $applicationEdition = '';
    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
        $applicationEdition = $applicationAuthenticationDetails[0]->user_application_edition;
    }

    // Get the order details
    $order = new WC_Order( $orderid );
    //get the order email.....
    $order_email = $order->get_billing_email();
    //get the tax for order
    $orderTaxDetails = (float) $order->get_total_tax();
    //get the discount on order.....
    $orderDiscountDetails  = (int) $order->get_total_discount();
    //get the list of used coupons
    $orderAssociatedCoupons = $order->get_used_coupons();
    $discountDesc = "Order Discount";//Set order discount disc....
    //Append list of discount coupon codes in string....
    if(!empty($orderAssociatedCoupons)){
        $discountDesc = implode(",", $orderAssociatedCoupons);
        $discountDesc = "Discount generated from coupons ".$discountDesc;
        //get the referral partner tracking status......
        $checkReferralTrackingStatus = get_option('referral_partner_tracking_status',true);
        //then check referral partner tracking is enable or not , if enable then proceed next.....
        if(!empty($checkReferralTrackingStatus) && $checkReferralTrackingStatus == 'On'){
            //execute loop on assoicated orders....
            foreach ($orderAssociatedCoupons as $key => $value) {
                global $woocommerce;
                //get the coupon details by coupon code...
                $couponDetails = new WC_Coupon($value);
                //get the coupon id...
                $couponId = $couponDetails->id;
                //check coupon id exist...
                if(!empty($couponId)){
                    //check referral tracking association in enable in coupon....
                    $checkReferralAssociationEnable = get_post_meta($couponId,'enable_referral_association',true);
                    //get the associated referral partner id with coupon.....
                    $getReferralPartnerId = get_post_meta($couponId,'associated_referral_partner',true);
                    //check referral partner association enable or referral partner id exist....
                    if(!empty($checkReferralAssociationEnable) && $checkReferralAssociationEnable == 'yes'
                        && !empty($getReferralPartnerId) && !headers_sent()){
                        //override the affiliate id.....
                        setcookie('affiliateId', $getReferralPartnerId, time() + 3600, "/", $_SERVER['SERVER_NAME']);
                        break;//break the loop if condition is met....
                    }
                }
            }
        }    
    }
    
    // Validate email is in valid format or not 
    validate_email($order_email,$callback_purpose,$wooconnectionLogger);

    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
    $orderContactId = checkAddContactApp($access_token,$order_email,$callback_purpose);

    //Woocommerce Standard trigger : Get the call name and integration name of goal "Woocommerce Successful Order"... 
    $generalSuccessfullOrderTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_GENERAL,'Order Successful');

    //Define variables....
    $generalSuccessfullOrderIntegrationName = '';
    $generalSuccessfullOrderCallName = '';

    // Check call name of wooconnection goal is exist or not if exist...
    if(isset($generalSuccessfullOrderTrigger) && !empty($generalSuccessfullOrderTrigger)){
        
        //Get and set the wooconnection goal integration name
        if(isset($generalSuccessfullOrderTrigger[0]->wc_integration_name) && !empty($generalSuccessfullOrderTrigger[0]->wc_integration_name)){
            $generalSuccessfullOrderIntegrationName = $generalSuccessfullOrderTrigger[0]->wc_integration_name;
        }

        //Get and set the wooconnection goal call name
        if(isset($generalSuccessfullOrderTrigger[0]->wc_call_name) && !empty($generalSuccessfullOrderTrigger[0]->wc_call_name)){
            $generalSuccessfullOrderCallName = $generalSuccessfullOrderTrigger[0]->wc_call_name;
        }    
    }

    //check if contact id is exist then hit the trigger....
    if(isset($orderContactId) && !empty($orderContactId)) {
        //check relation of current order with infusionsoft/keap application order.....
        $orderRelationId = get_post_meta($orderid, 'is_kp_order_relation', true);

        //get the payment gateway method name......
        $gatewayId = get_post_meta($orderid,'Custom Payment Gateway',true);

        if(empty($orderRelationId) || $gatewayId == 'infusionsoft_keap'){
            //define empty variable....
            $referralAffiliateId = '';
            $affiliateCode = '';
            $customField = array();

            //first check "affiliateId" exist in cookie....
            $cookieDetail = getCookieValue('affiliateId');
            if(!empty($cookieDetail)){
                $cookieDetailsArray = explode(';', $cookieDetail[0]);
                if(isset($cookieDetailsArray) && !empty($cookieDetailsArray)){
                    $referralAffiliateId = $cookieDetailsArray[0];
                }
            }else{
                if(isset($_COOKIE["affiliateId"])){
                    $referralAffiliateId = $_COOKIE['affiliateId'];
                }
            }
            
            if(isset($referralAffiliateId) && !empty($referralAffiliateId)){
                $affiliateCode = getAffiliateDetails($access_token,$referralAffiliateId);
                //Woocommerce Order Trigger : Get the integraton name and the call name of trigger "Referral Partner Order" 
                $referralPartnerOrderTrigger = orderTriggerReferralPartner($access_token,$referralAffiliateId,$orderContactId,$wooconnectionLogger);
            }
            
            if(isset($affiliateCode) && !empty($affiliateCode)){
                $customField['ReferralCode'] = $affiliateCode;
                updateContactCustomFields($access_token,$orderContactId,$customField);
                //check header is not sent....
                if(!headers_sent()){
                    //empty the cookie "affiliateId" value...
                    setcookie('affiliateId','',time()-99999,'/',$_SERVER['SERVER_NAME']);
                }   
            }else{//empty the affiliate variable and also empty it from the cookie.....
                $referralAffiliateId = '';
                //check header is not sent....
                if(!headers_sent()){
                    //empty the cookie "affiliateId" value...
                    setcookie('affiliateId','',time()-99999,'/',$_SERVER['SERVER_NAME']);
                }
            }
            
            //get order data and update the contact information,,
            $order_data = $order->get_data();
            
            // Check wooconnection integration name and call name of goal is exist or not if exist then hit the achieveGoal.
            if(!empty($generalSuccessfullOrderIntegrationName) && !empty($generalSuccessfullOrderCallName))
            {
                $generallSuccessfullOrderTriggerResponse = achieveTriggerGoal($access_token,$generalSuccessfullOrderIntegrationName,$generalSuccessfullOrderCallName,$orderContactId,$callback_purpose);
                if(!empty($generallSuccessfullOrderTriggerResponse)){
                    if(empty($generallSuccessfullOrderTriggerResponse[0]['success'])){
                        //Campign goal is not exist in infusionsoft/keap application then store the logs..
                        if(isset($generallSuccessfullOrderTriggerResponse[0]['message']) && !empty($generallSuccessfullOrderTriggerResponse[0]['message'])){
                            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Woocommerce Successful Order : Process of wooconnection successful order trigger is failed where contact id is '.$orderContactId.' because '.$generallSuccessfullOrderTriggerResponse[0]['message'].'');    
                        }else{
                            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Woocommerce Successful Order : Process of wooconnection successful order trigger is failed where contact id is '.$orderContactId.'');
                        }
                        
                    }
                }
            }
            
            //Call the common function to hit the any purchase trigger....
            $anyPurchaseTrigger = orderTriggerAnyPurchase($orderContactId,$access_token,$wooconnectionLogger);

            //Below code is used to push user in product purchase goal to remove from cart abandon follow up process.
            $callback_purchase_follow_up = 'Wooconnection Successful Order Follow Up : Process to push user in purchase goal to remove user from follow up sequence';
            $generalSuccessfullOrderFollowUpResponse = achieveTriggerGoal($access_token,FOLLOW_UP_INTEGRATION_NAME,FOLLOW_UP_PURCHASE_CALL_NAME,$orderContactId,$callback_purchase_follow_up);
            if(!empty($generalSuccessfullOrderFollowUpResponse)){
                if(empty($generalSuccessfullOrderFollowUpResponse[0]['success'])){
                    if(isset($generalSuccessfullOrderFollowUpResponse[0]['message']) && !empty($generalSuccessfullOrderFollowUpResponse[0]['success'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Successful order Follow Up : Process to push user in purchase goal to remove user from follow up sequence is failed where contact id is '.$orderContactId.' because '.$generalSuccessfullOrderFollowUpResponse[0]['message'].'');   
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft','Wooconnection Successful order Follow Up : Process to push user in purchase goal to remove user from follow up sequence if failed where contact id is '.$orderContactId.'');
                    }
                }    
            }
            
            //add goals form specfic coupons...
            if(!empty($orderAssociatedCoupons)){
                foreach ($orderAssociatedCoupons as $key => $value) {
                    if(!empty($value)){
                        //Call the common function to hit the coupon applied trigger....
                        $couponName = 'coupon'.substr($value, 0, 34);
                        $couponApplyTrigger = orderTriggerCouponApply($couponName,$orderContactId,$access_token,$wooconnectionLogger);
                    }
                }
            }
            
            //get the payment method to proceed next........
            $payment_method = $order->get_payment_method();
            //check if payment gateway is not equal to infusionsoft or keap then proceed next.....
            if($payment_method != "infusionsoft_keap") {

                //Get the order items from order then execute loop to create the order items array....
                if ( sizeof( $products_items = $order->get_items() ) > 0 ) {
                    foreach($products_items as $item_id => $item)
                    {
                        $parent_product_id = '';
                        if(!empty($item->get_variation_id())){
                            $product_id = $item->get_variation_id();    
                            $parent_product_id = $item->get_product_id();
                        }else{
                            $product_id = $item->get_product_id(); 
                        }
                        $product = wc_get_product($product_id);//get the prouct details...
                        $productDesc = strip_tags($product->get_description());//product description..
                        $productPrice = round($product->get_price(),2);//get product price....
                        $productQuan = $item['quantity']; // Get the item quantity....
                        $productIdCheck = checkAddProductIsKp($access_token,$product,$parent_product_id,$applicationEdition);//get the related  product id on the basis of relation with infusionsoft/keap application product...
                        $productTitle = $product->get_title();//get product title..
                        //push product details into array/......
                        $itemsArray[] = array('description' => $productDesc, 'price' => $productPrice, 'product_id' => $productIdCheck, 'quantity' => $productQuan);
                        //get product sku..
                        $length = SKU_LENGHT_SPECIFIC_PRODUCT;
                        $productSku = get_set_product_sku($item['product_id'],$length);
                        if(isset($productSku) && !empty($productSku)){
                            //Call the common function to hit the specific product purchase trigger....
                            $specificPurchaseTrigger = orderTriggerSpecificPurchase($productSku,$orderContactId,$access_token,$wooconnectionLogger);
                        }
                    }
                    //create order items json....
                    $jsonOrderItems = json_encode($itemsArray);
                    //create order in infusionsoft/keap application.....
                    $iskporderId = createOrder($orderid,$orderContactId,$jsonOrderItems,$access_token,$referralAffiliateId);
                    //update order relation between woocommerce order and infusionsoft/keap application order.....
                    if(!empty($iskporderId)){
                        //Update relation .....
                        update_post_meta($orderid, 'is_kp_order_relation', $iskporderId);
                        //Check of tax exist with current order....
                        if(isset($orderTaxDetails) && !empty($orderTaxDetails)){
                            //Call the common function to add order itema as a tax....
                            addOrderItems($access_token,$iskporderId, NON_PRODUCT_ID, ITEM_TYPE_TAX, $orderTaxDetails, ORDER_ITEM_QUANTITY, 'Order Tax',ITEM_TAX_NOTES);
                        }
                        //Check discount on order.....
                        if(isset($orderDiscountDetails) && !empty($orderDiscountDetails)){
                            $discountDetected = $orderDiscountDetails;
                            $discountDetected *= -1;
                            //Call the common function to add order itema as a discount....
                            addOrderItems($access_token,$iskporderId, NON_PRODUCT_ID, ITEM_TYPE_DISCOUNT, $discountDetected, ORDER_ITEM_QUANTITY, $discountDesc, ITEM_DISCOUNT_NOTES);
                        }
                       
                        global $wpdb;//define variable for query......
                        //execute query to get the details of order related custom fields.....
                        $orderCustomFields =  $wpdb->get_results($wpdb->prepare("SELECT *  FROM `wp_postmeta` WHERE `post_id` = $orderid AND `meta_key` LIKE '%orderCFields_%'"));
                        $orderCFields = array();//define empty array....
                        if(isset($orderCustomFields) && !empty($orderCustomFields)){
                          //execute loop....
                          foreach ($orderCustomFields as $key => $value) {
                              $cfieldKey = explode('orderCFields', $value->meta_key);
                              $orderCFields[$cfieldKey[1]] = $value->meta_value;
                          }
                        }

                        //check order related custom fields exist with data or not....
                        if(isset($orderCFields) && !empty($orderCFields) && !empty($access_token)){
                            //call the common function to update the order related custom fields in infusionsoft.......
                            $responseCheck = updateOrderCustomFields($access_token, $iskporderId, $orderCFields);
                        }

                        //get the payment method....
                        $paymentMethodTitle = $order->get_payment_method_title();
                        //get the amount ownd by the application order......
                        $totalAmountOwned = getOrderAmountOwned($access_token,$iskporderId,$wooconnectionLogger);
                        //then charge the payment....
                        $chargeManualPayment = chargePaymentManual($access_token,$iskporderId,$totalAmountOwned,$paymentMethodTitle,$paymentMethodTitle,$wooconnectionLogger);
                    }
                }
            }
        }
    }else{
        return false;
    }
    return true;
}

//Woocommerce hook : This action is triggered when a status mark as failed.
add_action('woocommerce_order_status_failed', 'woocommerce_trigger_status_failed_hook', 15, 2);
//Function Definiation : woocommerce_trigger_status_failed_hook
function woocommerce_trigger_status_failed_hook($order_id, $order)
{
    // Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    
    //Concate a error message to store the logs...
    $callback_purpose = 'Wooconnection Failed order : Process of failed order trigger';
    
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

    // Get the order
    $order_email = $order->get_billing_email();
    
    // Validate email is in valid format or not 
    validate_email($order_email,$callback_purpose,$wooconnectionLogger);

    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
    $orderContactId = checkAddContactApp($access_token,$order_email,$callback_purpose);

    //Woocommerce Standard trigger : Get the call name and integration name of goal "Order Failed"... 
    $generalFailOrderTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_GENERAL,'Order Failed');

    //Define variables....
    $generalFailOrderIntegrationName = '';
    $generalFailOrderCallName = '';

    //Check campaign goal details...
    if(isset($generalFailOrderTrigger) && !empty($generalFailOrderTrigger)){
        
        //Get and set the wooconnection goal integration name
        if(isset($generalFailOrderTrigger[0]->wc_integration_name) && !empty($generalFailOrderTrigger[0]->wc_integration_name)){
            $generalFailOrderIntegrationName = $generalFailOrderTrigger[0]->wc_integration_name;
        }

        //Get and set the wooconnection goal call name
        if(isset($generalFailOrderTrigger[0]->wc_call_name) && !empty($generalFailOrderTrigger[0]->wc_call_name)){
            $generalFailOrderCallName = $generalFailOrderTrigger[0]->wc_call_name;
        }    
    }

    //check if contact id is exist then hit the trigger....
    if(isset($orderContactId) && !empty($orderContactId)) {
        //define empty variable....
        $referralAffiliateId = '';
        $affiliateCode = '';
        $customField = array();

        $cookieData = getCookieValue('affiliateId');//get the cookie details....
        if(isset($cookieData) && !empty($cookieData)){
            $cookieDataArray = explde(';',$cookieData[0]);//explode the cookie....
            if(isset($cookieDataArray) && !empty($cookieDataArray)){
                $referralAffiliateId = $cookieDataArray[0];
            }
        }else{
            if(isset($_COOKIE['affiliateId'])){
                $referralAffiliateId = $_COOKIE['affiliateId'];
            }
        }

        //check affiliate id exist....
        if(isset($referralAffiliateId) && !empty($referralAffiliateId)){
            //then get the details of it by id.....
            $affiliateCode = getAffiliateDetails($access_token,$referralAffiliateId);
        }
        
        if(isset($affiliateCode) && !empty($affiliateCode)){
            $customField['ReferralCode'] = $affiliateCode;
            updateContactCustomFields($access_token,$orderContactId,$customField);
        }


        // Check wooconnection integration name and call name of goal is exist or not if exist then hit the achieveGoal.
        if(!empty($generalFailOrderIntegrationName) && !empty($generalFailOrderCallName))
        {
            $generalFailTriggerResponse = achieveTriggerGoal($access_token,$generalFailOrderIntegrationName,$generalFailOrderCallName,$orderContactId,$callback_purpose);
            if(!empty($generalFailTriggerResponse)){
                if(empty($generalFailTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($generalFailTriggerResponse[0]['message']) && !empty($generalFailTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Woocommerce Failed Order : Process of wooconnection failed order trigger is failed where contact id is '.$orderContactId.' because '.$generalFailTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Woocommerce Failed Order : Process of wooconnection failed order trigger is failed where contact id is '.$orderContactId.'');
                    }
                    
                }
            }    
        }
        
        //check relation of current order with infusionsoft/keap application order.....
        $orderRelationId = get_post_meta($order_id, 'is_kp_order_relation', true);
        if(!empty($orderRelationId)){
            $callback_purpose_order_notes = 'On Wooconnection Failed order : Process of add order notes when #'.$order_id.' status changed to failed.';
            //first get the order items listing......
            $returnData = getApplicationOrderDetails($access_token,$orderRelationId,$callback_purpose_order_notes);
            //then check order items array is not empty.....
            if(isset($returnData) && !empty($returnData)){
                $itemTitle = 'Items Deleted of order #'.$orderRelationId;//item title..
                //exceute loop on product items array to add the notes for current order....
                $currencySign = get_woocommerce_currency_symbol();//Get currency symbol....
                $itemsstring = array();//array to save the list of order items....
                foreach ($returnData as $key => $value) {
                    $noteText = 'Order item is '.$value['type'].' and name of item is '.$value['name'].' and price of item is '.$currencySign.$value['price'].'';
                    $itemsstring[] = $noteText;//push item string to array....
                }
                //once the order status change to failed then need to add notes for contact in infusionsoft/keap application.... 
                addContactNotes($access_token,$orderContactId,$itemsstring,$itemTitle,$callback_purpose_order_notes);
            }
            //after add notes of order items for order ...then needs to delete the order from infusionsoft/keap application....
            $callback_purpose_delete_order = 'On Wooconnection Failed order : Process of delete order from infusionsoft/keap application when #'.$order_id.' status changed to failed.';
            $returnDataId = deleteApplicationOrder($access_token,$orderRelationId,$callback_purpose_delete_order);
            //once the order is deleted then needs to add note for current order with relative product is deleted...
            $noteTextOrder = 'The infusionsoft/keap application relative order #'.$orderRelationId.' of this order is deleted';
            $order->add_order_note( $noteTextOrder );
        }
    }else{
        return false;
    }
    return false;
}
?>