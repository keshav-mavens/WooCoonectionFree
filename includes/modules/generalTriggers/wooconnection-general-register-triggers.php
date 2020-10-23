<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Woocommerce hook : This action is triggered when register process is hit from front end, It may be hit from registration page or at the time of checkout if "allow user registration during checkout"  option is enable in woocommerce settings.
add_action('user_register','wooconnection_trigger_register_user', 10, 1);

//Function Definiation : wooconnection_trigger_register_user
function wooconnection_trigger_register_user($new_user_id){
    // Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    
    //Concate a error message to store the logs...
    $callback_purpose = 'Wooconnection Registration : Process of wooconnection registration trigger';
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

    //New user id....
    $userid = $new_user_id;

    //Woocommerce Standard trigger : Get the call name and integration name of goal "Woocommerce User Registration"... 
    $generalRegistrationNewUserTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_GENERAL,'New User Registration');
    
    //Define variables
    $generalRegistrationNewUserIntegrationName = '';
    $generalRegistrationNewUserCallName = '';

    //Check campaign goal details...
    if(isset($generalRegistrationNewUserTrigger) && !empty($generalRegistrationNewUserTrigger)){
        //Get and set the wooconnection goal integration name
        if(isset($generalRegistrationNewUserTrigger[0]->wc_integration_name) && !empty($generalRegistrationNewUserTrigger[0]->wc_integration_name)){
            $generalRegistrationNewUserIntegrationName = $generalRegistrationNewUserTrigger[0]->wc_integration_name;
        }

        //Get and set the wooconnection goal call name
        if(isset($generalRegistrationNewUserTrigger[0]->wc_call_name) && !empty($generalRegistrationNewUserTrigger[0]->wc_call_name)){
            $generalRegistrationNewUserCallName = $generalRegistrationNewUserTrigger[0]->wc_call_name;
        }
    }

    //Call the main trigger function..
    wooconnection_general_user_registration_trigger($userid,$generalRegistrationNewUserIntegrationName,$generalRegistrationNewUserCallName,$wooconnectionLogger,$access_token,$callback_purpose);
}

//Trigger Wooconnection Goal : This function is used to trigger goal "Woocommerce User Registration"......
function wooconnection_general_user_registration_trigger($userid,$generalRegistrationNewUserIntegrationName,$generalRegistrationNewUserCallName,$wooconnectionLogger,$access_token,$callback_purpose){
    
    //check parameter user id is exist or not if not exist then get the current user if on the basis of logged in user id..
    $register_user_id = "";
    if(empty($userid)){
        $currentLoginUser = wp_get_current_user();
        $register_user_id = $currentLoginUser->ID;    
    }else{
        $register_user_id = $userid;
    }

    //Get user details on the basis of set current user id...
    $registerUserInformation = array();
    if(isset($register_user_id) && !empty($register_user_id)){
       $registerUserInformation  = get_user_by('id',$register_user_id);
    }
    
    //check current user information is exist
    if(isset($registerUserInformation) && !empty($registerUserInformation)){
        
        //check "user_email" is exist or not in information array if not exist then need to check from post data where user_email exist in post data
        $regsiterUserEmail = "";
        if(isset($registerUserInformation->user_email) && !empty($registerUserInformation->user_email)){
            $regsiterUserEmail = $registerUserInformation->user_email;
        }else{
            $regsiterUserEmail  = getPostData($_POST,'email');
        }

        // Validate email is in valid format or not 
        validate_email($regsiterUserEmail,$callback_purpose,$wooconnectionLogger);
        
        //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
        $registerContactId = checkAddContactApp($access_token,$regsiterUserEmail,$callback_purpose);

        //check if contact id is exist then hit the trigger....
        if(isset($registerContactId) && !empty($registerContactId)) {
            // Check wooconnection integration name and call name of goal is exist or not if exist then hit the achieveGoal.
            if(!empty($generalRegistrationNewUserIntegrationName) && !empty($generalRegistrationNewUserCallName))
            {
                
                $generalRegistrationTriggerResponse = achieveTriggerGoal($access_token,$generalRegistrationNewUserIntegrationName,$generalRegistrationNewUserCallName,$registerContactId,$callback_purpose);
                if(!empty($generalRegistrationTriggerResponse)){
                    if(empty($generalRegistrationTriggerResponse[0]['success'])){
                        //Campign goal is not exist in infusionsoft/keap application then store the logs..
                        if(isset($generalRegistrationTriggerResponse[0]['message']) && !empty($generalRegistrationTriggerResponse[0]['message'])){
                            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Registration : Process of wooconnection registration trigger is failed where contact id is '.$registerContactId.' because '.$generalRegistrationTriggerResponse[0]['message'].'');    
                        }else{
                            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Registration : Process of wooconnection registration trigger is failed where contact id is '.$registerContactId.'');
                        }
                        
                    }
                }
            }
        }else{
            return false;
        }
    }
    return true;
}


//get the email from post data and return it..
function getPostData($postData,$dataType){
  $returnValue = "";
  if(isset($postData) && !empty($postData)){
        if ($dataType == 'email') {
          if (isset($postData['user_email']) && !empty($postData['user_email'])) {
             $returnValue = $postData['user_email'];
          }elseif (isset($postData['email']) && !empty($postData['email'])) {
             $returnValue = $postData['email'];
          }elseif (isset($postData['billing_email']) && !empty($postData['billing_email'])) {
             $returnValue = $postData['billing_email'];
          }
       }
  }
  return $returnValue;
}

?>