<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  kreditor_functions.php                                   ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/

/*
 * Kreditor (http://kreditor.se/) transaction handler by www.viart.com
 */

	function kreditor_settype_integer($x) {
		if (is_double($x)) {
			$x = round($x)+(($x>0)?0.00000001:-0.00000001);
		} else if (is_float($x)) {
			$x = round($x)+(($x>0)?0.00000001:-0.00000001);
		} else if (is_string($x)) {
			$x = preg_replace("/[ \n\r\t\e]/", "", $x);
		}
		settype($x, "integer");
		return $x;
	}

	function kreditor_settype_string($x){
		settype($x, "string");
		$res = "";
		$length = strlen($x);
		for($i = 0; $i < $length; $i++){
			if($x[$i] >= " "){
				$res .= $x[$i];
			}else{
				$res .= " ";
			}
		}
		$x = $res;
		return $x;
	}

?>