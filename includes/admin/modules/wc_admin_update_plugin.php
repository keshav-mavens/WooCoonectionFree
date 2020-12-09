<?php
	//Wordpress hook : This action is used to check the plugin update notification.... 
	add_filter('site_transient_update_plugins', 'wooconnection_push_update_notification' );
 	//Function Definiation : wooconnection_push_update_notification
 	function wooconnection_push_update_notification( $plugin_transient ){
	 	//first check....
	 	if ( empty($plugin_transient->checked ) ) {
	        return $plugin_transient;
	    }
	 
		//compare the plugin by transient......
		if( false == $remoteDetails = get_transient( 'plugin_upgrade_wooconnection' ) ) {
		 	$remoteDetails = wp_remote_get( ADMIN_REMOTE_URL.'remote_plugin_information.json', array(
		      'timeout' => 10,'headers' => array('Accept' => 'application/json')));
		 	if ( !is_wp_error( $remoteDetails ) && isset( $remoteDetails['response']['code'] ) && $remoteDetails['response']['code'] == 200 && !empty( $remoteDetails['body'] ) ) {
		      set_transient( 'plugin_upgrade_wooconnection', $remoteDetails, 43200 ); // 12 hours cache
		    }
		 
		}
	 
		//check remote details is exist or not.....
		if( $remoteDetails ) {
			//decode the response body....
		 	$remoteDetails = json_decode( $remoteDetails['body'] );
		 	//check remote details and compare the verison of it.....
		 	if( $remoteDetails && version_compare( WOOCONNECTION_VERSION, $remoteDetails->version, '<' ) ) {
		      $res = new stdClass();
		      $res->slug = 'wooconnection-WPplugin-i8cwye';
		      $res->plugin = 'wooconnection-WPplugin-i8cwye/wooconnection.php';
		      $res->new_version = $remoteDetails->version;
		      $res->tested = $remoteDetails->tested;
		      $res->package = $remoteDetails->download_url;
		      $plugin_transient->response[$res->plugin] = $res;
		    }
		}
		//return plugin transient......
		return $plugin_transient;
	}

	//Wordpress hook : This action is used to show the plugin information when user clicks of view details link from the update plugin notification html...... 
	add_filter('plugins_api', 'wooconnection_plugin_info', 20, 3);
	//Function Definiation : wooconnection_plugin_info
	function wooconnection_plugin_info( $result, $trigger_action, $arguments ){
	 
	  //define plugin slug to compare...
	  $wc_plugin_slug = 'wooconnection-WPplugin-i8cwye'; 

	  //stop process if trigger_action is not for plugin information.....
	  if('plugin_information' !== $trigger_action) {return false;}
	 
	  //compare plugin by slug....
	  if( $wc_plugin_slug !== $arguments->slug ) {return false;}
	 
	  //compare the plugin by transient......
	  if( false == $remoteData = get_transient( 'plugin_upgrade_wooconnection_' . $wc_plugin_slug ) ) {
	 	$remoteData = wp_remote_get( ADMIN_REMOTE_URL.'remote_plugin_information.json', array('timeout' => 10,'headers' => array('Accept' => 'application/json')));
	 	if ( ! is_wp_error( $remoteData ) && isset( $remoteData['response']['code'] ) && $remoteData['response']['code'] == 200 && ! empty( $remoteData['body'] ) ) {
	      set_transient( 'plugin_upgrade_wooconnection_' . $wc_plugin_slug, $remoteData, 43200 ); // 12 hours cache
	    }
	  }
	 
	  //check if curl response to get the plugin information is ok then assign all the information to variables...
	  if( ! is_wp_error( $remoteData ) && isset( $remoteData['response']['code'] ) && $remoteData['response']['code'] == 200 && ! empty( $remoteData['body'] ) ) {
	 	$remoteData = json_decode( $remoteData['body'] );//decode the response body....
	    $result = new stdClass();
	 	$result->name = 'WooConnection';
	    $result->slug = $wc_plugin_slug;
	    $result->version = $remoteData->version;
	    $result->tested = $remoteData->tested;
	    $result->requires = $remoteData->requires;
	    $result->author = '<a href="http://www.fullstackmarketing.co">Fullstackmarketing.co</a>';
	    $result->author_profile = 'http://www.fullstackmarketing.co';
	    $result->download_link = $remoteData->download_url;
	    $result->trunk = $remoteData->download_url;
	    $result->requires_php = '5.3';
	    $result->last_updated = $remoteData->last_updated;
	    $result->sections = array(
	      'description' => $remoteData->sections->description,
	      'installation' => $remoteData->sections->installation,
	      'changelog' => $remoteData->sections->changelog
	    );
	 	$result->banners = array(
	      'low' => 'https://ps.w.org/woocommerce/assets/banner-772x250.jpg',
	      'high' => 'https://ps.w.org/woocommerce/assets/banner-1544x500.png'
	    );
	    return $result;
	  }
	  return false;
	}

	//Wordpress hook : This action is when wooconnection plugin update process is trigger then clera the cache by deleting the transient......
	add_action( 'upgrader_process_complete', 'wooconnection_after_update', 10, 2 );
 	//Function Definiation : wooconnection_after_update
	function wooconnection_after_update( $upgrader_object, $options ) {
		if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
			// just clean the cache when new plugin version is installed
			delete_transient( 'plugin_upgrade_wooconnection' );
			// just clean the cache when new plugin version is installed
			delete_transient( 'plugin_upgrade_wooconnection_wooconnection-WPplugin-i8cwye' );
		}
	}
?>