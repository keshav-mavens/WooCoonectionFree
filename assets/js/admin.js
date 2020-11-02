(function ($) {
    "use strict";
    /* ------------------------------------------------------------------------- *
     * COMMON VARIABLES
     * ------------------------------------------------------------------------- */
    var $wn = $(window),
        $document = $(document),
        $body = $('body');
        $(function () {
            //code to toggle the side menus..
            $('.accordian-list > li.expanded > a').on('click', function(e) {
                e.preventDefault();
                if($(this).next('ul.sub-menu').is(':visible')) {
                  $(this).next('ul.sub-menu').slideUp();
                } else {
                  $('.accordian-list > li.expanded > a').removeClass('active');
                  $(this).addClass('active');
                  $('.accordian-list > li.expanded > a').next('ul.sub-menu').slideUp();
                  $(this).next('ul.sub-menu').slideToggle();
                }
                var menu_heading = $(this).attr('id');
                if(menu_heading != "" && menu_heading == "import_products"){
                    jQuery(".ajax_loader").show();
                    jQuery(".tab_related_content").addClass('overlay');
                    var tab_id = 'import_products';
                    if(tab_id != ""){
                        jQuery.post( ajax_object.ajax_url + "?action=wc_load_tab_main_content",{tab_id:tab_id}, function(data) {
                            jQuery(".ajax_loader").hide();
                            if(!$('.tab_related_content').is(':visible')){
                               $(".tab_related_content").show(); 
                            }
                            $(".tab_related_content").html('');
                            $(".tab_related_content").html(data);
                            jQuery(".tab_related_content").removeClass('overlay');
                            if(jQuery("#export_products_listing").length){
                                applyDatables("export_products_listing");
                            }
                            //add select 2 for woocommerce products field
                            if($(".wc_iskp_products_dropdown").length){
                                applySelectTwo('wc_iskp_products_dropdown');
                            }
                        });
                    }
                    //Check if "response" done....
                    var checkResponse = getQueryParameter('response');
                    if(checkResponse != ""){
                        var uri = window.location.toString();
                        if (uri.indexOf("&") > 0) {
                            var clean_uri = uri.substring(0, uri.indexOf("&"));
                            window.history.replaceState({}, document.title, clean_uri);
                        }
                    }
                }
            });

            //code to change the main content on click of submenu of menus
            $('.sub-menu > li.sub-menu-expand > a').on('click', function(e) {
                jQuery(".ajax_loader").show();
                jQuery(".tab_related_content").addClass('overlay');
                $("li.sub-menu-expand a").removeClass("active-sub-menu");
                
                $(this).addClass("active-sub-menu");
                var tab_id = $(this).attr('id');
                if(tab_id != ""){
                    jQuery.post( ajax_object.ajax_url + "?action=wc_load_tab_main_content",{tab_id:tab_id}, function(data) {
                        jQuery(".ajax_loader").hide();
                        $(".import_tab_content").hide();
                        if(!$('.tab_related_content').is(':visible')){
                           $(".tab_related_content").show(); 
                        }
                        $(".tab_related_content").html('');
                        $(".tab_related_content").html(data);
                        jQuery(".tab_related_content").removeClass('overlay');
                        
                        //validate a activation_setup_form form.....
                        if($('#activation_setup_form').length){
                            validateForms('activation_setup_form');
                        }

                        //apply change icon rule on campaign goals "How it works" button..
                        if($("#collapseCampaignGoals").length){
                            applyCollapseRules('collapseCampaignGoals');
                        }

                        //add select 2 for wordpress posts field
                        if($(".redirectpostsselect").length){
                            applySelectTwo('redirectpostsselect');
                        }

                        //add select 2 for wordpress pages field
                        if($(".redirectpagesselect").length){
                            applySelectTwo('redirectpagesselect');
                        }

                        //add select 2 for wordpress pages field
                        if($(".redirectcartproductsselect").length){
                            applySelectTwo('redirectcartproductsselect');
                        }

                        //add select 2 for wordpress pages field
                        if($(".redirectcartcategoriesselect").length){
                            applySelectTwo('redirectcartcategoriesselect');
                        }

                        //validate a activation_setup_form form.....
                        if($('#thank_default_form').length){
                            validateForms('thank_default_form');
                        }

                        //validate a activation_setup_form form.....
                        if($('#thank_override_form_product').length){
                            validateForms('thank_override_form_product');
                        }

                        //validate a activation_setup_form form.....
                        if($('#thank_override_form_product_cat').length){
                            validateForms('thank_override_form_product_cat');
                        }
                    });
                    //Check if "response" done....
                    var checkResponse = getQueryParameter('response');
                    if(checkResponse != ""){
                        var uri = window.location.toString();
                        if (uri.indexOf("&") > 0) {
                            var clean_uri = uri.substring(0, uri.indexOf("&"));
                            window.history.replaceState({}, document.title, clean_uri);
                        }
                    }
                }
            });

            //Responsive Navigation : Toggle Menu
            $(".toggle-menus").click(function () {
                $(".main-menu-wc").toggle(500);
            });
            
            //change the wooconnection plugin image on hover....
            $('.toplevel_page_wooconnection-admin').hover(function () {
                $(this).find('img').attr('src', function (i, src) {
                    if(src == WOOCONNECTION_PLUGIN_URL+'assets/images/icon-grey-new.png'){
                        return src.replace('icon-grey-new.png', 'icon-blue.png') 
                    }
                });
            },  function () {
                $(this).find('img').attr('src', function (i, src) {
                    if(src == WOOCONNECTION_PLUGIN_URL+'assets/images/icon-blue.png'){
                        return src.replace('icon-blue.png', 'icon-grey-new.png')
                    }
                });
            });
            
            //on click of "*" icon of override delete the current override
            $document.on("click",".wizard > li",function(event) {
                var progress_tab_id = $(this).attr('id');
                if(progress_tab_id != ""){
                    var result = progress_tab_id.split('progress_');
                    if(result[1] != ""){
                        $("#"+result[1]).trigger('click');
                    }
                    if(result[1] == "automation"){
                        $("#campaign_goals").trigger('click');
                    }    
                }
            });

             //Export Tab : check all products checkbox rule....
            $document.on("click",".all_products_checkbox_export",function(event) {
                if ($(this).is(":checked"))
                {
                    $('.each_product_checkbox_export').prop("checked", true);
                }
                else
                {
                    $('.each_product_checkbox_export').prop("checked", false);
                }
            });
            
            //Export Tab : on change of select box of woocommerce products mark checkbox checked or unchecked on the basis of select value.....
            $document.on("click",".each_product_checkbox_export",function(event) {
                if ($('.all_products_checkbox_export').is(":checked"))
                {
                    $('.all_products_checkbox_export').prop("checked", false);
                }
            });

            //On click of import tabs change the content of corresponding tab.......
            $document.on("click",".custom-nav-tabs a",function(event) {
                var target_tab_id = $(this).attr('href');
                jQuery(".ajax_loader").show();
                jQuery(".tab_related_content").addClass('overlay');
                if(target_tab_id != ""){
                    $(target_tab_id+"_listing").html('');
                    $(target_tab_id+"_listing").html('<p class="heading-text" style="text-align:center;">Loading Data....</p>');
                    jQuery.post( ajax_object.ajax_url + "?action=wc_load_import_export_tab_main_content",{target_tab_id:target_tab_id}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            jQuery(".ajax_loader").hide();
                            jQuery(".tab_related_content").removeClass('overlay');
                            if(responsedata.latestHtml != ""){
                                if (target_tab_id == '#table_export_products') {
                                    $(target_tab_id+"_listing").html('');
                                    $(target_tab_id+"_listing").html(responsedata.latestHtml);
                                    //apply datatable on export products listing
                                    if(jQuery("#export_products_listing").length){
                                        applyDatables("export_products_listing");
                                    }
                                    //add select 2 for woocommerce products field
                                    if($(".wc_iskp_products_dropdown").length){
                                        applySelectTwo('wc_iskp_products_dropdown');
                                    }
                                }else if (target_tab_id == '#table_match_products') {
                                    $(target_tab_id+"_listing").html('');
                                    $(target_tab_id+"_listing").html(responsedata.latestHtml);
                                    //apply datatable on export products listing
                                    if(jQuery("#match_products_listing").length){
                                        applyDatables("match_products_listing");
                                    }
                                    //add select 2 for woocommerce products field
                                    if($(".application_match_products_dropdown").length){
                                        applySelectTwo('application_match_products_dropdown');
                                    }
                                }    
                            }
                        }
                    });
                }
            });

            //Check if "response" done....
            var checkResponse = getQueryParameter('response');
            if(checkResponse != "" && checkResponse == "1"){
                swal("Authorization!", 'Application authentication done successfully.', "success");
            }

            //Match Products Tab : check all products checkbox rule....
            $document.on("click",".all_products_checkbox_match",function(event) {
                if ($(this).is(":checked"))
                {
                    $('.each_product_checkbox_match').prop("checked", true);
                }
                else
                {
                    $('.each_product_checkbox_match').prop("checked", false);
                }
            });
            
            //Match Products Tab : on change of select box of woocommerce products mark checkbox checked or unchecked on the basis of select value.....
            $document.on("click",".each_product_checkbox_match",function(event) {
                if ($('.all_products_checkbox_match').is(":checked"))
                {
                    $('.all_products_checkbox_match').prop("checked", false);
                }
            });


            //on change of override redirect condition show hide the corresponding fields....
            $document.on("change","#overrideredirecturltype",function(event) {
                event.stopPropagation();
                var selectedCondition = $(this).val();
                if (selectedCondition == DEFAULT_WORDPRESS_POST) {
                    $(".redirect-type-common").hide();
                    $("#redirect-type-post").show();
                    //$(".redirectcartproductsselect").val("").trigger("change");
                }else if (selectedCondition == DEFAULT_WORDPRESS_PAGE) {
                    $(".redirect-type-common").hide();
                    $("#redirect-type-page").show();
                    //$(".redirectcartcategoriesselect").val("").trigger("change");
                }else if(selectedCondition == DEFAULT_WORDPRESS_CUSTOM_URL){
                    $(".redirect-type-common").hide();
                    $("#redirect-type-custom-url").show();
                }
            });



            //on click of edit icon of override ul show the edit override form.....
            $document.on("click",".controls .edit_default_thankpage_override",function(event) {
                event.stopPropagation();
                $(".thankyou_default_title").html('Edit Default Thankyou Page');
                jQuery.post( ajax_object.ajax_url + "?action=wc_get_thankyou_default_override",{option:'default_thankyou_details'}, function(data) {
                    var responsedata = JSON.parse(data);
                    if(responsedata.status == "1") {
                        if(responsedata.redirectType != "" && responsedata.redirectType !== null){
                            jQuery("#overrideredirecturltype").val(responsedata.redirectType);
                            $("#overrideredirecturltype").trigger("change");
                            if(responsedata.redirectType == DEFAULT_WORDPRESS_PAGE){
                                if(responsedata.redirectValue != "" && responsedata.redirectValue !== null){
                                    jQuery("#redirectwordpresspage").val(responsedata.redirectValue);
                                }
                            }
                            else if (responsedata.redirectType == DEFAULT_WORDPRESS_POST)
                            {
                                if(responsedata.redirectValue != "" && responsedata.redirectValue !== null){
                                    jQuery("#redirectwordpresspost").val(responsedata.redirectValue);
                                }
                            }
                            else{
                                if(responsedata.redirectValue != "" && responsedata.redirectValue !== null){
                                    jQuery("#customurl").val(responsedata.redirectValue);
                                }
                            }   
                        }
                    }
                });
                $('.defaultoverride,.main_rendered_thank_overrides').toggle();
            });
        
            //on click of edit icon of override ul show the edit override form.....
            $document.on("click",".controls .add_product_rules",function(event) {
                event.stopPropagation();
                //$(".thankyou_default_title").html('Edit Default Thankyou Page');
                $('.productoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of edit icon of override ul show the edit override form.....
            $document.on("click",".controls .add_product_category_rules",function(event) {
                event.stopPropagation();
                //$(".thankyou_default_title").html('Edit Default Thankyou Page');
                $('.productcatoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of cancel button show the initial listing of overrides...
            $document.on("click",".restore_overrides",function(event) {
                event.stopPropagation();
                var current_override_id = $(this).data('id');
                if(current_override_id !== '' && current_override_id !== null){
                    $("#"+current_override_id)[0].reset();
                    $("#"+current_override_id).validate().resetForm();
                }
                $('.main_rendered_thank_overrides').toggle();
                $('.hide').hide();
            });

            //on click of edit icon of override ul show the edit override form.....
            $document.on("click",".controls .edit_product_rule_override",function(event) {
                event.stopPropagation();
                $(".thankyou_override_title_product").html('Edit Product Thankyou Page Override');
                $('.productoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of edit icon of override ul show the edit override form.....
            $document.on("click",".controls .edit_product_category_rule_override",function(event) {
                event.stopPropagation();
                $(".thankyou_override_title_product_cat").html('Edit Product Thankyou Page Category Override');
                $('.productcatoverride,.main_rendered_thank_overrides').toggle();
            });

        });
}(jQuery));

//common function is used to validate the forms by form id....
function validateForms(form){
    //check form id is not empty
    if(form != ""){
        //check form is plugin activation form then validate it..
        if(form == "activation_setup_form"){
            $("#"+form).validate({
                rules:{
                      pluginactivationemail: "required",
                      pluginactivationkey: "required"
                    },
                messages:{
                    pluginactivationemail: {
                        required: 'Please enter activation email!'
                    },
                    pluginactivationkey: {
                        required: 'Please enter activation key!'
                    }
                }
            });
        }
        //check form of trigger deatils validate it..
        if(form == "trigger_details_form"){
            
            jQuery.validator.addMethod("alphanumeric", function(value, element) {
                return this.optional(element) || /^[\w.]+$/i.test(value);
            });

            $("#"+form).validate({
                rules:{
                        integrationname: {
                            required: true,
                            alphanumeric: true,
                        },
                        callname: {
                            required: true,
                            alphanumeric: true,
                        }
                    },
                messages:{
                    integrationname: {
                        required: 'Please enter trigger integration name!',
                        alphanumeric: 'Only alphanumeric characters are allowed in trigger integration name!',
                    },
                    callname: {
                        required: 'Please enter trigger call name!',
                        alphanumeric: 'Only alphanumeric characters are allowed in trigger call name!',
                    }
                }
            });
        }
        
        //check form is thank override form then validate it..
        if(form == "thank_default_form"){
            $("#"+form).validate({
                rules:{
                      redirectwordpresspost: "required",
                      redirectwordpresspage: "required",
                      customurl: "required",
                    },
                messages:{
                    redirectwordpresspost: {
                        required: 'Please select the post for redirection!'
                    },
                    redirectwordpresspage: {
                        required: 'Please select the page for redirection!'
                    },
                    customurl: {
                        required: 'Please enter the custom url for redirection'
                    },
                }
            }); 
        }

        //check form is thank override form then validate it..
        if(form == "thank_override_form_product"){
            $("#"+form).validate({
                rules:{
                      procductoverridename: "required",
                      "redirectcartproducts[]": "required",
                    },
                messages:{
                    procductoverridename: {
                        required: 'Please enter the name of override'
                    },
                    "redirectcartproducts[]": {
                        required: 'Please select the cart products!'
                    },
                }
            }); 
        }

        //check form is thank override form then validate it..
        if(form == "thank_override_form_product_cat"){
            $("#"+form).validate({
                rules:{
                      productcatoverridename: "required",
                      "redirectcartcategories[]": "required",
                    },
                messages:{
                    productcatoverridename: {
                        required: 'Please enter the name of override'
                    },
                    "redirectcartcategories[]": {
                        required: 'Please select the cart categories!'
                    },
                }
            }); 
        }

    }
}

//save application settings
function saveApplicationSettings(){
    var activationEmail = $("#activationEmail").val();
    var activationKey = $("#activationKey").val();siteUrl
    var currentSiteUrl = $("#siteUrl").val();
    var connectionType = $("#applicationtype").val();
    var formData = {userEmail:activationEmail,userPluginKey:activationKey,requestWebUrl:currentSiteUrl,connectionType:connectionType}; //Array 
    $.ajax({
        url : ADMIN_REMOTE_URL+"authentication.php",
        type: "POST",
        data : formData,
        success: function(data, textStatus, jqXHR)
        {
           var responsedata = JSON.parse(data);
           if(responsedata.redirectUrl != ""){
               window.open(responsedata.redirectUrl);
           }
        }
    });
}

//Below one function is related to plugin activation tab...
//activate wooconnection plugin....
function activateWcPlugin(){
    if($('#activation_setup_form').valid()){
        if($(".activation-details-error").is(":visible") || $(".activation-details-success").is(":visible")){
            $(".activation-details-error").hide();
            $(".activation-details-success").hide();
            $(".pluginActivation").show();
        }else{
            $(".pluginActivation").show();    
        }
        $('.plugin_activation_btn').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=activate_wooconnection_plugin",$('#activation_setup_form').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".pluginActivation").hide();
            if(responsedata.status == "1") {
                $("li").removeClass( "leftMenusDisable" );
                if(responsedata.successmessage != "" && responsedata.successmessage !== null){
                    $(".activation-details-success").html('');
                    $(".activation-details-success").html(responsedata.successmessage);
                    $(".activation-details-success").show();
                }else{
                    $(".activation-details-success").show();
                }
            }else{
                $(".activation-details-error").show();
                if(responsedata.errormessage != "" && responsedata.errormessage !== null){
                    $(".activation-details-error").html('');
                    $(".activation-details-error").html(responsedata.errormessage);
                }else{
                    $(".activation-details-error").html('');
                    $(".activation-details-error").html('Something Went Wrong');
                }
            }
            setTimeout(function()
            {
                $('.activation-details-success, .activation-details-error').fadeOut("slow");
                $('.plugin_activation_btn').removeClass("disable_anchor");
            }, 3000);
        });
    }
}

//show the popup to edit the trigger deatils....
function popupEditDetails(triggerid){
    if(triggerid != ""){
        jQuery.post( ajax_object.ajax_url + "?action=wc_get_trigger_details",{triggerid:triggerid}, function(data) {
            var responsedata = JSON.parse(data);
            if(responsedata.status == "1") {
                jQuery("#edittriggerid").val(triggerid);
                jQuery("#edittriggerid").val(triggerid);
                if(responsedata.triggerGoalName != ""){
                    jQuery(".trigger_goal_name").html('');
                    jQuery(".trigger_goal_name").html(responsedata.triggerGoalName);
                }
                if(responsedata.triggerIntegrationName != ""){
                    jQuery("#integrationname").val(responsedata.triggerIntegrationName);
                }
                if(responsedata.triggerCallName != ""){
                    jQuery("#callname").val(responsedata.triggerCallName);
                }
                $("#editTriggerDetails").show();
                //validate a application_settings_form form.....
                if($('#trigger_details_form').length){
                    validateForms('trigger_details_form');
                }
            }
        });
    }
}

//update trigger details...
function updateTriggerdetails(){
    if($('#trigger_details_form').valid()){
        if($(".trigger-details-error").is(":visible") || $(".trigger-details-success").is(":visible")){
            $(".trigger-details-error").hide();
            $(".trigger-details-success").hide();
            $(".savingTriggerDetails").show();
        }else{
            $(".savingTriggerDetails").show();    
        }
        $('.save_trigger_details').addClass("disable_anchor");
        var trigger_id = jQuery("#edittriggerid").val();
        jQuery.post( ajax_object.ajax_url + "?action=wc_update_trigger_details",$('#trigger_details_form').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".savingTriggerDetails").hide();
            if(responsedata.status == "1") {
                $('.save_trigger_details').removeClass("disable_anchor");
                $("#editTriggerDetails").hide();
                swal("Saved!", 'Trigger details updated Successfully.', "success");
                if(responsedata.triggerIntegrationName != ""){
                   jQuery("#trigger_tr_"+trigger_id+' td#trigger_integration_name_'+trigger_id).html(responsedata.triggerIntegrationName);
                }
                if(responsedata.triggerCallName != ""){
                    jQuery("#trigger_tr_"+trigger_id+' td#trigger_call_name_'+trigger_id).html(responsedata.triggerCallName);
                }
            }else{
                $(".trigger-details-error").show();
                $(".trigger-details-error").html('');
                $(".trigger-details-error").html('Something Went Wrong');
            }
            setTimeout(function()
            {
                $('.trigger-details-error').fadeOut("slow");
                $('.save_trigger_details').removeClass("disable_anchor");
            }, 4000);
        });
    }
}

//get the response from query string....
function getQueryParameter(qspar){
    var currentUrl = window.location.href;
    var url = new URL(currentUrl);
    var email = url.searchParams.get(qspar);
    return email;
}

//hide model by model id....
function hideCustomModel(modelId){
    if(modelId != ""){
        $("#"+modelId).hide();
    }
}

//comon function is used to apply a datatables by table id.....
function applyDatables(tabel_id){
    if(tabel_id != ""){
        //Export Tab: apply datatables on products listing..
        if (tabel_id == 'export_products_listing') {
            if(!$.fn.DataTable.isDataTable('#'+tabel_id))
            {
                $('#'+tabel_id).DataTable({
                    "pagingType": "simple_numbers",
                    "pageLength": 10,
                    "searching": false,
                    "bLengthChange" : false,
                    "bInfo":false,
                    "scrollX": false,
                    "ordering": false,
                    drawCallback: function(dt) {
                      applySelectTwo('wc_iskp_products_dropdown');
                    }
                });
            }
        }
        //Match Tab: apply datatables on products listing..
        else if (tabel_id == 'match_products_listing') {
            if(!$.fn.DataTable.isDataTable('#'+tabel_id))
            {
                $('#'+tabel_id).DataTable({
                    "pagingType": "simple_numbers",
                    "pageLength": 10,
                    "searching": false,
                    "bLengthChange" : false,
                    "bInfo":false,
                    "scrollX": false,
                    "ordering": false,
                    drawCallback: function(dt) {
                      applySelectTwo('application_match_products_dropdown');
                    }
                });
            }
        }
    }
}

//common function to apply a select2
function applySelectTwo(element){
    if(element != ""){
        //Export Tab: apply select 2 on infusionsoft products tab..
        if(element == 'wc_iskp_products_dropdown'){
            $("."+element).select2({
            });    
        }
        //Match Products Tab: apply select 2 on infusionsoft products tab..
        if(element == 'application_match_products_dropdown'){
            $("."+element).select2({
            });    
        }
        //add select 2 for products field on thankypu override form..
        if(element == 'redirectpostsselect'){
            $("."+element).select2({
                placeholder: 'Select Post',
            });    
        }
        //add select 2 for Categories field on thankypu override form..
        if(element == 'redirectpagesselect'){
            $("."+element).select2({
                placeholder: 'Select Page',
            });    
        } 
        //add select 2 for Categories field on thankypu override form..
        if(element == 'redirectcartproductsselect'){
            $("."+element).select2({
                placeholder: 'Select Cart Products',
                multiple : true,
            });    
        }
        //add select 2 for Categories field on thankypu override form..
        if(element == 'redirectcartcategoriesselect'){
            $("."+element).select2({
                placeholder: 'Select Cart Categories',
                multiple : true,
            });    
        }
        
    }
}

//On click of export products button send ajax to export products and on sucess update the html....
function wcProductsExport(){
    var checkProducts = checkSelectedProducts('export_products_listing_class','allproductsexport');
    var checkSelectedProductsCount = checkProducts.length;//console.log(checkProducts);
    if(checkSelectedProductsCount == 0){
        $(".export-products-error").html('You need to select atleast one product to export.');
        $(".export-products-error").show();
    }else{
        $(".export-products-error").hide();
        $(".exportProducts").show();
        $('.export_products_btn').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_export_wc_products",$('#wc_export_products_form').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".exportProducts").hide();
            if(responsedata.status == "1") {
                $('.export_products_btn').removeClass("disable_anchor");
                if(responsedata.latestExportProductsHtml != ""){
                     $('.export_products_listing_class').html();
                     $('.export_products_listing_class').html(responsedata.latestExportProductsHtml);
                }
                //apply datatable on export products listing
                if(jQuery("#export_products_listing").length){
                    applyDatables("export_products_listing");
                }

                //add select 2 for woocommerce products field
                if($(".wc_iskp_products_dropdown").length){
                    applySelectTwo('wc_iskp_products_dropdown');
                }
                swal("Saved!", 'Products exported successfully.', "success");
            }else{
                $(".export-products-error").show();
                $(".export-products-error").html('Something Went Wrong.');
                setTimeout(function()
                {
                    $('.export-products-error').fadeOut("slow");
                    $('.export_products_btn').removeClass("disable_anchor");
                }, 3000);
            }
        });
    }
    setTimeout(function()
    {
        $('.export-products-error').fadeOut("slow");
    }, 3000);
}

//On click of update products mapping button send ajax to update mapping of products and on sucess update the html....
function wcProductsMapping(){
    var checkProducts = checkSelectedProducts('match_products_listing_class','allproductsmatch');
    var checkSelectedProductsCount = checkProducts.length;//console.log(checkProducts);
    if(checkSelectedProductsCount == 0){
        $(".match-products-error").html('You need to select atleast one product to update mapping.');
        $(".match-products-error").show();
    }else{
        $(".match-products-error").hide();
        $(".matchProducts").show();
        $('.match_products_btn').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_update_products_mapping",$('#wc_match_products_form').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".matchProducts").hide();
            if(responsedata.status == "1") {
                $('.match_products_btn').removeClass("disable_anchor");
                if(responsedata.latestExportProductsHtml != ""){
                     $('.match_products_listing_class').html();
                     $('.match_products_listing_class').html(responsedata.latestExportProductsHtml);
                }
                
                //apply datatable on export products listing
                if(jQuery("#match_products_listing").length){
                    applyDatables("match_products_listing");
                }

                //add select 2 for woocommerce products field
                if($(".application_match_products_dropdown").length){
                    applySelectTwo('application_match_products_dropdown');
                }
                $('.all_products_checkbox_match').prop("checked", false);
                $('.each_product_checkbox_match').prop("checked", false);
                swal("Saved!", 'Products mapping updated successfully.', "success");
            }else{
                $(".match-products-error").show();
                $(".match-products-error").html('Something Went Wrong.');
                setTimeout(function()
                {
                    $('.match-products-error').fadeOut("slow");
                    $('.match_products_btn').removeClass("disable_anchor");
                }, 3000);
            }
        });
    }
    setTimeout(function()
    {
        $('.match-products-error').fadeOut("slow");
    }, 3000);
}

