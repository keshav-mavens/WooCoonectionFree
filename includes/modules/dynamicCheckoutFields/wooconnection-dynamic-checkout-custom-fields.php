<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Checkout Custom fields : wordpress hook is call to show the custom fields on checkout page after order notes textarea.....
add_action( 'woocommerce_after_order_notes', 'wc_checkout_custom_fields' );
//Function Definiation : wc_checkout_custom_fields
function wc_checkout_custom_fields(){
	global $table_prefix, $wpdb;
    //define table name....
    $cfield_group_table_name = 'wooconnection_custom_field_groups';
    $cfield_group_table_name = $table_prefix . "$cfield_group_table_name";
    //get all custom field groups......
  	$cFieldGroups = $wpdb->get_results("SELECT * FROM ".$cfield_group_table_name." WHERE wc_custom_field_group_status =".STATUS_ACTIVE." ORDER BY wc_custom_field_sort_order ASC"); 	
  	//check if data is not empty of custom field groups....
  	if(isset($cFieldGroups) && !empty($cFieldGroups)){
  		//then rotate loop............
  		foreach ($cFieldGroups as $key => $value)
  		{
  			//check parent group id....
  			if(!empty($value->id)){
  				//get the custom field by group id.....
  				$groupCFields=getGroupCFields($value->id);
  				//start main html.....
  				echo '<div class="custom_field_group_'.$value->id.'">';
					//set group as a custom field header........
					if(!empty($value->wc_custom_field_group_name)) {
						echo '<h4 class="group_header_'.$value->id.'">' . $value->wc_custom_field_group_name . '</h4>';
					}
					//check if data is not empty of custom fields....
					if(isset($groupCFields) && !empty($groupCFields)){
						//then rotate loop............
						//$cfieldIds = '';
						foreach ($groupCFields as $key => $value)
						{
							//define empty variables and arrays....
							$cfieldInputType = '';
							$cfieldInputLabel = '';
							$cfieldInputPlaceholder = '';
							$cfieldInputRequired = '';
							$cfieldInputDefault = '';
							$cfieldId = '';
							$cfieldOptions = array();
							$cfieldClass='';
							//check and set the value for each custom fields of each group to create custom field html...
							if(!empty($value['cFieldInputType'])){
								$cfieldInputType = $value['cFieldInputType'];
							}
							//check and set the custom field label....
							if(!empty($value['cFieldLabel'])){
								$cfieldInputLabel = $value['cFieldLabel'];
							}
							//check and set the custom field placeholder....
							if(!empty($value['cFieldPlaceholder'])){
								$cfieldInputPlaceholder = $value['cFieldPlaceholder'];
							}
							//check and set the custom field is required or not...
							if(!empty($value['cFieldReq'])){
								$cfieldInputRequired = $value['cFieldReq'];
							}
							//check and set the custom field default value....
							if(!empty($value['cFieldDefaultValue'])){
								$cfieldInputDefault = $value['cFieldDefaultValue'];
							}
							//check and set custom field id.....
							if(!empty($value['cFieldId'])){
								$cfieldId = $value['cFieldId'];
							}
							//check and set the custom field options when custom field input type is radio,select,checkbox......
							if(!empty($value['cFieldOptions'])){
								$cfieldOptions = $value['cFieldOptions'];
							}
							//check and set custom field class......
							if(!empty($value['cfieldClass'])){
								$cfieldClass = $value['cfieldClass'];
							}
							//use woocommerce function "woocommerce_form_field" to creare the html form......
							woocommerce_form_field( 'wc_checkout_field_' . $cfieldId, array(
							        'type'=> $cfieldInputType,
							        'class'=> array('wc_checkout_field_' . $cfieldId, $cfieldClass),
							       	'label'=>$cfieldInputLabel,
							        'placeholder'=>$cfieldInputPlaceholder,
							        'options'=>$cfieldOptions,
							        'required'=>$cfieldInputRequired,
								),$cfieldInputDefault);
							//set all custom field id in hidden field to apply the validation rule......
							echo '<input type="hidden" name="wc-checkout-field-ids[]" value="'.$cfieldId.'"/>';	
						}
					}
				echo  '</div>';
				//close main html.....
			}
		}
  	}
}


