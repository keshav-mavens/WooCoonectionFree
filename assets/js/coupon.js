(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
            //by default hide the extra fields......
			$('.custom_free_trial_coupon_field').hide();
            $('.subscription_discount_duration_field').hide();
            $('.subscription_discount_type_field').hide();
            $('.subscription_coupon_amount_field').hide();
			
            //on page load check the discount type for coupons.... on the basis of it show/hide the trial field option..... 
			if($('#discount_type').val() == 'custom_subscription_free_trial'){
                $('.custom_free_trial_coupon_field').show();
                $('.coupon_amount_field').show();
                $('.subscription_discount_duration_field').hide();
                $('.subscription_discount_type_field').hide();
                $('.subscription_coupon_amount_field').hide();
            }else if($("#discount_type").val() == 'custom_subscription_plan_discount'){
                $('.coupon_amount_field').hide();
                $('.custom_free_trial_coupon_field').hide();
                $('.subscription_discount_duration_field').show();
                $('.subscription_discount_type_field').show();
                $('.subscription_coupon_amount_field').show();
            }
            else{
                $('coupon_amount_field').show();
                $('.custom_free_trial_coupon_field').hide();
                $('.subscription_discount_duration_field').hide();
                $('.subscription_discount_type_field').hide();
                $('.subscription_coupon_amount_field').hide();
            }
            
            //on change of discount type from coupon code screen show/hide the trial period field ......
            $('#discount_type').change(function() {
                var selectedDiscountType = $( this ).val();
                if(selectedDiscountType == 'custom_subscription_free_trial'){
                    $('.coupon_amount_field').show();
                    $('.custom_free_trial_coupon_field').show();
                    $('.subscription_discount_duration_field').hide();
                    $('.subscription_discount_type_field').hide();
                    $('.subscription_coupon_amount_field').hide();
                }else if(selectedDiscountType == 'custom_subscription_plan_discount'){
                    $('.coupon_amount_field').hide();
                    $('.custom_free_trial_coupon_field').hide();
                    $('.subscription_discount_duration_field').show();
                    $('.subscription_discount_type_field').show();
                    $('.subscription_coupon_amount_field').show();
                }
                else{
                    $('.coupon_amount_field').show();
                    $('.custom_free_trial_coupon_field').hide();
                    $('.subscription_discount_duration_field').hide();
                    $('.subscription_discount_type_field').hide();
                    $('.subscription_coupon_amount_field').hide();
                }
            });
        });
}(jQuery));