<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
//Function is used to check whether the plugin is activated or not if not activated then return "leftMenusDisable" class....
function checkPluginActivatedNot(){
  $leftMenusDisable = "";
  //Get plugin details..
  $plugin_settings = get_option('wc_plugin_details');
  if(isset($plugin_settings) && !empty($plugin_settings)){
    if(!empty($plugin_settings['plugin_activation_status']) && $plugin_settings['plugin_activation_status'] != PLUGIN_ACTIVATED){
       $leftMenusDisable = 'leftMenusDisable';
    }
  }else{
    $leftMenusDisable = 'leftMenusDisable';
  }
  return $leftMenusDisable; 
}

//Function is used to return the html when connection is not created with infusionsoft/keap application......
function applicationAuthenticationStatus(){
  $applicationAuthenticationStatus = getAuthenticationDetails();
  $notConnectedHtml = '';
  if(empty($applicationAuthenticationStatus)){
    $notConnectedHtml ='<center><img src="'.WOOCONNECTION_PLUGIN_URL.'assets/images/connection_fail.jpg" style="width: 100%;"/><br><br><p class="heading-text">To access this feature, First you need to authorize infusionsoft/keap application by click on authorize button in infusionsoft settings section of Getting started.</p>';  
  }
  return $notConnectedHtml;
}


//Function is used to check whether the one product is imported or exported....
function checkImportExportStatus(){
  global $wpdb;
  $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='is_kp_product_id'");
  if (isset($meta) && !empty($meta) && isset($meta[0])) {
    $importExportProductId = $meta[0]->meta_value;
    if(isset($importExportProductId)){
      return true;
    }else{
        return false;
    }
  }else{
    return false;
  }   
}

//Function is used to check plugin is activated or not...
function checkPluginActivationStatus(){
  //Get plugin details..
  $plugin_settings = get_option('wc_plugin_details');
  if(isset($plugin_settings) && !empty($plugin_settings)){
    if(!empty($plugin_settings['plugin_activation_status']) && $plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED){
       return true;
    }else{
      return false;
    }
  }else{
    return false;
  }
  return $leftMenusDisable; 
}

//get the list of product purchase triggers...
function getGeneralTriggers(){
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_campaign_goals';
  $wp_table_name = $table_prefix . "$table_name";
  $trigger_type = WOOCONNECTION_TRIGGER_TYPE_GENERAL;
  $campaignGoalDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE wc_trigger_type=".$trigger_type);
  $wcGeneralTriggers = '';
  if(isset($campaignGoalDetails) && !empty($campaignGoalDetails)){
    foreach ($campaignGoalDetails as $key => $value) {
        $trigger_id = $value->id;
        $trigger_goal_name = $value->wc_goal_name;
        $trigger_integration_name = $value->wc_integration_name;
        $trigger_call_name = $value->wc_call_name;
        $wcGeneralTriggers.='<tr id="trigger_tr_'.$trigger_id.'">
                                <td>'.$trigger_goal_name.'</td>
                                <td id="trigger_integration_name_'.$trigger_id.'">'.strtolower($trigger_integration_name).'</td>
                                <td id="trigger_call_name_'.$trigger_id.'">'.strtolower($trigger_call_name).'</td>
                                <td><i class="fa fa-edit" aria-hidden="true" style="cursor:pointer;" onclick="popupEditDetails('.$trigger_id.');"></i>
                                </td>
                              </tr>';
    }
  }else{
    $wcGeneralTriggers = '<tr><td colspan="4" style="text-align: center; vertical-align: middle;">No General Triggers Exist</td></tr>';
  }
  return $wcGeneralTriggers;
}

//function is used to get the authentication details....
function getAuthenticationDetails(){
  $authenticationData = array();
  $pluginDetails = getPluginDetails();
  $email = '';
  $key = '';
  $website = get_site_url();
  if(isset($pluginDetails) && !empty($pluginDetails)){
      if(!empty($pluginDetails['activation_email'])){
        $email = $pluginDetails['activation_email'];
      }
      if(!empty($pluginDetails['activation_key'])){
        $key = $pluginDetails['activation_key'];
      }
  } 
  $url = ADMIN_REMOTE_URL.'authenticationManage.php';
  if(!empty($email) && !empty($key)){
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,
                "postemail=".$email."&postkey=".$key."&posturl=".$website);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      if($err){
      }else{
       $sucessData = json_decode($response);
       if(!empty($sucessData->authenticationDetails)){
         $authenticationData = $sucessData->authenticationDetails;
       }
      }
      return $authenticationData;
      curl_close($ch);  
  }else{
      return $authenticationData;
  }
  
}

//Main function is used to generate the export products html.....
function createExportProductsHtml(){
  //Define export table html variable.....
  $table_export_products_html = "";
  //call the common function to get the list of woocommerce publish products...
  $woocommerceProducts = listExistingDatabaseWooProducts();
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
  //Get the list of active products from authenticate application....
  $applicationProductsArray =  getApplicationProducts();
  
  //set html if no products exist in woocommerce for export....
  if(empty($woocommerceProducts)){
      $table_export_products_html = '<p class="heading-text" style="text-align:center">No products exist in woocommerce for export to '.$applicationLabel.' application.</p>';
  }else{
      //Compare woocommerce publish products with application products
      $exportProductsData = exportProductsListingApplication($woocommerceProducts,$applicationProductsArray,$applicationLabel);
      if(isset($exportProductsData) && !empty($exportProductsData)){
          //Get the export products table html and append to table
          if(!empty($exportProductsData['exportTableHtml'])){
            $table_export_products_html .= '<form action="" method="post" id="wc_export_products_form" onsubmit="return false">  
              <table class="table table-striped export_products_listing_class" id="export_products_listing">
                '.$exportProductsData['exportTableHtml'].'
              </table>
              <div class="form-group col-md-12 text-center m-t-60">
                <div class="exportProducts" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Process Export Products....</div>
                <div class="alert-error-message export-products-error" style="display: none;"></div>
                <div class="alert-sucess-message export-products-success" style="display: none;">Products export successfully.</div>
                <input type="button" value="Export Products" class="btn btn-primary btn-radius btn-theme export_products_btn" onclick="wcProductsExport()">
              </div>
            </form>';
          }
      }
  }
  //return the html...
  return $table_export_products_html;
}

//list of existing woocommerce products from database and then return...
function listExistingDatabaseWooProducts(){
    $productsListing = array();
    $existProductsDetails = get_posts(array('post_type' => 'product','post_status'=>'publish','orderby' => 'post_date','order' => 'DESC','posts_per_page'   => 999999));
    if(!empty($existProductsDetails)){
      $productsListing = $existProductsDetails;
    }
    return $existProductsDetails;
}

