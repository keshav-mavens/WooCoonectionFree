<?php 
//Check plugin is activated or not, if not activated then add disable class to some specific menus....
$leftMenuClass = checkPluginActivatedNot();
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
<div class="col-lg-3 col-md-4 col-sm-4 col-12 p-l-0 p-r-0">
	<div class="mobile-menu">
        <a href="javascript:void(0)" class="toggle-menus"><span><i class="fa fa-bars" aria-hidden="true"></i></span></a>  
    </div>
	<div class="main-menu-wc">
	    <ul class="navigation accordian-list">
	        <li class="expanded"><a href="javascript:void(0);" class="tablinks active" id="getting_started">
			<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/started.png" alt=""></span>
			<span class="menu-text">Getting Started</span>
			<div class="clr"></div>
			</a>
				<ul class="sub-menu" style="display: block;">
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs active-sub-menu" id="guided_setup">Guided Setup</a></li>
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs" id="plugin_activation">Activation</a></li>
	                <li class="sub-menu-expand <?php echo $leftMenuClass; ?>"><a href="javascript:void(0);" class="nav-tabs" id="application_settings"><span class="applicationtype" id="<?php echo $configurationType; ?>"><?php echo $applicationLabel; ?></span> Settings</a></li>
	            </ul>
	        </li>
			
	        <li class="expanded  <?php echo $leftMenuClass; ?>">
				<a href="javascript:void(0);" id="import_products">
					<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/export.png" alt=""></span>
					<span class="menu-text"><?php echo IMPORT_EXPORT_LABEL_FREE; ?></span>
		        	<div class="clr"></div>
		        </a>
	        </li>
			
	        <li class="expanded  <?php echo $leftMenuClass; ?>">
				<a href="javascript:void(0);" id="automation">
					<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/auto.png" alt=""></span>
					<span class="menu-text">Campaign Automation</span>
			        <div class="clr"></div>
				</a>
	            <ul class="sub-menu">
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs" id="campaign_goals">Campaign Goals</a></li>
	            </ul>
	        </li>

	        <li class="expanded <?php echo $leftMenuClass; ?>">
				<a class="last" href="javascript:void(0);" id="advanced_options">
					<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/integration.png" alt=""></span>
					<span class="menu-text">Advanced Options</span>
			        <div class="clr"></div>
				</a>
	            <ul class="sub-menu">
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs" id="dynamic_thank_you_page">Dynamic Thank You Pages</a></li>
	            </ul>
	        </li>
		</ul>
	</div>
</div>
<script type="text/javascript">
	//Define variable for use in js code....
	var WOOCONNECTION_PLUGIN_URL = '<?php echo WOOCONNECTION_PLUGIN_URL ?>';
</script>