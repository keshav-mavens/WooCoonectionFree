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
  }
  return $wcGeneralTriggers;
}

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
  global $wpdb;
  $wooCommerceProducts = listExistingDatabaseWooProducts();//call the common function to get the list of woocommerce publish products...
  $table_export_products_html = "";//Define export table html variable.....
  
  //set html if no products exist in woocommerce for export....
  if(empty($wooCommerceProducts)){
    $applicationName = applicationName();
    $connectedApplicationName = '';
    if(isset($applicationName) && !empty($applicationName)){
      $connectedApplicationName = $applicationName; 
    }
    $table_export_products_html = '<p class="heading-text" style="text-align:center">No products exist in woocommerce for export to "'.$connectedApplicationName.'" application .</p>';
  }else{
      //Compare woocommerce publish products infusionsoft/keap products....
      $exportProductsData = compareWooProductsWithInfusionKpProdcuts($wooCommerceProducts);
      //Check export products data....
      if(isset($exportProductsData) && !empty($exportProductsData)){
          //Show the number of products if they are already exported....
          if(isset($exportProductsData['exportExistingProductsCount']) && !empty($exportProductsData['exportExistingProductsCount'])){
              $table_export_products_html .= '<p class="heading-text">We have found '.$exportProductsData['exportExistingProductsCount'].' will not be available for export. Because they are already exported.</p>';
          }
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
  return $table_export_products_html;//return the html...
}

//list of existing woocommerce products from database and then return...
function listExistingDatabaseWooProducts(){
    global $wpdb;
    $existProductsDetails = get_posts(array('post_type' => 'product','post_status'=>'publish','orderby' => 'post_date','order' => 'DESC','posts_per_page'   => 999999));
    return $existProductsDetails;
}

//compare products of infusionsoft/keap with existing woocommerce products....
function compareWooProductsWithInfusionKpProdcuts($wooCommerceProducts)
{
    $productsData = array();//Define array...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
       $productsData = create_export_table_products_listing($wooCommerceProducts);
    }
    return $productsData;//Return array... 
}

//create products listing if no any product exist in infusionsoft/keap application......
function create_export_table_products_listing($wooCommerceProducts){
    $exportTableHtml  = '';//Define html variable...
    $exportProductsData = array();//Define array...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        $exportTableHtml .= '<thead>';
        $exportTableHtml .= '<tr>
                              <th style="text-align: center;"><input type="checkbox" id="export_products_all" name="export_products_all" class="all_products_checkbox_export" value="allproductsexport"></th>
                              <th>WooCommerce Product Name</th>
                              <th>WooCommerce Product SKU</th>
                              <th>WooCommerce Product Price</th>
                            </tr>';
        $exportTableHtml .= '</thead>';
        $exportTableHtml .= '<tbody>';
        $exportExistingProductsCount = 0;
        foreach ($wooCommerceProducts as $key => $value) {
            if(!empty($value->ID)){
                $wc_product_id = $value->ID;             
                $wcproduct = wc_get_product($value->ID);
                $wcproductPrice = $wcproduct->get_price();
                $currencySign = get_woocommerce_currency_symbol();
                if(!empty($wcproductPrice)){
                    $wcproductPrice = $wcproductPrice;
                }else{
                    $wcproductPrice = 0;
                }
                $wcproductPrice = $currencySign.number_format($wcproductPrice,2);
                
                $wcproductSku = $wcproduct->get_sku();
                $wcproductName = $wcproduct->get_name();
                if(!empty($wcproductSku)){
                  $wcproductSku = $wcproductSku;
                }else{
                  $wcproductSku = "--";
                }
                if(!empty($wcproductName)){
                  $wcproductName = $wcproductName;
                }else{
                  $wcproductName = "--";
                }
                $productExistId = get_post_meta($wc_product_id, 'is_kp_product_id', true);
                if(!empty($productExistId))
                {
                    $checkboxHtml = '';
                    $exportExistingProductsCount = $exportExistingProductsCount+1;
                }
                else
                {
                  $checkboxHtml = '<input type="checkbox" class="each_product_checkbox_export" name="wc_products[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'" '.$productChecked.'>'; 
                }
                $exportTableHtml .= '<tr><td style="text-align: center;">'.$checkboxHtml.'</td><td>'.$wcproductName.'</td><td>'.$wcproductSku.'</td><td>'.$wcproductPrice.'</td></tr>';

            }
        }
        $exportProductsData['exportTableHtml'] = $exportTableHtml;//set the export table html...
        $exportProductsData['exportExistingProductsCount'] = $exportExistingProductsCount;//return existing exported products count....
    }
    return $exportProductsData;
}

