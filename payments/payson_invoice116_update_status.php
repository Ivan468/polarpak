<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  payson_invoice116_update_status.php                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 *payson order status update
 */

    ini_set("display_errors", "1");

    error_reporting(E_ALL & ~E_STRICT);

    $is_admin_path = true;

    $root_folder_path = "../";

    include_once ($root_folder_path . "includes/common.php");

    include_once ($root_folder_path . "includes/record.php");

    include_once ($root_folder_path . "includes/parameters.php");

    include_once ($root_folder_path . "includes/order_items.php");

    include_once ($root_folder_path . "includes/order_links.php");

    include_once ($root_folder_path . "includes/shopping_cart.php");

    include_once ($root_folder_path . "includes/date_functions.php");


    $payment_parameters = array();

    $pass_parameters = array();

    $post_parameters = '';

    $pass_data = array();

    $variables = array();

    $inputs_array = array();

    $order_amount = 0;
    
    get_payment_parameters($order_id, $payment_parameters, $pass_parameters, $post_parameters, $pass_data, $variables);

    $token = get_db_value("SELECT authorization_code FROM " . $table_prefix . "orders WHERE order_id = ". $order_id);

    $p_data["token"] = $token;
    $p_data["action"] = "SHIPORDER";

    //payson api headers
    $payson_headers = array();

    $payson_headers[] = 'PAYSON-SECURITY-USERID:   ' . $payment_parameters["api_user_id"];

    $payson_headers[] = 'PAYSON-SECURITY-PASSWORD: ' . $payment_parameters["api_pwd"];

    $payson_headers[] = 'PAYSON-APPLICATION-ID:    ' . null;

    $payson_headers[] = 'PAYSON-MODULE-INFO:       ' . "Viart payson 1.0";

    $payson_headers[] = 'Content-type:       ' . "application/x-www-form-urlencoded";

    $p_data = http_build_query($p_data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, $payson_headers);
    curl_setopt($ch, CURLOPT_URL, "https://api.payson.se/1.0/PaymentUpdate/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $p_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $result = curl_exec($ch);


    if ($result === false) {
        die('Curl error: ' . curl_error($ch));
    }

    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    parse_str($result, $response_array);

    curl_close($ch);
    if ($response_code == 200 &&  $response_array["responseEnvelope_ack"] === "SUCCESS") {

    }
    else{
         $error_message = $result;
    }


$fH = fopen("abir22.txt", "w+");
fwrite($fH, "Current Token: " . $token);
fclose($fH);

?>