//create products listing if infusionsoft/keap products are exist...
function exportProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationType){
    $exportTableHtml  = '';//Define variable..
    $exportProductsData = array();//Define array...
    //first need to check connection is created or not infusionsoft/keap application then next process need to done..
    $applicationAuthenticationDetails = getAuthenticationDetails();
    //get the access token....
    $access_token = '';
    if(!empty($applicationAuthenticationDetails)){
      if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
          $access_token = $applicationAuthenticationDetails[0]->user_access_token;
      }
    }

    //by default subscription products is hide....
    $allowSubscription = false;
    //get the custom payment gateway settings.......
    $settingOptions = get_option('woocommerce_infusionsoft_keap_settings');
    //check settings is exist or not........
    if(isset($settingOptions) && !empty($settingOptions)){
      //then check custom gateway is enabled for payments......
      if($settingOptions['enabled'] == 'yes'){
        //then check subscriptions are enable or not if enable then call the class to give feature of trial subscription coupons......
        if(isset($settingOptions['wc_subscriptions']) && !empty($settingOptions['wc_subscriptions']) && $settingOptions['wc_subscriptions'] == 'yes' && !empty($applicationType) && $applicationType == APPLICATION_TYPE_INFUSIONSOFT_LABEL){
            $allowSubscription = true;
        }
      }
    }

    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        //Create first table....
        $exportTableHtml .= '<thead>';
        $exportTableHtml .= '<tr><th style="text-align: center;"><input type="checkbox" id="export_products_all" name="export_products_all" class="all_products_checkbox_export" value="allproductsexport"></th><th>WooCommerce Product Name</th><th>WooCommerce Product SKU</th><th>WooCommerce Product Price</th><th>'.$applicationType.' Product</th></tr>';
        $exportTableHtml .= '</thead>';
        $exportTableHtml .= '<tbody>';
        $productSelectHtml = '';
        foreach ($wooCommerceProducts as $key => $value) {
            if(!empty($value->ID)){
                $matchProductId = '';
                $wc_product_id = $value->ID;//Define product id...             
                $wcproduct = wc_get_product($value->ID);//Get product details..
                $wcproductPrice = $wcproduct->get_regular_price();//Get product price....
                $currencySign = get_woocommerce_currency_symbol();//Get currency symbol....
                //check product price and set....
                if(!empty($wcproductPrice)){
                    $wcproductPrice = $wcproductPrice;
                }else{
                    $wcproductPrice = 0;
                }
                //Create final price to display...
                $wcproductPrice = $currencySign.number_format($wcproductPrice,2);
                $wcproductSku = $wcproduct->get_sku();//get product sku....
                $wcproductName = $wcproduct->get_name();//get product name....
                $productsDropDown = '';
                //check and set the product name....
                if(!empty($wcproductName)){
                  $wcproductName = $wcproductName;
                }else{
                  $wcproductName = "--";
                }
                $wcProductType = $wcproduct->get_type();
                if(stripos($wcProductType, 'subscription') !== false){
                  $typeProduct = ITEM_TYPE_SUBSCRIPTION;
                }else{
                  $typeProduct = ITEM_TYPE_PRODUCT;
                }
                //first check if application products is not empty. If empty then skip match products process and show the html in place of select...
                if(!empty($applicationProductsArray['products'])){
                    //Check product relation is exist....
                    $productExistId = get_post_meta($wc_product_id, 'is_kp_product_id', true);
                    //If product relation exist then create select deopdown and set associative product selected....
                    if(isset($productExistId)){
                      $matchProductId = $productExistId;
                    }else if (!empty($wcproductSku)) {//Then check product sku,If product sku exist then check product in application with same sku is exist ot not....
                      $checkSkuMatchWithIskpProducts = checkProductMapping($wcproductSku,$applicationProductsArray); 
                      //if product/multiple products with same sku is exist then get the last matched product id.... 
                      if(isset($checkSkuMatchWithIskpProducts) && !empty($checkSkuMatchWithIskpProducts)){
                          $matchId =  end($checkSkuMatchWithIskpProducts);
                          //On the basis of match product id set the product selected and create html.....
                          if(!empty($matchId)){
                            $matchProductId = $matchId;
                          }
                      }
                    }
                    //check matchproduct id is exist or not if exist then get the product name from application products array......
                    if(!empty($matchProductId)){
                      $key = array_search($matchProductId, array_column($applicationProductsArray['products'], 'id'));
                      if (!empty($key) || $key === 0) {
                        $productDetails = $applicationProductsArray['products'][$key];
                        if(!empty($productDetails['product_name'])){
                          $productsDropDown = '<input type="hidden" value="'.$matchProductId.'" name="wc_product_export_with_'.$wc_product_id.'">'.$productDetails['product_name'];
                        }
                      }else{
                        $productsDropDown = 'Mapped Product Not Exist In App!';
                      }
                    }else{
                      $productsDropDown = 'No mapping exist!'; 
                    }
                    //Create final select html.....
                    $productSelectHtml = $productsDropDown;
                }else{
                  //Set the html of select if no products exist in application....
                  $productSelectHtml = 'No '.$applicationType.' Products Exist!';
                }
                //Check and set the product sku to display.....
                if(!empty($wcproductSku)){
                    $wcproductSku = $wcproductSku;
                }else{
                    $wcproductSku = '--';
                }
                if($typeProduct == ITEM_TYPE_PRODUCT){
                    //Create final html.......
                    $exportTableHtml .= '<tr><td style="text-align: center;"><input type="checkbox" class="each_product_checkbox_export" name="wc_products[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td><td>'.$wcproductName.'</td><td class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';
                }
                else if($typeProduct == ITEM_TYPE_SUBSCRIPTION && $allowSubscription == true){
                    //Create final html.......
                    $exportTableHtml .= '<tr><td style="text-align: center;"><input type="checkbox" class="each_product_checkbox_export" name="wc_products[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td><td>'.$wcproductName.'</td><td class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';
                }
            }

        }
        $exportProductsData['exportTableHtml'] = $exportTableHtml;//Assign html....
    }
    return $exportProductsData;//Return data....
}

//Check product with same sku is exist or not , if exist then return match products id.....
function checkProductMapping($sku,$productsArray){
    $matchProductsIds = array();//Define array...
    if(!empty($productsArray['products'])){//check is products array is not empty....
        //Execute loop on application prdoucts array,......
        foreach ($productsArray['products'] as $key => $value) {
          if(!empty($value['id'])){//check product id....
              //compare sku, if match the return the ids..
              if(isset($value['sku']) && !empty($value['sku'])){
                  if($value['sku'] == $sku){
                    $matchProductsIds[] = $value['id'];
                  }    
              }
          }
        }   
    }
    return $matchProductsIds;//Return array....
}

//Function is used to check whether the plugin is activated or not if not activated then return "leftMenusDisable" class....
function getPluginDetails(){
  $pluginDetails = array();
  $pluginDetails['activation_email'] = '';
  $pluginDetails['activation_key'] = '';
  $pluginDetails['plugin_activation_status'] = '';
  //Get plugin details..
  $plugin_settings = get_option('wc_plugin_details');
  if(isset($plugin_settings) && !empty($plugin_settings)){
    if($plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED){
        $pluginDetails['activation_email'] = $plugin_settings['wc_license_email'];
        $pluginDetails['activation_key'] = $plugin_settings['wc_license_key'];
        $pluginDetails['plugin_activation_status'] = PLUGIN_ACTIVATED;
    }
  }
  return $pluginDetails; 
}

