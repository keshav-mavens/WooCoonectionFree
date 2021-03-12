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
        if(strpos($email, "+") !== false)
        {
            $email = str_replace("+", "$", $email);
        }
        else 
        {
            $email = $email; 
        }
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
function createExportProductsHtml($limit='',$offset='',$htmlType=''){
  //Define export table html variable.....
  $table_export_products_html = "";
  //call the common function to get the list of woocommerce publish products...
  $woocommerceProducts = listExistingDatabaseWooProducts($limit,$offset);
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
  $applicationProductsArray =  getExistingAppProducts();
  
  //set html if no products exist in woocommerce for export....
  if(empty($woocommerceProducts)){
      if(empty($htmlType)){
        $addProductLink = admin_url('post-new.php?post_type=product');//set the link of add product page...
        $table_export_products_html = '<span class="no-woo-products"><p class="heading-text" style="text-align:center"><strong>You don’t have any products set up in WooCommerce that we can export to your '.$applicationLabel.' application.</strong></p><input type="button" value="Add a Product" class="btn btn-primary btn-radius btn-theme add-product-btn"  onclick = "showAddProductScreen(\''.$addProductLink.'\')"><span>';
      }
  }else{
      //Compare woocommerce publish products with application products
      $exportProductsData = exportProductsListingApplication($woocommerceProducts,$applicationProductsArray,$applicationLabel,$htmlType);
      if(isset($exportProductsData) && !empty($exportProductsData)){
          //Get the export products table html and append to table
          if(!empty($exportProductsData['exportTableHtml'])){
            if(!empty($htmlType) && $htmlType == PRODUCTS_HTML_TYPE_LOAD_MORE){
                $table_export_products_html = $exportProductsData['exportTableHtml'];
            }
            else{
              $table_export_products_html .= '<form action="" method="post" id="wc_export_products_form" onsubmit="return false">  
                <table class="table table-striped export_products_listing_class" id="export_products_listing">
                  '.$exportProductsData['exportTableHtml'].'
                </table>
                <div class="load_table_export_products loading_products text-center" style="display:none;"></div>
                <div class="exportProducts text-center" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Exporting products to your '.$applicationLabel.' account.</div>
                <div class="alert-error-message export-products-error" style="display: none;"></div>
                <div class="alert-sucess-message export-products-success" style="display: none;">Products export successfully.</div>
                <div class="btn-footer">
                  <div class="products-btn">
                    <input type="button" value="Load More Products" class="btn btn-primary btn-radius btn-theme load_products_export" onclick="loadMoreProducts()">
                  </div>
                  <div class="products-btn text-right">
                    <input type="button" value="Export Products" class="btn btn-primary btn-radius btn-theme export_products_btn" onclick="wcProductsExport()">
                  </div>
                </div>
              </form>';
            }
          }
      }
  }
  //return the html...
  return $table_export_products_html;
}

//list of existing woocommerce products from database and then return...
function listExistingDatabaseWooProducts($limit='',$offset=''){
    $productsLimit = 200;
    $productsOffset = 0;
    if(!empty($limit)){
      $productsLimit = $limit;
    }
    if(!empty($offset)){
      $productsOffset = $offset;
    }
    $productsListing = array();
    $existProductsDetails = get_posts(array('post_type' => 'product','post_status'=>'publish','orderby' => 'post_date','order' => 'DESC','posts_per_page'=>$productsLimit,'offset'=>$productsOffset));
    if(!empty($existProductsDetails)){
      $productsListing = $existProductsDetails;
    }
    return $existProductsDetails;
}

