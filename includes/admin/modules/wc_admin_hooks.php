<?php 
function insertProductToApplication( $post_id, $post ){  
    if(!empty($post_id)){
    		//first need to check connection is created or not infusionsoft/keap application then next process need to done..
			$applicationAuthenticationDetails = getAuthenticationDetails();
			//get the access token....
		    $access_token = '';
		    if(!empty($applicationAuthenticationDetails)){
			    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
			        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
			    }
		    }
		    $wooconnectionLogger = new WC_Logger();

		    //Concate a error message to store the logs...
    		$callback_purpose = 'Insert Woocommerce Product : Process of add woocommerce product to infusionsoft/keap application';

    		$wcproductdetails = wc_get_product($post_id);
			  $wcproductPrice = $wcproductdetails->get_regular_price();
        $wcproductSku = $wcproductdetails->get_sku();
        if(isset($wcproductSku) && !empty($wcproductSku)){
        	$wcproductSku = $wcproductSku;
        }else{
        	$wcproductSku = "";
        }
        $wcproductName = $wcproductdetails->get_name();
  			
  			$wcproductDesc = $wcproductdetails->get_description();
  			if(isset($wcproductDesc) && !empty($wcproductDesc)){
  				$wcproductDesc = $wcproductDesc;
  			}else{
  				$wcproductDesc = "";
  			}
  			$wcproductShortDesc = $wcproductdetails->get_short_description();
  			if(isset($wcproductShortDesc) && !empty($wcproductShortDesc)){
  				$wcproductShortDesc = $wcproductShortDesc;
  			}else{
  				$wcproductShortDesc = "";
  			}
  			$productDetailsArray = array();
  			$productDetailsArray['active'] = true;
  			$productDetailsArray['product_desc'] = $wcproductDesc;
  			$productDetailsArray['sku'] = $wcproductSku;
			  $productDetailsArray['product_price'] = $wcproductPrice;
  			$productDetailsArray['product_short_desc'] = $wcproductShortDesc;
  			$productDetailsArray['product_name'] = $wcproductName;
  			$jsonData = json_encode($productDetailsArray);
  			$newProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_BACK_END);
  			if(!empty($newProductId)){
  				$callback_purpose = $callback_purpose.' is sucessfully done and newly created product in application is #'.$newProductId;
  				$wooconnection_logs_entry = $wooconnectionLogger->add(LOG_TYPE_BACK_END, $callback_purpose);    
  			}
  	}

}
add_action( 'woocommerce_process_product_meta', 'insertProductToApplication',1000,2 );
?>