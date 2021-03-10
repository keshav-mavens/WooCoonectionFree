<?php
//Wordpress hook : This action is triggered when user click on tab.
add_action( 'wp_ajax_wc_load_tab_main_content', 'wc_load_tab_main_content');
//Function Definiation : wc_load_tab_main_content
function wc_load_tab_main_content(){
	if(isset($_POST['tab_id']) && !empty($_POST['tab_id'])){
		//define the memory limit infinite to prevent from exceed memory limit error....
		ini_set('memory_limit',"-1");
		//also set time limit "0" to prevent the execution time exceed....
		set_time_limit(0);
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
		
		if(!empty($plugin_settings['wc_license_email'])){
			$existingactivationemail = $plugin_settings['wc_license_email'];
		}
		
		//check post activation key exist or not..if not exist then need to check from the options table...
		if(isset($_POST['pluginactivationkey']) && !empty($_POST['pluginactivationkey'])){
			$pluginactivationkey = trim($_POST['pluginactivationkey']);
		}

		if(!empty($plugin_settings['wc_license_key'])){
			$existingactivationkey = $plugin_settings['wc_license_key'];
		}

		//check or set if email and key is same with existing plugin details....
		$pluginActivated = RESPONSE_STATUS_FALSE;
		if(!empty($existingactivationemail) && !empty($existingactivationkey)){
			if($existingactivationemail == $pluginactivationemail && $existingactivationkey == $pluginactivationkey){
				$pluginActivated = RESPONSE_STATUS_TRUE;
			}
		}

		//check the plugin status in activation table if plugin is already activated then return the success with success message
		if(!empty($plugin_settings['plugin_activation_status']) && $plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED && $pluginActivated == RESPONSE_STATUS_TRUE)
		{
			echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'successmessage'=>'Your Plugin is Already Activated.'));
		}
		else if(!empty($pluginactivationemail) && !empty($pluginactivationkey))
		{
			$queryParameters = array('request' => ACTIVATION_REQUEST_TYPE,
								     'email' => $pluginactivationemail,
								     'licence_key' => $pluginactivationkey,
								     'secret_key' => ACTIVATION_SECRET_KEY,
								     'product_id' => ACTIVATION_PRODUCT_ID,
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
		    		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'successmessage'=>'','licence_email'=>$pluginactivationemail,'licence_key'=>$pluginactivationkey));
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
		//define the memory limit infinite to prevent from exceed memory limit error....
		ini_set('memory_limit',"-1");
		//also set time limit "0" to prevent the execution time exceed....
		set_time_limit(0);
		$latestHtml = '';
		$offset = 0;
		if ($_POST['target_tab_id'] == '#table_export_products') {
			if(isset($_POST['newLimitExport']) && !empty($_POST['newLimitExport'])){
				$limit = $_POST['newLimitExport'];
			}
			$latestHtml = createExportProductsHtml($limit,$offset);
		}else if ($_POST['target_tab_id'] == '#table_match_products') {
			if(isset($_POST['newLimitMatch']) && !empty($_POST['newLimitMatch'])){
				$limit = $_POST['newLimitMatch'];
			}
			$latestHtml = createMatchProductsHtml($limit,$offset);
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
        //define the memory limit infinite to prevent from exceed memory limit error....
		ini_set('memory_limit',"-1");
		//also set time limit "0" to prevent the execution time exceed....
		set_time_limit(0);
        global $wpdb,$table_prefix;
        //first need to check whether the application authentication is done or not..
        $applicationAuthenticationDetails = getAuthenticationDetails();
        //get the access token....
        $access_token = '';
        $applicationEdition = '';
        if(!empty($applicationAuthenticationDetails)){//check authentication details......
            if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
                $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
            }
            
            //set the value of application edition such as keap, infusionsoft.....
        	$applicationEdition = $applicationAuthenticationDetails[0]->user_application_edition;
        }
        //Define the exact position of process to store the logs...
        $callback_purpose = 'Export Woocommerce Product : Process of export woocommerce product to infusionsoft/keap application';
        //set the wooconnection log class.....
        $wooconnectionLogger = new WC_Logger();
        //get the existing application app products...
        $existinProducts = getExistingAppProducts();
        $appProductsTableName = $table_prefix.'authorize_application_products';
        //check select products exist in post data to export.....
        if(isset($_POST['wc_products']) && !empty($_POST['wc_products'])){
            foreach ($_POST['wc_products'] as $key => $value) {
                $productDetailsArray = array();//Define variable..
                $mapppedProductId = '';//Define variable..
                $appProductData = array();
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
					    $wcproductSku = substr($wcproductSku,0,10);//get the first 10 charaters from the sku
                		$wcproductSku = $wcproductSku.$value;//append the product in sku to define as a unique....
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
                    //create final array with values.....
                    $productDetailsArray['active'] = true;
                    $productDetailsArray['product_desc'] = $wcproductDesc;
                    $productDetailsArray['sku'] = $wcproductSku;
                    $productDetailsArray['product_price'] = $wcproductPrice;
                    $productDetailsArray['product_short_desc'] = $wcproductShortDesc;
                    

                    //check the products data exist....
                    if(isset($productDetailsArray) && !empty($productDetailsArray)){
                    	$checkMappedProductExist = array();//define the empty array.....
                    	if(isset($mapppedProductId) && !empty($mapppedProductId)){//check if mapped product id exist.....
                    		$checkMappedProductExist = getApplicationProductDetail($mapppedProductId,$access_token);
                    	}
                        //if product is not associated along with export product request then need create new product in connected infusionsoft/keap appication or another when mapping exist but mapped product does not exist in authorize application.....
                        if(empty($mapppedProductId) || empty($checkMappedProductExist['product_name'])){
                        	$productDetailsArray['product_name'] = $wcproductName;//assign product name for new product creation....
                        	$jsonData = json_encode($productDetailsArray);//covert array to json...
                            $createdProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_BACK_END,$wooconnectionLogger);//call the common function to insert the product.....
                            if(!empty($createdProductId)){//if new product created is not then update relation and product sku...
                                //update relationship between woocommerce product and infusionsoft/keap product...
                                update_post_meta($value, 'is_kp_product_id', $createdProductId);
                                //update the woocommerce product sku......
                            	update_post_meta($value,'_sku',$wcproductSku);
                            	$appProductData['app_product_id'] = $createdProductId;
								$appProductData['app_product_name'] =  $wcproductName;
								$appProductData['app_product_description'] = $productDetailsArray['product_desc'];	
								$appProductData['app_product_excerpt'] = $productDetailsArray['product_short_desc'];
								$appProductData['app_product_sku'] = $wcproductSku;
								$appProductData['app_product_price'] = $wcproductPrice;
								$wpdb->insert($appProductsTableName,$appProductData);
								if(!empty($mapppedProductId) && isset($_POST['wc_product_primary_key_'.$mapppedProductId]) && !empty($_POST['wc_product_primary_key_'.$mapppedProductId])){
									$wpdb->update($appProductsTableName,array('app_product_status'=>STATUS_DELETED),array('id'=>$_POST['wc_product_primary_key_'.$mapppedProductId]));
								}
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
                                if(isset($_POST['wc_product_primary_key_'.$updateProductId]) && !empty($_POST['wc_product_primary_key_'.$updateProductId])){
	                                $primaryKey = $_POST['wc_product_primary_key_'.$updateProductId];
									$appProductData['app_product_name'] =  $wcproductName;
									$appProductData['app_product_description'] = $productDetailsArray['product_desc'];	
									$appProductData['app_product_excerpt'] = $productDetailsArray['product_short_desc'];
									$appProductData['app_product_sku'] = $wcproductSku;
									$appProductData['app_product_price'] = $wcproductPrice;
									$response = $wpdb->update($appProductsTableName, $appProductData, array('id'=>$primaryKey));
                                }
							}
                       	}
                    }
                }
            }
            echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
        }
    }
    die();
}