//Common Function : This function is used to get the custom fields on the basis of parent group id.....
function getGroupCFields($cfieldgroupId){
	global $wpdb,$table_prefix;
	//define table name....
	$cfields_table_name = 'wooconnection_custom_fields';
 	$cfields_table_name = $table_prefix . "$cfields_table_name";
 	//define empty array....
 	$cfieldHtml = array();
	//check parent group id exist the proceed next....
	if(!empty($cfieldgroupId)){
		//get all custom fields by group id........
		$cFields = $wpdb->get_results("SELECT * FROM ".$cfields_table_name." WHERE wc_cf_group_id = ".$cfieldgroupId." and wc_cf_status=".STATUS_ACTIVE." ORDER BY wc_cf_sort_order ASC");
		if(isset($cFields) && !empty($cFields)){
			foreach ($cFields as $key => $value)
	  		{
	  			//check custom field id.....
	  			if(!empty($value->id)){
	  				//call the function "getcfieldHtml" by custom field id....
	  				$cfieldHtml[] = getcfieldHtml($value->id);		
	  			}
	  		}
		}
	}
	return $cfieldHtml;
}


//Checkout Custom fields : wordpress hook is call to apply jquery datepicker by class on custom field of date type also add custom css for radio inputs.....
add_action( 'woocommerce_before_checkout_form', 'apply_date_picker_date_field');
//Function Definiation : apply_date_picker_date_field
function apply_date_picker_date_field() {
        wp_enqueue_style('jquery-ui-css', WOOCONNECTION_PLUGIN_URL.'assets/css/jquery-ui.css');
        wp_enqueue_script( 'jquery_min_js', WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.min.js', array( 'jquery' ), WOOCONNECTION_VERSION ,true );
        wp_enqueue_script('jquery_ui_js', WOOCONNECTION_PLUGIN_URL.'assets/js/jquery-ui-1.10.1.min.js', array('jquery'), WOOCONNECTION_VERSION, true );
    ?>
    <style type="text/css">
		.cfieldRadio input.input-radio,.cfieldRadio label.radio{
			float: left;
		}
	</style>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            if (jQuery(".cfieldDate")[0]){
                $( ".cfieldDate input" ).datepicker({
                  changeMonth: true,
                  changeYear: true
                });
            }
        });
    </script>
    
<?php
}

