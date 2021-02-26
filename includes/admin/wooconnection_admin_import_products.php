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
    <p>Import and Match Products</p>
</div>
<div class="righttextInner">
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>  
	<div class="row">
	    <div class="col-md-12 ">
	    	<?php if(empty($checkAuthenticationStatus)){?>
		  	<p class="heading-text">
		      	<p class="heading-text">
                    On this screen is where you're able to export the existing products in WooCommerce to your <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?> account.
                    You are also able to match your WooCommerce products up to existing products in your <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?> account.
                    These two important options make sure that when we record your orders in your <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?>, they get recorded properly using the right products</p>
                    <span>Click on icon to to reload the latest product from <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL ?> application </span><a href="javascript:void(0)" onclick="reloadLatestAppProducts()"> <i class="fa fa-refresh" aria-hidden="true"></i></a>
            </p>
	      	<nav>
		        <div class="nav nav-tabs nav-fill custom-nav-tabs" id="nav-tab" role="tablist">
		          <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#table_export_products" role="tab" aria-controls="nav-profile" aria-selected="false">Export WooCommerce Products to <?php echo $applicationLabel; ?></a>
				  <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#table_match_products" role="tab" aria-controls="nav-profile" aria-selected="false">Match Products</a>
		        </div>
	        </nav>
	        <div class="tab-content" id="nav-tabContent">
	        	<div class="tab-pane fade show active" id="table_export_products" role="tabpanel" aria-labelledby="nav-home-tab">
		       		<p class="heading-text">Select the WooCommerce products below that you would like to add to your <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?> account and press the Export Products button (you may need to scroll to the bottom of the list to see it).</p>
		       		<input type="hidden" id="scroll_count_table_export_products" value="0" class="scroll_counter">
		       		<input type="hidden" id="products_limit_export" value="100" class="products_limit">
		       		<div class="table-responsive export_products_listing_class" id="table_export_products_listing">
						<?php echo createExportProductsHtml(); ?>
					</div>
				</div>
		        <div class="tab-pane fade" id="table_match_products" role="tabpanel" aria-labelledby="nav-home-tab">
		        	<p class="heading-text">Match up the existing WooCommerce products to the existing <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?> products by choosing the appropriate <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?> product from the dropdown menu. This will control which product in <?php echo APPLICATION_TYPE_INFUSIONSOFT_LABEL; ?> we use when recording the orders. When you're done, don't forget to click the Update Mapping button at the bottom of the list.</p>
		        	<input type="hidden" id="scroll_count_table_match_products" value="0" class="scroll_counter">
		        	<input type="hidden" id="products_limit_match" value="100" class="scroll_counter">
		        	<div class="table-responsive" id="table_match_products_listing">
					</div>
					<div class="load_table_match_products loading_products" style="text-align: center;display: none;"></div>
		        </div>
	        </div>
	        <?php }else{
	              echo $checkAuthenticationStatus;
	        } ?>
	    </div>
	</div>
</div>
<script type="text/javascript">
	var PRODUCT_LAZY_LOADING_LIMIT = '<?php echo PRODUCT_LAZY_LOADING_LIMIT ?>';
	var PRODUCT_LAZY_LOADING_OFFSET = '<?php echo PRODUCT_LAZY_LOADING_OFFSET ?>';
</script>