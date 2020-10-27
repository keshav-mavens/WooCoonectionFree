<?php
/**
 * Plugin Name: Manage Wooconnection Authenticate User
 * Description: List users have authenticate the woocnnection plugin.
 * Version: 16
 * Author: Fullstackmarketing.co
*/
ob_start();
//Call the listing table file....
require_once("includes/authenticate-users-list-table.php");
class WooConnection_Authentication {
	
	//Create main constructor....
	function __construct(){
		global $wpdb;
        $this->tablename = $wpdb->prefix."plugin_authorization_details";
		//Call the hook admin_menu it’s best to create an admin menu in the dashboard
		add_action('admin_menu', array($this,'wooconnection_authentication_admin_menu'));
		// Add Javascript and CSS for admin screens
        add_action('admin_enqueue_scripts', array($this,'enqueue_admin_scripts'));
	}

	//Function Definition : wooconnection_authentication_admin_menu
	function wooconnection_authentication_admin_menu() {
		add_menu_page('Wc Authentications', 'Wc Authentications', 'manage_options', 'wooconnection-authentication-admin', array($this,'wooconnection_authentication_admin_settings'),'',27 );
	}
	
    //Function is used to call main function "displayAuthenticationUsersListing"....
	function wooconnection_authentication_admin_settings(){
		$this->displayAuthenticationUsersListing();
        return;
	}

	//Function Definition : displayAuthenticationUsersListing
	function displayAuthenticationUsersListing() {
		//Create object of listing class...
		$usersListTable = new authentication_List_TableReviews();
	    //Call the function to get the listing....
	    $usersListTable->prepare_listing();
	    $searchvalue = "";
	    if(!empty($_GET['searchdata'])){
	    	$searchvalue = $_GET['searchdata'];
	    }
    ?>
		<div class="wrap">
		    <div id="icon-users" class="icon32"><br/></div>
			<h2>Manage Wooconnection Authentication Users</h2>
			<div style="float:right;margin-top:-10px">
				<input type="text" name="searchdata" placeholder="Search user email address" id="datasearch" value="<?php echo $searchvalue; ?>">
				<input type="submit" name="submit" value="Search" class="textualbutton" onclick="searchUserEmail()">
			</div>
			<form id="movies-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $usersListTable->display() ?>
			</form>
		</div>
    <?php
	}


	//Function Definition : enqueue_admin_scripts
    public function enqueue_admin_scripts() {
    	if (isset($_GET['page'])) {
    		if($_GET['page'] == 'wooconnection-authentication-admin')
    		{
    			wp_register_script('wc_authentication_admin_js', ('/wp-content/plugins/manage-wc-authentication-users/assets/js/admin.js'),12, true);
    			wp_localize_script('wc_authentication_admin_js', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php')));
    			wp_enqueue_script('wc_authentication_admin_js');
    		}
    		?>
    			<style>
					.colspanchange{
						text-align: center;
					}
					.textualbutton {
					    display: inline-block;
					    text-decoration: none;
					    font-size: 13px;
					    line-height: 2.15384615;
					    min-height: 30px;
					    margin: 0;
					    padding: 0 10px;
					    cursor: pointer;
					    border-width: 1px;
					    border-style: solid;
					    -webkit-appearance: none;
					    border-radius: 3px;
					    white-space: nowrap;
					    box-sizing: border-box;
					    color: #0071a1;
					    border-color: #0071a1;
					    background: #f3f5f6;
					    vertical-align: top;s
					}
				</style>
    		<?php
		}
	}		
}
// Create global so you can use this variable beyond initial creation.
global $wcAuthentication;
// Create instance of our wooconnection admin class to use off the whole things.
$wcAuthentication = new WooConnection_Authentication();
?>
