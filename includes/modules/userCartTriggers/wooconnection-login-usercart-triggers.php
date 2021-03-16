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
                if(!isset($standardEmptiedCartTriggerResponse['fault'])){
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

        //Below code is used to push user in empty cart trigger to remove from cart abandon follow up process.....
        $callback_empty_cart_follow_up = 'Wooconnection Empty Cart Follow Up : Process to push user in empty cart goal to remove user from follow up sequence';
        $standardEmptiedCartFollowUpResponse = achieveTriggerGoal($access_token,FOLLOW_UP_INTEGRATION_NAME,FOLLOW_UP_EMPTY_CART_CALL_NAME,$emptiedCartContactId,$callback_empty_cart_follow_up);
        if(!empty($standardEmptiedCartFollowUpResponse)){
            if(!isset($standardEmptiedCartFollowUpResponse['fault'])){
                if(empty($standardEmptiedCartFollowUpResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($standardEmptiedCartFollowUpResponse[0]['message']) && !empty($standardEmptiedCartFollowUpResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Empty Cart Follow Up : Process to push user in empty cart goal to remove user from follow up sequence is failed where contact id is '.$emptiedCartContactId.' because '.$standardEmptiedCartFollowUpResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Empty Cart Follow Up : Process to push user in empty cart goal to remove user from follow up sequence is failed where contact id is '.$emptiedCartContactId.'');
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
    if(!session_id()) {
        session_start();
    }
    $appContactId = '';
    if(isset($_SESSION['app_contact_id']) && !empty($_SESSION['app_contact_id'])){
        $appContactId = $_SESSION['app_contact_id'];
    }

    $access_token = '';
    if(isset($_SESSION['auth_app_session']) && !empty($_SESSION['auth_app_session'])){
        $access_token = $_SESSION['auth_app_session'];
    }

    if(!empty($appContactId) && !empty($access_token)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        
        $callback_purpose = 'Wooconnection Add Cart Item : Process of wooconnection add item/product to cart trigger';

        //Woocommerce Cart trigger : Get the call name and integration name of goal "Item Added to Cart"... 
        $standardAddItemCartTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_CART,'Item Added to Cart');

        //Define variables....
        $standardAddItemCartIntegrationName = '';
        
        //Check campaign goal details...
        if(isset($standardAddItemCartTrigger) && !empty($standardAddItemCartTrigger)){
        
            //Get and set the wooconnection goal integration name
            if(isset($standardAddItemCartTrigger[0]->wc_integration_name) && !empty($standardAddItemCartTrigger[0]->wc_integration_name)){
                $standardAddItemCartIntegrationName = $standardAddItemCartTrigger[0]->wc_integration_name;
            }
        }

        //check the added product id....
        $addedProductId = '';
        if(isset($_POST['product_id']) && !empty($_POST['product_id'])){//with ajax....
            $addedProductId = $_POST['product_id'];
        }elseif (!empty($_GET['add-to-cart'])) {//without ajax.....
            $addedProductId = $_GET['add-to-cart'];
        }elseif (!empty($_POST['add-to-cart'])) {//from product detail page.....
            $addedProductId = $_POST['add-to-cart'];
        }
        
        $productSku = '';
        if(isset($addedProductId) && !empty($addedProductId)){
            $productSku = get_set_product_sku($addedProductId,SKU_LENGHT_CART_ITEM);
        }

        if(isset($productSku) && !empty($productSku) && !empty($standardAddItemCartIntegrationName)){
            $productSku = 'added'.$productSku;
            $standardAddItemCartTriggerResponse = achieveTriggerGoal($access_token,$standardAddItemCartIntegrationName,$productSku,$appContactId,$callback_purpose);
            $saveLogs = false;//set default value.....
            if(!empty($standardAddItemCartTriggerResponse)){
                if(!isset($standardAddItemCartTriggerResponse['fault'])){
                    if(empty($standardAddItemCartTriggerResponse[0]['success'])){
                        $saveLogs = true;//set true....
                    }
                }else{
                    if(!empty($standardAddItemCartTriggerResponse['fault']['faultstring']) && $standardAddItemCartTriggerResponse['fault']['faultstring'] == 'Invalid Access Token'){
                        $applicationAuthenticationDetails = getAuthenticationDetails();
                        if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
                            $access_token = $applicationAuthenticationDetails[0]->user_access_token;
                            $_SESSION['auth_app_session'] = $access_token;
                        }
                        $standardAddItemCartTriggerResponse = achieveTriggerGoal($access_token,$standardAddItemCartIntegrationName,$productSku,$appContactId,$callback_purpose);
                        
                        if(empty($standardAddItemCartTriggerResponse[0]['success'])){
                            $saveLogs = true;//set true....
                        }
                    }
                }
            }
            if($saveLogs == true){//check if value is true....
                //Campign goal is not exist in infusionsoft/keap application then store the logs..
                if(isset($standardAddItemCartTriggerResponse[0]['message']) && !empty($standardAddItemCartTriggerResponse[0]['message'])){
                    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Add Cart Item : Process of wooconnection add item/product to cart trigger is failed where contact id is '.$appContactId.' because '.$standardAddItemCartTriggerResponse[0]['message'].'');    
                }else{
                    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Add Cart Item : Process of wooconnection add item/product to cart trigger is failed where contact id is '.$appContactId.'');
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
            $productSku = get_set_product_sku($_POST['comment_post_ID'],SKU_LENGHT_REVIEW);
            if(!empty($standardReviewItemCartIntegrationName) && !empty($productSku))
            {
                $productSku = 'review'.$productSku;
                $standardAddItemCartTriggerResponse = achieveTriggerGoal($access_token,$standardReviewItemCartIntegrationName,$productSku,$reviewLeftCartContactId,$callback_purpose);
                if(!empty($standardAddItemCartTriggerResponse)){
                    if(!isset($standardAddItemCartTriggerResponse['fault'])){
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
            }

            //Add commant as a not for contact in infusionsoft/keap application.
            if(!empty($comment_text) && !empty($comment_parent_product)){
                $productName = get_the_title($comment_parent_product);//get the product name....
                $itemTitle = 'Review posted for product '.$productName;//set notes title....
                //Add note for contact with comment text or product name.....
                addContactNotes($access_token,$reviewLeftCartContactId,$comment_text,$itemTitle,$callback_purpose,NOTE_TYPE_REVIEW);
            }
        }
    }
    return true;
}

//Woocommerce Hook : This action is triggered when to show the saved cart products...
add_action('template_redirect','redirect_user_cart');

//Function Definition : redirect_user_cart.
function redirect_user_cart(){
    //on template redirect first check whether the page is caty or not if cart then start the process of saved cart items...
    if(is_page('cart') || is_cart()){
        //check user email is exist in query string or not if try then try to find the saved cart items.....
        if(isset($_GET['user_email'])){
            $userEmail = $_GET['user_email'];
            //get the user details by email id....
            $userDetails = get_user_by('email',$userEmail);
            if(isset($userDetails) && !empty($userDetails)){
                //get the user id from user details array..
                $userId = $userDetails->ID;
                if(isset($userId) && !empty($userId)){
                    //get the user cart details from the user meta on the basis of user id....
                    $userCartDetails = get_user_meta($userId,'_woocommerce_persistent_cart_1',false);
                    //then check the cart data exist in user meta array or not, if not exist then set the cart data in session...
                    if(isset($userCartDetails[0]['cart']) && !empty($userCartDetails[0]['cart'])){
                        if(isset(WC()->session) && !WC()->session->has_session()){
                            WC()->session->set_customer_session_cookie(true);
                            WC()->session->set('cart',$userCartDetails[0]['cart']);
                            WC()->session->set('session_email',$_GET['user_email']);
                        }
                    }
                }
                //get the cart page url...
                $headTo = wc_get_cart_url();
                //call the header location function to redirect it on the cart page.....
                Header("Location: ".$headTo);
                exit();
            }
        }
    }
}

//Woocommerce Hook : This action is triggered to store the app contact id and access token in session...
add_action('wp_login','handle_user_login_process',10,2);
//Function Definition : handle_user_login_process
function handle_user_login_process($user_login,$user){
    // Get all the user roles as an array.
    $userRoles = $user->roles;//get the user roles of login user data........
    $userDetails = $user->data;//get the user personal details...... 
    $loginUserEmail = $userDetails->user_email;//get the user email from user personal details array.....
    if (!in_array('admin',$userRoles,true)) {//check if user role is not admin......
        if(isset($loginUserEmail) && !empty($loginUserEmail)){//check user email is exist then proceed next.....
            // Create instance of our wooconnection logger class to use off the whole things.
            $wooconnectionLogger = new WC_Logger();
            
            //Concate a error message to store the logs...
            $callback_purpose = 'Wooconnection : Process of add contact to infusionsoft/keap application';
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
            
            // Validate email is in valid format or not 
            validate_email($loginUserEmail,$callback_purpose,$wooconnectionLogger);
            
            //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
            $applicationContactId = checkAddContactApp($access_token,$loginUserEmail,$callback_purpose);
            if(isset($applicationContactId) && !empty($applicationContactId)){//check the application contact id is exist or not.....
                if(!session_id()) {
                    session_start();
                }
                $_SESSION['app_contact_id'] = $applicationContactId;//set the value in session.....
                $_SESSION['auth_app_session'] = $access_token;
            }
        }
    }
}

//Woocommerce Hook : This action is triggered to store the app contact id and access token in session...
add_action('wp_logout','handle_user_logout_process');
//Function Definition : handle_user_logout_process
function handle_user_logout_process() {
    if(!session_id()) {
        session_start();
    }
    //empty the sessions....
    $_SESSION['app_contact_id'] = '';
    $_SESSION['auth_app_session'] = '';
}
?>