//create products listing if infusionsoft/keap products are exist...
function exportProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationType,$htmlType=''){
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

    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        if(empty($htmlType)){
          //Create first table....
          $exportTableHtml .= '<thead>';
          $exportTableHtml .= '<tr><th style="text-align: center;"><input type="checkbox" id="export_products_all" name="export_products_all" class="all_products_checkbox_export" value="allproductsexport"></th><th>WooCommerce Product Name</th><th>WooCommerce Product SKU</th><th>WooCommerce Product Price</th><th>'.$applicationType.' Product</th></tr>';
          $exportTableHtml .= '</thead>';
          $exportTableHtml .= '<tbody>';
        }
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
                //first check if application products is not empty. If empty then skip match products process and show the html in place of select...
                if(!empty($applicationProductsArray)){
                    //Check product relation is exist....
                    $productExistId = get_post_meta($wc_product_id, 'is_kp_product_id', true);
                    //If product relation exist then create select deopdown and set associative product selected....
                    if(isset($productExistId) && !empty($productExistId)){
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
                      $key = array_search($matchProductId, array_column($applicationProductsArray, 'app_product_id'));
                      if (!empty($key) || $key === 0) {
                        $productDetails = $applicationProductsArray[$key];
                        if(!empty($productDetails->app_product_name)){
                          $productsDropDown = '<input type="hidden" value="'.$matchProductId.'" name="wc_product_export_with_'.$wc_product_id.'">'.$productDetails->app_product_name;
                        }
                      }else{
                        $productsDropDown = 'Mapped Product Not Exist In App!';
                      }
                    }
                    else{
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
                //Create final html.......
                $exportTableHtml .= '<tr><td style="text-align: center;"><input type="checkbox" class="each_product_checkbox_export" name="wc_products[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td><td>'.$wcproductName.'</td><td class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';

            }

        }
        $exportProductsData['exportTableHtml'] = $exportTableHtml;//Assign html....
    }
    return $exportProductsData;//Return data....
}

