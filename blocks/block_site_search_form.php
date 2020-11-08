<?php

	$default_title = "{FULL_SITE_SEARCH_MSG}";

	$tag_name = get_setting_value($vars, "tag_name");
	$block_type = get_setting_value($vars, "block_type");
	$template_type = get_setting_value($vars, "template_type");

	if ($block_type != "bar" && $block_type != "header" && $template_type != "built-in") {
		if ($template_type == "default") {
			$html_template = "block_site_search_form.html"; 
		} else {
			$html_template = get_setting_value($block, "html_template", "block_site_search_form.html"); 
		}
		if ($block_type == "sub-block") {
		  $t->set_file($vars["tag_name"], $html_template);
		} else {
		  $t->set_file("block_body", $html_template);
		}
	}

	$t->set_var("search_href", get_custom_friendly_url("site_search.php"));
	$t->set_var("site_search_href", get_custom_friendly_url("site_search.php"));

	$q = trim(get_param("q"));
	$sq = trim(get_param("sq"));

	// set up search form parameters
	$t->set_var("q", htmlspecialchars($q));
	$t->set_var("sq", htmlspecialchars($sq));

	$block_parsed = true;