<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.6                                                  ***
  ***      File:  db_query.php                                             ***
  ***      Built: Wed Feb 12 01:09:03 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


class VA_Query
{  
	var $_data = array();  // save all SQL query data here

	public function __construct($data = "") {
		self::prepare_sql($data);
		$this->_data = $data;
	}

	public function add_select($select) {
		$this->_data["select"][] = $select;
	}

	public function add_from($from) {
		$this->_data["from"][] = $from;
	}

	public function add_where($where) {
		$this->_data["where"][] = $where;
	}

	public function add_join($join) {
		$this->_data["join"][] = $join;
	}

	public function add_group($group) {
		$this->_data["group"][] = $group;
	}

	public function add_order($order) {
		$this->_data["order"][] = $order;
	}

	public function build() 
	{
		return self::build_sql($this->_data);
	}


	static function prepare_sql(&$sql_data) 
	{
		if (!is_array($sql_data)) {
			$sql_data = ($sql_data) ? array("where" => $sql_data) : array();
		}
		if (isset($sql_data["order"])) {
			$sql_order = $sql_data["order"];
			if (!is_array($sql_order)) {
				$sql_data["order"] = preg_replace("/\s*order\s+by\s*/i", "", $sql_order);
			}
		}
		// check parameters and convert to arrays
		$params = array ("select", "from", "where", "join", "group", "distinct", "order");
		foreach ($params as $param_name) {
			if (isset($sql_data[$param_name])) {
				$param_value = $sql_data[$param_name];
				if (!is_array($param_value)) { 
					$param_value = trim($param_value);
					$sql_data[$param_name] = strlen($param_value) ? array($param_value) : array(); 
				}
			} else {
				$sql_data[$param_name] = array();
			}
		}
		return $sql_data;
	}

	static function build_sql($sql_data) 
	{
		$sql_select = " SELECT " . implode(", ", $sql_data["select"]);
		$sql_brackets = ""; $sql_join = "";
		foreach ($sql_data["join"] as $join_value) {
			$sql_brackets .= "(";
			$sql_join .= " ".$join_value ." )";
		}
		$sql_from = " FROM ".$sql_brackets.implode(",", $sql_data["from"]).$sql_join;
		$sql_where = count($sql_data["where"]) ? " WHERE " . implode(" AND ", $sql_data["where"]) : "";
		$sql_group = count($sql_data["group"]) ? " GROUP BY " . implode(", ", $sql_data["group"]) : "";
		$sql_order = count($sql_data["order"]) ? " ORDER BY " . implode(", ", $sql_data["order"]) : "";
		$sql = $sql_select . $sql_from . $sql_where . $sql_group . $sql_order;
		return $sql;
	}
}
