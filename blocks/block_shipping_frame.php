<?php

	if (!isset($shipping_frame_parsed)) { $shipping_frame_parsed = false; } 
	if (!$shipping_frame_parsed) {
		$t->set_file("hidden_block", "shipping_frame.html");
		$t->parse_to("hidden_block", "hidden_blocks", true);
		$shipping_frame_parsed = true;
	}

?>