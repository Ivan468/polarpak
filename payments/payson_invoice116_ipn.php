<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payson_invoice116_ipn.php                                ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/
 
/*
 * Payson Checkout IPN handler by http://www.viart.com/
 */

/* debug email */
mail ("enquiries@viart.com", "payson_invoice116_ipn init", "Init");

	ini_set("display_errors", "1");
	error_reporting(E_ALL & ~E_STRICT);
	$input_data = null;
	$input_data = payson_decode(file_get_contents("php://input"));

$fH = fopen("abir.txt", "w+");
fwrite($fH, file_get_contents("php://input"));
fclose($fH);
    $request_params = "";

    if (isset($input_data)) {
			$request_params = payson_encode($input_data);
    }

    $is_admin_path = true;
    $root_folder_path = "../";
    include_once ($root_folder_path . "includes/common.php");
    include_once ($root_folder_path . "includes/record.php");
    include_once ($root_folder_path . "includes/parameters.php");
    include_once ($root_folder_path . "includes/order_items.php");
    include_once ($root_folder_path . "includes/order_links.php");
    include_once ($root_folder_path . "includes/shopping_cart.php");
    include_once ($root_folder_path . "includes/date_functions.php");
    include_once ($root_folder_path . "messages/".$language_code."/cart_messages.php");


/* general debug data */
$admin_email = get_setting_value($settings, "admin_email", "");
$site_url = get_setting_value($settings, "site_url", "");
$mail_to = "enquiries@viart.com";
$mail_headers = "From: " . $admin_email;

