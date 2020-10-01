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

//Wordpress hook : This action is triggered when user try to save application details.
add_action( 'wp_ajax_wc_save_application_details', 'wc_save_application_details');
//Function Definiation : wc_save_application_details
function wc_save_application_details(){
	if(isset($_POST) && !empty($_POST)){
		$application_settings_array = array();
		if(isset($_POST['applicationtype']) && !empty($_POST['applicationtype'])){
			$application_settings_array['applicationtype'] = $_POST['applicationtype'];	
		}
		$applicationappname = '';
		if(isset($_POST['applicationappname']) && !empty($_POST['applicationappname'])){
			$application_settings_array['applicationappname'] = trim($_POST['applicationappname']);	
			$applicationappname = trim($_POST['applicationappname']);
		}
		$applicationapikey = '';
		if(isset($_POST['applicationapikey']) && !empty($_POST['applicationapikey'])){
			$application_settings_array['applicationapikey'] = trim($_POST['applicationapikey']);
			$applicationapikey = trim($_POST['applicationapikey']);
		}
		if(!empty($applicationappname) && !empty($applicationapikey))
		{
			$appresponse = connect_application(trim($_POST['applicationappname']),trim($_POST['applicationapikey']));
			$checkResponse = strrpos($appresponse, "ERROR");
			if ($checkResponse === false)  {
				update_option('application_settings',$application_settings_array);
				if($application_settings_array['applicationtype'] == APPLICATION_TYPE_INFUSIONSOFT){
					$page_id = add_check_page_affiliate_direct();
					if(!empty($page_id)){
						update_option('affiliate_page_id',$page_id);
					}
				}
				echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'applicationtype'=>$_POST['applicationtype']));
			}else{
				echo json_encode(array('status'=>RESPONSE_STATUS_FALSE,'errormessage'=>$appresponse));
			}
		}
		else{
			echo json_encode(array('status'=>RESPONSE_STATUS_FALSE,'errormessage'=>''));
		}
	}else{
		echo json_encode(array('status'=>RESPONSE_STATUS_FALSE,'errormessage'=>''));
	}
	die();
}

//Function : This function is used to create connection with infusionsoft and keap application
function connect_application($applicationname,$applicationkey){
	$application_connection = new wooconnection_iSDK;
	$application_connection->cfgCon($applicationname, $applicationkey);
  	$checkerApplicationResponse = $application_connection->dsGetSetting('Contact', 'optiontypes');
  	return $checkerApplicationResponse;
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
								     'instance' => ACTIVATION_INSTANCE
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
		if ($_POST['target_tab_id'] == '#table_export_products') {
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
//Function Definiation : wc_export_wc_products
function wc_export_wc_products()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		//first need to check connection is created or not infusionsoft/keap application then next process need to done..
		$applicationAuthenticationDetails = getAuthenticationDetails();
		//get the access token....
	    $access_token = '';
	    if(!empty($applicationAuthenticationDetails)){
		    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
		        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
		    }
	    }
		//Concate a error message to store the logs...
    	$callback_purpose = 'Export Woocommerce Product : Process of export woocommerce product to infusionsoft/keap application';
		//check select products exist in post data to export.....
		if(isset($_POST['wc_products']) && !empty($_POST['wc_products'])){
			foreach ($_POST['wc_products'] as $key => $value) {
	      		$productDetailsArray = array();
	      		$alreadyExistProductId = '';
	      		if(!empty($value)){
	      			//get the woocommerce product details on the basis of product id.....
	      			$wcproductdetails = wc_get_product($value);
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
	      			$productDetailsArray['active'] = true;
	      			$productDetailsArray['product_desc'] = $wcproductDesc;
	      			$productDetailsArray['sku'] = $wcproductSku;
					$productDetailsArray['product_price'] = $wcproductPrice;
	      			$productDetailsArray['product_short_desc'] = $wcproductShortDesc;
	    			$existingProductIds = checkProductAlreadyExistWithSku($wcproductSku,$access_token);
	      			if(!empty($existingProductIds)){
	      				$lastElement = end($existingProductIds);
	      				if(!empty($lastElement)){
	      					$alreadyExistProductId = $lastElement;
	      				}
	      			}
	      			
	      			
		      		//check the products data exist....
		      		if(!empty($productDetailsArray)){
		      			//if product is not associated along with export product request then need create new product in connected infusionsoft/keap appication.....
	      				if(empty($alreadyExistProductId)){
							$productDetailsArray['product_name'] = $wcproductName;
							$jsonData = json_encode($productDetailsArray);
							$createdProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_FRONT_END);
	      					if(!empty($createdProductId)){
	      						//update relationship between woocommerce product and infusionsoft/keap product...
	      						update_post_meta($value, 'is_kp_product_id', $createdProductId);
							}					
	      				}
	      				//if product is associated along with export product request then need to update the values of exitsing product in infusionsoft/keap product platform...........
	      				else{
	      					$jsonData = json_encode($productDetailsArray);
	      					$updateProductId = updateExistingProduct($alreadyExistProductId,$access_token,$jsonData);
	      					if(!empty($updateProductId)){
	      						//update relationship between woocommerce product and infusionsoft/keap product...
	      						update_post_meta($value, 'is_kp_product_id', $updateProductId);
							}
	      					
	      				}
	      			}
	      		}
	      	}
	      	//then call the "createImportProductsHtml" function to get the latest html...
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
	      		if(!empty($value)){
	      			//check any associated product is selected along with imported product request....
	      			if(isset($_POST['wc_product_mapped_with_'.$value]) && !empty($_POST['wc_product_mapped_with_'.$value])){
	      				$needUpdateExistingProduct = $_POST['wc_product_mapped_with_'.$value];
	      			}
	      			//update relationship between woocommerce product and infusionsoft/keap product...
	      			update_post_meta($value, 'is_kp_product_id', $needUpdateExistingProduct);
	      		}
	      	}
	    	//then call the "createImportProductsHtml" function to get the latest html...
			$latestMatchProductsHtml = createMatchProductsHtml();
	      	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'latestMatchProductsHtml'=>$latestMatchProductsHtml));
	    }
	}
	die();
}



?>