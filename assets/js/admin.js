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

                        //Custom fields Tab : validate a "add_custom_field_form" form.....
                        if($('#add_custom_field_form').length){
                            validateForms('add_custom_field_form');
                        }

                        //Custom fields Tab : validate a "form_cfield_group" form.....
                        if($('#form_cfield_group').length){
                            validateForms('form_cfield_group');
                        }

                        //Custom fields Tab : Load custom field groups with its custom fields
                        if(jQuery(".custom_fields_main_html").length) {
                            loadingCustomFields();
                        }

                        //Custom fields Tab : validate a "form_cfield_group" form.....
                        if($('#form_cfield').length){
                            validateForms('form_cfield');
                        }

                        //Custom fields Tab : Apply select2 for mapped field....
                        if($(".cfieldmappingwith").length){
                           applySelectTwo('cfieldmappingwith'); 
                        }

                        //Custom fields Tab : apply sortable event on custom fields group rows.....
                        if($(".main-group").length){
                            sortabledivs('main-group');
                        }
                        
                        //apply change icon rule on lead source "How this works" button..
                        if($("#collapseLeadSource").length){
                            applyCollapseRules('collapseLeadSource');

                        }

                        //add select 2 for wordpress posts field on thankyou page.....
                        if($(".redirectpostsselect").length){
                            applySelectTwo('redirectpostsselect');
                        }

                        //add select 2 for wordpress pages field on thankyou page.....
                        if($(".redirectpagesselect").length){
                            applySelectTwo('redirectpagesselect');
                        }

                        //add select 2 for cart products field on thankyou page.....
                        if($(".redirectcartproductsselect").length){
                            applySelectTwo('redirectcartproductsselect');
                        }

                        //add select 2 for cart categories field on thankyou page.....
                        if($(".redirectcartcategoriesselect").length){
                            applySelectTwo('redirectcartcategoriesselect');
                        }

                        //validate a "thank_default_form" form of thankyou page........
                        if($('#thank_default_form').length){
                            validateForms('thank_default_form');
                        }

                        //validate a "thank_override_form_product" form of thankyou page at the time of add thankyou rule on the basis of product........
                        if($('#thank_override_form_product').length){
                            validateForms('thank_override_form_product');
                        }

                        //validate a "thank_override_form_product_cat" form of thankyou page at the time of add thankyou rule on the basis of product categories........
                        if($('#thank_override_form_product_cat').length){
                            validateForms('thank_override_form_product_cat');
                        }

                        //apply sortable rule on the thankyou overrides of product rule.....
                        if($(".override_product_rule").length){
                            sortabledivs('override_product_rule');
                        }

                        //apply sortable rule on the thankyou overrides of product category rule.....
                        if($(".override_product_category_rule").length){
                            sortabledivs('override_product_category_rule');
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
                if(target_tab_id != ""){
                    jQuery(".ajax_loader").show();
                    jQuery(".tab_related_content").addClass('overlay');
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
                                else if (target_tab_id == '#table_standard_fields_mapping') {
                                    $(target_tab_id+"_listing").html('');
                                    $(target_tab_id+"_listing").html(responsedata.latestHtml);
                                    
                                    //add select 2 for infusionsoft/keap fields....
                                    if($(".standardcfieldmappingwith").length){
                                        applySelectTwo('standardcfieldmappingwith');
                                    }

                                    //code is used to set the infusionsoft/keap field selected from dropdown....
                                    $(".standardcfrows").each(function() {
                                        var standardcfId = $(this).attr('id');//get the standard field id....
                                        var standardcdmapp = $(this).attr("data-id");//get the standard field mapped with....
                                        if(standardcfId != ''){
                                            if(standardcdmapp != ""){
                                                //set field selected....
                                                $('#standard_cfield_mapping_'+standardcfId).val(standardcdmapp).trigger('change.select2');
                                            }
                                        }
                                    });

                                }    
                            }
                            else{
                                $('.custom_fields_main_html').show();//toggle the form and show listing....
                                $('.hide').hide();//hide the form whether it is custom field group form or custom field form....
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

            //apply change icon rule on lead source "How this works" button..
            if($("#collapseLeadSource").length){
                applyCollapseRules('collapseLeadSource');
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

            //Custom fields Tab : below code used to get the custom fields tab on change custom field type e.g contact, order at the time of custom field creation....
            $document.on("change","#cfieldformtypeapp", function(event)
            {
                event.stopPropagation();
                var cfieldFormType = $(this).children("option:selected").attr('id');
                //if custom field type exist e.g contact, order then send ajax to get the tabs related to custom field type....
                if(cfieldFormType != "" && cfieldFormType !== null){
                    //$(".cfield_header").hide();
                    jQuery(".customfieldsModal").addClass('overlay');
                    jQuery(".ajax_loader_custom_fields_related").show();
                    jQuery("#cfieldtabapp").html('<option value="">Select Tab</option>');//set default html....
                    jQuery("#cfieldheaderapp").val('');
                    jQuery("#cfieldheaderapp").html('<option value="">Select Header</option>');//set default html....
                    jQuery.post( ajax_object.ajax_url + "?action=wc_cfield_app_tabs",{cfieldFormType:cfieldFormType}, function(data) {
                        var responsedata = JSON.parse(data);
                        jQuery(".customfieldsModal").removeClass('overlay');
                        jQuery(".ajax_loader_custom_fields_related").hide();
                        if(responsedata.status == "1") {
                            $("#cfieldtabapp").html(responsedata.cfieldtabsHtml);//change the html of custom field tab.......
                        }
                    });
                }
            });

            //Custom fields Tab : below code used to get the custom fields headers on change custom field tab at the time of custom field creation....
            $document.on("change","#cfieldtabapp", function(event)
            {
                event.stopPropagation();
                var cfieldFormTab = $(this).children("option:selected").val();
                //if custom field tab exist then send ajax to the get headers related to custom field tab....
                if(cfieldFormTab != "" && cfieldFormTab !== null){
                    jQuery(".customfieldsModal").addClass('overlay');
                    jQuery(".ajax_loader_custom_fields_related").show();
                    jQuery("#cfieldheaderapp").html('<option value="">Select Header</option>');//set default html....
                    jQuery.post( ajax_object.ajax_url + "?action=wc_cfield_app_headers",{cfieldFormTab:cfieldFormTab}, function(data) {
                        var responsedata = JSON.parse(data);
                        jQuery(".customfieldsModal").removeClass('overlay');
                        jQuery(".ajax_loader_custom_fields_related").hide();
                        if(responsedata.status == "1") {
                            $("#cfieldheaderapp").html(responsedata.cfieldheaderHtml);//change the html of custom field header.......
                        }
                    });
                }
            });

            //Custom fields Tab : when user click on "create group" button then hide the custom fields listing and show the add custom field group form......
            $document.on("click",".addcfieldgroup",function(event) {
                event.stopPropagation();
                jQuery("#cfieldgroupid").val('');//empty the hidden input value....
                $(".cfieldgrouptitle").html('Create Custom Fields Group');//change the form title....
                $('.customfieldgroup,.custom_fields_main_html').toggle();//toggle the form and show listing....
                //reset form values and validation rules....
                $("#form_cfield_group")[0].reset();
                $("#form_cfield_group").validate().resetForm();
            });

            //Custom fields Tab : when user click on cancel button whether cancel button of custom fields group form or custom field form........
            $document.on("click",".restorecfieldGroups, .restoregroupcfields",function(event) {
                event.stopPropagation();
                cfieldoptioncount = 1;
                var form = $(this).data('id');
                //reset form values and validation rules....
                if(form !== '' && form !== null){
                    $("#"+form)[0].reset();
                    $("#"+form).validate().resetForm();
                }
                $('.custom_fields_main_html').toggle();//toggle the form and show listing....
                $('.hide').hide();//hide the form whether it is custom field group form or custom field form....
            });

            //Custom fields Tab : when user click on "+" icon of particular custom field group then hide the custom fields listing and show the add custom field form......
            $document.on("click",".addgroupcfield",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data('id');//get custom field parent group id....
                if(cfieldgroupId > 0){
                    $("#cfieldparentgroupid").val(cfieldgroupId);//set to input type hidden for create a child of parent custom field group.....
                }
                jQuery("#cfieldid").val('');//empty the hidden input value....
                $(".cfieldtitle").html('Add Custom Field');//change the form title....
                $('#cfieldtype').val('1').trigger('change');
                $(".morecfieldoptions").html('');
                //reset form values and validation rules....
                $("#form_cfield")[0].reset();
                $("#form_cfield").validate().resetForm();
                $("#cfieldmapping").val("").trigger("change");
                $('.customfields,.custom_fields_main_html').toggle();//toggle the listing and show custom field add form....
            });

            //Custom fields Tab : when user click on "*" icon of particular custom field group then proceed the delete process......
            $document.on("click",".deletecfieldgroup",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data('id');//get custom field group id....
                //check value
                if(cfieldgroupId > 0 ){
                    //ask to confirm....
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
                    function (isConfirm) {//if delete confirmation is yes then send ajax to delete a custom field group....
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_delete_cfield_group&jsoncallback=x", {cfieldgroupId: cfieldgroupId}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();//after sucessfull delete then load the latest custom fields....
                                }
                            });
                        }
                    });
                }    
            });


            //Custom fields Tab : At the time of add/update custom field when user change the input type e.g input,textarea,checkbox,dropdown etc , then show the external field based on the input type...
            $document.on("change","#cfieldtype",function(event){
                event.stopPropagation();
                var inputType = $(this).find(':selected').data('id');//get input type.....
                //check value
                if(inputType != "" && inputType !== null){
                    if(inputType == 'input-type-3' || inputType == 'input-type-4'){
                        $(".morecfieldoptions").show(); 
                    }else{
                        $(".morecfieldoptions").hide();
                    }
                    $(".externalcfields").hide();//hide the all external fields first,...
                    $("."+inputType).show();//then show the only external fields which is related to input type....
                }
            });

            //Custom fields Tab : When user try to add custom field with input type radio or selectbox then onclick of "+" add the extra option value and option label rows.....
            var cfieldoptionsmaxlen  = 15;
            var cfieldoptioncount = 1;
            $document.on("click",".addcfieldoptions",function(event){
                event.stopPropagation();
                if(cfieldoptioncount < cfieldoptionsmaxlen){//compare row count with maxlength.....
                   cfieldoptioncount++;//increase counter.....
                   //append html of options row.....
                   $(".morecfieldoptions")
                   .append('<div class="form-group row cfieldoptions_'+cfieldoptioncount+'"><label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label"></label><div class="col-lg-10 col-md-9 col-sm-12 col-12"><div class="row"><div class="col-lg-6"><input type="text" name="cfieldoptionvalue[' + cfieldoptioncount + ']" placeholder="Field Value" required id="cfieldoptionvalue_'+cfieldoptioncount+'"></div><div class="col-lg-5"><input type="text" name="cfieldoptionlabel[' + cfieldoptioncount + ']" placeholder="Field Label" id="cfieldoptionlabel_'+cfieldoptioncount+'" required></div><div class="col-lg-1 removecfieldoptions" data-target="cfieldoptions_'+cfieldoptioncount+'"><i class="fa fa-trash"></i></div></div></div></div>');
                }
            });
            
            //Custom fields Tab : when user click on delete icon of specific option row....
            $document.on("click",".removecfieldoptions", function(event){
               event.stopPropagation();
               var cfieldoption = $(this).data("target");
               $('.'+cfieldoption).remove();//then remove particular option row.....
               cfieldoptioncount--;
            });

            //Custom fields Tab : when user click on "edit" icon of particular custom field group then hide the custom fields listing and show the custom field group form......
            $document.on("click",".editcfieldgroup",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data("id");//get the edited custom field group is.....
                //check value
                if(cfieldgroupId > 0){
                    jQuery("#cfieldgroupid").val(cfieldgroupId);//set the value of input hidden to proceed the edit process....
                    $(".cfieldgrouptitle").html('Edit Custom Field Group');//change the form title....
                    $(".add-editform-groups").addClass('overlay');
                    //send ajax to get the custom field group data....
                    jQuery.post( ajax_object.ajax_url + "?action=wc_get_cfield_group",{cfieldgroupId:cfieldgroupId}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            if(responsedata.cfieldgroupname != "" && responsedata.cfieldgroupname !== null){
                                jQuery("#cfieldgroupname").val(responsedata.cfieldgroupname);//then set to appropriate field.....
                            }
                            $(".add-editform-groups").removeClass('overlay');
                        }
                    });
                }
                $('.customfieldgroup,.custom_fields_main_html').toggle();//toggle the listing and show the edit custom field group form....
            });
            
            
            //Custom fields Tab : when user click on "eye" or "eye-slash" icon of particular custom field group then proceed to set status show or hide custom field group......
            $document.on("click",".showhidecfieldgroup",function(event) {
                event.stopPropagation();
                var cfieldgroupId = $(this).data('id');//get the custom field group id.....
                var cfieldgroupactiontype = $(this).data("target");//get the action type whether it is a show or hide.....
                //check value
                if(cfieldgroupId > 0 ){
                    //ask to confirm....
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
                    function (isConfirm) {//if show/hide confirmation is yes then send ajax to update a status show/hide of custom field group....
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_update_cfieldgroup_showhide&jsoncallback=x", {cfieldgroupId: cfieldgroupId,cfieldgroupactiontype:cfieldgroupactiontype}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();//after sucessfull uodate then load the latest custom fields....
                                }
                            });
                        }
                    });
                }    
            });

            //Custom fields Tab : when user click on "eye" or "eye-slash" icon of particular custom field then proceed to set status show or hide custom field......
            $document.on("click",".showhidecfield",function(event) {
                event.stopPropagation();
                var cfieldId = $(this).data('id');//get the custom field id.....
                var cfieldactiontype = $(this).data("target");//get the action type whether it is a show or hide.....
                //check value
                if(cfieldId > 0 ){
                    //ask to confirm....
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
                    function (isConfirm) {//if show/hide confirmation is yes then send ajax to update a status show/hide of custom field....
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_update_cfield_showhide&jsoncallback=x", {cfieldId: cfieldId,cfieldactiontype:cfieldactiontype}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();//after sucessfull uodate then load the latest custom fields....
                                }
                            });
                        }
                    });
                }    
            });

            //Custom fields Tab : when user click on "*" icon of particular custom field then proceed the delete process.....
            $document.on("click",".deletecfield",function(event) {
                event.stopPropagation();
                var cfieldId = $(this).data('id');//get custom field id....
                //check value
                if(cfieldId > 0 ){
                    //ask to confirm....
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
                    function (isConfirm) {//if delete confirmation is yes then send ajax to delete a custom field....
                        if (isConfirm) {
                            jQuery(".tab_related_content").addClass('overlay');
                            jQuery.post(ajax_object.ajax_url + "?action=wc_delete_cfield&jsoncallback=x", {cfieldId: cfieldId}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    loadingCustomFields();//after sucessfull delete then load the latest custom fields....
                                }
                            });
                        }
                    });
                }
            });

            //Custom fields Tab : when user click on "edit" icon of particular custom field then hide the custom fields listing and show the custom field form......
            $document.on("click",".editcfield",function(event) {
                event.stopPropagation();
                var cfieldId = $(this).data("id");//get custom field id....
                //check value
                if(cfieldId > 0)
                {
                    jQuery("#cfieldid").val(cfieldId);//set the value of input hidden to proceed the edit process....
                    $(".cfieldtitle").html('Edit Custom Field');//change the form title....
                    $(".add-editform-groups").addClass('overlay');
                    //send ajax to get the custom field data....
                    jQuery.post( ajax_object.ajax_url + "?action=wc_get_cfield",{cfieldId:cfieldId}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            //then set to appropriate fields.....
                            if(responsedata.cfieldname !== '' && responsedata.cfieldname !== null){
                                jQuery("#cfieldname").val(responsedata.cfieldname);
                            } 
                            if(responsedata.cfieldtype !== ''){
                               jQuery("#cfieldtype").val(responsedata.cfieldtype); 
                               $("#cfieldtype").trigger("change");
                               if(responsedata.cfieldtype == CF_FIELD_TYPE_TEXT 
                                    || responsedata.cfieldtype == CF_FIELD_TYPE_TEXTAREA 
                                    || responsedata.cfieldtype == CF_FIELD_TYPE_DATE )
                               {
                                    if(responsedata.cfieldplaceholder !== '' && responsedata.cfieldplaceholder !== null){
                                        jQuery("#cfieldplaceholder").val(responsedata.cfieldplaceholder); 
                                    }
                                    $(".morecfieldoptions").html('');   
                               }else if(responsedata.cfieldtype == CF_FIELD_TYPE_DROPDOWN || responsedata.cfieldtype == CF_FIELD_TYPE_RADIO){
                                    if(responsedata.cfieldoptionVal !== '' && responsedata.cfieldoptionVal !== null){
                                        $("#cfieldoptionvalue").val(responsedata.cfieldoptionVal);
                                    }
                                    if(responsedata.cfieldoptionLab !== '' && responsedata.cfieldoptionLab !== null){
                                        $("#cfieldoptionlabel").val(responsedata.cfieldoptionLab);
                                    }
                                    if(responsedata.cfieldoptionsCount > 1 && responsedata.cfieldoptionsHtml !== ''){
                                        $(".morecfieldoptions").html('');
                                        $(".morecfieldoptions").append(responsedata.cfieldoptionsHtml);
                                    }
                                    if(responsedata.cfielddefaultvalue !== '' && responsedata.cfielddefaultvalue !== null){
                                        jQuery("#cfielddefault2value").val(responsedata.cfielddefaultvalue);
                                    }
                               }
                               else
                               {
                                    $(".morecfieldoptions").html('');
                                    if(responsedata.cfielddefaultvalue !== '' && responsedata.cfielddefaultvalue !== null){
                                        jQuery("#cfielddefault1value").val(responsedata.cfielddefaultvalue);
                                    }
                               }
                            } 
                            if(responsedata.cfieldmandatory !== '' && responsedata.cfieldmandatory !== null)
                            {
                                $("#cfieldmandatory").val(responsedata.cfieldmandatory)
                            }
                            if(responsedata.cfieldmapped !== '' && responsedata.cfieldmapped !== null)
                            {
                                $('#cfieldmapping').val(responsedata.cfieldmapped).trigger('change');
                            }else{
                                $('#cfieldmapping').val('').trigger('change');
                            }
                            $(".add-editform-groups").removeClass('overlay');
                        }
                    });
                }
                $('.customfields,.main_rendered').toggle();//toggle the listing and show the edit custom field form....
                cfieldoptioncount = 1;
            });
            
            //Custom fields Tab : when user try to search custom field from the dropdown and the click on search result show the popup to add new custom field to application......
            $document.on("select2:select",".cfieldmappingwith", function(event)
            {    
                var option = event.params.data;
                //check element......
                if(!option['element'])
                {
                    var name = option['text'];//get text.....
                    //check value...
                    if(name !="" && name !== null){
                        $("#addcfieldapp")[0].reset();
                        $("#addcfieldapp").validate().resetForm();
                        $("#cfieldnameapp").val(name);//set value....
                        $("#cfieldmodelapp").show();
                        $("#cfieldtabapp").val('');
                        $("#cfieldheaderapp").val('');
                        //get application custom fields related tabs.....
                        if($('.cfieldtabapp').length){
                            loadApplicationCFTabs();
                        }
                        //validate a custom field application form....
                        if($('#addcfieldapp').length){
                            validateForms('addcfieldapp');
                        }
                    }
                }
            });

            //on change of override redirect condition show hide the corresponding fields on default thankyou page....
            $document.on("change","#overrideredirecturltype",function(event) {
                event.stopPropagation();
                var selectedCondition = $(this).val();
                if (selectedCondition == DEFAULT_WORDPRESS_POST) {
                    $(".redirect-type-common").hide();
                    $("#redirect-type-post").show();
                    $("#redirectwordpresspost").val("").trigger("change");
                }else if (selectedCondition == DEFAULT_WORDPRESS_PAGE) {
                    $(".redirect-type-common").hide();
                    $("#redirect-type-page").show();
                    $("#redirectwordpresspage").val("").trigger("change");
                }else if(selectedCondition == DEFAULT_WORDPRESS_CUSTOM_URL){
                    $(".redirect-type-common").hide();
                    $("#redirect-type-custom-url").show();
                }
            });
            

            //on click of edit icon of default thankyou override show the edit default thankyou override form.....
            $document.on("click",".edit_default_thankpage_override",function(event) {
                event.stopPropagation();
                $(".add-editform-override").addClass('overlay');
                $(".thankyou_default_title").html('Edit Default Thankyou Page');
                jQuery.post( ajax_object.ajax_url + "?action=wc_get_thankyou_default_override",{option:'default_thankyou_details'}, function(data) {
                    var responsedata = JSON.parse(data);
                    if(responsedata.status == "1") {
                        if(responsedata.redirectType != "" && responsedata.redirectType !== null){
                            jQuery("#overrideredirecturltype").val(responsedata.redirectType);
                            $("#overrideredirecturltype").trigger("change");
                            if(responsedata.redirectType == DEFAULT_WORDPRESS_PAGE){
                                if(responsedata.redirectValue != "" && responsedata.redirectValue !== null){
                                    jQuery("#redirectwordpresspage").val(responsedata.redirectValue).trigger('change');
                                }
                            }
                            else if (responsedata.redirectType == DEFAULT_WORDPRESS_POST)
                            {
                                if(responsedata.redirectValue != "" && responsedata.redirectValue !== null){
                                    jQuery('#redirectwordpresspost').val(responsedata.redirectValue).trigger('change');
                                }
                            }
                            else{
                                if(responsedata.redirectValue != "" && responsedata.redirectValue !== null){
                                    jQuery("#customurl").val(responsedata.redirectValue);
                                }
                            }   
                        }
                        $(".add-editform-override").removeClass('overlay');
                    }
                });
                $('.defaultoverride,.main_rendered_thank_overrides').toggle();
            });
        
            //on click of "+" icon of thankyou product override show the form to add product thankyou override.....
            $document.on("click",".add_product_rules",function(event) {
                event.stopPropagation();
                jQuery("#productoverrideid").val('');
                $("#thank_override_form_product")[0].reset();
                $("#thank_override_form_product").validate().resetForm();
                $(".redirectcartproductsselect").val("").trigger("change");
                $('.productoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of "+" icon of thankyou product override show the form to add product category thankyou override.....
            $document.on("click",".add_product_category_rules",function(event) {
                event.stopPropagation();
                jQuery("#productcatoverrideid").val('');
                $("#thank_override_form_product_cat")[0].reset();
                $("#thank_override_form_product_cat").validate().resetForm();
                $(".redirectcartcategoriesselect").val("").trigger("change");
                $('.productcatoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of cancel button from add/edit forms then show the initial listing of overrides...
            $document.on("click",".restore_overrides",function(event) {
                event.stopPropagation();
                var current_override_id = $(this).data('id');//get the override id...
                if(current_override_id !== '' && current_override_id !== null){
                    $("#"+current_override_id)[0].reset();
                    $("#"+current_override_id).validate().resetForm();
                }
                $('.main_rendered_thank_overrides').toggle();
                $('.hide').hide();
            });

            //on click of edit icon of thankyou product override show the form to edit product thankyou override.....
            $document.on("click",".edit_product_rule_override",function(event) {
                event.stopPropagation();
                var currrent_override_id = $(this).data("id");//get the override id...
                //check form id then send ajax to get the details of if and then show the form.......
                if(currrent_override_id > 0){
                    jQuery("#productoverrideid").val(currrent_override_id);
                    $(".thankyou_override_title_product").html('Edit Product Thankyou Page Override');
                    $(".add-editproductrule-override").addClass('overlay');
                    jQuery.post( ajax_object.ajax_url + "?action=wc_get_product_thankyou_override",{overrideid:currrent_override_id}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            if(responsedata.overridename != "" && responsedata.overridename !== null){
                                jQuery("#procductoverridename").val(responsedata.overridename);
                            }
                            if(responsedata.overrideurl != "" && responsedata.overrideurl !== null){
                                jQuery("#productrediecturl").val(responsedata.overrideurl);
                            }
                            if(responsedata.products != "" && responsedata.products !== null){
                                var blockstrPro = $.map(responsedata.products, function(val,index) {
                                     var str = val;
                                     return str;
                                }).join(",");
                                var selectedPro = blockstrPro.split(',');
                                $('#redirectcartproducts').val(selectedPro).trigger('change');
                            }else{
                                $('#redirectcartproducts').val('').trigger('change');
                            }
                            $(".add-editproductrule-override").removeClass('overlay');
                        }
                    });
                }
                //use toggle event to show hide the form and override listing.....
                $('.productoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of edit icon of thankyou product override show the form to edit product category thankyou override.....
            $document.on("click",".edit_product_category_rule_override",function(event) {
                event.stopPropagation();
                var currrent_override_id = $(this).data("id");//get the override id...
                //check form id then send ajax to get the details of if and then show the form.......
                if(currrent_override_id > 0){
                    jQuery("#productcatoverrideid").val(currrent_override_id);
                    $(".thankyou_override_title_product_cat").html('Edit Product Category Thankyou Page Override');
                    $(".add-editproductrule-override").addClass('overlay');
                    jQuery.post( ajax_object.ajax_url + "?action=wc_get_product_cat_thankyou_override",{overrideid:currrent_override_id}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            if(responsedata.overridename != "" && responsedata.overridename !== null){
                                jQuery("#productcatoverridename").val(responsedata.overridename);
                            }
                            if(responsedata.overrideurl != "" && responsedata.overrideurl !== null){
                                jQuery("#productcatrediecturl").val(responsedata.overrideurl);
                            }
                            if(responsedata.categories != "" && responsedata.categories !== null){
                                var blockstrPro = $.map(responsedata.categories, function(val,index) {
                                     var str = val;
                                     return str;
                                }).join(",");
                                var selectedPro = blockstrPro.split(',');
                                $('#redirectcartcategories').val(selectedPro).trigger('change');
                            }else{
                                $('#redirectcartcategories').val('').trigger('change');
                            }
                            $(".add-editproductrule-override").removeClass('overlay');
                        }
                    });
                }
                //use toggle event to show hide the form and override listing.....
                $('.productcatoverride,.main_rendered_thank_overrides').toggle();
            });

            //on click of "*" icon of override delete the current override whethe its a product thankyou override or product thankyou override
            $document.on("click",".delete_current_override_product",function(event) {
                event.stopPropagation();
                var current_override_id = $(this).data('id');//get the override id...
                var current_override_type = $(this).data('type');//get the override type whether its a product or product category...
                //first check the override id is accurate then get the confirmation from user and then send ajax and mark override as a deleted...
                if(current_override_id > 0 ){
                    swal({
                        title: "Are you sure to delete this override?",
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
                            jQuery.post(ajax_object.ajax_url + "?action=wc_delete_thankyou_override&jsoncallback=x", {overrideid: current_override_id,overridetype:current_override_type}, function(data) {
                                jQuery(".tab_related_content").removeClass('overlay');
                                var responsedata = JSON.parse(data);
                                if(responsedata.status == "1") {
                                    if(current_override_type == REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS){
                                        loading_thanks_overrides(REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS);//load the list of latest overrides....
                                        swal("Saved!", 'Product Thankyou page override deleted Successfully.', "success");
                                    }else if (current_override_type == REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES) {
                                        loading_thanks_overrides(REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES);//load the list of latest overrides....
                                        swal("Saved!", 'Product Category Thankyou page override deleted Successfully.', "success");
                                    }
                                }
                            });
                        }
                    });
                }
            });

            //Below code is used to check whether a application type is infusionsoft/keap, if keap then hide some feature menus like leadsource,refferal partner..
            if($(".applicationtype").attr('id') != ""){
                var configurationType =  $(".applicationtype").attr('id');
                if(configurationType == '1'){//if application type is infusionsoft.......
                    $("#lead_sources").show();
                }else if (configurationType == '2'){//if application type is keap.....
                    $("#lead_sources").hide();
                }
            }


            //Custom Fields Tab : On change of application custom field from dropdown send ajax to update mapping of standard custom field............
            $document.on("change",".standardcfieldmappingwith", function(event)
            {
                event.stopPropagation();
                //get woocommerce custom field id....
                var wcCustomFieldId = $(this).data('id');
                //get application product id with woocommerce product mapping set.........
                var applicationCustomField = $(this).val();
                if(wcCustomFieldId != "" && typeof wcCustomFieldId  !== "undefined"){
                    jQuery(".standard_fields_listing_class").addClass('overlay');
                    jQuery(".ajax_loader_standard_fields_related").show();
                    jQuery.post(ajax_object.ajax_url + "?action=wc_update_standard_cfields_mapping&jsoncallback=x", {wcCustomFieldId: wcCustomFieldId,applicationCustomField:applicationCustomField}, function(data) {
                        jQuery(".standard_fields_listing_class").removeClass('overlay');
                        jQuery(".ajax_loader_standard_fields_related").hide();
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            swal("Saved!", 'Standard field mapping updated successfully.', "success");
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

        //check form is custom field form for application then validate it..
        if(form == "addcfieldapp"){
            $("#"+form).validate({
                rules:{
                    cfieldnameapp: "required",
                    cfieldtabapp: "required",
                    cfieldheaderapp: "required"
                },
                messages:{
                    cfieldnameapp: {
                        required: 'Please enter custom field name!'
                    },
                    cfieldtabapp: {
                        required: 'Please select the custom field tab!'
                    },
                    cfieldheaderapp: {
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

        //check form is "thank_default_form" then validate it.......
        if(form == "thank_default_form"){
            
            //code is used to validate a url is in valid format e.g with http,https....
            jQuery.validator.addMethod("checkurl", function(value, element) {
                return this.optional(element) || /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(value);
            });
            $("#"+form).validate({
                rules:{
                      redirectwordpresspost: "required",
                      redirectwordpresspage: "required",
                      customurl: {
                        required: true,
                        checkurl : true,
                      },
                    },
                messages:{
                    redirectwordpresspost: {
                        required: 'Please select the post for redirection!'
                    },
                    redirectwordpresspage: {
                        required: 'Please select the page for redirection!',
                    },
                    customurl: {
                        required: 'Please enter the custom url for redirection',
                        checkurl: 'Please enter the valid redirect url',
                    },
                }
            }); 
        }

        //check form is "thank_override_form_product" then validate it.......
        if(form == "thank_override_form_product"){
            
            //code is used to validate a url is in valid format e.g with http,https....
            jQuery.validator.addMethod("checkurl", function(value, element) {
                return this.optional(element) || /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(value);
            });
            $("#"+form).validate({
                rules:{
                      procductoverridename: "required",
                      productrediecturl: {
                            required: true,
                            checkurl : true,
                      },
                      "redirectcartproducts[]": "required",
                    },
                messages:{
                    procductoverridename: {
                        required: 'Please enter the name of override'
                    },
                    productrediecturl: {
                        required: 'Please enter the redirect url for redirection',
                        checkurl: 'Please enter the valid redirect url',
                    },
                    "redirectcartproducts[]": {
                        required: 'Please select the cart products!'
                    },
                }
            }); 
        }
        //check form is "thank_override_form_product_cat" then validate it........
        if(form == "thank_override_form_product_cat"){
            
            //code is used to validate a url is in valid format e.g with http,https....
            jQuery.validator.addMethod("checkurl", function(value, element) {
                return this.optional(element) || /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(value);
            });

            $("#"+form).validate({
                rules:{
                      productcatoverridename: "required",
                      productcatrediecturl:{
                         required : true,
                         checkurl : true,
                      },
                      "redirectcartcategories[]": "required",
                    },
                messages:{
                    productcatoverridename: {
                        required: 'Please enter the name of override'
                    },
                    productcatrediecturl: {
                        required: 'Please enter the redirect url for redirection',
                        checkurl: 'Please enter the valid redirect url',
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
        //reset form values and validation rules....
        if($("#addcfieldapp").length){
            $("#addcfieldapp")[0].reset();
            $("#addcfieldapp").validate().resetForm();
        }
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
        else if(tabel_id == 'products_listing_with_sku_34' 
                || tabel_id == 'products_listing_with_sku_35' 
                    || tabel_id == 'products_listing_with_sku_40' 
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

        if(element == 'cfieldmappingwith'){
            $("."+element).select2({
                placeholder: 'Select Mapped Infusionsoft Field',
                tags: true,
            }); 
        } 
        //add select 2 for woocommerce products field
        if(element == 'standardcfieldmappingwith'){
            $("."+element).select2({
                placeholder: 'Select Application Field',
            }); 
        }
        //add select 2 for wordpress posts field on thankyou page.....
        if(element == 'redirectpostsselect'){
            $("."+element).select2({
                placeholder: 'Select Post',
            });    
        }
        //add select 2 for wordpress pages field on thankyou page.....
        if(element == 'redirectpagesselect'){
            $("."+element).select2({
                placeholder: 'Select Page',
            });    
        } 
        //add select 2 for cart products field on thankyou page.....
        if(element == 'redirectcartproductsselect'){
            $("."+element).select2({
                placeholder: 'Select Cart Products',
                multiple : true,
            });    
        }
        //add select 2 for cart categories field on thankyou page.....
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

//Custom fields Tab : when user click on save button of custom field application form.....
function savecfieldapp(){
    //check form is validate ....
    if($('#addcfieldapp').valid()){
        //check error sucess messages....
        if(!$(".cfieldapperror").is(":visible")){
            $(".savingcfieldapp").show(); 
        }else{
            $(".cfieldapperror").hide();
            $(".savingcfieldapp").show();       
        }
        $('.savingCfieldAppBtn').addClass("disable_anchor");
        //send ajax to save the custom field in infusionsoft/keap application.....
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_cfield_app",$('#addcfieldapp').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".savingcfieldapp").hide();
            if(responsedata.status == "1") {
                $("#cfieldmodelapp").hide();
                $(".cfieldmappingwith").html('');
                $(".cfieldmappingwith").html(responsedata.cfieldOptions);
                $('.savingCfieldAppBtn').removeClass("disable_anchor");
                $("#cfieldmapping option").filter(function() {
                  return $(this).text() == responsedata.cfieldName;
                }).prop('selected', true);
            }else{
                $(".cfieldapperror").show();
                if(responsedata.errormessage != "" && responsedata.errormessage !== null){
                    $(".cfieldapperror").html('');
                    $(".cfieldapperror").html(responsedata.errormessage);
                }else{
                    $(".cfieldapperror").html('');
                    $(".cfieldapperror").html('Something Went Wrong');
                }
            }
        });
    }
}

//Custom fields Tab : when user click on save button of custom field group form.....
function savecfieldGroup(){
    //check form is validate ....
    if($('#form_cfield_group').valid()){
        //check error sucess messages....
        if(!$(".cfieldgrouperror").is(":visible")){
            $(".savingcfieldGroup").show(); 
        }else{
            $(".cfieldgrouperror").hide();
            $(".savingcfieldGroup").show();       
        }
        $('.savingcfieldGroupBtn').addClass("disable_anchor");
        //send ajax to save the custom field group in infusionsoft/keap application.....
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

//Custom fields Tab : common function to load the all custom fields at the time delete,edit,add custom field....
function loadingCustomFields(){
    $(".loading_custom_fields").show();
    jQuery(".tab_related_content").addClass('overlay');
    //send ajax to save the custom field in infusionsoft/keap application.....
    jQuery.post(ajax_object.ajax_url+"?action=wc_loading_cfields&jsoncallback=x", {}, function(data) {
        var responsedata = JSON.parse(data);
        $(".loading_custom_fields").hide();
        jQuery(".tab_related_content").removeClass('overlay');
        if(responsedata.status == "1") {
            if(responsedata.cfieldhtml != ""){
                $(".main-group").html(responsedata.cfieldhtml);
                $(".default_message").html('');
                $(".default_message").html('Above is the listing of available custom fields');    
                $('.main-group li.group-list').each(function () {
                    var li_id = this.id;
                    if(li_id != ''){
                        if($(".group_custom_field_"+li_id).length){
                            sortabledivs("group_custom_field_"+li_id);//apply sortable rules on all custom fields......
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


//Custom fields Tab : when user click on save button of custom field form.....
function savegroupcfield(){
    if($('#form_cfield').valid()){
        if(!$(".groupcfielderror").is(":visible")){
            $(".savinggroupcfield").show(); 
        }else{
            $(".groupcfielderror").hide();
            $(".savinggroupcfield").show();       
        }
        $('.savingGroupCfieldBtn').addClass("disable_anchor");
        //send ajax to save the custom field....
        jQuery.post( ajax_object.ajax_url + "?action=wc_save_groupcfield",$('#form_cfield').serialize(), function(data) {
            var responsedata = JSON.parse(data);
            $(".savinggroupcfield").hide();
            if(responsedata.status == "1") {
                $('.customfields,.main_rendered').toggle();
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

//Custom fields Tab : apply sortable rule on custom field group and also on all custom fields.......
function sortabledivs(element){
    if(element != ""){
        //apply sortable on custom fields.....
        if(element == 'main-group'){
            jQuery( "."+element ).sortable({
               update: function( event, ui ) {
                    jQuery.post( ajax_object.ajax_url + "?action=wc_update_cfieldgroups_order",{cfieldgrouplatestorder: $(".main-group").sortable('toArray')}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            loadingCustomFields();
                        }
                    });
                }
            });    
        }
        //apply sortable on custom field groups.....
        else
        {
            jQuery( "."+element ).sortable({
               update: function( event, ui ) {
                    jQuery.post( ajax_object.ajax_url + "?action=wc_update_groupcfields_order",{groupcfieldlatestorder: $("."+element).sortable('toArray')}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            loadingCustomFields();
                        }
                    });
               }
            });
        }
    
        if(element == 'override_product_rule'){
            jQuery( "."+element ).sortable({
               update: function( event, ui ) {
                    jQuery.post( ajax_object.ajax_url + "?action=update_thankyou_overrides_order",{order: $(".override_product_rule").sortable('toArray')}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            loading_thanks_overrides(REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS);//load the list of latest overrides....
                        }
                    });
                }
            });    
        }
        //apply sortable rule on the thankyou overrides of product category rule.....
        else if(element == 'override_product_category_rule'){
            jQuery( "."+element ).sortable({
               update: function( event, ui ) {
                    jQuery.post( ajax_object.ajax_url + "?action=update_thankyou_overrides_order",{order: $(".override_product_category_rule").sortable('toArray')}, function(data) {
                        var responsedata = JSON.parse(data);
                        if(responsedata.status == "1") {
                            loading_thanks_overrides(REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES);//load the list of latest overrides....
                        }
                    });
                }
            });   
        }

    }
}

//Custom fields Tab : This function is used to get the application custom field tabs.....
function loadApplicationCFTabs(){
    jQuery(".customfieldsModal").addClass('overlay');
    jQuery.post( ajax_object.ajax_url + "?action=wc_load_app_cfield_tabs",{}, function(data) {
        var responsedata = JSON.parse(data);
        if(responsedata.status == "1") {
            if(responsedata.cfRelatedTabs != "") {
                $(".cfieldtabapp").html('');
                $(".cfieldtabapp").html(responsedata.cfRelatedTabs);
            }
            jQuery(".customfieldsModal").removeClass('overlay');
        }
    });
}

//on click of save button of default thankyou override call the  function "saveThanksDefaultOverride" to update the default thankyou override....
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
                swal("Saved!", 'Default thankyou page override details updated successfully.', "success");
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


//on click of save button of product thankyou override call the function "saveThanksProductOverride" to add/update the product thankyou override....
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
            var responsedata = JSON.parse(data);
            $(".savingProductOverrideDetails").hide();
            if(responsedata.status == "1") {
                $('.productoverride,.main_rendered_thank_overrides').toggle();
                $('.save_thank_you_product_override').removeClass("disable_anchor");
                swal("Saved!", 'Product thankyou override details updated successfully.', "success");
                loading_thanks_overrides(REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS);//load the list of latest overrides....
            }else{
                $(".override-error").show();
                $(".override-error").html('Something Went Wrong');
                setTimeout(function()
                {
                    $('.override-error').fadeOut("slow");
                    $('.save_thank_you_product_override').removeClass("disable_anchor");
                }, 3000);
            }
        });
    }  
}

//on click of save button of product category thankyou override call the function "saveThanksProductCatOverride" to add/update the product thankyou override....
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
            var responsedata = JSON.parse(data);
            $(".savingProductCatOverrideDetails").hide();
            if(responsedata.status == "1") {
                $('.productcatoverride,.main_rendered_thank_overrides').toggle();
                $('.save_thank_you_product_cat_override').removeClass("disable_anchor");
                swal("Saved!", 'Product category thankyou override details updated successfully.', "success");
                loading_thanks_overrides(REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES);//load the list of latest overrides....
            }else{
                $(".override-error").show();
                $(".override-error").html('Something Went Wrong');
                setTimeout(function()
                {
                    $('.override-error').fadeOut("slow");
                    $('.save_thank_you_product_cat_override').removeClass("disable_anchor");
                }, 3000);
            }
        });
    }  
}


//This function is used to load the latest thanks override after add/edit/delete override.....
function loading_thanks_overrides($type){
    jQuery(".tab_related_content").addClass('overlay');
    jQuery.post(ajax_object.ajax_url+"?action=loading_thanks_overrides&jsoncallback=x", {overridesType:$type}, function(data) {
        var responsedata = JSON.parse(data);
        jQuery(".tab_related_content").removeClass('overlay');
        if(responsedata.status == "1") {
            if(responsedata.thankyouOverridesListing != ""){
                if($type == REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS){
                    $("#product_thank_overrides").html('');
                    $("#product_thank_overrides").html(responsedata.thankyouOverridesListing);
                    //apply sortable rule on the thankyou overrides of product rule.....
                    if($(".override_product_rule").length){
                        sortabledivs('override_product_rule');
                    }
                }else if($type == REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES){
                    $("#product_cat_thank_overrides").html('');
                    $("#product_cat_thank_overrides").html(responsedata.thankyouOverridesListing);
                    //apply sortable rule on the thankyou overrides of product category rule.....
                    if($(".override_product_category_rule").length){
                        sortabledivs('override_product_category_rule');
                    }
                }
            }
        }
    });
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