//This is a common function used to check whether the product is selected or not for import,export,mapping....
function checkSelectedProducts($class,$except){
    var checkProducts = $("."+$class+" input:checkbox:checked").map(function(){
                            if($(this).val() != $except){ return $(this).val(); }
                        }).get();
    return checkProducts;
}

//on collapse div change the icon of button done.....
function applyCollapseRules(div_id){
    if(div_id != ""){
        $('#'+div_id).on('shown.bs.collapse', function() {
            $("#icon_"+div_id).addClass('fa-caret-up').removeClass('fa-caret-down');
        });
        $('#'+div_id).on('hidden.bs.collapse', function() {
           $("#icon_"+div_id).addClass('fa-caret-down').removeClass('fa-caret-up');
        });    
    }
}

//save the thank you override.....
function saveThanksDefaultOverride(){
    if($('#thank_default_form').valid()){
        if($(".override-error").is(":visible") || $(".override-success").is(":visible")){
            $(".override-error").hide();
            $(".override-success").hide();
            $(".savingDefaultOverrideDetails").show();
        }else{
            $(".savingDefaultOverrideDetails").show();    
        }
        $('.save_thank_you_default_override').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_thanks_default_override",$('#thank_default_form').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".savingDefaultOverrideDetails").hide();
            if(responsedata.status == "1") {
                $('.defaultoverride,.main_rendered_thank_overrides').toggle();
                $('.save_thank_you_default_override').removeClass("disable_anchor");
            }else{
                $(".override-error").show();
                $(".override-error").html('Something Went Wrong');
                setTimeout(function()
                {
                    $('.override-error').fadeOut("slow");
                    $('.save_thank_you_default_override').removeClass("disable_anchor");
                }, 3000);
            }
        });
    }  
}


