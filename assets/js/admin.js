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
                if(menu_heading != ""){
                    var howManyMenus = $('.'+menu_heading+' li').length; 
                    if(howManyMenus == '1'){
                        //code to trigger the click event when only one submenu exist in main menu....
                        $('.'+menu_heading+'_active').trigger('click');
                    }
                    if(menu_heading == "import_products"){
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
                }
            });

            //code to change the main content on click of submenu of menus
            $('.sub-menu > li.sub-menu-expand > a').on('click', function(e) {
                var tab_id = $(this).attr('id');
                if(tab_id != "" && typeof tab_id != "undefined"){
                    jQuery(".ajax_loader").show();
                    jQuery(".tab_related_content").addClass('overlay');
                    $("li.sub-menu-expand a").removeClass("active-sub-menu");
                    
                    $(this).addClass("active-sub-menu");
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

                        //apply change icon rule on campaign goals "How this works" button..
                        if($("#collapseCampaignGoals").length){
                            applyCollapseRules('collapseCampaignGoals');
                        }

                        //apply data tables on coupons listing with coupon code...
                        if(jQuery("#coupon_listing_with_sku").length){
                            applyDatables("coupon_listing_with_sku");
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
                $("#application_settings").after('<span class="custom-icons"><i class="fa fa-check-circle" aria-hidden="true"></i></span>');
                swal({
                  title: "Authorization!",
                  text: "Application authentication done successfully.",
                  type: "success",
                  confirmButtonText: "OK"
                },
                function(isConfirm){
                  if (isConfirm) {
                    $('#import_products').trigger('click');
                  }
                });
            }

            //apply change icon rule on campaign goals "How this works" button..
            if($("#collapseCampaignGoals").length){
                applyCollapseRules('collapseCampaignGoals');
            }

            //this code is used to control the activate button enable and disable process on the basis of email value...
            $document.on("keyup","#pluginactivationemail",function(event) {
                var emailValue = $(this).val();
                var existingEmailValue = $("#activationEmail").val();
                if(existingEmailValue != "" && emailValue != ""){
                    if(emailValue == existingEmailValue){
                        $(".plugin_activation_btn").addClass('disable_anchor');
                    }else{
                        $(".plugin_activation_btn").removeClass('disable_anchor');
                    }
                }
            });
            
            //this code is used to control the activate button enable and disable process on the basis of plugin key value...
            $document.on("keyup","#pluginactivationkey",function(event) {
                var keyValue = $(this).val();
                var existingKeyValue = $("#activationKey").val();
                if(existingKeyValue != "" && keyValue != ""){
                    if(keyValue == existingKeyValue){
                        $(".plugin_activation_btn").addClass('disable_anchor');
                    }else{
                        $(".plugin_activation_btn").removeClass('disable_anchor');
                    }
                }
            });

            //this code is used to check whether the plugin is activated or not if activated then add "tick" icon in next of activation menu......
            if ($(".activated").length ) {
                $("#plugin_activation").after('<span class="custom-icons"><i class="fa fa-check-circle" aria-hidden="true"></i></span>');
            }

            //this code is used to check whether the plugin authentication is done or not if done then add "tick" icon in next of infusionsoft/keap setting menu......
            if ($(".authdone").length ) {
                $("#application_settings").after('<span class="custom-icons"><i class="fa fa-check-circle" aria-hidden="true"></i></span>');
            }

            //Match Products Tab : below code used to update the mapping of products........
            $document.on("change",".application_match_products_dropdown", function(event)
            {
                event.stopPropagation();
                //get woocommerce product id....
                var wcProductId = $(this).data('id');
                //get application product id with woocommerce product mapping set.........
                var applicationProductId = $(this).val();
                if(wcProductId != ""){
                    jQuery(".match_products_listing_class").addClass('overlay');
                    jQuery(".ajax_loader_match_products_related").show();
                    jQuery.post(ajax_object.ajax_url + "?action=wc_update_products_mapping&jsoncallback=x", {wcProductId: wcProductId,applicationProductId:applicationProductId}, function(data) {
                        jQuery(".match_products_listing_class").removeClass('overlay');
                        jQuery(".ajax_loader_match_products_related").hide();
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            swal("Updated!", 'Product mapping updated successfully.', "success");
                        }
                    });
                } 
            });
            
            //On click of "+" icon show the current product corresponding variations....
            $document.on("click",".exploder",function(event) {
                var productId = $(this).attr('id');//get parent product id....
                //check parent product id exist....
                if(productId != ''){
                    //get parent product relation with application product....
                    var matchProductId = $('select[name=wc_product_match_with_'+productId+'] option').filter(':selected').val();
                    //toggle class sucess with danger vice versa.....
                    $(this).toggleClass("btn-success btn-danger");
                    //change the icon of font awsome icons of button....
                    $(this).find('i').toggleClass('fa-plus fa-minus');
                    //check action...
                    var action = $(this).find('i').hasClass("fa-plus");
                    //on the basis of action proceed next.....
                    if(action){
                       $(".customvariations_"+productId).remove();
                    }else{
                        $(".customvariations_"+productId).remove();
                        $("#table_row_"+productId).after('<tr id="variation_loader_'+productId+'"><td colspan="5" style="text-align: center; vertical-align: middle;">Loading Variations......</td></tr>');   
                        jQuery.post( ajax_object.ajax_url + "?action=wc_get_product_variation",{productId: productId,matchProductId:matchProductId}, function(data) {
                            var responsedata = JSON.parse(data);
                            $("#variation_loader_"+productId).remove();
                            if(responsedata.status == "1") {
                                if(responsedata.variationsHtml != ""){
                                    $("#table_row_"+productId).after(responsedata.variationsHtml);
                                    applySelectTwo('application_match_products_dropdown');
                                }
                            }
                        });
                    }
                }
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
                onfocusout: false,
                onkeyup: false,
                ignore: ".ignore",
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
    }
}

//save application settings
function saveApplicationSettings(){
    var activationEmail = $("#activationEmail").val();
    var activationKey = $("#activationKey").val();siteUrl
    var currentSiteUrl = $("#siteUrl").val();
    var connectionType = $('input[name=applicationtype]:checked', '#application_settings_form').val();
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
                if(responsedata.licence_email != "" && responsedata.licence_email !== null){
                    $("#activationEmail").val('');
                    $("#activationEmail").val(responsedata.licence_email);
                }
                if(responsedata.licence_key != "" && responsedata.licence_key !== null){
                    $("#activationKey").val('');
                    $("#activationKey").val(responsedata.licence_key);
                }
                $("#plugin_activation").after('<span class="custom-icons" onclick="showMarkRelatedData(\'application_settings\')"><i class="fa fa-check-circle" aria-hidden="true"></i></span>');
                $('#application_settings').trigger('click');
            }else{
                $(".activation-details-error").show();
                $(".common_disable_class").addClass('leftMenusDisable');
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
        $checkClass = $("#trigger_tr_"+triggerid).attr('class');
        jQuery.post( ajax_object.ajax_url + "?action=wc_get_trigger_details",{triggerid:triggerid}, function(data) {
            var responsedata = JSON.parse(data);
            if(responsedata.status == "1") {
                jQuery("label.error").hide();
                jQuery("#edittriggerid").val(triggerid);
                jQuery("#edittriggerid").val(triggerid);
                if(responsedata.triggerGoalName != ""){
                    jQuery(".trigger_goal_name").html('');
                    jQuery(".trigger_goal_name").html('Edit ' + responsedata.triggerGoalName + ' Trigger');
                }
                if(responsedata.triggerIntegrationName != ""){
                    jQuery("#integrationname").val(responsedata.triggerIntegrationName);
                }
                if(responsedata.triggerCallName != ""){
                    jQuery("#callname").val(responsedata.triggerCallName);
                    if($checkClass!="" && $checkClass == 'readonly'){
                        $('#callname').attr('readonly', true);
                        $('#callname').addClass('ignore');
                    }else{
                        $('#callname').attr('readonly', false);
                        $('#callname').removeClass('ignore');
                    }
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
                if(responsedata.displayCallName != ""){
                    jQuery("#trigger_tr_"+trigger_id+' td#trigger_call_name_'+trigger_id).html(responsedata.displayCallName);
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
                        if ($('.all_products_checkbox_export').is(":checked"))
                        {
                            $('.all_products_checkbox_export').prop("checked", false);
                        }
                        $('.each_product_checkbox_export').prop("checked", false);
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
        //Campaign Goals Tab: apply datatables on products listing with sku..
        else if(tabel_id == 'products_listing_with_sku_'+SKU_LENGHT_SPECIFIC_PRODUCT 
                || tabel_id == 'products_listing_with_sku_'+SKU_LENGHT_REVIEW 
                    || tabel_id == 'products_listing_with_sku_'+SKU_LENGHT_CART_ITEM 
                        || tabel_id == 'coupon_listing_with_sku') {
            if(!$.fn.DataTable.isDataTable('#'+tabel_id))
            {
                $('#'+tabel_id).DataTable({
                    "pagingType": "simple_numbers",
                    "pageLength": 5,
                    "searching": false,
                    "bLengthChange" : false,
                    "bInfo":false,
                    "scrollX": false,
                    "ordering": false,
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

//This is a common function used to perform the copy clipboard content action........
function copyContent(elementid) {
  var elementDetails = document.getElementById(elementid);
  if(window.getSelection) {
    var selectWindow = window.getSelection();
    var eleTextRange = document.createRange();
    eleTextRange.selectNodeContents(elementDetails);
    selectWindow.removeAllRanges();
    selectWindow.addRange(eleTextRange);
    document.execCommand("Copy");
    var executeCommand = document.execCommand('copy',true);
  }else if(document.body.createTextRange) {
    var eleTextRange = document.body.createTextRange();
    eleTextRange.moveToElementText(elementDetails);
    eleTextRange.select();
    var executeCommand = document.execCommand('copy',true);
  }
  
}

//Onlick of products sku get the products listing with listing then show the popup....
function showProductsListing(length){
    jQuery("#products_sku_listing").html('');
    jQuery("#products_sku_listing").html('<tr class="text-center"><td colspan="3">Loading Records...</td></tr>');
    $("#productsListing").show();
    $(".common-table-class").attr("id", "products_listing_with_sku_"+length);
    jQuery.post( ajax_object.ajax_url + "?action=wc_get_products_listing",{length:length}, function(data) {
        var responsedata = JSON.parse(data);
        if(responsedata.status == "1") {
            if(responsedata.productsListing != ""){
                jQuery("#products_sku_listing").html('');
                jQuery("#products_sku_listing").html(responsedata.productsListing);
            }
            //apply data tables on products listing with sku...
            if(jQuery("#products_listing_with_sku_"+length).length){
                applyDatables("products_listing_with_sku_"+length);
            }
        }
    });
}