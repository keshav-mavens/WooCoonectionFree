<?php
//Wordpress hook : This action is triggered when user click on tab.
add_action( 'wp_ajax_wc_load_tab_main_content', 'wc_load_tab_main_content');
//Function Definiation : wc_load_tab_main_content
function wc_load_tab_main_content(){
	if(isset($_POST['tab_id']) && !empty($_POST['tab_id'])){
		//create file name on the basis of "tab_id"
		$pageName = 'wooconnection_admin_'.$_POST['tab_id'].'.php';
		require_once(WOOCONNECTION_PLUGIN_DIR.'includes/admin/'.$pageName);
	}
	exit();
}

//Wordpress hook : This action is triggered when user try to activate the plugin.
add_action( 'wp_ajax_activate_wooconnection_plugin', 'activate_wooconnection_plugin');
//Function Definiation : activate_wooconnection_plugin
function activate_wooconnection_plugin()
{
	if(isset($_POST) && !empty($_POST))
	{
		//Get plugin details..
		$plugin_settings = get_option('wc_plugin_details');
		//check post activation email exist or not..if not exist then need to check from the options table...
		if(isset($_POST['pluginactivationemail']) && !empty($_POST['pluginactivationemail'])){
			$pluginactivationemail = trim($_POST['pluginactivationemail']);
		}
		else
		{
			if(!empty($plugin_settings['wc_license_email'])){
				$pluginactivationemail = $plugin_settings['wc_license_email'];
			}
		}
		
		//check post activation key exist or not..if not exist then need to check from the options table...
		if(isset($_POST['pluginactivationkey']) && !empty($_POST['pluginactivationkey'])){
			$pluginactivationkey = trim($_POST['pluginactivationkey']);
		}
		else
		{
			if(!empty($plugin_settings['wc_license_key'])){
				$pluginactivationkey = $plugin_settings['wc_license_email'];
			}
		}
		
		//check the plugin status in activation table if plugin is already activated then return the success with success message
		if(!empty($plugin_settings['plugin_activation_status']) && $plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED)
		{
			echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'successmessage'=>'Your Plugin is Already Activated'));
		}
		else if(!empty($pluginactivationemail) && !empty($pluginactivationkey))
		{
			$queryParameters = array('request' => ACTIVATION_REQUEST_TYPE,
								     'email' => $pluginactivationemail,
								     'licence_key' => $pluginactivationkey,
								     'secret_key' => ACTIVATION_SECRET_KEY,
								     'product_id' => ACTIVATION_PRODUCT_ID,
								     'instance' => ACTIVATION_INSTANCE,
								     'platform' => SITE_URL
								     );
		    $targetUrl = add_query_arg('wc-api', 'software-api', ADMIN_REMOTE_URL).'&'.http_build_query($queryParameters);
		    $responseData = wp_remote_get($targetUrl);
		    if(isset($responseData) && !empty($responseData))
		    {
		    	$activationResponse = json_decode($responseData['body'],true);
		    	if(!empty($activationResponse['error'])){
		    		$plugin_details_array['plugin_activation_status'] = PLUGIN_NOT_ACTIVATED;
		    		update_option('wc_plugin_details', $plugin_details_array);
		    		$errormessage =  $activationResponse['error'];
		    		echo json_encode(array('status'=>RESPONSE_STATUS_FALSE,'errormessage'=>$errormessage));
		    	}else{
		    		$plugin_details_array['wc_license_email'] = $pluginactivationemail;
					$plugin_details_array['wc_license_key'] = $pluginactivationkey;
					$plugin_details_array['plugin_activation_status'] = PLUGIN_ACTIVATED;
		    		update_option('wc_plugin_details', $plugin_details_array);
		    		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'successmessage'=>''));
		    	}
		    }
		}
	}
	die();
}

