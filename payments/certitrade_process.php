<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  certitrade_process.php                                   ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CertiTrade (http://www.certitrade.net/) transaction handler by www.viart.com
 */

 	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$vc = get_session("session_vc");
	$order_id = get_session("session_order_id");

	$order_errors = check_order($order_id, $vc);
	if($order_errors) {
		echo $order_errors;
		exit;
	}

	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

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

	$md5str  = $payment_parameters['md5key'];
	$md5str .= $payment_parameters['merchantid'];
	$md5str .= $payment_parameters['rev'];
	$md5str .= $payment_parameters['orderid'];
	$md5str .= $payment_parameters['amount'];
	$md5str .= $payment_parameters['currency'];
	$md5str .= $payment_parameters['retururl'];
	$md5str .= $payment_parameters['approveurl'];
	$md5str .= $payment_parameters['declineurl'];
	$md5str .= $payment_parameters['cancelurl'];
	$md5str .= $payment_parameters['returwindow'];
	$md5str .= $payment_parameters['lang'];
	$md5str .= $payment_parameters['cust_id'];
	$md5str .= $payment_parameters['cust_name'];
	$md5str .= $payment_parameters['cust_address1'];
	$md5str .= $payment_parameters['cust_address2'];
	$md5str .= $payment_parameters['cust_address3'];
	$md5str .= $payment_parameters['cust_zip'];
	$md5str .= $payment_parameters['cust_city'];
	$md5str .= $payment_parameters['cust_phone'];
	$md5str .= $payment_parameters['cust_email'];
	$md5str .= $payment_parameters['connection'];
	$md5str .= $payment_parameters['acquirer'];
	$md5str .= $payment_parameters['debug'];
	$md5str .= $payment_parameters['httpdebug'];

	$pass_data['md5code'] = md5($md5str);

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$payment_name = 'CertiTrade';
	$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$payment_parameters['action_url']);
	$t->set_var("submit_method", "post");
	foreach ($pass_data as $parameter_name => $parameter_value) {
		$t->set_var("parameter_name", $parameter_name);
		$t->set_var("parameter_value", $parameter_value);
		$t->parse("parameters", true);
	}
	$t->sparse("submit_payment", false);
	$t->pparse("main");

	exit;
?>