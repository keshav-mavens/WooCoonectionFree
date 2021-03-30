<!--LEAR SOURCE SETUP-->
<?php 
//check the application authentication status if authorized then give access to check lead source data....
$checkAuthenticationStatus = applicationAuthenticationStatus();
?>
<div class="info-header">
  <p>Tracking Leadsouce</p>
</div>
<div class="righttextInner" >
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
	<div class="row">
        <div class="col-md-12 lead_source_main_html">
        	<?php if(empty($checkAuthenticationStatus)){
        			//get the lead source tracking status......
					$checkLeadTrackingStatus = get_option('lead_source_tracking_status',true);
					//define empty value.....
					$toggleValue = "";
					//set the value on the basis of database...
					if(isset($checkLeadTrackingStatus) && !empty($checkLeadTrackingStatus)){
						$toggleValue = $checkLeadTrackingStatus;
					}
        	?>
	        	<p class="text-right"><a class="btn btn-primary btn-theme" data-toggle="collapse" href="#collapseLeadSource" role="button" aria-expanded="false" aria-controls="collapseLeadSource">How This Works <i class="fa fa-caret-down" id="icon_collapseLeadSource" aria-hidden="true"></i></a></p>
			    <div class="collapse" id="collapseLeadSource">
			        <div class="card card-body col-md-12 m-b-40">
			            <p class="heading-text text-center m-t-30">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
			        		<iframe class="m-t-30" src="https://player.vimeo.com/video/477453672" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
			        </div>
			    </div>
			    <p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
				<label style="margin-right:10px">Enable/Disable Lead Source Tracking In Infusionsoft</label>
				<label class="switch" style="cursor: pointer;"><input value="<?php echo $toggleValue; ?>" <?php if($toggleValue == 'On'){ echo 'checked'; } ?> type="checkbox" name="lead_tracking" id="lead_tracking"><span class="slider round"></span></label>
				<?php }else{
		              echo $checkAuthenticationStatus;
		        } ?>
        </div>
    </div> 
</div>
 <!--LEAR SOURCE SETUP END-->