// basket javacript
function confirmDelete(itemType)
{
	var confirmMessage = removeFromCart;
	confirmMessage = confirmMessage.replace("\{item_type\}", itemType);
	return confirm(confirmMessage);
}

function confirmAllDelete(confirmMessage)
{
	return confirm(confirmMessage);
}

function changeCartQty(control)
{
	var controlTag = control.tagName;
	var cartId = control.getAttribute("data-cart-id");
	var productName = control.getAttribute("data-product-name");
	var initialQty = control.getAttribute("data-quantity");
	var newQty = control.value;
	var confirmMessage = qtyConfirmMessage(newQty, productName, initialQty);
	if (newQty != initialQty) {
		if (true) {
			qtyFormData(control);
		} else {
			control.value = initialQty;
		}
	}
}

function checkChanges(e, control)
{
	if (e.key == "Enter") {
		qtyFormData(control);
		return false;
	} else {
		return true;
	}
}

function qtyConfirmMessage(new_quantity, product_name, old_quantity)
{
	var confirmMessage = "";
  if (new_quantity < 1) {
		confirmMessage = cartQtyZero;
		confirmMessage = confirmMessage.replace("\{old_quantity\}", old_quantity);
		confirmMessage = confirmMessage.replace("\{product_name\}", product_name);
  } else {
		confirmMessage = alterCartQty;
		confirmMessage = confirmMessage.replace("\{old_quantity\}", old_quantity);
		confirmMessage = confirmMessage.replace("\{new_quantity\}", new_quantity);
		confirmMessage = confirmMessage.replace("\{product_name\}", product_name);
  }
	return confirmMessage;
}

function qtyFormData(control)
{
	var formObj = vaParent(control, "FORM");
	if (formObj) {
		var cartId = control.getAttribute("data-cart-id");
		var newQty = control.value;
    if (newQty < 1) {
			formObj.cart.value = "RM";
			formObj.cart_id.value = cartId;
			formObj.new_quantity.value = 0;
    } else {
			formObj.cart.value = "QTY";
			formObj.cart_id.value = cartId;
			formObj.new_quantity.value = newQty;
    }
		formObj.submit();
	}
}

function checkFastCheckoutDetails(pbId)
{
	requiredMessage = requiredMessage.replace("<b>", "");
	requiredMessage = requiredMessage.replace("</b>", "");
	var orderForm = document.fast_checkout;
	var errorMessage = ""; var controlObj = ""; var controlName = "";
	if (orderForm.country_required.value == "*") {
		if (orderForm.fast_checkout_country_id.options[orderForm.fast_checkout_country_id.selectedIndex].value == "") {
			controlName = "";
			controlObj = document.getElementById("fast_checkout_country_name");
			if (controlObj) { controlName = controlObj.innerHTML; }
			errorMessage += requiredMessage.replace("\{field_name\}", controlName) + ".\n";
		}
	}
	var stateObj = document.getElementById("fast_checkout_state_id_"+pbId);
	if (orderForm.state_required.value == "*" && stateObj) {
		if (stateObj.options && stateObj.options > 1 && stateObj.options[stateObj.selectedIndex].value == "") {
			controlName = "";
			controlObj = document.getElementById("fast_checkout_state_name");
			if (controlObj) { controlName = controlObj.innerHTML; }
			errorMessage += requiredMessage.replace("\{field_name\}", controlName) + ".\n";
		}
	}
	if (orderForm.postcode_required.value == "*" && orderForm.fast_checkout_postcode.value == "") {
		controlName = "";
		controlObj = document.getElementById("fast_checkout_postcode_name");
		if (controlObj) { controlName = controlObj.innerHTML; }
		errorMessage += requiredMessage.replace("\{field_name\}", controlName) + ".\n";
	}
	errorMessage = errorMessage.replace(/<b>/g, "");
	errorMessage = errorMessage.replace(/<\/b>/g, "");

	if (errorMessage != "") {
		alert(errorMessage);
		return false;
	} else {
		return true;
	}
}