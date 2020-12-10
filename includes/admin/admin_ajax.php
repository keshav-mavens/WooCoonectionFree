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
		$latestHtml = '';
		if ($_POST['target_tab_id'] == '#table_export_products') {
			$latestHtml = createExportProductsHtml();
		}else if ($_POST['target_tab_id'] == '#table_match_products') {
			$latestHtml = createMatchProductsHtml();
		}else if ($_POST['target_tab_id'] == '#table_standard_fields_mapping') {
			$latestHtml = createStandardFieldsMappingHtml();
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
  			$applicationProductsArray = getApplicationProducts();
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to update the default thanlkyou override.....
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to add/update the product thankyou override.....
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to add/update the product category thankyou override.....
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


//Dynamic Thankyou Override : wordpress hook is triggered when user try to eidt the default thankyou override.....
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to delete thankyou override.....
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

//Dynamic Thankyou Override : wordpress hook is used to load the latest thanks override after add/edit/delete override.....
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to edit the product thankyou override and then get the product details and return to show in the form...............
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to edit the product category thankyou override and then get the product details and return to show in the form...............
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

//Dynamic Thankyou Override : wordpress hook is triggered when user try to sort the thank you page overrides and then update the sorting order.....
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

//Custom fields Tab : wordpress hook is call when user click on save button of custom field application form to save custom field in application.....
add_action( 'wp_ajax_wc_save_cfield_app', 'wc_save_cfield_app');
//Function Definiation : wc_save_cfield_app
function wc_save_cfield_app(){
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

	    //check accesss token...
	    if(!empty($access_token)){
	  		if(!empty($_POST['cfieldformtypeapp']) && !empty($_POST['cfieldnameapp']))
			{
				//call the common function to add the custom field in application whether it is for contact or order.......
				$customFieldRes = addCustomField($access_token,$_POST['cfieldformtypeapp'],$_POST['cfieldnameapp'],$_POST['cfieldtypeapp'],$_POST['cfieldheaderapp']);
				if(is_int($customFieldRes)){
					$preFields = getPredefindCustomfields();//call the common function to get the latest custom fields listing.
					$cfieldOptions = "<option value=''></option>";
					if(isset($preFields) && !empty($preFields)){
						//create options html.....
						foreach($preFields as $key => $value) {
							$cfieldOptions .= "<optgroup label=\"$key\">";
							foreach($value as $key1 => $value1) {
								$cfieldoptionSelected = "";
								$cfieldOptions .= '<option value="'.$key1.'"'.$cfieldoptionSelected.'>'.$value1.'</option>';
							}
							$cfieldOptions .= "</optgroup>";
						}
					}
					//return response with options html....
					echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldOptions'=>$cfieldOptions,'cfieldName'=>trim($_POST['cfieldnameapp'])));
				}
			}	
	    }else{
			echo json_encode(array('status'=>RESPONSE_STATUS_FALSE,'errormessage'=>'Authentication Error'));
	    }
		
	}
	die();
}


//Custom fields Tab : wordpress hook is call to get the custom fields tab on change custom field type e.g contact, order at the time of custom field creation....
add_action( 'wp_ajax_wc_cfield_app_tabs', 'wc_cfield_app_tabs');
//Function Definiation : wc_cfield_app_tabs
function wc_cfield_app_tabs(){
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		//first check form type.....
		if(isset($_POST['cfieldFormType']) && !empty($_POST['cfieldFormType'])){
		 	//call the common function to get the tabs on the basis of form type e.g contact , order.......
		 	$cfieldtabsHtml =	cfRelatedTabs($_POST['cfieldFormType']);
		}
		//return response with tabs html....
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldtabsHtml'=>$cfieldtabsHtml));
	}
	die();
}



