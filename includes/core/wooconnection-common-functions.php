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
  $applicationType = DEFAULT_APPLICATION_TYPE;//Default application tyoe is "Infusionsoft".
  
  //set html if no products exist in woocommerce for export....
  if(empty($wooCommerceProducts)){
      $table_export_products_html = '<p class="heading-text" style="text-align:center">No products exist in woocommerce for export to '.$applicationType.' account .</p>';
  }else{
      //Compare woocommerce publish products infusionsoft/keap products....
      $exportProductsData = compareWooProductsWithInfusionKpProdcuts($wooCommerceProducts,$isKeapProductsArray,$applicationType);
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
function compareWooProductsWithInfusionKpProdcuts($wooCommerceProducts,$isKeapProductsArray,$applicationType)
{
    $productsData = array();//Define array...
    if(isset($wooCommerceProducts) && !empty($wooCommerceProducts)){
        //first check products exist in connected infusionsoft/keap application or not if exist then call the "create_export_table_products_listing_with_iskp_products" function to generate the html..... 
        if(!empty($isKeapProductsArray)){
          $productsData = create_export_table_products_listing_with_iskp_products($wooCommerceProducts,$isKeapProductsArray,$applicationType);
        }
        //if not exist then call the "create_export_table_products_listing" function to generate the html.....
        else{
          $productsData = create_export_table_products_listing($wooCommerceProducts,$applicationType);
        }
    }
    return $productsData;//Return array... 
}

//create products listing if no any product exist in infusionsoft/keap application......
function create_export_table_products_listing($wooCommerceProducts,$applicationType){
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
                $exportTableHtml .= '<tr>
                                        <td style="text-align: center;"><input type="checkbox" class="each_product_checkbox_export" name="wc_products[]" value="'.$wc_product_id.'" id="'.$wc_product_id.'"></td>
                                        <td>'.$wcproductName.'</td>
                                        <td>'.$wcproductSku.'</td>
                                        <td>'.$wcproductPrice.'</td>
                                    </tr>';

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
        $jsonData ='{"duplicate_option": "Email","email_addresses":[{"email": "'.$appUseremail.'","field": "EMAIL1"}],"opt_in_reason": "Customer opted-in through purchasing."}';
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
            return false;
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
      //$access_token = 'sdsd2121323f13s2df123sd1f32s1d';
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
?>