(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
			jQuery(document).ajaxSuccess(function(event, xhr, settings) {
                if (settings.url.indexOf('?wc-ajax=add_to_cart') !== -1) {
                    var productData = settings.data;
                    var productSku = getParameterValue(productData,'product_sku');
                    var productId = getParameterValue(productData,'product_id');
                    if(productId != ''){
                        jQuery.post(customAjaxUrl.ajax_url+"?action=wc_trigger_add_cart",{productId:productId,productSku:productSku}, function(data) {
                            //var responsedata = JSON.parse(data);
                        });
                    }
                }

            });
            jQuery( ".cart" ).on( "submit", function(e) {
                var dataString = jQuery(this).serialize();
                var buttonValue = jQuery(this).children('button').val();
                var productSku = '';
            	if(buttonValue != ""){
        			jQuery.post(customAjaxUrl.ajax_url+"?action=wc_trigger_add_cart",{productId:buttonValue,productSku:productSku}, function(data) {
                        //var responsedata = JSON.parse(data);
                    });
            	}
            });
        });
}(jQuery));

//Function is used to get the string parameter value from the string..
function getParameterValue(productData,searchParam) {
    var string = productData;
    var stringSplitVariable = string.split('&');
    var searchParameterName;
    var counter;
	for (counter = 0; counter < stringSplitVariable.length; counter++) {
        searchParameterName = stringSplitVariable[counter].split('=');
		if (searchParameterName[0] === searchParam) {
            return typeof searchParameterName[1] === undefined ? true : decodeURIComponent(searchParameterName[1]);
        }
    }
    return false;
}