//Custom fields Tab : wordpress hook is call to get the custom fields headers on change custom field tab at the time of custom field creation....
add_action( 'wp_ajax_wc_cfield_app_headers', 'wc_cfield_app_headers');
//Function Definiation : wc_cfield_app_headers
function wc_cfield_app_headers(){
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		//first check form type.....
		if(isset($_POST['cfieldFormTab']) && !empty($_POST['cfieldFormTab'])){
			//call the common function to get the headers on the basis of tab.......
			$cfieldheaderHtml =	cfRelatedHeaders($_POST['cfieldFormTab']);
		}
		//return response with headers html....
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldheaderHtml'=>$cfieldheaderHtml));
	}
	die();
}

//Custom fields Tab : wordpress hook is call when user click on save button of custom field group form.....
add_action( 'wp_ajax_wc_save_cfield_group', 'wc_save_cfield_group');
//Function Definiation : wc_save_cfield_group
function wc_save_cfield_group()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		
		global $table_prefix, $wpdb;
        //define table name....
        $cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        
        $cfield_group_fields_array = array();//empty array....
		//check group name.....
		if(isset($_POST['cfieldgroupname']) && !empty($_POST['cfieldgroupname'])){
			$cfield_group_fields_array['wc_custom_field_group_name'] = trim($_POST['cfieldgroupname']);
		}
		
		//check group id then needs to update......
		if(isset($_POST['cfieldgroupid']) && !empty($_POST['cfieldgroupid'])){

			$result_cfield_group = $wpdb->update($cfield_group_table_name,$cfield_group_fields_array,array('id' => $_POST['cfieldgroupid']));
		}else{//else needs to create new custom field group....
			$result_cfield_group = $wpdb->insert($cfield_group_table_name,$cfield_group_fields_array);
			if($result_cfield_group){
			    $cfieldGroupLastInsertId = $wpdb->insert_id;
			    //check last created custom field group id.....
			    if(!empty($cfieldGroupLastInsertId)){
			    	//then update the sort order with its primary key....
			   		$cfieldGroupUpdateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_sort_order' => $cfieldGroupLastInsertId),array('id' => $cfieldGroupLastInsertId));
			   	}
			}
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
    }
    die();
}

//Custom fields Tab : wordpress hook is call to load the all custom fields at the time delete,edit,add custom field....
add_action( 'wp_ajax_wc_loading_cfields', 'wc_loading_cfields');
//Function Definiation : wc_loading_cfields
function wc_loading_cfields()
{
	$cfieldhtml = get_cfields_groups();//call the function to get the custom fields
	//return response with html....
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldhtml'=>$cfieldhtml));
	die();
}

//Custom fields Tab : Code is used get the custom fields groups and its custom fields then create html....
function get_cfields_groups(){
  global $wpdb,$table_prefix;
  //define table name....
  $cfield_group_table_name = 'wooconnection_custom_field_groups';
  $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
  //get custom field groups which is not deleted......
  $customFieldGroups = $wpdb->get_results("SELECT * FROM ".$cfield_group_table_name." WHERE wc_custom_field_group_status !=".STATUS_DELETED." ORDER BY wc_custom_field_sort_order ASC");
  $customFieldsListing = "";//define variable.....
  //check if data is not empty rotate loop....
  if(isset($customFieldGroups) && !empty($customFieldGroups)){
    foreach ($customFieldGroups as $key => $value) {
        if(!empty($value->id)){
        	$custom_fields_html = get_cfields($value->id);//get the custom fields by parent group id....
        	//set show/hide icon on the basis of its status.....
        	if($value->wc_custom_field_group_status == STATUS_ACTIVE){
        		$showhidehtml = '<i class="fa fa-eye showhidecfieldgroup" title="Hide custom field group with its custom fields" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_HIDE.'" aria-hidden="true"></i>';
        	}else{
        		$showhidehtml = '<i class="fa fa-eye-slash showhidecfieldgroup" title="Show custom field group with its custom fields" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_SHOW.'" aria-hidden="true"></i>';	
        	}
        	//concate a html inside loop....
        	$customFieldsListing .=  '<li class="group-list" id="'.$value->id.'"><span class="group-name">'.$value->wc_custom_field_group_name.'<span class="controls"><i class="fa fa-plus addgroupcfield" title="Add custom field to this group" data-id="'.$value->id.'"></i>
        		<i class="fa fa-pencil editcfieldgroup" title="Edit custom field group details" data-id="'.$value->id.'">
        		</i>'.$showhidehtml.'<i class="fa fa-times deletecfieldgroup" title="Delete custom field group" data-id="'.$value->id.'"></i></span></span>'.$custom_fields_html.'</li>';
        }
    }
  }
  return $customFieldsListing;//return html
}

