<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  upc_process.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * eCommerceConnect Gateway (http://ecommerce.upc.ua/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."includes/date_functions.php");
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

	$totalamount = round($payment_parameters['TotalAmount']*100, 2);
	$datetime_show_format_upc = array("YY", "MM", "DD", "HH", "mm", "ss");
	$purchasetime = va_date($datetime_show_format_upc, $variables["order_placed_date"]);

	$data  = $payment_parameters['MerchantID'].';'.$payment_parameters['TerminalID'].';'.$purchasetime.';'.$payment_parameters['OrderID'];
	$data .= (isset($payment_parameters['Delay'])) ? ','.$payment_parameters['Delay'].';' : ';';
	$data .= $payment_parameters['Currency'];
	$data .= (isset($payment_parameters['AltCurrency'])) ? ','.$payment_parameters['AltCurrency'].';' : ';';
	if(isset($payment_parameters['AltTotalAmount'])){
		$alttotalamount = round($payment_parameters['AltTotalAmount']*100, 2);
		$data .= $totalamount.','.$alttotalamount.';';
	}else{
		$data .= $totalamount.';';
	}
	$data .= $payment_parameters['SD'].';';

	$fp = fopen($payment_parameters['Privatekey'], "r");
	$priv_key = fread($fp, 8192);
	fclose($fp);
	$pkeyid = openssl_get_privatekey($priv_key);
	openssl_sign( $data , $b64sign, $pkeyid);
	$signature = base64_encode($b64sign) ;

	$t = new VA_Template('.'.$settings["templates_dir"]);
	$t->set_file("main","payment.html");
	$payment_name = 'eCommerceConnect upc';
	$goto_payment_message = str_replace("{payment_system}", $payment_name, GOTO_PAYMENT_MSG);
	$goto_payment_message = str_replace("{button_name}", CONTINUE_BUTTON, $goto_payment_message);
	$t->set_var("GOTO_PAYMENT_MSG", $goto_payment_message);
	$t->set_var("payment_url",$payment_parameters['action_url']);
	$t->set_var("submit_method", "post");
	$t->set_var("parameter_name", "Version");
	$t->set_var("parameter_value", $payment_parameters['Version']);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "MerchantID");
	$t->set_var("parameter_value", $payment_parameters['MerchantID']);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "TerminalID");
	$t->set_var("parameter_value", $payment_parameters['TerminalID']);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "TotalAmount");
	$t->set_var("parameter_value", $totalamount);
	$t->parse("parameters", true);
	if(isset($payment_parameters['AltTotalAmount'])){
		$t->set_var("parameter_name", "AltTotalAmount");
		$t->set_var("parameter_value", $alttotalamount);
		$t->parse("parameters", true);
	}
	$t->set_var("parameter_name", "Currency");
	$t->set_var("parameter_value", $payment_parameters['Currency']);
	$t->parse("parameters", true);
	if(isset($payment_parameters['AltCurrency'])){
		$t->set_var("parameter_name", "AltCurrency");
		$t->set_var("parameter_value", $payment_parameters['AltCurrency']);
		$t->parse("parameters", true);
	}
	$t->set_var("parameter_name", "PurchaseTime");
	$t->set_var("parameter_value", $purchasetime);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "locale");
	$t->set_var("parameter_value", $payment_parameters['locale']);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "OrderID");
	$t->set_var("parameter_value", $payment_parameters['OrderID']);
	$t->parse("parameters", true);
	if(isset($payment_parameters['Delay'])){
		$t->set_var("parameter_name", "Delay");
		$t->set_var("parameter_value", $payment_parameters['Delay']);
		$t->parse("parameters", true);
	}
	$t->set_var("parameter_name", "SD");
	$t->set_var("parameter_value", $payment_parameters['SD']);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "PurchaseDesc");
	$t->set_var("parameter_value", $payment_parameters['PurchaseDesc']);
	$t->parse("parameters", true);
	$t->set_var("parameter_name", "Signature");
	$t->set_var("parameter_value", $signature);
	$t->parse("parameters", true);
	$t->sparse("submit_payment", false);
	$t->pparse("main");
		
	exit;
?>