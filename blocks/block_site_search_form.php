<?php

	$default_title = "{FULL_SITE_SEARCH_MSG}";

	$block_type = get_setting_value($vars, "block_type", "");
	if ($block_type != "bar" && $block_type != "header") {       
		$html_template = get_setting_value($block, "html_template", "block_site_search_form.html"); 
	  $t->set_file("block_body", $html_template);
	}

	$t->set_var("search_href", get_custom_friendly_url("site_search.php"));
	$t->set_var("site_search_href", get_custom_friendly_url("site_search.php"));

	$q = trim(get_param("q"));
	$sq = trim(get_param("sq"));

	// set up search form parameters
	$t->set_var("q", htmlspecialchars($q));
	$t->set_var("sq", htmlspecialchars($sq));

	$block_parsed = true;