//Custom fields Tab : Code is used get the custom fields by parent group id then create html....
function get_cfields($groupid){
	global $wpdb,$table_prefix;
	//define table name....
	$cfields_table_name = 'wooconnection_custom_fields';
 	$cfields_table_name = $table_prefix . "$cfields_table_name";
	$customFieldsHtml = '';
	//check parent group id....
	if(!empty($groupid)){
		//get all custom fields related to particular group......
		$customFields = $wpdb->get_results("SELECT * FROM ".$cfields_table_name." WHERE wc_cf_group_id = ".$groupid." and wc_cf_status!=".STATUS_DELETED." ORDER BY wc_cf_sort_order ASC");
		//check if data is not empty rotate loop....
		if(isset($customFields) && !empty($customFields)){
			$customFieldsHtml .= '<ul class="group-fields group_custom_field_'.$groupid.'">';
			foreach ($customFields as $key => $value) {
				//set show/hide icon on the basis of its status.....
				if($value->wc_cf_status == STATUS_ACTIVE){
	        		$showhidehtml = '<i class="fa fa-eye showhidecfield" title="Hide custom field" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_HIDE.'" aria-hidden="true"></i>';
	        	}else{
	        		$showhidehtml = '<i class="fa fa-eye-slash showhidecfield" title="Show custom field" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_SHOW.'" aria-hidden="true"></i>';	
	        	}
				//concate a html inside loop....
				$customFieldsHtml .= '<li class="group-field" id="'.$value->id.'">'.$value->wc_cf_name.'<span class="controls"><i class="fa fa-pencil editcfield" title="Edit Current Custom Field" data-id="'.$value->id.'"></i>'.$showhidehtml.'<i class="fa fa-times deletecfield" title="Edit Current Custom Field" data-id="'.$value->id.'"></i></span></li>';
			}
			$customFieldsHtml .= '</ul>';
		}
	}
	return $customFieldsHtml;//return html
}

