<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Woocommerce hook : This action is triggered when user reaches checkout page.
add_action('woocommerce_before_checkout_form', 'wooconnection_user_arrive_checkout', 10, 0);

//Function Definiation : wooconnection_user_arrive_checkout_page
function wooconnection_user_arrive_checkout(){
	// Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    
    //Concate a error message to store the logs...
    $callback_purpose = 'Wooconnection Reached Checkout : Process of wooconnection reached checkout trigger';
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
    $standardReachedCheckoutTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_CART,'Checkout Page View');

    //Define variables....
    $standardReachedCheckoutIntegrationName = '';
    $standardReachedCheckoutCallName = '';

    //Check campaign goal details...
    if(isset($standardReachedCheckoutTrigger) && !empty($standardReachedCheckoutTrigger)){
        
        //Get and set the wooconnection goal integration name
        if(isset($standardReachedCheckoutTrigger[0]->wc_integration_name) && !empty($standardReachedCheckoutTrigger[0]->wc_integration_name)){
            $standardReachedCheckoutIntegrationName = $standardReachedCheckoutTrigger[0]->wc_integration_name;
        }

        //Get and set the wooconnection goal call name
        if(isset($standardReachedCheckoutTrigger[0]->wc_call_name) && !empty($standardReachedCheckoutTrigger[0]->wc_call_name)){
            $standardReachedCheckoutCallName = $standardReachedCheckoutTrigger[0]->wc_call_name;
        }    
    }


    //get or set the reached checkout page user email..
    $reachedUseremail = get_set_user_email();
    if(empty($reachedUseremail)){
        $reachedUseremail = "";
    }

    // Validate email is in valid format or not 
    validate_email($reachedUseremail,$callback_purpose,$wooconnectionLogger);
    
    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
    $reachedContactId = checkAddContactApp($access_token,$reachedUseremail,$callback_purpose);


    //check if contact id is exist then hit the trigger....
    if(isset($reachedContactId) && !empty($reachedContactId)) {
        if(!empty($standardReachedCheckoutIntegrationName) && !empty($standardReachedCheckoutCallName))
        {
        	$standardReachedCheckoutTriggerResponse = achieveTriggerGoal($access_token,$standardReachedCheckoutIntegrationName,$standardReachedCheckoutCallName,$reachedContactId,$callback_purpose);
            if(!empty($standardReachedCheckoutTriggerResponse)){
                if(empty($standardReachedCheckoutTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($standardReachedCheckoutTriggerResponse[0]['message']) && !empty($standardReachedCheckoutTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Reached Checkout : Process of wooconnection reached checkout trigger is failed where contact id is '.$reachedContactId.' because '.$standardReachedCheckoutTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Reached Checkout : Process of wooconnection reached checkout trigger is failed where contact id is '.$reachedContactId.'');
                    }
                    
                }
            }
        }

        //Below code is used to push user in reached checkout page process for cart abandon follow up process.
        $callback_checkout_follow_up = 'Wooconnection Reached Checkout Follow Up : Process to push user in reached checkout follow up';
        $standardReachedCheckoutFollowUpResponse = achieveTriggerGoal($access_token,FOLLOW_UP_INTEGRATION_NAME,FOLLOW_UP_CHECKOUT_CALL_NAME,$reachedContactId,$callback_checkout_follow_up)
        if(!empty($standardReachedCheckoutFollowUpResponse)){
            if(empty($standardReachedCheckoutFollowUpResponse[0]['success'])){
                if(isset($standardReachedCheckoutFollowUpResponse[0]['message']) && !empty($standardReachedCheckoutFollowUpResponse[0]['message'])){
                    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft','Wooconnection Reached Checkout Follow Up : Process to push user in reached checkout follow up is failed where contact id is '.$reachedContactId.' because '.$standardReachedCheckoutFollowUpResponse[0]['message'].'');
                }else{
                    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft','Wooconnection Reached Checkout Follow Up : Process to push user in reached checkout follow up is failed where contact id is '.$reachedContactId.'');
                }
            }
        }
    }
    return true;
}
?>