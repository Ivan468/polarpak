<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  prosa_postform.php                                       ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Prosa Comercio transaction handler by http://www.viart.com/
 */

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
		echo $order_errors;
		exit;
	}
	
	$post_parameters = ""; $payment_parameters = array(); $pass_parameters = array(); $pass_data = array(); $variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables, "");

	$payment_name = get_setting_value($variables, "payment_name", "");
	$user_payment_name = get_setting_value($variables, "user_payment_name", $payment_name);

	$pass_data = array();
	foreach ($payment_parameters as $parameter_name => $parameter_value) {
		if (isset($pass_parameters[$parameter_name]) && $pass_parameters[$parameter_name] == 1) {
			$pass_data[$parameter_name] = $parameter_value;
			if(isset($pass_data[strtolower($parameter_name)])){
				unset($pass_data[strtolower($parameter_name)]);
				$pass_data[$parameter_name] = $parameter_value;
			}
		}
	}

	$merchant = get_setting_value($payment_parameters, "merchant", "");
	$store = get_setting_value($payment_parameters, "store", "");
	$term = get_setting_value($payment_parameters, "term", "");
	$total = get_setting_value($payment_parameters, "total", "");
	$currency = get_setting_value($payment_parameters, "currency", "");
	$order_id = get_setting_value($payment_parameters, "order_id", "");
	$payment_url = get_setting_value($payment_parameters, "payment_url", "");
	$digest =sha1($merchant.$store.$term.$total.$currency.$order_id);
	$pass_data["digest"] = $digest;

	if (!$payment_url) {
		$payment_url = "https://www.procom.prosa.com.mx/eMerch2/validaciones/valida.do";
	}

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url", $payment_url);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");

?>