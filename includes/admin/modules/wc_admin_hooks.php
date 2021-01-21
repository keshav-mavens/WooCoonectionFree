<?php
//Wordpress hook : This action is triggered when user create new product in woocommerce then add to application.....
add_action( 'woocommerce_process_product_meta', 'insertProductToApplication',1000,2 ); 
//Function Definiation : insertProductToApplication
function insertProductToApplication( $post_id, $post ){  
    if(!empty($post_id)){//check post id is exist
    		//Check product is update or publish....
        $productExistId = get_post_meta($post_id, 'wc_product_automation', true);
        //if product is not create yet then need to move in application
        if(empty($productExistId)){
          //first need to check whether the application authentication is done or not..
          $applicationAuthenticationDetails = getAuthenticationDetails();
          //get the access token....
          $access_token = '';
          if(!empty($applicationAuthenticationDetails)){//check authentication details......
            if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
                $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
            }
          }
          
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
          }
          $wcproductName = $wcproductdetails->get_name();//get product name....
          
          $wcproductDesc = $wcproductdetails->get_description();//get product description....
          if(isset($wcproductDesc) && !empty($wcproductDesc)){
            $wcproductDesc = $wcproductDesc;
          }else{
            $wcproductDesc = "";
          }
          $wcproductShortDesc = $wcproductdetails->get_short_description();//get product short description....
          if(isset($wcproductShortDesc) && !empty($wcproductShortDesc)){
            $wcproductShortDesc = strip_tags($wcproductShortDesc);
            $shortDescriptionLen = strlen($wcproductShortDesc);
            if($shortDescriptionLen > 250){
              $wcproductShortDesc = substr($wcproductShortDesc,0,250);
            }else{
              $wcproductShortDesc = $wcproductShortDesc;
            }
          }else{
            $wcproductShortDesc = "";
          }
          //create final array with values.....
          $productDetailsArray = array();
          $productDetailsArray['active'] = true;
          $productDetailsArray['product_desc'] = $wcproductDesc;
          $productDetailsArray['sku'] = $wcproductSku;
          $productDetailsArray['product_price'] = $wcproductPrice;
          $productDetailsArray['product_short_desc'] = $wcproductShortDesc;
          $productDetailsArray['product_name'] = $wcproductName;
          $jsonData = json_encode($productDetailsArray);//covert array to json...
          $newProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_BACK_END,$wooconnectionLogger);//call the common function to insert the product.....
          if(!empty($newProductId)){//if new product created is not then update relation and product sku...
            //update relationship between woocommerce product and infusionsoft/keap product...
            update_post_meta($post_id, 'is_kp_product_id', $newProductId);
            //update automation so next time product is updated stop the product insertion process.....
            update_post_meta($post_id, 'wc_product_automation', true);
            //update the woocommerce product sku......
            update_post_meta($post_id,'_sku',$wcproductSku);
            $callback_purpose = LOG_TYPE_BACK_END." : ".$callback_purpose.' is sucessfully done and newly created product in application is #'.$newProductId;
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', $callback_purpose);    
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