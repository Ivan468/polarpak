<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  protx_server_functions.php                               ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * SagePay VSP (www.sagepay.com) transaction handler by www.viart.com
 */

	function protx_vsp_get_associative_array($separator, $input)
	{
		for ($i=0; $i < count($input); $i++) {
			$splitAt = strpos($input[$i], $separator);
			$output[trim(substr($input[$i], 0, $splitAt))] = trim(substr($input[$i], ($splitAt+1)));
		}
		return $output;
	}

    function protx_vsp_set_order_message($order_id, $db_field, $input_message){
    	global $db, $table_prefix;
		$sql  = " UPDATE " . $table_prefix . "orders ";
		$sql .= " SET ".$db_field."=" . $db->tosql($input_message, TEXT) ;
		$sql .= " WHERE order_id=" . $db->tosql($order_id, INTEGER) ;
		$db->query($sql);
	}

?>