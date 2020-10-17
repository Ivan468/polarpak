<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  gtbill_api_confirm.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * GTBill QuickPay API (http://www.gtbill.com/) transaction handler by www.viart.com
 */

	$is_admin_path = true;
	$root_folder_path = "../";

	include_once ($root_folder_path ."includes/common.php");
	include_once ($root_folder_path ."includes/record.php");
	include_once ($root_folder_path ."includes/order_links.php");
	include_once ($root_folder_path ."includes/order_items.php");
	include_once ($root_folder_path ."includes/parameters.php");

	$Amount					= get_param("Amount");
	$order_id				= get_param("MerchantReference");
	$transaction_id			= get_param("TransactionID");
	$CardMask				= get_param("CardMask");
	$CustomerEmail			= get_param("CustomerEmail");
	$CustomerFirstName		= get_param("CustomerFirstName");
	$CustomerLastName		= get_param("CustomerLastName");
	$SiteName				= get_param("SiteName");
	$SiteID					= get_param("SiteID");
	$ShippingFirstName		= get_param("ShippingFirstName");
	$ShippingLastName		= get_param("ShippingLastName");
	$ShippingAddress1		= get_param("ShippingAddress1");
	$ShippingAddress2		= get_param("ShippingAddress2");
	$ShippingCity			= get_param("ShippingCity");
	$ShippingState			= get_param("ShippingState");
	$ShippingCountry		= get_param("ShippingCountry");
	$ShippingPostalCode		= get_param("ShippingPostalCode");

	$index_of_items = 0;
	$payment_parameters = array();
	$pass_parameters = array();
	$post_parameters = '';
	$pass_data = array();
	$variables = array();
	get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

	$ch = curl_init ();
	if ($ch && isset($payment_parameters['confirm_ip_list_url'])){

		curl_setopt($ch, CURLOPT_URL, $payment_parameters['confirm_ip_list_url']);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT,90);

		$response = curl_exec($ch);
		curl_close($ch);

		$response = trim($response);
		if(strlen($response)){
			$ips = explode("|", $response);
			$remote_address = get_ip();
			$update_fields = "";
			if(in_array($remote_address, $ips)){
				if(strlen($transaction_id)){
					$update_fields .= "transaction_id=" . $db->tosql($transaction_id, TEXT);
				}
				if(strlen($CustomerEmail)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "email=" . $db->tosql($CustomerEmail, TEXT);
				}
				if(strlen($CustomerFirstName)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "first_name=" . $db->tosql($CustomerFirstName, TEXT);
				}
				if(strlen($CustomerLastName)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "last_name=" . $db->tosql($CustomerLastName, TEXT);
				}
				if(strlen($ShippingFirstName)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_first_name=" . $db->tosql($ShippingFirstName, TEXT);
				}
				if(strlen($ShippingLastName)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_last_name=" . $db->tosql($ShippingLastName, TEXT);
				}
				if(strlen($ShippingAddress1)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_address1=" . $db->tosql($ShippingAddress1, TEXT);
				}
				if(strlen($ShippingAddress2)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_address2=" . $db->tosql($ShippingAddress2, TEXT);
				}
				if(strlen($ShippingCity)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_city=" . $db->tosql($ShippingCity, TEXT);
				}
				if(strlen($ShippingState)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_state_code=" . $db->tosql($ShippingState, TEXT);
				}
				if(strlen($ShippingPostalCode)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_zip=" . $db->tosql($ShippingPostalCode, TEXT);
				}
				if(strlen($ShippingCountry)){
					$update_fields .= (strlen($update_fields))? ", ": "";
					$update_fields .= "delivery_country_code=" . $db->tosql($ShippingCountry, TEXT);
				}
				if(strlen($update_fields)){
					$sql  = " UPDATE " . $table_prefix . "orders ";
					$sql .= " SET " . $update_fields ;
					$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
					$db->query($sql);
				}
				$status_error = "";
				$order_status_id = $variables["success_status_id"];
				$t = new VA_Template('.'.$settings["templates_dir"]);
				update_order_status($order_id, $order_status_id, true, "", $status_error);
			}
		}
	}
?>