//Check product with same sku is exist or not , if exist then return match products id.....
function checkProductMapping($sku,$productsArray){
    $matchProductsIds = array();//Define array...
    if(!empty($productsArray)){//check is products array is not empty....
        //Execute loop on application prdoucts array,......
        foreach ($productsArray as $key => $value) {
          if(!empty($value->app_product_id)){//check product id....
              //compare sku, if match the return the ids..
              if(!empty($value->app_product_sku)){
                  if($value->app_product_sku == $sku){
                    $matchProductsIds[] = $value->app_product_id;
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
function createMatchProductsHtml($matchProductsLimit='',$matchProductsOffset='',$matchProductHtmlType=''){
  global $wpdb;
  //Define match table html variable.....
  $table_match_products_html = "";
  //Define array to manage the sorting.....
  $wcproductsArray = array();
  //call the common function to get the list of woocommerce products they are in relation with application products.......
  $wooCommerceProducts = listExistingDatabaseWooProducts($matchProductsLimit,$matchProductsOffset);
  
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
  $applicationProductsArray = getExistingAppProducts();
  
  //set html if no products exist in woocommerce they are in relation with applcation products....
  if(empty($wooCommerceProducts)){
    if(empty($matchProductHtmlType)){
      $addProductLink = admin_url('post-new.php?post_type=product');//set the link of add product page...
      $table_match_products_html = '<span class="no-woo-products-match"><p class="heading-text" style="text-align:center"><strong>You don’t have any products set up in WooCommerce to map with your '.$applicationLabel.' application products.</strong></p><input type="button" value="Add a Product" class="btn btn-primary btn-radius btn-theme add-product-btn" onclick = "showAddProductScreen(\''.$addProductLink.'\')"><span>';
    }
  }else{
      //Compare woocommerce publish products application products....
      $matchProductsData = createMatchProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationLabel,$matchProductHtmlType);
      //Check export products data....
      if(isset($matchProductsData) && !empty($matchProductsData)){
          //Get the match products table html and append to table
          if(!empty($matchProductsData['matchTableHtml'])){
            if(!empty($matchProductHtmlType) && $matchProductHtmlType == PRODUCTS_HTML_TYPE_LOAD_MORE){
                $table_match_products_html = $matchProductsData['matchTableHtml'];
            }
            else{
              $table_match_products_html .= '<span class="ajax_loader_match_products_related" style="display:none"><img src="'.WOOCONNECTION_PLUGIN_URL.'assets/images/loader.gif"></span><form action="" method="post" id="wc_match_products_form" onsubmit="return false">  
                <table class="table table-striped match_products_listing_class" id="match_products_listing">
                  '.$matchProductsData['matchTableHtml'].'
                </table>
                <div class="form-group col-md-12 text-center">
                  <div class="load_table_match_products loading_products" style="text-align: center;display: none;"></div>
                  <input type="button" value="Load More Products" class="btn btn-primary btn-radius btn-theme" style="margin-top:10px;" onclick="loadMoreProducts()">
                </div>
                </form>';
            }
          }
      }
  }
  //return the html...
  return $table_match_products_html;
}

//Create the match products table listing....
function createMatchProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationType,$matchProductHtmlType=''){
    $matchTableHtml  = '';//Define variable..
    $matchProductsData = array();//Define array...
    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        if(empty($matchProductHtmlType)){
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
        }
        $productExistId = '';
        foreach ($wooCommerceProducts as $key => $value) {
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
                //first check if application products is not empty. If empty then skip match products process and show the html in place of select...
                if(!empty($applicationProductsArray)){
                    //Check product relation is exist....
                    $productExistId = get_post_meta($wc_product_id, 'is_kp_product_id', true);
                    //If product relation exist then create select deopdown and set associative product selected....
                    if(isset($productExistId) && !empty($productExistId)){
                      $productsDropDown = createMatchProductsSelect($applicationProductsArray,$productExistId);
                    }else{
                      $productsDropDown = createMatchProductsSelect($applicationProductsArray);
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

                //Create final html.......
                $matchTableHtml .= '<tr id="table_row_'.$wc_product_id.'"><td>'.$actionHtml.'</td><td>'.$wcproductName.'</td><td  class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';

            }

        }
        $matchProductsData['matchTableHtml'] = $matchTableHtml;//Assign html....
    }
    return $matchProductsData;//Return data....
}

//create the infusionsoft products dropdown for mapping..........
function createMatchProductsSelect($existingiskpProductResult,$wc_product_id_compare=''){
    $iskp_products_options_html = '';//Define variable...
    if(isset($existingiskpProductResult) && !empty($existingiskpProductResult)){//check application products...
        foreach($existingiskpProductResult as $iskpProductDetails) {
          $iskpProductId = $iskpProductDetails->app_product_id;//get or set the product id....
          $iskpProductName = $iskpProductDetails->app_product_name;//get or set the product name....
          $iskpProductSelected = "";
          if(!empty($wc_product_id_compare)){//if relation exist...
              if($wc_product_id_compare == $iskpProductId){//then compare the relation between products....
                  $iskpProductSelected = "selected";//set product selected....
              }else{
                  $iskpProductSelected = "";
              }
          }
           //create the final html.....
          $iskp_products_options_html.= '<option value="'.$iskpProductId.'" '.$iskpProductSelected.' data-id="'.$iskpProductId.'">'.$iskpProductName.'</option>';
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
          [{"field": "PHONE1","number": "'.$phone1.'","type":"Mobile"}],"given_name": "'.$firstName.'"}';
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
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
        }else{
          $sucessData = json_decode($response,true);
          if(!empty($sucessData)){
              if(!empty($sucessData['id'])){
                $newOrderId = $sucessData['id'];
                return $newOrderId;
              } 
          }
        }
        curl_close($ch);  
    }
    return $newOrderId;
    
}

//add product to infusionsoft/keap account..
function checkAddProductIsKp($access_token,$item,$parent_product_id='',$appEdition=''){
    global $wpdb,$table_prefix;
    $appProductsTableName = $table_prefix.'authorize_application_products';
    //define empty variables......
    $currentProductID = '';
    $checkAlreadyExist = '';
    $appProductData = array();
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

    //set default mapped product exist in application or not.....
    $productExistStatus= true;
    //check mapping exist in database...
    if(!empty($checkAlreadyExist)){
      //get the application product details by product id.....
      $checkAppProduct = getApplicationProductDetail($checkAlreadyExist,$access_token);
      //check api return the product details....
      if(!empty($checkAppProduct)){
        if(!empty($checkAppProduct['product_name'])){
          $productExistStatus = true;
        }else{
          $productExistStatus = false;
        }
      }
    }
    
    $wooconnectionLogger = new WC_Logger();
    //check product mapping exist else create new product and return the id newly created product.....
    if(isset($checkAlreadyExist) && !empty($checkAlreadyExist) && $productExistStatus == true){
       $currentProductID = $checkAlreadyExist; 
    }else{
      $wcproductSku = $item->get_sku();//get product sku....
      $wcproductPrice = $item->get_regular_price();
      $wcproductName = $item->get_name();//get product name....
      $wcproductDesc = $item->get_description();//get product description....
      if(isset($wcproductDesc) && !empty($wcproductDesc)){
          //check if application edition is keap...
          if($appEdition == APPLICATION_TYPE_KEAP){
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
      $wcproductShortDesc = $item->get_short_description();//get product short description....
      if(isset($wcproductShortDesc) && !empty($wcproductShortDesc)){
          $wcproductShortDesc = strip_tags($wcproductShortDesc);
          $shortDescriptionLen = strlen($wcproductShortDesc);
          //check if application edition is keap....
          if($appEdition == APPLICATION_TYPE_KEAP){
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
                //create the array then insert into the wordpress database.....
                $appProductData['app_product_id'] = $currentProductID;
                $appProductData['app_product_name'] =  $wcproductName;
                $appProductData['app_product_description'] = $productDetailsArray['product_desc'];  
                $appProductData['app_product_excerpt'] = $productDetailsArray['product_short_desc'];
                $appProductData['app_product_sku'] = $wcproductSku;
                $appProductData['app_product_price'] = $wcproductPrice;
                $wpdb->insert($appProductsTableName,$appProductData);
                if(!empty($checkAlreadyExist)){//check if match product is exist then mark the status deleted in wp database....
                  $wpdb->update($appProductsTableName, array('app_product_status'=>STATUS_DELETED),array('app_product_id'=>$checkAlreadyExist));
                }
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
          $wcproductSku = substr($wcproductSku,0,10);//get the first 10 chararters from the sku string....
          if(isset($productId) && !empty($productId)){//check the product id exist.....
            $wcproductSku = $wcproductSku.$productId;//append the product id in sku to define as a unique....
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
            //create the array then insert into the wordpress batabase...
            $appProductData['app_product_id'] = $currentProductID;
            $appProductData['app_product_name'] =  $wcproductName;
            $appProductData['app_product_description'] = $productDetailsArray['product_desc'];  
            $appProductData['app_product_excerpt'] = $productDetailsArray['product_short_desc'];
            $appProductData['app_product_sku'] = $wcproductSku;
            $appProductData['app_product_price'] = $wcproductPrice;
            $wpdb->insert($appProductsTableName,$appProductData);
            if(!empty($checkAlreadyExist)){//check if match product is exist then mark the status deleted in wp database....
              $wpdb->update($appProductsTableName, array('app_product_status'=>STATUS_DELETED),array('app_product_id'=>$checkAlreadyExist));
            }
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

//get the amount owned by the authoenticate application order.....
function getOrderAmountOwned($access_token,$orderId,$logger){
  //define empty variable....
  $amountOwned = '';
  //check access token and application order id exist......
  if(!empty($access_token) && !empty($orderId)){
    //set xmlrpc api link to get the amount owned by the application order.....
    $curlUrl = "https://api.infusionsoft.com/crm/xmlrpc/v1";
    $ch = curl_init($curlUrl);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $header = array('Accept:text/xml','Content-Type:text/xml','Authorization:Bearer '.$access_token);
    
    //create xml to hit curl request to get the amount owned by application order......
    $loadAmountXml = "<?xml version='1.0' encoding='UTF-8'?><methodCall><methodName>InvoiceService.calculateAmountOwed</methodName><params><param><value><string></string></value></param><param><value><int>".$orderId."</int></value></param></params></methodCall>";
    
    //curl setup....
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch,CURLOPT_POSTFIELDS,$loadAmountXml);
    
    //get the curl request error and response......
    $amountOwnedResponse = curl_exec($ch);
    $amountOwnedErr = curl_error($ch);
    //check error exist....
    if($amountOwnedErr){
        $amountOwnedErrorMessage = "Process to get order amount owned is failed due to ".$amountOwnedErr;
        $wooconnection_logs_entry = $logger->add('infusionsoft',print_r($amountOwnedErrorMessage));
    }else{
      //Convert/Decode response to xml....
      $amountOwnedResponseData = xmlrpc_decode($amountOwnedResponse);
      //check if any error occur like invalid access token,then save logs....
      if (is_array($amountOwnedResponseData) && xmlrpc_is_fault($amountOwnedResponseData)) {
          if(isset($amountOwnedResponseData['faultString']) && !empty($amountOwnedResponseData['faultString'])){
              $amountOwnedErrorMessage = "Process to get order amount owned is failed due to ". $amountOwnedResponseData['faultString']; 
              $wooconnection_logs_entry = $logger->add('infusionsoft', print_r($amountOwnedErrorMessage, true));
          }
      }else{
        //set the amount value owned by application order.....
        $amountOwned = $amountOwnedResponseData;
      }
    }
    curl_close($ch);
  }
  //return actual amount owned by application order
  return $amountOwned;
}


//charge a manual payment...
function chargePaymentManual($accessToken,$orderId,$amountDue,$description,$mode,$logger){
    //define empty variables....
    $paymentStatus = '';
    //check access token,order id exist then proceed next.....
    if(!empty($accessToken) && !empty($orderId)){
        //set xmlrpc api link to charge the amount owned for application order manually......
        $url = "https://api.infusionsoft.com/crm/xmlrpc/v1";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array('Accept:text/xml','Content-Type:text/xml','Authorization:Bearer '.$accessToken);
        //check mode and on the basis of it set the payment type of custom payment gateway......
        if(!empty($mode)){
          if($mode == PAYMENT_MODE_TEST){
            $paymentType = 'Payment of Test Mode';
          }else if($mode == PAYMENT_MODE_SKIPPED ){
            $paymentType = 'Payment of zero amount';
          }else{
            $paymentType = $mode;
          }
        }else{
          $paymentType = 'Credit Card';
        }
        //get/set the current date time for application order....
        $currentDateTime = new DateTime("now",new DateTimeZone('America/New_York'));
        $paymentDateTime = $currentDateTime->format('Ymd\TH:i:s');
        //create xml to hit the curl request to done payment manually.....
        $chargePaymentXml = "<methodCall><methodName>InvoiceService.addManualPayment</methodName><params>
                                  <param><value><string></string></value></param><param><value><int>".$orderId."</int></value></param><param><value><double>".$amountDue."</double></value></param><param><value><dateTime.iso8601>".$paymentDateTime."</dateTime.iso8601></value></param><param><value><string>".$paymentType."</string></value></param><param><value><string>Woocommerce Payment With ".$description." Method.</string></value></param><param><value><boolean>0</boolean></value></param></params></methodCall>";
        //curl setup....
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $chargePaymentXml);
        
        //get the curl repsone and curl error..
        $chargePaymentResponse = curl_exec($ch);
        $chargePaymentErr = curl_error($ch);
        //first check curl error exist.........
        if($chargePaymentErr){
          $chargePaymentErrorMessage = "Process to get order amount owned is failed due to ".$chargePaymentErr; 
          $wooconnection_logs_entry = $logger->add('infusionsoft', print_r($chargePaymentErrorMessage, true));
        }else{
          //Covert/Decode response to xml.....
          $chargePaymentResponseData = xmlrpc_decode($chargePaymentResponse);
          //check if any error occur like invalid access token,then save logs....
          if (is_array($chargePaymentResponseData) && xmlrpc_is_fault($chargePaymentResponseData)) {
              if(isset($chargePaymentResponseData['faultString']) && !empty($chargePaymentResponseData['faultString'])){
                  $amountOwnedErrorMessage = "Process to get order amount owned is failed due to ". $chargePaymentResponseData['faultString']; 
                  $wooconnection_logs_entry = $logger->add('infusionsoft', print_r($amountOwnedErrorMessage, true));
              }
          }else{
            //set payment status....
            $paymentStatus = $chargePaymentResponseData;
          }
        }
        curl_close($ch);
    }
    //return payment status...
    return $paymentStatus;
}


//Get the application product details by product id....
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

//get the list of application products from database....
function getExistingAppProducts(){
    global $wpdb,$table_prefix;
    $appProListing = array();
    $appProductsTableName = $table_prefix.'authorize_application_products';
    if($wpdb->get_var("SHOW TABLES LIKE '$appProductsTableName'") == $appProductsTableName) {
      $productsListing = $wpdb->get_results("SELECT * FROM `".$appProductsTableName."` WHERE app_product_status=".STATUS_ACTIVE." ORDER BY id DESC");
      if(isset($productsListing) && !empty($productsListing)){
          $appProListing = $productsListing;
      }
    }
    return $appProListing;
}

//Get the list of application products....
function getAppProductBySku($sku,$access_token){
    $skuMatchProductIds = array();
    if(!empty($sku) && !empty($access_token)){
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

      //Create xml to hit the curl request for get the list of products......
      $getProductsXml = '<methodCall>
                            <methodName>DataService.findByField</methodName>
                              <params><param><value><string></string></value></param><param><value><string>Product</string></value></param><param><value><int>1000</int></value></param><param><value><int>0</int></value></param><param><value><string>Sku</string></value></param><param><value><string>'.$sku.'</string></value></param><param><value><array><data><value><string>Id</string></value><value><string>ProductName</string></value></data></array></value></param></params></methodCall>';

      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $getProductsXml);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      //check if error occur due to any reason and then save the logs...
      if($err){
          $errorMessage = "Get the product detail by product sku is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
      }else{
        //Covert/Decode response to xml.....
        $responsedata = xmlrpc_decode($response);
        //check if any error occur like invalid access token,then save logs....
        if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
            if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
                $errorMessage = "Get the product detail by sku is failed due to ". $responsedata['faultString']; 
                $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
            }
        }else{
          $skuMatchProductIds = $responsedata;
        }
      }
      curl_close($ch);
    }
    return $skuMatchProductIds;
}

?>