//Get the application type.....
function applicationType(){
  $data = getAuthenticationDetails();
  $applicationType = '';
  if(isset($data) && !empty($data)){
    $applicationType =  $data[0]->user_application_edition;
  }
  return $applicationType;  
}

//Get the application label.....
function applicationLabel($type=''){
  $label =  APPLICATION_TYPE_INFUSIONSOFT_LABEL;
  if($type != ""){
      $label =  $type;
  } 
  return $label;
}

//Get the application name......
function applicationName(){
  $data = getAuthenticationDetails();
  $applicationName = '';
  if(isset($data) && !empty($data)){
    $applicationName =  $data[0]->user_authorize_application;
  }
  return $applicationName;  
}

//Get the list of application products....
function getApplicationProducts(){
    //first need to check connection is created or not infusionsoft/keap application then next process need to done..
    $applicationAuthenticationDetails = getAuthenticationDetails();
    //get the access token....
    $access_token = '';
    if(!empty($applicationAuthenticationDetails)){
      if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
          $access_token = $applicationAuthenticationDetails[0]->user_access_token;
      }
    }
    $productsListing = array();
    $url = "https://api.infusionsoft.com/crm/rest/v1/products";
    $postparam = array( 
      'active'   => true 
    );
    $params = http_build_query($postparam);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url."?".$params); //using the setopt function to send request to the url
    $header = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer '. $access_token
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //response returned but stored not displayed in browser
    $response = curl_exec($ch); //executing request
    $err = curl_error($ch);
    $matchIdsArray = array();
    if($err){
      // echo $err;
    }else{
      $sucessData = json_decode($response,true);
      return $sucessData;
    }
    curl_close($ch);
    return $productsListing;
}

//Function is used to update the infusionsoft/keap application existing products at the time of export...
function updateExistingProduct($alreadyExistProductId,$access_token,$productDetailsArray,$logtype,$wooconnectionLogger){
  $productId = '';
  if(!empty($alreadyExistProductId) && !empty($access_token) && !empty($productDetailsArray)){
        $url = 'https://api.infusionsoft.com/crm/rest/v1/products/'.$alreadyExistProductId;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $productDetailsArray);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
            $errorMessage = $logtype.' : '.'Update product('.$alreadyExistProductId.') in application is failed due to '. $err; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = $logtype.' : '.'Update product('.$alreadyExistProductId.') in application is failed '; 
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
          if(!empty($sucessData['id'])){
            $productId = $sucessData['id'];
          }
          return $productId;
        }
        curl_close($ch);
  }
  return $productId;
}

//Main function is used to generate the match products html.....
function createMatchProductsHtml(){
  global $wpdb;
  //Define match table html variable.....
  $table_match_products_html = "";
  //Define array to manage the sorting.....
  $wcproductsArray = array();
  //call the common function to get the list of woocommerce products they are in relation with application products.......
  $wooCommerceProducts = listExistingDatabaseWooProducts();
  
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
  //Get the list of active products from authenticate application....
  $applicationProductsArray = getApplicationProducts();
  
  //set html if no products exist in woocommerce they are in relation with applcation products....
  if(empty($wooCommerceProducts)){
    $table_match_products_html = '<p class="heading-text" style="text-align:center">No products mapping exist.</p>';
  }else{
      //Compare woocommerce publish products application products....
      $matchProductsData = createMatchProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationLabel);
      //Check export products data....
      if(isset($matchProductsData) && !empty($matchProductsData)){
          //Get the match products table html and append to table
          if(!empty($matchProductsData['matchTableHtml'])){
            $table_match_products_html .= '<span class="ajax_loader_match_products_related" style="display:none"><img src="'.WOOCONNECTION_PLUGIN_URL.'assets/images/loader.gif"></span><form action="" method="post" id="wc_match_products_form" onsubmit="return false">  
              <table class="table table-striped match_products_listing_class" id="match_products_listing">
                '.$matchProductsData['matchTableHtml'].'
              </table></form>';
          }
      }
  }
  //return the html...
  return $table_match_products_html;
}

//Create the match products table listing....
function createMatchProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationType){
    //by default subscription products is hide....
    $allowSubscription = false;
    //get the custom payment gateway settings.......
    $settingOptions = get_option('woocommerce_infusionsoft_keap_settings');
    //check settings is exist or not........
    if(isset($settingOptions) && !empty($settingOptions)){
      //then check custom gateway is enabled for payments......
      if($settingOptions['enabled'] == 'yes'){
        //then check subscriptions are enable or not if enable then call the class to give feature of trial subscription coupons......
        if(isset($settingOptions['wc_subscriptions']) && !empty($settingOptions['wc_subscriptions']) && $settingOptions['wc_subscriptions'] == 'yes' && !empty($applicationType) && $applicationType == APPLICATION_TYPE_INFUSIONSOFT_LABEL){
            $allowSubscription = true;
        }
      }
    }

    $matchTableHtml  = '';//Define variable..
    $matchProductsData = array();//Define array...
    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        //Create first table....
        $matchTableHtml .= '<thead>';
        $matchTableHtml .= '<tr>
                        <th></th>
                        <th>WooCommerce Product Name</th>
                        <th>WooCommerce Product SKU</th>
                        <th>WooCommerce Product Price</th>
                        <th>'.$applicationType.' Product</th>
                      </tr>';
        $matchTableHtml .= '</thead>';
        $matchTableHtml .= '<tbody>';
        $productExistId = '';
        foreach ($wooCommerceProducts as $key => $value) {
            $typeproduct = ITEM_TYPE_PRODUCT;
            if(!empty($value)){
                $wc_product_id = $value->ID;//Define product id...                  
                $wcproduct = wc_get_product($wc_product_id);//Get product details..
                $wcproductPrice = $wcproduct->get_regular_price();//Get product price....
                $currencySign = get_woocommerce_currency_symbol();//Get currency symbol....
                //check product price and set....
                if(!empty($wcproductPrice)){
                    $wcproductPrice = $wcproductPrice;
                }else{
                    $wcproductPrice = 0;
                }
                //Create final price to display...
                $wcproductPrice = $currencySign.number_format($wcproductPrice,2);
                $wcproductSku = $wcproduct->get_sku();//get product sku....
                $wcproductName = $wcproduct->get_name();//get product name....
                $productsDropDown = '';
                //check and set the product name....
                if(!empty($wcproductName)){
                  $wcproductName = $wcproductName;
                }else{
                  $wcproductName = "--";
                }
                //get the product type i.e simple product or simple subscription......
                $wcproductType = $wcproduct->get_type();
                //check it substring "subscription" not exist in product type it means it is a simple product..
                if (strpos($wcproductType, 'subscription') !== false) {
                    $typeproduct = ITEM_TYPE_SUBSCRIPTION;
                }
                //else it is a subscription plan.....
                else{
                    $typeproduct = ITEM_TYPE_PRODUCT;
                }
                //first check if application products is not empty. If empty then skip match products process and show the html in place of select...
                if(!empty($applicationProductsArray['products'])){
                    //Check product relation is exist....
                    $productExistId = get_post_meta($wc_product_id, 'is_kp_product_id', true);
                    //If product relation exist then create select deopdown and set associative product selected....
                    if(isset($productExistId) && !empty($productExistId)){
                      $productsDropDown = createMatchProductsSelect($applicationProductsArray,$productExistId,$typeproduct);
                    }else{
                      $productsDropDown = createMatchProductsSelect($applicationProductsArray,'',$typeproduct);
                    }
                    //Create final select html.....
                    $productSelectHtml = '<select class="application_match_products_dropdown" name="wc_product_match_with_'.$wc_product_id.'" data-id="'.$wc_product_id.'"><option value="0">Select '.$applicationType.' product</option>'.$productsDropDown.'</select>';
                }else{
                  //Set the html of select if no products exist in application....
                  $productSelectHtml = 'No '.$applicationType.' Products Exist!';
                }
                //Check and set the product sku to display.....
                if(!empty($wcproductSku)){
                  $wcproductSku = $wcproductSku;
                }else{
                  $wcproductSku = "--";
                }
                $actionHtml = '';
                if($wcproduct->is_type('variable')){
                    $actionHtml  = '<button type="button" title="Expand variations of this product." class="btn btn-success exploder" id="'.$wc_product_id.'" data-id="'.$productExistId.'"><i class="fa fa-plus"></i></button>';
                }

                if($typeproduct == ITEM_TYPE_PRODUCT){
                    //Create final html.......
                    $matchTableHtml .= '<tr id="table_row_'.$wc_product_id.'"><td>'.$actionHtml.'</td><td>'.$wcproductName.'</td><td  class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';  
                }
                else if($typeproduct == ITEM_TYPE_SUBSCRIPTION && $allowSubscription == true){
                    //Create final html.......
                    $matchTableHtml .= '<tr id="table_row_'.$wc_product_id.'"><td>'.$actionHtml.'</td><td>'.$wcproductName.'</td><td  class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';
                }
            }
        }
        $matchProductsData['matchTableHtml'] = $matchTableHtml;//Assign html....
    }
    return $matchProductsData;//Return data....
}