//Wordpress hook : This action is triggered when user try to update trigger details then return the trigger existing values.....
add_action( 'wp_ajax_wc_get_trigger_details', 'wc_get_trigger_details');
//Function Definiation : wc_get_trigger_details
function wc_get_trigger_details()
{
	if(!empty($_POST['triggerid']) && !empty($_POST['triggerid'])){
		global $wpdb,$table_prefix;
		$table_name = 'wooconnection_campaign_goals';
		$wp_table_name = $table_prefix . "$table_name";
		$triggerid = $_POST['triggerid'];
    	$triggerDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE id=".$triggerid);
    	$triggerGoalName = "";
        $triggerIntegrationName = "";
        $triggerCallName = "";
    	if(isset($triggerDetails) && !empty($triggerDetails)){
    		if(!empty($triggerDetails[0]->wc_goal_name)){
    			$triggerGoalName = $triggerDetails[0]->wc_goal_name;
    		}
    		if(!empty($triggerDetails[0]->wc_integration_name)){
    			$triggerIntegrationName = strtolower($triggerDetails[0]->wc_integration_name);	
    		}
	        if(!empty($triggerDetails[0]->wc_call_name)){
    			$triggerCallName = strtolower($triggerDetails[0]->wc_call_name);	
    		}
	    }
    	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'triggerGoalName'=>$triggerGoalName,'triggerIntegrationName'=>$triggerIntegrationName,'triggerCallName'=>$triggerCallName));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to update the trigger details.....
add_action( 'wp_ajax_wc_update_trigger_details', 'wc_update_trigger_details');
//Function Definiation : wc_update_trigger_details
function wc_update_trigger_details()
{
	if(isset($_POST) && !empty($_POST))
	{
		if(isset($_POST['edittriggerid']) && !empty($_POST['edittriggerid']))
		{
			global $wpdb,$table_prefix;
			$table_name = 'wooconnection_campaign_goals';
			$wp_table_name = $table_prefix . "$table_name";
			if(isset($_POST['integrationname']) && !empty($_POST['integrationname'])){
    			$triggerIntegrationName = strtolower(trim($_POST['integrationname']));	
    		}
	        if(isset($_POST['callname']) && !empty($_POST['callname'])){
    			$triggerCallName = strtolower(trim($_POST['callname']));	
    		}
    		$updateResult = $wpdb->update($wp_table_name, array('wc_integration_name' => $triggerIntegrationName,'wc_call_name'=>$triggerCallName),array('id' => $_POST['edittriggerid']));
    		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'triggerIntegrationName'=>$triggerIntegrationName,'triggerCallName'=>$triggerCallName));
    	}
	}
	die();	
}

//Wordpress hook : This action is triggered when user click on import/export/match products tabs of import tab...
add_action( 'wp_ajax_wc_load_import_export_tab_main_content', 'wc_load_import_export_tab_main_content');
//Function Definiation : wc_load_import_export_tab_main_content
function wc_load_import_export_tab_main_content(){
	//First check the target tab id the call the html function for latest html.....
	if(isset($_POST['target_tab_id']) && !empty($_POST['target_tab_id'])){
		$latestHtml = '';
		if ($_POST['target_tab_id'] == '#table_import_products') {
			$latestHtml = createImportProductsHtml();
		}else if ($_POST['target_tab_id'] == '#table_export_products') {
			$latestHtml = createExportProductsHtml();
		}else if ($_POST['target_tab_id'] == '#table_match_products') {
			$latestHtml = createMatchProductsHtml();
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'latestHtml'=>$latestHtml));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to export products.....
add_action( 'wp_ajax_wc_export_wc_products', 'wc_export_wc_products');
//Function Defination : wc_export_wc_products
function wc_export_wc_products()
{
	//first check post data is not empty
    if(isset($_POST) && !empty($_POST)){
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
        $callback_purpose = 'Export Woocommerce Product : Process of export woocommerce product to infusionsoft/keap application';
        //set the wooconnection log class.....
        $wooconnectionLogger = new WC_Logger();
        //check select products exist in post data to export.....
        if(isset($_POST['wc_products']) && !empty($_POST['wc_products'])){
            foreach ($_POST['wc_products'] as $key => $value) {
                $productDetailsArray = array();//Define variable..
                $mapppedProductId = '';//Define variable..
                if(!empty($value)){//check value...
                    //check any associated product is selected along with export product request....
	      			if(isset($_POST['wc_product_export_with_'.$value]) && !empty($_POST['wc_product_export_with_'.$value])){
	      				$mapppedProductId = $_POST['wc_product_export_with_'.$value];
	      			}else{
	      				$mapppedProductId = '';
	      			}

                    //get the woocommerce product details on the basis of product id.....
                    $wcproductdetails = wc_get_product($value);//Get product details..
                    $wcproductPrice = $wcproductdetails->get_regular_price();//Get product price....
                    $wcproductSku = $wcproductdetails->get_sku();//get product sku....
                    //Check if product sku is not exist then create the sku on the basis of product slug.........
                    if(isset($wcproductSku) && !empty($wcproductSku)){
                        $wcproductSku = $wcproductSku;
                    }else{
                        $wcproductSku =  $wcproductdetails->get_slug();//get product slug....
                        //if "-" is exist in product sku then replace with "_".....
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
                        $wcproductShortDesc = $wcproductShortDesc;
                    }else{
                        $wcproductShortDesc = "";
                    }
                    //create final array with values.....
                    $productDetailsArray['active'] = true;
                    $productDetailsArray['product_desc'] = $wcproductDesc;
                    $productDetailsArray['sku'] = $wcproductSku;
                    $productDetailsArray['product_price'] = $wcproductPrice;
                    $productDetailsArray['product_short_desc'] = $wcproductShortDesc;
                    

                    //check the products data exist....
                    if(isset($productDetailsArray) && !empty($productDetailsArray)){
                        //if product is not associated along with export product request then need create new product in connected infusionsoft/keap appication.....
                        if(empty($mapppedProductId)){
                        	$productDetailsArray['product_name'] = $wcproductName;//assign product name for new product creation....
                        	$jsonData = json_encode($productDetailsArray);//covert array to json...
                            $createdProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_BACK_END,$wooconnectionLogger);//call the common function to insert the product.....
                            if(!empty($createdProductId)){//if new product created is not then update relation and product sku...
                                //update relationship between woocommerce product and infusionsoft/keap product...
                                update_post_meta($value, 'is_kp_product_id', $createdProductId);
                                //update the woocommerce product sku......
                            	update_post_meta($value,'_sku',$wcproductSku);
                            }                   
                        }
                        //if product is associated along with export product request then need to update the values of exitsing product in infusionsoft/keap product platform...........
                        else{
                        	$jsonData = json_encode($productDetailsArray);//covert array to json...
                        	//call the common function to update the existing function in application.....
                            $updateProductId = updateExistingProduct($mapppedProductId,$access_token,$jsonData,LOG_TYPE_BACK_END,$wooconnectionLogger);
                            if(!empty($updateProductId)){//if new product created is not then update relation and product sku...
                                //update relationship between woocommerce product and infusionsoft/keap product...
                                update_post_meta($value, 'is_kp_product_id', $updateProductId);
                                //update the woocommerce product sku......
                                update_post_meta($value,'_sku',$wcproductSku);
                            }
                            
                        }
                    }
                    
                }
            }
            //then call the "createExportProductsHtml" function to get the latest html...
            $latestExportProductsHtml = createExportProductsHtml();
            echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'latestExportProductsHtml'=>$latestExportProductsHtml));
        }
    }
    die();
}


