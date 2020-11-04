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
//Wordpress hook : This action is triggered when user try to update the default thanlkyou override.....
add_action( 'wp_ajax_wc_save_thanks_default_override', 'wc_save_thanks_default_override');
//Function Definiation : wc_save_thanks_default_override
function wc_save_thanks_default_override()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		$defaultThanksArray = array();//define empty array...
		//check select redirect type in post data to save default thankyou override.........
		if(isset($_POST['overrideredirecturltype']) && !empty($_POST['overrideredirecturltype'])){
			if($_POST['overrideredirecturltype'] == DEFAULT_WORDPRESS_POST){//check if redirect type is wordpress post....
				if(!empty($_POST['redirectwordpresspost'])){
					$defaultThanksArray['redirectType'] = $_POST['overrideredirecturltype'];
					$defaultThanksArray['redirectValue'] = $_POST['redirectwordpresspost'];
				}
			}else if ($_POST['overrideredirecturltype'] == DEFAULT_WORDPRESS_PAGE) {//check if redirect type is wordpress page....
				if(!empty($_POST['redirectwordpresspage'])){
					$defaultThanksArray['redirectType'] = $_POST['overrideredirecturltype'];
					$defaultThanksArray['redirectValue'] = $_POST['redirectwordpresspage'];	
				}
			}else if ($_POST['overrideredirecturltype'] == DEFAULT_WORDPRESS_CUSTOM_URL){//check if redirect type is custom url....
				if(!empty($_POST['customurl'])){
					$defaultThanksArray['redirectType'] = $_POST['overrideredirecturltype'];
					$defaultThanksArray['redirectValue'] = $_POST['customurl'];
				}
			}
		}
		//update the option "default_thankyou_details" to save the default thankyou details.......
		update_option('default_thankyou_details', $defaultThanksArray);
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to add/update the product thankyou override.....
add_action( 'wp_ajax_wc_save_thanks_product_override', 'wc_save_thanks_product_override');
//Function Definiation : wc_save_thanks_product_override
function wc_save_thanks_product_override()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
        
        //override main table...
        $override_table_name = 'wooconnection_thankyou_overrides';
        $wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
    	
    	//override products table name....
        $override_product_table_name = 'wooconnection_thankyou_override_related_products';
        $wp_thankyou_override_related_products = $table_prefix . "$override_product_table_name";
		
		$override_fields_array = array();//define empty array...
		//check the post variables and assign to array....
		if(isset($_POST['procductoverridename']) && !empty($_POST['procductoverridename'])){
			$override_fields_array['wc_override_name'] = trim($_POST['procductoverridename']);
		}
		if(isset($_POST['productrediecturl']) && !empty($_POST['productrediecturl'])){
			$override_fields_array['wc_override_redirect_url'] = trim($_POST['productrediecturl']);
		}
		if(!empty($_POST['redirectcartproducts'])){
			$override_products_array = $_POST['redirectcartproducts'];
		}
		//assign redirect condition product thankyou override.....
		$override_fields_array['wc_override_redirect_condition'] = REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS;
		//first check the override id is exist in post data if exist then needs to perform update override process.....
		if(isset($_POST['productoverrideid']) && !empty($_POST['productoverrideid'])){
			$result_check_update = $wpdb->update($wp_thankyou_override_table_name,$override_fields_array,array('id' => $_POST['productoverrideid']));
			//if products array exist and not empty then add entries in tables..
	   		if(isset($override_products_array) && !empty($override_products_array)){
	   			$updateResultPro = $wpdb->update($wp_thankyou_override_related_products, array('wc_override_product_status' => STATUS_DELETED),array('override_id' => $_POST['productoverrideid']));
				$override_pro_array = array();
				$override_pro_array['override_id'] = $_POST['productoverrideid'];
	   			foreach ($override_products_array as $key => $value) {
	   				if(!empty($value)){
	   					$override_pro_array['override_product_id'] = $value;
						$result_check_group_products = $wpdb->insert($wp_thankyou_override_related_products,$override_pro_array);
	   				}
	   			}
	   		}
	   	}else{//if override is not exist then need to add new product override....
			//insert the record for custom field group 
			$result_check_override_check = $wpdb->insert($wp_thankyou_override_table_name,$override_fields_array);
			//check if insert successfull...
			if($result_check_override_check){
			   //get the last insert group id...
			   $lastInsertId = $wpdb->insert_id;
			   //check if last insert id exist...
			   if(!empty($lastInsertId)){
			   		//check if last insert id is exist then update the sort order ...
			   		$updateResult = $wpdb->update($wp_thankyou_override_table_name, array('wc_override_sort_order' => $lastInsertId),array('id' => $lastInsertId));
			   		
			   		//if products array exist and not empty then add entries in tables..
			   		if(isset($override_products_array) && !empty($override_products_array)){
						$override_products_array_data = array();
						$override_products_array_data['override_id'] = $lastInsertId;
						foreach ($override_products_array as $key => $value) {
							$override_products_array_data['override_product_id'] = $value;
							$result_check_group_products = $wpdb->insert($wp_thankyou_override_related_products,$override_products_array_data);
						}
					}
				}
			}
		}	
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to add/update the product category thankyou override.....
add_action( 'wp_ajax_wc_save_thanks_product_category_override', 'wc_save_thanks_product_category_override');
//Function Definiation : wc_save_thanks_product_category_override
function wc_save_thanks_product_category_override()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
		
		//override main table...
        $override_table_name = 'wooconnection_thankyou_overrides';
        $wp_thankyou_override_table_name = $table_prefix . "$override_table_name";

        //override cat table name....
        $override_cat_table_name = 'wooconnection_thankyou_override_related_categories';
        $wp_thankyou_override_related_categories = $table_prefix . "$override_cat_table_name";

        $override_fields_cat_array = array();//define empty array...
        //check the post variables and assign to array....
		if(isset($_POST['productcatoverridename']) && !empty($_POST['productcatoverridename'])){
			$override_fields_cat_array['wc_override_name'] = trim($_POST['productcatoverridename']);
		}
		if(isset($_POST['productcatrediecturl']) && !empty($_POST['productcatrediecturl'])){
			$override_fields_cat_array['wc_override_redirect_url'] = trim($_POST['productcatrediecturl']);
		}
		if(!empty($_POST['redirectcartcategories'])){
			$override_categories_array = $_POST['redirectcartcategories'];
		}
		//assign redirect condition product thankyou override.....
		$override_fields_cat_array['wc_override_redirect_condition'] = REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES;
		//first check the override id is exist in post data if exist then needs to perform update override process.....
		if(isset($_POST['productcatoverrideid']) && !empty($_POST['productcatoverrideid'])){
			$result_check_update = $wpdb->update($wp_thankyou_override_table_name,$override_fields_cat_array,array('id' => $_POST['productcatoverrideid']));
			//if products array exist and not empty then add entries in tables..
			if(isset($override_categories_array) && !empty($override_categories_array)){
	   			$updateResultCat = $wpdb->update($wp_thankyou_override_related_categories, array('wc_override_cat_status' => STATUS_DELETED),array('override_id' => $_POST['productcatoverrideid']));
				$override_cat_array = array();
				$override_cat_array['override_id'] = $_POST['productcatoverrideid'];
	   			foreach ($override_categories_array as $key => $value) {
	   				if(!empty($value)){
	   					$override_cat_array['override_cat_id'] = $value;
						$result_check_group_cat = $wpdb->insert($wp_thankyou_override_related_categories,$override_cat_array);
	   				}
	   			}
	   		}
		}else{//if override is not exist then need to add new product override....
			//insert the record for custom field group 
			$result_check_override_check = $wpdb->insert($wp_thankyou_override_table_name,$override_fields_cat_array);
			//check if insert successfull...
			if($result_check_override_check){
			   //get the last insert group id...
			   $lastInsertId = $wpdb->insert_id;
			   //check if last insert id exist...
			   if(!empty($lastInsertId)){
			   		//check if last insert id is exist then update the sort order ...
			   		$updateResult = $wpdb->update($wp_thankyou_override_table_name, array('wc_override_sort_order' => $lastInsertId),array('id' => $lastInsertId));
			   		
		   			//if categories array exist and not empty then add entries in tables..
					if(isset($override_categories_array) && !empty($override_categories_array)){
						$override_cat_array = array();
						$override_cat_array['override_id'] = $lastInsertId;
						foreach ($override_categories_array as $key => $value) {
							$override_cat_array['override_cat_id'] = $value;
							$result_check_cat_products = $wpdb->insert($wp_thankyou_override_related_categories,$override_cat_array);
						}
					}
				}
			}
		}	
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}


