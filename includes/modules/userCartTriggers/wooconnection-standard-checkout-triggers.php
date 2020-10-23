<?php
//If file accessed directly then exit;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Woocommerce hook : This action is triggered when user reaches checkout page.
add_action('woocommerce_before_checkout_form', 'wooconnection_user_arrive_checkout', 10, 0);

//Function Definiation : wooconnection_user_arrive_checkout_page
function wooconnection_user_arrive_checkout(){
}
?>