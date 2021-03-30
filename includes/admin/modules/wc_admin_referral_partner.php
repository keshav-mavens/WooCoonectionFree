<?php
	//get the authenticate application details first.....
	$authenticateAppdetails = getAuthenticationDetails();
	//get the referral partner tracking status......
	$checkReferralTrackingStatus = get_option('referral_partner_tracking_status');
	//check authenticate details....
	if(isset($authenticateAppdetails) && !empty($authenticateAppdetails)){
		//check authenticate application type is infusionsoft or application name is exist......
		if($authenticateAppdetails[0]->user_application_edition == APPLICATION_TYPE_INFUSIONSOFT && !empty($authenticateAppdetails[0]->user_authorize_application) && !empty($checkReferralTrackingStatus) && $checkReferralTrackingStatus == 'On'){
			//then call the hook to show the meta box in edit screen of every post, product, page.....
			add_action( 'add_meta_boxes', 'wpdocs_show_referral_link_meta_boxes' );
			//Wordpress Hook : This action is triggered to add custom fields to associate contact with referral partner.....
			add_action('woocommerce_coupon_options','add_referral_partner_related_fields',10,2);
			//add js for referral partner related fields on coupon admin screen...
			add_action('admin_enqueue_scripts','enqueue_script_referral_section_coupon',10,1);
			//Wordpress Hook : This action is used to save referral partner details with coupon code...
			add_action('woocommerce_coupon_options_save','save_referral_related_fields',10,2);
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
        	$referralTrackingLink = 'https://'.$authenticate_application_name.'.infusionsoft.com/aff.html?to='.get_the_permalink($post);
        	$referral_tracking_link_meta_box_html .= '<div class="custom-meta-box" id="referral_tracking_link">'.$referralTrackingLink.'</div><button onclick="copyContent(\'referral_tracking_link\')" type="button" class="button-primary" style="margin-top:10px">Copy Url</button><span id="copyResult"></span>'; 
        }else{
            $referral_tracking_link_meta_box_html .= "Publish ".$postType." first for infusionsoft referral partner tracking link.";
        }    
	    echo $referral_tracking_link_meta_box_html;
	}

	//Function Definition : add_referral_partner_related_fields...
	function add_referral_partner_related_fields($couponId,$couponDetails){
		//get the referral partner id associated with coupon code...
		$referralPartnerId = get_post_meta($couponId,'associated_referral_partner',true);
		woocommerce_wp_checkbox(array('id'=>'enable_referral_association','label'=>__('Contact Association With Referral Partner','woocommerce'),'description'=>__('Check this box if coupon code associate a contact with define referral partner.','woocommerce')));
		?>
		<p class="form-field referral_partner_field" style="display: none;">
			<label for="referral_partner_id">Referral Partner Id</label>
			<input type="number" name="referral_partner_id" id="referral_partner_id" value="<?php echo $referralPartnerId; ?>" placeholder="Referral Partner Id">
			<?php echo wc_help_tip("The mention referral partner id override the contact lead referral partner any previously set lead referral partner for contact."); ?>
		</p>
		<?php
	}
	
	//Function Definition : enqueue_script_referral_section_coupon
	function enqueue_script_referral_section_coupon($hook){
		global $post;
		//check page is add/edit post...
		if($hook == 'post-new.php' || $hook == 'post.php'){
			//get the post type.....
			$postType = '';
			if(!empty($post)){
				$postType = $post->post_type;
			}
			//check if post type is equal to coupon then add the custom jquery...
			if(isset($postType) && $postType === 'shop_coupon'){
				wp_deregister_script( 'jquery' );
		        //Wooconnection Scripts : Resgister the wooconnection scripts..
		        wp_register_script('jquery', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.min.js'),WOOCONNECTION_VERSION, true);
		        //Wooconnection Scripts : Enqueue the wooconnection scripts..
		        wp_enqueue_script('jquery');
		        ?>
		        	<script type="text/javascript">
			   			$(document).ready(function() {
			   				if($("#enable_referral_association").is(':checked')){
			   					$(".referral_partner_field").show();
			   				}else{
			   					$(".referral_partner_field").hide();
			   				}

			   				$("#enable_referral_association").change(function(){
			       				if(this.checked){
			       					$(".referral_partner_field").show();
			       				}else{
			       					$(".referral_partner_field").hide();
			       				}
			       			});	
			   			});
		       		</script>
		        <?php
			}
		}
	}

	//Function Definition : save_referral_related_fields 
	function save_referral_related_fields($postId,$coupon){
		//first check post if exist or not....
		if(isset($postId) && !empty($postId)){
			//update enable referral with coupon association
			update_post_meta($postId,'enable_referral_association',$_POST['enable_referral_association']);
			//check referral partner field exist in coupon code or not...
			if(isset($_POST['referral_partner_id'])){
				//check if referral association id disable then set the referral partner null....
				if(!isset($_POST['enable_referral_association'])){
					$referral_partner_id = NULL;
				}
				//else set referral partner set in post data....
				else{
					$referral_partner_id = $_POST['referral_partner_id'];
				}
				//update the referral partner association.....
				update_post_meta($postId,'associated_referral_partner',$referral_partner_id);
			}
		}
	}
?>