//Wordpress hook : This action is triggered when user try to eidt the default thankyou override.....
add_action( 'wp_ajax_wc_get_thankyou_default_override', 'wc_get_thankyou_default_override');
//Function Definiation : wc_get_thankyou_default_override
function wc_get_thankyou_default_override()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		$redirectType = '';//define empty variable...
		$redirectValue = '';//define empty variable...
		//check option "default_thankyou_details" exist in wp_options table if yes then set the values of redirect type and redirect value.....
		if(isset($_POST['option']) && !empty($_POST['option'])){
			$default_thankyou_details = get_option($_POST['option']);
			if (isset($default_thankyou_details) && !empty($default_thankyou_details)) {
				if(!empty($default_thankyou_details['redirectType'])){
					$redirectType = $default_thankyou_details['redirectType'];
				}
				if(!empty($default_thankyou_details['redirectValue'])){
					$redirectValue = $default_thankyou_details['redirectValue'];
				}
			}
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'redirectType'=>$redirectType,'redirectValue'=>$redirectValue));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to delete thankyou override.....
add_action( 'wp_ajax_wc_delete_thankyou_override', 'wc_delete_thankyou_override');
//Function Definiation : wc_delete_thankyou_override
function wc_delete_thankyou_override()
{
	//first check the override id is exist in post data if exist then needs to perform delete override process.....
	if(isset($_POST['overrideid']) && !empty($_POST['overrideid'])){
		global $table_prefix, $wpdb;
       	
       	//override main table...
        $override_table_name = 'wooconnection_thankyou_overrides';
        $wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
    	
    	//override products table name....
        $override_product_table_name = 'wooconnection_thankyou_override_related_products';
        $wp_thankyou_override_related_products = $table_prefix . "$override_product_table_name";

        //override cat table name....
        $override_cat_table_name = 'wooconnection_thankyou_override_related_categories';
        $wp_thankyou_override_related_categories = $table_prefix . "$override_cat_table_name";
		
		//mark override as a deleted.....
		$updateResult = $wpdb->update($wp_thankyou_override_table_name, array('wc_override_status' => STATUS_DELETED),array('id' => $_POST['overrideid']));
		//if update done sucessfully then needs to update the related products entires as a deleted in "$wp_thankyou_override_related_products" table or in "$wp_thankyou_override_related_categories" table depends on the overridetype
		if($updateResult){
			if(isset($_POST['overridetype']) && !empty($_POST['overridetype'])){
				if($_POST['overridetype'] == REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS){
					//get the related products and update the status to mark deleted...
					$relatedProducts = get_override_related_products($_POST['overrideid']);
					if(isset($relatedProducts) && !empty($relatedProducts)){
						$updateResultPro = $wpdb->update($wp_thankyou_override_related_products, array('wc_override_product_status' => STATUS_DELETED),array('override_id' => $_POST['overrideid']));
					}	
				}else if ($_POST['overridetype'] == REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES) {
					//get the related cat and update the status to mark deleted...
					$relatedCat = get_override_related_cat($_POST['overrideid']);
					if(isset($relatedCat) && !empty($relatedCat)){
							$updateResultCat = $wpdb->update($wp_thankyou_override_related_categories, array('wc_override_cat_status' => STATUS_DELETED),array('override_id' => $_POST['overrideid']));
					}
				}
			}	
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Wordpress hook : This function is used to load the latest thanks override after add/edit/delete override.....
add_action( 'wp_ajax_loading_thanks_overrides', 'loading_thanks_overrides');
//Function Definiation : loading_thanks_overrides
function loading_thanks_overrides()
{
	$thankyouOverridesListing = '';//define empty variable......
	//check override type then on the basis of it call the common function........
	if(isset($_POST['overridesType']) && !empty($_POST['overridesType'])){
		if($_POST['overridesType'] == REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS){
			$thankyouOverridesListing = loading_product_thanks_overrides();//call the common function to get the list of product thankyou overrides
		}else if ($_POST['overridesType'] == REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES) {
			$thankyouOverridesListing = loading_product_cat_thanks_overrides();//call the common function to get the list of product category thankyou overrides
		}
	}
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'thankyouOverridesListing'=>$thankyouOverridesListing));
	die();
}

//Wordpress hook : This action is triggered when user try to edit the product thankyou override and then get the product details and return to show in the form...............
add_action( 'wp_ajax_wc_get_product_thankyou_override', 'wc_get_product_thankyou_override');
//Function Definiation : wc_get_product_thankyou_override
function wc_get_product_thankyou_override()
{
	//first check the override id is exist in post data if exist then get the details of it.....
	if(isset($_POST['overrideid']) && !empty($_POST['overrideid']))
	{
		global $table_prefix, $wpdb;
       	$override_table_name = 'wooconnection_thankyou_overrides';
      	$wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
        $thankyouOverride = $wpdb->get_results("SELECT * FROM ".$wp_thankyou_override_table_name." WHERE id=".$_POST['overrideid']." and wc_override_status =".STATUS_ACTIVE);
        if(isset($thankyouOverride) && !empty($thankyouOverride)){
        	$overridename = "";//define empty variable......
        	$overrideurl = "";//define empty variable......
        	$products = array();//define empty array...
        	if(!empty($thankyouOverride[0]->wc_override_name)){
        		$overridename = $thankyouOverride[0]->wc_override_name;
        	}
        	if(!empty($thankyouOverride[0]->wc_override_redirect_url)){
        		$overrideurl = $thankyouOverride[0]->wc_override_redirect_url;
        	}
        	if(!empty($thankyouOverride[0]->wc_override_redirect_condition)){
        		if($thankyouOverride[0]->wc_override_redirect_condition == REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS){
        			$products = get_override_related_products($_POST['overrideid']);//call the common function to get the product related to thanks override....
        		}
        	}
        	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'overridename'=>$overridename,'overrideurl'=>$overrideurl,'products'=>$products));	
        }	
	}
	die();
}