//Function is used to check whether the plugin is activated or not if not activated then return "leftMenusDisable" class....
function getPluginDetails(){
  $pluginDetails = array();
  $pluginDetails['activation_email'] = '';
  $pluginDetails['activation_key'] = '';
  //Get plugin details..
  $plugin_settings = get_option('wc_plugin_details');
  if(isset($plugin_settings) && !empty($plugin_settings)){
    if($plugin_settings['plugin_activation_status'] == PLUGIN_ACTIVATED){
        $pluginDetails['activation_email'] = $plugin_settings['wc_license_email'];
        $pluginDetails['activation_key'] = $plugin_settings['wc_license_key'];
    }
  }
  return $pluginDetails; 
}

function applicationType(){
  $data = getAuthenticationDetails();
  $applicationType = '';
  if(isset($data) && !empty($data)){
    $applicationtypeSelected =  $data[0]->user_application_edition;
  }
  return $applicationType;  
}

function applicationLabel($type=''){
  $label =  APPLICATION_TYPE_INFUSIONSOFT_LABEL;
  if($type != ""){
      $label =  $type;
  } 
  return $label;
}

function applicationName(){
  $data = getAuthenticationDetails();
  $applicationName = '';
  if(isset($data) && !empty($data)){
    $applicationName =  $data[0]->user_authorize_application;
  }
  return $applicationName;  
}


//Main function is used to generate the export products html.....
function createMatchProductsHtml(){
  global $wpdb;
  //call the common function to get the list of woocommerce already export products...
  $wooCommerceProducts = listExistingExportedProducts();
  
  //Define export table html variable.....
  $table_match_products_html = "";
  
  $configurationType = applicationType();
  $type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
  if(isset($configurationType) && !empty($configurationType)){
    if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
      $type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
    }else if ($configurationType == APPLICATION_TYPE_KEAP) {
      $type = APPLICATION_TYPE_KEAP_LABEL;
    }
  }
  $applicationLabel = applicationLabel($type);

  $isKeapProductsArray = getApplicationProducts();
  $applicationProducts = array();
  if($isKeapProductsArray['count'] > 0){
    $applicationProducts = $isKeapProductsArray;
  }
  
  //set html if no products exist in woocommerce for export....
  if(empty($wooCommerceProducts)){
    $applicationName = applicationName();
    $connectedApplicationName = '';
    if(isset($applicationName) && !empty($applicationName)){
      $connectedApplicationName = $applicationName; 
    }
    $table_match_products_html = '<p class="heading-text" style="text-align:center">No products exist.</p>';
  }else{
      //Compare woocommerce publish products infusionsoft/keap products....
      $matchProductsData = compareMatchProducts($wooCommerceProducts,$applicationProducts,$applicationLabel);
      //Check export products data....
      if(isset($matchProductsData) && !empty($matchProductsData)){
          //Get the export products table html and append to table
          if(!empty($matchProductsData['matchTableHtml'])){
            $table_match_products_html .= '<form action="" method="post" id="wc_match_products_form" onsubmit="return false">  
              <table class="table table-striped match_products_listing_class" id="match_products_listing">
                '.$matchProductsData['matchTableHtml'].'
              </table>
              <div class="form-group col-md-12 text-center m-t-60">
                <div class="matchProducts" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Update Products Mapping....</div>
                <div class="alert-error-message match-products-error" style="display: none;"></div>
                <div class="alert-sucess-message match-products-success" style="display: none;">Products mapping update successfully.</div>
                <input type="button" value="Update Products Mapping" class="btn btn-primary btn-radius btn-theme match_products_btn" onclick="wcProductsMapping()">
              </div>
            </form>';
          }
      }
  }
  return $table_match_products_html;//return the html...
}


