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
	//$ifscustomfields = get_predefind_customfields();
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