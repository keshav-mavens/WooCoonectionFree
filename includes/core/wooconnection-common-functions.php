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
    }elseif (empty($plugin_settings['plugin_version'])) {
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
    if(!empty($plugin_settings['plugin_activation_status']) && $plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED && !empty($plugin_settings['plugin_version']) && $plugin_settings['plugin_version'] == ACTIVATION_PRODUCT_ID){
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
  $campaignGoalDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE wc_trigger_type=".$trigger_type." ORDER BY id ASC");
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
  $applicationProductsArray =  getApplicationProducts();
  
  //set html if no products exist in woocommerce for export....
  if(empty($woocommerceProducts)){
      if(empty($htmlType)){
        $table_export_products_html = '<p class="heading-text" style="text-align:center">No products exist in woocommerce for export to '.$applicationLabel.' application.</p>';
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
                <div class="form-group col-md-12 text-center m-t-25">
                  <div class="load_table_export_products loading_products" style="display:none;"></div>
                  <div class="exportProducts" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Exporting products to your '.$applicationLabel.' account.</div>
                  <div class="alert-error-message export-products-error" style="display: none;"></div>
                  <div class="alert-sucess-message export-products-success" style="display: none;">Products export successfully.</div>
                  <input type="button" value="Export Products" class="btn btn-primary btn-radius btn-theme export_products_btn" onclick="wcProductsExport()">
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
    $productsLimit = 20;
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
                      $key = array_search($matchProductId, array_column($applicationProductsArray, 'id'));
                      if (!empty($key) || $key === 0) {
                        $productDetails = $applicationProductsArray[$key];
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
          if(!empty($value['id'])){//check product id....
              //compare sku, if match the return the ids..
              if(isset($value['Sku']) && !empty($value['Sku'])){
                  if($value['Sku'] == $sku){
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
    if($plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED && !empty($plugin_settings['plugin_version']) && $plugin_settings['plugin_version'] == ACTIVATION_PRODUCT_ID){
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
function getApplicationProducts($appLimit = '',$pageNumber = ''){
    //define the empty variables.....
    $productsListing = array();
    
    //set the default variable values...
    $appProductsLimit = 20;
    $appProductsPageNumber = 0; 

    //check if limit exist in function parameter then override the default value of limit...
    if(!empty($appLimit)){
      $appProductsLimit = $appLimit;
    }

    //check if offset exist in function parameter then override the default value of offset....
    if(!empty($pageNumber)){
      $appProductsPageNumber = $pageNumber;
    }

    //first need to check connection is created or not infusionsoft/keap application then next process need to done..
    $applicationAuthenticationDetails = getAuthenticationDetails();
    //get the access token....
    $access_token = '';
    if(!empty($applicationAuthenticationDetails)){
      if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
          $access_token = $applicationAuthenticationDetails[0]->user_access_token;
      }
    }
    
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
    $getProductsXml = '<methodCall><methodName>DataService.findByField</methodName><params><param><value><string></string></value></param><param><value><string>Product</string></value></param><param><value><int>'.$appProductsLimit.'</int></value></param><param><value><int>'.$appProductsPageNumber.'</int></value></param><param><value><string>Status</string></value></param><param><value><string>1</string></value></param><param><value><array><data><value><string>Id</string></value><value><string>ProductName</string></value><value><string>Sku</string></value><value><string>ProductPrice</string></value><value><string>Description</string></value><value><string>ShortDescription</string></value></data></array></value></param></params></methodCall>';

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $getProductsXml);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    //check if error occur due to any reason and then save the logs...
    if($err){
        $errorMessage = "Get the list of products is failed due to ". $err; 
        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
    }else{
      //Covert/Decode response to xml.....
      $responsedata = xmlrpc_decode($response);
      //check if any error occur like invalid access token,then save logs....
      if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
          if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
              $errorMessage = "Get the list of products is failed due to ". $responsedata['faultString']; 
              $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
      }else{
        $productsListing = $responsedata;
      }
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
  $applicationProductsArray = getApplicationProducts();
  
  //set html if no products exist in woocommerce they are in relation with applcation products....
  if(empty($wooCommerceProducts)){
    if(empty($matchProductHtmlType)){
      $table_match_products_html = '<p class="heading-text" style="text-align:center">No products mapping exist.</p>';
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
                </table></form>';
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
          $iskpProductId = $iskpProductDetails['Id'];//get or set the product id....
          $iskpProductName = $iskpProductDetails['ProductName'];//get or set the product name....
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
        $leadsourceId = '';//define empty variable....
        //first check "leadsourceId" exist in cookie....
        if( isset($_COOKIE["leadsourceId"])){
            $leadsourceId = $_COOKIE["leadsourceId"];
        }else{
          //the check urm parameters value exist......
          if(isset($_COOKIE["lscategory"]) && isset($_COOKIE["lsmedium"]) && isset($_COOKIE["lsvendor"])){
              $message = '';
              if(isset($_COOKIE['lsmessage'])){
                $message = $_COOKIE['lsmessage'];
              }
              //call the function to check or add leadsource on the basis of utm parameters.....
              $leadsourceId = checkAddLeadSource($access_token,$_COOKIE["lscategory"],$_COOKIE["lsmedium"],$_COOKIE["lsvendor"],$message);
          }
        }
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        //create json array to push ocde in infusionsoft...
        $jsonData ='{"duplicate_option": "Email","email_addresses":[{"email": "'.$appUseremail.'","field": "EMAIL1"}],"opt_in_reason": "Customer opted-in through '.SITE_URL.'","lead_source_id": "'.$leadsourceId.'"}';
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
            if(!headers_sent()){
                if (isset($_COOKIE['leadsourceId'])){
                    setcookie( 'leadsourceId', '', time() - 999999, '/', $_SERVER['SERVER_NAME'] );
                }
                if(isset($_COOKIE['lscategory'])){
                  setcookie( 'lscategory', '', time() - 999999, '/', $_SERVER['SERVER_NAME'] );
                }
                if(isset($_COOKIE['lsmedium'])){
                  setcookie( 'lsmedium', '', time() - 999999, '/', $_SERVER['SERVER_NAME'] );
                }
                if(isset($_COOKIE['lsvendor'])){
                  setcookie( 'lsvendor', '', time() - 999999, '/', $_SERVER['SERVER_NAME'] );
                }
                if(isset($_COOKIE['lsmessage'])){
                  setcookie( 'lsmessage', '', time() - 999999, '/', $_SERVER['SERVER_NAME'] );
                }
            }
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

//get country code on the basis of code...
function getCountryName($code){
  global $wpdb,$table_prefix;
  $table_name = 'wp_wooconnection_countries';
  $countryDetails = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE code = '".$code."'");
  $countryname = "";
  if(!empty($countryDetails[0]->countryname)){
    $countryname =$countryDetails[0]->countryname;
  }
  return $countryname;
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


//Main function is used to generate the create products html.....
function createImportProductsHtml($importProductsLimit='',$importProductsPageNumber='',$importProductHtmlType=''){
    //Define import table html variable or arrays.....
    $isKeapProductsArray = array();
    $applicationProductsArray = array();
    $table_products_html_import = '';
    
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
    $applicationProductsArray = getApplicationProducts($importProductsLimit,$importProductsPageNumber);
    
    //Call the function to get the listing of woocommerce publish products....
    $existingProductResult = listExistingDatabaseWooProducts();

    //set html if no products exist in infusionsoft/keap account for import....
    if(empty($applicationProductsArray)){
        //return html only when import product html type is empty.....
        if(empty($importProductHtmlType)){
            $table_products_html_import = '<p class="heading-text" style="text-align:center">No products exist in authenticate '.$applicationLabel.' account for import.</p>';
        }
    }else{
        //Compare woocommerce publish products application products....
        $importProductsData = createImportProductsListingApplication($applicationProductsArray,$existingProductResult,$applicationLabel,$importProductHtmlType);
        //Check product data....
        if(isset($importProductsData) && !empty($importProductsData)){
            //Get the import products table html and append to table
            if(!empty($importProductsData['importTableHtml'])){
              if(!empty($importProductHtmlType) && $importProductHtmlType == PRODUCTS_HTML_TYPE_LOAD_MORE){
                    $table_products_html_import .= $importProductsData['importTableHtml'];
              }else{

                    $table_products_html_import .= '<form action="" method="post" id="wc_import_products_form" onsubmit="return false">  
                    <table class="table table-striped import_products_listing_class" id="import_products_listing">
                      '.$importProductsData['importTableHtml'].'
                    </table>
                    <div class="form-group col-md-12 text-center m-t-60">
                      <div class="load_table_import_products loading_products" style="display:none"></div>
                      <div class="load_table_export_products loading_products" style="display:none;"></div>
                      <div class="importProducts" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Importing products from your '.$applicationLabel.' account.</div>
                      <div class="alert-error-message import-products-error" style="display: none;"></div>
                      <div class="alert-sucess-message import-products-success" style="display: none;">Products import successfully.</div>
                      <input type="button" value="Import Products" class="btn btn-primary btn-radius btn-theme import_products_btn" onclick="infusionKeapProductsImport()">
                    </div>
                  </form>';
              }
            }
        }
    }
    //return the html...
    return $table_products_html_import;
}

//Create the match products table listing....
function createImportProductsListingApplication($applicationProductsArray,$wooCommerceProducts,$applicationType,$importProductHtmlType=''){
    $importTableHtml  = '';//Define variable..
    $importProductsData = array();//Define array...
    //First check if wooproducts exist...
    if(isset($applicationProductsArray) && !empty($applicationProductsArray)){
        //Create first table....
        if(empty($importProductHtmlType)){
          $importTableHtml .= '<thead>';
          $importTableHtml .= '<tr>
                          <th style="text-align: center;"><input type="checkbox" id="import_products_all" name="import_products_all" class="all_products_checkbox_import" value="allproductsexport"></th>
                          <th>'.$applicationType.' Product Name</th>
                          <th>'.$applicationType.' Product SKU</th>
                          <th>'.$applicationType.' Product Price</th>
                          <th>Woocommerce Product</th>
                        </tr>';
          $importTableHtml .= '</thead>';
          $importTableHtml .= '<tbody>';
        }
        if(!empty($wooCommerceProducts)){
          $wcProductsDropDown = createImportProductsSelect($wooCommerceProducts);
        }
        foreach ($applicationProductsArray as $key => $value) {
            if(!empty($value['Id'])){
                $wcProductExistId = '';
                $wcProductSelectHtml = '';
                $appProductId = $value['Id'];//Define product id...                  
                $appProductPrice = $value['ProductPrice'];//Get product price....
                $currencySign = get_woocommerce_currency_symbol();//Get currency symbol....
                //check product price and set....
                if(!empty($appProductPrice)){
                    $appProductPrice = $appProductPrice;
                }else{
                    $appProductPrice = 0;
                }
                //Create final price to display...
                $appProductPrice = $currencySign.number_format($appProductPrice,2);
                $appProductSku = $value['Sku'];//get product sku....
                $appProductName = $value['ProductName'];//get product name....
                //$wcProductsDropDown = '';
                //check and set the product name....
                if(!empty($appProductName)){
                  $appProductName = $appProductName;
                }else{
                  $appProductName = "--";
                }
                //first check if application products is not empty. If empty then skip match products process and show the html in place of select...
                if(!empty($wooCommerceProducts)){
                    //Check product relation is exist....
                    $wcProductExistId = getProductId('is_kp_product_id',$appProductId);
                    if(empty($wcProductExistId)){
                      $checkSkuMatchWithWcProducts = checkWcProductExistSku($appProductSku,$wooCommerceProducts);
                      //if product/multiple products with same sku is exist in woocommerce products then get the last matched product id.... 
                      if(isset($checkSkuMatchWithWcProducts) && !empty($checkSkuMatchWithWcProducts)){
                          $matchWcProductId =  end($checkSkuMatchWithWcProducts);
                          //On the basis of match product id set the product selected and create html.....
                          if(!empty($matchWcProductId)){
                            $wcProductExistId = $matchWcProductId;
                          }
                      } 
                    }
                    //Create final select html.....
                    $wcProductSelectHtml = '<select class="wc_import_products_dropdown wcProductsDropdown" name="wc_product_import_with_'.$appProductId.'" data-target="'.$appProductId.'" data-id="'.$wcProductExistId.'"><option value="0">Select woocommerce product</option>'.$wcProductsDropDown.'</select>';
                }else{
                  //Set the html of select if no products exist in application....
                  $wcProductSelectHtml = 'No Woocommerce Products Exist!';
                }
                //Check and set the product sku to display.....
                if(!empty($appProductSku)){
                  $appProductSku = $appProductSku;
                }else{
                  $appProductSku = "--";
                }
                //Create final html.......
                $importTableHtml .= '<tr><input type="hidden" name="plan_id_'.$value['Id'].'[price]" value="'.$value['ProductPrice'].'"><input type="hidden" name="plan_id_'.$value['Id'].'[name]" value="'.$value['ProductName'].'"><input type="hidden" name="plan_id_'.$value['Id'].'[description]" value="'.strip_tags($value['Description']).'"><input type="hidden" name="plan_id_'.$value['Id'].'[shortdescription]" value="'.strip_tags($value['ShortDescription']).'"><input type="hidden" name="plan_id_'.$value['Id'].'[sku]" value="'.$value['Sku'].'"><td><input type="checkbox" class="each_product_checkbox_import" name="wc_products_import[]" value="'.$appProductId.'" id="'.$appProductId.'"></td><td class="skucss">'.$appProductName.'</td><td class="skucss">'.$appProductSku.'</td><td>'.$appProductPrice.'</td><td>'.$wcProductSelectHtml.'</td></tr>';

            }

        }
        $importProductsData['importTableHtml'] = $importTableHtml;//Assign html....
    }
    return $importProductsData;//Return data....
}


//create the infusionsoft products dropdown for mapping..........
function createImportProductsSelect($existingwcProductResult,$iskp_product_id_compare=''){
    $wc_products_options_html = '';//Define variable...
    if(isset($existingwcProductResult) && !empty($existingwcProductResult)){//check application products...
        foreach($existingwcProductResult as $wcProductDetails) {
          $wcProductId = $wcProductDetails->ID;//get or set the product id....
          $wcProductName = $wcProductDetails->post_title;//get or set the product name....
          $wcProductSelected = "";
          if(!empty($iskp_product_id_compare)){//if relation exist...
              if($iskp_product_id_compare == $wcProductId){//then compare the relation between products....
                  $wcProductSelected = "selected";//set product selected....
              }else{
                  $wcProductSelected = "";
              }
          }
          //create the final html.....
          $wc_products_options_html.= '<option value="'.$wcProductId.'" '.$wcProductSelected.' data-id="'.$wcProductId.'">'.$wcProductName.'</option>';
        }
    }
    return $wc_products_options_html;//return html...
}

//Function is used to get the product id on the basis of meta key and meta value.....
function getProductId($key, $value) {
  global $wpdb;
  $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
  if (is_array($meta) && !empty($meta) && isset($meta[0])) {
    $meta = $meta[0];
  }   
  if (is_object($meta)) {
    return $meta->post_id;
  }
  else {
    return false;
  }
}

//Custom fields Tab :  Get all latest custom fields from infusionsoft/keap application related to orders/contacts..........
function getPredefindCustomfields(){
  //first need to check whether the application authentication is done or not..
  $applicationAuthenticationDetails = getAuthenticationDetails();
  //get the access token....
  $access_token = '';
  if(!empty($applicationAuthenticationDetails)){//check authentication details......
      if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
          $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
      }
  }
  
  $predefinedcfields = array();//define empty array.....
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':FirstName'] = "Contact First Name";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':MiddleName'] = "Contact Middle Name";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':LastName'] = "Contact Last Name";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Nickname'] = "Contact Nick Name";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':AssistantName'] = "Contact Assistant Name";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':AssistantPhone'] = "Contact Assistant Phone";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Email'] = "Contact Email Address 1";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':EmailAddress2'] = "Contact Email Address 2";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':EmailAddress3'] = "Contact Email Address 3";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Phone1'] = "Contact Phone 1";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Phone2'] = "Contact Phone 2";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Phone3'] = "Contact Phone 3";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Phone4'] = "Contact Phone 4";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Phone5'] = "Contact Phone 5";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Fax1'] = "Contact Fax 1";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Fax2'] = "Contact Fax 2";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Anniversary'] = "Contact Anniversary";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Birthday'] = "Contact Birthday";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':StreetAddress1'] = "Contact Billing Street 1";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':StreetAddress2'] = "Contact Billing Street 2";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Address2Street1'] = "Contact Shipping Street 1";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Address2Street2'] = "Contact Shipping Street 2";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':City'] = "Contact Billing City";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':City2'] = "Contact Shipping City";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':State'] = "Contact Billing State";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':State2'] = "Contact Shipping State";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Country'] = "Contact Billing Country";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Country2'] = "Contact Shipping Country";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':PostalCode'] = "Contact Billing Postal Code";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':PostalCode2'] = "Contact Shipping Postal Code";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':ZipFour1'] = "Contact Billing ZipFour";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':ZipFour2'] = "Contact Shipping ZipFour";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Suffix'] = "Contact Suffix";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':SpouseName'] = "Contact Spouse Name";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':ContactNotes'] = "Contact Notes";
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':Company'] = "Contact Company";
  //Infusionsoft/keap application access token check....
  if($access_token){
    //Infusionsoft/keap : Get Infusionsoft/Keap Contact Custom Fields
    $precontactcfields = contactOrderCustomFields($access_token,CUSTOM_FIELD_FORM_TYPE_CONTACT);
    
    //Infusionsoft/keap : Get Infusionsoft/Keap Order Custom Fields
    $preordercfields = contactOrderCustomFields($access_token,CUSTOM_FIELD_FORM_TYPE_ORDER);
    
    //create the options with label and name of custom field...
    if(isset($precontactcfields) && !empty($precontactcfields)){
      foreach($precontactcfields as $precustomfields) {
          $cfieldoptionValue = "FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':_'.$precustomfields["label"];
          $predefinedcfields["Contact Related Custom Fields"][$cfieldoptionValue] = $precustomfields["label"];
      }
    }
      
    if(isset($preordercfields) && !empty($preordercfields)){
      foreach($preordercfields as $precustomorderfields) {
          $cfieldorderoptionValue = "FormType:".CUSTOM_FIELD_FORM_TYPE_ORDER.':_'.$precustomorderfields["label"];
          $predefinedcfields["Order Related Custom Fields"][$cfieldorderoptionValue] = $precustomorderfields["label"];
      } 
    }
  }
  return $predefinedcfields;//return array.....
}

