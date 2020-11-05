<?php 
//check the application authentication status if authorized then give access to export products....
$checkAuthenticationStatus = applicationAuthenticationStatus();
//Get the application type so that application type selected from dropdown.....
$configurationType = applicationType();
$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
if(isset($configurationType) && !empty($configurationType)){
	if($configurationType == APPLICATION_TYPE_INFUSIONSOFT){
		$type = APPLICATION_TYPE_INFUSIONSOFT_LABEL;
	}else if ($configurationType == APPLICATION_TYPE_KEAP) {
		$type = APPLICATION_TYPE_KEAP_LABEL;
	}
}
//Get the application lable to display.....
$applicationLabel = applicationLabel($type);
?>
<div class="info-header">
    <p><?php echo IMPORT_EXPORT_LABEL_FREE; ?> Products</p>
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
		          <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#table_export_products" role="tab" aria-controls="nav-profile" aria-selected="false">Export WooCommerce Products to <?php echo $applicationLabel; ?></a>
				  <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#table_match_products" role="tab" aria-controls="nav-profile" aria-selected="false">Match Products</a>
		        </div>
	        </nav>
	        <div class="tab-content" id="nav-tabContent">
	        	<div class="tab-pane fade show active" id="table_export_products" role="tabpanel" aria-labelledby="nav-home-tab">
		       		<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
		       		<div class="table-responsive export_products_listing_class" id="table_export_products_listing">
						<?php echo createExportProductsHtml(); ?>
					</div>
				</div>
		        <div class="tab-pane fade" id="table_match_products" role="tabpanel" aria-labelledby="nav-home-tab">
		        	<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
		        	<div class="table-responsive" id="table_match_products_listing">
					</div>
		        </div>
	        </div>
	        <?php }else{
	              echo $checkAuthenticationStatus;
	        } ?>
	    </div>
	</div>
</div>