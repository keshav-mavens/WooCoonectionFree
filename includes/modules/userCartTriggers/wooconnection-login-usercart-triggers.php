<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Woocommerce hook : This action is triggered when empty cart event occurs.
add_action('woocommerce_cart_is_empty' , 'wooconnection_cart_empty_trigger',10, 0);

//Function Definiation : wooconnection_cart_empty_trigger
function wooconnection_cart_empty_trigger(){
	// Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    
    //Concate a error message to store the logs...
    $callback_purpose = 'Wooconnection Empty Cart : Process of wooconnection empty cart trigger';
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

    //Woocommerce Standard trigger : Get the call name and integration name of goal "Woocommerce Checkout Page"... 
    $standardEmptiedCartTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_CART,'Cart Emptied');

    //Define variables....
    $standardEmptiedCartIntegrationName = '';
    $standardEmptiedCartCallName = '';


    //Check campaign goal details...
    if(isset($standardEmptiedCartTrigger) && !empty($standardEmptiedCartTrigger)){
    
        //Get and set the wooconnection goal integration name
        if(isset($standardEmptiedCartTrigger[0]->wc_integration_name) && !empty($standardEmptiedCartTrigger[0]->wc_integration_name)){
            $standardEmptiedCartIntegrationName = $standardEmptiedCartTrigger[0]->wc_integration_name;
        }

        //Get and set the wooconnection goal call name
        if(isset($standardEmptiedCartTrigger[0]->wc_call_name) && !empty($standardEmptiedCartTrigger[0]->wc_call_name)){
            $standardEmptiedCartCallName = $standardEmptiedCartTrigger[0]->wc_call_name;
        }
    }

	//get or set the add cart user email..
    $emptiedCartUseremail = get_set_user_email();
    if(empty($emptiedCartUseremail)){
    	$emptiedCartUseremail = "";
    }

    // Validate email is in valid format or not 
    validate_email($emptiedCartUseremail,$callback_purpose,$wooconnectionLogger);
    
    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
    $emptiedCartContactId = checkAddContactApp($access_token,$emptiedCartUseremail,$callback_purpose);

    //check if contact id is exist then hit the trigger....
    if(isset($emptiedCartContactId) && !empty($emptiedCartContactId)) {
        if(!empty($standardEmptiedCartIntegrationName) && !empty($standardEmptiedCartCallName))
        {
            $standardEmptiedCartTriggerResponse = achieveTriggerGoal($access_token,$standardEmptiedCartIntegrationName,$standardEmptiedCartCallName,$emptiedCartContactId,$callback_purpose);
            if(!empty($standardEmptiedCartTriggerResponse)){
                if(empty($standardEmptiedCartTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($standardEmptiedCartTriggerResponse[0]['message']) && !empty($standardEmptiedCartTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Empty Cart : Process of wooconnection empty cart trigger is failed where contact id is '.$emptiedCartContactId.' because '.$standardEmptiedCartTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Empty Cart : Process of wooconnection empty cart trigger is failed where contact id is '.$emptiedCartContactId.'');
                    }
                    
                }
            }
        }
    }
	return true;
}
?>