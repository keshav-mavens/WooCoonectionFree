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

//Woocommerce hook : This action is triggered when specific product add to cart.Specification is work on the basis of product sku.
add_action('woocommerce_add_to_cart', 'wooconnection_cart_product_add_trigger', 10, 6);
//Function Definiation : wooconnection_cart_product_add_trigger
function wooconnection_cart_product_add_trigger(){
    // Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    
    //Concate a error message to store the logs...
    $callback_purpose = 'Wooconnection Add Cart Item : Process of wooconnection add item/product to cart trigger';
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

    //Woocommerce Cart trigger : Get the call name and integration name of goal "Item Added to Cart"... 
    $standardAddItemCartTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_CART,'Item Added to Cart');

    //Define variables....
    $standardAddItemCartIntegrationName = '';
    $standardAddItemCartCallName = '';


    //Check campaign goal details...
    if(isset($standardAddItemCartTrigger) && !empty($standardAddItemCartTrigger)){
    
        //Get and set the wooconnection goal integration name
        if(isset($standardAddItemCartTrigger[0]->wc_integration_name) && !empty($standardAddItemCartTrigger[0]->wc_integration_name)){
            $standardAddItemCartIntegrationName = $standardAddItemCartTrigger[0]->wc_integration_name;
        }

        //Get and set the wooconnection goal call name
        if(isset($standardAddItemCartTrigger[0]->wc_call_name) && !empty($standardAddItemCartTrigger[0]->wc_call_name)){
            $standardAddItemCartCallName = $standardAddItemCartTrigger[0]->wc_call_name;
        }
    }


    //get or set the add cart user email..
    $itemAddCartUseremail = get_set_user_email();
    if(empty($itemAddCartUseremail)){
        $itemAddCartUseremail = "";
    }

    // Validate email is in valid format or not 
    validate_email($itemAddCartUseremail,$callback_purpose,$wooconnectionLogger);
    
    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
    $itemAddCartContactId = checkAddContactApp($access_token,$itemAddCartUseremail,$callback_purpose);

    //check if contact id is exist then hit the trigger....
    if(isset($itemAddCartContactId) && !empty($itemAddCartContactId)) {
        $productSku = '';
        if(isset($_POST['product_sku']) && !empty($_POST['product_sku'])){
            $productSku = $_POST['product_sku'];
        }
        //if "-" is exist in product sku then replace with empty
        if (strpos($productSku, '-') !== false)
        {
            $productSku=str_replace("-", "", $productSku);
        }
        else if (strpos($productSku, '_') !== false)
        {
            $productSku=str_replace("_", "", $productSku);
        }
        else
        {
            $productSku=$productSku;
        }
        $productSku = 'added'.substr($productSku, 0,35);
        if(!empty($standardAddItemCartIntegrationName))
        {
            $standardAddItemCartTriggerResponse = achieveTriggerGoal($access_token,$standardAddItemCartIntegrationName,$productSku,$itemAddCartContactId,$callback_purpose);
            if(!empty($standardAddItemCartTriggerResponse)){
                if(empty($standardAddItemCartTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($standardAddItemCartTriggerResponse[0]['message']) && !empty($standardAddItemCartTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Add Cart Item : Process of wooconnection add item/product to cart trigger is failed where contact id is '.$itemAddCartContactId.' because '.$standardAddItemCartTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Add Cart Item : Process of wooconnection add item/product to cart trigger is failed where contact id is '.$itemAddCartContactId.'');
                    }
                    
                }
            }
        }
    }
    return true;
}