function listExistingExportedProducts(){
  global $wpdb;
  $metaDetails = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='is_kp_product_id'");
  $exportProductIds = array();
  if (isset($metaDetails) && !empty($metaDetails)){
      foreach ($metaDetails as $key => $value) {
        if(!empty($value->meta_value) && !empty($value->post_id)){
            $productId = $value->post_id;
            $productStatus = get_post_status( $productId );
            if($productStatus == 'publish'){
              $exportProductIds[] = $productId;
            }
        }
      }
  }
  return $exportProductIds;
}



//compare products of infusionsoft/keap with existing woocommerce products....
function compareMatchProducts($wooCommerceProducts,$isKeapProductsArray,$applicationLabel)
{
    $productsData = array();//Define array...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        //first check products exist in connected infusionsoft/keap application or not if exist then call the "create_export_table_products_listing_with_iskp_products" function to generate the html..... 
        if(!empty($isKeapProductsArray)){
          $productsData = createMatchProductsApplication($wooCommerceProducts,$isKeapProductsArray,$applicationLabel);
        }
        //if not exist then call the "create_export_table_products_listing" function to generate the html.....
        else{
          $productsData = createMatchProducts($wooCommerceProducts,$applicationLabel);
        }
    }
    return $productsData;//Return array... 
}

//create products listing if no any product exist in infusionsoft/keap application......
function createMatchProducts($wooCommerceProducts,$applicationLabel){
    $matchTableHtml  = '';//Define html variable...
    $matchProductsData = array();//Define array...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        $matchTableHtml .= '<thead>';
        $matchTableHtml .= '<tr>
                              <th style="text-align: center;"><input type="checkbox" id="match_products_all" name="match_products_all" class="all_products_checkbox_match" value="allproductsmatch"></th>
                              <th>WooCommerce Product Name</th>
                              <th>WooCommerce Product SKU</th>
                              <th>WooCommerce Product Price</th>
                              <th>'.$applicationLabel.' Product</th>
                            </tr>';
        $matchTableHtml .= '</thead>';
        $matchTableHtml .= '<tbody>';
        foreach ($wooCommerceProducts as $key => $value) {
            if(!empty($value)){
                $wc_product_id = $value;             
                $wcproduct = wc_get_product($wc_product_id);
                $wcproductPrice = $wcproduct->get_price();
                $currencySign = get_woocommerce_currency_symbol();
                if(!empty($wcproductPrice)){
                    $wcproductPrice = $wcproductPrice;
                }else{
                    $wcproductPrice = 0;
                }
                $wcproductPrice = $currencySign.number_format($wcproductPrice,2);
                
                $wcproductSku = $wcproduct->get_sku();
                $wcproductName = $wcproduct->get_name();
                if(!empty($wcproductSku)){
                  $wcproductSku = $wcproductSku;
                }else{
                  $wcproductSku = "--";
                }
                if(!empty($wcproductName)){
                  $wcproductName = $wcproductName;
                }else{
                  $wcproductName = "--";
                }
                $matchTableHtml .= '<tr>
                                        <td style="text-align: center;"><input type="checkbox" class="each_product_checkbox_match" name="wc_products_match[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td>
                                        <td>'.$wcproductName.'</td>
                                        <td>'.$wcproductSku.'</td>
                                        <td>'.$wcproductPrice.'</td>
                                        <td>No '.$applicationLabel.' Products Exist!</td>
                                    </tr>';

            }
        }
        $matchProductsData['matchTableHtml'] = $matchTableHtml;//set the export table html...
    }
    return $matchProductsData;
}

