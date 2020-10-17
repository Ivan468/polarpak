<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  ultimatepay_process.php                                  ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * UltimatePay (http://www.ultimatepay.com/) transaction handler by www.viart.com
 */

/*
	The commtype parameter below deserves special mention as it identifies what action to take on the customer's account.  There are three possible values:
	PAYMENT - Notifies you of receipt of payment from the customer and that you should proceed to deliver the service the customer has paid for.
	FORCED_REVERSAL - Notifies you that a previously received payment has been reversed by the customer or the customer's bank.  Examples of such actions include credit card chargebacks and PayPal reversals.  In these cases, the customer's account should be suspended.  The amount originally paid will not be settled to you and we will return a negative amount parameter.  If UltimatePay later sends a PAYMENT for this userid, it means that UltimatePay has collected the bad debt and the user's account can be reactivated.
	ADMIN_REVERSAL - Notifies you that a previously received payment must be reversed due to a merchant or UltimatePay-granted refund request, or to correct a system error.  We will return a negative amount parameter.  This action does not communicate ill will or fraud by the customer.  While the services granted should be backed out, the customer's account should not be suspended.
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

	$hash_string  = '';
	$hash_string .= (isset($payment_parameters['userid']))? $payment_parameters['userid']: "";
	$hash_string .= (isset($payment_parameters['adminpwd']))? $payment_parameters['adminpwd']: "";
	$hash_string .= (isset($payment_parameters['secret']))? $payment_parameters['secret']: "";
	$hash_string .= (isset($payment_parameters['pkgid']))? $payment_parameters['pkgid']: "";
	$hash_string .= (isset($payment_parameters['currency']))? $payment_parameters['currency']: "";
	$hash_string .= (isset($payment_parameters['amount']))? $payment_parameters['amount']: "";
	$hash_string .= (isset($payment_parameters['sepamount']))? $payment_parameters['sepamount']: "";
	$hash_string .= (isset($payment_parameters['paymentid']))? $payment_parameters['paymentid']: "";
	$hash_string .= (isset($payment_parameters['merchtrans']))? $payment_parameters['merchtrans']: "";
	$hash_string .= (isset($payment_parameters['riskmode']))? $payment_parameters['riskmode']: "";
	$hash_string .= (isset($payment_parameters['developerid']))? $payment_parameters['developerid']: "";
	$hash_string .= (isset($payment_parameters['appid']))? $payment_parameters['appid']: "";


	//userid, adminpwd, secret, pkgid, currency, amount, sepamount, paymentid, merchtrans, riskmode, developerid, appid.


	$pass_data['hash'] = md5($hash_string);

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$goto_payment_message = str_replace("{payment_system}", $user_payment_name, GOTO_PAYMENT_MSG);
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