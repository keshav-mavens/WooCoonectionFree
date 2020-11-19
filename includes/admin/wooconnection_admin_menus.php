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

//check the application authentication status if authorized then show the campaign goals tab on plugin load else show the getting started tab....
$checkAuthenticationStatus = applicationAuthenticationStatus();
//define default tab is getting started tab......
$getStartedActiveClass = "active";
$getStartedSubmenuClass = "active-sub-menu";
$getStartedUlStatus = "display: block";

//define empty variable for automation tab.....
$automationActiveClass = "";
$automationSubmenuClass = "";
$automationUlStatus = "";

//if plugin is activated then set the classes for automation tab to show by dafult.....
if(empty($checkAuthenticationStatus)){
	$getStartedActiveClass = "";
	$getStartedSubmenuClass = "";
	$getStartedUlStatus = "";

	$automationActiveClass = "active";
	$automationSubmenuClass = "active-sub-menu";
	$automationUlStatus = "display: block";
}

?>
<div class="col-lg-3 col-md-4 col-sm-4 col-12 p-l-0 p-r-0">
	<div class="mobile-menu">
        <a href="javascript:void(0)" class="toggle-menus"><span><i class="fa fa-bars" aria-hidden="true"></i></span></a>  
    </div>
	<div class="main-menu-wc">
	    <ul class="navigation accordian-list">
	        <li class="expanded"><a href="javascript:void(0);" class="tablinks <?php echo $getStartedActiveClass; ?>" id="getting_started">
			<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/started.png" alt=""></span>
			<span class="menu-text">Getting Started</span>
			<div class="clr"></div>
			</a>
				<ul class="sub-menu getting_started" style="<?php echo $getStartedUlStatus; ?>">
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs <?php echo $getStartedSubmenuClass; ?>" id="guided_setup">Guided Setup</a></li>
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs" id="plugin_activation">Activation</a></li>
	                <li class="sub-menu-expand common_disable_class <?php echo $leftMenuClass; ?>"><a href="javascript:void(0);" class="nav-tabs" id="application_settings"><span class="configurationType" id="<?php echo $configurationType; ?>"><?php echo $applicationLabel; ?></span> Settings</a></li>
	            </ul>
	        </li>
			
	        <li class="expanded  common_disable_class <?php echo $leftMenuClass; ?>">
				<a href="javascript:void(0);" id="import_products">
					<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/export.png" alt=""></span>
					<span class="menu-text">Import and Match</span>
		        	<div class="clr"></div>
		        </a>
	        </li>
			
	        <li class="expanded common_disable_class  <?php echo $leftMenuClass; ?>">
				<a href="javascript:void(0);" id="automation" class="<?php echo $automationActiveClass; ?>">
					<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/auto.png" alt=""></span>
					<span class="menu-text">Automate</span>
			        <div class="clr"></div>
				</a>
	            <ul class="sub-menu automation" style="<?php echo $automationUlStatus; ?>">
	                <li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs automation_active <?php echo $automationSubmenuClass; ?> " id="campaign_goals">Campaign Goals</a></li>
	            </ul>
	        </li>

	        <li class="expanded common_disable_class <?php echo $leftMenuClass; ?>" class="last">
				<a class="last" href="javascript:void(0);" id="advanced_options">
					<span class="menu-icon"><img src="<?php echo WOOCONNECTION_PLUGIN_URL; ?>assets/images/integration.png" alt=""></span>
					<span class="menu-text">Advanced Options</span>
			        <div class="clr"></div>
				</a>
	            <ul class="sub-menu advanced_options">
	               	<li class="sub-menu-expand"><a href="javascript:void(0);" class="nav-tabs advanced_options_active" id="lead_sources">Lead Sources</a></li>
	            </ul>
	        </li>

		</ul>
	</div>
</div>
<script type="text/javascript">
	//Define variable for use in js code....
	var WOOCONNECTION_PLUGIN_URL = "<?php echo WOOCONNECTION_PLUGIN_URL ?>";
	var APPLICATION_TYPE_INFUSIONSOFT = "<?php echo APPLICATION_TYPE_INFUSIONSOFT ?>";
	var APPLICATION_TYPE_KEAP = "<?php echo APPLICATION_TYPE_KEAP ?>";
</script>