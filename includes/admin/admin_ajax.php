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

// //Wordpress hook : This action is triggered when user try to add custom field to infusionsoft....
// add_action( 'wp_ajax_wc_add_custom_field', 'wc_add_custom_field');
// //Function Definiation : wc_add_custom_field
// function wc_add_custom_field(){
// 	if(isset($_POST) && !empty($_POST)){
// 		//first need to check whether the application authentication is done or not..
//         $applicationAuthenticationDetails = getAuthenticationDetails();
//         //get the access token....
//         $access_token = '';
//         if(!empty($applicationAuthenticationDetails)){//check authentication details......
//             if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
//                 $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
//             }
//         }

// 	    if(!empty($access_token)){
// 	    	if(!empty($_POST['cfFormType']) && !empty($_POST['cfname']))
// 			{
// 				$customFieldRes = addCustomField($access_token,$_POST['cfFormType'],$_POST['cfname'],$_POST['cfDataType'],$_POST['cfheader']);
// 				if(is_int($customFieldRes)){
// 					$contactOrderFields = getPredefindCustomfields();
// 					$fieldOptions = "<option value=''></option>";
// 					if(isset($contactOrderFields) && !empty($contactOrderFields)){
// 						foreach($contactOrderFields as $key => $value) {
// 							$fieldOptions .= "<optgroup label=\"$key\">";
// 							foreach($value as $key1 => $value1) {
// 								$optionSelected = "";
// 								$fieldOptions .= '<option value="'.$key1.'"'.$optionSelected.'>'.$value1.'</option>';
// 							}
// 							$fieldOptions .= "</optgroup>";
// 						}
// 					}
// 					echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'fieldOptions'=>$fieldOptions,'cfLatestName'=>trim($_POST['cfname'])));
// 				}
// 			}	
// 	    }else{
// 			echo json_encode(array('status'=>RESPONSE_STATUS_FALSE,'errormessage'=>'Authentication Error'));
// 	    }
		
// 	}
// 	die();
// }


// //Wordpress hook : This action is triggered when user change the custom field form type like contact order.....
// add_action( 'wp_ajax_wc_cf_form_type_tabs', 'wc_cf_form_type_tabs');
// //Function Definiation : wc_cf_form_type_tabs
// function wc_cf_form_type_tabs(){
// 	if(isset($_POST) && !empty($_POST)){
// 		if(isset($_POST['selectedFormType']) && !empty($_POST['selectedFormType'])){
// 		 	$tabsHtml =	cfRelatedTabs($_POST['selectedFormType']);
// 		}
// 		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'tabsHtml'=>$tabsHtml));
// 	}
// 	die();
// }



// //Wordpress hook : This action is triggered when user change the custom field tab.....
// add_action( 'wp_ajax_wc_cf_tab_headers', 'wc_cf_tab_headers');
// //Function Definiation : wc_cf_tab_headers
// function wc_cf_tab_headers(){
// 	if(isset($_POST) && !empty($_POST)){
// 		if(isset($_POST['selectedTabType']) && !empty($_POST['selectedTabType'])){
// 			$headerHtml =	cfRelatedHeaders($_POST['selectedTabType']);
// 		}
// 		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'headerHtml'=>$headerHtml));
// 	}
// 	die();
// }

//Wordpress hook : Funtion is use to add new custom field group as custom field parent.....
add_action( 'wp_ajax_wc_save_cfield_group', 'wc_save_cfield_group');
//Function Definiation : wc_save_cfield_group
function wc_save_cfield_group()
{
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
        $cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        
        $cfield_group_fields_array = array();
		if(isset($_POST['cfieldgroupname']) && !empty($_POST['cfieldgroupname'])){
			$cfield_group_fields_array['wc_custom_field_group_name'] = trim($_POST['cfieldgroupname']);
		}
		
		if(isset($_POST['cfieldgroupid']) && !empty($_POST['cfieldgroupid'])){
			$result_cfield_group = $wpdb->update($cfield_group_table_name,$cfield_group_fields_array,array('id' => $_POST['cfieldgroupid']));
		}else{
			$result_cfield_group = $wpdb->insert($cfield_group_table_name,$cfield_group_fields_array);
			if($result_cfield_group){
			    $cfieldGroupLastInsertId = $wpdb->insert_id;
			    if(!empty($cfieldGroupLastInsertId)){
			   		$cfieldGroupUpdateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_sort_order' => $cfieldGroupLastInsertId),array('id' => $cfieldGroupLastInsertId));
			   	}
			}
		}
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
    }
    die();
}

