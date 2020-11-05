<div class="col-md-12">
  <div class="white-box">
    <div class="top_logo_row">
      <div class="row">
        <div class="col-xl-2 col-lg-3 col-md-3 col-12">
		        <a href="javascript:void(0)"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/main_logo.jpg" alt=""></a>
		    </div>
    		<div class="col-xl-8 col-lg-7 col-md-7 col-12">
    		    <p class="tag-line">The Easiest Way to Connect Your WooCommerce Site to Infusionsoft and Keap</p>
    		</div>
		    <div class="col-xl-2 col-lg-2 col-md-2 col-12 text-right">
          <a href="https://www.wooconnection.com/support" target="_blank" class="btn btn-primary btn-radius btn-theme">Help</a>
        </div>
      </div>
    </div>
    <div class="content-body-row">
      <div class="body-container">
        <div class="row">
          <?php require_once('wooconnection_admin_menus.php'); ?>
  		  	<div class="col-lg-9 col-md-8 col-sm-8 col-12 p-l-0 p-r-0 tab_related_content">
  		  	<?php
            //check the application authentication status if authorized then show the campaign goals page content else show the getting started tab content....
            $checkAuthenticationStatus = applicationAuthenticationStatus();
            if(empty($checkAuthenticationStatus)){
              require_once('wooconnection_admin_campaign_goals.php');//call the campaign goals file....
            }else{
              require_once('wooconnection_admin_guided_setup.php');//call the guided setup file....
            }
          ?>
  		  	</div>
          <div class="col-lg-9 col-md-8 col-sm-8 col-12 p-l-0 p-r-0 import_tab_content" style="display: none;">
            <?php require_once('wooconnection_admin_import_products.php'); ?>
          </div>
		    </div>
      </div>
	  </div>
  </div>
</div>

