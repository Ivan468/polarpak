function jsonWindow(winData) {

	var windowUrl = winData["url"];
	var windowName = (typeof winData["win_name"] !== 'win_name') ?  winData["win_name"] : "windowSelect";
	var params = winData["params"];

	var queryString = "";
	for (var paramName in params) {
		var paramValue = params[paramName];
		queryString += ((queryString == "") ? "?" : "&");
		queryString += paramName + "=" + encodeURIComponent(paramValue);
	}
	var windowSelect = window.open (windowUrl + queryString, windowName, 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600');
	windowSelect.focus();
}

function jsonSelectItem(params)
{
	if (window.opener) {
		window.opener.jsonSetItem(params);
		window.opener.focus();
	}
	window.close();
}

function jsonSetItem(params)
{
	var newItemData = params["item"];
	var formName = params["form_name"];
	if (params["items_field"]) {
		var fieldName = params["items_field"];
		var itemsObjectName = params["items_object"];
		var templateName = params["item_template"];
		var controlObj = document.getElementById(itemsObjectName);
		var templateObj = document.getElementById(templateName);
		var templateHTML = templateObj.innerHTML;
		for (var paramName in newItemData) {
			var paramValue = newItemData[paramName];
			var re = new RegExp("\\["+paramName+"\\]","g");
			templateHTML = templateHTML.replace(re, paramValue);
		}
		// add data to field
		var items = new Array();
		var itemsString = document.forms[formName].elements[fieldName].value;
		if (itemsString != "") {
			items = JSON.parse(itemsString);
		}
		// check if element already in array
		var itemExists = false;
		for (var itemIndex in items) {
			var itemData = items[itemIndex];
			if (itemData.id == newItemData.id) {
				if (params["items_object"] && params["item_object"]) {
					// if we have all necessary parameters delete object to add it again
					var itemsObject = document.getElementById(params["items_object"]);
					var itemObject = document.getElementById(params["item_object"]);
					itemsObject.removeChild(itemObject);
					items.splice(itemIndex, 1);
				} else {
					itemExists = true; 
				}
			}
		}
		if (!itemExists) {
			controlObj.innerHTML += templateHTML;
			items.push(newItemData);
		}
		document.forms[formName].elements[fieldName].value = JSON.stringify(items);
	} else if (params["item_fields"]) {
	  var itemFields = params["item_fields"].split(",");
		for (var f = 0; f < itemFields.length; f++) {
			if (itemFields[f] != "") {
				var fieldData = itemFields[f].split("=");
				var fieldName = fieldData[0]; 
				var paramName = (fieldData.length == 2) ? fieldData[1] : fieldName;
				var fieldObj = document.forms[formName].elements[fieldName];
				if (fieldObj) {
					if (fieldObj.type == "checkbox") {
						if (newItemData[paramName] == "" || newItemData[paramName] == "0") { 
							fieldObj.checked = false;
						} else {
							fieldObj.checked = true;
						}
					} else {
						fieldObj.value = newItemData[paramName];
					}
					if (fieldObj.hasAttribute("data-onchange")) {
						var onChangeEvent = fieldObj.getAttribute("data-onchange");
						window[onChangeEvent]();
					}
				}
			}
		}
	}
}


function jsonRemoveItem(params)
{
	var formName = params["form_name"];
	var fieldName = params["items_field"];
	var itemsObjectName = params["items_object"];
	var itemObjectName = params["item_object"];
	var removeId = params["id"];
	// remove visual object for item
	var itemsObject = document.getElementById(itemsObjectName);
	var itemObject = document.getElementById(itemObjectName);
	itemsObject.removeChild(itemObject);
	// remove item from JSON object
	var items = new Array();
	var itemsString = document.forms[formName].elements[fieldName].value;
	if (itemsString != "") {
		items = JSON.parse(itemsString);
	}
	for (var itemIndex in items) {
		var itemData = items[itemIndex];
		if (itemData.id == removeId) {
			items.splice(itemIndex, 1);
		}
	}
	document.forms[formName].elements[fieldName].value = JSON.stringify(items);
}

function openWindowSelect(windowUrl, formName, fieldName, idName, selectionType, listType, startId)
{
	var queryString = "";
  if (formName != "") {
		queryString = "?form_name=" + formName;
	}
  if (fieldName != "") {
		queryString += ((queryString == "") ? "?" : "&");
		queryString += "field_name=" + fieldName;
	}
  if (idName != "") {
		queryString += ((queryString == "") ? "?" : "&");
		queryString += "id_name=" + idName;
	}
  if (selectionType != "") {
		queryString += ((queryString == "") ? "?" : "&");
		queryString += "selection_type=" + selectionType;
	}
  if (listType && listType != "") {
		queryString += ((listType == "") ? "?" : "&");
		queryString += "list_type=" + listType;
	}
  if (startId && startId != "") {
		queryString += ((queryString == "") ? "?" : "&");
		queryString += "start_id=" + startId;
	}
	var windowSelect = window.open (windowUrl + queryString, 'windowSelect', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
	windowSelect.focus();
}

function selectItem(itemId, itemName)
{
	if (window.opener) {
		var formName  = document.form_list.form_name.value;
		var fieldName  = document.form_list.field_name.value;
		var idName  = document.form_list.id_name.value;
		var selectionType = document.form_list.selection_type.value;
		window.opener.setItem(itemId, itemName, formName, fieldName, idName, selectionType);
		window.opener.focus();
	}
	window.close();
}

function closeWindowSelect()
{
	window.opener.focus();
	window.close();
}

function setItem(itemId, itemName, formName, fieldName, idName, selectionType)
{
	if (selectionType == "single") {
		// used only for user select on some forms
		var idControl = document.forms[formName].elements[fieldName];
		var itemNameObj = document.getElementById(idName);
		idControl.value = itemId;
		var itemInfo = "<a href=\""+userViewLink+"?user_id="+itemId+"\" class=\"title\" target=\"_blank\">"+itemName+"</a>";
		itemInfo += " (#"+itemId+") - <a href=\"#\" onClick=\"removeSingleItem('" + id + "', '" + formName + "', '" + fieldName + "', '" + idName + "'); return false;\">";
		itemInfo += removeButton + "</a> | ";
		itemNameObj.innerHTML = itemInfo;
	} else if (selectionType == "parent_category") {
		// to select parent category on different forms
		// set control value
		var idControl = document.forms[formName].elements[fieldName];
		idControl.value = itemId;
		// set control description
		var descControl = document.getElementById(idName);
		var descHidden = document.getElementById(idName+"_hidden");
		var descHTML = descHidden.innerHTML;
		descHTML = descHTML.replace("\["+idName+"_id\]", itemId);
		descHTML = descHTML.replace("\["+idName+"value\]", itemId);
		descHTML = descHTML.replace("\["+idName+"_desc\]", itemName);
		descControl.innerHTML = descHTML;
		descControl.style.display = "inline";
	} else if (selectionType == "control" || selectionType == "param") {
		// for single value selection on different forms
		// set control value
		var idControl = document.forms[formName].elements[fieldName];
		idControl.value = itemId;
		// set control description
		var descControl = document.getElementById(idName);
		var descHidden = document.getElementById(idName+"_hidden");
		var descHTML = descHidden.innerHTML;
		descHTML = descHTML.replace("\["+idName+"_id\]", itemId);
		descHTML = descHTML.replace("\["+idName+"value\]", itemId);
		descHTML = descHTML.replace("\["+idName+"_desc\]", itemName);
		descControl.innerHTML = descHTML;
		descControl.style.display = "inline";
	} else {
		var itemAdded = false;
		var itemsArray = items[fieldName];
		for(var id in itemsArray)
		{
			if (id == itemId) {
				itemAdded = true;
			}
		}
		
		if (!itemAdded) {
			// add new item to global array
			items[fieldName][itemId] = itemName;
			generateItemsList(formName, fieldName, idName);
		}
	}
}

function removeSingleItem(itemId, formName, fieldName, idName)
{
	var idControl = document.forms[formName].elements[fieldName];
	var itemNameObj = document.getElementById(idName);
	idControl.value = "";
	itemNameObj.innerHTML = "";
}

function clearControlValue(formName, fieldName, idName)
{
	var idControl = document.forms[formName].elements[fieldName];
	var descControl = document.getElementById(idName);
	idControl.value = "";
	descControl.innerHTML = "";
	descControl.style.display = "none";
}

function clearParentCategory(formName, fieldName, idName)
{
	var categoryName = (typeof parentCategoryName === 'undefined') ? "[[Top]]" : parentCategoryName;
	var categoryId = (typeof parentCategoryId === 'undefined') ? "0" : parentCategoryId;

	var idControl = document.forms[formName].elements[fieldName];
	var descControl = document.getElementById(idName);
	idControl.value = categoryId;
	descControl.innerHTML = categoryName;
}

function removeItem(itemId, formName, fieldName, idName)
{
	delete items[fieldName][itemId];
	generateItemsList(formName, fieldName, idName);
}

function generateItemsList(formName, fieldName, idName)
{
	var idsControl = ""; var itemsIds = "";
	var selectedDiv = document.getElementById(idName);; 
	var itemsArray = items[fieldName]; 
	selectedDiv.innerHTML = "";
	for(var id in itemsArray)
	{
		var itemName = itemsArray[id];
		var itemInfo = "<li class=selectedCategory>" + itemName;
		itemInfo += " - <a href=\"#\" onClick=\"removeItem('" + id + "', '" + formName + "', '" + fieldName + "', '" + idName + "'); return false;\">";
		itemInfo += removeButton + "</a>";
		if (selectedDiv.insertAdjacentHTML) {
			selectedDiv.insertAdjacentHTML("beforeEnd", itemInfo);
		} else {
			selectedDiv.innerHTML += itemInfo;
		}
		if (itemsIds != "") { itemsIds += "," }
		itemsIds += id;
	}
	idsControl = document.forms[formName].elements[fieldName];
	idsControl.value = itemsIds;
}