//Wordpress hook : This action is triggered when user try to edit the product category thankyou override and then get the product details and return to show in the form...............
add_action( 'wp_ajax_wc_get_product_cat_thankyou_override', 'wc_get_product_cat_thankyou_override');
//Function Definiation : wc_get_product_cat_thankyou_override
function wc_get_product_cat_thankyou_override()
{
	//first check the override id is exist in post data if exist then get the details of it.....
	if(isset($_POST['overrideid']) && !empty($_POST['overrideid']))
	{
		global $table_prefix, $wpdb;
       	$override_table_name = 'wooconnection_thankyou_overrides';
      	$wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
        $thankyouOverride = $wpdb->get_results("SELECT * FROM ".$wp_thankyou_override_table_name." WHERE id=".$_POST['overrideid']." and wc_override_status =".STATUS_ACTIVE);
        if(isset($thankyouOverride) && !empty($thankyouOverride)){
        	$overridename = "";//define empty variable......
        	$overrideurl = "";//define empty variable......
        	$categories = array();//define empty array...
        	if(!empty($thankyouOverride[0]->wc_override_name)){
        		$overridename = $thankyouOverride[0]->wc_override_name;
        	}
        	if(!empty($thankyouOverride[0]->wc_override_redirect_url)){
        		$overrideurl = $thankyouOverride[0]->wc_override_redirect_url;
        	}
        	if(!empty($thankyouOverride[0]->wc_override_redirect_condition)){
        		if($thankyouOverride[0]->wc_override_redirect_condition == REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES){
        			$categories = get_override_related_cat($_POST['overrideid']);//call the common function to get the category related to thanks override....
        		}
        	}
        	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'overridename'=>$overridename,'overrideurl'=>$overrideurl,'categories'=>$categories));	
        }	
	}
	die();
}

//Wordpress hook : This action is triggered when user try to sort the thank you page overrides and then update the sorting order.....
add_action( 'wp_ajax_update_thankyou_overrides_order', 'update_thankyou_overrides_order');
//Function Definiation : update_thankyou_overrides_order
function update_thankyou_overrides_order()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
       	//override main table...
        $override_table_name = 'wooconnection_thankyou_overrides';
        $wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
        //then check the sort order array exist in post data.........
		if(isset($_POST['order']) && !empty($_POST['order'])){
			//excuate a loop to update the sort order......
			for($i = 0; $i < count($_POST['order']); $i++) {
			    $override_id = $_POST['order'][$i];
			    $latest_order = $i+1;
			    if(isset($override_id) && !empty($override_id)){
					//check if last insert id is exist then update the sort order ...
			   		$updateResult = $wpdb->update($wp_thankyou_override_table_name, array('wc_override_sort_order' => $latest_order),array('id' => $override_id));
			    }
			}
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

?>