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
	$preDefinedCustomFields = getPredefindCustomfields();
	//check the application authentication status if authorized then give access to configure campaign goals....
	$checkAuthenticationStatus = applicationAuthenticationStatus();
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
			<div class="main_rendered">
		      	<div class="loading_custom_fields" style="display: none;">
			      	<img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.svg">
		      	</div>
		      	<ul class="main-group">
				</ul>
			  	<div class="text-center m-t-50">
			  		<span class="create-new-group"><span class="default_message">We don't have any Custom Fields</span> <a href="javascript:void(0)" class="btn btn-theme third addnewgroup"><span>Create Group</span></a></span>
			  	</div>
			</div>
			<?php }else{
              echo $checkAuthenticationStatus;
        	} ?>
		</div>
  	</div>
</div>

<div id="addCustomFieldModel" class="modal" role="dialog" style="display: block;">
 <div class="modal-dialog modal-lg">
	<div class="modal-content">
	    <div class="modal-header">
	      	<h2>Add Custom Field</h2>
	    	<span class="closeCfModel" onclick="hideCustomModel('addCustomFieldModel')">&times;</span>
        </div>
	    <div class="modal-body" style="height:600px; overflow:auto;">
	      	<form method="POST" accept-charset="utf-8" id="add_custom_field_form">
	      		<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field for</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfFormType" id="cfFormType" class="">
						<option value="<?php echo CUSTOM_FIELD_FORM_TYPE_CONTACT ?>" selected="" id="<?php echo CUSTOM_FIELD_FORM_TYPE_CONTACT ?>">Contact</option>
		                <option value="<?php echo CUSTOM_FIELD_FORM_TYPE_ORDER ?>" id="<?php echo CUSTOM_FIELD_FORM_TYPE_ORDER ?>">Order</option>
					</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field name</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<input class="textFild" name="cfname" id="cfname" type="text" maxlength="64" value="">
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field type</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfDataType" id="cfDataType" class="">
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
						<select name="cftab" id="cftab" class="">
							<?php echo cfRelatedTabs(); ?>
						</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="form-group row custom_field_header">
					<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom field header</label>
					<div class="col-lg-10 col-md-9 col-sm-12 col-12">
						<select name="cfheader" id="cfheader" class="">
							<!-- <option value="">Select header</option> -->
							<?php echo cfRelatedHeaders(); ?>
						</select>
						<div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
					</div>
				</div>
				<div class="row m-t-40">
					<div class="col-md-12 text-right">
						<div class="buttonloading addcf" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
						<div class="alert-error-message add-cf-error" style="display: none;"></div>
						<div class="alert-sucess-message add-cf-success" style="display: none;">Custom field to infusionsoft/keap saved successfully.</div>
						<input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default" onclick="hideCustomModel('addCustomFieldModel')">
						<input type="button" value="Save" class="btn btn-primary btn-radius btn-theme save_cf_btn" onclick="saveCustomFields()">
					</div>
				</div>
			</form>
	    </div>
		</div>
  	</div>
</div>