//Wordpress hook : This action is triggered when user click on  custom fields tab then loads tha custom fields group and its related fields.
add_action( 'wp_ajax_wc_loading_cfields', 'wc_loading_cfields');
//Function Definiation : wc_loading_cfields
function wc_loading_cfields()
{
	$htmldata = get_cfields_groups();
	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'htmldata'=>$htmldata));
	die();
}

//get the custom field groups.....
function get_cfields_groups(){
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_custom_field_groups';
  $wp_table_name = $table_prefix . "$table_name";
  $customFieldGroups = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE wc_custom_field_group_status !=".STATUS_DELETED." ORDER BY wc_custom_field_sort_order ASC");
  $customFieldsListing = "";
  if(isset($customFieldGroups) && !empty($customFieldGroups)){
    foreach ($customFieldGroups as $key => $value) {
        if(!empty($value->id)){
        	$custom_fields_html = get_cfields($value->id);
        	if($value->wc_custom_field_group_status == STATUS_ACTIVE){
        		$showhidehtml = '<i class="fa fa-eye showhidecfieldgroup" title="Hide custom field group with its custom fields" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_HIDE.'" aria-hidden="true"></i>';
        	}else{
        		$showhidehtml = '<i class="fa fa-eye-slash showhidecfieldgroup" title="Show custom field group with its custom fields" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_SHOW.'" aria-hidden="true"></i>';	
        	}
        	
        	$customFieldsListing .=  '<li class="group-list" id="'.$value->id.'"><span class="group-name">'.$value->wc_custom_field_group_name.'<span class="controls"><i class="fa fa-plus addgroupcfield" title="Add custom field to this group" data-id="'.$value->id.'"></i>
        		<i class="fa fa-pencil editcfieldgroup" title="Edit custom field group details" data-id="'.$value->id.'">
        		</i>'.$showhidehtml.'<i class="fa fa-times deletecfieldgroup" title="Delete custom field group" data-id="'.$value->id.'"></i></span></span>'.$custom_fields_html.'</li>';
        }
    }
  }
  return $customFieldsListing;
}

//get the group custom fields by group id.,....
function get_cfields($groupid){
	global $wpdb,$table_prefix;
	$cfields_table_name = 'wooconnection_custom_fields';
 	$cfields_table_name = $table_prefix . "$cfields_table_name";
	$customFieldsHtml = '';
	if(!empty($groupid)){
		$customFields = $wpdb->get_results("SELECT * FROM ".$cfields_table_name." WHERE wc_cf_group_id = ".$groupid." and wc_cf_status!=".STATUS_DELETED." ORDER BY wc_cf_sort_order ASC");
		if(isset($customFields) && !empty($customFields)){
			$customFieldsHtml .= '<ul class="group-fields group_custom_field_'.$groupid.'">';
			foreach ($customFields as $key => $value) {
				if($value->wc_cf_status == STATUS_ACTIVE){
	        		$showhidehtml = '<i class="fa fa-eye showhidecfield" title="Hide custom field" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_HIDE.'" aria-hidden="true"></i>';
	        	}else{
	        		$showhidehtml = '<i class="fa fa-eye-slash showhidecfield" title="Show custom field" data-id="'.$value->id.'" data-target="'.CF_FIELD_ACTION_SHOW.'" aria-hidden="true"></i>';	
	        	}
				$customFieldsHtml .= '<li class="group-field" id="'.$value->id.'">'.$value->wc_cf_name.'<span class="controls"><i class="fa fa-pencil editcfield" title="Edit Current Custom Field" data-id="'.$value->id.'"></i>'.$showhidehtml.'<i class="fa fa-times deletecfield" title="Edit Current Custom Field" data-id="'.$value->id.'"></i></span></li>';
			}
			$customFieldsHtml .= '</ul>';
		}
	}
	return $customFieldsHtml;
}