//create the infusionsoft products dropdown for mapping..........
function createMatchProductsSelect($existingiskpProductResult,$wc_product_id_compare='',$typeProduct){
    $iskp_products_options_html = '';//Define variable...
    if(isset($existingiskpProductResult['products']) && !empty($existingiskpProductResult['products'])){//check application products...
        foreach($existingiskpProductResult['products'] as $iskpProductDetails) {
          $iskpProductId = $iskpProductDetails['id'];//get or set the product id....
          $iskpProductName = $iskpProductDetails['product_name'];//get or set the product name....
          $iskpProductSelected = "";
          if(!empty($wc_product_id_compare)){//if relation exist...
              if($wc_product_id_compare == $iskpProductId){//then compare the relation between products....
                  $iskpProductSelected = "selected";//set product selected....
              }else{
                  $iskpProductSelected = "";
              }
          }
          //first check it item/product type is a subscription or subscription plans exist with products then only those products are available in dropown to update mapping.......
          if($typeProduct == ITEM_TYPE_SUBSCRIPTION && !empty($iskpProductDetails['subscription_plans'])){
              //create the final html.....
              $iskp_products_options_html.= '<option value="'.$iskpProductId.'" '.$iskpProductSelected.' data-id="'.$iskpProductId.'">'.$iskpProductName.'</option>';  
          }
          //then check if product type is product then show only those products which is not related to subscription plans.....
          else if ($typeProduct == ITEM_TYPE_PRODUCT && empty($iskpProductDetails['subscription_plans'])) {
              //create the final html.....
              $iskp_products_options_html.= '<option value="'.$iskpProductId.'" '.$iskpProductSelected.' data-id="'.$iskpProductId.'">'.$iskpProductName.'</option>';
          }
          
        }
    }
    return $iskp_products_options_html;//return html...
}

//get the campaign goal details on the basis of trigger type and campaign goal name....
function get_campaign_goal_details($trigger_type,$campaign_goal_name){
  $campginGoalDetails = array();
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_campaign_goals';
  $wp_table_name = $table_prefix . "$table_name";
  if(!empty($trigger_type) && !empty($campaign_goal_name)){
    $campginGoalDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE wc_goal_name = '".$campaign_goal_name."' and  wc_trigger_type=".$trigger_type);
  }
  return $campginGoalDetails;
}


//validate email whether is in valid format or not.
function validate_email($email='',$log_message,$wooconnectionLogger){
  if(isset($useremail) && !empty($useremail)){
      if (!filter_var($useremail, FILTER_VALIDATE_EMAIL)) {
        //Save logs and stop the process if email not is a valid email address also concate a error message...
        $log_message = $log_message.' is failed because '.$email.' is not a valid email address';
        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($log_message, true));
        return false;  
      }else{
        return true;
      }  
  }else{
      return false;
  }
}

//get or add the contact to infusionsoft/keap application..
function checkAddContactApp($access_token,$appUseremail,$callback_purpose){
    //check if appUseremail is exist then get the current user id from infusionsoft/keap application on the basis of appUseremail 
    $appContactId = "";
    if(isset($appUseremail) && !empty($appUseremail)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        //create json array to push ocde in infusionsoft...
        $jsonData ='{"duplicate_option": "Email","email_addresses":[{"email": "'.$appUseremail.'","field": "EMAIL1"}],"opt_in_reason": "Customer opted-in through '.SITE_URL.'"}';
        $url = 'https://api.infusionsoft.com/crm/rest/v1/contacts';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
          $errorMessage = 'Trying to add contact('.$appUseremail.') for ';
          $errorMessage .= $callback_purpose ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = 'Trying to add contact('.$appUseremail.') for ' .$callback_purpose ." is failed ";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
          if(!empty($sucessData['id'])){
            $appContactId = $sucessData['id'];
          }
          return $appContactId;
        }
        curl_close($ch);
    }
    return $appContactId;
}


//add contact to trigger....
function achieveTriggerGoal($access_token,$trigger_integration_name,$trigger_call_name,$contact_id,$callback_purpose){
    $sucessData = array();
    // Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    if(!empty($access_token) && !empty($trigger_integration_name) && !empty($trigger_call_name) && !empty($contact_id)){
      $url = 'https://api.infusionsoft.com/crm/rest/v1/campaigns/goals/'.$trigger_integration_name.'/'.$trigger_call_name;
      //create json array to push ocde in infusionsoft...
      $jsonData ='{"contact_id":'.$contact_id.'}';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $header = array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '. $access_token
      );
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      if($err){
          $errorMessage = $callback_purpose ." is failed where contact id is ".$contact_id." due to ". $err;
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
      }else{
        $sucessData = json_decode($response,true);
        if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
          $errorMessage = $callback_purpose ." is failed where contact id is ".$contact_id;
          if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
            $errorMessage .= " due to ".$sucessData['fault']['faultstring']; 
          }
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          return false;
        }
        return $sucessData;
      }
      curl_close($ch);
    }
    return $sucessData;  
}

