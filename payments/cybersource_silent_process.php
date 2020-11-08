<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  cybersource_silent_process.php                           ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
	https://ebctest.cybersource.com
	U: ecoproducts
	P: Ecotest61!!
//*/


/*
 * CyberSource (www.cybersource.com) SOP transaction handler by ViArt Ltd. (www.viart.com)
 * Date: 24.Sep.2018
 */

	global $is_admin_path, $is_sub_folder;
	$root_folder_path = ((isset($is_admin_path) && $is_admin_path) || (isset($is_sub_folder) && $is_sub_folder)) ? "../" : "./";
	include_once($root_folder_path . "payments/cybersource_functions.php");

	$order_id = get_session("session_order_id");
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "");

	// if CyberSource return error clear it from database so user can try once more time
	$payment_error = get_param("payment_error");
	if ($payment_error) {
		$sql  = " UPDATE ".$table_prefix."orders SET error_message='' ";
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
	}
	
	
	// check response parameters
	$reason_code = "";
	$decision = "";

	// use transaction type and test parameter to set correct cybersource url
	$test_account = get_setting_value($payment_parameters, "test", "");
	$transaction_type = trim(get_setting_value($payment_parameters, "transaction_type", ""));
	if ($transaction_type == "create_payment_token") {
		// Transaction type: create_payment_token
		if ($test_account) {
			$cybersource_url = "https://testsecureacceptance.cybersource.com/silent/token/create";
		} else {
			$cybersource_url = "https://secureacceptance.cybersource.com/silent/token/create";
		}
	} else {
		// Transaction types: authorization; authorization,create_payment_token;authorization,update_payment_token;sale;sale,create_payment_token;sale,update_payment_token;
		if ($test_account) {
			$cybersource_url = "https://testsecureacceptance.cybersource.com/silent/pay";
		} else {
			$cybersource_url = "https://secureacceptance.cybersource.com/silent/pay";
		}
	}
	$t->set_var("cybersource_url", htmlspecialchars($cybersource_url));

	// get secret key parameter to generate signature parameter
	$secret_key = get_setting_value($payment_parameters, "secret_key", "");

	// check available unsigned fields and pass to parameters array
	$unsigned_fields = array();
	if (isset($cc_info)) {
		if (get_setting_value($cc_info, "show_cc_type")) {
			$unsigned_fields[] = "card_type";
		}
		if (get_setting_value($cc_info, "show_cc_number")) {
			$unsigned_fields[] = "card_number";
		}
		if (get_setting_value($cc_info, "show_cc_expiry_date")) {
			$unsigned_fields[] = "card_expiry_date";
		}
		if (get_setting_value($cc_info, "show_cc_security_code")) {
			$unsigned_fields[] = "card_cvn";
		}
	}
	if (!count($unsigned_fields)) {
		$unsigned_fields = array("card_type","card_number","card_expiry_date","card_cvn");
	}
	$pass_data["unsigned_field_names"] = implode(",", $unsigned_fields);
	// generate data to pass
	get_cybersource_signature($pass_data, $secret_key, true);

	$cc_type_values = array(array("", PLEASE_CHOOSE_MSG));
	foreach ($cc_types as $type_id => $type_data) {
		$cc_type_code = cybersource_cc_type($type_data["code"]);
		if ($cc_type_code) {
			$cc_type_values[] = array($cc_type_code, $type_data["name"]);
		}
	}
	
	$post_params = "";
	foreach ($pass_data as $parameter_name => $parameter_value) {
		if (strlen($post_params)) { $post_params .= "&"; }
		$post_params .= urlencode($parameter_name) . "=" . urlencode($parameter_value);

		$t->set_var("parameter_name", htmlspecialchars($parameter_name));
		$t->set_var("parameter_value", htmlspecialchars($parameter_value));
		$t->parse("parameters", true);
	}

return;

	/*
	$ch = @curl_init();
	if ($ch) {
		curl_setopt($ch, CURLOPT_URL, $cybersource_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "ViArt SHOP Cybersource Module");
		set_curl_options($ch, $payment_parameters);

		$payment_response = curl_exec($ch);
		curl_close($ch);
echo "\n<br><hr>".htmlspecialchars($payment_response)."<hr>"; 
		exit;
	} else {
		$error_message .= "Can't initialize cURL.";
	} //*/


/*
001: Visa
002: Mastercard
003: American Express
004: Discover
005: Diners Club: cards starting with 54 or 55 are rejected.
006: Carte Blanche
007: JCB
014: EnRoute
021: JAL
024: Maestro UK Domestic
027: Nicos
031: Delta
033: Visa Electron
034: Dankort
036: Cartes Bancaires
037: Carta Si
042: Maestro International
043: GE Money UK card
050: Hipercard (sale only)
053: Orico
054: Elo
055: Private Label
//*/