//Wordpress hook : This action is triggered when user try to delete custom field group.....
add_action( 'wp_ajax_wc_delete_cfield_group', 'wc_delete_cfield_group');
//Function Definiation : wc_delete_cfield_group
function wc_delete_cfield_group()
{
	if(isset($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupId'])){
		global $table_prefix, $wpdb;
       	$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        $updateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_group_status' => STATUS_DELETED),array('id' => $_POST['cfieldgroupId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to edit the custom field group......
add_action( 'wp_ajax_wc_get_cfield_group', 'wc_get_cfield_group');
//Function Definiation : wc_get_cfield_group
function wc_get_cfield_group()
{
	if(isset($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupId']))
	{
		global $table_prefix, $wpdb;
       	$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
        $cfieldgroup = $wpdb->get_results("SELECT * FROM ".$cfield_group_table_name." WHERE id=".$_POST['cfieldgroupId']." and wc_custom_field_group_status =".STATUS_ACTIVE);
        if(isset($cfieldgroup) && !empty($cfieldgroup)){
        	$cfieldgroupname = "";
        	if(!empty($cfieldgroup[0]->wc_custom_field_group_name)){
        		$cfieldgroupname = $cfieldgroup[0]->wc_custom_field_group_name;
        	}
        	echo json_encode(array('status'=>RESPONSE_STATUS_TRUE,'cfieldgroupname'=>$cfieldgroupname));	
        }	
	}
	die();
}


//Wordpress hook : This action is triggered when user try to delete custom field group.....
add_action( 'wp_ajax_wc_update_cfieldgroup_showhide', 'wc_update_cfieldgroup_showhide');
//Function Definiation : wc_update_cfieldgroup_showhide
function wc_update_cfieldgroup_showhide()
{
	if(isset($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupId']) && !empty($_POST['cfieldgroupactiontype'])){
		global $table_prefix, $wpdb;
       	$cfield_group_table_name = 'wooconnection_custom_field_groups';
        $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
		if($_POST['cfieldgroupactiontype'] == CF_FIELD_ACTION_SHOW){
			$status = STATUS_ACTIVE;
		}elseif ($_POST['cfieldgroupactiontype'] == CF_FIELD_ACTION_HIDE) {
			$status = STATUS_INACTIVE;
		}
		$updateResult = $wpdb->update($cfield_group_table_name, array('wc_custom_field_group_status' => $status),array('id' => $_POST['cfieldgroupId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

//Wordpress hook : This action is triggered when user try to add new custom field.
add_action( 'wp_ajax_wc_save_groupcfield', 'wc_save_groupcfield');
//Function Definiation : wc_save_groupcfield
function wc_save_groupcfield()
{
	if(isset($_POST) && !empty($_POST)){
		global $table_prefix, $wpdb;
		$cfields_table_name = 'wooconnection_custom_fields';
    	$cfields_table_name = $table_prefix . "$cfields_table_name";
		
		$cfields_array = array();
		if(isset($_POST['cfieldname']) && !empty($_POST['cfieldname'])){
			$cfields_array['wc_cf_name'] = trim($_POST['cfieldname']);
		}
		if(isset($_POST['cfieldtype']) && !empty($_POST['cfieldtype'])){
			$cfields_array['wc_cf_type'] = $_POST['cfieldtype'];
			if($_POST['cfieldtype'] == CF_FIELD_TYPE_DROPDOWN || $_POST['cfieldtype'] == CF_FIELD_TYPE_RADIO)
			{
	         	$infoCustom = array();
	         	$string_version = '';
	         	if(isset($_POST['cfieldoptionvalue']) && !empty($_POST['cfieldoptionvalue']) && isset($_POST['cfieldoptionlabel']) && !empty($_POST['cfieldoptionlabel']))
				{
				    $contact_custom_fields_value = $_POST['cfieldoptionvalue'];
				    $contact_custom_fields_label = $_POST['cfieldoptionlabel'];
				    if(count($contact_custom_fields_value) > 0)
				    {               
				        for( $i = 1; $i <= count($contact_custom_fields_value); $i++)
				        {
				            $infoCustom[] =  $contact_custom_fields_value[$i].'#'.$contact_custom_fields_label[$i];
				        }
				    }
				}
	            if(isset($infoCustom) && !empty($infoCustom)){
	            	$string_version = implode('@', $infoCustom);
	            	if ($string_version != ''){
	            		$cfields_array['wc_cf_options'] = $string_version;
	            	}
	            }
	            if(isset($_POST['cfielddefault2value']) && !empty($_POST['cfielddefault2value'])){
	            	$cfields_array['wc_cf_default_value'] = trim($_POST['cfielddefault2value']);
	            }
	     		
	     	}else if ($_POST['customfieldtype'] == CF_FIELD_TYPE_CHECKBOX) {
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
			
			$mappedInputData = trim($_POST['cfieldmapping']);
			if(strpos($mappedInputData, "FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.":") !== false) {
				$arrayData = explode("FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.":", $mappedInputData);
				$mappedData = $arrayData[1];
				$cfields_array['wc_cf_mapped_field_type'] = CUSTOM_FIELD_FORM_TYPE_CONTACT;
			}elseif (strpos($mappedInputData, "FormType:".CUSTOM_FIELD_FORM_TYPE_ORDER.":") !== false) {
				$arrayData = explode("FormType:".CUSTOM_FIELD_FORM_TYPE_ORDER.":", $mappedInputData);
				$mappedData = $arrayData[1];
				$cfields_array['wc_cf_mapped_field_type'] = CUSTOM_FIELD_FORM_TYPE_ORDER;
			}
			$cfields_array['wc_cf_mapped'] = $mappedData;
		}
		
		if(isset($_POST['cfieldid']) && !empty($_POST['cfieldid'])){
			$result_check_update = $wpdb->update($cfields_table_name,$cfields_array,array('id' => $_POST['cfieldid']));
			echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
		}else{
			if(isset($_POST['cfieldparentgroupid']) && !empty($_POST['cfieldparentgroupid'])){
				if(isset($_POST['cfieldparentgroupid']) && !empty($_POST['cfieldparentgroupid'])){
					$cfields_array['wc_cf_group_id'] = $_POST['cfieldparentgroupid'];
				}
				$result_check_custom_field = $wpdb->insert($cfields_table_name,$cfields_array);
				if($result_check_custom_field){
				   	$lastInsertId = $wpdb->insert_id;
				   	if(!empty($lastInsertId)){
				   		$updateResult = $wpdb->update($cfields_table_name, array('wc_cf_sort_order' => $lastInsertId),array('id' => $lastInsertId));
				   	}
				}
				echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
			}
		}
		
	}
	die();
}

//Wordpress hook : This action is triggered when user try to delete custom field group.....
add_action( 'wp_ajax_wc_update_cfield_showhide', 'wc_update_cfield_showhide');
//Function Definiation : wc_update_cfield_showhide
function wc_update_cfield_showhide()
{
	if(isset($_POST['cfieldId']) && !empty($_POST['cfieldId']) && !empty($_POST['cfieldactiontype'])){
		global $table_prefix, $wpdb;
       	$cfield_table_name = 'wooconnection_custom_fields';
        $cfield_table_name = $table_prefix . "$cfield_table_name";
		if($_POST['cfieldactiontype'] == CF_FIELD_ACTION_SHOW){
			$status = STATUS_ACTIVE;
		}elseif ($_POST['cfieldactiontype'] == CF_FIELD_ACTION_HIDE) {
			$status = STATUS_INACTIVE;
		}
		$updateResult = $wpdb->update($cfield_table_name, array('wc_cf_status' => $status),array('id' => $_POST['cfieldId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}


//Wordpress hook : This action is triggered when user try to delete custom field .....
add_action( 'wp_ajax_wc_delete_cfield', 'wc_delete_cfield');
//Function Definiation : wc_delete_cfield
function wc_delete_cfield()
{
	if(isset($_POST['cfieldId']) && !empty($_POST['cfieldId'])){
		global $table_prefix, $wpdb;
		$cfield_table_name = 'wooconnection_custom_fields';
    	$cfield_table_name = $table_prefix . "$cfield_table_name";
		$updateResult = $wpdb->update($cfield_table_name, array('wc_cf_status' => STATUS_DELETED),array('id' => $_POST['cfieldId']));
		echo json_encode(array('status'=>RESPONSE_STATUS_TRUE));
	}
	die();
}

?>