//Wordpress hook : This action is triggered when user try to update  products mapping.....
add_action( 'wp_ajax_wc_update_products_mapping', 'wc_update_products_mapping');
//Function Definiation : wc_update_products_mapping
function wc_update_products_mapping()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		//check select products exist in post data to import.....
		if(isset($_POST['wcProductId']) && !empty($_POST['wcProductId'])){
	      	if(isset($_POST['applicationProductId'])){
	      			//update relationship between woocommerce product and infusionsoft/keap product...
	      			update_post_meta($_POST['wcProductId'], 'is_kp_product_id', $_POST['applicationProductId']);
	      	}
	   	}
	    echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Wordpress hook : This action is triggered to show the variations of specific product.....
add_action( 'wp_ajax_wc_get_product_variation', 'wc_get_product_variation');
//Function Definiation : wc_get_product_variation
function wc_get_product_variation()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		$variationsHtml = '';
		//check select products exist in post data to get the variations.....
		if(isset($_POST['productId']) && !empty($_POST['productId'])){
	      	$wcProductId = $_POST['productId'];
	      	$wcProductDetails = wc_get_product($wcProductId);//Get product details..
	      	$wcProductName = $wcProductDetails->get_name();//get product name....
	      	$available_variations = $wcProductDetails->get_available_variations();
	      	//Get the list of active products from authenticate application....
  			$applicationProductsArray = getExistingAppProducts();
			  //Get the application type and set the lable on the basis of it....
		  	$configurationType = applicationType();
		  	$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;//Default....
		  	if(isset($configurationType) && !empty($configurationType)){
			    if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
			      $type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
			    }else if ($configurationType == APPLICATION_TYPE_KEAP) {
			      $type = APPLICATION_TYPE_KEAP_LABEL;
			    }
		  	}
  			//Set the application label on the basis of type...
  			$applicationLabel = applicationLabel($type);
  			$currencySign = get_woocommerce_currency_symbol();//Get currency symbol....
	  		if(isset($available_variations) && !empty($available_variations)){
	  			foreach ($available_variations as $key => $value) {
	  				if($value['variation_is_active'] == STATUS_ACTIVE){
	  					$mappedProductHtml = '';
	  					$productsDropDown = '';
	  					if(!empty($applicationProductsArray)){
	  						//Check variation relation is exist....
		                    $variationExistId = get_post_meta($value['variation_id'], 'is_kp_product_id', true);
		                    //If variation relation exist then create select deopdown and set associative product selected....
		                    if(isset($variationExistId) && !empty($variationExistId)){
		                      $productsDropDown = createMatchProductsSelect($applicationProductsArray,$variationExistId);
		                    }else if($variationExistId === '0'){
		                    	$productsDropDown = createMatchProductsSelect($applicationProductsArray);
		                    }
		                    else if(isset($_POST['matchProductId']) && !empty($_POST['matchProductId'])){
		                      $productsDropDown = createMatchProductsSelect($applicationProductsArray,$_POST['matchProductId']);
		                    }
		                    $mappedProductHtml = '<select class="application_match_products_dropdown" name="wc_product_match_with_'.$value['variation_id'].'" data-id="'.$value['variation_id'].'"><option value="0">Select '.$applicationLabel.' product</option>'.$productsDropDown.'</select>';
	  					}else{
	  						//Set the html of select if no products exist in application....
                 			 $mappedProductHtml = 'No '.$applicationLabel.' Products Exist!';
	  					}
	  					//get variation attributes........
	  					$variationName = $value['attributes'];
	  					$keys = array_keys($variationName);
	  					//get variation version like in sizes small,medium,large and in colors red,green,blue etc....
						$variationVersion = $variationName[$keys[0]];
	  					$variationPrice = $currencySign.number_format($value['display_regular_price'],2);
	  					$variationSku = $value['sku'];
	  					//Check and set the product sku to display.....
		                if(!empty($variationSku)){
		                  $variationSku = $variationSku;
		                }else{
		                  $variationSku = "--";
		                }
	  					$variationsHtml .= '<tr id="table_row_'.$value['variation_id'].'" class="customvariations_'.$wcProductId.' custom_tr" ><td></td><td>'.$wcProductName.'('.$variationVersion.')</td><td  class="skucss">'.$variationSku.'</td><td>'.$variationPrice.'</td><td>'.$mappedProductHtml.'</td></tr>';
	  				}
	  			}
	  		}	
	   	}
	    echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'variationsHtml'=>$variationsHtml));
	}
	die();
}

