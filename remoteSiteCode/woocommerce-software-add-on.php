<?php
/*
 * Plugin Name: WooCommerce Software Add-On
 * Plugin URI: https://woocommerce.com/products/software-add-on/
 * Description: Extends WooCommerce to a full-blown software shop, including license activation, license retrieval, activation e-mails and more.
 * Version: 1.7.11
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * WC tested up to: 3.9
 * WC requires at least: 2.6
 *
 * Copyright: © 2020 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Partly based on the software addon by Joachim Kudish.
 *
 * Woo: 18683:79f6dbfe1f1d3a56a86f0509b6d6b04b
 */

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '79f6dbfe1f1d3a56a86f0509b6d6b04b', '18683' );

if ( is_woocommerce_active() && ! class_exists( 'WC_Software' ) ) {

	define( 'WC_SOFTWARE_VERSION', '1.7.11' );

	/**
	 * WC_Software class.
	 */
	class WC_Software {
		public $api_url;
		public $messages = array();

		/**
		 * Class constructor
		 * Plugin activation, hooks & filters, etc..
		 *
		 * @since 1.0
		 * @return void
		 */
		public function __construct() {
			global $wpdb;

			// Table names
			$wpdb->wc_software_licenses        = $wpdb->prefix . 'woocommerce_software_licenses';
			$wpdb->wc_software_activations     = $wpdb->prefix . 'woocommerce_software_activations';
			$wpdb->wc_software_licenses_legacy = $wpdb->prefix . 'woocommerce_software_licences';

			// API
			$this->api_url = add_query_arg( 'wc-api', 'software-api', home_url( '/' ) );

			// API hook
			add_action( 'woocommerce_api_software-api', array( $this, 'handle_api_request' ) );

			// Hooks
			add_action( 'admin_init', array( $this, 'check_version' ), 5 );
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'order_complete' ) );
			add_action( 'woocommerce_email_after_order_table', array( $this, 'email_keys' ) );
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'upgrade_form' ) );

			// Note used codes
			add_action( 'woocommerce_order_status_completed', array( $this, 'save_update_key' ) );
			add_action( 'woocommerce_order_status_processing', array( $this, 'save_update_key' ) );
			add_action( 'woocommerce_order_status_on-hold', array( $this, 'save_update_key' ) );

			// Filters for cart actions
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 1 );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 10, 3 );

			// AJAX
			add_action( 'wp_ajax_woocommerce_lost_license', array( $this, 'lost_license_ajax' ) );
			add_action( 'wp_ajax_nopriv_woocommerce_lost_license', array( $this, 'lost_license_ajax' ) );

			// Shortcodes
			add_shortcode( 'woocommerce_software_lost_license', array( $this, 'lost_license_page' ) );

		        // Add menu
		        add_action( 'admin_menu', array( $this, 'menu' ));

		        // Register CSV importer.
		        add_action( 'admin_init', array( $this, 'register_csv_importer' ) );

			// Initialize order meta. Need to be called after plugins_loaded because of WC_VERSION check.
			add_action( 'plugins_loaded', array( $this, 'init_order_meta' ) );

			// Include requires functions and classes. Need to be called after plugins_loaded because of WC_VERSION check.
			add_action( 'plugins_loaded', array( $this, 'includes' ) );
		}

		/**
		 * Actions for order item meta.
		 *
		 * @since 1.7.2
		 * @return void
		 */
		public function init_order_meta() {
			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				add_action( 'woocommerce_add_order_item_meta', array( $this, 'order_item_meta_bwc' ), 10, 3 );
			} else {
				add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_item_meta' ), 10, 3 );
			}
		}

	    /**
	     * Localisation
	     */
	    public function load_plugin_textdomain() {
	    	load_plugin_textdomain( 'woocommerce-software-add-on', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	    }

		/**
		 * Handle updates
		 */
		public function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'woocommerce_software_version' ) !== WC_SOFTWARE_VERSION ) {
				$this->activation();
			}
		}

		/**
		 * Add menu page
		 */
	    public function menu() {
	    	add_submenu_page( 'woocommerce', __( 'License Keys', 'woocommerce-software-add-on' ),  __( 'License Keys', 'woocommerce-software-add-on' ), 'manage_woocommerce', 'wc_software_keys', array( $this, 'license_key_page' ) );
	    }

		/**
		 * Add page to show license keys
		 */
		public function license_key_page() {
		    $WC_Software_Key_Admin = new WC_Software_Key_Admin();
		    $WC_Software_Key_Admin->prepare_items();
		    ?>
		    <div class="wrap">

		        <div id="icon-woocommerce" class="icon32 icon32-posts-product"><br/></div>
		        <h2><?php _e( 'License Keys', 'woocommerce-software-add-on' ); ?></h2>
		        <form method="post">
		        	<?php
		        		if ( $this->messages ) {
		        			echo '<div class="updated">';
		        			foreach ( $this->messages as $message ) {
		        				echo '<p>' . wp_kses_post( $message ) . '</p>';
		        			}
		        			echo '</div>';
		        		}
		        		wp_nonce_field( 'save', 'wc-stock-management' );
		        	?>
		            <input type="hidden" name="page" value="wc_software" />
		            <?php $WC_Software_Key_Admin->display() ?>
		        </form>

		    </div>
		    <?php
		}

		/**
		 * includes function.
		 */
		public function includes() {
			if ( is_admin() ) {
				include_once( 'includes/class-wc-software-key-admin.php' );
				include_once( 'includes/class-wc-software-reports.php' );
				include_once( 'includes/class-wc-software-order-admin.php' );
				include_once( 'includes/class-wc-software-product-admin.php' );
				include_once( 'includes/class-wc-software-privacy.php' );
			}
		}

		/**
		 * handle_api_request function.
		 *
		 * @access public
		 * @return void
		 */
		public function handle_api_request() {
			include_once( 'includes/class-wc-software-api.php' );
			die;
		}

		/**
		 * admin_scripts function.
		 */
		public function admin_styles() {
			wp_enqueue_style( 'woocommerce_software_admin_styles', $this->plugin_url() . '/assets/css/admin.css' );
		}

		/**
		 * runs various functions when the plugin first activates
		 *
		 * @see register_activation_hook()
		 * @link http://codex.wordpress.org/Function_Reference/register_activation_hook
		 * @since 1.0
		 * @return void
		 */
		public function activation() {
			global $wpdb;

			$lost_license_page_id = get_option( 'woocommerce_lost_license_page_id' );

			// Creates the lost license page with the right shortcode in it
			$slug  = 'lost-license';
			$found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s LIMIT 1;", $slug ) );

			if ( empty( $lost_license_page_id ) || ! $found ) {
				$lost_license_page = array(
					'post_title' 	 => _x( 'Lost License', 'Title of a page', 'woocommerce-software-add-on' ),
					'post_content' 	 => '[woocommerce_software_lost_license]',
					'post_status' 	 => 'publish',
					'post_type' 	 => 'page',
					'post_name' 	 => $slug,
				);
				$lost_license_page_id = (int) wp_insert_post( $lost_license_page );
				update_option( 'woocommerce_lost_license_page_id', $lost_license_page_id );
			}

			// Create database tables
			$wpdb->hide_errors();

			$collate = '';
		    if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				if ( ! empty( $wpdb->collate ) ) $collate .= " COLLATE $wpdb->collate";
		    }

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// Legacy naming
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->wc_software_licenses_legacy}';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->wc_software_licenses_legacy} RENAME TO {$wpdb->wc_software_licenses};" );
				$wpdb->query( "ALTER TABLE {$wpdb->wc_software_licenses} RENAME COLUMN 'licence_key' TO 'license_key';" );
				$wpdb->query( "ALTER TABLE {$wpdb->wc_software_licenses} CHANGE `licence_key` `license_key` varchar(200);" );
				$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_software_license_key_prefix' WHERE meta_key = '_software_licence_key_prefix';" );
			}

		    // Table for storing license keys for purchases
		    $sql = "
