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