<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_graph.php                                          ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	$width_graph = 180;
	$height_graph = 180;
	$margin_y = 20;
	$margin_x = 20;
	$margin_in_y = 10;
	$show_days = 31;

	include_once("./admin_config.php");
	include_once($root_folder_path . "includes/common.php");
	include_once("./admin_common.php");

	$tab = get_param("tab");
	$font = $root_folder_path . "includes/font/comic.ttf";

	if (!strlen(get_session("session_admin_id")) || !strlen(get_session("session_admin_privilege_id"))) {

		$img = imagecreate($width_graph, $height_graph);
		$bgColor = ImageColorAllocate($img, 245, 245, 245); // цвет фона
		$textColor = ImageColorAllocate($img, 255, 0, 56); // цвет осей
		ImageFill($img, 0, 0, $bgColor);
		imagettftext($img, 30, 0, 37, 70, $textColor, $font, "Error");
		imagettftext($img, 15, 0, 37, 120, $textColor, $font, "Please login");
		header("Content-type: image/png");
		ImagePng($img);
		ImageDestroy($img);
		exit;
	}

	$values_max = 0;

	$current_date = va_time();
	$cyear = $current_date[YEAR]; $cmonth = $current_date[MONTH]; $cday = $current_date[DAY]; 
	for ($i = $show_days - 1; $i >= 0; $i--) {$dates_all[] = mktime (0, 0, 0, $cmonth, $cday - $i, $cyear);}
	$dates_all[] = mktime (0, 0, 0, $cmonth, $cday + 1, $cyear);

	if ($tab == 2) {
		for ($i = 0; $i < $show_days; $i++) {
			$data = array();
			if ($i != $show_days - 1) {
				$data = get_cache(24,1,"counts_orders","date",date("Y-m-d",$dates_all[$i]));
			}
			if (is_array($data)) {
				$sql = " SELECT count(o.order_id) ";
				$sql.= " FROM ".$table_prefix."orders o ";
				//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
				$sql.= " WHERE o.order_placed_date >= ".$db->tosql($dates_all[$i], DATE)." AND o.order_placed_date < ".$db->tosql($dates_all[$i+1], DATE);
				$data2 = get_db_value($sql);
				if (!$data2) $data2 = 0;
				if ($i != $show_days - 1) {
					$data = set_cache($data2,$data[0],"counts_orders","date",date("Y-m-d",$dates_all[$i]));
				} else {
					$data = $data2;
				}
			}
			$mas[$i] = $data;
			if ($mas[$i] > $values_max) $values_max = $mas[$i];
		}
	} else if ($tab == 3) {
		for ($i = 0; $i < $show_days; $i++) {
			$data = array();
			if ($i != $show_days - 1) {
				$data = get_cache(24,1,"counts_sales","date",date("Y-m-d",$dates_all[$i]));
			}
			if (is_array($data)) {
				$sql = " SELECT sum(o.order_total) ";
				$sql.= " FROM ".$table_prefix."orders o ";
				//$sql.= " INNER JOIN ".$table_prefix."order_statuses os ON os.paid_status = 1 AND os.status_id = o.order_status ";
				$sql.= " WHERE o.order_placed_date >= ".$db->tosql($dates_all[$i], DATE)." AND o.order_placed_date < ".$db->tosql($dates_all[$i+1], DATE);
				$sales = get_db_value($sql);
				if (!$sales) {$sales = 0;}
				if ($i != $show_days - 1) {
					$data = set_cache($sales,$data[0],"counts_sales","date",date("Y-m-d",$dates_all[$i]));
				} else {
					$data = $sales;
				}
			}
			$mas[$i] = $data;
			if ($mas[$i] > $values_max) $values_max = $mas[$i];
		}
	} else if ($tab == 4) {
		for ($i = 0; $i < $show_days; $i++) {
			$data = array();
			if ($i != $show_days - 1) {
				$data = get_cache(24,1,"counts_visits","date",date("Y-m-d",$dates_all[$i]));
			}
			if (is_array($data)) {
				$sql = " SELECT count(visit_id) FROM ".$table_prefix."tracking_visits ";
				$sql.= " WHERE date_added >= ".$db->tosql($dates_all[$i], DATE)." AND date_added < ".$db->tosql($dates_all[$i+1], DATE);
				$data2 = get_db_value($sql);
				if (!$data2) $data2 = 0;
				if ($i != $show_days - 1) {
					$data = set_cache($data2,$data[0],"counts_visits","date",date("Y-m-d",$dates_all[$i]));
				} else {
					$data = $data2;
				}
			}
			$mas[$i] = $data;
			if ($mas[$i] > $values_max) $values_max = $mas[$i];
		}
		
	} else {
		for ($i = 0; $i < $show_days; $i++) {
			$data = array();
			if ($i != $show_days - 1) {
				$data = get_cache(24,1,"counts_users","date",date("Y-m-d",$dates_all[$i]));
			}
			if (is_array($data)) {
				$sql = " SELECT count(user_id) FROM ".$table_prefix."users ";
				$sql.= " WHERE registration_date > ".$db->tosql($dates_all[$i], DATE)." AND registration_date < ".$db->tosql($dates_all[$i+1], DATE)." AND is_approved = 1";
				$data2 = get_db_value($sql);
				if (!$data2) $data2 = 0;
				if ($i != $show_days - 1) {
					$data = set_cache($data2,$data[0],"counts_users","date",date("Y-m-d",$dates_all[$i]));
				} else {
					$data = $data2;
				}
			}
			$mas[$i] = $data;
			if ($mas[$i] > $values_max) $values_max = $mas[$i];
		}
	}

	if ($values_max > 1000) {
		$margin_x = 40;
		$width_graph = 160;
	} else if ($values_max > 10000) {
		$margin_x = 60;
		$width_graph = 140;
	} else if ($values_max == 0) {
		$values_max = 5;
	}

	$img = imagecreate($width_graph + $margin_x, $height_graph + $margin_y);

	$bgColor = ImageColorAllocate($img, 245, 245, 245);
	$lnColor = ImageColorAllocate($img, 230, 230, 230);
	$margindColor = ImageColorAllocate($img, 0, 0, 0);
	$grColor = ImageColorAllocate($img, 0, 0, 255);

	ImageFill($img, 0, 0, $bgColor);

	ImageLine($img, $margin_x,  0, $margin_x, $height_graph, $margindColor);
	ImageLine($img, $margin_x,  $height_graph, $width_graph + $margin_x, $height_graph, $margindColor);

	$stepX = (($width_graph - 10) / $show_days);
	$stepY = ($height_graph - $margin_y) / $values_max;
	$counts = $values_max / $margin_in_y;

	$counts_round = round($counts);

	if (($counts - $counts_round) < 0.5 && ($counts - $counts_round) != 0) {
		$counts++;
	}

	if (($values_max / $counts) >= $margin_in_y) {
		for ($i = 1; $i <= $values_max; $i++) {
			ImageLine($img, $margin_x - 5, $height_graph - (int) ($i * $stepY), $margin_x + 5, $height_graph - (int) ($i * $stepY), $margindColor);
			ImageLine($img, $margin_x, $height_graph - (int) ($i * $stepY), $margin_x + $width_graph, $height_graph - (int) ($i * $stepY), $lnColor);
			imagettftext($img, 7, 0, 5, $height_graph - (int) ($i * $stepY) + 5, $margindColor, $font, $i);
		}
	} else {
		for ($i = 1; $i <= $values_max; $i++) {
			if ($i % $counts == 0) {
				ImageLine($img, $margin_x - 5, $height_graph - (int) ($i * $stepY), $margin_x + 5, $height_graph - (int) ($i * $stepY), $margindColor);
				ImageLine($img, $margin_x, $height_graph - (int) ($i * $stepY), $margin_x + $width_graph, $height_graph - (int) ($i * $stepY), $lnColor);
				imagettftext($img, 7, 0, 5, $height_graph - (int) ($i * $stepY) + 5, $margindColor, $font, $i);
			}
		}
	}
	$i = round($values_max,0);
	ImageLine($img, $margin_x - 5, $height_graph - (int) ($i * $stepY), $margin_x + 5, $height_graph - (int) ($i * $stepY), $margindColor);
	ImageLine($img, $margin_x, $height_graph - (int) ($i * $stepY), $margin_x + $width_graph, $height_graph - (int) ($i * $stepY), $lnColor);
	imagettftext($img, 7, 0, 5, $height_graph - (int) ($i * $stepY) + 5, $margindColor, $font, $i);

	$show_space = (int) (($show_days - 1) / 4);
	$show_text[] = 1;
	$show_text[] = 1 + $show_space;
	$show_text[] = 1 + 2 * $show_space;
	$show_text[] = 1 + 3 * $show_space;
	$show_text[] = $show_days - 1;

	for ($i = 0; $i < $show_days; $i++) {
		ImageLine($img, $margin_x + (int) $i * $stepX,  $height_graph - 5, $margin_x + (int) $i * $stepX, $height_graph + 5, $margindColor);
		ImageLine($img, $margin_x + (int) $i * $stepX,  $height_graph, $margin_x + (int) $i * $stepX, 0, $lnColor);
		for($i1 = 0; $i1 <= 4; $i1++) {
			if ($i == $show_text[$i1]) {
				$date_string = va_charset_convert(CHARSET, "UTF-8", va_date("D MMM",$dates_all[$i]));
				imagettftext($img, 7, 0, $margin_x + (int) $i * $stepX - 15, $height_graph + $margin_y - 5, $margindColor, $font, $date_string);
				break;
			}
		}
		$mas2[$i][0] = $margin_x + (int) $i * $stepX;
		$mas2[$i][1] = $height_graph - (int) $mas[$i] * $stepY;
	}

	for ($i = 0; $i < $show_days - 1; $i++) {
		$i2 = $i+1;
		ImageLine($img, $mas2[$i][0], $mas2[$i][1], $mas2[$i2][0], $mas2[$i2][1], $grColor);
		imageellipse($img,$mas2[$i][0],$mas2[$i][1],4,4,$grColor);
	}
	imageellipse($img,$mas2[$i][0],$mas2[$i][1],4,4,$grColor);

	header("Content-type: image/png");
	ImagePng($img);
	ImageDestroy($img);

	
?>