<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  admin_block_breadcrumb.php                               ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


	if (!isset($va_trail) || !is_array($va_trail)) { return; }

	// convert to special trail 
	reset($va_trail);
	$trail_data = current($va_trail);
	if (!isset($trail_data["url"])) {
		$tmp_trail = $va_trail; 
		$va_trail = array();
		foreach ($tmp_trail as $trail_url => $trail_title) {
			$va_trail[] = array("url" => $trail_url, "title" => $trail_title);
		}
	}

	// if the first element isn't admin.php add it to the begin of trail
	reset($va_trail);
	$trail_data = current($va_trail);
	if ($trail_data["url"] != "admin.php") {
		array_unshift ($va_trail, array("url" => "admin.php", "title" => va_message("ADMINISTRATION_MSG"), "class" => "home"));
	}

	$t->set_file("admin_breadcrumb", "admin_block_breadcrumb.html");
	$t->set_var("trail", ""); // clear trail

	foreach ($va_trail as $trail_url => $trail_data) {
		$trail_url = $trail_data["url"];
		$trail_title = $trail_data["title"];
		$trail_class = isset($trail_data["class"]) ? $trail_data["class"] : "";
		$t->set_var("trail_url", htmlspecialchars($trail_url));
		$t->set_var("trail_title", htmlspecialchars($trail_title));
		$t->set_var("trail_class", htmlspecialchars($trail_class));
		$t->parse("trail");
	}

	$t->parse("admin_breadcrumb", false);