//Custom fields Tab : wordpress hook is call when user click on "*" icon of particular custom field group then proceed the delete process......
add_action( 'wp_ajax_wc_delete_cfield_group', 'wc_delete_cfield_group');
//Function Definiation : wc_delete_cfield_group
function wc_delete_cfield_group()
{
	//check custom field group id exist in post data.....
	if(isset($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupId'])){
		global $table_prefix, $wpdb;
       	//define table name....
       	$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        //update custom field status in database....
        $updateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_group_status' => STATUS_DELETED),array('id' => $_POST['cfieldgroupId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Custom fields Tab : wordpress hook is call when user click on "edit" icon of particular custom field group then hide the custom fields listing and show the custom field group form......
add_action( 'wp_ajax_wc_get_cfield_group', 'wc_get_cfield_group');
//Function Definiation : wc_get_cfield_group
function wc_get_cfield_group()
{
	//check custom field group id exist in post data.....
	if(isset($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupId']))
	{
		global $table_prefix, $wpdb;
       	//define table name....
       	$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        //get all custom field group data on the basis of id......
        $cfieldgroup = $wpdb->get_results("SELECT * FROM ".$cfield_group_table_name." WHERE id=".$_POST['cfieldgroupId']." and wc_custom_field_group_status !=".STATUS_DELETED);
        //check if data is not empty....
        if(isset($cfieldgroup) && !empty($cfieldgroup)){
        	$cfieldgroupname = "";//define empty variable....
        	//set the group name....
        	if(!empty($cfieldgroup[0]->wc_custom_field_group_name)){
        		$cfieldgroupname = $cfieldgroup[0]->wc_custom_field_group_name;
        	}
        	//return response with custom field group name....
        	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldgroupname'=>$cfieldgroupname));	
        }	
	}
	die();
}


//Custom fields Tab : wordpress hook is call when user click on "eye" or "eye-slash" icon of particular custom field group then proceed to set status show or hide custom field group and also of its custom fields......
add_action( 'wp_ajax_wc_update_cfieldgroup_showhide', 'wc_update_cfieldgroup_showhide');
//Function Definiation : wc_update_cfieldgroup_showhide
function wc_update_cfieldgroup_showhide()
{
	if(isset($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupactiontype'])){
		global $table_prefix, $wpdb;
       	//define table names....
       	$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        $cfields_table_name = 'wooconnection_custom_fields';
    	$cfields_table_name = $table_prefix . "$cfields_table_name";

		//set show/hide on the basis of action type.....
		if($_POST['cfieldgroupactiontype'] == CF_FIELD_ACTION_SHOW){
			$status = STATUS_ACTIVE;
		}elseif ($_POST['cfieldgroupactiontype'] == CF_FIELD_ACTION_HIDE) {
			$status = STATUS_INACTIVE;
		}
		//update custom field group status....
		$updateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_group_status' => $status),array('id' => $_POST['cfieldgroupId']));
		//once the group status is update then update the show/hide status of its custom  fields.....
		if($updateResult){
			$wpdb->query($wpdb->prepare('UPDATE '.$cfields_table_name.' SET wc_cf_status = '.$status.' WHERE wc_cf_group_id = '.$_POST['cfieldgroupId'].' and  wc_cf_status != '.STATUS_DELETED.''));
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Custom fields Tab : wordpress hook is call when user click on save button of custom field form.....
add_action( 'wp_ajax_wc_save_groupcfield', 'wc_save_groupcfield');
//Function Definiation : wc_save_groupcfield
function wc_save_groupcfield()
{
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
		//define table names....
		$cfields_table_name = 'wooconnection_custom_fields';
    	$cfields_table_name = $table_prefix . "$cfields_table_name";
		
		$cfields_array = array();//define empty array....
		if(isset($_POST['cfieldname']) && !empty($_POST['cfieldname'])){
			$cfields_array['wc_cf_name'] = trim($_POST['cfieldname']);
		}
		//check field type then set or get the values in variables....
		if(isset($_POST['cfieldtype']) && !empty($_POST['cfieldtype'])){
			$cfields_array['wc_cf_type'] = $_POST['cfieldtype'];
			if($_POST['cfieldtype'] == CF_FIELD_TYPE_DROPDOWN || $_POST['cfieldtype'] == CF_FIELD_TYPE_RADIO)
			{
	         	$customOptionsArray = array();//define empty array....
	         	$cfieldsOptionBreak = '';
	         	if(isset($_POST['cfieldoptionvalue']) && !empty($_POST['cfieldoptionvalue']) && isset($_POST['cfieldoptionlabel']) && !empty($_POST['cfieldoptionlabel']))
				{
				    $cfields_value = $_POST['cfieldoptionvalue'];
				    $cfields_label = $_POST['cfieldoptionlabel'];
				    if(count($cfields_value) > 0)
				    {               
				        for( $i = 1; $i <= count($cfields_value); $i++)
				        {
				            $customOptionsArray[] =  $cfields_value[$i].'#'.$cfields_label[$i];
				        }
				    }
				}
	            if(isset($customOptionsArray) && !empty($customOptionsArray)){
	            	$cfieldsOptionBreak = implode('@', $customOptionsArray);
	            	if ($cfieldsOptionBreak != ''){
	            		$cfields_array['wc_cf_options'] = $cfieldsOptionBreak;
	            	}
	            }
	            if(isset($_POST['cfielddefault2value']) && !empty($_POST['cfielddefault2value'])){
	            	$cfields_array['wc_cf_default_value'] = trim($_POST['cfielddefault2value']);
	            }
	     		
	     	}else if ($_POST['cfieldtype'] == CF_FIELD_TYPE_CHECKBOX) {
	     		if(isset($_POST['cfielddefault1value'])){
	            	$cfields_array['wc_cf_default_value'] = trim($_POST['cfielddefault1value']);
	            }
	     	}
		}
		if(isset($_POST['cfieldmandatory']) && !empty($_POST['cfieldmandatory'])){
			$cfields_array['wc_cf_mandatory'] = $_POST['cfieldmandatory'];
		}
		if(isset($_POST['cfieldplaceholder']) && !empty($_POST['cfieldplaceholder'])){
			$cfields_array['wc_cf_placeholder'] = trim($_POST['cfieldplaceholder']);
		}
		if(isset($_POST['cfieldmapping']) && !empty($_POST['cfieldmapping'])){
			$cfieldMappedWith = trim($_POST['cfieldmapping']);
			if(strpos($cfieldMappedWith, "FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.":") !== false) {
				$mappedcfieldData = explode("FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.":", $cfieldMappedWith);
				$mappedWith = $mappedcfieldData[1];
				$cfields_array['wc_cf_mapped_field_type'] = CUSTOM_FIELD_FORM_TYPE_CONTACT;
			}elseif (strpos($cfieldMappedWith, "FormType:".CUSTOM_FIELD_FORM_TYPE_ORDER.":") !== false) {
				$mappedcfieldData = explode("FormType:".CUSTOM_FIELD_FORM_TYPE_ORDER.":", $cfieldMappedWith);
				$mappedWith = $mappedcfieldData[1];
				$cfields_array['wc_cf_mapped_field_type'] = CUSTOM_FIELD_FORM_TYPE_ORDER;
			}
			$cfields_array['wc_cf_mapped'] = $mappedWith;
		}else{
			$cfields_array['wc_cf_mapped'] = '';
		}
		//check if custom field id is exist then perform the update process....
		if(isset($_POST['cfieldid']) && !empty($_POST['cfieldid'])){
			$resultcfieldupdate = $wpdb->update($cfields_table_name,$cfields_array,array('id' => $_POST['cfieldid']));
			echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
		}else{//else needs to create new custom field group....
			if(isset($_POST['cfieldparentgroupid']) && !empty($_POST['cfieldparentgroupid'])){
				if(isset($_POST['cfieldparentgroupid']) && !empty($_POST['cfieldparentgroupid'])){
					$cfields_array['wc_cf_group_id'] = $_POST['cfieldparentgroupid'];
				}
				$resultcfield = $wpdb->insert($cfields_table_name,$cfields_array);
				if($resultcfield){
					//check last created custom field id.....
				   	$lastcfieldInsertId = $wpdb->insert_id;
				   	if(!empty($lastcfieldInsertId)){
				   		//then update the sort order with its primary key....
				   		$updateResult = $wpdb->update($cfields_table_name, array('wc_cf_sort_order' => $lastcfieldInsertId),array('id' => $lastcfieldInsertId));
				   	}
				}
				echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
			}
		}
	}
	die();
}

//Custom fields Tab : wordpress hook is call when user click on "eye" or "eye-slash" icon of particular custom field then proceed to set status show or hide custom field......
add_action( 'wp_ajax_wc_update_cfield_showhide', 'wc_update_cfield_showhide');
//Function Definiation : wc_update_cfield_showhide
function wc_update_cfield_showhide()
{
	//check if custom field id is exist then perform the update show/hide status process....
	if(isset($_POST['cfieldId']) && !empty($_POST['cfieldId']) && !empty($_POST['cfieldactiontype'])){
		global $table_prefix, $wpdb;
		//define table names....
       	$cfield_table_name = 'wooconnection_custom_fields';
        $cfield_table_name = $table_prefix . "$cfield_table_name";
        //set show/hide on the basis of action type.....
		if($_POST['cfieldactiontype'] == CF_FIELD_ACTION_SHOW){
			$status = STATUS_ACTIVE;
		}elseif ($_POST['cfieldactiontype'] == CF_FIELD_ACTION_HIDE) {
			$status = STATUS_INACTIVE;
		}
		//update custom field group status....
		$updateResult = $wpdb->update($cfield_table_name, array('wc_cf_status' => $status),array('id' => $_POST['cfieldId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}


//Custom fields Tab : wordpress hook is call when user click on "*" icon of particular custom field then proceed the delete process.....
add_action( 'wp_ajax_wc_delete_cfield', 'wc_delete_cfield');
//Function Definiation : wc_delete_cfield
function wc_delete_cfield()
{
	//check if custom field id is exist then proceed delete process....
	if(isset($_POST['cfieldId']) && !empty($_POST['cfieldId'])){
		global $table_prefix, $wpdb;
		//define table names....
		$cfield_table_name = 'wooconnection_custom_fields';
    	$cfield_table_name = $table_prefix . "$cfield_table_name";
    	//update custom field status....
		$updateResult = $wpdb->update($cfield_table_name, array('wc_cf_status' => STATUS_DELETED),array('id' => $_POST['cfieldId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Custom fields Tab : wordpress hook is call when user click on "edit" icon of particular custom field then hide the custom fields listing and show the custom field form......
add_action( 'wp_ajax_wc_get_cfield', 'wc_get_cfield');
//Function Definiation : wc_get_cfield
function wc_get_cfield()
{
	//check if custom field id is exist then get then get the details of it....
	if(isset($_POST['cfieldId']) && !empty($_POST['cfieldId']))
	{
		global $table_prefix, $wpdb;
		//define table names....
		$cfield_table_name = 'wooconnection_custom_fields';
    	$cfield_table_name = $table_prefix . "$cfield_table_name";
    	//get all custom field data on the basis of id......
    	$cfieldData = $wpdb->get_results("SELECT * FROM ".$cfield_table_name." WHERE id=".$_POST['cfieldId']." and wc_cf_status !=".STATUS_DELETED);
    	if(isset($cfieldData) && !empty($cfieldData)){
    		//define empty variables and arrays....
    		$cfieldname = "";
    		$cfieldtype = "";
    		$cfieldplaceholder = "";
    		$cfieldoptionsarray = "";
    		$cfieldmandatory = "";
    		$cfieldmapped = "";
    		$cfieldoptionValues = array();
    		$cfieldoptionVal = "";
    		$cfieldoptionLab = "";
    		$cfoptionsarray = array();
    		$cfieldoptionsHtml = '';
    		$cfieldoptionsCount = '';
    		$cfielddefaultvalue = '';
    		//set and get the values.....
    		if(!empty($cfieldData[0]->wc_cf_name)){
        		$cfieldname = $cfieldData[0]->wc_cf_name;
        	}
        	if(!empty($cfieldData[0]->wc_cf_type)){
        		$cfieldtype = $cfieldData[0]->wc_cf_type;
        	}
        	if(!empty($cfieldtype)){
        		if($cfieldtype == CF_FIELD_TYPE_TEXT || $cfieldtype == CF_FIELD_TYPE_TEXT || $cfieldtype == CF_FIELD_TYPE_DATE){
        			if(!empty($cfieldData[0]->wc_cf_placeholder)){
						$cfieldplaceholder = $cfieldData[0]->wc_cf_placeholder;
        			}
        		}elseif ($cfieldtype == CF_FIELD_TYPE_DROPDOWN || $cfieldtype == CF_FIELD_TYPE_RADIO) {
        			if(!empty($cfieldData[0]->wc_cf_options)){
        				$cfieldoptionsarray = explode("@",$cfieldData[0]->wc_cf_options);
    					foreach ($cfieldoptionsarray as $key => $value) {
    						$cfieldoptionValues[] = $value;
    					}
    				}
        		}
        	}
        	foreach ($cfieldoptionValues as $key => $value) {
    			$cfoptionsarray[] = explode("#",$value);
    		}
    		$cfieldoptionsCount =	count($cfoptionsarray);
			if($cfieldoptionsCount == 1){
				$cfieldoptionVal = $cfoptionsarray[0][0];
				$cfieldoptionLab = $cfoptionsarray[0][1];
			}else{
				$cfieldoptionVal = $cfoptionsarray[0][0];
				$cfieldoptionLab = $cfoptionsarray[0][1];
				$arraycount = 2;
				foreach(array_slice($cfoptionsarray,1) as $key=>$value)
				{
				    $cfieldoptionsHtml .= '<div class="form-group row custom_options_'.$arraycount.'"><label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label"></label><div class="col-lg-10 col-md-9 col-sm-12 col-12"><div class="row"><div class="col-lg-6"><input type="text" name="cfieldoptionvalue['.$arraycount.']" placeholder="Field Value" id="cfieldoptionvalue_'.$arraycount.'" value="'.$value[0].'" required></div><div class="col-lg-5"><input type="text" name="cfieldoptionlabel['.$arraycount.']" placeholder="Field Label" id="cfieldoptionlabel_'.$arraycount.'" value="'.$value[1].'" required></div><div class="col-lg-1 removecfieldoptions" data-target="custom_options_'.$arraycount.'"><i class="fa fa-trash"></i></div></div></div></div>';
						$arraycount++;
				}
			}
			if(!empty($cfieldData[0]->wc_cf_default_value)){
        		$cfielddefaultvalue = $cfieldData[0]->wc_cf_default_value;
        	}
        	if(!empty($cfieldData[0]->wc_cf_mandatory)){
        		$cfieldmandatory = $cfieldData[0]->wc_cf_mandatory;
        	}
        	if(!empty($cfieldData[0]->wc_cf_mapped)){
        		$cfieldmapped = $cfieldData[0]->wc_cf_mapped;
        		if(!empty($cfieldData[0]->wc_cf_mapped_field_type)){
        			$cfieldMappedWith = 'FormType:'.$cfieldData[0]->wc_cf_mapped_field_type.':'.$cfieldmapped;
        		}
        	}
        	//return response with all fields of custom fields form....
        	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldname'=>$cfieldname,'cfieldtype' => $cfieldtype,'cfieldplaceholder'=>$cfieldplaceholder,'cfielddefaultvalue'=>$cfielddefaultvalue,'cfieldmandatory'=>$cfieldmandatory,'cfieldmapped'=>$cfieldMappedWith,'cfieldoptionsCount'=>$cfieldoptionsCount,'cfieldoptionVal'=>$cfieldoptionVal,'cfieldoptionLab'=>$cfieldoptionLab,'cfieldoptionsHtml'=>$cfieldoptionsHtml));
        }
	}
	die();
}

//Custom fields Tab : wordpress hook is call apply sortable event on custom field groups.....
add_action( 'wp_ajax_wc_update_cfieldgroups_order', 'wc_update_cfieldgroups_order');
//Function Definiation : wc_update_cfieldgroups_order
function wc_update_cfieldgroups_order()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
		//define table names....
		$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        //check custom field group order exist in post data.........
		if(isset($_POST['cfieldgrouplatestorder']) && !empty($_POST['cfieldgrouplatestorder'])){
			for($i = 0; $i < count($_POST['cfieldgrouplatestorder']); $i++) {
			    $cfieldgroupid = $_POST['cfieldgrouplatestorder'][$i];
			    $cfieldgrouplatestorder = $i+1;
			    //check group id and then update the sorting order with latest order come in post data.....
			    if(isset($cfieldgroupid) && !empty($cfieldgroupid)){
					$groupupdateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_sort_order' => $cfieldgrouplatestorder),array('id' => $cfieldgroupid));
			    }
			}
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Custom fields Tab : wordpress hook is call apply sortable event on custom fields.....
add_action( 'wp_ajax_wc_update_groupcfields_order', 'wc_update_groupcfields_order');
//Function Definiation : wc_update_groupcfields_order
function wc_update_groupcfields_order()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
		//define table names....
		$cfield_table_name = 'wooconnection_custom_fields';
    	$cfield_table_name = $table_prefix . "$cfield_table_name";
    	//check custom field group exist in post data.........
		if(isset($_POST['groupcfieldlatestorder']) && !empty($_POST['groupcfieldlatestorder'])){
			for($i = 0; $i < count($_POST['groupcfieldlatestorder']); $i++) {
			    $groupcfieldid = $_POST['groupcfieldlatestorder'][$i];
			    $groupcfieldlatestorder = $i+1;
			    //check custom field id and then update the sorting order with latest order come in post data.....
			    if(isset($groupcfieldid) && !empty($groupcfieldid)){
					$cfieldupdateResult = $wpdb->update($cfield_table_name, array('wc_cf_sort_order' => $groupcfieldlatestorder),array('id' => $groupcfieldid));
			    }
			}
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Custom fields Tab : wordpress hook is call apply sortable event on custom fields.....
add_action( 'wp_ajax_wc_update_standard_cfields_mapping', 'wc_update_standard_cfields_mapping');
//Function Definiation : wc_update_standard_cfields_mapping
function wc_update_standard_cfields_mapping()
{
	//first check post data is not empty
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
		//define table names....
		$standard_cfield_table_name = 'wooconnection_standard_custom_field_mapping';
    	$standard_cfield_table_name = $table_prefix . "$standard_cfield_table_name";
		if(isset($_POST['wc_fields_mapping']) && !empty($_POST['wc_fields_mapping'])){
	      	$mappedFieldName = '';
	      	$mappedFieldType = '';
	      	foreach ($_POST['wc_fields_mapping'] as $key => $value) {
	      		if(!empty($value)){//check id value is not empty...
	      			//check any associated product is selected along with imported product request....
	      			if(isset($_POST['standard_cfield_mapping_'.$value]) && !empty($_POST['standard_cfield_mapping_'.$value])){
	      				$mappedField = $_POST['standard_cfield_mapping_'.$value];
	      				if($mappedField == 'donotmap'){
	      					$mappedFieldName = '';
	      					$mappedFieldType = CUSTOM_FIELD_FORM_TYPE_CONTACT;
	      				}
	      				if (strpos($mappedField, 'FormType:'.CUSTOM_FIELD_FORM_TYPE_CONTACT.':') !== false)
					    {
					       $fieldname=explode("FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.":", $mappedField);
					       $mappedFieldName = $fieldname[1];
					       $mappedFieldType = CUSTOM_FIELD_FORM_TYPE_CONTACT;	
					    }
					    else if (strpos($mappedField, 'FormType:'.CUSTOM_FIELD_FORM_TYPE_ORDER.':') !== false)
					    {
					        $fieldname=explode("FormType:".CUSTOM_FIELD_FORM_TYPE_ORDER.":", $mappedField);
					    	$mappedFieldName = $fieldname[1];
					    	$mappedFieldType = CUSTOM_FIELD_FORM_TYPE_ORDER;	
					    }
	      			}
	      			//update relationship between woocommerce standard field and infusionsoft/keap custom fields...
	      			$standardCfieldUpdateResult = $wpdb->update($standard_cfield_table_name, array('wc_standardcf_mapped' => $mappedFieldName,'wc_standardcf_mapped_field_type'=>$mappedFieldType),array('id' => $value));
	      		}
	      	}
	    }
	    //then call the "createStandardFieldsMappingHtml" function to get the latest html...
		$latestMappedStandardFieldsHtml = createStandardFieldsMappingHtml();
      	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'latestMappedStandardFieldsHtml'=>$latestMappedStandardFieldsHtml));
	}
	die();
}

//Custom fields Tab : this is used to get the list of application custom fields tabs....
add_action( 'wp_ajax_wc_load_app_cfield_tabs', 'wc_load_app_cfield_tabs');
//Function Definiation : wc_load_app_cfield_tabs
function wc_load_app_cfield_tabs()
{
	$cfRelatedTabs = cfRelatedTabs();
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfRelatedTabs'=>$cfRelatedTabs));
	die();
}

//Custom fields Tab : this is used to get the list of application custom fields headers....
add_action( 'wp_ajax_wc_load_app_cfield_headers', 'wc_load_app_cfield_headers');
//Function Definiation : wc_load_app_cfield_headers
function wc_load_app_cfield_headers()
{
	$cfRelatedHeaders = cfRelatedHeaders();
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfRelatedHeaders'=>$cfRelatedHeaders));
	die();
}
?>