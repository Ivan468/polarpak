<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cybersource_hop_process.php                              ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * CyberSource (http://www.cybersource.com/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."includes/date_functions.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");
	include_once ($root_folder_path ."payments/cybersource_functions.php");

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
			if(strtolower($parameter_name) == 'currency'){
				$parameter_value = strtolower($parameter_value);
			}
			/*
			if(strtolower($parameter_name) == 'taxamount'){
				$sql  = " SELECT * FROM " . $table_prefix . "orders_taxes";
				$sql .= " WHERE order_id=". $db->tosql($variables["order_id"], INTEGER);
				$sql .= " AND tax_id=". $db->tosql(0, INTEGER);
				$sql .= " AND order_fixed_amount>". $db->tosql(0, FLOAT);
				$db->query($sql);
				if($db->next_record()) {
					$parameter_value = $db->f("order_fixed_amount");
				}else{
					$parameter_value = 0;
				}
			}//*/
			$pass_data[$parameter_name] = $parameter_value;
			if(!strlen($parameter_value)){
				unset($pass_data[$parameter_name]);
			}
			if(isset($pass_data[strtolower($parameter_name)])){
				unset($pass_data[strtolower($parameter_name)]);
				$pass_data[$parameter_name] = $parameter_value;
			}
		}
	}

	if ($test_account) {
		$payment_url = "https://secureacceptance.cybersource.com/silent/pay";
	} else {
		$payment_url = "https://testsecureacceptance.cybersource.com/silent/pay";
	}

	$timestamp = getmicrotime();
	$data = $payment_parameters['merchantID'] . $payment_parameters['amount'] . $pass_data['currency'] . $timestamp . $payment_parameters['orderPage_transactionType'];
	$pub_digest = hopHash($data, $payment_parameters['PublicKey']);

	$pass_data['orderPage_timestamp'] = $timestamp;
	$pass_data['orderPage_signaturePublic'] = $pub_digest;
	
	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$payment_name = 'CyberSource HOP';
	$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$payment_parameters['payment_url']);
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