//Custom fields Tab : Code is used to get order/contact related custom fields from autherize application....
function contactOrderCustomFields($access_token,$fieldType){
    $customFieldsArray = array();//define empty array.....
    // Create instance of our wooconnection logger class to use off the whole things.
    $wooconnectionLogger = new WC_Logger();
    //check access token....
    if(!empty($access_token)){
      //check form type then set curl url on the basis of it.....
      if($fieldType == CUSTOM_FIELD_FORM_TYPE_CONTACT){
        $url = "https://api.infusionsoft.com/crm/rest/v1/contacts/model";
      }else if ($fieldType == CUSTOM_FIELD_FORM_TYPE_ORDER) {
        $url = "https://api.infusionsoft.com/crm/rest/v1/orders/model";
      }
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
          $errorMessage = "Get contact and order related custom fields is failed ";
          if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
            $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
          }
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }
        if(!empty($sucessData['custom_fields'])){
          $customFieldsArray = $sucessData['custom_fields'];
        }
        return $customFieldsArray;
      }
      
      curl_close($ch); 
    }
    return $customFieldsArray;//return array.....
}

//Custom fields Tab : Code is used to add order/contact related custom fields to autherize application....
function addCustomField($access_token,$formType,$fieldName,$fieldType,$fieldHeader){
  $fieldId = '';
  // Create instance of our wooconnection logger class to use off the whole things.
  $wooconnectionLogger = new WC_Logger();
  if(!empty($access_token) && !empty($formType) && !empty($fieldName)){
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
      if($formType == CUSTOM_FIELD_FORM_TYPE_CONTACT){
        $customFieldType = "Contact";
      }else if ($formType == CUSTOM_FIELD_FORM_TYPE_ORDER) {
        $customFieldType = "Job";
      }
      
      //Create xml to hit the curl request for add order item.....
      $xmlData = "<methodCall><methodName>DataService.addCustomField</methodName><params><param><value><string></string></value></param><param><value><string>".$customFieldType."</string></value></param><param><value><string>".$fieldName."</string></value></param><param><value><string>".$fieldType."</string></value></param><param><value><int>".$fieldHeader."</int></value></param></params></methodCall>";
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      //check if error occur due to any reason and then save the logs...
      if($err){
          $errorMessage = "Add custom field is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
      }else{
        //Covert/Decode response to xml.....
        $responsedata = xmlrpc_decode($response);
        //check if any error occur like invalid access token,then save logs....
        if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
            if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
                $errorMessage = "Add custom field is failed due to ". $responsedata['faultString']; 
                $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
            }
        }else{
          $fieldId = $responsedata;
        }
        return $fieldId;
      }
      curl_close($ch);
  }
  return $fieldId;
}