CREATE TABLE {$wpdb->wc_software_licenses} (
  key_id bigint(20) NOT NULL auto_increment,
  order_id bigint(20) NOT NULL DEFAULT 0,
  activation_email varchar(200) NOT NULL,
  license_key varchar(200) NOT NULL,
  software_product_id varchar(200) NOT NULL,
  software_version varchar(200) NOT NULL,
  activations_limit varchar(9) NULL,
  created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  PRIMARY KEY  (key_id)
) $collate;
			";
			dbDelta( $sql );

		    // Table for tracking license key activations
		    $sql = "
CREATE TABLE {$wpdb->wc_software_activations} (
  activation_id bigint(20) NOT NULL auto_increment,
  key_id bigint(20) NOT NULL,
  instance varchar(200) NOT NULL,
  activation_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  activation_active int(1) NOT NULL DEFAULT 1,
  activation_platform varchar(200) NULL,
  PRIMARY KEY  (activation_id)
) $collate;
			";
			dbDelta( $sql );

			// Update version
			delete_option( 'woocommerce_software_version' );
			add_option( 'woocommerce_software_version', WC_SOFTWARE_VERSION );
		}

		/**
		 * order_complete function.
		 *
		 * Order is complete - give out any license codes!
		 */
		public function order_complete( $order_id ) {
			global $wpdb;

			if ( get_post_meta( $order_id, 'software_processed', true ) == 1 ) return; // Only do this once

			$order = new WC_Order( $order_id );

			if ( sizeof( $order->get_items() ) == 0 ) {
				return;
			}

			if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_renewal( $order ) ) {
				// license keys on the original/parent order still valid.
				return;
			}

			foreach ( $order->get_items() as $item ) {

				$item_product_id = ( isset( $item['product_id'] ) ) ? $item['product_id'] : $item['id'];

				if ( ! ( $item_product_id > 0 ) ) {
					continue;
				}

				$meta = get_post_custom( $item_product_id );

				if ( ! isset( $meta['_is_software'] ) || 'yes' != $meta['_is_software'][0] ) {
					continue;
				}

				$quantity = 1;
				if ( isset( $item['item_meta']['_qty'][0] ) ) {
					$quantity = absint( $item['item_meta']['_qty'][0] );
				} elseif ( isset( $item['quantity'] ) ) {
					$quantity = absint( $item['quantity'] );
				}
			    
			    $wp_table_name = "wp_woocommerce_software_licenses";
            	// FOUND SOME SOFTWARE - Lets make those licenses!
				for ( $i = 0; $i < $quantity; $i++ ) {

					$data = array(
						'order_id'            => $order_id,
						'activation_email'    => version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_email : $order->get_billing_email(),
						'prefix'              => empty( $meta['_software_license_key_prefix'][0] ) ? '' : $meta['_software_license_key_prefix'][0],
						'software_product_id' => empty( $meta['_software_product_id'][0] ) ? '' : $meta['_software_product_id'][0],
						'software_version'    => empty( $meta['_software_version'][0] ) ? '' : $meta['_software_version'][0],
						'activations_limit'   => empty( $meta['_software_activations'][0] ) ? '' : (int) $meta['_software_activations'][0],
						);
					
						//Check whether the license key already exist for same user or not if exist then pass to create license key with same user email...
						if(!empty($data['activation_email'])){
							//check if data exist prievously by email....
	            			$campaignGoalDetails = $wpdb->get_results("SELECT * FROM ".$wp_table_name." WHERE activation_email='".$data['activation_email']."'");
	            			if(!empty($campaignGoalDetails)){
	            				if(!empty($campaignGoalDetails[0]->license_key)){
	            					$str = $campaignGoalDetails[0]->license_key;
									$details = explode("wc",$str);
									if(!empty($details[1])){
										$data['license_key'] = $details[1];
									}
								}
							}
						}
					$key_id = $this->save_license_key( $data );
				}
			}

			update_post_meta( $order_id,  'software_processed', 1);
		}

		/**
		 * email_keys function.
		 *
		 * @access public
		 * @return void
		 */
		public function email_keys( $order ) {
			global $wpdb;

			$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

			$license_keys = $wpdb->get_results( "
				SELECT * FROM {$wpdb->wc_software_licenses}
				WHERE order_id = {$order_id}
			" );

			wc_get_template( 'email-keys.php', array(
				'keys'	=> $license_keys
			), 'woocommerce-software', $this->plugin_path() . '/templates/' );
		}

		/**
		 * upgrade_form function.
		 *
		 * @access public
		 * @return void
		 */
		public function upgrade_form() {
			global $product, $post;

			$is_software         = get_post_meta( $post->ID, '_is_software', true );
			$software_product_id = get_post_meta( $post->ID, '_software_product_id', true );
			$price               = get_post_meta( $post->ID, '_software_upgrade_price', true );
			$upgradable_product  = get_post_meta( $post->ID, '_software_upgradable_product', true );

			if ( $is_software == 'yes' && $software_product_id && $price && $upgradable_product ) {
				wc_get_template( 'upgrade-form.php', array(
					'software_product_id' => $software_product_id,
					'product_id'          => $product->get_id(),
					'prefix'              => get_post_meta( $post->ID, '_software_license_key_prefix', true ),
					'price'               => $price,
					'upgradable_product'  => $upgradable_product
				), 'woocommerce-software', $this->plugin_path() . '/templates/' );
			}
		}

		/** Checkout actions ************************************************************/

		/**
		 * save_update_key function.
		 *
		 * @access public
		 * @return void
		 */
		public function save_update_key( $order_id ) {
			$order = new WC_Order( $order_id );

			if ( sizeof( $order->get_items() ) > 0 ) {
				foreach ( $order->get_items() as $item ) {
					$item_product_id = $item['product_id'];

					if ( get_post_meta( $item_product_id, '_is_software', true ) == 'yes' ) {

						$license_key = false;

						foreach ( $item['item_meta'] as $meta_key => $meta_value ) {
							if ( $meta_key == __( 'Upgrade key', 'woocommerce-software-add-on' ) ) {
				            	$license_key = $meta_value[0];
							}
						}

						if ( ! $license_key ) {
							continue;
						}

						$used_keys 		= array_filter( array_map( 'trim', explode( ',', get_post_meta( $item_product_id, '_software_used_license_keys', true ) ) ) );
						$license_keys 	= array_filter( array_map( 'trim', explode( ',', get_post_meta( $item_product_id, '_software_upgrade_license_keys', true ) ) ) );

						$used_keys[] = $license_key;
						unset( $license_keys[ array_search( $license_key, $license_keys ) ] );

						update_post_meta( $item_product_id, '_software_used_license_keys', implode( ', ', $used_keys ) );
						update_post_meta( $item_product_id, '_software_upgrade_license_keys', implode( ', ', $license_keys ) );
					}
				}
			}
		}

		/** Add to cart actions ************************************************************/

		public function validate_add_cart_item( $passed, $product_id, $qty ) {
			$is_software = get_post_meta( $product_id, '_is_software', true );

			if ( ! empty( $_POST['activation_email'] ) && ! empty( $_POST['license_key'] ) && $is_software == 'yes' ) {
				// Check the posted key
				if ( empty( $_POST['license_key'] ) ) {
					wc_add_notice( __( 'Please enter your upgrade key!', 'woocommerce-software-add-on' ), 'error' );
					return false;
				}
				if ( empty( $_POST['activation_email'] ) ) {
					wc_add_notice( __( 'Please enter your activation email address!', 'woocommerce-software-add-on' ), 'error' );
					return false;
				}

				$license_key 	= esc_attr( stripslashes( trim( $_POST['license_key'] ) ) );
				$email_address 	= esc_attr( stripslashes( trim( $_POST['activation_email'] ) ) );

				if ( ! is_email( $email_address ) ) {
					wc_add_notice( __( 'Please enter a valid activation email address.', 'woocommerce-software-add-on' ), 'error' );
					return false;
				}

				// CHECK VALID!
				if ( $this->is_used_upgrade_key( $license_key, $product_id ) ) {
					wc_add_notice( __( 'This upgrade key has been used already. If you need assistance please contact us.', 'woocommerce-software-add-on' ), 'error' );
					return false;
				}

				if ( ! $this->is_valid_upgrade_key( $license_key, $product_id ) ) {
					wc_add_notice( __( 'This upgrade key is not valid. If you need assistance please contact us.', 'woocommerce-software-add-on' ), 'error' );
					return false;
				}

				// Check cart
				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					if ( isset( $values['software_upgrade'] ) && $values['software_upgrade'] ) {
						if ( $values['software_upgrade_key'] == $license_key ) {
							wc_add_notice( __( 'This key has already been used on an item in your cart.', 'woocommerce-software-add-on' ), 'error' );
							return false;
						}
					}
				}

			}

			return $passed;
		}

		/**
		 * add_cart_item_data
		 * @param array $cart_item_meta
		 * @param int $product_id
		 */
		public function add_cart_item_data( $cart_item_meta, $product_id ) {
			$is_software = get_post_meta( $product_id, '_is_software', true );

			if ( ! empty( $_POST['activation_email'] ) && ! empty( $_POST['license_key'] ) && $is_software == 'yes' ) {

				$cart_item_meta['software_upgrade'] = true;

				$license_key   = esc_attr( stripslashes( trim( $_POST['license_key'] ) ) );
				$email_address = esc_attr( stripslashes( trim( $_POST['activation_email'] ) ) );

				$cart_item_meta['software_upgrade_key']      = $license_key;
				$cart_item_meta['software_activation_email'] = $email_address;
			}

			return $cart_item_meta;
		}

		/**
		 * get_cart_item_from_session
		 * @param  array $cart_item
		 * @param  array $values
		 * @return array
		 */
		public function get_cart_item_from_session( $cart_item, $values ) {
			if ( isset( $values['software_upgrade'] ) ) {
				$cart_item['software_upgrade'] = true;
				$cart_item['software_upgrade_key'] = $values['software_upgrade_key'];
				$cart_item['software_activation_email'] = $values['software_activation_email'];

				$software_upgrade_price = get_post_meta( $cart_item['product_id'], '_software_upgrade_price', true );

				if ( version_compare( WC_VERSION, '3.0', '>' ) ) {
					$cart_item['data']->set_price( $software_upgrade_price );
				} else {
					$cart_item['data']->price = $software_upgrade_price;
				}

				$cart_item['data']->sold_individually = "yes";
			}

			return $cart_item;
		}

		/**
		 * get_item_data
		 * @param  array $other_data
		 * @param  array $cart_item
		 * @return array
		 */
		public function get_item_data( $other_data, $cart_item ) {
			if ( isset( $cart_item['software_upgrade'] ) ) {
				$software_upgradable_product = get_post_meta( $cart_item['product_id'], '_software_upgradable_product', true );

				$other_data[] = array(
					'name' 		=> __( 'Upgrading from', 'woocommerce-software-add-on' ),
					'value' 	=> $software_upgradable_product,
					'display' 	=> ''
				);

				$other_data[] = array(
					'name' 		=> __( 'Upgrade key', 'woocommerce-software-add-on' ),
					'value' 	=> $cart_item['software_upgrade_key'],
					'display' 	=> ''
				);

				$other_data[] = array(
					'name' 		=> __( 'Upgrade email', 'woocommerce-software-add-on' ),
					'value' 	=> $cart_item['software_activation_email'],
					'display' 	=> ''
				);
			}
			return $other_data;
		}

		/**
		 * add_cart_item
		 * @param array $cart_item
		 */
		public function add_cart_item( $cart_item ) {
			// Adjust price if addons are set
			if ( isset( $cart_item['software_upgrade'] ) ) {

				$software_upgrade_price = get_post_meta( $cart_item['product_id'], '_software_upgrade_price', true );

				if ( $software_upgrade_price !== '' ) {
					if ( version_compare( WC_VERSION, '3.0', '>' ) ) {
						$cart_item['data']->set_price( $software_upgrade_price );
					} else {
						$cart_item['data']->price = $software_upgrade_price;
					}

					$cart_item['data']->sold_individually = "yes";
				}

			}
			return $cart_item;
		}

		/**
		 * order_item_meta
		 * @param  int $item_id
		 * @param  array $values
		 */
		public function order_item_meta_bwc( $item_id, $values ) {
			// Add the fields
			if ( isset( $values['software_upgrade'] ) ) {
				$software_upgradable_product = get_post_meta( $values['product_id'], '_software_upgradable_product', true );
				wc_add_order_item_meta( $item_id, __( 'Upgrading from', 'woocommerce-software-add-on' ), $software_upgradable_product );
				wc_add_order_item_meta( $item_id, __( 'Upgrade key', 'woocommerce-software-add-on' ), $values['software_upgrade_key'] );
				wc_add_order_item_meta( $item_id, __( 'Upgrade email', 'woocommerce-software-add-on' ), $values['software_activation_email'] );
			}
		}

		/**
		 * Include add-ons line item meta.
		 *
		 * @param  WC_Order_Item_Product $item          Order item data.
		 * @param  string                $cart_item_key Cart item key.
		 * @param  array                 $values        Order item values.
		 */
		public function order_item_meta( $item, $cart_item_key, $values ) {
			// Add the fields
			if ( isset( $values['software_upgrade'] ) ) {
				$item_id = $item->save();

				$software_upgradable_product = get_post_meta( $values['product_id'], '_software_upgradable_product', true );
				wc_add_order_item_meta( $item_id, __( 'Upgrading from', 'woocommerce-software-add-on' ), $software_upgradable_product );
				wc_add_order_item_meta( $item_id, __( 'Upgrade key', 'woocommerce-software-add-on' ), $values['software_upgrade_key'] );
				wc_add_order_item_meta( $item_id, __( 'Upgrade email', 'woocommerce-software-add-on' ), $values['software_activation_email'] );
			}
		}

		/** AJAX ************************************************************/

		public function lost_license_ajax() {
			global $wpdb;

			check_ajax_referer( 'wc-lost-license', 'security' );

			$email = esc_attr( trim( $_POST['email'] ) );

			if ( ! is_email( $email ) ) {
				wp_send_json( array(
					'success' 	 => false,
					'message'	 => __( 'Invalid Email Address', 'woocommerce-software-add-on' )
				) );
			}

			$license_keys = $wpdb->get_results( "
				SELECT * FROM {$wpdb->wc_software_licenses}
				WHERE activation_email = '{$email}'
			" );

			if ( sizeof( $license_keys ) > 0 ) {

				ob_start();

				wc_get_template( 'email-lost-keys.php', array(
					'keys'	=> $license_keys,
					'email_heading' => __( 'Your license keys', 'woocommerce-software-add-on' )
				), 'woocommerce-software', $this->plugin_path() . '/templates/' );

				$message = ob_get_clean();

				wc_mail( $email, __( 'Your license keys', 'woocommerce-software-add-on' ), $message );

				wp_send_json( array(
					'success' 	=> true,
					'message'	=> __( 'Your license keys have been emailed', 'woocommerce-software-add-on' )
				) );

			} else {
				wp_send_json( array(
					'success' 	=> false,
					'message'	=> __( 'No license keys were found for your email address', 'woocommerce-software-add-on' )
				) );
			}
		}

		/** Shortcodes ************************************************************/

		/**
		 * lost_license_page function.
		 *
		 * @access public
		 */
		public function lost_license_page() {
			wc_get_template( 'lost-license.php', '', 'woocommerce-software', $this->plugin_path() . '/templates/' );
		}

		/** Helper functions ******************************************************/

		/**
		 * Get the plugin url
		 */
		public function plugin_url() {
			return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		}

		/**
		 * Get the plugin path
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * activations_remaining function.
		 *
		 * @access public
		 * @param mixed $key
		 * @return int
		 */
		public function activations_remaining( $key ) {
			global $wpdb;

			$key = (int) $key;

			if ( ! $key ) return 0;

			$activations_limit = $wpdb->get_var( $wpdb->prepare( "SELECT activations_limit FROM {$wpdb->wc_software_licenses} WHERE key_id = %s;", $key ) );

			if ( NULL == $activations_limit || 0 == $activations_limit ) {
				return 999999999;
			}

			$active_activations = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(activation_id) FROM {$wpdb->wc_software_activations} WHERE key_id = %s AND activation_active = 1;", $key ) );
			$remaining          = max( 0, $activations_limit - $active_activations );

			return $remaining;
		}

        /**
         * Resets the platform for all activations of a specific key
         *
         * @param $key_id Integer id of the key
         * @return int Number of rows affected
         */
        public function reset_platform_for_key( $key_id ) {
            global $wpdb;

            $key_id = absint( $key_id );

            if ( ! $key_id ) {
                return 0;
            }

            return $wpdb->update(
                $wpdb->wc_software_activations,
                array(
                    'activation_platform' => ''
                ),
                array( 'key_id' => $key_id ),
                array( '%s' ),
                array( '%d' )
            );
        }

		/**
		 * checks if a key is a valid upgrade key for a particular product
		 *
		 * @since 1.0
		 * @param string $key the key to validate
		 * @param int $item_id the product to validate for
		 * @return bool valid key or not
		 */
		public function is_valid_upgrade_key( $key = null, $item_id = null ) {
			if ( $key && $item_id ) {
				$_software_upgrade_license_keys = array_filter( array_map( 'trim', explode( ',', get_post_meta( $item_id, '_software_upgrade_license_keys', true ) ) ) );

				if ( in_array( $key, $_software_upgrade_license_keys ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * checks if a key is a used upgrade key for a particular product
		 *
		 * @since 1.0
		 * @param string $key the key to validate
		 * @param int $item_id the product to validate for
		 * @return bool valid key or not
		 */
		public function is_used_upgrade_key( $key = null, $item_id = null ) {
			if ( $key && $item_id ) {
				$_software_used_license_keys = array_filter( array_map( 'trim', explode( ',', get_post_meta( $item_id, '_software_used_license_keys', true ) ) ) );

				if ( in_array( $key, $_software_used_license_keys ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Save license key function.
		 *
		 * @param array $data License data.
		 *
		 * @return int Row ID of saved license key
		 */
		public function save_license_key( $data ) {
			global $wpdb;

			$defaults = array(
				'order_id'            => '',
				'activation_email'    => '',
				'prefix'              => '',
				'license_key'         => '',
				'software_product_id' => '',
				'software_version'    => '',
				'activations_limit'   => '',
				'created'             => current_time( 'mysql' ),
			);

			$data = wp_parse_args( $data, $defaults );

			if ( empty( $data['license_key'] ) ) {
				$data['license_key'] = $this->generate_license_key();
			}

			/**
			 * Filter the software license insert data.
			 *
			 * @since 1.7.3
			 *
			 * @param array{
			 *  @type string $order_id
			 *  @type string $activation_email
			 *  @type string $license_key
			 *  @type string $software_product_id
			 *  @type string $software_version
			 *  @type string $activations_limit
			 *  @type string $created
			 * }
			 *
			 * @return void
			 */
			$insert = apply_filters( 'woocommerce_software_addon_save_license_key', array(
				'order_id'            => $data['order_id'],
				'activation_email'    => $data['activation_email'],
				'license_key'         => $data['prefix'] . $data['license_key'],
				'software_product_id' => $data['software_product_id'],
				'software_version'    => $data['software_version'],
				'activations_limit'   => $data['activations_limit'],
				'created'             => $data['created'],
			) );

			$format = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			);

			$wpdb->insert( $wpdb->wc_software_licenses,
				$insert,
				$format
			);

			return $wpdb->insert_id;
		}

		/**
		 * Generates a unique id that is used as the license code.
		 *
		 * @since 1.0
		 * @return string the unique ID
		 */
		public function generate_license_key() {
			return apply_filters( 'woocommerce_software_addon_generate_license_key', sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			) );
		}

		/**
		 * check_product_secret function.
		 *
		 * @access public
		 * @param mixed $software_product_id
		 * @param mixed $secret_key
		 * @return void
		 */
		public function check_product_secret( $software_product_id, $secret_key ) {
			global $wpdb;

			$product_id = $wpdb->get_var( $wpdb->prepare( "
				SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = '_software_product_id'
				AND meta_value = %s LIMIT 1
			", $software_product_id ) );

			if ( ! $product_id ) return false;

			$product_secret_key = get_post_meta( $product_id, '_software_secret_product_key', true );

			if ( $product_secret_key == $secret_key ) {
				return true;
			}

			return false;
		}

		/**
		 * get_license_key function.
		 *
		 * @access public
		 * @param mixed $license_key
		 * @param mixed $software_product_id
		 * @param mixed $email
		 * @return bool
		 */
		public function get_license_key( $license_key, $software_product_id, $email ) {
			global $wpdb;

			$key = $wpdb->get_row( $wpdb->prepare( "
				SELECT * FROM {$wpdb->wc_software_licenses}
				WHERE license_key = %s
				AND software_product_id = %s
				AND activation_email = %s
				LIMIT 1
			", $license_key, $software_product_id, $email ) );

			return $key;
		}

		/**
		 * get_license_activations function.
		 *
		 * @access public
		 * @param mixed $license_key
		 * @param mixed $activation_id
		 * @return void
		 */
		public function get_license_activations( $license_key ) {
			global $wpdb;

			$licenses = $wpdb->get_results( $wpdb->prepare( "
				SELECT * FROM {$wpdb->wc_software_activations} as activations
				LEFT JOIN {$wpdb->wc_software_licenses} as licenses ON activations.key_id = licenses.key_id
				WHERE licenses.license_key = %s
			", $license_key ) );

			return $licenses;
		}

		/**
		 * Deactivate activation(s) for a given license key ID. Optionally,
		 * instance and activation_id could be passed to deactivate specific
		 * activations.
		 *
		 * @param int    $key_id         License key ID.
		 * @param string $instance       Activation instance. Default to empty. If
		 *                               provided, deactivation will target active
		 *                               activations with given instance. Instance
		 *                               is not unique so multiple activations could
		 *                               be deactivated.
		 * @param int    $activation_id  Activation ID. Default to empty. If provided
		 *                               deactivation will target activation with
		 *                               given activation_id.
		 * @param int    $limit          Limit the number of affected deactivations.
		 *                               Default to 1. Set it with zero will bypass
		 *                               limit.
		 *
		 * @return bool Returns true if deactivated successfully.
		 */
		public function deactivate_license_key( $key_id, $instance = '', $activation_id = '', $limit = 1 ) {
			global $wpdb;

			$key_id = absint( $key_id );
			$where  = $wpdb->prepare( 'key_id = %d AND activation_active = %d', $key_id, 1 );

			if ( ! empty( $instance ) ) {
				$where .= $wpdb->prepare( ' AND instance = %s', $instance );
			}

			if ( ! empty( $activation_id ) ) {
				$where .= $wpdb->prepare( ' AND activation_id = %d', absint( $activation_id ) );
			}

			$query = "SELECT activation_id FROM {$wpdb->wc_software_activations} WHERE $where";
			$limit = absint( $limit );
			if ( $limit ) {
				$query .= $wpdb->prepare( ' LIMIT %d', $limit );
			}

			$activation_ids = $wpdb->get_col( $query );
			if ( ! empty( $activation_ids ) ) {
				foreach ( $activation_ids as $activation_id ) {
					$wpdb->update(
						$wpdb->wc_software_activations,
						array(
							'activation_active' => '0'
						),
						array( 'activation_id' => $activation_id ),
						array( '%d' ),
						array( '%d' )
					);

				}
				return true;
			}

			return false;
		}

		/**
		 * activate_license_key function.
		 *
		 * @access public
		 * @param mixed $key_id
		 * @param string $instance (default: '' )
		 * @param string $platform (default: '' )
		 * @return bool
		 */
		public function activate_license_key( $key_id, $instance = '', $platform = '' ) {
			global $wpdb;

			// Find instance for license key.
			$activation_id = $this->get_activation_instance_id( $key_id, $instance );

			if ( $activation_id > 0 ) {

				// UPDATE ACTIVATION
				$wpdb->update(
					$wpdb->wc_software_activations,
					array(
						'activation_active' => '1'
					),
					array( 'activation_id' => $activation_id ),
					array( '%d' ),
					array( '%d' )
				);

				return true;

	        } else {

				// NEW ACTIVATION
		        $insert = array(
					'key_id' 				=> $key_id,
					'instance'				=> $instance,
					'activation_time'		=> current_time( 'mysql' ),
					'activation_active'		=> 1,
					'activation_platform'	=> $platform
		        );

		        $format = array(
					'%d',
					'%s',
					'%s',
					'%d',
					'%s'
		        );

		        $wpdb->insert( $wpdb->wc_software_activations,
		            $insert,
		            $format
		        );

		        return $wpdb->insert_id;
	        }
	        return false;
		}

		/**
		 * Get activation_id of given license key_id and instance.
		 *
		 * @since 1.6.3
		 *
		 * @param mixed  $key_id   License key_id
		 * @param string $instance Activation instance
		 *
		 * @return mixed Activation ID
		 */
		public function get_activation_instance_id( $key_id, $instance ) {
			global $wpdb;

			return $wpdb->get_var( $wpdb->prepare( "
				SELECT activation_id
				FROM {$wpdb->wc_software_activations}
				WHERE key_id = %s
				AND instance = %s
			", $key_id, $instance ) );
		}

		/**
		 * Legacy spelling
		 */
		public function save_licence_key( $data ) {
			return $this->save_license_key( $data );
		}

		/**
		 * Legacy spelling
		 */
		public function generate_licence_key() {
			return $this->generate_license_key();
		}

		/**
		 * Legacy spelling
		 */
		public function get_licence_key( $license_key, $software_product_id, $email ) {
			return $this->get_license_key( $license_key, $software_product_id, $email );
		}

		/**
		 * Legacy spelling
		 */
		public function get_licence_activations( $license_key ) {
			return $this->get_license_activations( $license_key );
		}

		/**
		 * Legacy spelling
		 */
		public function deactivate_licence_key( $key_id, $instance = '' ) {
			return $this->deactivate_license_key( $key_id, $instance );
		}

		/**
		 * Legacy spelling
		 */
		public function activate_licence_key( $key_id, $instance = '', $platform = '' ) {
			return $this->activate_license_key( $key_id, $instance, $platform );
		}

		/**
		 * Register CSV importer.
		 *
		 * @return void
		 */
		public function register_csv_importer() {
			if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
				return;
			}

			// Load Importer API.
			require_once ABSPATH . 'wp-admin/includes/import.php';

			if ( ! class_exists( 'WP_Importer' ) ) {
				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
				if ( file_exists( $class_wp_importer ) ) {
					require_once $class_wp_importer;
				}
			}

			// Load our license keys CSV importer.
			require_once dirname( __FILE__ ) . '/includes/class-wc-software-csv-importer.php';

			$csv_importer = new WC_Software_CSV_Importer( __FILE__ );

			// Register our license keys CSV importer.
			register_importer( 'woocommerce_software_keys_csv', __( 'WooCommerce Software License Keys (CSV)', 'woocommerce-software-add-on' ), __( 'Import software license keys to your store via a csv file.', 'woocommerce-software-add-on' ), array( $csv_importer, 'dispatch' ) );
		}
	}

	$GLOBALS['wc_software'] = new WC_Software(); // Init the main class

	// Hook into activation
	register_activation_hook( __FILE__, array( $GLOBALS['wc_software'], 'activation' ) );
}
