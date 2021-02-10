<!--Campaign Bundle SETUP-->
<?php 
	//check the application authentication status if authorized then give access to configure campaign goals....
	$checkAuthenticationStatus = applicationAuthenticationStatus();
?>
<div class="info-header">
  <p>Campaign Bundle</p>
</div>
<div class="righttextInner"> 
 	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
 	<div class="row">
        <div class="col-md-12 ">
 		<?php if(empty($checkAuthenticationStatus)){
				require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/admin/webFormHtmlCode.html'); 
 			  }else{
 			  	echo $checkAuthenticationStatus;
 			  }
 		?>
 		</div>
 	</div>
</div>
 <!--Campaign Bundle END-->