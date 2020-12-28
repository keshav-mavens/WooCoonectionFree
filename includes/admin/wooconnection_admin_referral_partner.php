<!--REFERRAL PARTNER SETUP-->
<?php 
//check the application authentication status if authorized then give access to configure campaign goals....
$checkAuthenticationStatus = applicationAuthenticationStatus();
$pageDetails = affiliatePageDetails();
$pageSlug = '';
$pageUrl = '';
if(isset($pageDetails['affiliatePageSlug']) && !empty($pageDetails['affiliatePageSlug'])){
	$pageSlug = $pageDetails['affiliatePageSlug'];
}
if(isset($pageDetails['pageUrl']) && !empty($pageDetails['pageUrl'])){
	$pageUrl = $pageDetails['pageUrl'];
}
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
					  	<table class="table table-striped pages_with_aff_links">
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
					<form action="" method="post" id="affiliate_redirect_form" onsubmit="return false">
	                  	<div class="form-group row">
							<label class="col-lg-4 col-md-3 col-sm-12 col-12 col-form-label">Redirect Slug</label>
							<div class="col-lg-8 col-md-9 col-sm-12 col-12">
								<input class="form-control" type="text" placeholder="Enter Affiliate Redirect Slug" name="affiliateredirectslug" id="affiliateredirectslug"  value="<?php echo $pageSlug; ?>">
								<div class="note-bottom">Help article link for customize referral tracking url : <a href="https://help.infusionsoft.com/help/customize-a-referral-tracking-link-url" target="_blank">Click here</a></div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-lg-4 col-md-3 col-sm-12 col-12 col-form-label" style="padding-top: 0px;">Custom Referral Partner link URL</label>
							<div class="col-lg-8 col-md-9 col-sm-12 col-12">
								<span id="custom_affiliate_redirect_url"><?php echo $pageUrl; ?></span>
								<span><i class="fa fa-copy" style="cursor:pointer;padding-left: 10px;" onclick="copyContent('custom_affiliate_redirect_url')"></i></span>
							</div>
						</div>
						<div class="form-group col-md-12 text-right m-t-60">
							<div class="buttonloading affiliateRedirectUrl" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
							<div class="alert-error-message affiliate-redirect-error" style="display: none;"></div>
							<div class="alert-sucess-message affiliate-redirect-success" style="display: none;">Affiliate rediect slug updated successfully.</div>
							<input type="button" value="Save" class="btn btn-primary btn-radius btn-theme affiliate_redirect_btn" onclick="saveAffiliateRedirectSlug()">
						</div>
					</form>
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
			<table class="table table-striped" id="products_listing_with_aff_links">
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