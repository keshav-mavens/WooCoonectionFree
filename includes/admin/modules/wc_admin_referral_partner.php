<?php
	//get the authenticate application details first.....
	$authenticateAppdetails = getAuthenticationDetails();
	//check authenticate details....
	if(isset($authenticateAppdetails) && !empty($authenticateAppdetails)){
		//check authenticate application type is infusionsoft or application name is exist......
		if($authenticateAppdetails[0]->user_application_edition == APPLICATION_TYPE_INFUSIONSOFT && !empty($authenticateAppdetails[0]->user_authorize_application)){
			//then call the hook to show the meta box in edit screen of every post, product, page.....
			add_action( 'add_meta_boxes', 'wpdocs_show_referral_link_meta_boxes' );
		}
	}

	//Function Definiation : wpdocs_show_referral_link_meta_boxes
	function wpdocs_show_referral_link_meta_boxes() {
	    $showOnscreens = array( 'post', 'page','product');//set the name of post type where need to display the referral partner tracking link......
	    //execute loop to add meta box on screen as set in array.....
		foreach ( $showOnscreens as $screen ) {
	        add_meta_box( 'meta-box-id', __( 'Infusionsoft Referral Partner Tracking Link', 'textdomain' ), 'wpdocs_display_referral_tracking_link', $screen,'side');
	    }
	}

	//Function Definiation : wpdocs_show_referral_link_meta_boxes
	function wpdocs_display_referral_tracking_link( $post ) {
	    //get the authenticate application details first.....
	    $authenticateAppdetails = getAuthenticationDetails();
		//define empty variables.....
		$authenticate_application_name = "";
	    $referral_tracking_link_meta_box_html = '';
	    //check authenticate details....
	    if(isset($authenticateAppdetails) && !empty($authenticateAppdetails)){
	    	//check authenticate  name is exist......
	    	if(isset($authenticateAppdetails[0]->user_authorize_application)){
		        $authenticate_application_name = $authenticateAppdetails[0]->user_authorize_application;
		    }	
	    }
	    //get post status....
	    $postStatus = $post->post_status;
	    //get post type...
	    $postType = $post->post_type;
	    //check post status is publish....
	    if($postStatus == 'publish'){
        	$referralTrackingLink = 'http://'.$authenticate_application_name.'.infusionsoft.com/aff.html?to='.get_the_permalink($post);
        	$referral_tracking_link_meta_box_html .= '<div class="custom-meta-box" id="referral_tracking_link">'.$referralTrackingLink.'</div><button onclick="copyContent(\'referral_tracking_link\')" type="button">Copy url</button><span id="copyResult"></span>'; 
        }else{
            $referral_tracking_link_meta_box_html .= "Publish ".$postType." first for infusionsoft referral partner tracking link.";
        }    
	    echo $referral_tracking_link_meta_box_html;
	}
?>