<style type="text/css">
div#addCustomFieldModel {
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}
span.closeCfModel {
    font-size: 25px;
    cursor: pointer;
}
</style>
<?php 
	//check the application authentication status if authorized then give access to configure campaign goals....
	$checkAuthenticationStatus = applicationAuthenticationStatus();
	//get the array of application custom fields.....
	$preDefinedCustomFields = getPredefindCustomfields();
	//already fields mapped in standard fields..
	$alreadtMappedFields = listAlreadyUsedFields();
?>
<div class="info-header" style="position:relative;">
  <p>Checkout Custom Fields</p>
</div>
<div class="righttextInner">
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
  	<div class="row">
	    <div class="col-md-12 ">
		  	<?php if(empty($checkAuthenticationStatus)){?>
		  	<p class="heading-text">
	      	<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.<br/>
	        <br/>
	        Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
	      	</p>

	      	<nav>
		        <div class="nav nav-tabs nav-fill custom-nav-tabs" id="nav-tab" role="tablist">
		          <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#table_contact_custom_fields" role="tab" aria-controls="nav-profile" aria-selected="false">Checkout Custom Fields</a>
				  <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#table_standard_fields_mapping" role="tab" aria-controls="nav-profile" aria-selected="false">Woocommerce Standard Fields Mapping</a>
		        </div>
	        </nav>

			
	        <div class="tab-content" id="nav-tabContent">
	        	<div class="tab-pane fade show active" id="table_contact_custom_fields" role="tabpanel" aria-labelledby="nav-home-tab">
	        		<div class="main_rendered custom_fields_main_html">
				      	<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
				      	<div class="loading_custom_fields" style="display: none;">
					      	<img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.svg">
				      	</div>
				      	<ul class="main-group">
						</ul>
					  	<div class="text-center m-t-50">
					  		<span class="create-new-group"><span class="default_message">We don't have any Custom Fields</span> <a href="javascript:void(0)" class="btn btn-theme third addcfieldgroup"><span>Create Group</span></a></span>
					  	</div>
					</div>
					<div class="add-editform-groups">
						<div class="hide customfieldgroup">
						    <form action="" method="post" id="form_cfield_group" onsubmit="return false">
						      	<input type="hidden" name="cfieldgroupid" id="cfieldgroupid" value="" />
						      	<h5 class="text-left cfieldgrouptitle">Create Custom Fields Group</h5>
						      	<div class="form-group row">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Group Name</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<input class="form-control" type="text" placeholder="Custom Field Group Name" name="cfieldgroupname" id="cfieldgroupname">
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
									</div>
								</div>
								<div class="row m-t-40">
									<div class="col-md-12 text-right">
										<div class="buttonloading savingcfieldGroup" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
										<input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default restorecfieldGroups" data-id="form_cfield_group">
										<input type="button" value="Save" class="btn btn-primary btn-radius btn-theme savingcfieldGroupBtn" onclick="savecfieldGroup()">
										<div class="alert-error-message cfieldgrouperror" style="display: none;"></div>
										<div class="alert-sucess-message cfieldgroupsuccess" style="display: none;">Custom fields group saved successfully.</div>
									</div>
								</div>
						   	</form>
						</div>
						<div class="hide customfields">
							<form action="" method="post" id="form_cfield" onsubmit="return false">
								<input type="hidden" name="cfieldparentgroupid" id="cfieldparentgroupid" value="" />
								<input type="hidden" name="cfieldid" id="cfieldid" value="" />
								<h5 class="text-left cfieldtitle">Add Custom Field</h5>
								<div class="form-group row">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom Field Name</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<input class="form-control" type="text" name="cfieldname" id="cfieldname" placeholder="Custom Field Name">
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom Field Type</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<select name="cfieldtype" id="cfieldtype">
											<option value = "<?php echo CF_FIELD_TYPE_TEXT ?>" data-id="input-type-<?php echo CF_FIELD_TYPE_TEXT; ?>">Field Input Type Text</option>
											<option value = "<?php echo CF_FIELD_TYPE_TEXTAREA ?>" data-id="input-type-<?php echo CF_FIELD_TYPE_TEXTAREA; ?>">Field Input Type Text Area</option>
											<option value = "<?php echo CF_FIELD_TYPE_DROPDOWN ?>" data-id="input-type-<?php echo CF_FIELD_TYPE_DROPDOWN; ?>">Field Input Type Select</option>
											<option value = "<?php  echo CF_FIELD_TYPE_RADIO ?>" data-id="input-type-<?php echo CF_FIELD_TYPE_RADIO; ?>">Field Input Type Radio</option>
											<option  value = "<?php  echo  CF_FIELD_TYPE_CHECKBOX ?>" data-id="input-type-<?php echo CF_FIELD_TYPE_CHECKBOX; ?>">Field Input Type Checkbox(Yes / No)</option>
											<option value = "<?php  echo  CF_FIELD_TYPE_DATE ?>"  data-id="input-type-<?php echo CF_FIELD_TYPE_DATE; ?>">Field Input Type Date</option>
										</select>
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="form-group row input-type-<?php echo CF_FIELD_TYPE_DROPDOWN; ?> input-type-<?php echo CF_FIELD_TYPE_RADIO; ?> externalcfields" style="display:  none;">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom Field Related Options </label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<div class="row">
											<div class="col-lg-6"><input type="text" name="cfieldoptionvalue[1]" placeholder="Field Value" id="cfieldoptionvalue" required></div>
											<div class="col-lg-5"><input type="text" name="cfieldoptionlabel[1]" placeholder="Field Label" id="cfieldoptionlabel" required></div>
											<div class="col-lg-1"><span type="button" class="addcfieldoptions"><i class="fa fa-plus"></i></span></div>
										</div>
									</div>
								</div>
								<div class="morecfieldoptions">
								</div>
								<div  class="form-group row input-type-<?php echo CF_FIELD_TYPE_CHECKBOX; ?> externalcfields" style="display:  none;">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom  Field Default Value</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<select name="cfielddefault1value" id="cfielddefault1value"><option value="0">no</option><option value="1">yes</option></select>
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="form-group row input-type-<?php echo CF_FIELD_TYPE_DROPDOWN; ?> input-type-<?php echo CF_FIELD_TYPE_RADIO; ?> externalcfields" style="display:  none;">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom  Field Default Value</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<input id="cfielddefault2value" name="cfielddefault2value" type="text" value="" placeholder="Optional">
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="form-group row input-type-<?php echo CF_FIELD_TYPE_TEXT; ?> input-type-<?php echo CF_FIELD_TYPE_TEXTAREA; ?> input-type-<?php echo CF_FIELD_TYPE_DATE; ?> externalcfields">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom  Field Placeholder</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<input name="cfieldplaceholder" id="cfieldplaceholder" type="text" value="" placeholder="Optional" /><br>
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom  Field Required?</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<select name="cfieldmandatory" id="cfieldmandatory">
											<option value="<?php echo CF_FIELD_REQUIRED_NO; ?>" selected="">no</option>
											<option value="<?php echo CF_FIELD_REQUIRED_YES; ?>">yes</option>
										</select>
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom  Field Mapped</label>
									<div class="col-lg-10 col-md-9 col-sm-12 col-12">
										<select name="cfieldmapping" id="cfieldmapping" class="cfieldmappingwith">
											<option value="donotmap">Do not mapped</option>
											<?php 
											    $fieldOptions = "";
											    foreach($preDefinedCustomFields as $key => $value) {
											        $fieldOptions .= "<optgroup label=\"$key\">";
											        foreach($value as $key1 => $value1) {
											            if (!in_array($key1, $alreadtMappedFields)) {   
											                $optionSelected = "";
											                $fieldOptions .= '<option value="'.$key1.'"'.$optionSelected.'>'.$value1.'</option>';
											            }
											        }
											        $fieldOptions .= "</optgroup>";
											    }
											    echo $fieldOptions;
											?>
										</select>
										<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry</div>
									</div>
								</div>
								<div class="row m-t-40">
									<div class="col-md-12 text-right">
										<div class="buttonloading savinggroupcfield" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
										<div class="alert-error-message groupcfielderror" style="display: none;"></div>
										<div class="alert-sucess-message groupcfieldsuccess" style="display: none;">Custom fields saved successfully.</div>
										<input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default restoregroupcfields" data-id="form_cfield">
										<input type="button" value="Save" class="btn btn-primary btn-radius btn-theme savingGroupCfieldBtn" onclick="savegroupcfield()">
									</div>
								</div>
							</form>
						</div>
	  				</div>
	        	</div>
	        	<div class="tab-pane fade" id="table_standard_fields_mapping" role="tabpanel" aria-labelledby="nav-home-tab">
	        	 	<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
	        	 	<div class="table-responsive" id="table_standard_fields_mapping_listing">
					</div>
	        	</div>
	        </div>
			<?php }else{
              echo $checkAuthenticationStatus;
        	} ?>
		</div>
  	</div>
