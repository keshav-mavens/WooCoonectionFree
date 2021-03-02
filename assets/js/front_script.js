(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
		    jQuery( document.body ).on( 'added_to_cart', function(event, fragments, cart_hash, button){
                var product_sku   = button.data('product_sku');  // Get the product sku
                if(product_sku !== ""){
                    console.log(product_sku);
                }
            });
        });
}(jQuery));