//Woocommerce hook : This action is triggered when comment is posted for particluar product.
add_action( 'comment_post', 'wooconnection_cart_product_comment_trigger', 10, 2 );
//Function Definiation : wooconnection_cart_product_comment_trigger
function wooconnection_cart_product_comment_trigger( $comment_ID, $comment_approved ){
    //check comment id....
    if(!empty($comment_ID)){
        $commentData = get_comment( intval( $comment_ID ) );//Get the comment details by comment id....
        $comment_text = $commentData->comment_content;//Get the comment 
        $comment_parent_product = $commentData->comment_post_ID;//Get the post id..
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        
        //Concate a error message to store the logs...
        $callback_purpose = 'Wooconnection Add Review Item : Process of wooconnection review left for item/product';
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

        //Woocommerce Cart trigger : Get the call name and integration name of goal "Item Added to Cart"... 
        $standardReviewItemCartTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_CART,'Review Left');

        //Define variables....
        $standardReviewItemCartIntegrationName = '';
        $standardReviewItemCartCallName = '';


        //Check campaign goal details...
        if(isset($standardReviewItemCartTrigger) && !empty($standardReviewItemCartTrigger)){
        
            //Get and set the wooconnection goal integration name
            if(isset($standardReviewItemCartTrigger[0]->wc_integration_name) && !empty($standardReviewItemCartTrigger[0]->wc_integration_name)){
                $standardReviewItemCartIntegrationName = $standardReviewItemCartTrigger[0]->wc_integration_name;
            }

            //Get and set the wooconnection goal call name
            if(isset($standardReviewItemCartTrigger[0]->wc_call_name) && !empty($standardReviewItemCartTrigger[0]->wc_call_name)){
                $standardReviewItemCartCallName = $standardReviewItemCartTrigger[0]->wc_call_name;
            }
        }


        //get or set the add cart user email..
        $reviewLeftCartUseremail = get_set_user_email();
        if(empty($reviewLeftCartUseremail)){
            $reviewLeftCartUseremail = "";
        }

        // Validate email is in valid format or not 
        validate_email($reviewLeftCartUseremail,$callback_purpose,$wooconnectionLogger);
        
        //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
        $reviewLeftCartContactId = checkAddContactApp($access_token,$reviewLeftCartUseremail,$callback_purpose);

        //check if contact id is exist then hit the trigger....
        if(isset($reviewLeftCartContactId) && !empty($reviewLeftCartContactId)) {
            $productSkuById = get_post_meta($_POST['comment_post_ID'], '_sku', true);
            $productSku = '';
            if(isset($productSkuById) && !empty($productSkuById)){
                $productSku = $productSkuById;
            }
            //if "-" is exist in product sku then replace with empty
            if (strpos($productSku, '-') !== false)
            {
                $productSku=str_replace("-", "", $productSku);
            }
            else if (strpos($productSku, '_') !== false)
            {
                $productSku=str_replace("_", "", $productSku);
            }
            else
            {
                $productSku=$productSku;
            }
            $productSku = 'review'.substr($productSku, 0,34);
            if(!empty($standardReviewItemCartIntegrationName))
            {
                $standardAddItemCartTriggerResponse = achieveTriggerGoal($access_token,$standardReviewItemCartIntegrationName,$productSku,$reviewLeftCartContactId,$callback_purpose);
                if(!empty($standardAddItemCartTriggerResponse)){
                    if(empty($standardAddItemCartTriggerResponse[0]['success'])){
                        //Campign goal is not exist in infusionsoft/keap application then store the logs..
                        if(isset($standardAddItemCartTriggerResponse[0]['message']) && !empty($standardAddItemCartTriggerResponse[0]['message'])){
                            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Add Review Item : Process of wooconnection review left for item/product to cart trigger is failed where contact id is '.$reviewLeftCartContactId.' because '.$standardAddItemCartTriggerResponse[0]['message'].'');    
                        }else{
                            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Add Review Item : Process of wooconnection review left for item/product to cart trigger is failed where contact id is '.$reviewLeftCartContactId.'');
                        }
                        
                    }
                }
            }

            //Add commant as a not for contact in infusionsoft/keap application.
            if(!empty($comment_text) && !empty($comment_parent_product)){
                $productName = get_the_title($comment_parent_product);//get the product name....
                $itemTitle = 'Review posted for product '.$productName;//set notes title....
                //Add note for contact with comment text or product name.....
                addContactNotes($access_token,$reviewLeftCartContactId,$comment_text,$itemTitle);
            }
        }
    }
    return true;
}


?>