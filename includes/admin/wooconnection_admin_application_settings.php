<!--Settings-->
<?php
	$pluginDetailsArray = getPluginDetails();//Get plugin activation related details....
	$configurationType = applicationType();//Get the application type so that application type selected from dropdown.....
	$selectedTypeIs = 'checked';//set radio(infusionsoft) checked by default.....
	$selectedTypeKp = '';//define variable...
	$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
	if(isset($configurationType) && !empty($configurationType)){
		if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
			$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
			$selectedTypeIs = 'checked';//set radio(infusionsoft) checked
		}else if ($configurationType == APPLICATION_TYPE_KEAP) {
			$selectedTypeKp = 'checked';//set radio(keap) checked by default.....
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
	<p class="heading-text m-b-40 m-t-20">There are 2 simple steps to connect your Keap or Infusionsoft account to your WooCommerce site. Simply choose the edition of the software you're using (Keap or Infusionsoft) and then click the authorize button. You will be taken to a login screen that will let you give WooConnection the permissions it needs to access your account. We'll take it from there.</p>
	<form action="" method="post" id="application_settings_form" onsubmit="return false">
		<input type="hidden" name="activationEmail" id="activationEmail" value="<?php echo $pluginDetailsArray['activation_email']; ?>">
		<input type="hidden" name="activationKey" id="activationKey" value="<?php echo $pluginDetailsArray['activation_key']; ?>">
		<input type="hidden" name="siteUrl" id="siteUrl" value="<?php echo SITE_URL; ?>">
		<div class="form-group row">
			<label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Which Edition Are You Using?</label>
			<div class="col-lg-10 col-md-9 col-sm-12 col-12">
			   	<input type="radio" id="<?php echo APPLICATION_TYPE_INFUSIONSOFT; ?>" name="applicationtype" value="<?php echo APPLICATION_TYPE_INFUSIONSOFT; ?>" <?php echo $selectedTypeIs; ?>>Infusionsoft
			  	<input type="radio" id="<?php echo APPLICATION_TYPE_KEAP; ?>" name="applicationtype" value="<?php echo APPLICATION_TYPE_KEAP; ?>" <?php echo $selectedTypeKp; ?>>Keap
				<div class="note-bottom">Choose which product you are using (Keap or Infusionsoft). This will make sure that we only enable the features in WooConnection that will work with your edition of the software.</div>
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
<!--Settings END-->