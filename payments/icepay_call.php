<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  icepay_call.php                                          ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 *  IcePay (www.icepay.eu) transaction handler by http://www.viart.com/
 */

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

	$source  = $payment_parameters['Encryptioncode']."|";
	$source .= $payment_parameters['IC_Merchant']."|";
	$source .= $payment_parameters['IC_Amount']."|";
	$source .= $payment_parameters['IC_Currency']."|";
	$source .= $payment_parameters['IC_OrderID']."|";
	$source .= $payment_parameters['IC_PaymentMethod']."|";
	$source .= $payment_parameters['IC_Issuer'];
	
	$signature = sha1($source);
	$pass_data['IC_CheckSum'] = strtoupper($signature);

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$payment_name = 'IcePay';
	$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$advanced_url);
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