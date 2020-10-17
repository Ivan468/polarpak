<?php

	$default_title = "{CART_TITLE}";

	$html_template = get_setting_value($block, "html_template", "block_order_cart.html"); 
  $t->set_file("block_body", $html_template);

	if ($current_page == "order_final.php") {
		$order_id = get_order_id();
		$vc = get_session("session_vc");
		$order_errors = check_order($order_id, "", true);
	} else {
		$order_id = get_param("order_id");
		$vc = get_param("vc");
		if (!strlen($order_id)) { $order_id = get_session("session_order_id"); }
		if (!strlen($vc)) { $vc = get_session("session_vc"); }
		$order_errors = check_order($order_id, $vc);
	}

	if (!$order_errors) {
		// show cart always as for payment details page
		$items_text = show_order_items($order_id, true, "cc_info");
	}

	$block_parsed = true;

?>