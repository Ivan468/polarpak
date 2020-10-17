function openShippingsWindow(windowUrl, formName, fieldName, selectionType)
{
	var queryString = "";
  if (formName != "") {
		queryString = "?form_name=" + formName;
	}
  if (fieldName != "") {
		queryString += ((queryString == "") ? "?" : "&");
		queryString += "field_name=" + fieldName;
	}
  if (selectionType != "") {
		queryString += ((queryString == "") ? "?" : "&");
		queryString += "selection_type=" + selectionType;
	}
	var shippingsWin = window.open (windowUrl + queryString, 'shippingsWin', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
	shippingsWin.focus();
}

function selectShipping(shippingId, shippingName)
{
	if (window.opener) {
		var formName  = document.shippings_list.form_name.value;
		var fieldName  = document.shippings_list.field_name.value;
		var selectionType = document.shippings_list.selection_type.value;
		window.opener.setShipping(shippingId, shippingName, formName, fieldName, selectionType);
		window.opener.focus();
	}
	window.close();
}

function closeShippingsWindow()
{
	window.opener.focus();
	window.close();
}

function setShipping(shippingId, shippingName, formName, fieldName, selectionType)
{
	var shippingAdded = false;
	for(var id in shippings)
	{
		if (id == shippingId) {
			shippingAdded = true;
		}
	}
	
	if (!shippingAdded) {
		shippings[shippingId] = new Array(shippingName);
		generateShippingsList(formName, fieldName);
	}
}

function removeShipping(shippingId, formName, fieldName)
{
	delete shippings[shippingId];
	generateShippingsList(formName, fieldName);
}

function generateShippingsList(formName, fieldName)
{
	var selectedDiv = ""; 
	var idsControl = ""; 
	var shippingsIds = "";
	selectedDiv = document.getElementById("selectedShippings");
	selectedDiv.innerHTML = "";

	for(var id in shippings)
	{
		var shippingName = shippings[id];
		var sippingInfo = "<li class=selectedCategory>" + shippingName;
		sippingInfo += " - <a href=\"#\" onClick=\"removeShipping('" + id + "', '" + formName + "', '" + fieldName + "'); return false;\">" + removeButton + "</a>";
		if (selectedDiv.insertAdjacentHTML) {
			selectedDiv.insertAdjacentHTML("beforeEnd", sippingInfo);
		} else {
			selectedDiv.innerHTML += sippingInfo;
		}
		if (shippingsIds != "") { shippingsIds += "," }
		shippingsIds += id;
	}
	idsControl = document.forms[formName].elements[fieldName];
	idsControl.value = shippingsIds;
}
