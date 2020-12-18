(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
			//check if option is enable or not on the basis of that show/hide the merchant id field....
		    if($(".methodEnable").prop('checked') == true){
			    $(".merchantClass").closest("tr").show();
				$(".processCreditCardEnable").closest("tr").show();	
			}else{
				$(".merchantClass").closest("tr").hide();
				$(".processCreditCardEnable").closest("tr").hide();	
	        	$(".subscriptionEnable").closest("tr").hide();

	        	$(".subscriptionEnable").prop('checked', false);
	        	$(".processCreditCardEnable").prop('checked', false);
			}
			
			//on change of enable/disable show hide the merchant id field....
		    $('.methodEnable').change(function() {
		        if(this.checked) {
		            $(".merchantClass").closest("tr").show();
		            $(".subscriptionEnable").closest("tr").hide();
		        	$(".processCreditCardEnable").closest("tr").show();	
		        }else{
		        	$(".merchantClass").closest("tr").hide();
		        	
		        	$(".processCreditCardEnable").closest("tr").hide();	
		        	$(".subscriptionEnable").closest("tr").hide();

		        	$(".subscriptionEnable").prop('checked', false);
		        	$(".processCreditCardEnable").prop('checked', false);
		        }
		    });

		    //check if option to process credit cards option is enable or not on the basis of that show/hide the woocommerce subscription field....
		    if($(".processCreditCardEnable").prop('checked') == true){
			    $(".subscriptionEnable").closest("tr").show();
			}else{
				$(".subscriptionEnable").closest("tr").hide();
				$(".subscriptionEnable").prop('checked', false);
			}
			
			//on change of process credit cards option show hide the woocommerce subscription field....
		    $('.processCreditCardEnable').change(function() {
		        if(this.checked) {
		            $(".subscriptionEnable").closest("tr").show();
		        }else{
		        	$(".subscriptionEnable").closest("tr").hide();
		        	$(".subscriptionEnable").prop('checked', false);	
		        }
		    });
        });
}(jQuery));