//Add logs if access token not exist or authentication is done...
function addLogsAuthentication($connection_called_position = ''){
    $wooconnectionLogger = new WC_Logger();
    if(isset($connection_called_position) && !empty($connection_called_position)){
      $errorMessage = $connection_called_position ." is failed because authentication with infusionsoft/keap application is not done"; 
    }
    $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
}

//check compnay is exist or not if not then add new company..
function checkAddCompany($companyName,$access_token){
    //check if companyName is exist then get the current company id from infusionsoft/keap application on the basis of companyName 
    $companyId = "";
    if(isset($companyName) && !empty($companyName)){
        $url = "https://api.infusionsoft.com/crm/rest/v1/companies";
        $postparam = array( 
          'company_name'   => $companyName 
        );
        $params = http_build_query($postparam);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url."?".$params); //using the setopt function to send request to the url
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //response returned but stored not displayed in browser
        $response = curl_exec($ch); //executing request
        $err = curl_error($ch);
        if($err){
        }else{
          $sucessData = json_decode($response,true);
          if(!empty($sucessData['companies'][0])){
              if(!empty($sucessData['companies'][0]['id'])){
                  $companyId = $sucessData['companies'][0]['id'];
              } 
          }
          if(!empty($companyId)){
              return $companyId;
          }else{
            $companyId = addNewCompany($companyName,$access_token);
            return $companyId;
          } 
        }
        curl_close($ch);
    }
    return $companyId;
}



//check compnay is exist or not if not then add new company..
function addNewCompany($newCompanyName,$access_token){
    //check if companyName is exist then get the current company id from infusionsoft/keap application on the basis of companyName 
    $newCompanyId = "";
    if(isset($newCompanyName) && !empty($newCompanyName)){
        $url = "https://api.infusionsoft.com/crm/rest/v1/companies";
        $jsonArray = '{"company_name": "'.$newCompanyName.'"}';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
        }else{
          $sucessData = json_decode($response,true);
          if(!empty($sucessData)){
              if(!empty($sucessData['id'])){
                $newCompanyId = $sucessData['id'];
                return $newCompanyId;
              } 
          }
        }
        curl_close($ch);
    }
    return $companyId;
}



//update contact to infusionsoft/keap account..
function updateContact($contactId,$woocommerce_order_data,$access_token){
    $contactLatestInformation = array();
    if(!empty($contactId) && !empty($woocommerce_order_data) && !empty($access_token)){
        if(isset($woocommerce_order_data['billing']['country']) && !empty($woocommerce_order_data['billing']['country'])){
            $countryCode = get_country_code($woocommerce_order_data['billing']['country']);
            $contactLatestInformation['country_code'] = $countryCode;
        }
        $contactLatestInformation['field'] = "BILLING";
        if(isset($woocommerce_order_data['billing']['address_1']) && !empty($woocommerce_order_data['billing']['address_1'])){
            $contactLatestInformation['line1'] = trim($woocommerce_order_data['billing']['address_1']);
        }
        if(isset($woocommerce_order_data['billing']['address_2']) && !empty($woocommerce_order_data['billing']['address_2']))
        {
            $contactLatestInformation['line2'] = $woocommerce_order_data['billing']['address_2'];
        }
        if(isset($woocommerce_order_data['billing']['postcode']) && !empty($woocommerce_order_data['billing']['postcode'])){
            $contactLatestInformation['postal_code'] = $woocommerce_order_data['billing']['postcode'];
        }
        if(isset($woocommerce_order_data['billing']['city']) && !empty($woocommerce_order_data['billing']['city'])){
            $contactLatestInformation['locality'] = $woocommerce_order_data['billing']['city'];
        }
        if(isset($woocommerce_order_data['billing']['state']) && !empty($woocommerce_order_data['billing']['state'])){
            $states = WC()->countries->get_states($woocommerce_order_data['billing']['country']);
            $state = !empty($states[$woocommerce_order_data['billing']['state']]) ? $states[$woocommerce_order_data['billing']['state']] : '';
            $contactLatestInformation['region'] = $state;
        }
        $companyId = '';
        if(isset($woocommerce_order_data['billing']['company']) && !empty($woocommerce_order_data['billing']['company'])){
            $company = stripslashes($woocommerce_order_data['billing']['company']);
            $companyId = checkAddCompany($company,$access_token);
        }
        $firstName = '';
        if(isset($woocommerce_order_data['billing']['first_name']) && !empty($woocommerce_order_data['billing']['first_name'])){
            $firstName = trim($woocommerce_order_data['billing']['first_name']);
        }
        $phone1 = '';
        if(isset($woocommerce_order_data['billing']['phone']) && !empty($woocommerce_order_data['billing']['phone'])){
            $phone1 = $woocommerce_order_data['billing']['phone'];
        }
        $jsonAddressedArray = json_encode($contactLatestInformation);
        $jsonArray = '{"addresses": ['.$jsonAddressedArray.'],"company": {"id": '.$companyId.'},"phone_numbers": 
          [{"field": "PHONE1","number": "'.$phone1.'"}],"given_name": "'.$firstName.'"}';
        $url = 'https://api.infusionsoft.com/crm/rest/v1/contacts/'.$contactId;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
          $errorMessage = 'Trying to update contact('.$contactId.')';
          $errorMessage .= $errorMessage ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = 'Trying to update contact('.$contactId.')';
            $errorMessage = $errorMessage ." is failed";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= " due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
        }
        curl_close($ch);
    }
    return true;
}

//get country code on the basis of code...
function get_country_code($code){
  global $wpdb,$table_prefix;
  $table_name = 'wp_wooconnection_countries';
  $countryDetails = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE code = '".$code."'");
  $countryCode = "";
  if(!empty($countryDetails[0]->countrycode)){
    $countryCode =$countryDetails[0]->countrycode;
  }
  return $countryCode;
}

//Create order in infusionsoft/keap application at the time checkout......
function createOrder($orderid,$contactId,$jsonOrderItems,$access_token){
    $newOrderId = "";
    if(!empty($contactId) && !empty($orderid) && !empty($access_token)){
      $wooconnectionLogger = new WC_Logger();
      $orderTitle = "New Order Generated where order number is #" . $orderid . " and generated from " . site_url();
      $url = "https://api.infusionsoft.com/crm/rest/v1/orders";
      $current_time = date("Y-m-d")."T".date("H:i:s")."Z"; 
      $jsonArray = '{
                      "contact_id": '.$contactId.',
                      "order_items": '.$jsonOrderItems.',
                      "order_date": "'.$current_time.'",
                      "order_title": "'.$orderTitle.'",
                      "order_type": "Offline"
                    }';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $header = array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '. $access_token
      );
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonArray);
      $newOrderResponse = curl_exec($ch);
      $newOrdererr = curl_error($ch);
      if($newOrdererr){
        $newOrderErrorMessage = "Create order for woocommerce order # ".$orderid." is fail due to ".$newOrdererr; 
        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($newOrderErrorMessage, true));
      }else{
        $newOrderSucessData = json_decode($newOrderResponse,true);
        if(isset($newOrderSucessData['fault']) && !empty($newOrderSucessData['fault'])){
          if(isset($newOrderSucessData['fault']['faultstring']) && !empty($newOrderSucessData['fault']['faultstring'])){
            $newOrderErrorMessage = "Create order for woocommerce order # ".$orderid." is fail due to ".$newOrderSucessData['fault']['faultstring']; 
          }
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($newOrderErrorMessage, true));
        }
        //check if order id exist.....
        if(!empty($newOrderSucessData)){
            if(!empty($newOrderSucessData['id'])){
              $newOrderId = $newOrderSucessData['id'];
              return $newOrderId;
            } 
        }
      }
      curl_close($ch); 
    }
    return $newOrderId;
    
}

