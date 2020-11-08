<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  navigator.php                                            ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


// navigator types
define ("SIMPLE",    1); // only current page is shown
define ("CENTERED",  2); // the current page is always shown in the navigator center
define ("MOVING",    3); // a navigator splits to pages series
define ("ALL_PAGES", 4); // shows links to all available pages

class VA_Navigator
{
	var $t;                         // template with navigator structure
	var $order_by;                  
	var $is_first_last;
	var $is_prev_next;
	var $navigator_page;
	var $records_left;
	var $data_type;
	var $data_js;
	var $html_id;

  public function __construct($template_path, $filename, $navigator_page)
  {
		global $t;
		$t->set_file("_navigator", $filename);
		$this->is_first_last = false;
		$this->is_prev_next = true;
		$this->navigator_page = $navigator_page;
  }

	function set_data_type($data_type)
	{
		$this->data_type = $data_type;
	}
	function set_data_js($data_js)
	{
		$this->data_js = $data_js;
	}
	function set_html_id($html_id)
	{
		$this->html_id = $html_id;
	}

	function set_parameters($is_first_last, $is_prev_next)
	{
		$this->is_first_last = $is_first_last;
		$this->is_prev_next = $is_prev_next;
	}

	function set_navigator($navigator_name, $navigator_parameter, $navigator_type, $pages_number, $records_per_page, $total_records, $empty_navigator, $pass_parameters = "", $remove_parameters = array(), $suffix = "")
	{
		global $t, $db;
		$get_vars = isset($_GET) ? $_GET : array();

		// empty navigator variables before parse		
		$t->set_var("pn_first", "");
		$t->set_var("pn_prev", "");
		$t->set_var("pn_before", "");
		$t->set_var("pn_active", "");
		$t->set_var("pn_after", "");
		$t->set_var("pn_last", "");
		$t->set_var("pn_next", "");
		if ($this->data_type) {
			$t->set_var("data_type", htmlspecialchars($this->data_type));
		}
		if ($this->data_js) {
			$t->set_var("data_js", htmlspecialchars($this->data_js));
		}
		if ($this->html_id) {
			$t->set_var("html_id", htmlspecialchars($this->html_id));
		}
		$t->set_var("page_param", htmlspecialchars($navigator_parameter));
		
		$page = $this->navigator_page;
		$page_number = intval(get_param($navigator_parameter));
		if($page_number < 1)  $page_number = 1; 
		if (is_array($pass_parameters)) {	
			$remove_parameters[] = $navigator_parameter;
			$query_string = get_query_string($pass_parameters, $remove_parameters, "", false);
		} else {
			$remove_parameters[] = $navigator_parameter;
			$query_string = get_query_string($get_vars, $remove_parameters, "", false);
		}
		$t->set_var("data_params", htmlspecialchars(trim($query_string, "&?")));

		$query_string .= strlen($query_string) ? "&" : "?";
		$page .= htmlspecialchars($query_string) . $navigator_parameter . "=";
		$total_pages = ceil($total_records / $records_per_page);
		if($page_number > $total_pages) $page_number = $total_pages;

		$t->set_var("page", $page_number);
		$t->set_var("page_number", $page_number);
		$t->set_var("current_page", $page_number);
		$t->set_var("total_pages", $total_pages);
		$t->set_var("total_records", $total_records);
		$t->sparse("pn_active", false);

		if($page_number > 1)
		{
			if($this->is_first_last)
			{
				$t->set_var("navigating_href", $page . "1" . $suffix);
				$t->set_var("page", 1);
				$t->parse("pn_first", false);
			}

			if($this->is_prev_next)
			{
				$t->set_var("navigating_href", $page . ($page_number - 1) . $suffix);
				$t->set_var("page", ($page_number - 1));
				$t->parse("pn_prev", false);
			}
		}

		if($navigator_type == CENTERED)
		{
			$start_page = $page_number - intval(($pages_number - 1) / 2);
			if($start_page < 1) $start_page = 1;
			$end_page = $start_page + $pages_number - 1;
      if($end_page > $total_pages) 
			{
        $start_page = $start_page - $end_page + $total_pages;
				if($start_page < 1) $start_page = 1;
				$end_page = $total_pages;
      }

			$this->parse_pages($navigator_type, $start_page, $page_number, $end_page, $page, $records_per_page, $total_records);
		}
		else if($navigator_type == MOVING)
		{
			$pages_group = ceil($page_number / $pages_number);
      $start_page = 1 + $pages_number * ($pages_group - 1);
      $end_page = $pages_number * $pages_group;
			if($start_page < 1) $start_page = 1;
      if($end_page > $total_pages) $end_page = $total_pages;

			$this->parse_pages($navigator_type, $start_page, $page_number, $end_page, $page, $records_per_page, $total_records);
		}
		else if($navigator_type == ALL_PAGES)
		{
			$this->parse_pages($navigator_type, 1, $page_number, $total_pages, $page, $records_per_page, $total_records);
		}
		else 
		{
			$this->parse_pages($navigator_type, $page_number, $page_number, $page_number, $page, $records_per_page, $total_records);
		}

		if($page_number < $total_pages)
		{
			if($this->is_first_last)
			{
				$t->set_var("navigating_href", $page . $total_pages . $suffix);
				$t->set_var("page", $total_pages);
				$t->parse("pn_last", false);
			}

			if($this->is_prev_next)
			{
				$t->set_var("navigating_href", $page . ($page_number + 1) . $suffix);
				$t->set_var("page", ($page_number + 1));
				$t->parse("pn_next", false);
			}
		}

		if($records_per_page >= $total_records && $empty_navigator == false)
		{
			$t->set_var($navigator_name, "");
			$t->set_var($navigator_name."_block", "");
		}
		else
		{
			$t->parse("_navigator", false);
			$t->set_var($navigator_name, $t->get_var("_navigator"));
			$t->sparse($navigator_name."_block", false);
		}
	
		return $page_number;	
	}

