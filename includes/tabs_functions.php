<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  tabs_functions.php                                       ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


function parse_tabs($tabs, $current_tab = "", $tabs_class = "tabs", $tabs_in_row = 0)
{
	global $t;

	// clear previously parsed data
	$t->set_var("tabs", "");
	$t->set_var("tabs_data", "");
	$t->set_var("tabs_class", htmlspecialchars($tabs_class));

	$tab_row = 0; $tab_number = 0; $active_tab = false;
	if (!strlen($current_tab)) {
		$current_tab = get_param("tab");
		if (!strlen($current_tab)) { 
			foreach ($tabs as $tab_name => $tab_info) {
				$tab_show = isset($tab_info["show"]) ? $tab_info["show"] : true;
				if ($tab_show) {
					$current_tab = $tab_name;
					break; 
				}
			}
		} 
	}

	$tab_row = 0; $tab_number = 0; $active_tab = false;
	foreach ($tabs as $tab_name => $tab_info) {
		$tab_title = $tab_info["title"];
		$tab_show = isset($tab_info["show"]) ? $tab_info["show"] : true;
		$tab_id = "tab_".$tab_name;
		$tab_data_id = $tab_name."_data";
		if ($tab_show) {
			$tab_number++;
			$t->set_var("tab_id", $tab_id);
			$t->set_var("tab_name", $tab_name);
			$t->set_var("tab_title", $tab_title);
			$t->set_var("tab_data_id", $tab_data_id);
			if ($tab_name == $current_tab) {
				$active_tab = true;
				$tab_class = "tab-title tab-active";
				$tab_data_class = "tab-data "."tab-".$tab_name." tab-show";
				$tab_show_class = "tab-show";
				$tab_data_style = "display: block;"; 
			} else {
				$tab_class = "tab-title";
				$tab_data_class = "tab-data "."tab-".$tab_name." tab-hide";
				$tab_show_class = "tab-hide";
				$tab_data_style = "display: none;"; 
			}
			$t->set_var("tab_class", $tab_class);
			$t->set_var("tab_data_class", $tab_data_class);
			$t->set_var($tab_name . "_style", $tab_data_style);
			$t->set_var($tab_name . "_class", $tab_data_class);
			$t->set_var($tab_name . "_show_class", $tab_show_class);

			$t->parse("tabs", true);
			if ($tabs_in_row && $tab_number % $tabs_in_row == 0) {
				$tab_row++;
				$t->set_var("row_id", "tab_row_" . $tab_row);
				if ($active_tab) {
					$t->rparse("tabs_rows", true);
				} else {
					$t->parse("tabs_rows", true);
				}
				$t->set_var("tabs", "");
			}
			// check if we need to parse tabs data
			if (isset($tab_info["data"])) {
				$t->set_var("tab_data", $tab_info["data"]);
				$t->parse("tabs_data", true);
			}
		} else {
			// hide all related blocks in case if tab hidden
			$t->set_var($tab_name . "_style", "display: none;");
			$t->set_var($tab_name . "_class", "tab-data tab-hide");
			$t->set_var($tab_name . "_show_class", "tab-hide");
		}
	}
	if ($tabs_in_row && $tab_number % $tabs_in_row != 0) {
		$tab_row++;
		$t->set_var("row_id", "tab_row_" . $tab_row);
		if ($active_tab) {
			$t->rparse("tabs_rows", true);
		} else {
			$t->parse("tabs_rows", true);
		}
	}
	$t->set_var("current_tab", $current_tab);
	$t->set_var("tab", $current_tab);
	return $current_tab;
}
