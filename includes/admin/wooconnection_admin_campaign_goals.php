<style>
.modal-dialog {
    margin: 5.75rem auto;
}
@media(max-width:768px) {
.table-overflow {
    width: 909px;
    overflow-x: auto;
}
}
</style>
<!-- Campaign Goals SETUP-->
<?php 
//check the application authentication status if authorized then give access to configure campaign goals....
$checkAuthenticationStatus = applicationAuthenticationStatus();
?>
<div class="info-header">
  <p>Configure Campaign Goals</p>
</div>
<div class="righttextInner">
<span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span> 
  <div class="row">
    <div class="col-md-12 ">
       <?php if(empty($checkAuthenticationStatus)){?>
       <p class="text-right"><a class="btn btn-primary btn-theme" data-toggle="collapse" href="#collapseCampaignGoals" role="button" aria-expanded="false" aria-controls="collapseCampaignGoals">How is Works <i class="fa fa-caret-down" id="icon_collapseCampaignGoals" aria-hidden="true"></i></a></p>
        <div class="collapse" id="collapseCampaignGoals">
          <div class="card card-body col-md-12 m-b-40">
            <p class="heading-text text-center m-t-30">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
                <iframe class="m-t-30" src="https://player.vimeo.com/video/60771693" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
          </div>
          </div>
          <p class="heading-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
          <h4>Woocommerce General Triggers</h4>
          <div class="table-responsive">
              <table class="table table-striped table-overflow">
                <thead>
                  <tr>
                    <th style="width:30%">Trigger Goal Name</th>
                    <th style="width:30%">Trigger Integration Name</th>
                    <th style="width:40%">Trigger Call Name</th>
                    <th style="width:40%">Action</th>
                  </tr>
                </thead>
                <tbody>
                    <?php echo getGeneralTriggers();?>
                </tbody>
              </table>
          </div>
          <h4>Woocommerce Logged In User Cart Triggers</h4>
          <div class="table-responsive">
              <table class="table table-striped table-overflow">
                <thead>
                  <tr>
                    <th style="width:30%">Trigger Goal Name</th>
                    <th style="width:30%">Trigger Integration Name</th>
                    <th style="width:40%">Trigger Call Name</th>
                    <th style="width:40%">Action</th>
                  </tr>
                </thead>
                <tbody>
                    <?php echo getCartTriggers();?>
                </tbody>
              </table>
          </div>
        <?php }else{
              echo $checkAuthenticationStatus;
        } ?>
      </div>
    </div>
</div>

<!--Popup to edit the trigger details...-->
<div id="editTriggerDetails" class="modal" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title trigger_goal_name"></h4>
        <button type="button" class="close" onclick="hideCustomModel('editTriggerDetails')">&times;</button>
      </div>
      <div class="modal-body">
        <form action="" method="post" id="trigger_details_form" onsubmit="return false">
          <input type="hidden" name="edittriggerid" id="edittriggerid" value="">
          <div class="form-group row">
            <label class="col-lg-3 col-md-3 col-sm-12 col-12 col-form-label">Trigger Integration Name</label>
            <div class="col-lg-9 col-md-9 col-sm-12 col-12">
              <input class="form-control" type="text" name="integrationname" id="integrationname" placeholder="Integration Name" value="" maxlength="20">
              <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book..</div>
            </div>
          </div>
          
          <div class="form-group row">
            <label class="col-lg-3 col-md-3 col-sm-12 col-12 col-form-label">Trigger Call Name</label>
            <div class="col-lg-9 col-md-9 col-sm-12 col-12">
              <input class="form-control" type="text" name="callname" id="callname" value=""  maxlength="40" placeholder="Call Name">
              <div class="note-bottom">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</div>
            </div>
          </div>
          
          <div class="form-group col-md-12 text-right m-t-60">
            <div class="buttonloading savingTriggerDetails" style="display: none;"><i class="fa fa-spinner fa-spin"></i>Saving....</div>
            <div class="alert-error-message trigger-details-error" style="display: none;"></div>
            <div class="alert-sucess-message trigger-details-success" style="display: none;">Trigger details updated successfully.</div>
            <input type="button" value="Save" class="btn btn-primary btn-radius btn-theme save_trigger_details" onclick="updateTriggerdetails()">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!--Campaign Goals SETUP END