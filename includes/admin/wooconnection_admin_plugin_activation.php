<!--ACTIVATION SETUP-->
<div class="info-header">
  <p>Activation Setup</p>
</div>
<div class="righttextInner"> 
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
	<h5>Enter activation email and activation key to activate a plugin.</h5>
	<div class="form-area">
		<form action="" method="post" id="activation_setup_form" onsubmit="return false">
			<div class="form-group col-md-12">
				<input class="form-control" type="email" placeholder="Activation License Email" id="pluginactivationemail" name="pluginactivationemail" value="">
			</div>
			
			<div class="form-group col-md-12">
				<input class="form-control" type="text" placeholder="Activation License Key" id="pluginactivationkey" name="pluginactivationkey" value="">
			</div>
			
			<div class="form-group col-md-12 text-center m-t-60">
				<div class="pluginActivation" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Verify Details for activation....</div>
				<div class="alert-error-message activation-details-error" style="display: none;"></div>
				<div class="alert-sucess-message activation-details-success" style="display: none;">Plugin activated successfully.</div>
				<input type="button" value="Activate" class="btn btn-primary btn-radius btn-theme plugin_activation_btn" onclick="activateWcPlugin()">
			</div>
		</form>
	</div>
</div>
 <!--ACTIVATION SETUP END-->