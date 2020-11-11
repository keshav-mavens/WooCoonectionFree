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
                        
                        //apply change icon rule on campaign goals "How this works" button..
                        if($("#collapseCampaignGoals").length){
                            applyCollapseRules('collapseCampaignGoals');
                        }

                        //validate a "add_custom_field_form" form.....
                        if($('#add_custom_field_form').length){
                            validateForms('add_custom_field_form');
                        }

                        //validate a "form_cfield_group" form.....
                        if($('#form_cfield_group').length){
                            validateForms('form_cfield_group');
                        }

                        //Custom fields Group : Load fields group and its custom fields...
                        if(jQuery(".custom_fields_main_html").length) {
                            loadingCustomFields();
                        }

                        //validate a "form_cfield_group" form.....
                        if($('#form_cfield').length){
                            validateForms('form_cfield');
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

            // //On change of form type from custom fields popup form change the custom fields related tabs... 
            // $document.on("change","#cfFormType", function(event)
            // {
            //     event.stopPropagation();
            //     var selectedFormType = $(this).children("option:selected").attr('id');
            //     if(selectedFormType != "" && selectedFormType !== null){
            //         $(".custom_field_header").hide();
            //         jQuery("#cftab").html('<option value="">Select Tab</option>');
            //         jQuery.post( ajax_object.ajax_url + "?action=wc_cf_form_type_tabs",{selectedFormType:selectedFormType}, function(data) {
            //             var responsedata = JSON.parse(data);
            //             if(responsedata.status == "1") {
            //                 $("#cftab").html(responsedata.tabsHtml);
            //             }
            //             $(".custom_field_header").show();
            //         });
            //     }
            // });

            // //On change of custom field tab from custom fields popup form change the custom fields related headers...
            // $document.on("change","#cftab", function(event)
            // {
            //     event.stopPropagation();
            //     var selectedTabType = $(this).children("option:selected").val();
            //     if(selectedTabType != "" && selectedTabType !== null){
            //         jQuery("#cfheader").html('<option value="">Select Header</option>');
            //         jQuery.post( ajax_object.ajax_url + "?action=wc_cf_tab_headers",{selectedTabType:selectedTabType}, function(data) {
            //             var responsedata = JSON.parse(data);
            //             if(responsedata.status == "1") {
            //                 $("#cfheader").html(responsedata.headerHtml);
            //             }
            //         });
            //     }
            // });

            $document.on("click",".addcfieldgroup",function(event) {
                event.stopPropagation();
                jQuery("#cfieldgroupid").val('');
                $(".cfieldgrouptitle").html('Create Custom Fields Group');
                $('.customfieldgroup,.custom_fields_main_html').toggle();
                $("#form_cfield_group")[0].reset();
                $("#form_cfield_group").validate().resetForm();
            });

            $document.on("click",".restorecfieldGroups, .restoregroupcfields",function(event) {
                event.stopPropagation();
                // cfieldoptioncount = 1;
                var form = $(this).data('id');
                if(form !== '' && form !== null){
                    $("#"+form)[0].reset();
                    $("#"+form).validate().resetForm();
                }
                $('.custom_fields_main_html').toggle();
                $('.hide').hide();
            });

            $document.on("click",".addgroupcfield",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data('id');
                if(cfieldgroupId > 0){
                    $("#cfieldparentgroupid").val(cfieldgroupId)
                }
                jQuery("#cfieldid").val('');
                $(".cfieldtitle").html('Add Custom Field');
                $('#cfieldtype').val('1').trigger('change');
                $(".more-options").html('');
                $("#form_cfield")[0].reset();
                $("#form_cfield").validate().resetForm();
                $('.customfields,.custom_fields_main_html').toggle();
            });

           
            $document.on("click",".deletecfieldgroup",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data('id');
                if(cfieldgroupId > 0 ){
                    swal({
                        title: "Are you sure to delete this custom field group?",
                        text: "You will not be able to recover!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes",
                        cancelButtonText: "cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_delete_cfield_group&jsoncallback=x", {cfieldgroupId: cfieldgroupId}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();
                                }
                            });
                        }
                    });
                }    
            });


            $document.on("change","#cfieldtype",function(event){
                event.stopPropagation();
                var inputType = $(this).find(':selected').data('id');
                if(inputType != "" && inputType !== null){
                    $(".externalcfields").hide();
                    $("."+inputType).show();
                }
            });

            var cfieldoptionsmaxlen  = 15;
            var cfieldoptioncount = 1;
            $document.on("click",".addcfieldoptions",function(event){
                event.stopPropagation();
                if(cfieldoptioncount < cfieldoptionsmaxlen){
                   cfieldoptioncount++;
                   $(".morecfieldoptions")
                   .append('<div class="form-group row cfieldoptions_'+cfieldoptioncount+'"><label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label"></label><div class="col-lg-10 col-md-9 col-sm-12 col-12"><div class="row"><div class="col-lg-6"><input type="text" name="cfieldoptionvalue[' + cfieldoptioncount + ']" placeholder="Field Value" required id="cfieldoptionvalue_'+cfieldoptioncount+'"></div><div class="col-lg-5"><input type="text" name="cfieldoptionlabel[' + cfieldoptioncount + ']" placeholder="Field Label" id="cfieldoptionlabel_'+cfieldoptioncount+'" required></div><div class="col-lg-1 removecfieldoptions" data-target="cfieldoptions_'+cfieldoptioncount+'"><i class="fa fa-trash"></i></div></div></div></div>');
                }
            });
            
            $document.on("click",".removecfieldoptions", function(event){
               event.stopPropagation();
               var cfieldoption = $(this).data("target");
               $('.'+cfieldoption).remove();
               cfieldoptioncount--;
            });

            $document.on("click",".editcfieldgroup",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data("id");
                if(cfieldgroupId > 0){
                    jQuery("#cfieldgroupid").val(cfieldgroupId);
                    $(".cfieldgrouptitle").html('Edit Custom Field Group');
                    jQuery.post( ajax_object.ajax_url + "?action=wc_get_cfield_group",{cfieldgroupId:cfieldgroupId}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            if(responsedata.cfieldgroupname != "" && responsedata.cfieldgroupname !== null){
                                jQuery("#cfieldgroupname").val(responsedata.cfieldgroupname);
                            }
                        }
                    });
                }
                $('.customfieldgroup,.custom_fields_main_html').toggle();
            });
            
            $document.on("click",".showhidecfieldgroup",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data('id');
                var cfieldgroupactiontype = $(this).data("target");
                if(cfieldgroupId > 0 ){
                    swal({
                        title: "Are you sure to "+cfieldgroupactiontype+" this group with all custom fields of it?",
                        text: "You will not be able to recover!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes",
                        cancelButtonText: "cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_update_cfieldgroup_showhide&jsoncallback=x", {cfieldgroupId: cfieldgroupId,cfieldgroupactiontype:cfieldgroupactiontype}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();
                                }
                            });
                        }
                    });
                }    
            });

            $document.on("click",".showhidecfield",function(event) {
                event.stopPropagation();
                var cfieldId = $(this).data('id');
                var cfieldactiontype = $(this).data("target");
                if(cfieldId > 0 ){
                    swal({
                        title: "Are you sure to "+cfieldactiontype+" this custom field ?",
                        text: "You will not be able to recover!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes",
                        cancelButtonText: "cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_update_cfield_showhide&jsoncallback=x", {cfieldId: cfieldId,cfieldactiontype:cfieldactiontype}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();
                                }
                            });
                        }
                    });
                }    
            });

            //on click of "*" icon of group delete the custom field....
            $document.on("click",".deletecfield",function(event) {
                event.stopPropagation();
                var cfieldId = $(this).data('id');
                if(cfieldId > 0 ){
                    swal({
                        title: "Are you sure to delete this custom field ?",
                        text: "You will not be able to recover!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes",
                        cancelButtonText: "cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_delete_cfield&jsoncallback=x", {cfieldId: cfieldId}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();
                                }
                            });
                        }
                    });
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

        //check form is add custom field form then validate it..
        if(form == "add_custom_field_form"){
            $("#"+form).validate({
                rules:{
                    cfname: "required",
                    cftab: "required",
                    cfheader: "required"
                },
                messages:{
                    cfname: {
                        required: 'Please enter custom field name!'
                    },
                    cftab: {
                        required: 'Please select the custom field tab!'
                    },
                    cfheader: {
                        required: 'Please select the custom field header!'
                    },
                }
            });
        }

        //check form is custom fields group form then validate it..
        if(form == "form_cfield_group"){
            $("#"+form).validate({
                rules:{
                      cfieldgroupname: "required",
                    },
                messages:{
                    cfieldgroupname: {
                        required: 'Please enter custom field group name!'
                    }
                }
            }); 
        }
        
        //check form is custom fields form then validate it..
        if(form == "form_cfield"){
            $("#"+form).validate({
                rules:{
                      cfieldname: "required",
                    },
                messages:{
                    cfieldname: {
                        required: 'Please enter custom field name!'
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
        jQuery.post( ajax_object.ajax_url + "?action=wc_get_trigger_details",{triggerid:triggerid}, function(data) {
            var responsedata = JSON.parse(data);
            if(responsedata.status == "1") {
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
                        if ($('.all_products_checkbox_match').is(":checked"))
                        {
                            $('.all_products_checkbox_match').prop("checked", false);
                        }
                        $('.each_product_checkbox_match').prop("checked", false);
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

//save custom field to infusionsoft application....
// function saveCustomFields(){
//     if($('#add_custom_field_form').valid()){
//         if($(".add-cf-error").is(":visible") || $(".add-cf-success").is(":visible")){
//             $(".add-cf-error").hide();
//             $(".add-cf-success").hide();
//             $(".addcf").show();
//         }else{
//             $(".addcf").show();    
//         }
//         $('.save_cf_btn').addClass("disable_anchor");
//         // var cfDataType = $("#cfDataType option:selected").text();
//         // $("#cfdata").val(cfDataType);
//         jQuery.post( ajax_object.ajax_url + "?action=wc_add_custom_field",$('#add_custom_field_form').serialize(), function(data) {
//             // var responsedata = JSON.parse(data);
//             // $(".addcf").hide();
//             // if(responsedata.status == "1") {
//             //     $("#addCustomFieldModel").hide();
//             //     // $(".wccfmappingwith").html('');
//             //     // $(".wccfmappingwith").html(responsedata.fieldOptions);
//             //     // $("#wccfmapping option").filter(function() {
//             //     //   return $(this).text() == responsedata.cfLatestName;
//             //     // }).prop('selected', true);
//             // }else{
//             //     $(".add-cf-error").show();
//             //     if(responsedata.errormessage != "" && responsedata.errormessage !== null){
//             //         $(".add-cf-error").html('');
//             //         $(".add-cf-error").html(responsedata.errormessage);
//             //     }else{
//             //         $(".add-cf-error").html('');
//             //         $(".add-cf-error").html('Something Went Wrong');
//             //     }
//             // }
//         });
//     }
// }


function savecfieldGroup(){
    if($('#form_cfield_group').valid()){
        if(!$(".cfieldgrouperror").is(":visible")){
            $(".savingcfieldGroup").show(); 
        }else{
            $(".cfieldgrouperror").hide();
            $(".savingcfieldGroup").show();       
        }
        $('.savingcfieldGroupBtn').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_cfield_group",$('#form_cfield_group').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".savingcfieldGroup").hide();
            if(responsedata.status == "1") {
                $('.customfieldgroup,.main_rendered').toggle();
                $('.savingcfieldGroupBtn').removeClass("disable_anchor");
                loadingCustomFields();
            }else{
                $(".cfieldgrouperror").show();
                $(".cfieldgrouperror").html('Something Went Wrong');
                setTimeout(function()
                {
                    $('.cfieldgrouperror').fadeOut("slow");
                    $('.savingcfieldGroupBtn').removeClass("disable_anchor");
                }, 3000);
            }
        });
    }
}

function loadingCustomFields(){
    $(".loading_custom_fields").show();
    jQuery(".tab_related_content").addClass('overlay');
    jQuery.post(ajax_object.ajax_url+"?action=wc_loading_cfields&jsoncallback=x", {}, function(data) {
        var responsedata = JSON.parse(data);
        $(".loading_custom_fields").hide();
        jQuery(".tab_related_content").removeClass('overlay');
        if(responsedata.status == "1") {
            if(responsedata.htmldata != ""){
                $(".main-group").html(responsedata.htmldata);
                $(".default_message").html('');
                $(".default_message").html('Above is the listing of available custom fields');    
                $('.main-group li.group-list').each(function () {
                    var li_id = this.id;
                    if(li_id != ''){
                        if($(".group_custom_field_"+li_id).length){
                            sortabledivs("group_custom_field_"+li_id);
                        }       
                    }
                });


            }else{
                $(".main-group").html('');
                $(".default_message").html('');
                $(".default_message").html("We don't have any Custom Fields");
            }
            
        }
    }).fail( function(){
        $(".loading_custom_fields").html('');
        $(".loading_custom_fields").html('Something Went Wrong...');
        jQuery(".tab_related_content").removeClass('overlay');
    });
}



function savegroupcfield(){
    if($('#form_cfield').valid()){
        if(!$(".groupcfielderror").is(":visible")){
            $(".savinggroupcfield").show(); 
        }else{
            $(".groupcfielderror").hide();
            $(".savinggroupcfield").show();       
        }
        $('.savingGroupCfieldBtn').addClass("disable_anchor");
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_groupcfield",$('#form_cfield').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".savinggroupcfield").hide();
            if(responsedata.status == "1") {
                $('.add_custom_field,.main_rendered').toggle();
                $('.savingGroupCfieldBtn').removeClass("disable_anchor");
                loadingCustomFields();
            }else{
                $(".groupcfielderror").show();
                $(".groupcfielderror").html('Something Went Wrong');
            }
            setTimeout(function()
            {
                $('.groupcfielderror').fadeOut("slow");
                $('.savingGroupCfieldBtn').removeClass("disable_anchor");
            }, 3000);
        });  
    }
}

function sortabledivs(element){
    if(element != ""){
        if(element == 'main-group'){
            jQuery( "."+element ).sortable({
               update: function( event, ui ) {
                    jQuery.post( ajax_object.ajax_url + "?action=update_custom_field_groups_order",{order: $(".main-group").sortable('toArray')}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            loadingCustomFields();
                        }
                    });
                }
            });    
        }
        else
        {
            jQuery( "."+element ).sortable({
               update: function( event, ui ) {
                    jQuery.post( ajax_object.ajax_url + "?action=update_custom_fields_order",{order: $("."+element).sortable('toArray')}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            loadingCustomFields();
                        }
                    });
               }
            });
        }
    }
}