//add product to infusionsoft/keap account..
function checkAddProductIsKp($access_token,$item,$parent_product_id=''){
    //define empty variables......
    $currentProductID = '';
    $checkAlreadyExist = '';
    //get product id...
    $productId = $item->get_id();
    //check product id on the basis of main product id.....
    $checkAlreadyExist = get_post_meta($productId, 'is_kp_product_id', true);
    if(empty($checkAlreadyExist) && $checkAlreadyExist !== '0'){
      //check if parent product id is exist it means product is a variation product....
      if(!empty($parent_product_id)){
          //then get the relation as same set for parent product....
          $checkAlreadyExist = get_post_meta($parent_product_id, 'is_kp_product_id', true);
      }
    }
    $wooconnectionLogger = new WC_Logger();
    //check product mapping exist else create new product and return the id newly created product.....
    if(isset($checkAlreadyExist) && !empty($checkAlreadyExist)){
       $currentProductID = $checkAlreadyExist; 
    }else{
      $wcproductSku = $item->get_sku();//get product sku....
      $wcproductPrice = $item->get_regular_price();
      $wcproductName = $item->get_name();//get product name....
      $wcproductDesc = $item->get_description();//get product description....
      if(isset($wcproductDesc) && !empty($wcproductDesc)){
          $wcproductDesc = $wcproductDesc;
      }else{
          $wcproductDesc = "";
      }
      $wcproductShortDesc = $item->get_short_description();//get product short description....
      if(isset($wcproductShortDesc) && !empty($wcproductShortDesc)){
          $wcproductShortDesc = $wcproductShortDesc;
      }else{
          $wcproductShortDesc = "";
      }
      $wcproductSlug =  $item->get_slug();//get product slug....
      //create final array with values.....
      $productDetailsArray = array();
      $productDetailsArray['active'] = true;
      $productDetailsArray['product_desc'] = $wcproductDesc;
      $productDetailsArray['product_price'] = $wcproductPrice;
      $productDetailsArray['product_short_desc'] = $wcproductShortDesc;
      $productDetailsArray['product_name'] = $wcproductName;
      $callback_purpose = 'Add Woocommerce Product : Process of add woocommerce product to infusionsoft/keap application at the time of order creation';
      //Check if product sku is not exist then create the sku on the basis of product slug.........
      if(isset($wcproductSku) && !empty($wcproductSku)){
          $existingProductIds =  checkProductAlreadyExistWithSku($wcproductSku,$access_token);
          if(!empty($existingProductIds)){
            $lastElement = end($existingProductIds);
            if(!empty($lastElement)){
              $currentProductID = $lastElement;
            }
          }
          if(empty($currentProductID)){
              //if "-" is exist in product sku then replace with "_".....
              if (strpos($wcproductSku, '-') !== false)
              {
                  $wcproductSku=str_replace("-", "_", $wcproductSku);
              }
              else
              {
                  $wcproductSku=$wcproductSku;
              }
              $productDetailsArray['sku'] = $wcproductSku;
              $jsonData = json_encode($productDetailsArray);

              $createdProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_FRONT_END,$wooconnectionLogger);
              if(!empty($createdProductId)){
                //update relationship between woocommerce product and infusionsoft/keap product...
                update_post_meta($productId, 'is_kp_product_id', $createdProductId);
                //update the woocommerce product sku......
                update_post_meta($productId,'_sku',$wcproductSku);
                $currentProductID = $createdProductId;
              }
          }
      }else{
          //if "-" is exist in product sku then replace with "_".....
          if (strpos($wcproductSlug, '-') !== false)
          {
              $wcproductSku=str_replace("-", "_", $wcproductSlug);
          }
          else
          {
              $wcproductSku=$wcproductSlug;
          }
          $productDetailsArray['sku'] = $wcproductSku;
          $jsonData = json_encode($productDetailsArray);
          $createdProductId = createNewProduct($access_token,$jsonData,$callback_purpose,LOG_TYPE_FRONT_END,$wooconnectionLogger);
          if(!empty($createdProductId)){
            //update relationship between woocommerce product and infusionsoft/keap product...
            update_post_meta($productId, 'is_kp_product_id', $createdProductId);
            //update the woocommerce product sku......
            update_post_meta($productId,'_sku',$wcproductSku);
            $currentProductID = $createdProductId;
          }         
                
      }
    }
    return $currentProductID;
}

//Check product is exist in infusionsoft/keap application on the basis of product sku......
function checkProductAlreadyExistWithSku($productsku,$token){
  $url = "https://api.infusionsoft.com/crm/rest/v1/products";
    $postparam = array( 
      'active'   => true 
    );
    $params = http_build_query($postparam);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url."?".$params); //using the setopt function to send request to the url
    $header = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer '. $token
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //response returned but stored not displayed in browser
    $response = curl_exec($ch); //executing request
    $err = curl_error($ch);
    $matchIdsArray = array();
    if($err){
    }else{
      $sucessData = json_decode($response,true);
      if(!empty($sucessData)){
        if($sucessData['count'] > 0){
          if(!empty($sucessData['products'])){
            foreach ($sucessData['products'] as $key => $value) {
                if(!empty($value['sku'])){
                  if($value['sku'] == $productsku){
                    $matchIdsArray[] = $value['id'];
                  }
                }
              }
            }
        } 
      }
      return $matchIdsArray;
    }
    curl_close($ch);
    return $matchIdsArray;
}

//Create new product at the time of checkout process.......
function createNewProduct($access_token,$productDetailsArray,$callback_purpose,$logtype,$wooconnectionLogger){
  $productId = '';
  if(!empty($access_token) && !empty($productDetailsArray)){
      $url = 'https://api.infusionsoft.com/crm/rest/v1/products';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $productDetailsArray);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
          $errorMessage = $logtype.' : '.$callback_purpose ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = $logtype.' : '.$callback_purpose ." is failed ";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
          if(!empty($sucessData['id'])){
            $productId = $sucessData['id'];
          }
          return $productId;
        }
        curl_close($ch);
  }
  return $productId;
}

