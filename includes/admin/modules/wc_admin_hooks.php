<?php
//Wordpress hook : This action is triggered when user create new product in woocommerce then add to application.....
add_action( 'woocommerce_process_product_meta', 'insertProductToApplication',1000,2 ); 
//Function Definiation : insertProductToApplication
function insertProductToApplication( $post_id, $post ){  
    if(!empty($post_id)){//check post id is exist
        global $wpdb,$table_prefix;
        $applicationProductsTableName = $table_prefix.'authorize_application_products';
        //Check product is update or publish....
        $productExistId = get_post_meta($post_id, 'wc_product_automation', true);
        //if product is not create yet then need to move in application
        if(empty($productExistId)){
          //first need to check whether the application authentication is done or not..
          $applicationAuthenticationDetails = getAuthenticationDetails();
          //get the access token....
          $access_token = '';
          $applicationEdition = '';//get the application edition such as keap, infusionsoft.......
          if(!empty($applicationAuthenticationDetails)){//check authentication details......
            if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
                $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
            }
            //set the value of application edition such as keap, infusionsoft...
            $applicationEdition = $applicationAuthenticationDetails[0]->user_application_edition;
          }
          
          //first need to check access token is exist then proceed next......
          if(!empty($access_token)){
            //Define the exact position of process to store the logs...
            $callback_purpose = 'Insert Woocommerce Product : Process of add woocommerce product to infusionsoft/keap application';
            //set the wooconnection log class.....
            $wooconnectionLogger = new WC_Logger();

            $wcproductdetails = wc_get_product($post_id);//Get product details..
            $wcproductPrice = $wcproductdetails->get_regular_price();//Get product price....
            $wcproductSku = $wcproductdetails->get_sku();//get product sku....
            //Check if product sku is not exist then create the sku on the basis of product slug.........
            if(isset($wcproductSku) && !empty($wcproductSku)){
              $wcproductSku = $wcproductSku;
            }else{
                $wcproductSku =  $wcproductdetails->get_slug();
                //if "-" is exist in product sku then replace with empty
                if (strpos($wcproductSku, '-') !== false)
                {
                    $wcproductSku=str_replace("-", "_", $wcproductSku);
                }
                else
                {
                    $wcproductSku=$wcproductSku;
                }
                $wcproductSku = substr($wcproductSku,0,10);//get the first 10 charaters from the sku
                $wcproductSku = $wcproductSku.$post_id;//append the product in sku to define as a unique....
            }
            $wcproductName = $wcproductdetails->get_name();//get product name....
            
            $wcproductDesc = $wcproductdetails->get_description();//get product description....
            if(isset($wcproductDesc) && !empty($wcproductDesc)){
              //check if application edition is keap...
              if($applicationEdition == APPLICATION_TYPE_KEAP){
                  //then strip tags of description because keap application description section is simple textarea......
                  $wcproductDesc = strip_tags($wcproductDesc);
              }
              else{
                  //if application edition is infusionsoft then pass description same set in wp product....
                  $wcproductDesc = $wcproductDesc;
              }
            }else{
              $wcproductDesc = "";
            }
            $wcproductShortDesc = $wcproductdetails->get_short_description();//get product short description....
            if(isset($wcproductShortDesc) && !empty($wcproductShortDesc)){
              $wcproductShortDesc = strip_tags($wcproductShortDesc);
              $shortDescriptionLen = strlen($wcproductShortDesc);
              //check if application edition is keap....
              if($applicationEdition == APPLICATION_TYPE_KEAP){
                //then check if description is empty......
                if(empty($wcproductDesc)){
                  //then set short description as description....
                  $wcproductDesc = $wcproductShortDesc;
                }
              }else{
                if($shortDescriptionLen > 250){
                  $wcproductShortDesc = substr($wcproductShortDesc,0,250);
                }else{
                  $wcproductShortDesc = $wcproductShortDesc;
                }
              }
            }else{
              $wcproductShortDesc = "";
            }
              
            //get the product type....
            $addedProductType = $wcproductdetails->get_type();

            //check from post data ...
            $addedProductSoldAsSubscription = '';
            if(!empty($_POST['_product_subscription'])){
              $addedProductSoldAsSubscription = $_POST['_product_subscription'];
            } 

            //create final array with values.....
            $productDetailsArray = array();
            //check if product type is marked as a subscription and is also managed by infusionsoft.....
            if(stripos($addedProductType, 'subscription') !== false && $addedProductSoldAsSubscription == 'yes'){
                $productDetailsArray['subscription_only'] = true;
            }

            $productDetailsArray['active'] = true;
            $productDetailsArray['product_desc'] = $wcproductDesc;
            $productDetailsArray['sku'] = $wcproductSku;
            $productDetailsArray['product_price'] = $wcproductPrice;
            $productDetailsArray['product_short_desc'] = $wcproductShortDesc;
            $productDetailsArray['product_name'] = $wcproductName;
            $jsonData = json_encode($productDetailsArray);//covert array to json...
            $newProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_BACK_END,$wooconnectionLogger);//call the common function to insert the product.....
            if(!empty($newProductId)){//if new product created is not then update relation and product sku...
              if(!empty($addedProductSoldAsSubscription) && $addedProductSoldAsSubscription == 'yes'){
                  if(isset($_POST['_subscription_period_interval'])){
                    //get the subscription plan interval.......
                    $subPlanInterval = $_POST['_subscription_period_interval'];
                  }
                  
                  if(isset($_POST['_subscription_period'])){
                    //get the subscription plan period....
                    $subPlanPeriod = $_POST['_subscription_period'];
                  }
                  
                  if(isset($_POST['_subscription_length'])){
                    //get the subscription plan cycle....
                    $subPlanLength = $_POST['_subscription_length'];
                  }

                  $planPrice = $wcproductPrice;
                  if(isset($_POST['_subscription_price'])){
                    $planPrice = $_POST['_subscription_price'];
                  }
                  
                  //create json array to add subscription plan....
                  $subPlanJsonArray = '{"active":true,"cycle_type":"'.strtoupper($subPlanPeriod).'","frequency":'.$subPlanInterval.',"number_of_cycles":'.$subPlanLength.',"plan_price":'.$planPrice.',"subscription_plan_index":0}';
                  
                  //add subscription plan in particular application product....
                  $createdSubscriptionPlanId = addSubscriptionPlan($access_token,$newProductId,$subPlanJsonArray,$wooconnectionLogger);
              }
              //update relationship between woocommerce product and infusionsoft/keap product...
              update_post_meta($post_id, 'is_kp_product_id', $newProductId);
              //update automation so next time product is updated stop the product insertion process.....
              update_post_meta($post_id, 'wc_product_automation', true);
              //update the woocommerce product sku......
              update_post_meta($post_id,'_sku',$wcproductSku);
              //insert the product into own database.....
              $productDataArray = array();
              $productDataArray['app_product_id'] = $newProductId;
              $productDataArray['app_product_name'] =  $wcproductName;
              $productDataArray['app_product_description'] = $wcproductDesc;  
              $productDataArray['app_product_excerpt'] = $wcproductShortDesc;
              $productDataArray['app_product_sku'] = $wcproductSku;
              $productDataArray['app_product_price'] = $wcproductPrice;
              $wpdb->insert($applicationProductsTableName,$productDataArray);
              $callback_purpose = LOG_TYPE_BACK_END." : ".$callback_purpose.' is sucessfully done and newly created product in application is #'.$newProductId;
              $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', $callback_purpose);    
            }  
          }
        }
    }
}

//on move product to trash delete the relation.....
add_action('wp_trash_post', 'custom_trash_function');
function custom_trash_function($post_id){
    if(!empty($post_id)){
        //Check product relation is exist....
        $productRelationId = get_post_meta($post_id, 'is_kp_product_id', true);  
        if(!empty($productRelationId)){
          delete_post_meta( $post_id, 'is_kp_product_id', $productRelationId );
        }
    }
  return true;  
}
?>