//Custom fields Tab : Code is used to get order/contact related custom fields tabs from autherize application....
function cfRelatedTabs($form_type_id=""){
  
  //first need to check whether the application authentication is done or not..
  $applicationAuthenticationDetails = getAuthenticationDetails();
  //get the access token....
  $access_token = '';
  if(!empty($applicationAuthenticationDetails)){//check authentication details......
      if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
          $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
      }
  }
  //Infusion soft connection check
  $tabRelatedOptions = '<option value="">Select Tab</option>';
  if(!empty($access_token)){
    if(!empty($form_type_id)){
      $form_type_id = $form_type_id;
    }else{
      $form_type_id = CUSTOM_FIELD_FORM_TYPE_CONTACT;
    }
    $relatedTabs = getTabs($access_token,$form_type_id);//call the function to get tabs
    if(isset($relatedTabs) && !empty($relatedTabs)){
        foreach ($relatedTabs as $key => $value) {
          $tabRelatedOptions.= '<option value="';
          $tabRelatedOptions.= $value['Id'];
          $tabRelatedOptions.= '">' . $value['TabName'];
          $tabRelatedOptions .= '</option>';
        }
    }
    
  }
  $tabRelatedOptions .= '</option>';
  
  return $tabRelatedOptions;//return html.....
}

//Custom fields Tab : Code is used to get order/contact related custom fields headers from autherize application....
function cfRelatedHeaders($tab_type_id=""){
    //first need to check whether the application authentication is done or not..
    $applicationAuthenticationDetails = getAuthenticationDetails();
    //get the access token....
    $access_token = '';
    if(!empty($applicationAuthenticationDetails)){//check authentication details......
        if(!empty($applicationAuthenticationDetails[0]->user_access_token)){//check access token....
            $access_token = $applicationAuthenticationDetails[0]->user_access_token;//assign access token....
        }
    }

    $tabRelatedHeaders='<option value="">Select Header</option>';
    if(!empty($access_token)){
      $relatedTabHeaders = getHeaders($access_token,$tab_type_id);
      if(isset($relatedTabHeaders) && !empty($relatedTabHeaders)){
        foreach ($relatedTabHeaders as $key => $value) {
          $tabRelatedHeaders.= '<option value="';
          $tabRelatedHeaders.= $value['Id'];
          $tabRelatedHeaders.= '">' . $value['Name'];
          $tabRelatedHeaders.= "</option>";
        }
      }
    }
    $tabRelatedHeaders .= '</option>';
    return $tabRelatedHeaders;//return html.....
}

//Custom fields Tab : Code is used to hit curl request to get the tabs....
function getTabs($access_token,$form_type_id){
  // Create instance of our wooconnection logger class to use off the whole things.
  $wooconnectionLogger = new WC_Logger();
  $tabsArray = '';
  $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $header = array(
    'Accept: text/xml',
    'Content-Type: text/xml',
    'Authorization: Bearer '. $access_token
  );
  
  //Create xml to hit the curl request for add order item.....
  $xmlData = "<methodCall><methodName>DataService.findByField</methodName><params><param><value><string></string></value></param><param><value><string>DataFormTab</string></value></param><param><value><int>200</int></value></param><param><value><int>0</int></value></param><param><value><string>FormId</string></value></param><param><value><string>".$form_type_id."</string></value></param><param><value><array><data><value><string>Id</string></value><value><string>TabName</string></value></data></array></value></param></params></methodCall>";
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
  $response = curl_exec($ch);
  $err = curl_error($ch);
  //check if error occur due to any reason and then save the logs...
  if($err){
      $errorMessage = "Get custom fields tab is failed due to ". $err; 
      $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
  }else{
    //Covert/Decode response to xml.....
    $responsedata = xmlrpc_decode($response);
    //check if any error occur like invalid access token,then save logs....
    if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
        if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
            $errorMessage = "Get custom fields tab is failed due to ". $responsedata['faultString']; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }
    }else{
      $tabsArray = $responsedata;
    }
    return $tabsArray;
  }
  curl_close($ch);
  return $tabsArray;
}

