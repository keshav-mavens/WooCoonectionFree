<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Woocommerce hook : This action is triggered when user reaches checkout page.
add_action('woocommerce_before_checkout_form', 'wooconnection_user_arrive_checkout', 10, 0);

//Function Definiation : wooconnection_user_arrive_checkout_page
function wooconnection_user_arrive_checkout(){
    $customSessionData = WC()->session->get('custom_data');//get the custom session data.....
    $access_token = '';
    if(isset($customSessionData['auth_app_session']) && !empty($customSessionData['auth_app_session'])){
        $access_token = $customSessionData['auth_app_session'];
    }
    
    $reachedContactId = '';
    if(isset($customSessionData['app_contact_id']) && !empty($customSessionData['app_contact_id'])){
        $reachedContactId = 'c2RYTQH6TCjl0bVs4GbAK96bYzsVwrong';//$customSessionData['app_contact_id'];
    }
    
    //check if contact id is exist then hit the trigger....
    if(isset($reachedContactId) && !empty($reachedContactId) && !empty($access_token)) {
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        
        //Concate a error message to store the logs...
        $callback_purpose = 'Wooconnection Reached Checkout : Process of wooconnection reached checkout trigger';
        
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


        if(!empty($standardReachedCheckoutIntegrationName) && !empty($standardReachedCheckoutCallName))
        {
        	$standardReachedCheckoutTriggerResponse = achieveTriggerGoal($access_token,$standardReachedCheckoutIntegrationName,$standardReachedCheckoutCallName,$reachedContactId,$callback_purpose);
            $saveCheckoutLogs = false;
            if(!empty($standardReachedCheckoutTriggerResponse)){
                if(!isset($standardReachedCheckoutTriggerResponse['fault'])){
                    if(empty($standardReachedCheckoutTriggerResponse[0]['success'])){
                        $saveCheckoutLogs = true;//set the true to save the logs....
                    }
                }else{
                    if(!empty($standardReachedCheckoutTriggerResponse['fault']['faultstring']) && $standardReachedCheckoutTriggerResponse['fault']['faultstring'] == 'Invalid Access Token'){
                        $applicationAuthenticationDetails = getAuthenticationDetails();
                        if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
                            $access_token = $applicationAuthenticationDetails[0]->user_access_token;
                            WC()->session->__unset('custom_data');//unset the previous session data....
                            //reset the session data........
                            WC()->session->set('custom_data',array('app_contact_id' => $reachedContactId, 'auth_app_session'=>$access_token));
                        }
                        $standardReachedCheckoutTriggerResponse = achieveTriggerGoal($access_token,$standardReachedCheckoutIntegrationName,$standardReachedCheckoutCallName,$reachedContactId,$callback_purpose);
                        if(empty($standardReachedCheckoutTriggerResponse[0]['success'])){
                            $saveCheckoutLogs = true;//set the true to save the logs....
                        }
                    }
                }
            }
            
            if($saveCheckoutLogs == true){//check if true then proceed next to save the logs......
                //Campign goal is not exist in infusionsoft/keap application then store the logs..
                if(isset($standardReachedCheckoutTriggerResponse[0]['message']) && !empty($standardReachedCheckoutTriggerResponse[0]['message'])){
                    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Reached Checkout : Process of wooconnection reached checkout trigger is failed where contact id is '.$reachedContactId.' because '.$standardReachedCheckoutTriggerResponse[0]['message'].'');    
                }else{
                    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Reached Checkout : Process of wooconnection reached checkout trigger is failed where contact id is '.$reachedContactId.'');
                }
            }
        }
        
        //Below code is used to push user in reached checkout page process for cart abandon follow up process.
        $callback_checkout_follow_up = 'Wooconnection Reached Checkout Follow Up : Process to push user in reached checkout follow up';
        $standardReachedCheckoutFollowUpResponse = achieveTriggerGoal($access_token,FOLLOW_UP_INTEGRATION_NAME,FOLLOW_UP_CHECKOUT_CALL_NAME,$reachedContactId,$callback_checkout_follow_up);
        $saveCheckoutFollowUpLogs = false;
        if(!empty($standardReachedCheckoutFollowUpResponse)){
            if(!isset($standardReachedCheckoutFollowUpResponse['fault'])){
                if(empty($standardReachedCheckoutFollowUpResponse[0]['success'])){
                    $saveCheckoutFollowUpLogs = true;//set true to save the logs....
                }
            }else{
                if(!empty($standardReachedCheckoutFollowUpResponse['fault']['faultstring']) && $standardReachedCheckoutFollowUpResponse['fault']['faultstring'] == 'Invalid Access Token'){
                    $applicationAuthenticationDetails = getAuthenticationDetails();
                    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
                        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
                        WC()->session->__unset('custom_data');//unset the previous session data....
                        //reset the session data........
                        WC()->session->set('custom_data',array('app_contact_id' => $reachedContactId, 'auth_app_session'=>$access_token));
                    }
                    $standardReachedCheckoutFollowUpResponse = achieveTriggerGoal($access_token,FOLLOW_UP_INTEGRATION_NAME,FOLLOW_UP_CHECKOUT_CALL_NAME,$reachedContactId,$callback_checkout_follow_up);
                    if(empty($standardReachedCheckoutFollowUpResponse[0]['success'])){
                        $saveCheckoutFollowUpLogs = true;//set true to save the logs.....
                    }
                }
            }
        }

        if($saveCheckoutFollowUpLogs == true){//check if true then proceed next to save the logs.....
            if(isset($standardReachedCheckoutFollowUpResponse[0]['message']) && !empty($standardReachedCheckoutFollowUpResponse[0]['message'])){
                $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft','Wooconnection Reached Checkout Follow Up : Process to push user in reached checkout follow up is failed where contact id is '.$reachedContactId.' because '.$standardReachedCheckoutFollowUpResponse[0]['message'].'');
            }else{
                $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft','Wooconnection Reached Checkout Follow Up : Process to push user in reached checkout follow up is failed where contact id is '.$reachedContactId.'');
            }
        }
    }
    return true;
}
?>