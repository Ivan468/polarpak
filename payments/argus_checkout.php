<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  argus_checkout.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Argus Checkout handler by http://www.viart.com/
 */

	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_STRICT);

	$is_admin_path = true;
	$root_folder_path = "../";


	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."includes/parameters.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		//echo $order_errors;
		//exit;
	}

	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "", "no");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", $site_url);

	//https://svc.arguspayments.com/paymentpage/load/9261/15668/31011?OFFER_AMOUNT=0.1&OFFER_NAME=DVD&XTL_UDF01=123&PG_SUCC_URL=http%3A%2F%2Fkristenbjorn.com%2Fweb%2Fmodel%2Fstoreonline%2Forder_final.php&PG_DEC_URL=http%3A%2F%2Fkristenbjorn.com%2Fweb%2Fmodel%2Fstoreonline%2Forder_final.php

	// payment URL
	$payment_url = get_setting_value($payment_parameters, "payment_url", "");  
	$CLIENT_ID = get_setting_value($payment_parameters, "CLIENT_ID");  // Client Identifier (provided by Argus) 
	$SITE_ID = get_setting_value($payment_parameters, "SITE_ID");  // Site Identifier (provided by Argus) 
	$PRODUCT_ID = get_setting_value($payment_parameters, "PRODUCT_ID", "");  
	if (!$payment_url) {
		//$payment_url = "https://svc.arguspayments.com/paymentpage/load/".$CLIENT_ID."/".$SITE_ID."/".$PRODUCT_ID;
		$payment_url = "https://svc.arguspayments.com/paymentpage/load/".$CLIENT_ID."/".$SITE_ID;
	}

	$order_total = get_setting_value($variables, "order_total", 0);  
	$total_quantity = get_setting_value($variables, "total_quantity", 0);  
	$OFFER_AMOUNT = get_setting_value($payment_parameters, "OFFER_AMOUNT", $order_total);  // Product Amount/Price Number (10,2) 
	$OFFER_NAME = get_setting_value($payment_parameters, "OFFER_NAME", $total_quantity." ".PRODUCTS_TITLE);  //  Product Name Varchar (64 char.)
	if (strlen($OFFER_NAME) > 64) {
		$OFFER_NAME = substr($OFFER_NAME, 0, 64);
	}
	$OFFER_NAME = preg_replace("/[^0-9a-z\.\s\-]/i", "", $OFFER_NAME); // remove symbols which can cause errors
	$OFFER_DESC = get_setting_value($payment_parameters, "OFFER_DESC", $OFFER_NAME); // Product Description (this will be displayed on the Payment Page) Varchar (128 char.)  
	$OFFER_DESC = preg_replace("/[^0-9a-z\.\s\-]/i", "", $OFFER_DESC); // remove symbols which can cause errors
	if (strlen($OFFER_DESC) > 128) {
		$OFFER_DESC = substr($OFFER_DESC, 0, 64);
	}

	$REBILL_METRIC = get_setting_value($payment_parameters, "REBILL_METRIC", "Y");  // Rebill Metric Type Set this parameter:  D - Day M - Month Y - Year (Required)
	$REBILL_PERIOD = get_setting_value($payment_parameters, "REBILL_PERIOD", "1");  // Rebill Period Number (3) (Required)
	$PROD_TYPE = get_setting_value($payment_parameters, "PROD_TYPE", "6");  // Product type: 1 - Non-renewing membership 2 - Renewing membership  6 - One-time purchase (nonmembership) (Required)
	$OFFER_TTL = get_setting_value($payment_parameters, "OFFER_TTL"); // Page "time to live". The date and time should be set in the future. The page will "expire" if TTL set is in the past. Date (DDMMYYYYHH24MISS)
	if (!strlen($OFFER_TTL)) {
		$OFFER_TTL = gmdate("dmYHis", time() + 24*3600); // 1 hour forward
	}
	$OFFER_VAR = get_setting_value($payment_parameters, "OFFER_VAR"); // Salt - this should be a randomly generated 2 alphanumeric characters for each time you generate a link.  
	if (!strlen($OFFER_VAR)) {
		$OFFER_VAR = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 2)), 0, 2);
	}
	$SHAREDKEY = get_setting_value($payment_parameters, "SHAREDKEY"); 

	$OFFER_KEY = ""; // This field should contain the hash. 
	if ($SHAREDKEY) {
		$OFFER_KEY = strtoupper(sha1($OFFER_AMOUNT.$OFFER_NAME.$REBILL_METRIC.$REBILL_PERIOD.$OFFER_TTL.$OFFER_VAR.$SHAREDKEY));
	}

	$CUST_FNAME = get_setting_value($variables, "first_name"); // Customer First Name 
	$CUST_LNAME = get_setting_value($variables, "last_name"); // Customer Last Name
	$CUST_EMAIL = get_setting_value($variables, "email"); // Email Address
	$CUST_ID_XTL = get_setting_value($variables, "user_id"); // External Customer ID

	$BILL_ADDR = get_setting_value($variables, "address"); // Billing Street Address
	$BILL_ADDR_CITY = get_setting_value($variables, "city"); // Billing City
	$BILL_ADDR_STATE = get_setting_value($variables, "state_code"); // Billing 2-letter State Code
	$BILL_ADDR_ZIP = get_setting_value($variables, "zip"); // Billing ZIP or Postal Code
	$BILL_ADDR_COUNTRY = get_setting_value($variables, "country_code"); // Billing 2-letter Country Code 
	$XTL_UDF01 = get_setting_value($payment_parameters, "XTL_UDF01"); // user-defined field 1 - pass order_id here
	$XTL_UDF02 = get_setting_value($payment_parameters, "XTL_UDF02"); // user-defined field 2 
	if (!strlen($XTL_UDF01)) {
		$XTL_UDF01 = get_setting_value($variables, "order_id");
	}
 
	$PGCSS = get_setting_value($payment_parameters, "PGCSS"); // CSS Location (email support@arguspayments.com before using this field)
	$PG_REDIR = 1; // Set to "1" to redirect the user to an external page after purchase attempt 
	$PG_SUCC_URL = $secure_url."order_final.php";  // URL of where user will be redirected to after a successful purchase
	$PG_DEC_URL = $secure_url."order_final.php"; // URL of where user will be redirected to after a failed purchase 

	$argus_params = array(
		"OFFER_AMOUNT" => $OFFER_AMOUNT,
		"OFFER_NAME" => $OFFER_NAME,
		"OFFER_DESC" => $OFFER_DESC,
  
		"REBILL_METRIC" => $REBILL_METRIC,
		"REBILL_PERIOD" => $REBILL_PERIOD,
		"PROD_TYPE" => $PROD_TYPE,
		"OFFER_TTL" => $OFFER_TTL,
		"OFFER_VAR" => $OFFER_VAR,
		"OFFER_KEY" => $OFFER_KEY,
  
		"CUST_FNAME" => $CUST_FNAME,
		"CUST_LNAME" => $CUST_LNAME,
		"CUST_EMAIL" => $CUST_EMAIL,
		"CUST_ID_XTL" => $CUST_ID_XTL,
  
		"BILL_ADDR" => $BILL_ADDR,
		"BILL_ADDR_CITY" => $BILL_ADDR_CITY,
		"BILL_ADDR_STATE" => $BILL_ADDR_STATE,
		"BILL_ADDR_ZIP" => $BILL_ADDR_ZIP,
		"BILL_ADDR_COUNTRY" => $BILL_ADDR_COUNTRY,
		"XTL_UDF01" => $XTL_UDF01,
		"XTL_UDF02" => $XTL_UDF02,
   
		"PGCSS" => $PGCSS,
		"PG_REDIR" => $PG_REDIR,
		"PG_SUCC_URL" => $PG_SUCC_URL,
		"PG_DEC_URL" => $PG_DEC_URL,
	);

	$url_params = "";
	foreach ($argus_params as $param_name => $param_value) {
		if (strlen($param_value)) {
			$url_params .= ($url_params) ? "&" : "?";
			$url_params .= urlencode($param_name)."=".urlencode($param_value);
		}
	}
	$payment_url .= $url_params;

	// redirect user to payment page
	header("Location: " .$payment_url);
	exit;

?>