	function parse_pages($navigator_type, $start_page, $current_page_number, $end_page, $page, $records_per_page, $total_records)
	{
		global $t;
    if($navigator_type == MOVING && $start_page > 1)
		{
			$first_record = ($start_page - 2) * $records_per_page + 1;
			$last_record = ($start_page - 1) * $records_per_page;
			$t->set_var("navigating_href", $page . ($start_page - 1));
			$t->set_var("page", ($start_page - 1));
			$t->set_var("page_number", "..." . ($start_page - 1));
			$t->set_var("first_record", "..." . $first_record);
			$t->set_var("last_record", $last_record);
			$t->parse("pn_before", true);
		}

		for($i = $start_page; $i <= $end_page; $i++)
		{
			$first_record = ($i - 1) * $records_per_page + 1;
			$last_record = $i * $records_per_page;
			if($last_record > $total_records) $last_record = $total_records;
			$t->set_var("navigating_href", $page . $i);
			$t->set_var("page", $i);
			$t->set_var("page_number", $i);
			$t->set_var("first_record", $first_record);
			$t->set_var("last_record", $last_record);
			if($i < $current_page_number) {
				$t->parse("pn_before", true);
			} else if($i > $current_page_number) {
				$t->parse("pn_after", true);
			} else {
				$t->set_var("current_first_record", $first_record);
				$t->set_var("current_last_record", $last_record);	
			}
		}

		$total_pages = ceil($total_records / $records_per_page);
    if($navigator_type == MOVING && $end_page < $total_pages)
		{
			$first_record = $end_page * $records_per_page + 1;
			$last_record = ($end_page + 1) * $records_per_page;
			if($last_record > $total_records) $last_record = $total_records;
			$t->set_var("navigating_href", $page . ($end_page + 1));
			$t->set_var("page_number", ($end_page + 1) . "...");
			$t->set_var("page", ($end_page + 1));
			$t->set_var("first_record", $first_record);
			$t->set_var("last_record", $last_record . "...");
			$t->parse("pn_after", true);
		}
	}
}