//Custom fields Tab : Code is used to hit curl request to get the headers....
function getHeaders($access_token,$tab_type_id){
  // Create instance of our wooconnection logger class to use off the whole things.
  $wooconnectionLogger = new WC_Logger();
  $headersArray = '';
  $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $header = array(
    'Accept: text/xml',
    'Content-Type: text/xml',
    'Authorization: Bearer '. $access_token
  );
  
  //Create xml to hit the curl request for add order item.....
  $xmlData = "<methodCall><methodName>DataService.findByField</methodName><params><param><value><string></string></value></param><param><value><string>DataFormGroup</string></value></param><param><value><int>200</int></value></param><param><value><int>0</int></value></param><param><value><string>TabId</string></value></param><param><value><string>".$tab_type_id."</string></value></param><param><value><array><data><value><string>Id</string></value><value><string>Name</string></value></data></array></value></param></params></methodCall>";
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
  $response = curl_exec($ch);
  $err = curl_error($ch);
  //check if error occur due to any reason and then save the logs...
  if($err){
      $errorMessage = "Get custom fields headers is failed due to ". $err; 
      $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
  }else{
    //Covert/Decode response to xml.....
    $responsedata = xmlrpc_decode($response);
    //check if any error occur like invalid access token,then save logs....
    if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
        if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
            $errorMessage = "Get custom fields headers is failed due to ". $responsedata['faultString']; 
            $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
        }
    }else{
      $headersArray = $responsedata;
    }
    return $headersArray;
  }
  curl_close($ch);
  return $headersArray;
}

//Checkout Custom fields : Code is used to check whether a input date is valid or not....  
function validateDatecField($dateValue, $dateFormat = 'm/d/Y'){
    $date = DateTime::createFromFormat($dateFormat, $dateValue);
    return $date && $date->format($dateFormat) === $dateValue;
}

//Checkout Custom fields : Code is used to update contact custom fields with contact id...  
function updateContactCustomFields($access_token,$contact_id,$customFieldsData){
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
    $customFieldHtml = '';
    foreach ($customFieldsData as $key => $value) {
        $keyname = str_replace(" ", "", $key);
        $customFieldHtml .= '<member><name>'.$keyname.'</name><value><string>'.$value.'</string></value></member>';
    }


    //Create xml to hit the curl request for add order item.....
    $xmlData = "<methodCall><methodName>ContactService.update</methodName><params><param><value><string>privateKey</string></value></param><param><value><int>".$contact_id."</int></value></param><param><value><struct>".$customFieldHtml."</struct></value></param></params></methodCall>";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    //check if error occur due to any reason and then save the logs...
    if($err){
        $errorMessage = "Update contact custom field values is failed due to ". $err; 
        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
    }else{
      //Covert/Decode response to xml.....
      $responsedata = xmlrpc_decode($response);
      //check if any error occur like invalid access token,then save logs....
      if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
          if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
              $errorMessage = "Update contact custom field values is failed due to ". $responsedata['faultString']; 
              $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
      }else{
        return true;
      }
    }
    curl_close($ch);
    return true;
}

//Checkout Custom fields : Code is used to update order custom fields with order id...
function updateOrderCustomFields($access_token,$job_id,$ordercFieldsData){
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
    $ordercFieldHtml = '';
    foreach ($ordercFieldsData as $key => $value) {
        $keyname = str_replace(" ", "", $key);
        $ordercFieldHtml .= '<member><name>'.$keyname.'</name><value><string>'.$value.'</string></value></member>';
    }


    //Create xml to hit the curl request for add order item.....
    $xmlData = "<methodCall><methodName>DataService.update</methodName><params><param><value></value></param><param><value><string>Job</string></value></param><param><value><int>".$job_id."</int></value></param><param><value><struct>".$ordercFieldHtml."</struct></value></param></params></methodCall>";
    //echo $xmlData;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    //check if error occur due to any reason and then save the logs...
    if($err){
        $errorMessage = "Update order custom field values is failed due to ". $err; 
        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
    }else{
      //Covert/Decode response to xml.....
      $responsedata = xmlrpc_decode($response);
      //check if any error occur like invalid access token,then save logs....
      if (is_array($responsedata) && xmlrpc_is_fault($responsedata)) {
          if(isset($responsedata['faultString']) && !empty($responsedata['faultString'])){
              $errorMessage = "Update order custom field values is failed due to ". $responsedata['faultString']; 
              $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', print_r($errorMessage, true));
          }
      }else{
        return true;
      }
    }
    curl_close($ch);
    return true;
}

//Main function is used to generate the standard checkout fields mapping html.....
function createStandardFieldsMappingHtml(){
  //Define variables.....
  $standard_fields_mapping_html = "";
  
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
  
  //call the function to get the list of standard checkout fields....
  $wooStandardCheckoutFields = listStandardCheckoutFields();

  //Get the export products table html and append to table
  if(empty($wooStandardCheckoutFields)){
    $standard_fields_mapping_html = '<p class="heading-text" style="text-align:center">No standard custom fields mapping exist.</p>';
  }else{
    $standard_fields_mapping_html .= '<span class="ajax_loader_standard_fields_related" style="display:none"><img src="'.WOOCONNECTION_PLUGIN_URL.'assets/images/loader.gif"></span><form action="" method="post" id="wc_standard_fields_mapping_form" onsubmit="return false"><table class="table table-striped standard_fields_listing_class" id="standard_fields_listing"><thead><tr><th>WooCommerce Standard Field</th><th>'.$applicationLabel.' Field</th></tr></thead><tbody>'.$wooStandardCheckoutFields.'</tbody></table></form>';  
  }
  
  //return the html...
  return $standard_fields_mapping_html;
}


//get the list of standard checkout fields and its mapped fields...
function listStandardCheckoutFields(){
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_standard_custom_field_mapping';
  $wooconnection_standard_custom_field_mapping = $table_prefix . "$table_name";
  $checkoutStandardFields = $wpdb->get_results("SELECT * FROM ".$wooconnection_standard_custom_field_mapping."");
  $wccheckoutStandardFieldsHtml = '';
  $fieldsDropDown = createMappedFieldSelect();
  if(isset($checkoutStandardFields) && !empty($checkoutStandardFields)){
    foreach ($checkoutStandardFields as $key => $value) {
        $field_id = $value->id;
        $field_name = $value->wc_standardcf_label;
        $field_mapping = $value->wc_standardcf_mapped;
        $mapped_field_type = $value->wc_standardcf_mapped_field_type; 
        $mappedFieldName = 'FormType:'.$mapped_field_type.':'.$field_mapping;
        $wccheckoutStandardFieldsHtml.='<tr class="standardcfrows" id="'.$field_id.'" data-id="'.$mappedFieldName.'"><td>'.$field_name.'</td><td><select name="standard_cfield_mapping_'.$field_id.'" id="standard_cfield_mapping_'.$field_id.'" data-id="'.$field_id.'" class="standardcfieldmappingwith"><option value="donotmap">Do not mapped</option>'.$fieldsDropDown.'</select></td></tr>';
    }
  }
  return $wccheckoutStandardFieldsHtml;
}

//create infusionsoft/keap fields options html on the basis of mapping...
function createMappedFieldSelect(){
  //get the array of application custom fields.....
  $preDefinedCustomFields = getPredefindCustomfields();
  $cfieldOptionsHtml = '';
  foreach($preDefinedCustomFields as $key => $value) {
    $cfieldOptionsHtml .= "<optgroup label=\"$key\">";
    foreach($value as $key1 => $value1) {
      $cfieldoptionSelected = "";
      $cfieldOptionsHtml .= '<option value="'.$key1.'"'.$cfieldoptionSelected.'>'.$value1.'</option>';
    }
    $cfieldOptionsHtml .= "</optgroup>";
  }
  return $cfieldOptionsHtml;
}


//get the list of standard checkout fields and its mapped fields...
function listAlreadyUsedFields(){
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_standard_custom_field_mapping';
  $wooconnection_standard_custom_field_mapping = $table_prefix . "$table_name";
  $checkoutStandardFields = $wpdb->get_results("SELECT * FROM ".$wooconnection_standard_custom_field_mapping."");
  $wccheckoutStandardFieldsHtml = array();
  if(isset($checkoutStandardFields) && !empty($checkoutStandardFields)){
    foreach ($checkoutStandardFields as $key => $value) {
        $field_id = $value->id;
        $field_mapping = $value->wc_standardcf_mapped;
        $mapped_field_type = $value->wc_standardcf_mapped_field_type; 
        $wccheckoutStandardFieldsHtml[] = 'FormType:'.$mapped_field_type.':'.$field_mapping;
    }
  }
  return $wccheckoutStandardFieldsHtml;
}