//Wordpress hook : This action is triggered when user try to export products.....
add_action( 'wp_ajax_wc_update_products_mapping', 'wc_update_products_mapping');
//Function Definiation : wc_update_products_mapping
function wc_update_products_mapping()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		//check select products exist in post data to import.....
		if(isset($_POST['wc_products_match']) && !empty($_POST['wc_products_match'])){
	      	foreach ($_POST['wc_products_match'] as $key => $value) {
	      		if(!empty($value)){//check id value is not empty...
	      			//check any associated product is selected along with imported product request....
	      			if(isset($_POST['wc_product_match_with_'.$value]) && !empty($_POST['wc_product_match_with_'.$value])){
	      				$needUpdateExistingProduct = $_POST['wc_product_match_with_'.$value];
	      			}
	      			//update relationship between woocommerce product and infusionsoft/keap product...
	      			update_post_meta($value, 'is_kp_product_id', $needUpdateExistingProduct);
	      		}
	      	}
	    }
	    //then call the "createMatchProductsHtml" function to get the latest html...
		$latestMatchProductsHtml = createMatchProductsHtml();
      	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'latestMatchProductsHtml'=>$latestMatchProductsHtml));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to import products.....
add_action( 'wp_ajax_wc_import_iskp_products', 'wc_import_iskp_products');
//Function Definiation : wc_import_infusions_keap_products
function wc_import_iskp_products()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		//first need to check whether the application authentication is done or not..
        $applicationAuthenticationDetails = getAuthenticationDetails();
        //get the access token....
        $access_token = '';
        if(!empty($applicationAuthenticationDetails)){//check authentication details......
            if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
                $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
            }
        }
		//check select products exist in post data to export.....
        if(isset($_POST['wc_products_import']) && !empty($_POST['wc_products_import'])){
            foreach ($_POST['wc_products_import'] as $key => $value) {
 				if(!empty($value)){//check value...
          			//check any associated product is selected along with imported product request....
	      			if(isset($_POST['wc_product_import_with_'.$value]) && !empty($_POST['wc_product_import_with_'.$value])){
	      				$needUpdateExistingProduct = $_POST['wc_product_import_with_'.$value];
	      			}else{
	      				$needUpdateExistingProduct = '';
	      			}
	      			//get infusionsoft/keap application product details on the basis of infusionsoft/keap product id...
	      			//define array to store the infusionsoft/keap product detail......
	      			$product_extra_data_array = array();
	      			$infusionKeapProduct = getApplicationProductDetail($value,$access_token);
	      			if(isset($infusionKeapProduct) && !empty($infusionKeapProduct)){
	      				$pContent = '';
		      			if(!empty($infusionKeapProduct['product_desc'])){
		      				$pContent = trim($infusionKeapProduct['product_desc']);	
		      			}else if ($infusionKeapProduct[0]['product_short_desc']) {
		      				$pContent = trim($infusionKeapProduct[0]['product_short_desc']);
		      			}else if ($infusionKeapProduct['product_name']) {
		      				$pContent = trim($infusionKeapProduct['product_name']);
		      			}
		      			$product_extra_data_array['_regular_price'] = $infusionKeapProduct['product_price'];
		      			$product_extra_data_array['_price'] = $infusionKeapProduct['product_price'];
		      			if(!empty($infusionKeapProduct['sku'])){
		      				$product_extra_data_array['_sku'] = $infusionKeapProduct['sku'];
		      			}

		      			//if product is not associated along with imported product request then need create new product..
		      			if(empty($needUpdateExistingProduct)){
		      				if(!empty($infusionKeapProduct['product_name'])){
			      				$productName = $infusionKeapProduct['product_name'];
			      			}
		      				$postData = array(
							    'post_content' => $pContent,
							    'post_status' => "publish",
							    'post_title' => $productName,
							    'post_type' => "product",
							);
							$new_post_id = wp_insert_post($postData);
							//check if product imported done then need to check the image associated with product if yes then need to update....
							if($new_post_id){
								$product_extra_data_array['is_kp_product_id'] = $value;
								if(empty($product_extra_data_array['_sku'])){
									$product = get_post($new_post_id); 
									$slug = $product->post_name;
									//if "-" is exist in product sku then replace with "_".....
								    if (strpos($slug, '-') !== false)
								    {
								        $wcproductSku=str_replace("-", "_", $slug);
								    }
								    else
								    {
								        $wcproductSku=$slug;
								    }
									$product_extra_data_array['_sku'] = $wcproductSku;
								}	
								//update post meta of newly created post...
								updateProductMetaData($new_post_id,$product_extra_data_array);
							}
						}
		      			//if product is associated along with imported product request then need to update the values of exitsing product.........
		      			else{
		      				$product_extra_data_array['is_kp_product_id'] = $value;
	      					if(empty($product_extra_data_array['_sku'])){
								$product = get_post($new_post_id); 
								$slug = $product->post_name;
								//if "-" is exist in product sku then replace with "_".....
							    if (strpos($slug, '-') !== false)
							    {
							        $wcproductSku=str_replace("-", "_", $slug);
							    }
							    else
							    {
							        $wcproductSku=$slug;
							    }
								$product_extra_data_array['_sku'] = $wcproductSku;
							}
		      				//update post meta of existing post...
		      				updateProductMetaData($needUpdateExistingProduct,$product_extra_data_array);
		      			}

		      		}
	      		}
 			}
 			//then call the "createImportProductsHtml" function to get the latest html...
            $latestImportProductsHtml = createImportProductsHtml();
            echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'latestImportProductsHtml'=>$latestImportProductsHtml));
 		}
 	}
	die();
}

//Get the list of application products....
function getApplicationProductDetail($id,$access_token){
   	$productsListing = array();
    $url = "https://api.infusionsoft.com/crm/rest/v1/products/".$id;
    $ch = curl_init($url);
    $header = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer '. $access_token
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    $response = curl_exec($ch);
    $err = curl_error($ch);
    $matchIdsArray = array();
    if($err){
    }else{
      $sucessData = json_decode($response,true);
      return $sucessData;
    }
    curl_close($ch);
}

//This function is used to update the post meta with latest details..
function updateProductMetaData($productId,$detailsArray){
	//first check post/product id is exist.. 
	if(!empty($productId) && !empty($detailsArray)){
		//Execute the loop to update the post/product meta data....
		foreach ($detailsArray as $key => $value) {
			if(!empty($key)){
				update_post_meta($productId, $key, $value);			
			}
		}
		return RESPONSE_STATUS_TRUE;
	}
}

?>