</div>

<div id="cfieldmodelapp" class="modal" role="dialog">
 <div class="modal-dialog modal-lg">
	<div class="modal-content">
	    <div class="modal-header">
	      	<h2>Add Custom Field</h2>
	    	<span class="closeCfModel" onclick="hideCustomModel('cfieldmodelapp')">&times;</span>
        </div>
	    <div class="modal-body customfieldsModal" style="height:600px; overflow:auto;">
	    	<span class="ajax_loader_custom_fields_related" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
	      	<form method="POST" accept-charset="utf-8" id="addcfieldapp">
	      		<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field for</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfieldformtypeapp" id="cfieldformtypeapp" class="">
						<option value="<?php echo CUSTOM_FIELD_FORM_TYPE_CONTACT ?>" selected="" id="<?php echo CUSTOM_FIELD_FORM_TYPE_CONTACT ?>">Contact</option>
		                <option value="<?php echo CUSTOM_FIELD_FORM_TYPE_ORDER ?>" id="<?php echo CUSTOM_FIELD_FORM_TYPE_ORDER ?>">Order</option>
					</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field name</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<input class="textFild" name="cfieldnameapp" id="cfieldnameapp" type="text" maxlength="64" value="">
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field type</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfieldtypeapp" id="cfieldtypeapp" class="">
							<option value="PhoneNumber">Phone Number</option>
							<option value="Currency">Currency</option>
							<option value="Percent">Percent</option>
							<option value="State">State</option>
							<option value="YesNo">Yes/No</option>
							<option value="Year">Year</option>
							<option value="Month">Month</option>
							<option value="Name">Name</option>
							<option value="Date">Date</option>
							<option value="DateTime">Date/Time</option>
							<option value="Text" selected="selected">Text</option>
							<option value="TextArea">TextArea</option>
							<option value="Website">Website</option>
							<option value="Email">Email</option>
							<option value="Radio">Radio</option>
						</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field tab</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfieldtabapp" id="cfieldtabapp" class="cfieldtabapp">
							<option value="">Select Tab</option>
						</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row cfield_header">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field header</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfieldheaderapp" id="cfieldheaderapp" class="cfieldheaderapp">
							<option value="">Select Header</option>
						</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="row m-t-40">
					<div class="col-md-12 text-right">
						<div class="buttonloading savingcfieldapp" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
						<div class="alert-error-message cfieldapperror" style="display: none;"></div>
						<div class="alert-sucess-message cfieldappsuccess" style="display: none;">Custom field to infusionsoft/keap saved successfully.</div>
						<input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default" onclick="hideCustomModel('cfieldmodelapp')">
						<input type="button" value="Save" class="btn btn-primary btn-radius btn-theme savingCfieldAppBtn" onclick="savecfieldapp()">
					</div>
				</div>
			</form>
	    </div>
		</div>
  	</div>
</div>
<script type="text/javascript">
	var CF_FIELD_TYPE_TEXT = '<?php echo CF_FIELD_TYPE_TEXT ?>';
	var CF_FIELD_TYPE_TEXTAREA = '<?php echo CF_FIELD_TYPE_TEXTAREA ?>';
	var CF_FIELD_TYPE_DROPDOWN = '<?php echo CF_FIELD_TYPE_DROPDOWN ?>';
	var CF_FIELD_TYPE_RADIO = '<?php echo CF_FIELD_TYPE_RADIO ?>';
	var CF_FIELD_TYPE_CHECKBOX = '<?php echo CF_FIELD_TYPE_CHECKBOX ?>';
	var CF_FIELD_TYPE_DATE = '<?php echo CF_FIELD_TYPE_DATE ?>';

	var CF_FIELD_ACTION_SHOW = '<?php echo CF_FIELD_ACTION_SHOW ?>';
	var CF_FIELD_ACTION_HIDE = '<?php echo CF_FIELD_ACTION_HIDE ?>';
</script>