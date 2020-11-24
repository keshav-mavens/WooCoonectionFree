<!--REFERRAL PARTNER SETUP-->
<?php 
//check the application authentication status if authorized then give access to configure campaign goals....
$checkAuthenticationStatus = applicationAuthenticationStatus();
?>
<div class="info-header">
  <p>REFERRAL PARTNERS</p>
</div>
<div class="righttextInner">
	<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>  
	<div class="row">
        <div class="col-md-12 ">
			<?php if(empty($checkAuthenticationStatus)){?>
			<p class="text-right">
				<a class="btn btn-primary btn-theme" data-toggle="collapse" href="#collapseReferralPartner" role="button" aria-expanded="false" aria-controls="collapseReferralPartner">
				How This Works
				<i class="fa fa-caret-down" id="icon_collapseReferralPartner" aria-hidden="true"></i>
				</a>
			</p>
			<div class="collapse" id="collapseReferralPartner">
			  	<div class="card card-body col-md-12 m-b-40">
			    	<p class="heading-text text-center m-t-30">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
					<iframe class="m-t-30" src="https://player.vimeo.com/video/60771693" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
			  	</div>
			</div>
          	<nav>
            	<div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
	              	<a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#tab-5" role="tab" aria-controls="nav-profile" aria-selected="false">Affiliate Tracking Links</a>
				 	<a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#tab-6" role="tab" aria-controls="nav-profile" aria-selected="false">Custom Tracking Link</a>
			 	</div>
          	</nav>
            <div class="tab-content" id="nav-tabContent">
			  	<div class="tab-pane fade show active" id="tab-5" role="tabpanel" aria-labelledby="nav-profile-tab">
                  	<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
				  	<h4>Pages Listing With Affiliate Links</h4>
				  	<div class="table-responsive">
					  	<table class="table table-striped">
						    <thead>
						      <tr>
						        <th>Page Name</th>
						        <th>Page Affiliate Link</th>
								<th>Action</th>
						      </tr>
						    </thead>
						    	<?php echo wc_standard_pages_listing(); ?>
						    <tbody>
						    </tbody>
					  	</table>
					</div>
					<h4>Products Categories Listing</h4>
				  	<div class="table-responsive">
					  	<table class="table table-striped">
						    <thead>
						      <tr>
						        <th>Category Name</th>
						        <th>Action</th>
						      </tr>
						    </thead>
						    	<?php echo wc_standard_categories_listing(); ?>
						    <tbody>
						    </tbody>
					  	</table>
					</div>
				</div>
				<div class="tab-pane fade" id="tab-6" role="tabpanel" aria-labelledby="nav-home-tab">
                  	<p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
				</div>
			</div>
			<?php }else{
	              echo $checkAuthenticationStatus;
	        } ?>
        </div>
    </div>
</div>

<!--Below model is used to show the list of products with their affiliate tracking link with copy feature-->
<div class="modal" role="dialog" id="productsWithAffiliateLInks">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Products Listing With Affiliate Links</h4>
        <button type="button" class="close" onclick="hideCustomModel('productsWithAffiliateLInks')">&times;</button>
      </div>
      <div class="modal-body">
      	<div class="table-responsive">
			<table class="table table-striped">
			    <thead>
			      <tr>
			        <th>Product Name</th>
			        <th>Affiliate Link</th>
			        <th>Action</th>
			      </tr>
			    </thead>
			    <tbody id="productsAffiliateLinks">
			    	<tr><td colspan="3" style="text-align: center; vertical-align: middle;">Loading Products.....</td></tr>
			    </tbody>
			</table>
      	</div>
      </div>
    </div>
  </div>
</div>
<!--REFERRAL PARTNER SETUP END-->