<style>
.righttextInner{overflow-x:hidden;}
</style>
<!--GUIDED SETUP-->
<?php
  //code is used to check import/export status...
  $checkImportExportStatus = checkImportExportStatus();
  $importExportClass = "";
  $importExportHtml = "2";
  
  //code is used to check plugin activation status...
  $checkActivationstatus = checkPluginActivationStatus();
  $activationClass = "";
  $activationHtml = "1";
  if($checkActivationstatus == true){
    $activationClass = "active";
    $activationHtml = '<i class="fa fa-check" aria-hidden="true"></i>';
    if($checkImportExportStatus == true){
      $importExportClass = "active";
      $importExportHtml = '<i class="fa fa-check" aria-hidden="true"></i>';
    }
  }

  //Code is used check plugin is activated or not.If not activated then stop the click event on progress bar..
  $leftMenuClass = checkPluginActivatedNot();
  //Define variables.....
  $activeConnectId = "";
  $importExportId = "";
  $automationId = "";
  //If plugin is activated then set the id attribute for progress bar steps, so onclick work sucessfully.
  if($leftMenuClass == ""){
    $activeConnectId = "progress_plugin_activation";
    $importExportId = "progress_import_products";
    $automationId = "progress_automation";
  }
?>
<div class="info-header">
  <p>Guided Setup</p>
</div>
<div class="righttextInner"> 
  <div class="tabbable">
    <ul class="nav nav-tabs wizard">
      <li class="<?php echo $activationClass; ?>" id="<?php echo $activeConnectId; ?>">
        <a href="javascript:void(0)" data-toggle="tab" aria-expanded="false"><span class="nmbr"><?php echo $activationHtml; ?></span>Activate and Connect</a>
      </li>
      <li id="<?php echo $importExportId; ?>" class="<?php echo $importExportClass; ?>">
        <a href="javascript:void(0)" data-toggle="tab" aria-expanded="false"><span class="nmbr"><?php echo $importExportHtml; ?></span>Import and Match</a>
      </li>
      <li  id="<?php echo $automationId; ?>">
        <a href="javascript:void(0)" data-toggle="tab" aria-expanded="false"><span class="nmbr">3</span>Automate</a>
      </li>
    </ul>
  </div>
  <span class="ajax_loader" style="display: none;"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>/assets/images/loader.gif"></span>
	<p class="heading-text text-center m-t-30">Lorem Ipsum is simply dummy text of the printing and typesetting industry.Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
	<iframe class="m-t-30" src="https://player.vimeo.com/video/60771693" width="100%" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>

</div>
<!--GUIDED SETUP END-->
