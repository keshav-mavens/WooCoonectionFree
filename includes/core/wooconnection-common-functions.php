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
    if(isset($importExportProductId) && !empty($importExportProductId)){
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
      $exportProductsData = compareWooProductsWithAppProducts($woocommerceProducts,$applicationProductsArray,$applicationLabel);
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

//compare products of infusionsoft/keap with existing woocommerce products....
function compareWooProductsWithAppProducts($wooCommerceProducts,$applicationProductsArray,$applicationType)
{
    //Define array...
    $productsData = array();
    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
      $productsData = exportProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationType);
    }
    //Return array... 
    return $productsData;
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
                if(!empty($applicationProductsArray['products'])){
                    //Check product relation is exist....
                    $productExistId = get_post_meta($wc_product_id, 'is_kp_product_id', true);
                    //If product relation exist then create select deopdown and set associative product selected....
                    if(isset($productExistId) && !empty($productExistId)){
                      $productName = getApplicationProductDetails($access_token,$productExistId);
                      if(!empty($productName)){
                          $productsDropDown = '<input type="hidden" value="'.$productExistId.'" name="wc_product_export_with_'.$wc_product_id.'">'.$productName;
                      }else{
                        $productsDropDown = 'Mapped Product Not Exist In App!';
                      }
                    }else if (!empty($wcproductSku)) {//Then check product sku,If product sku exist then check product in application with same sku is exist ot not....
                      $checkSkuMatchWithIskpProducts = checkProductMapping($wcproductSku,$applicationProductsArray); 
                      //if product/multiple products with same sku is exist then get the last matched product id.... 
                      if(isset($checkSkuMatchWithIskpProducts) && !empty($checkSkuMatchWithIskpProducts)){
                          $matchId =  end($checkSkuMatchWithIskpProducts);
                          //On the basis of match product id set the product selected and create html.....
                          if(!empty($matchId)){
                            $productName = getApplicationProductDetails($access_token,$matchId);
                            if(!empty($productName)){
                                $productsDropDown = '<input type="hidden" value="'.$matchId.'" name="wc_product_export_with_'.$wc_product_id.'">'.$productName;
                            }else{
                              $productsDropDown = 'Mapped Product Not Exist In App!';
                            }
                          }
                      }else{//If product with same sku is not exist, then create select without any product selected...
                        $productsDropDown = 'No mapping exist!';
                      }
                    }else{//If relation is not exist with product then create select without any product selected...
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
      $matchProductsData = compareMatchProductsWithAppProducts($wooCommerceProducts,$applicationProductsArray,$applicationLabel);
      //Check export products data....
      if(isset($matchProductsData) && !empty($matchProductsData)){
          //Get the match products table html and append to table
          if(!empty($matchProductsData['matchTableHtml'])){
            $table_match_products_html .= '<form action="" method="post" id="wc_match_products_form" onsubmit="return false">  
              <table class="table table-striped match_products_listing_class" id="match_products_listing">
                '.$matchProductsData['matchTableHtml'].'
              </table>
              <div class="form-group col-md-12 text-center m-t-60">
                <div class="matchProducts" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Update Mapping....</div>
                <div class="alert-error-message match-products-error" style="display: none;"></div>
                <div class="alert-sucess-message match-products-success" style="display: none;">Products mapping update successfully.</div>
                <input type="button" value="Update Mapping" class="btn btn-primary btn-radius btn-theme match_products_btn" onclick="wcProductsMapping()">
              </div>
            </form>';
          }
      }
  }
  //return the html...
  return $table_match_products_html;
}

//compare products of infusionsoft/keap with existing woocommerce products....
function compareMatchProductsWithAppProducts($wooCommerceProducts,$applicationProductsArray,$applicationLabel)
{
    //Define array...
    $productsData = array();
    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        $productsData = createMatchProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationLabel);
    }
    //Return array... 
    return $productsData;
}

