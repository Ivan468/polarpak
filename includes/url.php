<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  url.php                                                  ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


class VA_URL
{
 /**
   * Page part of the URL
	 *
	 * @var string
	 * @access private
	 */
	var $page_name = "";
    /**
     * Query string part of the URL
	 *
	 * @var string
	 * @access private
	 */
	var $parameters = array();

	/**
	* Constructor
	*
	* @param string $page_name
	* @param boolean $save_query_string
	* @param string/array $remove_parameters
	* @param string $query_string
	* @access public
	*/
	public function __construct($page_name, $save_query_string = false, $remove_parameters = "")
	{
		$this->page_name = $page_name;
		if ($save_query_string) {
			if (!is_array($remove_parameters)) {
				$remove_parameters = array($remove_parameters);
			}
			foreach($_GET as $key => $value) {
				if (strlen($value) && !in_array($key, $remove_parameters)) {
					$this->parameters[$key] = array($key, CONSTANT, $value);
				}
			}
		}
	}

	/**
	 * Add parameter to parameters array
	 *
	 * @param string $parameter_name
	 * @param string $parameter_type
	 * @param string $parameter_source
	 * @return void
	 * @access public
	 */
	function add_parameter($parameter_name, $parameter_type, $parameter_source)
	{
		$this->parameters[$parameter_name] = array($parameter_name, $parameter_type, $parameter_source);
	}

	/**
	 * Remove parameter from parameters array
	 *
	 * @param string $parameter_name
	 * @return void
	 * @access public
	 */
	function remove_parameter($parameter_name)
	{
		if (isset($this->parameters[$parameter_name])) {
			unset($this->parameters[$parameter_name]);
		}
	}

	/**
	 * Create URL string with containing all used parameters
	 *
	 * @param string $page_name
	 * @return string
	 * @access public
	 */
	function get_url($page_name = "")
	{
		if ($page_name) {
			$this->page_name = $page_name;
		}
		$query_string = "";
		if (is_array($this->parameters)) {
			$param_number = 0;
			foreach ($this->parameters as $parameter_name => $parameter)
			{
				$parameter_type = $parameter[1];
				$parameter_source = $parameter[2];
				if ($parameter_type == CONSTANT) {
					$parameter_value = $parameter_source;
				} elseif ($parameter_type == GET || $parameter_type == POST || $parameter_type == REQUEST) {
					$parameter_value = get_param($parameter_source);
				} elseif ($parameter_type == SESSION) {
					$parameter_value = get_session($parameter_source);
				} elseif ($parameter_type == COOKIE) {
					$parameter_value = get_cookie($parameter_source);
				} elseif ($parameter_type == APPLICATION) {
					$parameter_value = get_session($parameter_source);
				} elseif ($parameter_type == DB) {
					global $db;
					$parameter_value = $db->f($parameter_source);
				}
				if (strlen($parameter_value)) {
					if ($param_number) {
						$query_string .= "&" . urlencode($parameter_name) . "=" . urlencode($parameter_value);
					} else {
						$query_string .= "?" . urlencode($parameter_name) . "=" . urlencode($parameter_value);
					}
					$param_number++;
				}
			}
		}
		return $this->page_name . $query_string;
	}
}
?>