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
                var product_sku   = button.data('product_sku'),  // Get the product sku
                if(product_sku !== ""){
                    console.log(product_sku);
                }else{
                    console.log('===slug is not exist');
                }
            });
        });
}(jQuery));

//Function is used to get the string parameter value from the string..
// function getParameterValue(productData,searchParam) {
//     var string = productData;
//     var stringSplitVariable = string.split('&');
//     var searchParameterName;
//     var counter;
// 	for (counter = 0; counter < stringSplitVariable.length; counter++) {
//         searchParameterName = stringSplitVariable[counter].split('=');
// 		if (searchParameterName[0] === searchParam) {
//             return typeof searchParameterName[1] === undefined ? true : decodeURIComponent(searchParameterName[1]);
//         }
//     }
//     return false;
// }