//Below function is used to get the lead source id....
function checkAddLeadSource($access_token,$category,$meduim,$vendor,$content=''){
  $leadsourceId = '';//define empty variables....
  if(!empty($category) && !empty($access_token)){
    //call the function to get the category id....
    $categoryId = checkAddCategory($access_token,$category);
    if(!empty($categoryId)){
      $leadsourceId = getAddLeadSource($access_token,$categoryId,$meduim,$vendor,$content);
    }
  }
  return $leadsourceId;//return lead source id....
}

//With below function first check "lead source category" exist by name....
function checkAddCategory($access_token,$categoryname){
  $categoryId = '';//define empty variables....
  if(!empty($categoryname) && !empty($access_token)){
        $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = array(
          'Accept: text/xml',
          'Content-Type: text/xml',
          'Authorization: Bearer '. $access_token
        );
        //Create xml to hit the curl request for get category id by category name.....
        $xmlData = "<methodCall><methodName>DataService.findByField</methodName><params><param>
                        <value></value></param><param><value><string>LeadSourceCategory</string></value></param><param><value><int>100</int></value></param><param><value><int>0</int></value></param><param><value><string>Name</string></value></param><param><value><string>".$categoryname."</string></value></param><param><value><array><data><value><string>Id</string></value></data></array></value></param></params></methodCall>";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        if($err){
        }else{
          $responsedata = xmlrpc_decode($response);
          if(isset($responsedata) && !empty($responsedata)){
              if(!empty($responsedata[0]['Id'])){
                  $categoryId = $responsedata[0]['Id'];
              } 
          }
          //check if category exist..
          if(!empty($categoryId)){
            return $categoryId;
          }else{//else call the "addNewLsCategory" to add new category.....
            $categoryId = addNewLsCategory($access_token,$categoryname);
            return $categoryId;
          } 
        }
        curl_close($ch);  
  }
  return $categoryId;//return lead source category id....
}

//Below function is add new lead source category.....
function addNewLsCategory($access_token,$category){
  $newCatId = '';//define empty variables....
  if(!empty($category) && !empty($access_token)){
      $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $header = array(
        'Accept: text/xml',
        'Content-Type: text/xml',
        'Authorization: Bearer '. $access_token
      );
      //Create xml to hit the curl request to add new lead source category.....
      $xmlData = "<methodCall><methodName>DataService.add</methodName><params><param><value></value></param><param><value><string>LeadSourceCategory</string></value></param><param><value><struct><member><name>Name</name><value><string>".$category."</string></value></member></struct></value></param></params></methodCall>";
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      if($err){
      }else{
        $responsedata = xmlrpc_decode($response);
        if(!empty($responsedata) && is_int($responsedata)){
          $newCatId = $responsedata;
        }
        return $newCatId;  
      }
      curl_close($ch); 
  } 
  return $newCatId;//return lead source category id....
}

//get the lead source id on the basis of utm parameters....
function getAddLeadSource($access_token,$categoryId,$meduim,$vendor,$content=''){
  $leadId = '';//define empty variables....
  if(!empty($access_token) && !empty($categoryId) && !empty($meduim) && !empty($vendor)){
      $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $header = array(
        'Accept: text/xml',
        'Content-Type: text/xml',
        'Authorization: Bearer '. $access_token
      );
      //Create xml to hit the curl request to get lead source id.....
      $xmlData = "<methodCall><methodName>DataService.query</methodName><params><param><value></value></param><param><value><string>LeadSource</string></value></param><param><value><int>1</int></value></param><param><value><int>0</int></value></param><param><value><struct><member><name>LeadSourceCategoryId</name><value><string>".$categoryId."</string></value></member><member><name>Medium</name><value><string>".$meduim."</string></value></member><member><name>Message</name><value><string>".$content."</string></value></member><member><name>Vendor</name><value><string>".$vendor."</string></value></member></struct></value></param><param><value><array><data><value><string>Id</string></value></data></array></value></param></params></methodCall>";
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      if($err){
      }else{
        //Covert/Decode response to xml.....
        $responsedata = xmlrpc_decode($response);
        if(isset($responsedata) && !empty($responsedata)){
            if(!empty($responsedata[0]['Id'])){
                $leadId = $responsedata[0]['Id'];
            } 
        }
        //check if lead source exist....
        if(!empty($leadId)){
          return $leadId;
        }else{//else call the function "addNewLs" to add new lead source....
          $leadId = addNewLs($access_token,$categoryId,$meduim,$vendor,$content);
          return $leadId;
        } 
      }
      curl_close($ch);  
  }
  return $leadId;//return lead source id....
}

//Below function is add new lead source .....
function addNewLs($access_token,$categoryId,$meduim,$vendor,$content=''){
  $newLsId = '';//define empty variables....
  if( !empty($access_token) && !empty($categoryId) && !empty($meduim) && !empty($vendor)){
      $url = 'https://api.infusionsoft.com/crm/xmlrpc/v1';
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $header = array(
        'Accept: text/xml',
        'Content-Type: text/xml',
        'Authorization: Bearer '. $access_token
      );
      //Create xml to hit the curl request for add new lead source....
      $xmlData = "<methodCall><methodName>DataService.add</methodName><params><param>
                        <value></value></param><param><value><string>LeadSource</string></value></param><param><value><struct><member><name>LeadSourceCategoryId</name><value><string>".$categoryId."</string></value></member><member><name>Medium</name><value><string>".$meduim."</string></value></member><member><name>Vendor</name><value><string>".$vendor."</string></value></member>
                          <member><name>Name</name><value><string>".$vendor."</string></value></member><member><name>Message</name><value><string>".$content."</string></value></member><member><name>Status</name><value><string>Active</string></value></member></struct></value></param></params></methodCall>";
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
      $response = curl_exec($ch);
      $err = curl_error($ch);
      if($err){
      }else{
        //Covert/Decode response to xml.....
        $responsedata = xmlrpc_decode($response);
        if(!empty($responsedata) && is_int($responsedata)){
          $newLsId = $responsedata;
        }
        return $newLsId;  
      }
      curl_close($ch); 
  }
  return $newLsId;//return newly created lead source id....
}

//Dynamic Thankyou Override : Function is used to get the list all publish wordpress posts for default thankyou override....
function get_wp_posts(){
    $wp_posts_options_html = '';//Define the empty variable.....
    $wpPostsListing = get_posts(array('post_type'=> 'post','orderby' => 'ID','post_status' => 'publish','order' => 'DESC','posts_per_page' => -1));
    //check array is not empty then execute a loop to create the list of options....
    if(!empty($wpPostsListing)){
      foreach ($wpPostsListing as $key => $value) {
          $wpPostName = $value->post_title;
          $wp_posts_options_html.= '<option value="'.$value->ID.'">'.$wpPostName.'</option>';
      }
    }else{
      $wp_posts_options_html = '<option>No Wordpress Posts Exist!</option>';
    }
    return $wp_posts_options_html;//return html....
}

//Dynamic Thankyou Override : Function is used to get the list all publish wordpress pages for default thankyou override....
function get_wp_pages(){
    $wp_page_options_html = '';//Define the empty variable.....
    $wpPagesListing = get_posts(array('post_type'=> 'page','orderby' => 'ID','post_status' => 'publish','order' => 'DESC','posts_per_page' => -1));
    //check array is not empty then execute a loop to create the list of options....
    if(!empty($wpPagesListing)){
      foreach ($wpPagesListing as $key => $value) {
          $wpPageName = $value->post_title;
          $wp_page_options_html.= '<option value="'.$value->ID.'">'.$wpPageName.'</option>';
      }
    }else{
      $wp_page_options_html = '<option>No Wordpress Pages Exist!</option>';
    }
    return $wp_page_options_html;//return html....
}

//Dynamic Thankyou Override : Function is used to get the list all publish wordpress products for product thankyou override....
function get_products_options(){
  $productLisingWithOptions = "";//Define the empty variable.....
  $products_listing = get_posts(array('post_type' => 'product','post_status'=>'publish','orderby' => 'post_date','order' => 'DESC','posts_per_page'   => 999999));
  //check array is not empty then execute a loop to create the list of options....
  if(isset($products_listing) && !empty($products_listing))
  {
      foreach ($products_listing as $key => $value)
      {
        $productLisingWithOptions.= '<option value="'.$value->ID.'">'.$value->post_title.'</option>';
      }
  }else{
    $productLisingWithOptions.= '<option value="">No Products Exist!</option>';
  }
  return $productLisingWithOptions;//return html....
}

