<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  chart_functions.php                                      ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function parse_chart($svg_width, $svg_height, $data, $chart_type = "", $decimals = 0)
{
	global $t;
	if (!$svg_width) { $svg_width = 900; }
	if (!$svg_height) { $svg_width = 400; }

	// clear blocks before parse
	$t->set_var("svg_paths", "");
	$t->set_var("svg_texts", "");

	// set global text style tag
	if ($svg_width < 700 || $svg_height < 300) {
		$t->set_var("text_style", "font-size: 13px;");
	} else if ($svg_width < 550 || $svg_height < 250) {
		$t->set_var("text_style", "font-size: 12px;");
	} else if ($svg_width < 400 || $svg_height < 200) {
		$t->set_var("text_style", "font-size: 11px;");
	}

	$x_points = count($data); // number of x points

		$chart_width = $svg_width - 120; $chart_start_x = 20;
		$chart_height = $svg_height - 50; $chart_start_y = 20;
		if ($svg_width < 400) {
			$chart_width = $svg_width - 70; $chart_start_x = 10;
			$chart_height = $svg_height - 30; $chart_start_y = 10;
		}
		// set SVG and Chart parameters
		$t->set_var("svg_width", $svg_width);
		$t->set_var("svg_height", $svg_height);
		$t->set_var("chart_width", $chart_width);
		$t->set_var("chart_height", $chart_height);
		$t->set_var("chart_start_x", $chart_start_x);
		$t->set_var("chart_start_y", $chart_start_y);
		$t->set_var("data_step_x", round($chart_width / ($x_points - 1), 4));

		// get first element data, and min and max y values
		reset($data);
		$start_key = key($data);
		$start_y_value = isset($data[$start_key]["y_value"]) ? $data[$start_key]["y_value"] : $data[$start_key]["y"];
		$start_y_text = isset($data[$start_key]["y_text"]) ? $data[$start_key]["y_text"] : $data[$start_key]["y"];
		$start_x_text = isset($data[$start_key]["x_text"]) ? $data[$start_key]["x_text"] : $data[$start_key]["x"];
		$min_y_value = $start_y_value; 
		$max_y_value = $start_y_value;
		// check 'y' min and max value to calculate 'x' and 'y' scales 
		foreach ($data as $key => $values) {
			$y_value = isset($values["y_value"]) ? $values["y_value"] : $values["y"];
			if ($y_value < $min_y_value) { $min_y_value = $y_value; } 
			if ($y_value > $max_y_value) { $max_y_value = $y_value; } 
		}
		if ($chart_type != "average") {
			$min_y_value = 0; // user zero value as default min value
		}

		$max_x_sections = floor($chart_width / 70);
		if ($max_x_sections > 14) $max_x_sections = 14; 
		$max_y_sections = floor($chart_height / 35);
		if ($min_y_value == 0 && $max_y_value == 0) {
			$max_y_value = 1;
			$max_y_sections = 1;
		}
		if ($x_points <= $max_x_sections) {
			$width_sections = $x_points - 1;
		} else {
			$width_sections = $max_x_sections;
			//$width_sections = ceil($x_points / $max_x_sections);
		}
		if ($decimals == 0 && $max_y_value <= $max_y_sections) {
			$height_sections = ceil($max_y_value) + 1;
			$max_y_value++;
			if ($min_y_value) {
				$height_sections++;
				$max_y_value--;
			}
			$y_diff =  ($max_y_value - $min_y_value);
		} else {
			$height_sections = $max_y_sections;
			// increase y axis by 10% to have some space on top and for average add for below as well
			if ($min_y_value) {
				$y_diff = ($max_y_value - $min_y_value) * (1.2);
			} else {
				$y_diff = ($max_y_value - $min_y_value) * (1.1);
			}
			if ($y_diff == 0) { $y_diff = $max_y_value * 0.1; }
			$y_value_increase = ($y_diff - $max_y_value + $min_y_value) / 2;
			if ($min_y_value) {
				$height_sections++;
				$min_y_value -= $y_value_increase; 
				$max_y_value += $y_value_increase;
			} else {
				$max_y_value += $y_value_increase*2;
			}
			$height_sections++;
		}
//echo ":".$min_y_value;
//echo ":".$max_y_value;

		$width_step = $chart_width / $width_sections; 
		$height_step = $chart_height / $height_sections;
		// draw vertical lines
		$path_d = "";
		for ($section = 0; $section <= $width_sections; $section++) {
			$move_x = $chart_start_x + ceil($section * $width_step);
			$move_y = $chart_start_y;
			$line_x = $move_x;
			$line_y = $chart_start_y + $chart_height;
			$path_d .= " M $move_x $move_y L $line_x $line_y ";
		}
		$t->set_var("path_d", $path_d);
		$t->set_var("path_class", "chart-grid");
		$t->sparse("svg_paths", true);

		// draw horizonal lines
		$path_d = "";
		for ($section = 0; $section <= $height_sections; $section++) {
			$move_x = $chart_start_x;
			$move_y = $chart_start_y + ceil($section * $height_step);
			$line_x = $chart_start_x + $chart_width;
			$line_y = $move_y;
			$path_d .= " M $move_x $move_y L $line_x $line_y ";
		}
		$t->set_var("path_d", $path_d);
		$t->set_var("path_class", "chart-grid");
		$t->sparse("svg_paths", true);


		$scale_x = $chart_width / $width_sections;
		$scale_x = $chart_width / ($x_points - 1);
		$scale_y = $chart_height / $y_diff; 

		// move graph to start position and save chart data
		$chart_data = array();
		$start_pos_x = $chart_start_x;
		$start_pos_y = $chart_start_y + $chart_height - ceil(($start_y_value - $min_y_value) * $scale_y);
		$path_d = " M $start_pos_x $start_pos_y ";
		$chart_data[] = array("prev-pos-x" => 0, "pos-x" => $start_pos_x, "pos-y" => $start_pos_y, "y-value" => $start_y_value, "y-text" => $start_y_text, "x-text" => $start_x_text);
		$prev_pos_x = $start_pos_x;
		$x_index = 0;
		// get new array without first element to call foreach
		$data_lines = array_slice($data, 1);
		foreach ($data_lines as $key => $values) {
			$x_index++;
			$pos_x = $chart_start_x + ceil($x_index * $scale_x);
			$y_value = isset($values["y_value"]) ? $values["y_value"] : $values["y"];
			$y_text = isset($values["y_text"]) ? $values["y_text"] : $values["y"];
			$pos_y = $chart_start_y + $chart_height - ceil(($y_value - $min_y_value) * $scale_y);
			$path_d .= " L $pos_x $pos_y ";
			// save chart data and previous value for X position
			$x_text = isset($values["x_text"]) ? $values["x_text"] : $values["x"];;
			$chart_data[] = array("prev-pos-x" => $prev_pos_x, "pos-x" => $pos_x, "pos-y" => $pos_y, "y-value" => $y_value, "y-text" => $y_text, "x-text" => $x_text);
			$prev_pos_x = $pos_x;
		}
		$t->set_var("path_d", $path_d);
		$t->set_var("path_class", "chart-graph");
		$t->sparse("svg_paths", true);
		// set chart data in JSON format
		$t->set_var("data_chart", htmlspecialchars(json_encode($chart_data)));
		// fill chart 
		$pos_x = $chart_start_x + $chart_width; $pos_y = $chart_start_y + $chart_height;
		$path_d .= " L $pos_x $pos_y ";
		$pos_x = $chart_start_x; $pos_y = $chart_start_y + $chart_height;
		$path_d .= " L $pos_x $pos_y Z";
		$t->set_var("path_d", $path_d);
		$t->set_var("path_class", "chart-fill");
		$t->sparse("svg_paths", true);

		// show text for Y-axis
		for ($section = 1; $section < $height_sections; $section++) {
			$text_x = $chart_width + $chart_start_x + 5;
			$text_y = $chart_start_y + $section * $height_step + 5;
			$axis_y = round($max_y_value - ($y_diff / $height_sections) * $section, $decimals);
			$t->set_var("text_x", $text_x);
			$t->set_var("text_y", $text_y);
			$t->set_var("text_class", "axis-y");
			if ($chart_type == "currency") {
				$t->set_var("svg_text", currency_format($axis_y));
			} else {
				$t->set_var("svg_text", $axis_y);
			}
			$t->parse("svg_texts", true);
		}

		// show text for X-axis
		$x_section = $x_points - 1;
		$section_coef = ($x_section / $width_sections);
		$index_coef = ceil($x_section / $width_sections);
		$x_index = 0;
		foreach ($data as $key => $values) {
			if ($x_index % $index_coef == 0) {
				$svg_text = isset($values["x_text"]) ? $values["x_text"] : $values["x"];
				$text_x = $chart_start_x + ($x_index * $width_step) / $section_coef  - 5;
				$text_y = $chart_height + $chart_start_y + 15;
				$t->set_var("text_x", $text_x);
				$t->set_var("text_y", $text_y);
				$t->set_var("text_class", "axis-x");
				$t->set_var("svg_text", $svg_text);
				$t->parse("svg_texts", true);
			}
			$x_index++;
		}
}

?>