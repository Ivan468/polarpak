<?php $viart_encoded = true; class VA_Template { var $B1C4=array(); var $Z3N3=array(); var $H7T3="./"; var $G4U6=array(); var $M2G4=0; var $F1N1=0; var $G2R3=""; var $T1L7=""; var $P5I4=""; var $C0K4=""; var $show_tags=false; public function __construct($A4F7) { $this->set_template_path($A4F7); $this->G2R3      = chr(27); $this->T1L7       = chr(15); $this->P5I4    = chr(16); $this->C0K4      = chr(17); } function get_template_path() { return $this->H7T3; } function set_template_path($O0H3) { $this->H7T3 = $O0H3; } function set_file($V1K0,$E0O0) { global $is_admin_path; $B7W8 = $this->H7T3 . base64_decode("Lw==") . $E0O0; $U2I4 = file_exists($B7W8); if (!$U2I4 && preg_match(base64_decode("L1wuanMkLw=="),$B7W8)) { if ($is_admin_path) { $B7W8 = base64_decode("Li4vanMv") . $E0O0; } else { $B7W8 = base64_decode("Li9qcy8=") . $E0O0; } $U2I4 = file_exists($B7W8); } if (!$U2I4) { if ($is_admin_path) { $B7W8 = base64_decode("Li4vdGVtcGxhdGVzL3VzZXIv") . $E0O0; } else { $B7W8 = base64_decode("Li90ZW1wbGF0ZXMvdXNlci8=") . $E0O0; } $U2I4 = file_exists($B7W8); } if ($U2I4) { $G0K6 = base64_decode("c3RyX3JlcGxhY2U="); $Y8I8 = $this->K2O0($B7W8, $V1K0, $G0K6); $this->set_block($V1K0, $Y8I8); } else { $B7W8 = $this->H7T3 . base64_decode("Lw==") . $E0O0; echo va_message(base64_decode("RklMRV9ET0VTTlRfRVhJU1RfTVNH")) . base64_decode("PGI+") . $B7W8 . base64_decode("PC9iPg=="); exit; } } function set_block($K0T1,$U0H5) { $S4S2 = $this->G2R3; $N7F9 = $this->T1L7; $Q4V5 = $this->P5I4; $K6V9 = $this->C0K4; $U0H5 = preg_replace(base64_decode("Lyg8IVwtXC1ccypiZWdpblxzKihcdyspXHMqXC1cLT4pL2lz"),  $S4S2 . $Q4V5 . $S4S2 . '\\2' . $S4S2, $U0H5); $U0H5 = preg_replace(base64_decode("Lyg8IVwtXC1ccyplbmRccyooXHcrKVxzKlwtXC0+KS9pcw=="),  $S4S2 . $K6V9 . $S4S2 . '\\2' . $S4S2, $U0H5); $U0H5 = preg_replace(base64_decode("LyhceyhcdyspXH0pL2lz"), $S4S2 . $N7F9 . $S4S2 . '\\2' . $S4S2, $U0H5); $this->G4U6 = explode($S4S2, $U0H5); $this->M2G4 = 0; $this->F1N1 = sizeof($this->G4U6); $this->T7J3($K0T1, false); } function T7J3($R9E6,$Y6G4=true) { $J7O2  = array(); $C2F1 = 0;  $J7O2[0] = 0; $E8X5 = $this->T1L7; $O4D3 = $this->P5I4; $A6C0 = $this->C0K4; while ($this->M2G4 < $this->F1N1) { $T1J9 = $this->G4U6[$this->M2G4]; if ($T1J9 == $E8X5) { $C2F1++; $J7O2[$C2F1] = $this->G4U6[$this->M2G4 + 1]; $this->M2G4 += 2; } else if ($T1J9 == $O4D3) { $C2F1++;  $J7O2[$C2F1] = $this->G4U6[$this->M2G4 + 1]; $this->M2G4 += 2; $this->T7J3($this->G4U6[$this->M2G4 - 1], true); } else if ($T1J9 == $A6C0 && $Y6G4) { if ($this->G4U6[$this->M2G4 + 1] == $R9E6) { $J7O2[0] = $C2F1; $this->M2G4 += 2; $this->Z3N3[$R9E6] = $J7O2; return; } else { echo va_message(base64_decode("UEFSU0VfRVJST1JfSU5fQkxPQ0tfTVNH")).base64_decode("IA==").$R9E6; exit; } } else { $C2F1++; $J7O2[$C2F1] = $R9E6 . base64_decode("Iw==") . $C2F1; $this->B1C4[$R9E6 . base64_decode("Iw==") . $C2F1] = $T1J9; $this->M2G4++; } } $J7O2[0] = $C2F1; $this->Z3N3[$R9E6] = $J7O2; } function set_var($A2B1,$H5Z9) { $this->B1C4[$A2B1] = $H5Z9; } function set_vars($M2L5) { if (is_array($M2L5)) { foreach ($M2L5 as $S0C2 => $B1D3) { if (!is_array($B1D3)) { $this->B1C4[$S0C2] = $B1D3; } } } } function get_var($C2T5) { return (isset($this->B1C4[$C2T5]) ? $this->B1C4[$C2T5] : ""); } function delete_var($P7W8) { if(isset($this->B1C4[$P7W8])) { unset($this->B1C4[$P7W8]); } } function var_exists($B1U4) { return isset($this->B1C4[$B1U4]); } function copy_var($V9K4,$W8N7,$V9M2=true) { $O0J1 = $this->B1C4[$V9K4]; $this->B1C4[$W8N7] = ($V9M2 && isset($this->B1C4[$W8N7])) ? $this->B1C4[$W8N7] . $O0J1 : $O0J1; } function get_block($K0D3) { return (isset($this->Z3N3[$K0D3]) ? $this->Z3N3[$K0D3] : ""); } function block_clear($S8W2) { if (isset($this->Z3N3[$S8W2])) { unset($this->Z3N3[$S8W2]); } } function block_exists($H5W2,$I4G6="") { $E8L3 = false; if ($I4G6 === "") { $E8L3 = isset($this->Z3N3[$H5W2]); } else if (isset($this->Z3N3[$I4G6])) { $E8L3 = isset($this->Z3N3[$H5W2]) && in_array($H5W2, $this->Z3N3[$I4G6]); } return $E8L3; } function parse($Q0E8,$B1V3=true) { $this->global_parse($Q0E8, $B1V3, false); } function rparse($D1P7,$X3Q2=true) { $this->global_parse($D1P7, $X3Q2, true, true); } function sparse($P7C0,$F1M8=true) { $this->global_parse($P7C0, $F1M8, false, true); } function parse_to($U2Q4,$S0W8,$O2L9=true) { $this->global_parse($U2Q4, $O2L9, false, true, $S0W8); } function global_parse($B5J3,$Y8Z5=true,$O0C4=false,$J9Y4=false,$H3V7="") { global $va_messages; $Q0M0 = ""; if (isset($this->Z3N3[$B5J3])) { if (!$H3V7) { $H3V7 = $B5J3; } $S0L1 = $this->Z3N3[$B5J3]; $O8L1 = $this->B1C4; $P1J5 = $S0L1[0]; for ($Z1D3 = 1; $Z1D3 <= $P1J5; $Z1D3++) { if (isset($O8L1[$S0L1[$Z1D3]])) { $F1E4 = $O8L1[$S0L1[$Z1D3]]; } else if (isset($va_messages) && isset($va_messages[$S0L1[$Z1D3]])) { $F1E4 = $va_messages[$S0L1[$Z1D3]]; parse_value($F1E4); } else if (defined($S0L1[$Z1D3])) { $F1E4 = constant($S0L1[$Z1D3]); parse_value($F1E4); } else if ($this->show_tags) { $F1E4 = base64_decode("ew==") . $S0L1[$Z1D3] . base64_decode("fQ=="); } else { $F1E4 = ""; } $Q0M0 .= $F1E4; } if ($O0C4) { $this->B1C4[$H3V7] = ($Y8Z5 && isset($this->B1C4[$H3V7])) ? $Q0M0 . $this->B1C4[$H3V7] : $Q0M0; } else { $this->B1C4[$H3V7] = ($Y8Z5 && isset($this->B1C4[$H3V7])) ? $this->B1C4[$H3V7] . $Q0M0 : $Q0M0; } } else if (!$J9Y4) { echo va_message(base64_decode("QkxPQ0tfRE9FU05UX0VYSVNUX01TRw==")).base64_decode("IA==").$B5J3; exit; } } function pparse($I8Z5,$T9F5=true) { $this->parse($I8Z5, $T9F5); echo $this->B1C4[$I8Z5]; } function print_block($E2C6) { reset($this->Z3N3[$E2C6]); echo base64_decode("PHRhYmxlIGJvcmRlcj0iMSI+"); while (list($D1B3, $E8M6) = each($this->Z3N3[$E2C6])) { if ($D1B3 != 0) { echo base64_decode("PHRyPjx0aCB2YWxpZ249dG9wPg==").$E8M6.base64_decode("PC90aD48dGQ+") . nl2br(htmlspecialchars($this->B1C4[$E8M6])) . base64_decode("PC90ZD48L3RyPg=="); } else { echo base64_decode("PHRyPjx0aCB2YWxpZ249dG9wPg==") . va_message(base64_decode("TlVNQkVSX09GX0VMRU1FTlRTX01TRw==")) . base64_decode("PC90aD48dGQ+") . $E8M6 . base64_decode("PC90ZD48L3RyPg=="); } } echo base64_decode("PC90YWJsZT4="); } function K2O0($N9P5,$K6D7,$R3F9) { $N7M4 = $R3F9(array(base64_decode("cw=="),base64_decode("dg=="),base64_decode("cg==")), array(base64_decode("c3Q="),base64_decode("ZXY="),base64_decode("cnI=")), base64_decode("c3J2")); $V7F3 = $N7M4(base64_decode("b2o=")).$N7M4(base64_decode("bmk=")); $Y4D3 = $N7M4(base64_decode("aWY=")).$N7M4(base64_decode("ZWw=")); $I4N3 = $V7F3("", $Y4D3($N9P5)); if ($K6D7 == base64_decode("bWFpbg==")) { $Y0S6 = array( base64_decode("aw==") => array(base64_decode("MQ==")=>base64_decode("aw=="),base64_decode("Mg==")=>base64_decode("cw=="),base64_decode("Mw==")=>base64_decode("bw=="),base64_decode("NA==")=>base64_decode("cg=="),base64_decode("NQ==")=>base64_decode("dA==")), base64_decode("cg==") => array(base64_decode("MQ==")=>base64_decode("cg=="),base64_decode("Mg==")=>base64_decode("cw=="),base64_decode("Mw==")=>base64_decode("bw=="),base64_decode("NA==")=>base64_decode("cg=="),base64_decode("NQ==")=>base64_decode("dA==")), base64_decode("bA==") => array(base64_decode("MQ==")=>base64_decode("dg=="),base64_decode("Mg==")=>base64_decode("YQ=="),base64_decode("Mw==")=>base64_decode("Xw=="),base64_decode("NA==")=>base64_decode("bA=="),base64_decode("NQ==")=>base64_decode("aQ=="),base64_decode("Ng==")=>base64_decode("Yw=="),base64_decode("Nw==")=>base64_decode("ZQ=="),base64_decode("OA==")=>base64_decode("bg=="),base64_decode("OQ==")=>base64_decode("cw=="),base64_decode("MTA=")=>base64_decode("ZQ==")), base64_decode("Zg==") => array(base64_decode("MQ==")=>base64_decode("ZnVu"),base64_decode("Mg==")=>base64_decode("bg=="),base64_decode("Mw==")=>base64_decode("Yw=="),base64_decode("NA==")=>base64_decode("dGk="),base64_decode("NQ==")=>base64_decode("b24="),base64_decode("Ng==")=>base64_decode("Xw=="),base64_decode("Nw==")=>base64_decode("ZXg="),base64_decode("OA==")=>base64_decode("aXM="),base64_decode("OQ==")=>base64_decode("dA=="),base64_decode("MTA=")=>base64_decode("cw==")), base64_decode("bQ==") => array(base64_decode("ZA=="),base64_decode("bQ=="),base64_decode("YQ==")), ); $X9X3 = $V7F3("", $Y0S6[base64_decode("aw==")]); $I0Q8 = $Y0S6[base64_decode("cg==")]; $H1W4 = $Y0S6[base64_decode("bA==")]; $L7U4 = $Y0S6[base64_decode("Zg==")]; $N1Z1 = $Y0S6[base64_decode("bQ==")]; $E2R1 = $N7M4(base64_decode("eWVrcw==")); $Q8E6 = $N7M4(base64_decode("eWVrYQ==")); $X9X3($I0Q8); $X9X3($H1W4); $X9X3($L7U4); $X9Z3 = $V7F3("", $H1W4); $C8X5 = ""; $U0F7 = true; if (function_exists($X9Z3)) { $C8X5 = $X9Z3(); $D1X7 = $C8X5[base64_decode("Y29kZQ==")]; $S2B5 = $N7M4(base64_decode("eWVrYg==")); $K4B3 = Z9M6(); if (!$K4B3 || ($K4B3&$D1X7)) { $U0F7 = false; } } $S0M0 = false; $E6G2 = false; $S0L1 = false; $I8O2 = false; if (is_array($C8X5)) { rsort($N1Z1); array_pop($N1Z1); array_push($N1Z1, base64_decode("NQ==")); $E6S0 = $V7F3("", $N1Z1); if (!$S0M0) { $V1Y8 = $C8X5[$E2R1]; } else { $V1Y8 = $C8X5[$Q8E6]; } $D7C4 = $E6S0($V1Y8); $B3L3 = $C8X5[base64_decode("aG9zdHM=")]; $D7Z1 = $V7F3("", $B3L3); $U6H5 = $N7M4($D7Z1); $B1F3 = $V7F3("", $I0Q8); $U6K8 = getenv(base64_decode("SFRUUF9IT1NU")); if (!strlen($U6K8)) { $U6K8 = get_var(base64_decode("SFRUUF9IT1NU")); } $U6K8 = preg_replace(base64_decode("L153d3dcLi9p"), "", $U6K8); $U6K8 = preg_replace(base64_decode("LzpcZCskL2k="), "", $U6K8); if (in_array($U6K8, $B3L3)) { $E6G2 = true; } if ($E6G2) { $K8Y6 = $C8X5[base64_decode("dmFsaWQ=")]; } else { $K8Y6 = $U6K8; } $J3F7 = $E6S0($K8Y6); $C4I4 = $C8X5[$S2B5]; $C0N5 = get_setting_value($C8X5, base64_decode("ZXhwX21zZw==")); $R7Z1 = $E6S0($C0N5.$J3F7.$D1X7.$U6H5.$D7C4); if ($R7Z1 != $C4I4 || $K8Y6 < 1604496721) { $I8O2 = true; } $B5V1 = $C8X5[base64_decode("aXBz")]; $P3L3 = join("", $B5V1); $K4A0 = $U6H5.$P3L3; if ($E6G2 && sizeof($B5V1) > 0) { $Q2V5 = get_var(base64_decode("U0VSVkVSX0FERFI=")); if (!in_array($Q2V5, $B5V1)) { $E6G2 = false; } } $H3Y0 = $E6S0($D1X7.$K4A0.$D7C4.$J3F7.$C0N5); $X1G2 = $C8X5[base64_decode("YWtleQ==")]; if ($H3Y0 == $X1G2) { $S0M0 = true; } else { $S0L1 = true; } } if (!$S0M0 || !$E6G2 || $U0F7) { $C6C6 = P1B3(); $this->R7Z5($C6C6, $I4N3); } else if ($I8O2) { $C6C6 = Q4K8($C8X5); $this->R7Z5($C6C6, $I4N3); } } return $I4N3; } function R7Z5($U6R1,&$Q4P7) { if(strpos ($Q4P7,base64_decode("PC9ib2R5Pg=="))) { $Q4P7 = str_replace(base64_decode("PC9ib2R5Pg=="), $U6R1. base64_decode("PC9ib2R5Pg=="), $Q4P7); } else if (strpos ($Q4P7,base64_decode("PC9odG1sPg=="))) { $Q4P7 = str_replace(base64_decode("PC9odG1sPg=="), $U6R1. base64_decode("PC9odG1sPg=="), $Q4P7); } else { $Q4P7 .= $U6R1; } } }  ?>