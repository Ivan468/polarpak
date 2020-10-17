<?php

	$default_title = "{SEARCH_TITLE}";

	$block_type = get_setting_value($vars, "block_type", "");
	if ($block_type != "bar" && $block_type != "header") {       
		$html_template = get_setting_value($block, "html_template", "block_music_search_form.html"); 
	  $t->set_file("block_body", $html_template);
	}

	$t->set_var("search_href", get_custom_friendly_url("music_search.php"));
	$t->set_var("music_search_href", get_custom_friendly_url("music_search.php"));

	$sq = trim(get_param("sq"));
	$sw = trim(get_param("sw"));
	if (!$sw) { $sw = $sq; }
	if (!$sq) { $sq = $sw; }

	// set up search form parameters
	$t->set_var("sq", htmlspecialchars($sq));
	$t->set_var("sw", htmlspecialchars($sw));

	$block_parsed = true;