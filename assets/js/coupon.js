(function ($){
	"use strict";
	/* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
     var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
        	//by default referral partner input field is hidden....
        	$(".referral_partner_id_field").hide();
        	//check if linked with affiliate checkbox is checked..
        	if($("#linked_with_affiliate").is(':checked')){
        		//then show the referral partner field...
        		$(".referral_partner_id_field").show();
        	}
        	//else hide the referral partner input field....
        	else{
        		$(".referral_partner_id_field").hide();
        	}

        	//on change of assoicated contact with affiliate field.....
        	$("#linked_with_affiliate").on('change',function(event){
        		event.preventDefault();
        		//if field is checked then show the referral partner id field....
        		if ($(this).is(':checked')) {
        			$(".referral_partner_id_field").show();
        		}
        		//else hide the referral partner id field....
        		else{
        			$(".referral_partner_id_field").hide();
        		}
        	});

        	//validate the referral partner id is a number or not.....
        	$("#referral_partner_id").on('keypress keyup blur' ,function(event){
    			$(this).val($(this).val().replace(/[^\d].+/,""));
	       		//check if press key not in digit creteria......
	       		if((event.which<48 || event.which>57)){
	       			//then prevent to enter the anothe characters.....
	       			event.preventDefault();
	       		}
	       	});
        });
}(jQuery));