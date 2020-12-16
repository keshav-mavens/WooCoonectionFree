<?php 
	
	//Define the class WC_Subscription_Coupons.....
	class WC_Subscription_Coupons extends WC_Coupon {
		
		/** 
	     * Coupon constructor. Loads coupon data. 
	     * @param mixed $data Coupon data, object, ID or code. 
	     */ 
		public function __construct() {
 			global $woocommerce;
 			//Wordpress hook : This action is triggered to add new discount type in coupons codes admin panel......
 			add_filter( 'woocommerce_coupon_discount_types', [$this, 'create_custom_discount_type'], 10, 1);
        	//Wordpress hook : This action is triggered to add new discount type related fields......
        	add_action( 'woocommerce_coupon_options', [$this, 'add_custom_discount_type_fields'], 10, 2 );
        	//Wordpress hook : This action is triggered to save custom fields related data...........
        	add_action( 'woocommerce_coupon_options_save', [$this, 'save_custom_discount_coupon_fields'], 10, 2 );  
 			//include custom css and js coupons admin panel.....
            $this->includeCssJs();
 		}

 		//Function Definiation : create_custom_discount_type
 		public function create_custom_discount_type($discountTypes){
	        	$discountTypes['custom_subscription_free_trial'] =__( 'Subscription Free Trial', 'woocommerce' );
	            return $discountTypes;
	    }

	    //Function Definiation : add_custom_discount_type_fields
		public function add_custom_discount_type_fields($coupon_get_id, $coupon){
				$trialDuration = get_post_meta($coupon_get_id,'custom_free_coupon_trial_duration',true);
	        	$trialPeriod = get_post_meta($coupon_get_id,'custom_free_coupon_trial_period',true);
	        	?>
	            <p class="form-field custom_free_trial_coupon_field" style="display: none;">
	            	<label for="free_coupon_trial_duration">Free Trial Validity</label>
	            	<input type="number" id="free_coupon_trial_duration" name="free_coupon_trial_duration" class="" value="<?php echo $trialDuration; ?>" />
	            	<select id="free_coupon_trial_period" name="free_coupon_trial_period" class="">
                        <option value="day" <?php if (isset($trialPeriod) && $trialPeriod == 'day') echo ' selected="selected"'; ?>>days</option>
                        <option value="week" <?php if (isset($trialPeriod) && $trialPeriod == 'week') echo ' selected="selected"'; ?>>weeks</option>
                        <option value="month" <?php if (isset($trialPeriod) && $trialPeriod == 'month') echo ' selected="selected"'; ?>>months</option>
                        <option value="year" <?php if (isset($trialPeriod) && $trialPeriod == 'year') echo ' selected="selected"'; ?>>years</option>
                    </select>
	            	<?php echo wc_help_tip("First recurring payment will be charged after the duration mention in field."); ?>
	            </p>
	        	<?php
	    }

	    //Function Definiation : save_custom_discount_coupon_fields
	    public function save_custom_discount_coupon_fields($post_id, $coupon){
	    	update_post_meta( $post_id, 'custom_free_coupon_trial_duration', $_POST['free_coupon_trial_duration'] );
	    	update_post_meta( $post_id, 'custom_free_coupon_trial_period', $_POST['free_coupon_trial_period'] );
	    }


	    //Function Definition : includeCssJs
	    public function includeCssJs(){
	        ob_start();
			?>
	            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	            <script type="text/javascript">
					$( document ).ready(function() {
						$('.custom_free_trial_coupon_field').hide();
					    if($('#discount_type').val() == 'custom_subscription_free_trial'){
					    	$('.custom_free_trial_coupon_field').show();
					    }else{
					    	$('.custom_free_trial_coupon_field').hide();
					    }
					    //on change of discount type from coupon code screen show/hide the trial period field ......
					    $('#discount_type').change(function() {
					        var selectedDiscountType = $( this ).val();
					    	if(selectedDiscountType == 'custom_subscription_free_trial'){
					    		$('.custom_free_trial_coupon_field').show();
					    	}else{
					    		$('.custom_free_trial_coupon_field').hide();
					    	}
					    });
					});
				</script>
	        <?php
	    }
	}

	// Create global so you can use this variable beyond initial creation.
	global $custom_subscription_coupons;

	// Create instance of our wooconnection class to use off the whole things.
	$custom_subscription_coupons = new WC_Subscription_Coupons();
?>