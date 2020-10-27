<?php
require_once('wp-config.php');
global $table_prefix, $wpdb;
$plugin_table_name = 'plugin_authorization_details';
$wp_plugin_authorization_details = $table_prefix . "$plugin_table_name";
$listAuthenticateUsers = $wpdb->get_results('SELECT * FROM '.$wp_plugin_authorization_details.'');
if(isset($listAuthenticateUsers) && !empty($listAuthenticateUsers)){
	$authorization_details_array = array();
	$user_client_id =  get_option('admin_client_id');
	$user_client_secret =  get_option('admin_client_secret');
	foreach ($listAuthenticateUsers as $key => $value) {
		if(!empty($value->id)){
			$expire_seconds = '';
			$access_token = '';
			$refresh_token = '';
			$recent_token_updated_date = '';
			if(!empty($value->access_token)){
		      $access_token = $value->access_token;
		    }
		    if(!empty($value->user_access_token_expires_in)){
		      $expire_seconds = $value->user_access_token_expires_in;
		    }
		    if(!empty($value->user_refresh_token)){
		      $refresh_token = $value->user_refresh_token;
		    }	
			if(!empty($value->token_updated_date)){
				$recent_token_updated_date = $value->token_updated_date;//'2020-09-21 13:39:55';
			}
				
			//create the new refresh token..
			if(!empty($refresh_token)){
				$responseData = refreshToken($refresh_token,$user_client_id,$user_client_secret);     
				if(isset($responseData) && !empty($responseData)){
					if(!empty($responseData['access_token'])){
				    	$authorization_details_array['user_access_token'] = $responseData['access_token'];
				    }
				    if(!empty($responseData['expires_in'])){
				    	$authorization_details_array['user_access_token_expires_in'] = $responseData['expires_in'];
				    }
				    if(!empty($responseData['refresh_token'])){
				    	$authorization_details_array['user_refresh_token'] = $responseData['refresh_token'];
				    }
				    $authorization_details_array['token_updated_date'] = date("Y-m-d H:i:s");
				    $updateResultPro = $wpdb->update($wp_plugin_authorization_details, $authorization_details_array,array('id' => $value->id));
				}
			}
		}
	}
}

//Check the admin authentication details if exist then update the details...
$admin_authentication_settings = get_option('admin_authentication_details');
if(isset($admin_authentication_settings) && !empty($admin_authentication_settings)){
	$user_client_id =  get_option('admin_client_id');
	$user_client_secret =  get_option('admin_client_secret');
	$admin_access_token = '';
	$admin_refresh_token = '';
	if(!empty($admin_authentication_settings['access_token'])){
      $admin_access_token = $admin_authentication_settings['access_token'];
    }
    if(!empty($admin_authentication_settings['refresh_token'])){
      $admin_refresh_token = $admin_authentication_settings['refresh_token'];
    }
    //create the new refresh token..
	if(!empty($admin_refresh_token)){
		$responseData = refreshToken($admin_refresh_token,$user_client_id,$user_client_secret);
		update_option('admin_authentication_details',$responseData);	
	}			
}



//generate refresh toke on the basis of last refresh token, clientId, clientSecret
function refreshToken($refreshToken,$clientId,$clientSecret){
	$token_url = "https://api.infusionsoft.com/token";
		$PostData = array(
      	'grant_type' => 'refresh_token',
      	'refresh_token' => $refreshToken,
	);
	$client_id = $clientId;
    $client_secret = $clientSecret;
    $curl = curl_init($token_url);
   	$authentication_header = "Basic ". base64_encode($client_id.':'.$client_secret);
    $header = array(
	   'Authorization: '.$authentication_header,
	);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($PostData));
	$json_response = curl_exec($curl);
	$err = curl_error($curl);
    if ($err) {
    }else{
    	$responseArray = json_decode($json_response,true);
    	return $responseArray;
	}
	curl_close($curl);
}

?>