<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  knet_response.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Knet (http://www.knet.com.kw/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");
	include_once ($root_folder_path ."messages/".$language_code."/cart_messages.php");

	$paymentid = get_param('paymentid');
	$result = get_param('result');
	$auth = get_param('auth');
	$ref = get_param('ref');
	$tranid = get_param('tranid');
	$postdate = get_param('postdate');
	$trackid = get_param('trackid');
	$udf1 = get_param('udf1');
	$udf2 = get_param('udf2');
	$udf3 = get_param('udf3');
	$udf4 = get_param('udf4');
	$udf5 = get_param('udf5');
	$responsecode = get_param('responsecode');
	
	if(strlen($paymentid) && strlen($result)){
		$sql  = " SELECT order_id, payment_id ";
		$sql .= " FROM " . $table_prefix . "orders ";
		$sql .= " WHERE success_message=" . $db->tosql($paymentid, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$order_id = $db->f("order_id");
			$payment_id = $db->f("payment_id");

			$payment_parameters = array();
			$pass_parameters = array();
			$post_parameters = '';
			$pass_data = array();
			$variables = array();
			get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

			$success_status_id = 0;
			$pending_status_id = 0;
			$failure_status_id = 0;
			$sql  = " SELECT setting_name, setting_value ";
			$sql .= " FROM " . $table_prefix . "global_settings ";
			$sql .= " WHERE setting_type = " . $db->tosql('order_final_'.$payment_id, TEXT);
			$sql .= " AND ( ";
			$sql .= " setting_name = " . $db->tosql('success_status_id', TEXT);
			$sql .= " OR setting_name = " . $db->tosql('pending_status_id', TEXT);
			$sql .= " OR setting_name = " . $db->tosql('failure_status_id', TEXT);
			$sql .= " ) ";
			if (isset($site_id) && $site_id) {
				$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER) . ")";
				$sql .= " ORDER BY site_id ASC ";
			} else {
				$sql .= " AND site_id=1 ";
			}
			$db->query($sql);
			while ($db->next_record()) {
				if($db->f("setting_name") == 'success_status_id'){
					$success_status_id = $db->f("setting_value");
				}elseif($db->f("setting_name") == 'pending_status_id'){
					$pending_status_id = $db->f("setting_value");
				}elseif($db->f("setting_name") == 'failure_status_id'){
					$failure_status_id = $db->f("setting_value");
				}
			}
			$status_error = '';
			$transaction_id = '';
			if(strlen($ref)){
				$transaction_id .= "ref=".$ref;
			}
			if(strlen($tranid)){
				$transaction_id .= (strlen($transaction_id))? " ": "";
				$transaction_id .= "tranid=".$tranid;
			}
			if((strtoupper($result) == 'CAPTURED' || strtoupper($result) == 'APPROVED') && strlen($transaction_id)){
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET transaction_id=" . $db->tosql($transaction_id, TEXT) ;
				if(strlen($auth)){
					$sql .= ", authorization_code=" . $db->tosql($auth, TEXT) ;
				}
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
				$db->query($sql);
				$t = new VA_Template('.'.$settings["templates_dir"]);
				update_order_status($order_id, $success_status_id, true, "", $status_error);
			}else{
				$sql  = " UPDATE " . $table_prefix . "orders ";
				$sql .= " SET error_message=" . $db->tosql($result, TEXT) ;
				if(strlen($transaction_id)){
					$sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT) ;
				}
				if(strlen($auth)){
					$sql .= ", authorization_code=" . $db->tosql($auth, TEXT) ;
				}
				$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
				$db->query($sql);
				$t = new VA_Template('.'.$settings["templates_dir"]);
				update_order_status($order_id, $failure_status_id, true, "", $status_error);
			}
			if (isset($payment_parameters['errorURL'])){
				echo 'REDIRECT='. $payment_parameters['errorURL'];
			}
		}
	}

?>