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
    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
    }

    // Get the order details
    $order = new WC_Order( $orderid );
    $order_email = $order->get_billing_email();
    $order_tax_details = (float) $order->get_total_tax();
    // echo "<pre>";
    // print_r($order_tax_details);
    // die();
    
    // Validate email is in valid format or not 
    validate_email($order_email,$callback_purpose,$wooconnectionLogger);

    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
    $orderContactId = checkAddContactApp($access_token,$order_email,$callback_purpose);

    //Woocommerce Standard trigger : Get the call name and integration name of goal "Woocommerce Successful Order"... 
    $generalSuccessfullOrderTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_GENERAL,'Order Successful');

    //Define variables....
    $generalSuccessfullOrderIntegrationName = '';
    $generalSuccessfullOrderCallName = '';

    //Check campaign goal details...
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
        //get order data and update the contact information,,
        $order_data = $order->get_data();
        updateContact($orderContactId,$order_data,$access_token);

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
        //$apiOrderId = createBlankOrder($orderid,$orderContactId,$access_token);
        if ( sizeof( $products_items = $order->get_items() ) > 0 ) {
            foreach($products_items as $item_id => $item)
            {
                // echo "<pre>";
                // print_r($item);
                $product = wc_get_product($item['product_id']);
                $productDesc = $product->get_description();//product description..
                $productPrice = round($product->get_price(),2);//get product price
                $productQuan = $item['quantity']; // Get the item quantity
                $productIdCheck = checkAddProductIsKp($access_token,$product);
                $productTitle = $product->get_title();//get product title..
                //$productTax = $item['taxes'];//product description..
                // $taxes = $item->get_taxes();
                // // Loop through taxes array to get the right label
                // foreach( $taxes['subtotal'] as $rate_id => $tax ){
                //     $taxexArray[$productIdCheck] = $tax;
                // }
                // $productIdsArray[] = $productIdCheck;
                // $productQuanArray[$productIdCheck] = $productQuan;
                $itemsArray[] = array('description' => $productDesc, 'price' => $productPrice, 'product_id' => $productIdCheck, 'quantity' => $productQuan);
            }
            $jsonOrderItems = json_encode($itemsArray);
            $iskporderId = createOrder($orderid,$orderContactId,$jsonOrderItems,$access_token);
            if(!empty($iskporderId)){
                update_post_meta($orderid, 'is_kp_order_relation', $iskporderId);
            }
            if(!empty($order_tax_details) && !empty($orderId)){
                // $callback_purpose = 'Order Item Tax : Process of add order item tax for order #'.$orderId;
                // foreach ($productIdsArray as $key => $value) {
                //    if(isset($taxexArray[$value]) && !empty($taxexArray[$value])){
                //         $finalPrice = ($taxexArray[$value]) / $productQuanArray[$value];
                //         $orderTaxJson = '{"description": "Order Tax","price": '.round($finalPrice,2).',"product_id": '.$value.',"quantity": '.$productQuanArray[$value].'}';
                //         addOrderItem($orderId,$orderTaxJson,$access_token,$callback_purpose,LOG_TYPE_FRONT_END,$wooconnectionLogger);
                //    }
                //     //echo $taxexArray[$value].'<br>';
                // }
                // $orderTaxJson = '{"description": "Order Tax","price": '.round($order_tax_details,2).',"product_id": 0,"quantity": 1}';
                // addOrderItem($orderId,$orderTaxJson,$access_token,$callback_purpose,LOG_TYPE_FRONT_END,$wooconnectionLogger);
            }
            //die();
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

    //Woocommerce Standard trigger : Get the call name and integration name of goal "Woocommerce Successful Order"... 
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

    //check contact id..
    if(isset($orderContactId) && !empty($orderContactId)) {
        // Check call name of wooconnection goal is exist or not if exist then hit the achieveGoal where integration name is purchaseProductIntegrationName and call name sku of product...
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
    }else{
        return false;
    }
    return true;

}
?>