//Wordpress Hook : This hook is triggered to load the more product either for match products tab or export products tab.....
add_action('wp_ajax_wc_load_more_products','wc_load_more_products');
//Function Definition : wc_load_more_products
function wc_load_more_products(){
	if(isset($_POST) && !empty($_POST)){
		$moreProductsListing = '';
		if(!empty($_POST['tabversion']) && !empty($_POST['productsLimit']) && !empty($_POST['productsOffset'])){
			if($_POST['tabversion'] == 'table_export_products'){
				//then call the "createExportProductsHtml" function to get the next products for export products...
        		$moreProductsListing = createExportProductsHtml($_POST['productsLimit'],$_POST['productsOffset'],PRODUCTS_HTML_TYPE_LOAD_MORE);
			}else if ($_POST['tabversion'] == 'table_match_products') {
				//then call the "createMatchProductsHtml" function to get the next products for match products....
				$moreProductsListing = createMatchProductsHtml($_POST['productsLimit'],$_POST['productsOffset'],PRODUCTS_HTML_TYPE_LOAD_MORE);
			}
	    }
	    echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'moreProductsListing'=>$moreProductsListing));
	}
	die();
}

//Wordpreess Hool : This hook is triggered to get the products from authorized application and insert into the database.....
add_action('wp_ajax_wc_get_insert_app_products','wc_get_insert_app_products');
//Function Definition : wc_get_insert_app_products
function wc_get_insert_app_products(){
	if(isset($_POST) && !empty($_POST)){
		global $wpdb,$table_prefix;
		$applicationProductsTableName = $table_prefix.'authorize_application_products';
		$appProducts = getApplicationProducts();
		$applicationExistingProducts = getExistingAppProducts();
		if(isset($appProducts['products']) && !empty($appProducts['products'])){
			foreach ($appProducts['products'] as $key => $value) {
				if(!empty($value['id'])){
					$productDataArray = array();
					$productDataArray['app_product_id'] = $value['id'];
					$productDataArray['app_product_name'] =  $value['product_name'];
					$productDataArray['app_product_description'] = strip_tags($value['product_desc']);	
					$productDataArray['app_product_excerpt'] = $value['product_short_desc'];
					$productDataArray['app_product_sku'] = $value['sku'];
					$productDataArray['app_product_price'] = $value['product_price'];
					$productDataArray['app_product_subscription'] = $value['subscription_only'];
					if(isset($applicationExistingProducts) && !empty($applicationExistingProducts)){
						$matchKey = array_search($value['id'],array_column($applicationExistingProducts,'app_product_id'));
						if(!empty($matchKey) || $matchKey === 0){
							$alreadyExistProductId = $applicationExistingProducts[$matchKey]->id;
							if (!empty($alreadyExistProductId)) {
								$updateResponse = $wpdb->update($applicationProductsTableName,$productDataArray,array('id'=>$alreadyExistProductId));
							}
						}else{
							$wpdb->insert($applicationProductsTableName,$productDataArray);
						}
					}else{
						$wpdb->insert($applicationProductsTableName,$productDataArray);
					}
				}
			}
		}
	}
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	die();
}

