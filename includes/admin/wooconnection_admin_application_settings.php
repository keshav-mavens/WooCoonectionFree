<!--Settings-->
<?php
	$pluginDetailsArray = getPluginDetails();//Get plugin activation related details....
	$configurationType = applicationType();//Get the application type so that application type selected from dropdown.....
	$selectedTypeIs = 'selected';
	$selectedTypeKp = '';
	$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
	if(isset($configurationType) && !empty($configurationType)){
		if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
			$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
			$selectedTypeIs = 'selected';
		}else if ($configurationType == APPLICATION_TYPE_KEAP) {
			$selectedTypeKp = 'selected';
			$type = APPLICATION_TYPE_KEAP_LABEL;
		}
	}
	//Get the application lable to display.....
	$applicationLabel = applicationLabel($type);
	//Set the text in place of application name..
	$connectedApplicationName = 'Please authorize the application first.';
	//Get the application name to display in front of application name......
	$applicationName = applicationName();
	if(isset($applicationName) && !empty($applicationName)){
		$connectedApplicationName = $applicationName;	
	}
?>
<div class="info-header">
  <p><span class="applicationtype"><?php echo $applicationLabel; ?></span> Settings</p>
</div>
<div class="righttextInner"> 
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
	<p class="heading-text m-b-40 m-t-20">To connect your woocommerce site to <span class="applicationtype"><?php echo $applicationLabel; ?></span> application, please first authorize the application.</p>
	<form action="" method="post" id="application_settings_form" onsubmit="return false">
		<input type="hidden" name="activationEmail" id="activationEmail" value="<?php echo $pluginDetailsArray['activation_email']; ?>">
		<input type="hidden" name="activationKey" id="activationKey" value="<?php echo $pluginDetailsArray['activation_key']; ?>">
		<input type="hidden" name="siteUrl" id="siteUrl" value="<?php echo SITE_URL; ?>">
		<div class="form-group row">
			<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Application Type</label>
			<div class="col-lg-10 col-md-9 col-sm-12 col-12">
			<select name="applicationtype" id="applicationtype" aria-invalid="false">
				<option value="<?php echo APPLICATION_TYPE_INFUSIONSOFT; ?>" <?=$selectedTypeIs;?>>Infusionsoft</option>
				<option value="<?php echo APPLICATION_TYPE_KEAP; ?>" <?=$selectedTypeKp;?>>Keap</option>
			</select>
			<div class="note-bottom">Select integration type. Eg: Infusionsoft, Keap</div>
			</div>
		</div>
		
		<div class="form-group row">
			<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Application Name</label>
			<div class="col-lg-10 col-md-9 col-sm-12 col-12">
				<label class="col-form-label" style="background: #f5f5f5;padding: 10px 15px;border-radius: 5px;"><?php echo $connectedApplicationName; ?></label>
			</div>
		</div>
		
		<div class="form-group col-md-12 text-right m-t-60">
			<div class="buttonloading savingDetails" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Authorize....</div>
			<div class="alert-error-message application-details-error" style="display: none;"></div>
			<div class="alert-sucess-message application-details-success" style="display: none;">Application settings saved successfully.</div>
			<input type="button" value="Authorize" class="btn btn-primary btn-radius btn-theme save_application_settings" onclick="saveApplicationSettings()">
		</div>
	</form>
</div>
<script type="text/javascript">
	var ADMIN_REMOTE_URL = '<?php echo  ADMIN_REMOTE_URL ?>';
</script>
<!-- <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script> -->
<!--Settings END