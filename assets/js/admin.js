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
                  $(this).removeClass('active');
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
                        });
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
                    });
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
                                }else if (target_tab_id == '#table_match_products') {
                                    $(target_tab_id+"_listing").html('');
                                    $(target_tab_id+"_listing").html(responsedata.latestHtml);
                                }    
                            }
                        }
                    });
                }
            });

            //Check if "response" done....
            var checkResponse = getQueryParameter('response');
            if(checkResponse != "" && checkResponse == "1"){
                swal("Saved!", 'Application authentication done successfully.', "success");
            }


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
                        required: 'Please enter trigger integration name!',
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
            }, 4000);
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