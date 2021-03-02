<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class WooConnection_Admin {
    /**
     * @var WooConnection_Admin
     */
    public function __construct() {
        //Call the hook admin_menu it’s best to create an admin menu in the dashboard
        add_action( 'admin_menu', array($this, 'wooconnection_admin_menu' ));
        // Add Javascript and CSS for admin screens
        add_action('admin_enqueue_scripts', array($this,'enqueue_admin_scripts'));
        // Include php core files...
        add_action('init', array($this, 'wooconnection_include_files'));
    }

    //Function Definition : wooconnection_admin_menu
	public function wooconnection_admin_menu() {
		$icon_url = WOOCONNECTION_PLUGIN_URL .'assets/images/icon-grey-new.png';
		add_menu_page( 'WooConnection Pro', 'WooConnection Pro', 'manage_options', 'wooconnection-admin', array( $this, 'wooconnection_admin_settings' ), $icon_url, 27 );
	}

    //Admin Menu : Fuction is used to call main plugin file..
    public function wooconnection_admin_settings(){
       require_once( WOOCONNECTION_PLUGIN_DIR . 'includes/admin/wooconnection_admin.php' );
    }

    //Function Definition : enqueue_admin_scripts
    public function enqueue_admin_scripts() {
        if (isset($_GET['page'])) {
            if($_GET['page'] == 'wooconnection-admin'){
                wp_deregister_script( 'jquery' ); // deregisters the default WordPress jQuery  
                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('jquery', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.min.js'),WOOCONNECTION_VERSION, true);
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('jquery');
                
                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('jquery_validate_js', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery.validate.min.js'),WOOCONNECTION_VERSION, true);
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('jquery_validate_js');
                
                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('bootstrap_bundle_min_js', (WOOCONNECTION_PLUGIN_URL.'assets/js/bootstrap.bundle.min.js'),WOOCONNECTION_VERSION, true);
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('bootstrap_bundle_min_js');
                
                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('select_two_min_js', (WOOCONNECTION_PLUGIN_URL.'assets/js/select2.min.js'),WOOCONNECTION_VERSION, true);
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('select_two_min_js');
                
                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('jquery_ui_js', (WOOCONNECTION_PLUGIN_URL.'assets/js/jquery-ui-1.10.1.min.js'),WOOCONNECTION_VERSION, true);
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('jquery_ui_js');

                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('wooconnection_admin_js', (WOOCONNECTION_PLUGIN_URL.'assets/js/admin.js'),WOOCONNECTION_VERSION, true);
                wp_localize_script('wooconnection_admin_js', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php')));
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('wooconnection_admin_js');
                wp_localize_script( 'ajax-script', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ));

                //Wooconnection Scripts : Resgister the wooconnection scripts..
                wp_register_script('jquery_sweetalert_js', (WOOCONNECTION_PLUGIN_URL.'assets/js/sweetalert.min.js'),WOOCONNECTION_VERSION, true);
                //Wooconnection Scripts : Enqueue the wooconnection scripts..
                wp_enqueue_script('jquery_sweetalert_js');

                //Wooconnection Styles : Resgister the wooconnection styles..
                wp_register_style( 'wooconnection_admin_style', WOOCONNECTION_PLUGIN_URL.'assets/css/admin.css', array(), WOOCONNECTION_VERSION );
                //Wooconnection Styles : Enqueue the wooconnection styles..
                wp_enqueue_style('wooconnection_admin_style');
                wp_enqueue_style('bootstrap_min_css', WOOCONNECTION_PLUGIN_URL.'assets/css/bootstrap.min.css', array(), WOOCONNECTION_VERSION);//Wooconnection Styles : Enqueue the wooconnection styles..
                wp_enqueue_style('fontawesome_min_css', WOOCONNECTION_PLUGIN_URL.'assets/css/font-awesome.min.css', array(), WOOCONNECTION_VERSION);//Wooconnection Styles : Enqueue the wooconnection styles..
                wp_enqueue_style('select_two_min_css', WOOCONNECTION_PLUGIN_URL.'assets/css/select2.min.css', array(), WOOCONNECTION_VERSION);//Wooconnection Styles : Enqueue the wooconnection styles..   
                wp_enqueue_style('sweetalert_min_css', WOOCONNECTION_PLUGIN_URL.'assets/css/sweetalert.min.css', array(), WOOCONNECTION_VERSION);//Wooconnection Styles : Enqueue the wooconnection styles..

            }else{
                //include custom css and js for another pages of wp-admin.....
                $this->includeCssJs();     
            }
        }else{
           //include custom css and js for another plugins pages of wp-admin.....
           $this->includeCssJs();
        }
    }
    
    //Function Definition : wooconnection_include_files
    public function wooconnection_include_files() {
        require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/core/wooconnection-common-functions.php');
        require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/admin/admin_ajax.php');
        if(is_admin() && !wp_doing_ajax()){//check the screen of wp-admin is exist then include the below mention files..
            require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/admin/modules/wc_admin_hooks.php');
            require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/admin/modules/wc_admin_update_plugin.php');
            require_once(WOOCONNECTION_PLUGIN_DIR . 'includes/admin/modules/wc_admin_referral_partner.php');
        }
    }

    //Function Definition : includeCssJs
    public function includeCssJs(){
        ?>
            <style type="text/css">
                .toplevel_page_wooconnection-admin .wp-menu-image img {
                    padding: 3px 0 0 0 !important;
                }
                .custom-meta-box{
                    word-break: break-all;
                }
            </style>
            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
            <script type="text/javascript">
                var WOOCONNECTION_PLUGIN_URL = '<?php echo WOOCONNECTION_PLUGIN_URL ?>';
                $( document ).ready(function() {
                    //change the wooconnection plugin image on hover....
                    $('.toplevel_page_wooconnection-admin').hover(function () {
                        $(this).find('img').attr('src', function (i, src) {
                            if(src == WOOCONNECTION_PLUGIN_URL+'assets/images/icon-grey-new.png'){
                                return src.replace('icon-grey-new.png', 'icon-blue.png') 
                            }
                        });
                    },  function () {
                        $(this).find('img').attr('src', function (i, src) {
                            if(src == WOOCONNECTION_PLUGIN_URL+'assets/images/icon-blue.png'){
                                return src.replace('icon-blue.png', 'icon-grey-new.png')
                            }
                        });
                    });
                });
                //This function is used to perform the copy clipboard content action........
                function copyContent(elementid) {
                  var elementDetails = document.getElementById(elementid);
                  if(window.getSelection) {
                    var selectWindow = window.getSelection();
                    var eleTextRange = document.createRange();
                    eleTextRange.selectNodeContents(elementDetails);
                    selectWindow.removeAllRanges();
                    selectWindow.addRange(eleTextRange);
                    document.execCommand("Copy");
                    var executeCommand = document.execCommand('copy',true);
                  }else if(document.body.createTextRange) {
                    var eleTextRange = document.body.createTextRange();
                    eleTextRange.moveToElementText(elementDetails);
                    eleTextRange.select();
                    var executeCommand = document.execCommand('copy',true);
                  }
                }
            </script>
        <?php
    }

    //Function Definition : twiceday_update_application_products(Cron job run twice a day)
    public function twiceday_update_application_products(){
        //get the authentication details of plugin....
        $pluginAuthenticationDetails = getAuthenticationDetails();

        //get the access token....
        $application_access_token = '';
        
        if(!empty($pluginAuthenticationDetails[0]->user_access_token)){
            $application_access_token = $pluginAuthenticationDetails[0]->user_access_token;
        }
        
        if(isset($application_access_token) && !empty($application_access_token)){
            global $wpdb,$table_prefix;
            $applicationProductsTableName = $table_prefix.'authorize_application_products';
            $appLatestProductsListing = getApplicationProducts();
            $existingDbProducts = getExistingAppProducts();
            if(isset($appLatestProductsListing) && !empty($appLatestProductsListing)){
                foreach ($appLatestProductsListing['products'] as $key => $value) {
                    if(!empty($value['id'])){
                        $cronProductDataArray = array();
                        $cronProductDataArray['app_product_id'] = $value['id'];
                        $cronProductDataArray['app_product_name'] =  $value['product_name'];
                        $cronProductDataArray['app_product_description'] = strip_tags($value['product_desc']);   
                        $cronProductDataArray['app_product_excerpt'] = $value['product_short_desc'];
                        $cronProductDataArray['app_product_sku'] = $value['sku'];
                        $cronProductDataArray['app_product_price'] = $value['product_price'];
                        $cronProductDataArray['app_product_subscription'] = $value['subscription_only'];
                        if(isset($existingDbProducts) && !empty($existingDbProducts)){
                            $key = array_search($value['id'], array_column($existingDbProducts, 'app_product_id'));
                            if (!empty($key) || $key === 0) {
                                $existingProductId =  $existingDbProducts[$key]->id;
                                if(!empty($existingProductId)){
                                    $response = $wpdb->update($applicationProductsTableName, $cronProductDataArray, array('id'=>$existingProductId));
                                }
                            }else{
                                $wpdb->insert($applicationProductsTableName,$cronProductDataArray);
                            }
                        }else{
                            $wpdb->insert($applicationProductsTableName,$cronProductDataArray);
                        }
                    }
                }
            }
        }
    }

}
// Create global so you can use this variable beyond initial creation.
global $wooconnectionadmin;

// Create instance of our wooconnection admin class to use off the whole things.
$wooconnectionadmin = new WooConnection_Admin();
?>