	function vaRulePopup(clickObj) 
	{
		var overrideField = clickObj.getAttribute("data-field");
		document.record.override_field.value = overrideField;
		var overrideLink = "";
		if (clickObj.hasAttribute("data-link")) {
			overrideLink = clickObj.getAttribute("data-link");
		} else if (clickObj.id) {
			overrideLink = clickObj.id;
		} else {
			clickObj.id = overrideField+"_link";
			overrideLink = clickObj.id;
		}
		document.record.override_link.value = overrideLink;

		var overridePopup = document.querySelector(".rule-popup-template");
		vaShowPopup(overridePopup.innerHTML);	
		// populate window with the list of available rules
		vaRuleList();
	}

	function vaRuleList()
	{
		var popupArea = document.getElementById("popupArea");
		// remove edit mode if it was used
		var popupObj = popupArea.querySelector(".rule-popup");
		popupObj.className = popupObj.className.replace(/edit-mode/gi, "").trim();
		// get data to show available rules
		var overrideField = document.record.override_field.value;
		var overrideValue = document.record.elements[overrideField].value;
		var overrideRules = [];
		try {
			overrideRules = JSON.parse(overrideValue);	
		} catch(e) {}

		// HTML elements for overried popup
		var rulesObj = popupArea.querySelector(".rule-list");
		var headObj = popupArea.querySelector(".list-head-template");
		var ruleTemplate = popupArea.querySelector(".list-rule-row-template");
		var noRulesObj = popupArea.querySelector(".list-no-rules-template");

		// always clear all nodes in rules node
	  while (rulesObj.firstChild) {
  	  rulesObj.removeChild(rulesObj.lastChild);
	  }

		// Show rules
		var ruleNumber = 0;
		for (var ruleId in overrideRules) {
			var overrideRule = overrideRules[ruleId];
			ruleNumber++;
			if (ruleNumber == 1) {
				rulesObj.insertAdjacentHTML("beforeend", headObj.innerHTML);
			}

			var ruleStatus = parseInt(overrideRules[ruleId].rule_status);
			if (isNaN(ruleStatus)) { ruleStatus = 0; }
			var ruleClass = (overrideRule.rule_status ==1) ? "rule-active" : "rule-inactive";
			var ruleRowHTML = ruleTemplate.innerHTML.replace(/\[rule_class\]/gi, ruleClass);
			ruleRowHTML = ruleRowHTML.replace(/\[rule_id\]/gi, ruleId);
			ruleRowHTML = ruleRowHTML.replace(/\[rule_order\]/gi, overrideRule.rule_order);
			ruleRowHTML = ruleRowHTML.replace(/\[rule_name\]/gi, overrideRule.rule_name);
			rulesObj.insertAdjacentHTML("beforeend", ruleRowHTML);
		}

		if (ruleNumber == 0) {
			// show no rules object
			rulesObj.insertAdjacentHTML("beforeend", noRulesObj.innerHTML);
		}

		vaCenterBlock(popupArea);
	}

	function vaRuleEdit(clickObj, editId)
	{
		var popupArea = document.getElementById("popupArea");

		// get list of available rules for selected notification
		var overrideField = document.record.override_field.value;
		var overrideValue = document.record.elements[overrideField].value;
		var overrideRules = [];
		try {
			overrideRules = JSON.parse(overrideValue);	
		} catch(e) {}
		// get data for selected rule 
		if (typeof editId === "undefined") {
			editId = clickObj.getAttribute("data-id");
		}
		var overrideRule = {};
		if (overrideRules[editId]) {
			overrideRule = overrideRules[editId];
		} else {
			// prepare some default values for a new rule
			overrideRule.rule_status = 1;
			var maxRuleOrder = 0;
			for (ruleId in overrideRules) {
				var ruleOrder = parseInt(overrideRules[ruleId].rule_order);
				if (isNaN(ruleOrder)) { ruleOrder = 0; }
				if (ruleOrder > maxRuleOrder) {
					maxRuleOrder = ruleOrder;
				}
			}
			overrideRule.rule_order = (maxRuleOrder + 1);
		}
		var popupObj = vaParent(clickObj, ".rule-popup");
		popupObj.className += " edit-mode";

		var ruleForm = popupObj.querySelector("form");
		var parameterList = popupObj.querySelector(".parameter-list");
		var parameterTemplate = popupObj.querySelector(".parameter-template");
		var valueTemplate = popupObj.querySelector(".value-template");
		// save current rule index
		ruleForm.rule_id.value = editId;
		// show rule status
		if (overrideRule.rule_status == 1) {
			ruleForm.rule_status[0].checked = true;
		} else {
			ruleForm.rule_status[1].checked = true;
		}
		// set rule order
		if (typeof overrideRule.rule_order !== "undefined") {
			ruleForm.rule_order.value = overrideRule.rule_order;
		} else {
			ruleForm.rule_order.value = "";
		}
		if (typeof overrideRule.rule_name !== "undefined") {
			ruleForm.rule_name.value = overrideRule.rule_name;
		} else {
			ruleForm.rule_name.value = "";
		}

		// clear parameters list before populate it
	  while (parameterList.firstChild) {
  	  parameterList.removeChild(parameterList.lastChild);
	  }

		// get rule parameters
		var ruleParams;
		if (overrideRule.parameters) {
			ruleParams = overrideRule.parameters;
		}
		if(!ruleParams) {
			ruleParams = [{"name": "", "values": [""]}];
		}
		var paramNumber = 0;
		for (paramId in ruleParams) {
			paramNumber++;
			var ruleParam = ruleParams[paramId];
			parameterList.insertAdjacentHTML("beforeend", parameterTemplate.innerHTML);
			var nameInputObj = ruleForm.param_name;
			var parameterRow = vaParent(nameInputObj, ".parameter-row");
			parameterRow.setAttribute("data-index", paramNumber);
			var newValueObj = parameterRow.querySelector(".param-value .bn-new-value");
			ruleForm.param_name.setAttribute("value", ruleParam.name);
			ruleForm.param_name.setAttribute("name","param_name_"+paramNumber);
			// parse parameter values
			var valueNumber = 0;
			for (valueId in ruleParam.values) {
				valueNumber++;
				var paramValue = ruleParam.values[valueId];
				newValueObj.insertAdjacentHTML("beforebegin", valueTemplate.innerHTML);
				ruleForm.param_value.setAttribute("value", paramValue);
				ruleForm.param_value.setAttribute("name","param_value_"+paramNumber+"_"+valueNumber);
			}
			ruleForm.value_number.setAttribute("value", valueNumber);
			ruleForm.value_number.setAttribute("name","value_number_"+paramNumber);

		}
		ruleForm.param_number.value = paramNumber;
		// populate override mail parameters
		var mailParams = ["mail_to", "mail_from", "mail_cc", "mail_bcc", "mail_reply_to", "mail_return_path", "mail_subject", "mail_type", "mail_message"];
		for (var p in mailParams) {
			var mailParam = mailParams[p];
			if (typeof overrideRule[mailParam] !== "undefined") {
				ruleForm.elements[mailParam].value = overrideRule[mailParam];
			} else {
				ruleForm.elements[mailParam].value = "";
			}
		}

		
		// centering popup area as edit form could has a different height
		popupArea.style.transition = "top 1s, left 1s"; // use transition 
		vaCenterBlock(popupArea);
	}

	function vaRuleNewParameter(btnObj)
	{
		var popupObj = vaParent(btnObj, ".rule-popup");
		var formObj = popupObj.querySelector("form");
		var parameterList = popupObj.querySelector(".parameter-list");
		var parameterTemplate = popupObj.querySelector(".parameter-template");
		var valueTemplate = popupObj.querySelector(".value-template");
		var paramNumber = formObj.param_number.value;

		// add a new parameter
		paramNumber++;
		parameterList.insertAdjacentHTML("beforeend", parameterTemplate.innerHTML);
		var nameInputObj = formObj.param_name;
		var parameterRow = vaParent(nameInputObj, ".parameter-row");
		parameterRow.setAttribute("data-index", paramNumber);
		var newValueObj = parameterRow.querySelector(".param-value .bn-new-value");
		formObj.param_name.setAttribute("name","param_name_"+paramNumber);
		// parse parameter value
		newValueObj.insertAdjacentHTML("beforebegin", valueTemplate.innerHTML);
		formObj.param_value.setAttribute("name","param_value_"+paramNumber+"_1");
		formObj.value_number.setAttribute("value", 1);
		formObj.value_number.setAttribute("name","value_number_"+paramNumber);
		formObj.param_number.value = paramNumber;
		// centering popup area as adding a new control increase popup height
		var areaObj = vaParent(popupObj, ".popup-area");
		vaCenterBlock(areaObj);
	}

	function vaRuleNewValue(btnObj)
	{
		var popupObj = vaParent(btnObj, ".rule-popup");
		var formObj = popupObj.querySelector("form");
		var valueTemplate = popupObj.querySelector(".value-template");
		var parameterRow = vaParent(btnObj, ".parameter-row");
		var paramIndex = parameterRow.getAttribute("data-index");
		var newValueObj = parameterRow.querySelector(".param-value .bn-new-value");
		var valueNumber = formObj.elements["value_number_"+paramIndex].value;

		// parse parameter value
		valueNumber++;
		newValueObj.insertAdjacentHTML("beforebegin", valueTemplate.innerHTML);
		formObj.param_value.setAttribute("name","param_value_"+paramIndex+"_"+valueNumber);
		formObj.elements["value_number_"+paramIndex].value = valueNumber;

		// centering popup area as adding a new value could increase popup height
		var areaObj = vaParent(popupObj, ".popup-area");
		vaCenterBlock(areaObj);
	}

	function vaRuleSave(btnObj)
	{
		// get list of available rules for selected notification
		var overrideField = document.record.override_field.value;
		var overrideValue = document.record.elements[overrideField].value;
		var overrideRules = [];
		try {
			overrideRules = JSON.parse(overrideValue);	
		} catch(e) {}
		// get form data for selected rule
		var formObj = vaParent(btnObj, "form");
		var editId = formObj.rule_id.value;
		// check if all required fields were set
		var ruleErrors = false;
		var ruleOrder = formObj.rule_order.value;
		var ruleOrderField = formObj.querySelector(".fd-order");
		if (!ruleOrder.match(/^[0-9]+$/g)) {
			ruleErrors = true;
			ruleOrderField.className += " fd-error";
		} else {
			ruleOrderField.className = ruleOrderField.className.replace(/fd-error/gi, "").trim();
		}
		var ruleName = formObj.rule_name.value;
		var ruleNameField = formObj.querySelector(".fd-name");
		if (!ruleName) {
			ruleErrors = true;
			ruleNameField.className += " fd-error";
		} else {
			ruleNameField.className = ruleNameField.className.replace(/fd-error/gi, "").trim();
		}
		if (ruleErrors) {
			return;
		}

		// save new or update current rule
		var overrideRule = {};
		overrideRule.rule_status = formObj.rule_status.value;
		overrideRule.rule_order = formObj.rule_order.value;
		overrideRule.rule_name = formObj.rule_name.value;
		// get rule parameters
		var paramNumber = formObj.param_number.value;
		overrideRule.parameters = []; // use array as there could be parameters with the same name when we may need to check multi-selection
		for (var p = 1; p <= paramNumber; p++) {
			var paramName = formObj.elements["param_name_"+p].value;
			if (paramName) {
				var valueNumber = formObj.elements["value_number_"+p].value;
				var parameter = {"name": paramName, "values": []};
				for (var v = 1; v <= valueNumber; v++) {
					var paramValue = formObj.elements["param_value_"+p+"_"+v].value;
					if (paramValue) {
						parameter.values.push(paramValue);
					}
				}
				overrideRule.parameters.push(parameter);
			}
		}
		// get mail override parameters
		var mailParams = ["mail_to", "mail_from", "mail_cc", "mail_bcc", "mail_reply_to", "mail_return_path", "mail_subject", "mail_type", "mail_message"];
		for (var p in mailParams) {
			var mailParam = mailParams[p];
			var overrideValue = formObj.elements[mailParam].value;
			if (overrideValue !== "") {
				overrideRule[mailParam] = overrideValue;
			}
		}

		// save new rule
		if (editId !== "") {
			overrideRules[editId] = overrideRule;
		} else {
			overrideRules.push(overrideRule);
		}
		// sort rules by their rule_order before save them
		overrideRules.sort(function(a, b) {
			var orderA = parseInt(a.rule_order); var orderB = parseInt(b.rule_order); 
			if (orderA < orderB) { return -1; }
			if (orderA > orderB) { return 1;  }
		  return 0;
		});
		document.record.elements[overrideField].value = JSON.stringify(overrideRules);
		vaRuleUpdateField();
		// back to the list of updated rules
		// populate window with the list of available rules
		vaRuleList();
	}

	function vaRuleDeleteConfirm(btnObj)
	{
		var listDelObj = vaParent(btnObj, ".list-delete");
		listDelObj.className += " confirm-delete";
	}

	function vaRuleDeleteCancel(btnObj)
	{
		var listDelObj = vaParent(btnObj, ".list-delete");
		listDelObj.className = listDelObj.className.replace(/confirm-delete/gi, "").trim();
	}

	function vaRuleDelete(btnObj, editId)
	{
		// get list of available rules for selected notification
		var overrideField = document.record.override_field.value;
		var overrideValue = document.record.elements[overrideField].value;
		var overrideRules = [];
		try {
			overrideRules = JSON.parse(overrideValue);	
		} catch(e) {}
		// get form data for selected rule
		if (typeof editId === "undefined") {
			var formObj = vaParent(btnObj, "form");
			editId = formObj.rule_id.value;
		}
		if (editId !== "" && overrideRules[editId]) {
			overrideRules.splice(editId, 1);
			document.record.elements[overrideField].value = JSON.stringify(overrideRules);
			vaRuleUpdateField();
		}
		vaRuleList();
	}

	function vaRuleCancel(btnObj) 
	{
		vaRuleList();
	}

	function vaRuleUpdateField()
	{
		var depId = document.record.dep_id.value;
		var overrideField = document.record.override_field.value;
		var overrideValue = document.record.elements[overrideField].value;
		var overrideLink = document.record.override_link.value;
		var overrideRules = [];
		try {
			overrideRules = JSON.parse(overrideValue);	
		} catch(e) {}
		if (vaSettings.messages) {
			var fieldMessage;
			if (overrideRules.length > 0) {
				fieldMessage = vaSettings.messages["NUMBER_RULES_MSG"].replace("[number_rules]", overrideRules.length);
			} else {
				fieldMessage = vaSettings.messages["NO_RULES_MSG"];
			}
			var linkId = document.getElementById(overrideLink);
			if (linkId) {
				linkId.innerHTML = fieldMessage;
			}
		}

		if (depId) {
			var postParams = {
				"ajax": "1", 
				"operation": "override", 
				"dep_id": depId, 
				"override_field": overrideField, 
				"override_value": overrideValue, 
			};
			postAjax("admin_support_department.php", function(){}, "", "", postParams);
		}
	}
