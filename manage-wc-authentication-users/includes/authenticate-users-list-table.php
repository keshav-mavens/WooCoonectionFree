<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class authentication_List_TableReviews extends WP_List_Table {
    //set the table name..
    var $tablename = "plugin_authorization_details";
    
    //Create main constructor....
    function __construct(){
        global $status,$page;
        
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'slide',     //singular name of the listed records
            'plural'    => 'slides',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
 
    //Set column values on the basis of column name...
    function column_default($item, $column_name){
        switch($column_name){
            case 'id':
		    case 'SNo': {
				$current_page = $this->get_pagenum();
				$per_page = 15;
				$sno = $item[$column_name] + ($current_page-1)*$per_page;
				return $sno;
			}
            //For column expiration date get the value and covert into required format....
			case 'expirationDate' : {
				$createExpTime = date('F j, Y, g:i a',strtotime('+'.$item['user_access_token_expires_in'].' seconds',strtotime($item['token_updated_date'])));
                return $createExpTime;
            }
            //For email get "user_email"...
            case 'email': {
                return $item['user_email'];
            }
            //For userpluginkey get "user_plugin_key"...
            case 'userpluginkey': {
                return $item['user_plugin_key'];
            }
            //For userrequestwebsite get "user_request_website"...
			case 'userrequestwebsite': {
                return '<a href="'.$item['user_request_website'].'" target="_blank">'.$item['user_request_website'].'<a>';
            }
            //For userauthorizeapplication get "user_authorize_application"...
            case 'userauthorizeapplication': {
                return $item['user_authorize_application'];
            }
            //For userapplicationedition get "user_application_edition" and set the label on the basis of value.....
            case 'userapplicationedition': {
                $edition = $item['user_application_edition'];
                if($edition == 1){
                    $label = 'Infusionsoft';
                }else if($edition == 2){
                    $label = 'Keap';
                }
                return $label;
            }
            //For accesstoken get "user_access_token"...
            case 'accesstoken': {
                return $item['user_access_token'];
            }
            
            return $item[$column_name];
            default:
            return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    //Set columns label...
    function get_columns(){
        $columnsListing = array(
            'SNo'    =>    'S.No.',
            'email' => 'User Email Address',
            'userpluginkey' => 'Activation Key',
            'userrequestwebsite'=>'Application Authentication On',
            'userauthorizeapplication'=>'Authenticate Application Name',
            'userapplicationedition'=>'Authenticate Application Edition',
            'accesstoken'=>'Access Token',
            'expirationDate' => 'Token Expiration Date', 
        );
        return $columnsListing;
    }
    
   
    //Define the name of columns which is available with sorting.....
    function get_sortable_columns() {
        $sortable_columns_rule = array('expirationDate' => array('expirationDate',true));
        return $sortable_columns_rule;
    }
    
    //Function is mainly used to fetch the data and prepare the users listing...
    function prepare_listing() {
        global $wpdb;
        $per_page_items = 15;
        $columnsList = $this->get_columns();
        $hiddenArray = array();
        $sortableColumns = $this->get_sortable_columns();
        $this->_column_headers = array($columnsList, $hiddenArray, $sortableColumns);
        $current_page_number = $this->get_pagenum();
        $list_start = ($current_page_number - 1)*$per_page_items;         
		$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to title
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
        $searchRecord="";
        if(isset($_REQUEST['searchdata'])){
            $searchRecord=$_REQUEST['searchdata'];
        }
		if($searchRecord!=""){
		    if(!empty($_GET['searchdata'])){
                $searchRecord = $_GET['searchdata'];    
            }
            else{
                $searchRecord = "";
            }
            
			$query =  "SELECT *,@imageid  := @imageid  + 1 as SNo FROM " . $wpdb->prefix . $this->tablename ." ,(SELECT @imageid  := 0) as r where (user_email LIKE '%".$searchRecord."%') ORDER BY ".$orderby." ".$order." LIMIT ".$list_start.", ".$per_page_items;
            $data = $wpdb->get_results($query); 
			$data = $this->covertObjectToArray($data);
            $sql = "SELECT COUNT(*) FROM `".$wpdb->prefix . $this->tablename."` where user_email LIKE '".$searchRecord."%'";
			$total_records = $wpdb->get_var($sql);
			$this->items = $data;
		}
        else {
			$query = "SELECT *,@imageid  := @imageid  + 1 as SNo FROM`" . $wpdb->prefix . $this->tablename ."` ,(SELECT @imageid  := 0) as r ORDER BY ".$orderby." ".$order." LIMIT ".$list_start.", ".$per_page_items;
			$data = $wpdb->get_results($query);
			$data = $this->covertObjectToArray($data);
			$sql = "SELECT COUNT(*) FROM ". $wpdb->prefix . $this->tablename." ";
			$total_records = $wpdb->get_var($sql);
			$this->items = $data;
		}
		$this->set_pagination_args( array(
            'total_items' => $total_records,                 
            'per_page'    => $per_page_items,                     
            'total_pages' => ceil($total_records/$per_page_items)  
        ) );
    }
    
    //Function is used to covert object to array....
    function covertObjectToArray($data){
		if(is_array($data)) {
    		$data = json_encode($data);
    		$data = json_decode($data,true);
    		return $data;
		}else{
		  return $data;
		}
	}
}
?>
