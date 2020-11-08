<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      Viart Shop 5.8                                                  ***
  ***      File:  order_payment.php                                        ***
  ***      Built: Fri Nov  6 06:13:11 2020                                 ***
  ***      http://www.viart.com                                            ***
  ***                                                                      ***
  ****************************************************************************
*/


class VA_Payment
{
	var $status = ""; // success / pending / failure / error
	var $action = ""; // redirect / 3d / form 
	var $failure_action = ""; //  0 - Go to the final step  1 - Redirect user back to the payment details page 
	var $type = ""; // direct / remote
	var $step = ""; // opc / payment / preview / final 
	var $transaction_id = ""; // external payment system ID for payment
	var $url = ""; // URL for redirect
	var $success_url = ""; 
	var $pending_url = ""; 
	var $failure_url = ""; 

	var $form_url = ""; // URL to submit the form
	var $form_method = ""; // get / post
	var $form_class = ""; // CSS class for form
	var $form_params = array(); // hidden parameters submitted on form
	var $form_html = ""; // HTML code for form
	var $form_auto = true; // auto-submit form
	var $ajax_data = ""; // data returned for AJAX call

	var $error_code = ""; 
	var $error_desc = ""; 

	var $pending_code = ""; 
	var $pending_desc = ""; 

	public function __construct() {
		$this->init();
	}

	public function init() {
		$this->status = "";
		$this->action = "";
		$this->failure_action = "";
		$this->type = "";
		$this->step = "";
		$this->transaction_id = "";

		$this->url = "order_final.php";
		$this->form_url = "";
		$this->form_method = "";
		$this->form_class = "";
		$this->form_params = array();
		$this->form_html  = "";
		$this->form_auto = true;


		$this->error_code = "";
		$this->error_desc = "";

		$this->pending_code = "";
		$this->pending_desc = "";
	}

	public function set_status($status_type) {
		$this->status = $status_type;
	}

	public function set_action($action) {
		$this->action = $action;
	}

	public function set_failure_action($failure_action) {
		$this->failure_action = $failure_action;
	}

	public function set_type($type) {
		$this->type = $type;
	}

	public function set_step($step) {
		$this->step = $step;
	}

	public function set_transaction_id($transaction_id) {
		$this->transaction_id = $transaction_id;
	}

	public function convert_advanced_data($error_message, $pending_message, $transaction_id, $variables, $step) {
		global $settings;

		$this->type = "direct";
		$this->transaction_id = $transaction_id;
		$this->step = $step;

	  if ($error_message) {
			$this->status = "failure";
			$this->action = "redirect"; 
			$this->error_desc = $error_message;
		} else if ($pending_message) {
			$this->status = "pending";
			$this->action = "redirect"; 
			$this->pending_desc = $pending_message;
		} else {
			// check 3d variables
			$this->status = "success";
			$secure_3d_acsurl = get_setting_value($variables, "secure_3d_acsurl", "");
			if ($secure_3d_acsurl) {
				$this->action = "3d"; 
				$this->form_url = $secure_3d_acsurl;
				$this->form_method = "post";
				$secure_3d_pareq = get_setting_value($variables, "secure_3d_pareq", "");
				$secure_3d_md = get_setting_value($variables, "secure_3d_md", "");
				if ($settings["secure_url"]) {
					$term_url = $settings["secure_url"]."order_final.php";
				} else {
					$term_url = $settings["site_url"]."order_final.php";
				}
				$this->form_params = array(
					"PaReq" => $secure_3d_pareq,
					"TermUrl" => $term_url,
					"MD" => $secure_3d_md,
				);
			} else {
				$this->action = "redirect"; 
			}
		}
	}

	public function get_ajax_data() {
		if ($this->status == "failure" || $this->status == "error") {
			if ($this->failure_action) {
				$this->ajax_data = array(
					"operation" => "error",
					"errors" => $this->error_desc,
					"error_code" => $this->error_code,
					"error_desc" => $this->error_desc,
					"step" => "payment",
				);
			} else {
				$this->ajax_data = array(
					"operation" => "redirect",
					"location" => $this->url,
				);
			}
		} else if ($this->status == "pending") {
			$this->ajax_data = array(
				"operation" => "redirect",
				"location" => $this->url,
			);
		} else if ($this->status == "success") {
			if ($this->action == "form" || $this->action == "3d") {
				$this->ajax_data = array(
					"operation" => "3d",
					"form" => array(
						"url" => $this->form_url,
						"method" => $this->form_method,
						"class" => $this->form_class,
						"params" => $this->form_params,
						"html" => $this->form_html,
						"auto" => intval($this->form_auto),
					),
				);
			} else {
				$this->ajax_data = array(
					"operation" => "redirect",
					"location" => $this->url,
				);
			}
		}
		return $this->ajax_data;
	}

}