function createMatchProductsApplication($wooCommerceProducts,$isKeapProductsArray,$applicationType){
  $matchTableHtml  = '';
    $matchProductsData = array();
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
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
                $wc_product_id = $value;             
                $wcproduct = wc_get_product($wc_product_id);
                $wcproductPrice = $wcproduct->get_regular_price();
                $currencySign = get_woocommerce_currency_symbol();
                $wcproductPrice = $currencySign.number_format($wcproductPrice,2);
                $wcproductSku = $wcproduct->get_sku();
                $wcproductName = $wcproduct->get_name();
                $productsDropDown = '';
                if(!empty($wcproductName)){
                  $wcproductName = $wcproductName;
                }else{
                  $wcproductName = "--";
                }
                if(!empty($wcproductSku)){
                  $wcproductSku = $wcproductSku;
                }else{
                  $wcproductSku = "--";
                }
                $productExistId = get_post_meta($value, 'is_kp_product_id', true);
                $productsDropDown = createMatchProductsSelect($isKeapProductsArray,$productExistId);
                $productSelectHtml = '<select class="application_match_products_dropdown" name="wc_product_mapped_with_'.$value.'" data-id="'.$value.'">'.$productsDropDown.'</select>';
                $matchTableHtml .= '<tr>
                                        <td><input type="checkbox" class="each_product_checkbox_match" name="wc_products_match[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td>
                                        <td>'.$wcproductName.'</td>
                                        <td>'.$wcproductSku.'</td>
                                        <td>'.$wcproductPrice.'</td>
                                        <td>'.$productSelectHtml.'</td>
                                    </tr>';

            }

        }
        $matchProductsData['matchTableHtml'] = $matchTableHtml;
    }
    return $matchProductsData;
}


//create the infusionsoft products dropdown for mapping..........
function createMatchProductsSelect($existingiskpProductResult,$wc_product_id_compare=''){
    $iskp_products_options_html = '';
    if(isset($existingiskpProductResult) && !empty($existingiskpProductResult)){
        foreach($existingiskpProductResult['products'] as $iskpProductDetails) {
          $iskpProductId = $iskpProductDetails['id'];
          $iskpProductName = $iskpProductDetails['product_name'];
          $iskpProductSelected = "";
          if(!empty($wc_product_id_compare)){
              if($wc_product_id_compare == $iskpProductId){
                  $iskpProductSelected = "selected";
              }else{
                  $iskpProductSelected = "";
              }
          }
          $iskp_products_options_html.= '<option value="'.$iskpProductId.'" '.$iskpProductSelected.' data-id="'.$iskpProductId.'">'.$iskpProductName.'</option>';
        }
    }
    return $iskp_products_options_html;
}


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
      //echo $err;
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
 
function updateExistingProduct($alreadyExistProductId,$access_token,$productDetailsArray){
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
        }else{
          $sucessData = json_decode($response,true);
          return $sucessData['id'];
        }
        curl_close($ch);
  }
  return true;
}


function createNewProduct($access_token,$productDetailsArray,$callback_purpose,$logtype){
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
          $errorMessage .= $callback_purpose ." is failed due to ". $err; 
          $wooconnection_logs_entry = $wooconnectionLogger->add($logtype, print_r($errorMessage, true));
        }else{
          $sucessData = json_decode($response,true);
          if(isset($sucessData['fault']) && !empty($sucessData['fault'])){
            $errorMessage = $callback_purpose ." is failed ";
            if(isset($sucessData['fault']['faultstring']) && !empty($sucessData['fault']['faultstring'])){
              $errorMessage .= "due to ".$sucessData['fault']['faultstring']; 
            }
            $wooconnection_logs_entry = $wooconnectionLogger->add($logtype, print_r($errorMessage, true));
            return false;
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



?>