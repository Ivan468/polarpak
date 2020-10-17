<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  cybersource_functions.php                                ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


/*
 * Cybersource (www.cybersource.com) transaction handler functions by ViArt Ltd. (www.viart.com)
 */
	function get_cybersource_signature(&$data, $secret_key, $generate_data = false)
	{
		// pass sign as one of the parameter 
		if ($generate_data) {
			$data["signed_date_time"] = gmdate("Y-m-d\TH:i:s\Z");
			$data["unsigned_field_names"] = "";
			$data["signed_field_names"] = "";

			$signed_field_names = "";
			$unsigned_field_names = "";
			foreach ($data as $name => $value) {
				if (preg_match("/^card_/", $name)) {
					if ($unsigned_field_names) { $unsigned_field_names .= ","; }
					$unsigned_field_names .= $name;
					if ($name == "card_type") {
						$data["card_type"] = cybersource_cc_type($value);
					}
				} else {	
					if ($signed_field_names) { $signed_field_names .= ","; }
					$signed_field_names .= $name;
				}
			}
			// update values 
			$data["signed_field_names"] = $signed_field_names; 
			$data["unsigned_field_names"] = $unsigned_field_names; 
		}
		
		$signature_fields = explode(",", $data["signed_field_names"]);
		$signature_data = "";
		foreach ($signature_fields as $field_name) {
			if (isset($data[$field_name])) {
				$field_value = $data[$field_name];
			} else {
				$field_value = "";
				$data[$field_name] = "";
			}
			if ($signature_data) { $signature_data .= ","; }
			$signature_data .= $field_name."=".$field_value;
		}
  
		$signature = base64_encode(hash_hmac("sha256", $signature_data, $secret_key, true));
		if ($generate_data) {
			$data["signature"] = $signature;
		}
	}

	function cybersource_cc_type($cc_code, $inverse_match = false)
	{
		$cc_types = array(
			"VISA" => "001",
			"VISAELECTRON" => "033",
			"MC" => "002",
			"AMEX" => "003",
			"DISCOVER" => "004",
			"DINNERS" => "005",
			"CARTEBLANCHE" => "006",
			"JCB" => "007",
			"MAESTROUK" => "024",
			"MAESTROUK" => "042",
		);
		if ($inverse_match) {
			$cc_types = array_flip($cc_types);
		}
		$cc_type = "";
		$cc_code = strtoupper($cc_code);
		if ($cc_code && isset($cc_types[$cc_code])) {
			$cc_type = $cc_types[$cc_code];
		}
		return $cc_type;
	}

	function get_cybersource_error($error_code)
	{
		// 100 Successful transaction.
		$errors = array(
			"101" => "The request is missing one or more required fields.",
			"102" => "One or more fields in the request contains invalid data.",
			"104" => "The access_key and transaction_uuid fields for this authorization request matches with another request sent within 15 minutes.",
			"110" => "Only a partial amount was approved.",
			"150" => " General system failure. ",
			"151" => "The request was received but there was a server timeout. ",
			"152" => "The request was received, but a service did not finish running in time. ",
			"200" => "The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the Address Verification System (AVS) check.",
			"201" => "The issuing bank has questions about the request. ",
			"202" => "Expired card. ",
			"203" => "General decline of the card. ",
			"204" => "Insufficient funds in the account.",
			"205" => "Stolen or lost card.",
			"207" => "Issuing bank unavailable.",
			"208" => "Inactive card or card not authorized for card-not-present transactions.",
			"209" => "American Express Card Identification Digits (CID) did not match.",
			"210" => "The card has reached the credit limit. ",
			"211" => "Invalid card verification number or credit card date. ",
			"221" => "The customer matched an entry on the processor's negative file. ",
			"230" => "The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the CVN check.",
			"231" => "Invalid account number or wrong card type.",
			"232" => "The card type is not accepted by the payment processor.",
			"233" => "General decline by the processor.",
			"234" => "There is a problem with the information in your CyberSource account.",
			"235" => "The requested capture amount exceeds the originally authorized amount. ",
			"236" => "Processor failure. ",
			"237" => "The authorization has already been reversed.",
			"238" => "The authorization has already been captured.",
			"239" => "The requested transaction amount must match the previous transaction amount. ",
			"240" => "The card type sent is invalid or does not correlate with the credit card number.",
			"241" => "The request ID is invalid.",
			"242" => "You requested a capture, but there is no corresponding, unused authorization record. ",
			"243" => "The transaction has already been settled or reversed.",
			"246" => "You requested a void for a type of transaction that cannot be voided.",
			"247" => "You requested a credit for a capture that was previously voided.",
			"250" => "The request was received, but there was a timeout at the payment processor.",
		);

		if (isset($errors[$error_code])) {
			$error_message = $errors[$error_code];
		} else {
			$error_message = "Your order has been rejected.";
		}
		return $error_message;
	}



	function php_hmacsha1($data, $key) {
		$klen = strlen($key);
		$blen = 64;
		$ipad = str_pad("", $blen, chr(0x36));
		$opad = str_pad("", $blen, chr(0x5c));

		if ($klen <= $blen) {
			while (strlen($key) < $blen) {
				$key .= "\0";
			}				#zero-fill to blocksize
		} else {
			$key = cybs_sha1($key);	#if longer, pre-hash key
		}
		$key = str_pad($key, strlen($ipad) + strlen($data), "\0");
		return cybs_sha1(($key ^ $opad) . cybs_sha1($key ^ $ipad . $data));
	}

	# calculates SHA-1 digest of the input string
	# cleaned up from John Allen's "SHA in 8 lines of perl5"
	# at http://www.cypherspace.org/~adam/rsa/sha.html
	#
	# returns the hash in a (binary) string

	function cybs_sha1($in) {
		/*
		Due to problems with standard hash calculation.
		Visit this Cybersource Knowledge Base article for details.
		http://www.cybersource.com/cgi-bin/kb/kbanswersdetail.cgi?solution_id=1155&category=Known%20Issues&sub_category=All
		*/
		return pack("H*", sha1($in));
		//return mhash(MHASH_SHA1, $in);

		$indx = 0;
		$chunk = "";

		$A = array(1732584193, 4023233417, 2562383102,  271733878, 3285377520);
		$K = array(1518500249, 1859775393, 2400959708, 3395469782);
		$a = $b = $c = $d = $e = 0;
		$l = $p = $r = $t = 0;

		do{
			$chunk = substr($in, $l, 64);
			$r = strlen($chunk);
			$l += $r;

			if ($r<64 && !$p++) {
				$r++;
				$chunk .= "\x80";
			}
			$chunk .= "\0\0\0\0";
			while (strlen($chunk) % 4 > 0) {
				$chunk .= "\0";
			}
			$len = strlen($chunk) / 4;
			if ($len > 16) $len = 16;
			$fmt = "N" . $len;
			$W = array_values(unpack($fmt, $chunk));
			if ($r < 57 ) {
				while (count($W) < 15) {
					array_push($W, "\0");
				}
				$W[15] = $l*8;
			}

			for ($i = 16; $i <= 79; $i++) {
				$v1 = d($W, $i-3);
				$v2 = d($W, $i-8);
				$v3 = d($W, $i-14);
				$v4 = d($W, $i-16);
				array_push($W, L($v1 ^ $v2 ^ $v3 ^ $v4, 1));
			}

			list($a,$b,$c,$d,$e)=$A;

			for ($i = 0; $i<=79; $i++) {
				$t0 = 0;
				switch(intval($i/20)) {
					case 1:
					case 3:
						$t0 = F1($b, $c, $d);
						break;
					case 2:
						$t0 = F2($b, $c, $d);
						break;
					default:
						$t0 = F0($b, $c, $d);
						break;
				}
				$t = M($t0 + $e  + d($W, $i) + d($K, $i/20) + L($a, 5));
				$e = $d;
				$d = $c;
				$c = L($b,30);
				$b = $a;
				$a = $t;
			}

			$A[0] = M($A[0] + $a);
			$A[1] = M($A[1] + $b);
			$A[2] = M($A[2] + $c);
			$A[3] = M($A[3] + $d);
			$A[4] = M($A[4] + $e);

		}while ($r>56);
		$v = pack("N*", $A[0], $A[1], $A[2], $A[3], $A[4]);
		return $v;
	}

	#### Ancillary routines used by sha1

	function dd($x) {
		if (defined($x)) return $x;
		return 0;
	}

	function d($arr, $x) {
		if ($x < count($arr)) return $arr[$x];
		return 0;
	}

	function F0($b, $c, $d) {
		return $b & ($c ^ $d) ^ $d;
	}

	function F1($b, $c, $d) {
		return $b ^ $c ^ $d;
	}

	function F2($b, $c, $d) {
		return ($b | $c) & $d | $b & $c;
	}

	# ($num)
	function M($x) {
		$m = 1+~0;
		if ($m == 0) return $x;
		return($x - $m * intval($x/$m));
	}

	# ($string, $count)
	function L($x, $n) {
		return ( ($x<<$n) | ((pow(2, $n) - 1) & ($x>>(32-$n))) );
	}

	####
	#### end of HMAC SHA1 implementation #####


	####
	#### HOP functions
	#### Copyright 2003, CyberSource Corporation.  All rights reserved.
	####

	function getmicrotime() {
		list($usec, $sec) = explode(" ",microtime());
		$usec = (int)((float)$usec * 1000);
		while (strlen($usec) < 3) { $usec = "0" . $usec; }
		return $sec . $usec;
	}


	function hopHash($data, $key) {
		return base64_encode(php_hmacsha1($data, $key));
	}

	function VerifySignature($data, $signature, $pub) {
		$pub_digest = hopHash($data, $pub);
		return strcmp($pub_digest, $signature) == 0;
	}

	####
	#### end of HOP functions

	// begin ViArt functions for Cybersource

?>