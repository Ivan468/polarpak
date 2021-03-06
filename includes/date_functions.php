<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  date_functions.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$date_formats = array("YYYY", "MMMM", "MMM", "GMT", "GTF", "YY", "MM", "DD", "HH", "hh",
			"mm", "ss", "AM", "am", "M", "D", "H", "h", "m", "s", "WWWW", "WWW", 
			"u6", "u5", "u4", "u3", "u2", "u1", "u");

	$months = array(
		array(1,  va_message("JANUARY")),
		array(2,  va_message("FEBRUARY")),
		array(3,  va_message("MARCH")),
		array(4,  va_message("APRIL")),
		array(5,  va_message("MAY")),
		array(6,  va_message("JUNE")),
		array(7,  va_message("JULY")),
		array(8,  va_message("AUGUST")),
		array(9,  va_message("SEPTEMBER")),
		array(10, va_message("OCTOBER")),
		array(11, va_message("NOVEMBER")),
		array(12, va_message("DECEMBER")),
	);

	$short_months = array(
		array(1,  va_message("JANUARY_SHORT")),
		array(2,  va_message("FEBRUARY_SHORT")),
		array(3,  va_message("MARCH_SHORT")),
		array(4,  va_message("APRIL_SHORT")),
		array(5,  va_message("MAY_SHORT")),
		array(6,  va_message("JUNE_SHORT")),
		array(7,  va_message("JULY_SHORT")),
		array(8,  va_message("AUGUST_SHORT")),
		array(9,  va_message("SEPTEMBER_SHORT")),
		array(10, va_message("OCTOBER_SHORT")),
		array(11, va_message("NOVEMBER_SHORT")),
		array(12, va_message("DECEMBER_SHORT")),
	);

	$weekdays = array(
		array(1, va_message("SUNDAY")),
		array(2, va_message("MONDAY")),
		array(3, va_message("TUESDAY")),
		array(4, va_message("WEDNESDAY")),
		array(5, va_message("THURSDAY")),
		array(6, va_message("FRIDAY")),
		array(7, va_message("SATURDAY")),
	);

	$short_weekdays = array(
		array(1, va_message("SUNDAY_SHORT")),
		array(2, va_message("MONDAY_SHORT")),
		array(3, va_message("TUESDAY_SHORT")),
		array(4, va_message("WEDNESDAY_SHORT")),
		array(5, va_message("THURSDAY_SHORT")),
		array(6, va_message("FRIDAY_SHORT")),
		array(7, va_message("SATURDAY_SHORT")),
	);
	
	function va_time($timestamp = "")
	{
		global $va_time_shift;
		$time_shift = (isset($va_time_shift)) ? $va_time_shift : 0;
		if(!$timestamp) { $timestamp = time() + $time_shift; }
		return array(date("Y", $timestamp), date("m", $timestamp), date("d", $timestamp), date("H", $timestamp), date("i", $timestamp), date("s", $timestamp));
	}

	function va_timestamp($date_array = "")
	{
		global $va_time_shift;
		if (is_array($date_array)) {
			$timestamp = mktime ($date_array[HOUR], $date_array[MINUTE], $date_array[SECOND], $date_array[MONTH], $date_array[DAY], $date_array[YEAR]);
		}	else {
			$time_shift = (isset($va_time_shift)) ? $va_time_shift : 0;
			$timestamp = time() + $time_shift;
		}
		return $timestamp;
	}

	function get_ampmhour($date_array)
	{
		$hour = intval($date_array[HOUR]);
		if($hour > 12)
			$hour -= 12;
		else if($hour == 0)
			$hour = 12;
		if(strlen($hour) == 1) $hour = "0" . $hour;
		return $hour;
	}

	function get_ampm($date_array)
	{
		$hour = intval($date_array[HOUR]);
		if($hour >= 12 && $hour <= 23)
			$ampm = "PM";
		else
			$ampm = "AM";

		return $ampm;
	}

	function set_hour($date_array)
	{
		if(isset($date_array[AMPMHOUR]) && isset($date_array[AMPM]))
			if(strtoupper($date_array[AMPM]) == "AM" && $date_array[AMPMHOUR] == 12)
				$date_array[HOUR] = 0;
			else if(strtoupper($date_array[AMPM]) == "PM" && $date_array[AMPMHOUR] != 12)
				$date_array[HOUR] = 12 + intval($date_array[AMPMHOUR]);
			else
				$date_array[HOUR] = $date_array[AMPMHOUR];
		else if(isset($date_array[AMPMHOUR]))
			$date_array[HOUR] = $date_array[AMPMHOUR];
		else
			$date_array[HOUR] = "00";

		return $date_array;
	}

	function set_month($date_array)
	{
		global $months;
		global $short_months;

		if(isset($date_array[FULLMONTH]))
			$date_array[MONTH] = get_array_id($date_array[FULLMONTH], $months);
		else if(isset($date_array[SHORTMONTH]))
			$date_array[MONTH] = get_array_id($date_array[SHORTMONTH], $short_months);
		else
			$date_array[MONTH] = "01";

		return $date_array;
	}

	function set_year($date_array)
	{
		if(isset($date_array[SHORTYEAR]))
			if($date_array[SHORTYEAR] >= 70 && $date_array[SHORTYEAR] <= 99)
				$date_array[YEAR] = "19" . $date_array[SHORTYEAR];
			else
				$date_array[YEAR] = "20" . $date_array[SHORTYEAR];
		else 
			$date_array[YEAR] = "1970";

		return $date_array;
	}

	function get_yearweek($date)
	{
		$start_year = mktime(0,0,0, 1, 1, $date[YEAR]);
		$end_year = mktime(0,0,0, 12, 31, $date[YEAR]);
		$week_date = mktime(0,0,0, $date[MONTH], $date[DAY], $date[YEAR]);
		
		$year_day = date("z", $week_date);
		$start_week_day = date("w", $start_year);
		if (!$start_week_day) { $start_week_day = 7; }
		$end_week_day = date("w", $end_year);
  
		if ($year_day < 3 && $start_week_day >= 5) {
			$prev_year = va_time(mktime(0,0,0, 12, 31, $date[YEAR] - 1));
			$yearweek = get_yearweek($prev_year);
		} else if ($date[MONTH] == 12 && $date[DAY] > 28 && $end_week_day > 0 && $end_week_day < 4) {
			$yearweek = (($date[YEAR] + 1) * 100) + 1;
		} else if ($start_week_day >= 5) {
			$yearweek = ($date[YEAR] * 100) + ceil(($year_day - 7 + $start_week_day) / 7);
		} else {
			$yearweek = ($date[YEAR] * 100) + ceil(($year_day + $start_week_day) / 7);
		}
  
		return $yearweek;
	}

	function va_gmt_zone($date) {
		$gmt_zone = 0;
		if (isset($date[GTF])) {
			$gtf = $date[GTF];
			if (preg_match("/^([\-\+])(\d{2})\:(\d{2})$/", $gtf, $matches)) {
				$gmt_sign = $matches[1];
				$gmt_hours = $matches[2];
				$gmt_minutes = $matches[3];
				$gmt_zone = intval($gmt_hours) * 3600 + intval($gmt_minutes) * 60;
				if ($gmt_sign == "-") { $gmt_zone = -$gmt_zone; }
			}
		}
		return $gmt_zone;
	}


	function va_date($mask = "", $date = "")
	{
		global $months;
		global $short_months;
		global $weekdays;
		global $short_weekdays;
	
		$formated_date = "";

		if (!is_array($date)) { $date = is_numeric($date) ? va_time($date) : va_time(); }
		if (!is_array($mask)) { $mask = parse_date_format($mask); }
		
		if(is_array($mask))
		{
	    for($i = 0; $i < sizeof($mask); $i++)
  	  {
        switch ($mask[$i])
        {
					case "YYYY":
						$formated_date .= $date[YEAR]; break;
					case "YY":
						$formated_date .= substr($date[YEAR], 2); break;
					case "WWWW":
						$formated_date .= $weekdays[intval(date("w",va_timestamp($date)))][1]; break;
					case "WWW":
						$formated_date .= $short_weekdays[intval(date("w",va_timestamp($date)))][1]; break;
					case "MMMM":
						$formated_date .= $months[intval($date[MONTH]) - 1][1]; break;
					case "MMM":
						$formated_date .= $short_months[intval($date[MONTH]) - 1][1]; break;
					case "MM":
						$formated_date .= (strlen($date[MONTH]) == 2) ? $date[MONTH] : "0" . $date[MONTH]; break;
					case "M":
						$formated_date .= intval($date[MONTH]); break;
					case "DD":
						$formated_date .= (strlen($date[DAY]) == 2) ? $date[DAY] : "0" . $date[DAY]; break;
					case "D":
						$formated_date .= intval($date[DAY]); break;
					case "HH":
						$formated_date .= (strlen($date[HOUR]) == 2) ? $date[HOUR] : "0" . $date[HOUR]; break;
					case "H":
						$formated_date .= intval($date[HOUR]); break;
					case "hh":
						$formated_date .= (get_ampmhour($date) == 2) ? get_ampmhour($date) : "0" . get_ampmhour($date); break;
					case "h":
						$formated_date .= intval(get_ampmhour($date)); break;
					case "mm":
						$formated_date .= (strlen($date[MINUTE]) == 2) ? $date[MINUTE] : "0" . $date[MINUTE]; break;
					case "m":
						$formated_date .= intval($date[MINUTE]); break;
					case "ss":
						$formated_date .= (strlen($date[SECOND]) == 2) ? $date[SECOND] : "0" . $date[SECOND]; break;
					case "s":
						$formated_date .= intval($date[SECOND]); break;
					case "AM":
						$formated_date .= get_ampm($date); break;
					case "am":
						$formated_date .= strtolower(get_ampm($date)); break;
					case "GMT":
						$formated_date .= isset($date[GMT]) ? $date[GMT] : ""; break;
					case "GTF":
						$formated_date .= isset($date[GTF]) ? $date[GTF] : ""; break;
					case "u":
					case "u1":
					case "u2":
					case "u3":
					case "u4":
					case "u5":
					case "u6":
						$formated_date .= isset($date[MICROSECOND]) ? $date[MICROSECOND] : ""; break;
          default:
						$formated_date .= stripslashes($mask[$i]);
				}
			}
		}
		else
		{
			$formated_date = $date[YEAR]."-".$date[MONTH]."-".$date[DAY]." ".$date[HOUR].":".$date[MINUTE].":".$date[SECOND];
		}
		return $formated_date;
	}

	function parse_date_format($mask_string)
	{
		global $date_formats;

		$total_formats = sizeof($date_formats);
		$mask = array();
		$chars = ""; $date_format = "";
		while(strlen($mask_string) > 0) {
			$first_char = substr($mask_string, 0, 1);
			if($first_char == "\\") {
				$chars .= substr($mask_string, 0, 1);
				$mask_string = substr($mask_string, 1);
			} else {
				for($i = 0; $i < $total_formats; $i++) {
	  			if(preg_match("/^" . $date_formats[$i] . "/", $mask_string)) {
						$date_format = $date_formats[$i];
						$mask_string = substr($mask_string, strlen($date_format));
						break;
					}
				}
			}
			if($date_format == "") {
				$chars .= substr($mask_string, 0, 1);
				$mask_string = substr($mask_string, 1);
			} else {
				if(strlen($chars)) {
					$mask[] = $chars;
					$chars = "";
				}
				$mask[] = $date_format;
				$date_format = "";
			}

		}
		if(strlen($chars)) {
			$mask[] = $chars;
			$chars = "";
		}

		return $mask;
	}

	function parse_date($date_string, $date_mask, &$date_errors, $control_name = "")
	{
		global $months;
		global $short_months;
		global $weekdays;
		global $short_weekdays;
		global $datetime_edit_format;

		if (is_array($date_string) || !strlen($date_string)) {
			return $date_string;
		}
		$date_string = trim($date_string);
    
		if (!is_array($date_mask)) { $date_mask = parse_date_format($date_mask); }

		$result = "";
		$reg_exp = "";
		$reg_exps = array(
				"YYYY" => "(\d{4})", "YY" => "(\d{2})",
				"MMMM" => build_date_regexp($months), "MMM" => build_date_regexp($short_months),
				"WWWW" => build_date_regexp($weekdays), "WWW" => build_date_regexp($short_weekdays),
				"MM" => "(\d{2})", "M" => "(\d{1,2})",
				"DD" => "(\d{2})", "D" => "(\d{1,2})",
				"HH" => "(\d{2})", "H" => "(\d{1,2})",
				"hh" => "(\d{2})", "h" => "(\d{1,2})",
				"mm" => "(\d{2})", "m" => "(\d{1,2})",
				"ss" => "(\d{2})", "s" => "(\d{1,2})",
				"u6" => "(\d{6})", "u5" => "(\d{5})", "u4" => "(\d{4})", 
				"u3" => "(\d{3})", "u2" => "(\d{2})", "u1" => "(\d{1})", "u" => "(\d{1,6})",
				"AM" => "(AM|PM)", "am" => "(am|pm)",
				"GMT" => "([\+\-]\d{2,4})?",
				"GTF" => "([\+\-]\d{2}\:\d{2})?",
			);
		$indexes = array(
				"YYYY" => YEAR, "YY" => SHORTYEAR,
				"MMMM" => FULLMONTH, "MMM" => SHORTMONTH,
				"MM" => MONTH, "M" => MONTH,
				"DD" => DAY, "D" => DAY,
				"HH" => HOUR, "H" => HOUR,
				"hh" => AMPMHOUR, "h" => AMPMHOUR,
				"mm" => MINUTE, "m" => MINUTE,
				"ss" => SECOND, "s" => SECOND,
				"u6" => MICROSECOND, "u5" => MICROSECOND, "u4" => MICROSECOND, "u3" => MICROSECOND,
				"u2" => MICROSECOND, "u1" => MICROSECOND, "u" => MICROSECOND, 
				"AM" => AMPM, "am" => AMPM,
				"GMT" => GMT, "GTF" => GTF,
			);
		$matches_indexes = array();
		$matches_number = 0;
    for($i = 0; $i < sizeof($date_mask); $i++)
 	  {
			if(isset($reg_exps[$date_mask[$i]])) {
				$matches_number++;
				$reg_exp .= $reg_exps[$date_mask[$i]];
				$matches_indexes[$matches_number] = isset($indexes[$date_mask[$i]]) ? $indexes[$date_mask[$i]] : "";
			} else {
				$reg_exp .= prepare_regexp($date_mask[$i]);
			}
		}
		$reg_exp = str_replace(" ", "\\s+", $reg_exp);
		$reg_exp = "/^" . $reg_exp . "\$/i";
		if(preg_match($reg_exp, $date_string, $matches))
		{
			for($i = 1; $i <= $matches_number; $i++)
				if (isset($matches[$i])) $date_value[$matches_indexes[$i]] = $matches[$i];
			if(!isset($date_value[YEAR]))
				$date_value = set_year($date_value);
			if(!isset($date_value[MONTH]))
				$date_value = set_month($date_value);
			if(!isset($date_value[DAY]))
				$date_value[DAY] = "01";
			if(!isset($date_value[HOUR]))
				$date_value = set_hour($date_value);
			if(!isset($date_value[MINUTE]))
				$date_value[MINUTE] = "00";
			if(!isset($date_value[SECOND]))
				$date_value[SECOND] = "00";

			if (checkdate($date_value[MONTH], $date_value[DAY], $date_value[YEAR])) {
				$result = $date_value;
			} else if ($date_value[MONTH] != 0 && $date_value[DAY] != 0 && $date_value[YEAR] != 0) {
				if (!strlen($control_name)) { $control_name = $date_string; }
				$date_errors = str_replace("{field_name}", $control_name, INCORRECT_DATE_MESSAGE);
			}
		}
		else
		{
			if (!strlen($control_name)) { $control_name = $date_string; }
			$date_errors = str_replace("{field_name}", $control_name, INCORRECT_MASK_MESSAGE);
			$date_errors = str_replace("{field_mask}", join("", $date_mask), $date_errors);
		}

		return $result;
	}	

	function build_date_regexp($dates_array)
	{
		$reg_exp = "";
		for($i = 0; $i < sizeof($dates_array); $i++)
		{
			if($i != 0) $reg_exp .= "|";
			$reg_exp .= $dates_array[$i][1];
		}
		$reg_exp = "(" . $reg_exp . ")";
		return $reg_exp;
	}

?>