//save the thank you override.....
function saveThanksProductOverride(){
    if($('#thank_override_form_product').valid()){
        if($(".override-error").is(":visible") || $(".override-success").is(":visible")){
            $(".override-error").hide();
            $(".override-success").hide();
            $(".savingProductOverrideDetails").show();
        }else{
            $(".savingProductOverrideDetails").show();    
        }
        $('.save_thank_you_product_override').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_thanks_product_override",$('#thank_override_form_product').serialize(), function(data) {
            // var responsedata = JSON.parse(data);
            // $(".savingDefaultOverrideDetails").hide();
            // if(responsedata.status == "1") {
            //     $('.defaultoverride,.main_rendered_thank_overrides').toggle();
            //     $('.save_thank_you_default_override').removeClass("disable_anchor");
            //     //loading_thanks_overrides();
            // }else{
            //     $(".override-error").show();
            //     $(".override-error").html('Something Went Wrong');
            //     setTimeout(function()
            //     {
            //         $('.override-error').fadeOut("slow");
            //         $('.save_thank_you_default_override').removeClass("disable_anchor");
            //     }, 3000);
            // }
        });
    }  
}

//save the thank you override.....
function saveThanksProductCatOverride(){
    if($('#thank_override_form_product_cat').valid()){
        if($(".override-error").is(":visible") || $(".override-success").is(":visible")){
            $(".override-error").hide();
            $(".override-success").hide();
            $(".savingProductCatOverrideDetails").show();
        }else{
            $(".savingProductCatOverrideDetails").show();    
        }
        $('.save_thank_you_product_cat_override').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_thanks_product_category_override",$('#thank_override_form_product_cat').serialize(), function(data) {
            // var responsedata = JSON.parse(data);
            // $(".savingDefaultOverrideDetails").hide();
            // if(responsedata.status == "1") {
            //     $('.defaultoverride,.main_rendered_thank_overrides').toggle();
            //     $('.save_thank_you_default_override').removeClass("disable_anchor");
            //     //loading_thanks_overrides();
            // }else{
            //     $(".override-error").show();
            //     $(".override-error").html('Something Went Wrong');
            //     setTimeout(function()
            //     {
            //         $('.override-error').fadeOut("slow");
            //         $('.save_thank_you_default_override').removeClass("disable_anchor");
            //     }, 3000);
            // }
        });
    }  
}