//Get the infusionsoft/keap application order deatils on the basis of order id....
function getApplicationOrderDetails($access_token,$orderRelationId,$callback_purpose){
  $data = array();
  if(!empty($access_token) && !empty($orderRelationId)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/rest/v1/orders/'.$orderRelationId;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $productDetailsArray);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
          $errorMessage = $callback_purpose ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = $callback_purpose ." is failed ";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
          if(!empty($sucessData['order_items'])){
            $data = $sucessData['order_items'];
          }
          return $data;
        }
        curl_close($ch);
  }
  return $data;
}

//add notes for contact in infusionsoft/keap application....
function addContactNotes($access_token,$orderContactId,$noteText,$itemTitle,$callback_purpose){
    if(!empty($access_token) && !empty($orderContactId) && !empty($noteText)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $logtype = LOG_TYPE_FRONT_END;
        //create json array to push ocde in infusionsoft...
        if (strpos($noteText, ",") !== false)
        {
          $comma_separated = implode(",", $noteText);
        }else{
          $comma_separated = $noteText;
        }
        $jsonData ='{"body": "'.$comma_separated.'","title":"'.$itemTitle.'" ,"contact_id":'.$orderContactId.'}';
        $url = 'https://api.infusionsoft.com/crm/rest/v1/notes';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
          $errorMessage = $logtype.' : '.$callback_purpose ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = $logtype.' : '.$callback_purpose ." is failed ";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
          return true;
        }
        curl_close($ch);
    }
}

//delete infusionsoft/keap application order after the notes added for contact....
function deleteApplicationOrder($access_token,$orderRelationId,$callback_purpose){
  $data = true;
  if(!empty($access_token) && !empty($orderRelationId)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/rest/v1/orders/'.$orderRelationId;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $productDetailsArray);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
          $errorMessage = $callback_purpose ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = $callback_purpose ." is failed ";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
          return true;
        }
        curl_close($ch);
  }
  return $data;
}

//Function is used to add order item for order with xmlrpc request....
function addOrderItems($access_token,$orderid,$productId,$type,$price,$quan,$desc,$notes){
    //First needs to check access token and order is exist or not.....
    if(!empty($access_token) && !empty($orderid)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $access_token
        );
        //Create xml to hit the curl request for add order item.....
        $xmlData = "<methodCall><methodName>InvoiceService.addOrderItem</methodName><params><param><value><string></string></value></param><param><value><int>".$orderid."</int></value></param><param><value><int>".$productId."</int></value></param><param><value><int>".$type."</int></value></param><param><value><double>".$price."</double></value></param><param><value><int>".$quan."</int></value></param><param><value><string>".$desc."</string></value></param><param><value><string>".$notes."</string></value></param></params></methodCall>";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        //check if error occur due to any reason and then save the logs...
        if($err){
            $errorMessage = "Add order item to order #".$orderid." is failed due to ". $err; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $responsedata = xmlrpc_decode($response);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
              if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
                  $errorMessage = "Add order item to order #".$orderid." is failed due to ". $responsedata['faultString']; 
                  $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
              }
          }else{
            return true;
          }
        }
        curl_close($ch);
    }
}

//Function is used to get the product information from the the authenticate application.....
function getApplicationProductDetails($access_token,$productId){
    $productName = '';
    if(!empty($access_token) && !empty($productId))
    {
        $url = "https://api.infusionsoft.com/crm/rest/v1/products/".$productId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //using the setopt function to send request to the url
        $header = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer '. $access_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //response returned but stored not displayed in browser
        $response = curl_exec($ch); //executing request
        $err = curl_error($ch);
        if($err){
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
           
          }else{
            if(!empty($sucessData['product_name'])){
                $productName = $sucessData['product_name'];
            }
          }
          return $productName;
        }
        curl_close($ch);  
    }
    return $productName;
}

//Function is used to vaidate a credit card on the basis of card details.....
function validateCreditCard($accessToken,$cardDetails){
    $creditCardResponseData = '';
    //First needs to check access token is exist or not.....
    if(!empty($accessToken) && !empty($cardDetails)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $accessToken
        );
        
        //Create xml to hit the curl request to validate the credit card.....
        $creditCardXmlData = "<methodCall><methodName>InvoiceService.validateCreditCard</methodName><params><param><value><string></string></value></param><param><value><struct><member><name>CardType</name><value><string>".$cardDetails['CardType']."</string></value></member><member><name>ContactId</name><value><int>".$cardDetails['ContactId']."</int></value></member><member><name>CardNumber</name><value><string>".$cardDetails['CardNumber']."</string></value></member><member><name>ExpirationMonth</name><value><string>".$cardDetails['ExpirationMonth']."</string></value></member><member><name>ExpirationYear</name><value><string>".$cardDetails['ExpirationYear']."</string></value></member><member><name>CVV2</name><value><string>".$cardDetails['CVV2']."</string></value></member></struct></value></param></params></methodCall>";
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $creditCardXmlData);
        $creditCardResponse = curl_exec($ch);
        $creditCardErr = curl_error($ch);
        //check if error occur due to any reason and then save the logs...
        if($creditCardErr){
            $creditCardErrorMessage = "Validate contact credit card is failed due to ". $creditCardErr; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($creditCardErrorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $creditCardResponseData = xmlrpc_decode($creditCardResponse);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($creditCardResponseData) && xmlrpc_is_fault($creditCardResponseData)) {
              if(isset($creditCardResponseData['faultString']) && !empty($creditCardResponseData['faultString'])){
                  $creditCardErrorMessage = "Validate contact credit card is failed due to ". $creditCardResponseData['faultString']; 
                  $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($creditCardErrorMessage, true));
              }
          }else{
            return $creditCardResponseData;
          }
        }
        curl_close($ch);
    }
    return $creditCardResponseData;
}

//Function is used to vaidate a credit card on the basis of card details.....
function checkContactCardExist($access_token,$conatctId,$cardNumber){
    $contactCardResponseData = '';
    //First needs to check access token is exist or not.....
    if(!empty($access_token) && !empty($conatctId) && !empty($cardNumber)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $access_token
        );
        
        //Create xml to hit the curl request to check contact credit card already exist or not......
        $contactCardXmlData = "<methodCall><methodName>InvoiceService.locateExistingCard</methodName><params><param><value><string></string></value></param><param><value><int>".$conatctId."</int></value></param><param><value><string>".$cardNumber."</string></value></param></params></methodCall>";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contactCardXmlData);
        $contactCardResponse = curl_exec($ch);
        $contactCardErr = curl_error($ch);
        //check if error occur due to any reason and then save the logs...
        if($contactCardErr){
            $contactCardErrorMessage = "Check contact credit card is failed due to ". $contactCardErr; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($contactCardErrorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $contactCardResponseData = xmlrpc_decode($contactCardResponse);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($contactCardResponseData) && xmlrpc_is_fault($contactCardResponseData)) {
              if(isset($contactCardResponseData['faultString']) && !empty($contactCardResponseData['faultString'])){
                  $contactCardErrorMessage = "Check contact credit card is failed due to ". $contactCardResponseData['faultString']; 
                  $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($contactCardErrorMessage, true));
              }
          }else{
            return $contactCardResponseData;
          }
        }
        curl_close($ch);
    }
    return $contactCardResponseData;
}