/* debug email */
mail ($mail_to, $site_url . " payson_invoice116_ipn input", "Input data: $input_data", $mail_headers);

    // initialize template object to use in update_order_status() function
    $t = new VA_Template(".");

    // get IPN parameters
    $transaction_id = get_param("purchaseId"); //payson id 
    $type = get_param("type"); // INVOICE
    $status = get_param("invoiceStatus"); //PENDING     ORDERCREATED    CANCELED    SHIPPED    DONE    CREDITED
    $token = get_param("token"); // 2bb48fe3-7152-4de7-afe1-aa326479c927
    $HASH = get_param("HASH"); // c9193ad8bd929993c7b96256db668fce
    $order_id = get_param("trackingId");
    $currencyCode = get_param("currencyCode");

    // get payment parameters
    $payment_parameters = array(); $pass_parameters = array(); $post_parameters = ""; $pass_data = array(); $variables = array();
    get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

		$test_api = get_setting_value($payment_parameters, "test", false);
		if ($test_api) {
			//$api_user_id = get_setting_value($payment_parameters, "api_user_id", "1");
			//$api_pwd = get_setting_value($payment_parameters, "api_pwd", "fddb19ac-7470-42b6-a91d-072cb1495f0a");
			$api_user_id = get_setting_value($payment_parameters, "api_user_id", "4");
			$api_pwd = get_setting_value($payment_parameters, "api_pwd", "2acab30d-fe50-426f-90d7-8c60a7eb31d4");
			$reciever_email = get_setting_value($payment_parameters, "reciever_email", "testagent-1@payson.se");
			$validation_url = get_setting_value($payment_parameters, "validation_url", "https://test-api.payson.se/1.0/Validate/");
		} else {
			$api_user_id = get_setting_value($payment_parameters, "api_user_id", "");
			$api_pwd = get_setting_value($payment_parameters, "api_pwd", "");
			$reciever_email = get_setting_value($payment_parameters, "reciever_email", "");
			$validation_url = get_setting_value($payment_parameters, "validation_url", "https://api.payson.se/1.0/Validate/");
		}

    $sql  = " SELECT payment_id FROM " . $table_prefix . "orders WHERE order_id=" . $db->tosql($order_id, INTEGER);
    $payment_id = get_db_value($sql);
    $order_final = array();

    $setting_type = "order_final_" . $payment_id;
    $sql  = "SELECT setting_name, setting_value FROM " . $table_prefix . "global_settings";
    $sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
    $db->query($sql);
    while($db->next_record()) {
        $order_final[$db->f("setting_name")] = $db->f("setting_value");
    }
    $success_status_id = get_setting_value($order_final, "success_status_id", "");
    $failure_status_id = get_setting_value($order_final, "failure_status_id", "");
    $pending_status_id = get_setting_value($order_final, "pending_status_id", "");

    // get keys to calculate signature 

    //prepare ipn data for sending to payson

    //payson api headers
    $payson_headers = array();
    $payson_headers[] = 'PAYSON-SECURITY-USERID:   ' . $api_user_id;
    $payson_headers[] = 'PAYSON-SECURITY-PASSWORD: ' . $api_pwd;
    $payson_headers[] = 'PAYSON-APPLICATION-ID:    ' . null;
    $payson_headers[] = 'PAYSON-MODULE-INFO:       ' . "Viart payson 1.0";
    $payson_headers[] = 'Content-type:       ' . "application/x-www-form-urlencoded";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, $payson_headers);
    curl_setopt($ch, CURLOPT_URL, $validation_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $result = curl_exec($ch);
    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

/* debug */
mail ($mail_to, $site_url . " payson_invoice116_ipn curl", "Pass data: $request_params \n\nResult: $result \n\nRes code: $response_code" , $mail_headers);

    curl_close($ch);

    //check if data received is verified
    if ($response_code == 200 &&  $result === "VERIFIED") {


/* debug */
mail ($mail_to, $site_url . " payson_invoice116_ipn verified", "VERIFIED" , $mail_headers);

        // Signature is ok so we can proceed
        if (strtolower($status) == "ordercreated" || strtolower($status) == "pending") {
            if (strtolower($status) == "ordercreated"){
                $pending_message = "New order has been created in Payson. And will be shipped soon.";
            } else {
                $pending_message = "We've received confirmation for new order from Payson.";
            }

            // update order information
            $sql  = " UPDATE " . $table_prefix . "orders ";
            $sql .= " SET success_message=" . $db->tosql($status, TEXT);
            $sql .= ", error_message='', pending_message=" . $db->tosql($pending_message, TEXT);
            if ($transaction_id) {
                $sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT);
            }
            $sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
            $db->query($sql);
            // update order status
            if ($pending_status_id) {
                update_order_status($order_id, $pending_status_id, true, "", $status_error);
            }
        } else if (strtolower($status) == "done" && $type == "INVOICE") {
            // the order is payed. 
            // update order information
            $sql  = " UPDATE " . $table_prefix . "orders ";
            $sql .= " SET success_message=" . $db->tosql($status, TEXT);;
            $sql .= ", pending_message='', error_message='' ";
            if ($transaction_id) {
                $sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT);
            }
            $sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
            $db->query($sql);
            // update order status
            if ($success_status_id) {
                update_order_status($order_id, $success_status_id, true, "", $status_error);
            }
        } else if (strtolower($status) == "canceled") {
            $error_message = "Your transaction has been cancelled.";
            // update order information
            $sql  = " UPDATE " . $table_prefix . "orders ";
            $sql .= " SET success_message=" . $db->tosql($status, TEXT);
            $sql .= ", pending_message='' ";
            $sql .= ", error_message=" . $db->tosql($error_message, TEXT);
            if ($transaction_id) {
                $sql .= ", transaction_id=" . $db->tosql($transaction_id, TEXT);
            }
            $sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER);
            $db->query($sql);
            // update order status
            if ($failure_status_id) {
                update_order_status($order_id, $failure_status_id, true, "", $status_error);
            }
        } else {
            // unknown status returned
        }
    } else {
/* debug */
mail ($mail_to, $site_url . " payson115_ipn  curl", "FAILED" , $mail_headers);
		}


    function payson_decode($input) {
        $entries = explode("&", $input);
        $output = array();
        foreach ($entries as $entry) {
           // entry should look like 'key=urlencodedsvalue'
           $temp = explode("=", $entry, 2);
            if (isset($temp[1])) {
               $output[$temp[0]] = urldecode($temp[1]);
           } else {
               $output[$temp[0]] = null;
           }
       }
       return $output;
    }

    function payson_encode($input) {
        $output = "";
        $entries = array();
        foreach ($input as $key => $value) {
           $entries[$key] = sprintf("%s=%s", $key, urlencode($value));
        }
        return join("&", $entries);
    }


?>