//Dynamic Thankyou Override : Function is used to get the list all publish wordpress products for product category thankyou override....
function get_category_options(){
  $categoriesLisingWithOptions = "";//Define the empty variable.....
  $category_args = array('orderby' => 'name','order' => 'asc','hide_empty' => false,);
  $product_categories = get_terms( 'product_cat', $category_args );
  //check array is not empty then execute a loop to create the list of options....
  if(isset($product_categories) && !empty($product_categories)){
    foreach ($product_categories as $key => $value) {
      $categoriesLisingWithOptions.= '<option value="'.$value->term_id.'">'.$value->name.'</option>';
    }
  }else{
    $categoriesLisingWithOptions.= '<option value="">No Categories Exist</option>';
  }
  return $categoriesLisingWithOptions;//return html....
}


//Dynamic Thankyou Override : Function is used to get the list all active product thankyou overrides....
function loading_product_thanks_overrides(){
  global $wpdb,$table_prefix;
  //override main table...
  $override_table_name = 'wooconnection_thankyou_overrides';
  $wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
  $thankyouOverrides = $wpdb->get_results("SELECT * FROM ".$wp_thankyou_override_table_name." WHERE wc_override_status=".STATUS_ACTIVE." and wc_override_redirect_condition = ".REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS." ORDER BY wc_override_sort_order ASC");
  $thankyouOverridesListing = "";//Define the empty variable.....
  //check array is not empty then execute a loop to create the list of product thankyou overrides....
  if(isset($thankyouOverrides) && !empty($thankyouOverrides)){
    $thankyouOverridesListing = '<ul class="group-fields override_product_rule">';
    foreach ($thankyouOverrides as $key => $value) {
        if(!empty($value->id)){
          $thankyouOverridesListing .=  '<li class="group-field" id="'.$value->id.'"><span class="wc_thankyou_override_name override_name_inner">'.$value->wc_override_name.'<span class="listing-operators"><i class="fa fa-pencil edit_product_rule_override" title="Edit thankyou override" data-id="'.$value->id.'"></i><i class="fa fa-times delete_current_override_product" title="Delete thankyou override" data-type="'.REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS.'" data-id="'.$value->id.'"></i></span></span></li>';
        }
    }
    $thankyouOverridesListing .= '</ul>';
  }else{
    $thankyouOverridesListing = "<p class='no_override_exist'>We don't have any product based overrides</p>";
  }
  return $thankyouOverridesListing;//return html....
}

//Dynamic Thankyou Override : Function is used to get the list all active product category thankyou overrides....
function loading_product_cat_thanks_overrides(){
  global $wpdb,$table_prefix;
  //override main table...
  $override_table_name = 'wooconnection_thankyou_overrides';
  $wp_thankyou_override_table_name = $table_prefix . "$override_table_name";
  $thankyouOverrides = $wpdb->get_results("SELECT * FROM ".$wp_thankyou_override_table_name." WHERE wc_override_status=".STATUS_ACTIVE." and wc_override_redirect_condition = ".REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES." ORDER BY wc_override_sort_order ASC");
  $thankyouOverridesListing = "";//Define the empty variable.....
  //check array is not empty then execute a loop to create the list of product category thankyou overrides....
  if(isset($thankyouOverrides) && !empty($thankyouOverrides)){
    $thankyouOverridesListing = '<ul class="group-fields override_product_category_rule">';
    foreach ($thankyouOverrides as $key => $value) {
        if(!empty($value->id)){
          $thankyouOverridesListing .=  '<li class="group-field" id="'.$value->id.'"><span class="wc_thankyou_override_name override_name_inner">'.$value->wc_override_name.'<span class="listing-operators"><i class="fa fa-pencil edit_product_category_rule_override" title="Edit thankyou override" data-id="'.$value->id.'"></i><i class="fa fa-times delete_current_override_product" title="Delete thankyou override" data-type="'.REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES.'" data-id="'.$value->id.'"></i></span></span></li>';
        }
    }
    $thankyouOverridesListing .= '</ul>';
  }else{
    $thankyouOverridesListing = "<p class='no_override_exist'>We don't have any product category based overrides</p>";
  }
  return $thankyouOverridesListing;//return html....
}

//Dynamic Thankyou Override : Function is used to get the list of override related products....
function get_override_related_products($overrideid){
    global $table_prefix, $wpdb;
    //override products table name....
    $override_product_table_name = 'wooconnection_thankyou_override_related_products';
    $wp_thankyou_override_related_products = $table_prefix . "$override_product_table_name";
    $thankyouOverrideProductsArray = array();//Define the empty array.....
    //check the override exist is not empty if exist then execute a query to get the list of products.....
    if(!empty($overrideid)){
      $thankyouOverrideProducts = $wpdb->get_results("SELECT * FROM ".$wp_thankyou_override_related_products." WHERE override_id=".$overrideid." and wc_override_product_status =".STATUS_ACTIVE);
      //if related products array is not empty then excuate a loop......
      if(isset($thankyouOverrideProducts) && !empty($thankyouOverrideProducts)){
        foreach ($thankyouOverrideProducts as $key => $value) {
          if(!empty($value->override_product_id)){
            $thankyouOverrideProductsArray[] = $value->override_product_id;
          }
        }
      }
    }
    return $thankyouOverrideProductsArray;//return products array....
}

//Dynamic Thankyou Override : Function is used to get the list of override related categories....
function get_override_related_cat($overrideid){
    global $table_prefix, $wpdb;
    //override cat table name....
    $override_cat_table_name = 'wooconnection_thankyou_override_related_categories';
    $wp_thankyou_override_related_categories = $table_prefix . "$override_cat_table_name";
    $thankyouOverrideCatArray = array();//Define the empty array.....
    //check the override exist is not empty if exist then execute a query to get the list of products.....
    if(!empty($overrideid)){
      $thankyouOverrideCat = $wpdb->get_results("SELECT * FROM ".$wp_thankyou_override_related_categories." WHERE override_id=".$overrideid." and wc_override_cat_status =".STATUS_ACTIVE);
      //if related products array is not empty then excuate a loop...... 
      if(isset($thankyouOverrideCat) && !empty($thankyouOverrideCat)){
        foreach ($thankyouOverrideCat as $key => $value) {
          if(!empty($value->override_cat_id)){
            $thankyouOverrideCatArray[] = $value->override_cat_id;
          }
        }
      }
    }
    return $thankyouOverrideCatArray;//return products array....
}

//get the list of product purchase triggers...
function getCartTriggers(){
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_campaign_goals';
  $wp_table_name = $table_prefix . "$table_name";
  $trigger_type = WOOCONNECTION_TRIGGER_TYPE_CART;
  $campaignGoalDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE wc_trigger_type=".$trigger_type." ORDER BY id ASC");
  $wcGeneralTriggers = '';
  if(isset($campaignGoalDetails) && !empty($campaignGoalDetails)){
    foreach ($campaignGoalDetails as $key => $value) {
        $trigger_id = $value->id;
        $trigger_goal_name = $value->wc_goal_name;
        $trigger_integration_name = $value->wc_integration_name;
        $trigger_call_name = $value->wc_call_name;
        if($trigger_goal_name == 'Item Added to Cart'){
            $call_name = explode('added', $trigger_call_name);
            $length = 35;
            $callName = 'added'.'<a href="javascript:void(0);" onclick="showProductsListing('.$length.')">'.$call_name[1].'</a>';
            $class = 'readonly';
        }
        else if($trigger_goal_name == 'Review Left'){
            $call_name = explode('review', $trigger_call_name);
            $length = 34;
            $callName = 'review'.'<a href="javascript:void(0);" onclick="showProductsListing('.$length.')">'.$call_name[1].'</a>';
            $class = 'readonly';
        }
        else{
            $callName = strtolower($trigger_call_name);
            $class = '';
        }
        $wcGeneralTriggers.='<tr class="'.$class.'" id="trigger_tr_'.$trigger_id.'">
                                <td>'.$trigger_goal_name.'</td>
                                <td id="trigger_integration_name_'.$trigger_id.'">'.strtolower($trigger_integration_name).'</td>
                                <td id="trigger_call_name_'.$trigger_id.'">'.$callName.'</td>
                                <td><i class="fa fa-edit" aria-hidden="true" style="cursor:pointer;" onclick="popupEditDetails('.$trigger_id.');"></i>
                                </td>
                              </tr>';
    }
  }else{
    $wcGeneralTriggers = '<tr><td colspan="4" style="text-align: center; vertical-align: middle;">No Cart Triggers Exist</td></tr>';
  }
  return $wcGeneralTriggers;
}

