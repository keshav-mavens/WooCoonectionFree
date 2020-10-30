<!-- Campaign Goals SETUP-->
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
                  <div class="loading_thanku_overrides" style="display: none;">
                      <img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.svg">
                  </div>
                  <ul class="main-override">
                  </ul>
                  <div class="text-center m-t-50">
                      <span class="create-new-override">
                          <span class="default_message_override">We don't have any Overrides</span>
                          <a href="javascript:void(0)" class="btn btn-theme third addnewoverride">
                              <span>Create Override</span>
                          </a>
                      </span>
                  </div>
              </div>
          <?php
        }else{
            echo $checkAuthenticationStatus;
          }?>
    </div>
  </div>
</div>