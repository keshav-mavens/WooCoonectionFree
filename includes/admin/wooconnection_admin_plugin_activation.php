<!--ACTIVATION SETUP-->
<?php
$pluginActivationDetails = getPluginDetails();//get the plugin activation details....
//define empty variables.....
$pluginEmail = '';
$pluginKey = '';
$activateButtonClass = '';
//first check whether plugin is activated or not if activated then set the values in variables e.g license email,license key and disable button class.....
if(!empty($pluginActivationDetails['plugin_activation_status']) && $pluginActivationDetails['plugin_activation_status'] == PLUGIN_ACTIVATED){
	$activateButtonClass = 'disable_anchor';//set disable class....
	if(isset($pluginActivationDetails['activation_email']) && !empty($pluginActivationDetails['activation_email'])){
		$pluginEmail = $pluginActivationDetails['activation_email'];//set activation email....
	}
	if(isset($pluginActivationDetails['activation_key']) && !empty($pluginActivationDetails['activation_key'])){
		$pluginKey = $pluginActivationDetails['activation_key'];//set activation key....
	}
} 
?>
<div class="info-header">
  <p>Activation Setup</p>
</div>
<div class="righttextInner"> 
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
	<h5>Enter your email and activation key to activate WooConnection. Your activation key was emailed to you after you purchased the plugin.</h5>
    <div class="form-area">
		<form action="" method="post" id="activation_setup_form" onsubmit="return false">
			<input type="hidden" name="activationEmail" id="activationEmail" value="<?php echo $pluginEmail; ?>">
			<input type="hidden" name="activationKey" id="activationKey" value="<?php echo $pluginKey; ?>">
			<div class="form-group col-md-12">
				<input class="form-control" type="email" placeholder="Activation License Email" id="pluginactivationemail" name="pluginactivationemail" value="<?php echo $pluginEmail; ?>">
			</div>
			
			<div class="form-group col-md-12">
				<input class="form-control" type="text" placeholder="Activation License Key" id="pluginactivationkey" name="pluginactivationkey" value="<?php echo $pluginKey; ?>">
			</div>
			
			<div class="form-group col-md-12 text-center m-t-60">
				<div class="pluginActivation" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Verify Details for activation....</div>
				<div class="alert-error-message activation-details-error" style="display: none;"></div>
				<div class="alert-sucess-message activation-details-success" style="display: none;">Plugin activated successfully.</div>
				<input type="button" value="Activate" class="btn btn-primary btn-radius btn-theme plugin_activation_btn <?php echo $activateButtonClass; ?>" onclick="activateWcPlugin()">
			</div>
		</form>
	</div>
</div>
 <!--ACTIVATION SETUP END-->