//Create the match products table listing....
function createMatchProductsListingApplication($wooCommerceProducts,$applicationProductsArray,$applicationType){
    $matchTableHtml  = '';//Define variable..
    $matchProductsData = array();//Define array...
    //First check if wooproducts exist...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        //Create first table....
        $matchTableHtml .= '<thead>';
        $matchTableHtml .= '<tr>
                        <th style="text-align: center;"><input type="checkbox" id="match_products_all" name="match_products_all" class="all_products_checkbox_match" value="allproductsexport"></th>
                        <th>WooCommerce Product Name</th>
                        <th>WooCommerce Product SKU</th>
                        <th>WooCommerce Product Price</th>
                        <th>'.$applicationType.' Product</th>
                      </tr>';
        $matchTableHtml .= '</thead>';
        $matchTableHtml .= '<tbody>';
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
                if(!empty($applicationProductsArray['products'])){
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
                //Create final html.......
                $matchTableHtml .= '<tr><td><input type="checkbox" class="each_product_checkbox_match" name="wc_products_match[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td><td>'.$wcproductName.'</td><td  class="skucss">'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td><td>'.$productSelectHtml.'</td></tr>';

            }

        }
        $matchProductsData['matchTableHtml'] = $matchTableHtml;//Assign html....
    }
    return $matchProductsData;//Return data....
}


//create the infusionsoft products dropdown for mapping..........
function createMatchProductsSelect($existingiskpProductResult,$wc_product_id_compare=''){
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
function checkAddProductIsKp($access_token,$item){
    $currentProductID = '';
    $productId = $item->get_id();
    $checkAlreadyExist = get_post_meta($productId, 'is_kp_product_id', true);
    $wooconnectionLogger = new WC_Logger();
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
function addContactNotes($access_token,$orderContactId,$noteText,$itemTitle){
    if(!empty($access_token) && !empty($orderContactId) && !empty($noteText)){
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
  $predefinedcfields["Contact Basic Infomation"]["FormType:".CUSTOM_FIELD_FORM_TYPE_CONTACT.':CompanyID'] = "Contact Company";
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
    $standard_fields_mapping_html .= '<form action="" method="post" id="wc_standard_fields_mapping_form" onsubmit="return false">
        <table class="table table-striped standard_fields_listing_class" id="standard_fields_listing">
          <thead>
            <tr>
              <th>
                <input type="checkbox" id="match_fields_all" name="match_fields_all" class="all_fields_mapped_checkbox" value="allfieldsmapped">
              </th>
              <th>WooCommerce Standard Field</th>
              <th>'.$applicationLabel.' Field</th>
            </tr>
          </thead>
          <tbody>'.$wooStandardCheckoutFields.'</tbody>
        </table>
        <div class="form-group col-md-12 text-center m-t-60">
          <div class="fieldsMapping" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Update Standard Fields Mapping......</div>
          <div class="alert-error-message standard-fields-error" style="display: none;"></div>
          <div class="alert-sucess-message standard-fields-success" style="display: none;">Products export successfully.</div>
          <input type="button" value="Update Fields Mapping" class="btn btn-primary btn-radius btn-theme standard_fields_mapping_btn" onclick="wcStandardFieldsMapping()">
        </div>
    </form>';  
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
  if(isset($checkoutStandardFields) && !empty($checkoutStandardFields)){
    foreach ($checkoutStandardFields as $key => $value) {
        $field_id = $value->id;
        $field_name = $value->wc_standardcf_label;
        $field_mapping = $value->wc_standardcf_mapped;
        $mapped_field_type = $value->wc_standardcf_mapped_field_type; 

        if(isset($field_mapping) && !empty($field_mapping)){
          $fieldsDropDown = createMappedFieldSelect($mapped_field_type,$field_mapping);
        }else{
          $fieldsDropDown = createMappedFieldSelect($mapped_field_type,'');
        }
        $wccheckoutStandardFieldsHtml.='<tr>
                                <td><input type="checkbox" class="each_field_mapped_checkbox" name="wc_fields_mapping[]" value="'.$field_id.'" id="'.$field_id.'"></td>
                                <td>'.$field_name.'</td>
                                <td><select name="standard_cfield_mapping_'.$field_id.'" class="standardcfieldmappingwith"><option value="donotmap">Do not mapped</option>'.$fieldsDropDown.'</select></td>
                              </tr>';
    }
  }
  return $wccheckoutStandardFieldsHtml;
}

//create infusionsoft/keap fields options html on the basis of mapping...
function createMappedFieldSelect($field_type,$mappedWith=""){
  $preDefinedCustomFields = getPredefindCustomfields();
  $cfieldOptionsHtml = "";
  foreach($preDefinedCustomFields as $key => $value) {
    $cfieldOptionsHtml .= "<optgroup label=\"$key\">";
    foreach($value as $key1 => $value1) {
      $cfieldoptionSelected = "";
      if(!empty($mappedWith)){
        if($key1 == 'FormType:'.$field_type.':'.$mappedWith){
          $cfieldoptionSelected = "selected";
        }
      }
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

?>