//Function is used to update the existing credit card details....
function updateExistingCreditCard($access_token,$cardId,$cardFields){
    $updateCardResponseData = '';
    //First needs to check access token is exist or not.....
    if(!empty($access_token) && !empty($cardId) && !empty($cardFields)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $access_token
        );
        
        //create xml html by executing loop...
        $cardFieldsHtml = '';
        foreach ($cardFields as $key => $value) {
            $cardFieldsHtml .= '<member><name>'.$key.'</name><value><string>'.$value.'</string></value></member>';
        }

        //Create xml to hit the curl request to update credit card fields.....
        $updateCardXmlData = "<methodCall><methodName>DataService.update</methodName><params><param><value></value></param><param><value><string>CreditCard</string></value></param><param><value><int>".$cardId."</int></value></param><param><value><struct>".$cardFieldsHtml."</struct></value></param></params></methodCall>";
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updateCardXmlData);
        $updateCardResponse = curl_exec($ch);
        $updateCardErr = curl_error($ch);
        //check if error occur due to any reason and then save the logs...
        if($updateCardErr){
            $updateCardErrorMessage = "Update existing credit card details is failed due to ". $updateCardErr; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($updateCardErrorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $updateCardResponseData = xmlrpc_decode($updateCardResponse);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($updateCardResponseData) && xmlrpc_is_fault($updateCardResponseData)) {
              if(isset($updateCardResponseData['faultString']) && !empty($updateCardResponseData['faultString'])){
                  $updateCardErrorMessage = "Update existing credit card details is failed due to ". $updateCardResponseData['faultString']; 
                  $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($updateCardErrorMessage, true));
              }
          }else{
            return $updateCardResponseData;
          }
        }
        curl_close($ch);
    }
    return $updateCardResponseData;
}


//Function is used to add the new credit card details....
function addNewCreditCard($access_token,$creditCardFields){
    $addCardResponseData = '';
    //First needs to check access token is exist or not.....
    if(!empty($access_token) && !empty($creditCardFields)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $access_token
        );
        
        //create xml html by executing loop...
        $addCardFieldsHtml = '';
        foreach ($creditCardFields as $key => $value) {
            $addCardFieldsHtml .= '<member><name>'.$key.'</name><value><string>'.$value.'</string></value></member>';
        }

        //Create xml to hit the curl request for add order item.....
        $addCardXmlData = "<methodCall><methodName>DataService.add</methodName><params><param><value></value></param><param><value><string>CreditCard</string></value></param><param><value><struct>".$addCardFieldsHtml."</struct></value></param></params></methodCall>";
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $addCardXmlData);
        $addCardResponse = curl_exec($ch);
        $addCardErr = curl_error($ch);
        //check if error occur due to any reason and then save the logs...
        if($addCardErr){
            $addCardErrorMessage = "Add new credit card is failed due to ". $addCardErr; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($addCardErrorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $addCardResponseData = xmlrpc_decode($addCardResponse);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($addCardResponseData) && xmlrpc_is_fault($addCardResponseData)) {
              if(isset($addCardResponseData['faultString']) && !empty($addCardResponseData['faultString'])){
                  $addCardErrorMessage = "Add new credit card is failed due to ". $addCardResponseData['faultString']; 
                  $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($addCardErrorMessage, true));
              }
          }else{
            return $addCardResponseData;
          }
        }
        curl_close($ch);
    }
    return $addCardResponseData;
}

//Create order payment in infusionsoft/keap application at the time checkout......
function createOrderPayment($access_token,$orderid,$cardId,$merchId){
    $orderpaymentResponseData = '';
    //First needs to check access token is exist or not.....
    if(!empty($access_token) && !empty($orderid) && !empty($cardId) && !empty($merchId)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $access_token
        );

        //Create xml to hit the curl request to process the payment.....
        $orderpaymentXmlData = "<methodCall><methodName>InvoiceService.chargeInvoice</methodName><params><param><value><string></string></value></param><param><value><int>".$orderid."</int></value></param><param><value><string>Online Shopping Cart</string></value></param><param><value><int>".$cardId."</int></value></param><param><value><int>".$merchId."</int></value></param><param><value><boolean>0</boolean></value></param> </params></methodCall>";
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $orderpaymentXmlData);
        $orderPaymentResponse = curl_exec($ch);
        $orderPaymentErr = curl_error($ch);
        //check if error occur due to any reason and then save the logs...
        if($orderPaymentErr){
            $orderPaymentErrorMessage = "Process payment for application order # ".$orderid." is fail due to ".$orderPaymentErr; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($orderPaymentErrorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $orderpaymentResponseData = xmlrpc_decode($orderPaymentResponse);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($orderpaymentResponseData) && xmlrpc_is_fault($orderpaymentResponseData)) {
              if(isset($orderpaymentResponseData['faultString']) && !empty($orderpaymentResponseData['faultString'])){
                  $orderPaymentErrorMessage = "Validate contact credit card is failed due to ". $orderpaymentResponseData['faultString']; 
                  $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($orderPaymentErrorMessage, true));
              }
          }else{
            return $orderpaymentResponseData;
          }
        }
        curl_close($ch);
    }
    return $orderpaymentResponseData;
}

//create subscription plan at the time of export products.....
function addSubscriptionPlan($accessToken,$appProductId,$subJsonData,$logger)
{
  if(!empty($accessToken) && !empty($appProductId) && !empty($subJsonData)){
      //append the application product is in url to add the subscription plan for specific product.....
      $url = 'https://api.infusionsoft.com/crm/rest/v1/products/'.$appProductId.'/subscriptions';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $header = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer '. $accessToken
      );
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $subJsonData);
      $subResponse = curl_exec($ch);
      $subError = curl_error($ch);
      if($subError){
        $subErrorMessage = "Add subscription plan for application product #".$appProductId." is failed due to ".$subError;
        $wooconnection_logs_entry = $logger->add('infusionsoft',print_r($subErrorMessage,true));
        return false;
      }else{
        $subSucessData = json_decode($subResponse,true);
        if(isset($subSucessData['fault']) && !empty($subSucessData['fault'])){
          $subErrorMessage = 'Try to add subscription for particluar product #'.$appProductId.' in application is failed ';
          if(isset($subSucessData['fault']['faultstring']) && !empty($subSucessData['fault']['faultstring'])){
            $subErrorMessage .= "due to ".$subErrorMessage['fault']['faultstring'];
          }
          $wooconnection_logs_entry = $logger->add('infusionsoft',print_r($subErrorMessage,true));
          return false;
        }
        if(isset($subSucessData['message']) && !empty($subSucessData['message'])){
          $subErrorMessage = 'Process to add subscription plan for particluar product #'.$appProductId.' in application is failed due to ';
          $subErrorMessage .= $subSucessData['message'];
          $wooconnection_logs_entry = $logger->add('infusionsoft',print_r($subErrorMessage,true)); 
          return false;
        }
      }
      curl_close($ch);
  }
  return true;
}
?>