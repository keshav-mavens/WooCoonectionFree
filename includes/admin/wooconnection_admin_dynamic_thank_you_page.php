<!--Dynamic Thankspage SETUP-->
<?php 
//check the application authentication status if authorized then give access to configure campaign goals....
$checkAuthenticationStatus = applicationAuthenticationStatus();
?>
<div class="info-header">
  <p>Dynamic Thankyou Page</p>
</div>
<div class="righttextInner">
<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span> 
  <div class="row">
    <div class="col-md-12 ">
       <?php if(empty($checkAuthenticationStatus)){
          ?>
          <p class="heading-text">
          <p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.<br/>
          <br/>
          Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
          </p>
          <div class="main_rendered_thank_overrides">
              <ul class="main-override">
                  <li class="group-list">
                    <span class="group-name">Setup Default Thank You Page
                        <span class="controls">
                            <i class="fa fa-pencil edit_default_thankpage_override" title="Edit default thankyou page override"></i>
                        </span>
                    </span>
                  </li>
                  <li class="group-list">
                    <span class="group-name">Thank You Page Product Rules
                        <span class="controls">
                            <i class="fa fa-plus add_product_rules" title="Add New Thank You Page Override Product Rules"></i>
                        </span>
                    </span>
                    <span id="product_thank_overrides"><?php echo loading_product_thanks_overrides(); ?></span>
                  </li>
                  <li class="group-list">
                    <span class="group-name">Thank You Page Product Category Rules
                        <span class="controls">
                            <i class="fa fa-plus add_product_category_rules" title="Add New Thank You Page Override Product Category Rules"></i>
                        </span>
                    </span>
                    <span id="product_cat_thank_overrides"><?php echo loading_product_cat_thanks_overrides(); ?></span>
                  </li>
              </ul>
          </div>
          <div class="add-editform-override">
            <div class="hide defaultoverride">
              <form action="" method="post" id="thank_default_form" onsubmit="return false">
                <input type="hidden" name="defaultoverrideid" id="defaultoverrideid" value="" />
                <h5 class="text-left thankyou_default_title">Create Thankyou Page Override</h5>
                  <div class="form-group row">
                    <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Redirect Settings</label>
                    <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                        <select name="overrideredirecturltype" id="overrideredirecturltype">
                          <option value="<?php echo DEFAULT_WORDPRESS_POST; ?>">Wordpress Post</option>
                          <option value="<?php echo DEFAULT_WORDPRESS_PAGE; ?>">Wordpress Page</option>
                          <option value="<?php echo DEFAULT_WORDPRESS_CUSTOM_URL; ?>">Custom Url</option>
                        </select>
                        <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                    </div>
                  </div>
                  <div class="form-group row redirect-type-common" id="redirect-type-post">
                    <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Select Wordpress Post</label>
                    <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                        <select name="redirectwordpresspost" id="redirectwordpresspost" class="redirectpostsselect">
                            <option value="">Select Post</option>
                            <?php echo get_wp_posts(); ?>
                        </select>
                        <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                    </div>
                  </div>
                  <div class="form-group row redirect-type-common" id="redirect-type-page" style="display: none;">
                    <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Select Wordpress Page</label>
                    <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                        <select name="redirectwordpresspage" id="redirectwordpresspage" class="redirectpagesselect">
                            <option value="">Select Page</option>
                            <?php echo get_wp_pages(); ?>
                        </select>
                        <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                    </div>
                  </div>
                  <div class="form-group row redirect-type-common" id="redirect-type-custom-url" style="display: none;">
                    <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Custom Url</label>
                    <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                        <input class="form-control" name="customurl" id="customurl" type="text" placeholder="Custom Url">
                        <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                    </div>
                  </div>
                  <div class="row m-t-40">
                    <div class="col-md-12 text-right">
                        <div class="buttonloading savingDefaultOverrideDetails" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
                        <input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default restore_overrides" data-id="thank_default_form">
                        <input type="button" value="Save" class="btn btn-primary btn-radius btn-theme save_thank_you_default_override" onclick="saveThanksDefaultOverride()">
                        <div class="alert-error-message override-error" style="display: none;"></div>
                        <div class="alert-sucess-message override-success" style="display: none;">Thanks Page default override saved successfully.</div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="add-editproductrule-override">
              <div class="hide productoverride">
                <form action="" method="post" id="thank_override_form_product" onsubmit="return false">
                  <input type="hidden" name="productoverrideid" id="productoverrideid" value="" />
                  <h5 class="text-left thankyou_override_title_product">Create Product Rule Override</h5>
                  <div class="form-group row">
                      <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Name</label>
                      <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                          <input class="form-control" name="procductoverridename" id="procductoverridename" type="text" placeholder="Name">
                          <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                      </div>
                  </div>
                  <div class="form-group row">
                      <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Redirect URL</label>
                      <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                          <input class="form-control" name="productrediecturl" id="productrediecturl" type="text" placeholder="Redirect URL">
                          <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                      </div>
                  </div>
                  <div class="form-group row" id="redirect-condition-cartproducts">
                      <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Select Cart Products</label>
                      <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                          <select name="redirectcartproducts[]" id="redirectcartproducts" class="redirectcartproductsselect">
                              <?php echo get_products_options(); ?>
                          </select>
                          <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                      </div>
                  </div>
                  <div class="row m-t-40">
                      <div class="col-md-12 text-right">
                          <div class="buttonloading savingProductOverrideDetails" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
                          <input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default restore_overrides" data-id="thank_override_form_product">
                          <input type="button" value="Save" class="btn btn-primary btn-radius btn-theme save_thank_you_product_override" onclick="saveThanksProductOverride()">
                          <div class="alert-error-message override-error" style="display: none;"></div>
                          <div class="alert-sucess-message override-success" style="display: none;">Thanks Page product override saved successfully.</div>
                      </div>
                  </div>
                </form>
              </div>
            </div>
               <div class="add-editproductrule-override">
                  <div class="hide productcatoverride">
                      <form action="" method="post" id="thank_override_form_product_cat" onsubmit="return false">
                      <input type="hidden" name="productcatoverrideid" id="productcatoverrideid" value="" />
                      <h5 class="text-left thankyou_override_title_product_cat">Create Product Category Rule Override</h5>
                      <div class="form-group row">
                          <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Name</label>
                          <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                              <input class="form-control" name="productcatoverridename" id="productcatoverridename" type="text" placeholder="Name">
                              <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                          </div>
                      </div>
                      <div class="form-group row">
                          <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Redirect URL</label>
                          <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                              <input class="form-control" name="productcatrediecturl" id="productcatrediecturl" type="text" placeholder="Redirect URL">
                              <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                          </div>
                      </div>
                      <div class="form-group row" id="redirect-condition-cartcategories">
                        <label class="col-lg-2 col-md-3 col-sm-12 col-12 col-form-label">Select Cart Categories</label>
                        <div class="col-lg-10 col-md-9 col-sm-12 col-12">
                            <select name="redirectcartcategories[]" id="redirectcartcategories" class="redirectcartcategoriesselect">
                                <?php echo get_category_options(); ?>
                            </select>
                            <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                        </div>
                      </div>
                      <div class="row m-t-40">
                          <div class="col-md-12 text-right">
                              <div class="buttonloading savingProductCatOverrideDetails" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
                              <input type="button" value="Cancel" class="btn btn-primary btn-radius btn-theme-default restore_overrides" data-id="thank_override_form_product_cat">
                              <input type="button" value="Save" class="btn btn-primary btn-radius btn-theme save_thank_you_product_cat_override" onclick="saveThanksProductCatOverride()">
                              <div class="alert-error-message override-error" style="display: none;"></div>
                              <div class="alert-sucess-message override-success" style="display: none;">Thanks Page product category override saved successfully..</div>
                          </div>
                      </div>
                    </form>
                </div>
            </div>

          <?php
        }else{
            echo $checkAuthenticationStatus;
          }?>
    </div>
  </div>
</div>
<!--THANKS SETUP END-->
<script type="text/javascript">
    //below 7 var is related to the custom fields groups
    var DEFAULT_WORDPRESS_POST = '<?php echo DEFAULT_WORDPRESS_POST ?>';
    var DEFAULT_WORDPRESS_PAGE = '<?php echo DEFAULT_WORDPRESS_PAGE ?>';
    var DEFAULT_WORDPRESS_CUSTOM_URL = '<?php echo DEFAULT_WORDPRESS_CUSTOM_URL ?>';
    var REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS = '<?php echo REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS ?>';
    var REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES = '<?php echo REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES ?>';
</script>
<!--Dynamic Thankspage SETUP END