//Common Function : This function is used to get the custom fields html on the basis of custom field id.....
function getcfieldHtml($cfieldId){
	global $wpdb,$table_prefix;
	//define table name....
	$cfields_table_name = 'wooconnection_custom_fields';
	$cfields_table_name = $table_prefix . "$cfields_table_name";
	//define empty array....
	$cfieldArray = array();
	
	//check custom field id exist then proceed next/....
	if(!empty($cfieldId)){
		//get custom field details by custom field id....
		$cFieldData = $wpdb->get_results("SELECT * FROM ".$cfields_table_name." WHERE id = ".$cfieldId);
		//define empty variables and arrays....
		$cFieldDefaultValue = '';
		$cFieldOptions = array();
		$cFieldReq = '';
		$cFieldPlaceholder = '';
		$cFieldLabel = '';
		$cFieldId = '';
		$cfieldClass = "";
		//set default value.....
		$cFieldInputType =  'text';
		//check if data is not empty of particular custom field....
		if(isset($cFieldData) && !empty($cFieldData)){
			//check custom field type exist...
			if(!empty($cFieldData[0]->wc_cf_type))
				//if exist then set the value of "cFieldInputType" according to custom field type.....
				if($cFieldData[0]->wc_cf_type == CF_FIELD_TYPE_TEXT ){
					$cFieldInputType = 'text';
				}else if($cFieldData[0]->wc_cf_type == CF_FIELD_TYPE_TEXTAREA ){
					$cFieldInputType = 'textarea';
				}else if($cFieldData[0]->wc_cf_type == CF_FIELD_TYPE_DROPDOWN ){
					$cFieldInputType = 'select';
					//if custom field type is dropdown then get the options relations related to custom field......
					if(!empty($cFieldData[0]->wc_cf_options)){
						$fcoptionsarray = explode("@",$cFieldData[0]->wc_cf_options);
					}
					if(!empty($fcoptionsarray)){
						foreach ($fcoptionsarray as $key => $value) {
							$optionsarray[] = explode("#",$value);
						}
					}
					if(!empty($optionsarray)){
						foreach ($optionsarray as $key => $value) {
							$cFieldOptions[$value[0]] = $value[1]; 
						}
					}
				}
				else if($cFieldData[0]->wc_cf_type == CF_FIELD_TYPE_RADIO ){
					$cFieldInputType = 'radio';
					//if custom field type is radio then get the options relations related to custom field......
					if(!empty($cFieldData[0]->wc_cf_options)){
						$fcoptionsarray = explode("@",$cFieldData[0]->wc_cf_options);
					}
					if(!empty($fcoptionsarray)){
						foreach ($fcoptionsarray as $key => $value) {
							$optionsarray[] = explode("#",$value);
						}
					}
					if(!empty($optionsarray)){
						foreach ($optionsarray as $key => $value) {
							$cFieldOptions[$value[0]] = $value[1]; 
						}
					}
					//set custom class to set the html of radio butons.....
					$cfieldClass = "cfieldRadio";
				}
				else if($cFieldData[0]->wc_cf_type == CF_FIELD_TYPE_CHECKBOX ){
					$cFieldInputType = 'checkbox';
				}else if($cFieldData[0]->wc_cf_type == CF_FIELD_TYPE_DATE ){
					$cFieldInputType = 'text';
					//set custom class to apply jquery date module on input field.....
					$cfieldClass = 'cfieldDate';
				}
				//check and set if default value of custom field if exist......
				if(!empty($cFieldData[0]->wc_cf_default_value)){
					$cFieldDefaultValue = $cFieldData[0]->wc_cf_default_value;
				}
				//check custom field is mandatory on not......
				if($cFieldData[0]->wc_cf_mandatory == CF_FIELD_REQUIRED_YES){
					$cFieldReq = CF_FIELD_REQUIRED_YES;
				}
				//check and set the placeholder of custom field if exist..........
				if(!empty($cFieldData[0]->wc_cf_placeholder)){
					$cFieldPlaceholder = $cFieldData[0]->wc_cf_placeholder;
				}
				//check and set the custom field label.....
				if(!empty($cFieldData[0]->wc_cf_name)){
					$cFieldLabel = $cFieldData[0]->wc_cf_name;
				}
				//check and set the custom field id.....
				if(!empty($cFieldData[0]->id)){
					$cFieldId = $cFieldData[0]->id;
				}
					
		}
		//assign all values in array....
		$cfieldArray["cFieldId"] = $cFieldId;
		$cfieldArray["cFieldInputType"] = $cFieldInputType;
        $cfieldArray["cFieldLabel"] = $cFieldLabel;
        $cfieldArray["cFieldPlaceholder"] = $cFieldPlaceholder;
		$cfieldArray["cFieldReq"] = $cFieldReq;
		$cfieldArray["cFieldDefaultValue"] = $cFieldDefaultValue;
		$cfieldArray["cFieldOptions"] = $cFieldOptions;
		$cfieldArray["cfieldClass"] = $cfieldClass;
	}
	return  $cfieldArray;//return array of values.....
}