//Wordpress Hook : This action is triggered when user click on refresh button to refresh the list of products.....
add_action('wp_ajax_wc_get_reload_app_products','wc_get_reload_app_products');
//Function Definition : wc_get_reload_app_products
function wc_get_reload_app_products(){
	if(isset($_POST) && !empty($_POST)){
		global $wpdb,$table_prefix;
		$appLatestProducts = getApplicationProducts();
		$appExistingProducts = getExistingAppProducts();
		$applicationProductsTableName = $table_prefix.'authorize_application_products';
		if(isset($appLatestProducts) && !empty($appLatestProducts)){
			foreach ($appLatestProducts['products'] as $key => $value) {
				if(!empty($value['id'])){
					$refreshProductDataArray = array();
					$refreshProductDataArray['app_product_id'] = $value['id'];
					$refreshProductDataArray['app_product_name'] =  $value['product_name'];
					$refreshProductDataArray['app_product_description'] = strip_tags($value['product_desc']);	
					$refreshProductDataArray['app_product_excerpt'] = $value['product_short_desc'];
					$refreshProductDataArray['app_product_sku'] = $value['sku'];
					$refreshProductDataArray['app_product_price'] = $value['product_price'];
					$refreshProductDataArray['app_product_subscription'] = $value['subscription_only'];
					if(isset($appExistingProducts) && !empty($appExistingProducts)){
						$key = array_search($value['id'], array_column($appExistingProducts, 'app_product_id'));
						if (!empty($key) || $key === 0) {
							$existingProductId =  $appExistingProducts[$key]->id;
							if(!empty($existingProductId)){
								$response = $wpdb->update($applicationProductsTableName, $refreshProductDataArray, array('id'=>$existingProductId));
							}
						}else{
							$wpdb->insert($applicationProductsTableName,$refreshProductDataArray);
						}
					}else{
						$wpdb->insert($applicationProductsTableName,$refreshProductDataArray);
					}
				}
			}
		}
	}
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	die();
}
?>
