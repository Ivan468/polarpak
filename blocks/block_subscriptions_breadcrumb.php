<?php                           

	$default_title = "";

	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$html_template = get_setting_value($block, "html_template", "block_subscriptions_breadcrumb.html"); 
  $t->set_file("block_body", $html_template);

	$breadcrumbs_tree_array = array();
	
	$breadcrumbs_tree_array[] = array (get_custom_friendly_url("products_list.php"), PRODUCTS_TITLE);
	$breadcrumbs_tree_array[] = array (get_custom_friendly_url("subscriptions.php"), SUBSCRIPTIONS_MSG);
	
	$ic = count($breadcrumbs_tree_array) - 1;
	for ($i=0; $i<$ic; $i++) {
		$t->set_var("tree_url", $breadcrumbs_tree_array[$i][0]);
		$t->set_var("tree_title", $breadcrumbs_tree_array[$i][1]);
		$t->set_var("tree_class", "");
		$t->parse("tree", true);
	}
	
	if ($ic>=0) {
		$t->set_var("tree_url", $breadcrumbs_tree_array[$ic][0]);
		$t->set_var("tree_title", $breadcrumbs_tree_array[$ic][1]);
		$t->set_var("tree_class", "treeItemLast");
		$t->parse("tree", true);
	}

	if(!$layout_type) { $layout_type = "bb"; }
	$block_parsed = true;

?>