//get or set the user email first check user is loged in if not then get email from woocommerce session email...
function get_set_user_email(){
    $useremail = "";
    if(is_user_logged_in()) {
      $currentLoginUser = wp_get_current_user();
      if(!empty($currentLoginUser->user_email)){
        $useremail = $currentLoginUser->user_email;
      }else{
        $useremail = get_user_meta($currentLoginUser->ID, 'billing_email', true);
      }
    }
    return $useremail;
}

//get the list of order triggers...
function getOrderTriggers(){
  global $wpdb,$table_prefix;
  $table_name = 'wooconnection_campaign_goals';
  $wp_table_name = $table_prefix . "$table_name";
  $trigger_type = WOOCONNECTION_TRIGGER_TYPE_ORDER;
  $campaignGoalDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE wc_trigger_type=".$trigger_type." ORDER BY id ASC");
  $wcGeneralTriggers = '';
  if(isset($campaignGoalDetails) && !empty($campaignGoalDetails)){
    foreach ($campaignGoalDetails as $key => $value) {
        $trigger_id = $value->id;
        $trigger_goal_name = $value->wc_goal_name;
        $trigger_integration_name = $value->wc_integration_name;
        $trigger_call_name = $value->wc_call_name;
        if($trigger_goal_name == 'Specific Product'){
            $length = 40;
            $callName = '<a href="javascript:void(0);" onclick="showProductsListing('.$length.')">'.$trigger_call_name.'</a>';
            $class = 'readonly';
        }
        else if($trigger_goal_name == 'Coupon Code Applied'){
            $call_name = explode('coupon', $trigger_call_name);
            $callName = 'coupon'.'<a href="javascript:void(0);" data-toggle="modal" data-target="#couponsListing">'.$call_name[1].'</a>';
            $class = 'readonly';
        }
        else if($trigger_goal_name == 'Referral Partner Order'){
            $call_name = explode('refferal', $trigger_call_name);
            $callName = 'refferal'.'<a href="javascript:void(0);" data-toggle="modal" data-target="#refferalListing">'.$call_name[1].'</a>';
            $class = 'readonly';
        }
        else{
            $callName = strtolower($trigger_call_name);
            $class = '';
        }
        $wcGeneralTriggers.='<tr class="'.$class.'" id="trigger_tr_'.$trigger_id.'">
                                <td>'.$trigger_goal_name.'</td>
                                <td id="trigger_integration_name_'.$trigger_id.'">'.strtolower($trigger_integration_name).'</td>
                                <td id="trigger_call_name_'.$trigger_id.'">'.$callName.'</td>
                                <td><i class="fa fa-edit" aria-hidden="true" style="cursor:pointer;" onclick="popupEditDetails('.$trigger_id.');"></i>
                                </td>
                              </tr>';
    }
  }else{
    $wcGeneralTriggers = '<tr><td colspan="4" style="text-align: center; vertical-align: middle;">No Order Triggers Exist</td></tr>';
  }
  return $wcGeneralTriggers;
}

//get the list of products with sku...
function get_products_listing($length,$limit='',$offset='',$htmlType = ''){
  //set default limit and offset....
  $listingLimit = 20;
  $listingOffset = 0;
  //check if limit exist in function parameter...
  if(!empty($limit)){
    $listingLimit = $limit;
  }
  //check if offset exist in function parameter....
  if(!empty($offset)){
    $listingOffset = $offset;
  }
  $productLisingWithSku = "";
  $woo_products_listing = get_posts(array('post_type' => 'product','post_status'=>'publish','orderby' => 'post_date','order' => 'DESC','posts_per_page'   => $listingLimit,'offset'=>$listingOffset));
  if(isset($woo_products_listing) && !empty($woo_products_listing)){
    foreach ($woo_products_listing as $key => $value)
    {
        $currentProductSku = get_set_product_sku($value->ID,$length);
        $productLisingWithSku .= '<tr><td  class="skucss">'.$value->post_title.'</td><td id="product_'.$value->ID.'_sku"  class="skucss">'.$currentProductSku.'</td><td><i class="fa fa-copy" style="cursor:pointer" 
                                      onclick="copyContent(\'product_'.$value->ID.'_sku\')">
                                      </i>
                                  </td>
                              </tr>';
    }
  }else{
    //if html type is empty then return table with message...else return empty....
    if(empty($htmlType)){
      $productLisingWithSku .= '<tr><td colspan="3" style="text-align: center; vertical-align: middle;">No Products Exist!</td></tr>';
    }
  }
  return $productLisingWithSku;
}


//get the list of coupons with coupon code...
function get_coupons_listing($couponListingLimit='',$couponListingOffset='',$couponListingType=''){
  //set default limit and offset.....
  $couponsListingLimit = 20;
  $couponsListingOffset = 0;
  //check if coupon limit exist in function parameter.....
  if(!empty($couponListingLimit)){
    $couponsListingLimit = $couponListingLimit;
  }
  //check if offset exist in function parameter.......
  if(!empty($couponListingOffset)){
    $couponsListingOffset = $couponListingOffset;
  }
  $couponsLisingWithCode = "";
  $woo_coupons_listing = get_posts(array('post_type' => 'shop_coupon','post_status'=>'publish','orderby' => 'post_date','order' => 'DESC','posts_per_page'=>$couponsListingLimit,'offset'=>$couponsListingOffset));
  if(isset($woo_coupons_listing) && !empty($woo_coupons_listing)){
    foreach ($woo_coupons_listing as $key => $value)
    {
        if(isset($value->post_excerpt) && !empty($value->post_excerpt)){
            $couponDescriptionLength = strlen($value->post_excerpt);
            if($couponDescriptionLength > 60){
                $coupondesc_default = substr($value->post_excerpt, 0, 60);
                $couponDescription = $coupondesc_default.'....';    
            }else{
                $couponDescription = $value->post_excerpt;
            }
            
        }else{
          $couponDescription = "--";
        }
        $couponsLisingWithCode.='<tr><td id="coupon_'.$value->ID.'_code" class="skucss">'.substr($value->post_name, 0, 34).'</td><td class="skucss">'.$couponDescription.'</td><td><i class="fa fa-copy" onclick = "copyContent(\'coupon_'.$value->ID.'_code\')" style="cursor:pointer"></i></td></tr>';
    }
  }else{
    if(empty($couponListingType)){
      $couponsLisingWithCode .= '<tr><td colspan="3" style="text-align: center; vertical-align: middle;">No Coupons Exist!</td></tr>';
    }
  }
  return $couponsLisingWithCode;
}

//get or set the product sku on the basis of product id and set the length of sku on the basis of lenght set in parameter..
function get_set_product_sku($productId,$length=''){
    $productSku = "";
    if(isset($productId) && !empty($productId)){
      $productSku = get_post_meta($productId, '_sku', true);
      
      //check product sku if exist then ok else create productSku from post name...
      if(empty($productSku)){
        $currentPostData = get_post($productId);
        if(isset($currentPostData->post_name) && !empty($currentPostData->post_name)){
           $productSku =  $currentPostData->post_name;
        }
      }else{
        $productSku = $productSku;  
      }

      //if "-" is exist in product sku then replace with empty
      if (strpos($productSku, '-') !== false)
      {
          $productSku=str_replace("-", "", $productSku);
      }
      else if (strpos($productSku, '_') !== false)
      {
          $productSku=str_replace("_", "", $productSku);
      }
      else
      {
          $productSku=$productSku;
      }
      //convert string to lowercase
      $productSku=strtolower($productSku);  
      $productSku = substr($productSku, 0, $length);
    }
    return $productSku;
}

