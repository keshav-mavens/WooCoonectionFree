(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
            $('.custom_free_trial_coupon_field').hide();
            $('.sub_discount_type_field').hide();
            $('.subscription_discount_validity_field').hide();
            $(".sub_discount_amount_field").hide();
            //on page load check the discount type for coupons.... on the basis of it show/hide the trial field option..... 
            if($('#discount_type').val() == 'custom_subscription_managed'){
                $('.custom_free_trial_coupon_field').show();
                $('.coupon_amount_field').hide();
                $('.sub_discount_type_field').show();
                $('.subscription_discount_validity_field').show();
                $(".sub_discount_amount_field").show();
            }else{
                $('.custom_free_trial_coupon_field').hide();
                $('.coupon_amount_field').show();
                $('.sub_discount_type_field').hide();
                $('.subscription_discount_validity_field').hide();
                $('.sub_discount_amount_field').hide();
            }
            
            //on change of discount type from coupon code screen show/hide the trial period field ......
            $('#discount_type').change(function() {
                var selectedDiscountType = $( this ).val();
                if(selectedDiscountType == 'custom_subscription_managed'){
                    $('.custom_free_trial_coupon_field').show();
                    $(".coupon_amount_field").hide();
                    $(".sub_discount_type_field").show();
                    $('.subscription_discount_validity_field').show();
                    $(".sub_discount_amount_field").show();
                }else{
                    $('.custom_free_trial_coupon_field').hide();
                    $(".coupon_amount_field").show();
                    $(".sub_discount_type_field").hide();
                    $(".subscription_discount_validity_field").hide();
                    $(".sub_discount_amount_field").hide();
                }
            });
        });
}(jQuery));