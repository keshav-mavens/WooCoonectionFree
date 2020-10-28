<?php
/**
 * Plugin Name: WooConnection Pro
 * Description: Automatically sync your WooCommerce orders with your Infusionsoft or Keap account.
 * Version: 16
 * Author: Fullstackmarketing.co
 * Author URI: http://www.informationstreet.com
 * Plugin URI: https://www.fullstackmarketing.co
 */
class WooConnectionPro {

  	public function __construct() {
		//Call the hook plugin_loaded at the time of plugin initialization..
		add_action("plugins_loaded", array($this, "wooconnection_plugin_initialization"));
        //Call the hook register_activation_hook at the time of plugin activation and create the table in database for campaign goals management..
        register_activation_hook( __FILE__, array($this, 'create_campaign_goals_database_table' ) );
        //Call the hook register_activation_hook to insert records in table..
        register_activation_hook( __FILE__, array($this, 'insert_campaign_goals_database_table' ) );
        register_activation_hook( __FILE__, array($this, 'create_countries_database_table' ) );
        register_activation_hook( __FILE__, array($this, 'insert_countries_database_table' ) );
        //register_activation_hook( __FILE__, array($this, 'insert_pro_campaign_goals' ) );
    }

    
	//Function Definition : wooconnection_plugin_initialization
    public function wooconnection_plugin_initialization(){
    	if (!class_exists('WC_Integration')) {
			add_action('admin_notices', array($this, 'woocommerce_plugin_necessary'));
			return;
		}
        //check if wooconnection free version plugin is already activated then user needs to deactivate or delete the free verison of wooconnection to use the pro wooconnection version.....
        if (class_exists('WooConnection')) {
            add_action('admin_notices', array($this, 'wc_pro_deactivate_free_version_notice'));
            return;
        }
        define( 'WOOCONNECTION_VERSION', '16' );//Version Entity
        define( 'WOOCONNECTION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );//Directory Path Entity
        define( 'WOOCONNECTION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );//Directory Url Entity
        require_once( WOOCONNECTION_PLUGIN_DIR . 'includes/core/wooconnection-entities.php' );
		require_once( WOOCONNECTION_PLUGIN_DIR . 'includes/classes/class.wooconnection-admin.php' );
        require_once( WOOCONNECTION_PLUGIN_DIR . 'includes/classes/class.wooconnection-front.php' );
    }

    //Function Definition : woocommerce_plugin_necessary
    public function woocommerce_plugin_necessary(){
    	$class = 'notice notice-error';
		$message = __( 'WooConnection plugin requires the WooCommerce plugin to be installed and active.', 'error-text-plugin' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }

    //Function Definition : wc_pro_deactivate_free_version_notice function is used to show the notice when user try to activate the pro version of wooconnection...
    public function wc_pro_deactivate_free_version_notice() {
       ?>
       <div class="notice notice-error is-dismissible">
          <p><?php echo sprintf( __( 'You need to deactivate and delete the free version of WooConnection plugin on the %splugins page%s', 'deactivate-wooconnection-2' ), '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=wooconnection/wooconnection.php&amp;plugin_status=all&amp;paged=1&amp;s=', 'deactivate-plugin_wooconnection/wooconnection.php' ) . '">', '</a>' ); ?></p>
       </div>
       <?php
    }
    
    //Function Definition : create_campaign_goals_database_table
    public function create_campaign_goals_database_table()
    {
        global $table_prefix, $wpdb;

        $table_name = 'wooconnection_campaign_goals';
        $wp_table_name = $table_prefix . "$table_name";
        //Check Table : First need to check whether the table is exist or not if not exist then create new table with name $wp_table_name..
        if($wpdb->get_var( "show tables like '$wp_table_name'" ) != $wp_table_name) 
        {
            $sql = "CREATE TABLE `". $wp_table_name . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `wc_goal_name`  varchar(255)   NOT NULL, ";
            $sql .= "  `wc_integration_name`  varchar(255)   NOT NULL, ";
            $sql .= "  `wc_call_name`  varchar(255)   NOT NULL, ";
            $sql .= "  `wc_trigger_type`  tinyint(4) DEFAULT 1 COMMENT '1-trigger_type_general,2-trigger_type_cart,3-trigger_type_order', ";
            $sql .= "  `wc_trigger_verison`  tinyint(4) DEFAULT 1 COMMENT '1-trigger_version_free,2-trigger_version_pro', ";
            $sql .= "  `created`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
            $sql .= "  `modified`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
    }


    //Function Definition : insert_campaign_goals_database_table
    public function insert_campaign_goals_database_table()
    {
        global $table_prefix, $wpdb;
        $table_name = 'wooconnection_campaign_goals';
        $wp_table_name = $table_prefix . "$table_name";
        //Check Table Records : First need to check whether the table records is exist or not if not exist then create new table records..
        $checkTableRecords = $wpdb->get_results('SELECT * FROM '.$wp_table_name.' WHERE wc_trigger_verison=1');
        if(empty($checkTableRecords)){
            $wpdb->query("INSERT INTO ".$wp_table_name."
                (`wc_goal_name`,`wc_integration_name`,`wc_call_name`,`wc_trigger_type`,`wc_trigger_verison`)
                VALUES
                ('New User Registration','wooconnection','registered',1,1),
                ('Order Successful','wooconnection','successfulorder',1,1),
                ('Order Failed','wooconnection','failedorder',1,1)");
        }

        $checkTableRecordsPro = $wpdb->get_results('SELECT * FROM '.$wp_table_name.'  WHERE wc_trigger_verison=2');
        if(empty($checkTableRecordsPro)){
            $wpdb->query("INSERT INTO ".$wp_table_name."
                (`wc_goal_name`,`wc_integration_name`,`wc_call_name`,`wc_trigger_type`,`wc_trigger_verison`)
                VALUES
                ('Card Declined','wooconnection','declinedcard',1,2),
                ('Checkout Page View','wooconnectionuser','checkoutpage',2,2),
                ('Cart Emptied','wooconnectionuser','emptiedcart',2,2),
                ('Item Added to Cart','wooconnectionuser','added{Product SKU}',2,2),
                ('Review Left','wooconnectionuser','review{Product SKU}',2,2),
                ('Any Purchase','wooconnectionorder','success',3,2),
                ('Specific Product','wooconnectionorder','{Product SKU}',3,2),
                ('Coupon Code Applied','wooconnectionorder','coupon{Coupon Code}',3,2)");
        }
    }

    //Function Definition : create_countries_database_table
    public function create_countries_database_table(){
        global $table_prefix, $wpdb;
        $table_name = 'wooconnection_countries';
        $wp_table_name = $table_prefix . "$table_name";
        if($wpdb->get_var( "show tables like '$wp_table_name'" ) != $wp_table_name) 
        {
            $sql = "CREATE TABLE `". $wp_table_name . "` ( ";
            $sql .= "  `id`  int(11)   NOT NULL auto_increment, ";
            $sql .= "  `countrycode` char(3) NOT NULL, ";
            $sql .= "  `countryname` varchar(200) NOT NULL, ";
            $sql .= "  `code` char(2) DEFAULT NULL, ";
            $sql .= "  PRIMARY KEY (`id`) ";
            $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
            require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
    }

    //Function Definition : insert_countries_database_table
    public function insert_countries_database_table(){
        global $table_prefix, $wpdb;
        $table_name = 'wooconnection_countries';
        $wp_table_name = $table_prefix . "$table_name";
        //Check Table Records : First need to check whether the table records is exist or not if not exist then create new table records..
        $checkTableRecords = $wpdb->get_results('SELECT * FROM '.$wp_table_name.'');
        if(empty($checkTableRecords)){
            $wpdb->query("INSERT INTO ".$wp_table_name."
                (`countrycode`,`countryname`,`code`)
                VALUES
                ('AFG','Afghanistan','AF'), ('ALA','Åland','AX'), ('ALB','Albania','AL'), ('DZA','Algeria','DZ'), ('ASM','American Samoa','AS'), ('AND','Andorra','AD'), ('AGO','Angola','AO'), ('AIA','Anguilla','AI'), ('ATA','Antarctica','AQ'), ('ATG','Antigua and Barbuda','AG'), ('ARG','Argentina','AR'), ('ARM','Armenia','AM'), ('ABW','Aruba','AW'), ('AUS','Australia','AU'), ('AUT','Austria','AT'), ('AZE','Azerbaijan','AZ'), ('BHS','Bahamas','BS'), ('BHR','Bahrain','BH'), ('BGD','Bangladesh','BD'), ('BRB','Barbados','BB'), ('BLR','Belarus','BY'), ('BEL','Belgium','BE'), ('BLZ','Belize','BZ'), ('BEN','Benin','BJ'), ('BMU','Bermuda','BM'), ('BTN','Bhutan','BT'), ('BOL','Bolivia','BO'), ('BES','Bonaire','BQ'), ('BIH','Bosnia and Herzegovina','BA'), ('BWA','Botswana','BW'), ('BVT','Bouvet Island','BV'), ('BRA','Brazil','BR'), ('IOT','British Indian Ocean Territory','IO'), ('VGB','British Virgin Islands','VG'), ('BRN','Brunei','BN'), ('BGR','Bulgaria','BG'), ('BFA','Burkina Faso','BF'), ('BDI','Burundi','BI'), ('KHM','Cambodia','KH'), ('CMR','Cameroon','CM'), ('CAN','Canada','CA'), ('CPV','Cape Verde','CV'), ('CYM','Cayman Islands','KY'), ('CAF','Central African Republic','CF'), ('TCD','Chad','TD'), ('CHL','Chile','CL'), ('CHN','China','CN'), ('CXR','Christmas Island','CX'), ('CCK','Cocos [Keeling] Islands','CC'), ('COL','Colombia','CO'), ('COM','Comoros','KM'), ('COK','Cook Islands','CK'), ('CRI','Costa Rica','CR'), ('HRV','Croatia','HR'), ('CUB','Cuba','CU'), ('CUW','Curacao','CW'), ('CYP','Cyprus','CY'), ('CZE','Czech Republic','CZ'), ('COD','Democratic Republic of the Congo','CD'), ('DNK','Denmark','DK'), ('DJI','Djibouti','DJ'), ('DMA','Dominica','DM'), ('DOM','Dominican Republic','DO'), ('TLS','East Timor','TL'), ('ECU','Ecuador','EC'), ('EGY','Egypt','EG'), ('SLV','El Salvador','SV'), ('GNQ','Equatorial Guinea','GQ'), ('ERI','Eritrea','ER'), ('EST','Estonia','EE'), ('ETH','Ethiopia','ET'), ('FLK','Falkland Islands','FK'), ('FRO','Faroe Islands','FO'), ('FJI','Fiji','FJ'), ('FIN','Finland','FI'), ('FRA','France','FR'), ('GUF','French Guiana','GF'), ('PYF','French Polynesia','PF'), ('ATF','French Southern Territories','TF'), ('GAB','Gabon','GA'), ('GMB','Gambia','GM'), ('GEO','Georgia','GE'), ('DEU','Germany','DE'), ('GHA','Ghana','GH'), ('GIB','Gibraltar','GI'), ('GRC','Greece','GR'), ('GRL','Greenland','GL'), ('GRD','Grenada','GD'), ('GLP','Guadeloupe','GP'), ('GUM','Guam','GU'), ('GTM','Guatemala','GT'), ('GGY','Guernsey','GG'), ('GIN','Guinea','GN'), ('GNB','Guinea-Bissau','GW'), ('GUY','Guyana','GY'), ('HTI','Haiti','HT'), ('HMD','Heard Island and McDonald Islands','HM'), ('HND','Honduras','HN'), ('HKG','Hong Kong','HK'), ('HUN','Hungary','HU'), ('ISL','Iceland','IS'), ('IND','India','IN'), ('IDN','Indonesia','ID'), ('IRN','Iran','IR'), ('IRQ','Iraq','IQ'), ('IRL','Ireland','IE'), ('IMN','Isle of Man','IM'), ('ISR','Israel','IL'), ('ITA','Italy','IT'), ('CIV','Ivory Coast','CI'), ('JAM','Jamaica','JM'), ('JPN','Japan','JP'), ('JEY','Jersey','JE'), ('JOR','Jordan','JO'), ('KAZ','Kazakhstan','KZ'), ('KEN','Kenya','KE'), ('KIR','Kiribati','KI'), ('XKX','Kosovo','XK'), ('KWT','Kuwait','KW'), ('KGZ','Kyrgyzstan','KG'), ('LAO','Laos','LA'), ('LVA','Latvia','LV'), ('LBN','Lebanon','LB'), ('LSO','Lesotho','LS'), ('LBR','Liberia','LR'), ('LBY','Libya','LY'), ('LIE','Liechtenstein','LI'), ('LTU','Lithuania','LT'), ('LUX','Luxembourg','LU'), ('MAC','Macao','MO'), ('MKD','Macedonia','MK'), ('MDG','Madagascar','MG'), ('MWI','Malawi','MW'), ('MYS','Malaysia','MY'), ('MDV','Maldives','MV'), ('MLI','Mali','ML'), ('MLT','Malta','MT'), ('MHL','Marshall Islands','MH'), ('MTQ','Martinique','MQ'), ('MRT','Mauritania','MR'), ('MUS','Mauritius','MU'), ('MYT','Mayotte','YT'), ('MEX','Mexico','MX'), ('FSM','Micronesia','FM'), ('MDA','Moldova','MD'), ('MCO','Monaco','MC'), ('MNG','Mongolia','MN'), ('MNE','Montenegro','ME'), ('MSR','Montserrat','MS'), ('MAR','Morocco','MA'), ('MOZ','Mozambique','MZ'), ('MMR','Myanmar [Burma]','MM'), ('NAM','Namibia','NA'), ('NRU','Nauru','NR'), ('NPL','Nepal','NP'), ('NLD','Netherlands','NL'), ('NCL','New Caledonia','NC'), ('NZL','New Zealand','NZ'), ('NIC','Nicaragua','NI'), ('NER','Niger','NE'), ('NGA','Nigeria','NG'), ('NIU','Niue','NU'), ('NFK','Norfolk Island','NF'), ('PRK','North Korea','KP'), ('MNP','Northern Mariana Islands','MP'), ('NOR','Norway','NO'), ('OMN','Oman','OM'), ('PAK','Pakistan','PK'), ('PLW','Palau','PW'), ('PSE','Palestine','PS'), ('PAN','Panama','PA'), ('PNG','Papua New Guinea','PG'), ('PRY','Paraguay','PY'), ('PER','Peru','PE'), ('PHL','Philippines','PH'), ('PCN','Pitcairn Islands','PN'), ('POL','Poland','PL'), ('PRT','Portugal','PT'), ('PRI','Puerto Rico','PR'), ('QAT','Qatar','QA'), ('COG','Republic of the Congo','CG'), ('REU','Réunion','RE'), ('ROU','Romania','RO'), ('RUS','Russia','RU'), ('RWA','Rwanda','RW'), ('BLM','Saint Barthélemy','BL'), ('SHN','Saint Helena','SH'), ('KNA','Saint Kitts and Nevis','KN'), ('LCA','Saint Lucia','LC'), ('MAF','Saint Martin','MF'), ('SPM','Saint Pierre and Miquelon','PM'), ('VCT','Saint Vincent and the Grenadines','VC'), ('WSM','Samoa','WS'), ('SMR','San Marino','SM'), ('STP','São Tomé and Príncipe','ST'), ('SAU','Saudi Arabia','SA'), ('SEN','Senegal','SN'), ('SRB','Serbia','RS'), ('SYC','Seychelles','SC'), ('SLE','Sierra Leone','SL'), ('SGP','Singapore','SG'), ('SXM','Sint Maarten','SX'), ('SVK','Slovakia','SK'), ('SVN','Slovenia','SI'), ('SLB','Solomon Islands','SB'), ('SOM','Somalia','SO'), ('ZAF','South Africa','ZA'), ('SGS','South Georgia and the South Sandwich Islands','GS'), ('KOR','South Korea','KR'), ('SSD','South Sudan','SS'), ('ESP','Spain','ES'), ('LKA','Sri Lanka','LK'), ('SDN','Sudan','SD'), ('SUR','Suriname','SR'), ('SJM','Svalbard and Jan Mayen','SJ'), ('SWZ','Swaziland','SZ'), ('SWE','Sweden','SE'), ('CHE','Switzerland','CH'), ('SYR','Syria','SY'), ('TWN','Taiwan','TW'), ('TJK','Tajikistan','TJ'), ('TZA','Tanzania','TZ'), ('THA','Thailand','TH'), ('TGO','Togo','TG'), ('TKL','Tokelau','TK'), ('TON','Tonga','TO'), ('TTO','Trinidad and Tobago','TT'), ('TUN','Tunisia','TN'), ('TUR','Turkey','TR'), ('TKM','Turkmenistan','TM'), ('TCA','Turks and Caicos Islands','TC'), ('TUV','Tuvalu','TV'), ('UMI','U.S. Minor Outlying Islands','UM'), ('VIR','U.S. Virgin Islands','VI'), ('UGA','Uganda','UG'), ('UKR','Ukraine','UA'), ('ARE','United Arab Emirates','AE'), ('GBR','United Kingdom','GB'), ('USA','United States','US'), ('URY','Uruguay','UY'), ('UZB','Uzbekistan','UZ'), ('VUT','Vanuatu','VU'), ('VAT','Vatican City','VA'), ('VEN','Venezuela','VE'), ('VNM','Vietnam','VN'), ('WLF','Wallis and Futuna','WF'), ('ESH','Western Sahara','EH'), ('YEM','Yemen','YE'), ('ZMB','Zambia','ZM'), ('ZWE','Zimbabwe','ZW')");
        } 
    }
}
// Create global so you can use this variable beyond initial creation.
global $wooconnection;

// Create instance of our wooconnection class to use off the whole things.
$wooconnection = new WooConnectionPro();
?>