//Checkout Custom fields : wordpress hook is used to check whether data is valid and required fields not empty.....
add_action('woocommerce_checkout_process', 'validate_checkout_custom_fields');
//Function Definiation : validate_checkout_custom_fields
function validate_checkout_custom_fields() {
	if(isset($_POST) && !empty($_POST)){
		global $wpdb,$table_prefix;
		//define table name....
		$cfields_table_name = 'wooconnection_custom_fields';
		$cfields_table_name = $table_prefix . "$cfields_table_name";
		//check custom field id exist in post data , if yes then proceed next....
		if(!empty($_POST['wc-checkout-field-ids'])){
			$cFieldIds = $_POST['wc-checkout-field-ids'];//set custom field ids......	
			//check custom field ids not empty....
			if(!empty($cFieldIds)){
				//execute loop....
				foreach ($cFieldIds as $key => $value) {
					if(!empty($value)){
						//get custom field details by custom field id....
						$cFieldData = $wpdb->get_results("SELECT * FROM ".$cfields_table_name." WHERE id = ".$value);
						//check id data is not empty.....
						if(isset($cFieldData) && !empty($cFieldData)){
							$data = (array) $cFieldData[0];//convert to array....
							//check mandatory is not empty....
							if(!empty($data['wc_cf_mandatory'])){
								//check if field is required.....
								if($data['wc_cf_mandatory'] == CF_FIELD_REQUIRED_YES){
									//check if required field of type date....
									if(!empty($_POST['wc_checkout_field_'.$value]) && $data['wc_cf_type'] == CF_FIELD_TYPE_DATE){
										//check input date is valid or not....
										$datecfieldresponse = validateDatecField($_POST['wc_checkout_field_'.$value]);
										//if input data is invalid then add notice.....
										if(empty($datecfieldresponse)){
											wc_add_notice( __('Format of date is invalid in <strong>'.$data['wc_cf_name'].'</strong> field'), 'error' );
										}
									}else if(empty($_POST['wc_checkout_field_'.$value]))
									{
										wc_add_notice( __('<strong>'.$data['wc_cf_name'].'</strong> is a required field.' ), 'error' );	
									}
								}else{//check if field is not required then also check the input value is valid or not......
									if(!empty($_POST['wc_checkout_field_'.$value]) && $data['wc_cf_type'] == CF_FIELD_TYPE_DATE){
										//check input date is valid or not....
										$datecfieldresponse = validateDatecField($_POST['wc_checkout_field_'.$value]);
										//if input data is invalid then add notice.....
										if(empty($datecfieldresponse)){
											wc_add_notice( __('Format of date is invalid in <strong>'.$data['wc_cf_name'].'</strong> field'), 'error' );
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

//Checkout Custom fields : wordpress hook is used to update custom fields data......
add_action( 'woocommerce_checkout_update_order_meta', 'wc_custom_field_update_data' );
//Function Definiation : wc_custom_field_update_data
function wc_custom_field_update_data($orderId)
{
	//check order id exist then proceed exist......
	if(isset($orderId) && !empty($orderId)){
		if(isset($_POST) && !empty($_POST)){
			global $wpdb,$table_prefix;
			//define table name....
			$cfields_table_name = 'wooconnection_custom_fields';
			$cfields_table_name = $table_prefix . "$cfields_table_name";
			
			// Create instance of our wooconnection logger class to use off the whole things.
		    $wooconnectionLogger = new WC_Logger();
		    
		    //Concate a error message to store the logs...
		    $callback_purpose = 'Wooconnection Checkout Custom Fields : Process of update custom field values of contact';
		    
		    $applicationAuthenticationDetails = getAuthenticationDetails();

		    //Stop the below process if not authentication done with infusionsoft/keap application..
		    if(empty($applicationAuthenticationDetails) || empty($applicationAuthenticationDetails[0]->user_access_token))
		    {
		        $addLogs = addLogsAuthentication($callback_purpose);
		        return false;
		    }

		    //get the access token....
		    $access_token = '';
		    if(!empty($applicationAuthenticationDetails[0]->user_access_token)){
		        $access_token = $applicationAuthenticationDetails[0]->user_access_token;
		    }

			$cFieldContactRelated = array();
			$cFieldOrderRelated = array();
			if(!empty($_POST['wc-checkout-field-ids'])){
				if(!empty($_POST['wc-checkout-field-ids'])){
					foreach ($_POST['wc-checkout-field-ids'] as $key => $value) {
						if(!empty($value)){
							$cFieldData = $wpdb->get_results("SELECT * FROM ".$cfields_table_name." WHERE id = ".$value);
							if(isset($cFieldData) && !empty($cFieldData)){
								$cFieldKey = trim($cFieldData[0]->wc_cf_name);
								if(!empty($_POST['wc_checkout_field_'.$value])){
									update_post_meta( $orderId, $cFieldKey, trim($_POST['wc_checkout_field_'.$value]));
									if(!empty($cFieldData[0]->wc_cf_mapped)){
										$cFieldMappedWith = $cFieldData[0]->wc_cf_mapped;
										if(!empty($cFieldData[0]->wc_cf_mapped_field_type) && $cFieldData[0]->wc_cf_mapped_field_type == CUSTOM_FIELD_FORM_TYPE_CONTACT){
											$cFieldContactRelated[$cFieldMappedWith] = trim($_POST['wc_checkout_field_'.$value]);
										}else if (!empty($cFieldData[0]->wc_cf_mapped_field_type) && $cFieldData[0]->wc_cf_mapped_field_type == CUSTOM_FIELD_FORM_TYPE_ORDER) {
											$cFieldOrderRelated[$cFieldMappedWith] = trim($_POST['wc_checkout_field_'.$value]);
										}
									}
								}
							}
						}
					}
				}
			}
			
			//code is used to update the update the standard custom fields data in infusionsoft/keap application........
			$standardcf_table_name = 'wooconnection_standard_custom_field_mapping';
  			$wooconnection_standard_custom_field_mapping = $table_prefix . "$standardcf_table_name";
			$standardcFieldData = $wpdb->get_results("SELECT * FROM ".$wooconnection_standard_custom_field_mapping);
			if(isset($standardcFieldData) && !empty($standardcFieldData)){
				foreach ($standardcFieldData as $key => $value) {
					$field_name = $value->wc_standardcf_name;
			        $field_mapping = $value->wc_standardcf_mapped;
			       	$mapped_field_type = $value->wc_standardcf_mapped_field_type; 
			        if(isset($field_mapping) && !empty($field_mapping)){
			        	if(isset($_POST[$field_name]) && !empty($_POST[$field_name])){
				        	$standardcFieldMappedWith = $field_mapping;
				        	if(!empty($mapped_field_type) && $mapped_field_type == CUSTOM_FIELD_FORM_TYPE_CONTACT){
								if($field_name == 'billing_country'){
									$countryName = getCountryName($_POST[$field_name]);
									$fieldValue = $countryName;
								}else if ($field_name == 'billing_state') {
									$states = WC()->countries->get_states($_POST['billing_country']);
									$state = !empty($states[$_POST['billing_state']]) ? $states[$_POST['billing_state']] : '';
									$fieldValue = $state;
								}else if ($field_name == 'billing_company') {
									$company = stripslashes($_POST['billing_company']);
									$companyId = checkAddCompany($company,$access_token);
									if($standardcFieldMappedWith == 'CompanyID'){
										$fieldValue = $companyId;
										$cFieldContactRelated['Company'] = $company;
									}else{
										$fieldValue = $company;
									}
								}
								else{
									$fieldValue = $_POST[$field_name];
								}
								$cFieldContactRelated[$standardcFieldMappedWith] = trim($fieldValue);
							}else if (!empty($mapped_field_type) && $mapped_field_type == CUSTOM_FIELD_FORM_TYPE_ORDER) {
								$cFieldOrderRelated[$standardcFieldMappedWith] = trim($_POST[$field_name]);
							}
				        }
			        }
			    }
			}

			$order = wc_get_order( $orderId );	
			$order_email = $order->get_billing_email();
			// Validate email is in valid format or not 
		    validate_email($order_email,$callback_purpose,$wooconnectionLogger);

		    //check if contact already exist in infusionsoft/keap or not then add the contact infusionsoft/keap application..
		    $orderContactId = checkAddContactApp($access_token,$order_email,$callback_purpose);

			//update contact related custom field values.....
			if(isset($cFieldContactRelated) && !empty($cFieldContactRelated)){
		        if(!empty($orderContactId)){
		        	$responseCheck = updateContactCustomFields($access_token, $orderContactId, $cFieldContactRelated);
		        }
		    }
			
			//update order related custom field values.....
			if(isset($cFieldOrderRelated) && !empty($cFieldOrderRelated)){
				foreach ($cFieldOrderRelated as $key => $value) {
					if(!empty($key) && !empty($value)){
						update_post_meta($orderId,'orderCFields'.$key, $value);
					}
				}
			}
		}
	}
}
?>