//Function is used to apply the any purchase trigger......
function orderTriggerAnyPurchase($orderContactId,$access_token,$wooconnectionLogger){
    if(!empty($orderContactId)){
        //Concate a error message to store the logs...
        $callback_purpose = 'Wooconnection Any Purchase : Process of any purchase success order trigger';
        // //Woocommerce Order trigger : Get the call name and integration name of goal "Any Purchase"... 
        $purchaseProductTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_ORDER,'Any Purchase');

        //Define variables....
        $purchaseProductIntegrationName = '';
        $purchaseProductCallName = '';

        //Check campaign goal details...
        if(isset($purchaseProductTrigger) && !empty($purchaseProductTrigger)){
            
            //Get and set the wooconnection goal integration name
            if(isset($purchaseProductTrigger[0]->wc_integration_name) && !empty($purchaseProductTrigger[0]->wc_integration_name)){
                $purchaseProductIntegrationName = $purchaseProductTrigger[0]->wc_integration_name;
            }

            //Get and set the wooconnection goal call name
            if(isset($purchaseProductTrigger[0]->wc_call_name) && !empty($purchaseProductTrigger[0]->wc_call_name)){
                $purchaseProductCallName = $purchaseProductTrigger[0]->wc_call_name;
            }    
        }

        // Check wooconnection integration name and call name of goal is exist or not if exist then hit the achieveGoal.
        if(!empty($purchaseProductIntegrationName) && !empty($purchaseProductCallName))
        {
            $orderAnyPurchaseTriggerResponse = achieveTriggerGoal($access_token,$purchaseProductIntegrationName,$purchaseProductCallName,$orderContactId,$callback_purpose);
            if(!empty($orderAnyPurchaseTriggerResponse)){
                if(empty($orderAnyPurchaseTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($orderAnyPurchaseTriggerResponse[0]['message']) && !empty($orderAnyPurchaseTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Any Purchase : Process of any purchase success order trigger is failed where contact id is '.$orderContactId.' because '.$orderAnyPurchaseTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Any Purchase : Process of any purchase success order trigger is failed where contact id is '.$orderContactId.'');
                    }
                    
                }
            }    
        }
    }
    return true;
}

//Function is used to apply the specific product purchase trigger......
function orderTriggerSpecificPurchase($productSku,$orderContactId,$access_token,$wooconnectionLogger){
    if(!empty($orderContactId) && !empty($productSku)){
        //Concate a error message to store the logs...
        $callback_purpose = 'Wooconnection Specific Product Purchase : Process of specific product purchase trigger';
        // //Woocommerce Order trigger : Get the call name and integration name of goal "Specific Product"... 
        $specificPurchaseProductTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_ORDER,'Specific Product');

        //Define variables....
        $specificPurchaseProductIntegrationName = '';
        $specificPurchaseProductCallName = $productSku;

        //Check campaign goal details...
        if(isset($specificPurchaseProductTrigger) && !empty($specificPurchaseProductTrigger)){
            
            //Get and set the wooconnection goal integration name
            if(isset($specificPurchaseProductTrigger[0]->wc_integration_name) && !empty($specificPurchaseProductTrigger[0]->wc_integration_name)){
                $specificPurchaseProductIntegrationName = $specificPurchaseProductTrigger[0]->wc_integration_name;
            }
        }

        // Check wooconnection integration name and call name of goal is exist or not if exist then hit the achieveGoal.
        if(!empty($specificPurchaseProductIntegrationName) && !empty($specificPurchaseProductCallName))
        {
            $orderSpecificPurchaseTriggerResponse = achieveTriggerGoal($access_token,$specificPurchaseProductIntegrationName,$specificPurchaseProductCallName,$orderContactId,$callback_purpose);
            if(!empty($orderSpecificPurchaseTriggerResponse)){
                if(empty($orderSpecificPurchaseTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($orderSpecificPurchaseTriggerResponse[0]['message']) && !empty($orderSpecificPurchaseTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Specific Product Purchase : Process of specific product purchase trigger is failed where contact id is '.$orderContactId.' because '.$orderSpecificPurchaseTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Specific Product Purchase : Process of specific product purchase trigger is failed where contact id is '.$orderContactId.'');
                    }
                    
                }
            }    
        }
    }
    return true;
}


//Function is used to apply the specific product purchase trigger......
function orderTriggerCouponApply($couponName,$orderContactId,$access_token,$wooconnectionLogger){
    if(!empty($orderContactId) && !empty($couponName)){
        //Concate a error message to store the logs...
        $callback_purpose = 'Wooconnection Coupon Code Applied : Process of coupon code applied trigger';
        // //Woocommerce Order trigger : Get the call name and integration name of goal "Coupon Code Applied"... 
        $couponCodeTrigger = get_campaign_goal_details(WOOCONNECTION_TRIGGER_TYPE_ORDER,'Coupon Code Applied');

        //Define variables....
        $couponCodeIntegrationName = '';
        $couponCodeCallName = $couponName;

        //Check campaign goal details...
        if(isset($couponCodeTrigger) && !empty($couponCodeTrigger)){
            
            //Get and set the wooconnection goal integration name
            if(isset($couponCodeTrigger[0]->wc_integration_name) && !empty($couponCodeTrigger[0]->wc_integration_name)){
                $couponCodeIntegrationName = $couponCodeTrigger[0]->wc_integration_name;
            }
        }

        // Check wooconnection integration name and call name of goal is exist or not if exist then hit the achieveGoal.
        if(!empty($couponCodeIntegrationName) && !empty($couponCodeCallName))
        {
            $couponCodeTriggerResponse = achieveTriggerGoal($access_token,$couponCodeIntegrationName,$couponCodeCallName,$orderContactId,$callback_purpose);
            if(!empty($couponCodeTriggerResponse)){
                if(empty($couponCodeTriggerResponse[0]['success'])){
                    //Campign goal is not exist in infusionsoft/keap application then store the logs..
                    if(isset($couponCodeTriggerResponse[0]['message']) && !empty($couponCodeTriggerResponse[0]['message'])){
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Coupon Code Applied : Process of coupon code applied trigger is failed where contact id is '.$orderContactId.' because '.$couponCodeTriggerResponse[0]['message'].'');    
                    }else{
                        $wooconnection_logs_entry = $wooconnectionLogger->add('infusionsoft', 'Wooconnection Coupon Code Applied : Process of coupon code applied trigger is failed where contact id is '.$orderContactId.'');
                    }
                    
                }
            }    
        }
    }
    return true;
}

//Get the infusionsoft/keap application order deatils on the basis of order id....
function getRefferalPartnersListing(){
  $data = array();
  //first need to check connection is created or not infusionsoft/keap application then next process need to done..
  $applicationAuthenticationDetails = getAuthenticationDetails();
  //get the access token....
  $access_token = '';
  if(!empty($applicationAuthenticationDetails)){
    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
    }
  }
  if(!empty($access_token)){
        // Create instance of our wooconnection logger class to use off the whole things.
        $wooconnectionLogger = new WC_Logger();
        $url = 'https://api.infusionsoft.com/crm/rest/v1/affiliates';
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
          return $sucessData['affiliates'];
        }
        curl_close($ch);
  }
  return $data;
}

//Function is used to get the list of refferal partners....
function affiliateListing(){
  $arrayData = getRefferalPartnersListing();  
  $listing = '';
  if(isset($arrayData) && !empty($arrayData)){
    foreach ($arrayData as $key => $value) {
      $listing .= '<tr>
                  <td id="refferal_'.$value['id'].'_code">'.$value['id'].'</td>
                  <td>'.$value['name'].'</td>
                  <td>'.$value['code'].'</td>
                  <td><i class="fa fa-copy" onclick = "copyContent(\'refferal_'.$value['id'].'_code\')" style="cursor:pointer"></i></td>
                  </tr>';
    }
  }else{
    $listing = '<tr><td colspan="4" style="text-align: center; vertical-align: middle;">No Affiliates Exist!</td></tr>';
  }
  return $listing;
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

//Check product with same sku is exist or not  in woocommerce products, if exist then return match woocommerce products id.....
function checkWcProductExistSku($appsku,$wcproductsArray){
  $wcMatchProductsIds = array();//Define array...
  if(!empty($wcproductsArray)){//check is products array is not empty....
      //Execute loop on application prdoucts array,......
      foreach ($wcproductsArray as $key => $value) {
        if(!empty($value->ID)){//check product id....
            $product_id = $value->ID;
            $product = wc_get_product( $product_id );
            $productSku = $product->get_sku();
            //compare sku, if match the return the ids..
            if(isset($productSku) && !empty($productSku)){
                if($productSku == $appsku){
                  $wcMatchProductsIds[] = $product_id;
                }    
            }
        }
      }   
  }
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
  if(!empty($productId) && !empty($detailsArray)){
    foreach ($detailsArray as $key => $value) {
      if(!empty($key)){
        update_post_meta($productId, $key, $value);     
      }
    }
    return RESPONSE_STATUS_TRUE;
  }
}
?>