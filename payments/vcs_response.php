<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  vcs_response.php                                         ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * VCS (https://www.vcs.co.za) transaction handler by http://www.viart.com/
 */

	$is_admin_path = true;
	$root_folder_path = "../";
		
	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$p1 = get_param("p1");
	$p2 = get_param("p2");
	$p3 = get_param("p3");
	$p4 = get_param("p4");
	$p5 = get_param("p5");
	$p6 = get_param("p6");
	$p7 = get_param("p7");
	$p8 = get_param("p8");
	$p9 = get_param("p9");
	$p10 = get_param("p10");
	$p11 = get_param("p11");
	$p12 = get_param("p12");
	$pam = get_param("pam");
	$m1 = get_param("m1");
	$m2 = get_param("m2");
	$m3 = get_param("m3");
	$m4 = get_param("m4");
	$m5 = get_param("m5");
	$m6 = get_param("m6");
	$m7 = get_param("m7");
	$m8 = get_param("m8");
	$m9 = get_param("m9");
	$m10 = get_param("m10");
	$CardHolderIpAddr = get_param("CardHolderIpAddr");
	$MaskedCardNumber = get_param("MaskedCardNumber");

	$t = new VA_Template('.'.$settings["templates_dir"]);

	$order_id = $p2;

	$status_error = '';
	$error_message = '';
	$pending_message = '';
	$transaction_id = '';
	$order_status_id = 0;
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);
	if ($payment_parameters['pam']!=$pam) {
		echo "<CallBackResponse>PAM Declined</CallBackResponse>";
		exit;
	}
	if(preg_match('/Duplicate/Uis', $p4, $value)){
		$pending_message = "Response: " . $p4 . " This order will be reviewed manually.";
	}elseif(preg_match('/APPROVED/Uis', $p3, $value)){
		$transaction_id = $p3 . $p2;
	}else{
		$error_message = $p3;
	}
	if ($p6!=$payment_parameters['p4']) {
		$error_message = "Failed. Amount incorrect!";
	}
	
	if(strlen($error_message)){
		$order_status_id = $variables["failure_status_id"];
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET error_message=" . $db->tosql($error_message, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
	}elseif(strlen($pending_message)){
		$order_status_id = $variables["pending_status_id"];
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET pending_message=" . $db->tosql($pending_message, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
	}elseif(strlen($transaction_id)){
		$order_status_id = $variables["success_status_id"];
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT);
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
		$db->query($sql);
	}

	if($order_status_id){
		update_order_status($order_id, $order_status_id, true, "", $status_error);
		echo "<CallBackResponse>Accepted</CallBackResponse>";
	}else{
		echo "<CallBackResponse>Declined</CallBackResponse>";
	}

?>