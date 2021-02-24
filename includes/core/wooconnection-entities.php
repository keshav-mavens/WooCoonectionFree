<?php
	//Trigger type entity : GENERAL
	define("WOOCONNECTION_TRIGGER_TYPE_GENERAL", 1);
	//Trigger type entity : CART
	define("WOOCONNECTION_TRIGGER_TYPE_CART", 2);
	//Trigger type entity : ORDER
	define("WOOCONNECTION_TRIGGER_TYPE_ORDER", 3);
	//Trigger version type : FREE
	define("TRIGGER_VERISON_FREE", 1);
	//Trigger version type : PRO
	define("TRIGGER_VERISON_PRO", 2);
	//Application type entity : infusionsoft
	define("APPLICATION_TYPE_INFUSIONSOFT", 1);
	//Application type entity : keap
	define("APPLICATION_TYPE_KEAP", 2);
	//Response type entity : success
	define("RESPONSE_STATUS_TRUE", 1);
	//Response type entity : fail
	define("RESPONSE_STATUS_FALSE", 2);
	//Plugin Activation status : Activated..
	define("PLUGIN_ACTIVATED", 1);
	//Plugin Activation status : Not Activated..
	define("PLUGIN_NOT_ACTIVATED", 2);
	//Plugin Activation Remote url
	define("ADMIN_REMOTE_URL", "https://wooconnection.com/");
	//Plugin Activation Request Type
	define("ACTIVATION_REQUEST_TYPE", "activation");
	//Plugin Activation Product Id...
	define("ACTIVATION_PRODUCT_ID", "wooconnectionpaid");
	//Plugin Activation Secret key...
	define("ACTIVATION_SECRET_KEY", "wooconnectionpaid16");
	//Plugin Activation Instance...
	define("ACTIVATION_INSTANCE", 16.0);
	//Site url....
	define("SITE_URL", get_site_url());
	//Application type label..
	define("APPLICATION_TYPE_INFUSIONSOFT_LABEL", 'Infusionsoft');
	//Application type label..
	define("APPLICATION_TYPE_KEAP_LABEL", 'Keap');
	//define log type....
	define("LOG_TYPE_FRONT_END", 'FrontendLogs');
	define("LOG_TYPE_BACK_END", 'BackendLogs');
	//Set the label export page.....
	define("IMPORT_EXPORT_LABEL_FREE",'Export');
	define("IMPORT_EXPORT_LABEL_PAID",'Import/Export');
	//order item types.....
	define("ITEM_TYPE_TAX", 2);
	define("ITEM_TYPE_DISCOUNT", 7);
	define("ITEM_TYPE_PRODUCT",4);
	define("ITEM_TYPE_SUBSCRIPTION",9);
	//Non product....
	define("NON_PRODUCT_ID", 0);
	//Order item quantity
	define("ORDER_ITEM_QUANTITY", 1);
	//Note for order item tax.....
	define("ITEM_TAX_NOTES", "Order Tax");
	//Note for order item discount......
	define("ITEM_DISCOUNT_NOTES", "Order Discount");
	//database custom entries status....
	define('STATUS_ACTIVE', 1);
	define('STATUS_INACTIVE', 2);
	define('STATUS_DELETED', 3);
	//custom field form type entitiy.....
	define('CUSTOM_FIELD_FORM_TYPE_CONTACT', -1);
	define('CUSTOM_FIELD_FORM_TYPE_ORDER', -9);
	//custom field input type entities........
	define('CF_FIELD_TYPE_TEXT', 1);
	define('CF_FIELD_TYPE_TEXTAREA', 2);
	define('CF_FIELD_TYPE_DROPDOWN', 3);
	define('CF_FIELD_TYPE_RADIO', 4);
	define('CF_FIELD_TYPE_CHECKBOX', 5);
	define('CF_FIELD_TYPE_DATE', 6);
	define('CF_FIELD_REQUIRED_YES', 1);
	define('CF_FIELD_REQUIRED_NO', 2);
	//custom field actions entity.....
	define('CF_FIELD_ACTION_SHOW', 'show');
	define('CF_FIELD_ACTION_HIDE', 'hide');
	//Below 3 entities is related to default thanks override....
	define('DEFAULT_WORDPRESS_POST', 1);
	define('DEFAULT_WORDPRESS_PAGE', 2);
	define('DEFAULT_WORDPRESS_CUSTOM_URL', 3);
	//Below 7 entities is related to redirect thanks override....
	define('REDIRECT_CONDITION_CART_SPECIFIC_PRODUCTS', 1);
	define('REDIRECT_CONDITION_CART_SPECIFIC_CATEGORIES', 2);
	//override link yes no...
	define('REDIRECT_OVERRIDE_TRUE', 1);
	define('REDIRECT_OVERRIDE_FALSE', 0);
	//payment modes....
	define('PAYMENT_MODE_TEST',1);
	define('PAYMENT_MODE_SKIPPED',2);
	define('PAYMENT_MODE_LIVE',3);
	//limit and offset entities for lazy loading of products..
	define("PRODUCT_LAZY_LOADING_LIMIT", 20);
	define("PRODUCT_LAZY_LOADING_OFFSET", 20);
	//set the products html.....
	define("PRODUCTS_HTML_TYPE_LOAD_MORE", 1);
	//set the coupon listing html type...
	define("COUPONS_HTML_WITH_LOAD_MORE", 1);
	//sku on the basis of campaign goals....
	define('SKU_LENGHT_SPECIFIC_PRODUCT', 40);
	define('SKU_LENGHT_REVIEW', 34);
	define('SKU_LENGHT_CART_ITEM', 35);
	//contact note type entities....
	define('NOTE_TYPE_PRODUCT', 1);
	define('NOTE_TYPE_REVIEW', 2);
	//follow up related entities....
	define('FOLLOW_UP_INTEGRATION_NAME', 'wooconnection');
	define('FOLLOW_UP_CHECKOUT_CALL_NAME', 'reachedcheckoutpage');
	define('FOLLOW_UP_PURCHASE_CALL_NAME', 'purchaseany');
	define('FOLLOW_UP_EMPTY_CART_CALL_NAME', 'cartempty');
	//subscription amount update status.....
	define('SUBSCRIPTION_AMOUNT_UPDATED_FALSE',0);
	define('SUBSCRIPTION_AMOUNT_UPDATED_TRUE', 1);
	//discount/free trial duration entities....
	define('DURATION_TYPE_DAY', 1);
	define('DURATION_TYPE_WEEK', 2);
	define('DURATION_TYPE_MONTH', 3);
	define('DURATION_TYPE_YEAR',4);
	//subscription discount type....
	define('SUBSCRIPTION_DISCOUNT_TYPE_AMOUNT', 1);
	define('SUBSCRIPTION_DISCOUNT_TYPE_PERCENT', 2);
