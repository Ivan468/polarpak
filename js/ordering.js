// global variables
var vaLoaded = {}; // array to save loaded scripts which should be loaded only once

function vaLoadScripts(vaScripts)
{
	if (vaScripts.length > 0) {
		var ps = document.getElementsByTagName('head')[0]; // parent element where we will add our scripts
		var vaScript = vaScripts.shift();
		var scriptCode = ""; var scriptURL = ""; var scriptOnce = false;
		if (vaScript.code || vaScript.url) {
			if (vaScript.code) { scriptCode = vaScript.code; }
			if (vaScript.url) { 
				scriptURL = vaScript.url; 
				if (vaScript.once && (vaScript.once == "1" || vaScript.once == true)) { scriptOnce = true; }
			}
		} else if (typeof vaScript == 'string') {
			var regExp = /\.js$/i;
			if (vaScript.match(regExp)) {
				scriptURL = vaScript; 
			} else {
				scriptCode = vaScript; 
			}
		}

		if (scriptCode) {
			var js = document.createElement('script');
		  js.text = scriptCode;
			ps.parentNode.appendChild(js);
		}

		if (scriptURL && !vaLoaded[scriptURL]) {
			var js = document.createElement('script');
		  js.setAttribute("type","text/javascript")
		  js.setAttribute("src", scriptURL)
			js.onload = function() {
				vaLoadScripts(vaScripts);
				if (scriptOnce) {
					vaLoaded[scriptURL] = true;
				}
			}
			js.onerror = function() { 
        alert('Loading error: ' + scriptURL);
			}
			ps.parentNode.appendChild(js);
		} else {
			vaLoadScripts(vaScripts);
		}
	}
}

function vaOPC()
{
	var orderForm = document.order_info;
	var opcType = (orderForm.opc_type) ? orderForm.opc_type.value : "steps";
	if (opcType == "single") {
		if (orderForm.delivery_country_id) {
			orderForm.delivery_country_id.addEventListener("change", changeCountry, false);
		} else if (orderForm.country_id) {
			orderForm.country_id.addEventListener("change", changeCountry, false);
		}
		if (orderForm.delivery_state_id) {
			orderForm.delivery_state_id.addEventListener("change", changeState, false);
		} else if (orderForm.state_id) {
			orderForm.state_id.addEventListener("change", changeState, false);
		}
		if (orderForm.delivery_zip) {
			orderForm.delivery_zip.addEventListener("change", changeZip, false);
		} else if (orderForm.zip) {
			orderForm.zip.addEventListener("change", changeZip, false);
		}
	}
}

// shopping javacript
function checkOrder(orderForm)
{
	var prMessage = "{REQUIRED_PROPERTY_MSG}";
	var prIDs = orderForm.properties.value;
	if (prIDs != "") {
		var properties = prIDs.split(",");
		for ( var i = 0; i < properties.length; i++) {
			var prID = properties[i];
			var cp = prID.split("_");
			var cartID = "";
			if (cp.length == 4) {
				cartID = cp[0] + "_" + cp[1] + "_" + cp[2];
			} else {
				cartID = cp[0];
			}
			if (orderForm.elements["property_required_" + prID] && orderForm.elements["property_required_" + prID].value == 1) {
				var productName = orderForm.elements["item_name_" + cartID].value;
				var prValue = "";
				var prControl = orderForm.elements["property_control_" + prID].value;
				if (prControl == "LISTBOX") {
					prValue = orderForm.elements["property_" + prID].options[orderForm.elements["property_" + prID].selectedIndex].value;
				} else if (prControl == "RADIOBUTTON") {
					var radioControl = orderForm.elements["property_" + prID];
					if (radioControl.length) {
						for ( var ri = 0; ri < radioControl.length; ri++) {
							if (radioControl[ri].checked) {
								prValue = radioControl[ri].value;
								break;
							}
						}
					} else {
						if (radioControl.checked) {
							prValue = radioControl.value;
						}
					}
				} else if (prControl == "CHECKBOXLIST") {
					if (orderForm.elements["property_total_" + prID]) {
						var totalOptions = parseInt(orderForm.elements["property_total_" + prID].value);
						for ( var ci = 1; ci <= totalOptions; ci++) {
							if (orderForm.elements["property_" + prID + "_" + ci].checked) {
								prValue = 1;
								break;
							}
						}
					}
				} else {
					prValue = orderForm.elements["property_" + prID].value;
				}
				if (prValue == "") {
					var propertyName = orderForm.elements["property_name_" + prID].value;
					prMessage = prMessage.replace("\{property_name\}", propertyName);
					prMessage = prMessage.replace("\{product_name\}", productName);
					alert(prMessage);
					if (prControl != "RADIOBUTTON" && prControl != "CHECKBOXLIST") {
						orderForm.elements["property_" + prID].focus();
					}
					return false;
				}
			}
		}
	}


	// check if all shipments selected
	var shippingError = "{REQUIRED_DELIVERY_JS}";
	var shippingIndex = 1; 
	// read shipping groupds 
	while (orderForm.elements["shipping_type_id_"+shippingIndex]) {
		var shippingTypeId  = ""; 
		var shippingObj = orderForm.elements["shipping_type_id_"+shippingIndex];
		var shippingControl = (shippingObj.length) ? shippingObj[0].type : shippingObj.type;
		if (shippingControl == "select-one") {
			shippingTypeId = shippingObj.options[shippingObj.selectedIndex].value;
		} else if (shippingControl == "radio") { 
			for(var i = 0; i < shippingObj.length; i++) {
				var radioShippingId = shippingObj[i].value;
				if (shippingObj[i].checked) {
					shippingTypeId = radioShippingId;
				}
			}
		} else {
			shippingTypeId = shippingObj.value;
		}

		if (shippingTypeId == "") {
			alert(shippingError);
			if (shippingControl == "select-one") {
				shippingObj.focus();
			} else if (shippingControl == "radio") { 
				shippingObj[0].focus();
			}
			return false;
		}

		// get next index
		shippingIndex++;
	}

	orderForm.operation.value = 'save';
	return true;
}

function loadCheckoutBlock(response, pbId)
{
	var data; // save here parsed data
	try {
		data = JSON.parse(response);
	} catch(e) {
		alert(e + "\n" + response); 
		return;
	}

	var formObj = document.order_info;
	var orderData = safeJsonParse("order_info", "order_data");
	var activeStep = formObj.active_step.value;
	var nextStep = formObj.next_step.value;
	var opcType = (formObj.opc_type) ? formObj.opc_type.value : "steps";
	// always update payment systems data for payment step
	if (activeStep == "payment" || nextStep == "payment") {
		if (formObj.order_data && data.payment_systems) {
			orderData["payment_systems"] = data.payment_systems;
			formObj.order_data.value = JSON.stringify(orderData);
		}
	}

	// check for redirect first
	if (data.location) {
		// php script return a redirect 
		window.location = data.location;
		return;
	}

	// re-activate disabled continue button
	vaStopSpin(activeStep+"Continue");
	activateContinueButton(activeStep);

	// check if current step doesn't return any errors
	if (data.errors) {
		var errorsStep = data.step;
		var errorsBlock = document.getElementById(errorsStep+"Errors");
		errorsBlock.innerHTML = data.errors;
		errorsBlock.className = "errors";
		if (opcType == "single") {
			var blockId = errorsStep+"Errors";
			location.hash = blockId;
		} else if (activeStep != errorsStep) {
			// if for some reason errors happen on previous step reopen it
			reopenStep(errorsStep);
		} else if (activeStep == "payment" && data.block) {
			// refresh active payment step with errors
			var blockContent = data.block;
			var activeBlock = document.getElementById(activeStep+"Step");
			var parentObj = activeBlock.parentNode;
			var divObj = document.createElement('div'); 
			// trim data to correctly get main div object
			blockContent = blockContent.replace(/^\s+|\s+$/g, "");
			divObj.innerHTML = blockContent; 
			var newBlockObj = divObj.firstChild;
			parentObj.replaceChild(newBlockObj, activeBlock);
		}
		return;
	} else if (data.form) {
		// get or create payment form element first
		var formObj = document.payment;
		if (!formObj) {
			formObj = document.createElement("form");
			formObj.name = "payment";
			document.body.appendChild(formObj);
		}
		// set form attributes 
		formObj.method = (data.form.method) ? data.form.method : "post";
		formObj.action = data.form.url;
		formObj.className = data.form.class;
		// load scripts if available
		if (data.form.scripts) {
			vaLoadScripts(data.form.scripts);
		}
		// clear all previous hidden controls and add new
		var hiddenObjs = formObj.querySelectorAll("input[type=hidden]");
		for (var h = 0; h < hiddenObjs.length; h++) {
			formObj.removeChild(hiddenObjs[h]);
		}
		// add new hidden form params
		var formParams = data.form.params;
		for (paramName in formParams) {
			var inputObj = document.createElement("input"); 
			inputObj.type = "hidden";
			inputObj.name = paramName;
			inputObj.value = formParams[paramName];
			formObj.appendChild(inputObj);  
		}
		// update HTML code for the form
		formHTMLObj = formObj.querySelector(".form-html");
		if (!formHTMLObj) {
			formHTMLObj= document.createElement("div");
			formHTMLObj.className = "form-html";
			formObj.appendChild(formHTMLObj);
		}
		formHTMLObj.innerHTML = (data.form.html) ? data.form.html : "";

		if (data.form.auto) {
			formObj.submit()
		}
		return;
	}

	// show next step block
	var blockContent = data.block;
	var nextBlock = document.getElementById(nextStep+"Step");
	var parentObj = nextBlock.parentNode;
	var divObj = document.createElement('div'); 
	// trim data to correctly get main div object
	blockContent = blockContent.replace(/^\s+|\s+$/g, "");
	divObj.innerHTML = blockContent; 
	var newBlockObj = divObj.firstChild;
	parentObj.replaceChild(newBlockObj, nextBlock);

	// get updated block object and get it height to open it slowly
	nextBlock = document.getElementById(nextStep+"Step");
	nextBlock.className = "active";
	var nextBlockHeight = nextBlock.offsetHeight;
	nextBlock.className = "closed";
	// set initial next block height 
	var closedHeight = nextBlock.offsetHeight;
	nextBlock.style.height = closedHeight +"px";

	if (nextStep == "payment") {
		// clear credit card number and code when payment step opened
		if (formObj.cc_number) { formObj.cc_number.value = ""; }
		if (formObj.cc_security_code) { formObj.cc_security_code.value = ""; }
	}

	changeBlocks(activeStep, closedHeight, nextStep, nextBlockHeight);
}

function activateContinueButton(activeStep)
{
	var formObj = document.order_info;
	var continueObj = document.getElementById(activeStep+"Continue");
	continueObj.style.opacity = "1";

	var buttonObj = document.getElementById(activeStep+"Button");
	var buttonValue = formObj.continue_button.value
	buttonObj.value = buttonValue;
}

function nextCheckoutStep(activeStep, nextStep)
{
	var isAjax = GetXmlHttpObject();
	var formObj = document.order_info;
	// check if it's a saved user order
	var userOrder = formObj.user_order.value;
	// save steps in hidden controls 
	formObj.active_step.value = activeStep;
	formObj.next_step.value = nextStep;
	if (nextStep == "final") {
		// for last step save/update order
		formObj.operation.value = "save";
	} else {
		formObj.operation.value = "next";
	}
	if (!isAjax) {
		// if Ajax couldn't be called use usual submit method
		formObj.ajax.value = 0;
		formObj.submit();
		return;
	} else {
		// for Ajax call set special value
		formObj.ajax.value = 1;
	}

	// hide active step error
	//var errorsBlock = document.getElementById(activeStep+"Errors");
	//errorsBlock.className = "hidden";
	// hide all errors before next check
	var errorsBlock = document.getElementById("cartErrors");
	if (errorsBlock) { errorsBlock.className = "hidden"; }
	errorsBlock = document.getElementById("userErrors");
	if (errorsBlock) { errorsBlock.className = "hidden"; }
	errorsBlock = document.getElementById("shippingErrors");
	if (errorsBlock) { errorsBlock.className = "hidden"; }
	errorsBlock = document.getElementById("paymentErrors");
	if (errorsBlock) { errorsBlock.className = "hidden"; }


	// some JS validation rules for current step
	if (activeStep == "user" && userOrder != "1") {
		// profile checks
		if (typeof personalFields !== 'undefined') {
			for (var fieldName in personalFields) {
				var fieldData = personalFields[fieldName];
				var fieldType = ""; var fieldDisplay = ""; var fieldObj = ""; var fieldValue = "";
				if (fieldData.type) {
					fieldType = fieldData.type;
				}
				if (fieldType == "checkboxlist") {
					fieldObj = formObj.elements[fieldName+"_1"];
				} else {
					fieldObj = formObj.elements[fieldName];
				}
				if (fieldObj) {
					if (fieldType == "") {
						fieldType = formObj.elements[fieldName].type;
					}
					if ((fieldType == "radio" || fieldType == "radiobutton") && fieldObj.length) {
						fieldDisplay = window.getComputedStyle(fieldObj[0],null).getPropertyValue("display");
					} else {
						fieldDisplay = window.getComputedStyle(fieldObj,null).getPropertyValue("display");
					}
				}
				if (fieldType == "text" || fieldType == "select-one" || fieldType == "textbox" || fieldType == "textarea" || fieldType == "listbox") {
					fieldValue = formObj.elements[fieldName].value;
				} else if (fieldType == "checkbox") {
					if (formObj.elements[fieldName].checked) {
						fieldValue = formObj.elements[fieldName].value;
					}
				} else if (fieldType == "radio" || fieldType == "radiobutton") {
					var radioControl = formObj.elements[fieldName];
					if (radioControl.length) {
						for ( var ri = 0; ri < radioControl.length; ri++) {
							if (radioControl[ri].checked) {
								fieldValue = radioControl[ri].value;
								break;
							}
						}
					} else {
						if (radioControl.checked) {
							fieldValue = radioControl.value;
						}
					}
				} else if (fieldType == "checkboxlist") {
					var ci = 1;
					while (formObj.elements[fieldName+"_"+ci]) {
						if (formObj.elements[fieldName+"_"+ci].checked) {
							fieldValue = formObj.elements[fieldName+"_"+ci].value;
							break
						}
						ci++;
					}
				} else {
					fieldValue = 1;
				}
				if (fieldData.required == 1 && fieldValue == "" && fieldDisplay != "" && fieldDisplay != "none") {
					if (fieldData.required_message) {
						alert(fieldData.required_message);
					} else {
						alert(fieldData.name);
					}
					// check element
					if (fieldType == "checkboxlist") {
						formObj.elements[fieldName+"_1"].focus();
					} else if (formObj.elements[fieldName].length) {
						formObj.elements[fieldName][0].focus();
					} else {
						formObj.elements[fieldName].focus();
					}
					return false;
				}
			}
		}
	} else if (activeStep == "shipping" && userOrder != "1") {
		// check if all shipments selected
		var shippingError = "{REQUIRED_DELIVERY_JS}";
		var shippingIndex = 1; 
		// read shipping groupds 
		while (formObj.elements["shipping_type_id_"+shippingIndex]) {
			var shippingTypeId  = ""; 
			var shippingObj = formObj.elements["shipping_type_id_"+shippingIndex];
			var shippingControl = (shippingObj.length) ? shippingObj[0].type : shippingObj.type;
			if (shippingControl == "select-one") {
				shippingTypeId = shippingObj.options[shippingObj.selectedIndex].value;
			} else if (shippingControl == "radio") { 
				for(var i = 0; i < shippingObj.length; i++) {
					var radioShippingId = shippingObj[i].value;
					if (shippingObj[i].checked) {
						shippingTypeId = radioShippingId;
					}
				}
			} else {
				shippingTypeId = shippingObj.value;
			}
  
			if (shippingTypeId == "") {
				alert(shippingError);
				if (shippingControl == "select-one") {
					shippingObj.focus();
				} else if (shippingControl == "radio") { 
					shippingObj[0].focus();
				}
				return false;
			}
  
			// get next index
			shippingIndex++;
		}
	} else if (activeStep == "payment") {
		// check if payment 
	}

	var continueObj = document.getElementById(activeStep+"Continue");
	continueObj.style.opacity = "0.7";

	var buttonObj = document.getElementById(activeStep+"Button");
	var buttonWidth = buttonObj.offsetWidth;
	formObj.continue_button.value = buttonObj.value;
	buttonObj.style.width = buttonWidth + "px";
	buttonObj.value = "";
	vaSpin(activeStep+"Continue", activateContinueButton, activeStep);

	// load next checkout block
	var pbId = formObj.pb_id.value;
	var url = "block.php?pb_id=" + encodeURIComponent(pbId);
	postAjax(url, loadCheckoutBlock, pbId, formObj);

}

function changeBlocks(activeStep, closedHeight, nextStep, nextBlockHeight)
{
	var nextCall = false; 
	var isMobile = "";
	if (document.order_info.is_mobile) {
		isMobile = document.order_info.is_mobile.value;
	}
	var disableAnimation = "";
	if (document.order_info.disable_animation) {
		instantChange = document.order_info.disable_animation.value;
	}
	if (isMobile == "1" || disableAnimation == "1") {
		// quick change
		var obj = document.getElementById(activeStep+"Step");
		var nextObj = document.getElementById(nextStep+"Step");
		obj.className = "closed";
		obj.style.height = "";
		nextObj.className = "active";
		nextObj.style.height = "";
		setActiveStep(nextStep);
		return;
	}
	//var titleObj = document.getElementById(activeStep+"Title");
	//var titleHeight = titleObj.offsetHeight; // get title height

	// close active block 
	var obj = document.getElementById(activeStep+"Step");
	obj.className = "moving";
	var currentHeight = obj.offsetHeight;
	if (currentHeight > closedHeight) {
		if (currentHeight > (closedHeight + 20)) {
			currentHeight -= 20;
		} else {
			currentHeight = closedHeight;
		}
		obj.style.height = currentHeight+"px";
		if (currentHeight > closedHeight) { 
			nextCall = true; 
		} else {
			obj.className = "closed";
		}
	} else {
		obj.className = "closed";
	}

	// open next block
	var nextObj = document.getElementById(nextStep+"Step");
	nextObj.className = "moving";
	currentHeight = nextObj.offsetHeight;
	if (currentHeight <= nextBlockHeight) {
		if (currentHeight < (nextBlockHeight - 20)) {
			currentHeight += 20;
		} else {
			currentHeight = nextBlockHeight;
		}
		nextObj.style.height = currentHeight+"px";
		if (currentHeight < nextBlockHeight) { 
			nextCall = true; 
		} else {
			nextObj.className = "active";
		}

	} else {
		nextObj.className = "active";
	}

	// check if we need to call this function again
	if (nextCall) {
		setTimeout("changeBlocks('" + activeStep + "',"+closedHeight+",'"+nextStep+"',"+nextBlockHeight+")", 25);
	} else {
		// remove any temp style attributes
		obj.style.height = "";
		nextObj.style.height = "";
		setActiveStep(nextStep);
	}

}

function setActiveStep(activeStep)
{
	var formObj = document.order_info;
	formObj.active_step.value = activeStep;
	formObj.next_step.value = "";
	var steps = {
		'cart': '1', 
		'user': '2', 
		'coupon': '3', 
		'shipping': '4', 
		'payment': '5', 
		'review': '6', 
	};
	var activeStepOrder = steps[activeStep];
	for (stepName in steps) {
		var stepOrder = steps[stepName];
		var stepObj = document.getElementById(stepName+"Step");
		var linkObj = document.getElementById(stepName+"Link");
		if (stepObj) {
			if (stepOrder < activeStepOrder) {
				stepObj.className = "closed";
				linkObj.onclick = new Function("reopenStep('"+stepName+"')");
			} else if (stepOrder == activeStepOrder) {
				stepObj.className = "active";
				linkObj.onclick = "";
			} else if (stepOrder > activeStepOrder) {
				stepObj.className = "inactive";
				linkObj.onclick = "";
			}
		}
	}
}

function reopenStep(nextStep)
{
	var formObj = document.order_info;
	// get active step
	var activeStep = formObj.active_step.value;
	// set next step
	formObj.next_step.value = nextStep;
	if (nextStep == "payment") {
		// clear credit card number and code when payment step opened
		if (formObj.cc_number) { formObj.cc_number.value = ""; }
		if (formObj.cc_security_code) { formObj.cc_security_code.value = ""; }
	}

	// hide all errors
	var errorsBlock = document.getElementById(activeStep+"Errors");
	errorsBlock.className = "hidden";

	// get updated block object and get it height to open it slowly
	nextBlock = document.getElementById(nextStep+"Step");
	nextBlock.className = "active";
	var nextBlockHeight = nextBlock.offsetHeight;
	nextBlock.className = "closed";
	// set initial height 
	var closedHeight = nextBlock.offsetHeight;
	nextBlock.style.height = closedHeight +"px";

	changeBlocks(activeStep, closedHeight, nextStep, nextBlockHeight);
}

function closeBlock(activeStep)
{
	// get title height
	var titleObj = document.getElementById(activeStep+"Title");
	var titleHeight = titleObj.offsetHeight;

	var obj = document.getElementById(activeStep+"Step");
	obj.className = "moving";
	var currentHeight = obj.offsetHeight;
	if (currentHeight > titleHeight) {
		if (currentHeight > (titleHeight + 20)) {
			currentHeight -= 20;
		} else {
			currentHeight = titleHeight;
		}
		obj.style.height = currentHeight+"px";
		setTimeout("closeBlock('" + activeStep + "')", 25);
	} else {
		obj.className = "closed";
	}
}
			
function changeOrderProperty()
{
	calculateOrder();
}

function changeShipping()
{
	calculateOrder();
}

function changeShippingList()
{
	calculateOrder();
}

function calculateItems()
{
	calculateOrder();
}

function changePayment()
{
	var orderForm = document.order_info;
	var isMobile = 0;
	if (document.order_info.is_mobile) {
		isMobile = document.order_info.is_mobile.value;
	}
	// get paymentId
	var paymentId = "";
	if (orderForm.payment_id)	{
		if (orderForm.payment_id.options) {
			// select control
			paymentId = orderForm.payment_id.options[orderForm.payment_id.selectedIndex].value;
		} else if (orderForm.payment_id.length > 0) {
			// radio control
			for (var i = 0; i < orderForm.payment_id.length; i++) {
				if (orderForm.payment_id[i].checked) {
					paymentId = orderForm.payment_id[i].value;
					break;
				}
			}
		} else {
			// hidden control
			paymentId = orderForm.payment_id.value;
		}
	}
	if (paymentId) {
		// check if variable with data available
		var paymentData; var paymentFields;
		if (orderForm.payment_data && orderForm.payment_data.value != "") {
			try { 
				paymentData = JSON.parse(orderForm.payment_data.value); 
				paymentFields = paymentData[paymentId]["fields"];
			} catch(e) { }
		}
		// hide/show fields
		var paymentInfo = paymentData[paymentId]["payment_info"];
		var fieldObj = orderForm.querySelector(".fd-payment-info");
		if (!fieldObj) { 
			fieldObj = document.getElementById("payment_info");
		}
		if (fieldObj) {
			if (paymentInfo) {
				controlObj = fieldObj.querySelector(".control");
				if (controlObj) {
					controlObj.innerHTML = paymentInfo;
					fieldObj.style.display = "block";
				}
			} else {
				fieldObj.style.display = "none";
			}
		}
		for(fieldName in paymentFields) {
			var fieldObj = document.getElementById(fieldName);
			var requiredObj = document.getElementById(fieldName+"_required");
			var fieldInfo = paymentFields[fieldName];
			var showField = "0"; var fieldRequired = "0"; 
			if (fieldInfo) {
				if (fieldInfo["show"]) {
					showField = fieldInfo["show"];	
				}
				if (fieldInfo["required"]) {
					fieldRequired = fieldInfo["required"];	
				}
			}
			if (fieldObj) {
				if (showField == "1") {
					fieldObj.style.display = "block";
				} else {
					fieldObj.style.display = "none";
				}
			}
			if (requiredObj) {
				if (fieldRequired == "1") {
					requiredObj.style.display = "inline";
				} else {
					requiredObj.style.display = "none";
				}
			}
		}
	}

	calculateOrder();
}

function calculateOrder()
{
	var orderForm = document.order_info;
	var orderData = safeJsonParse("order_info", "order_data");
	var userOrder = orderForm.user_order.value;
	// initiliaze variables with shop settings
	var pricesType = parseFloat(orderForm.tax_prices_type.value);
	if (isNaN(pricesType)) { pricesType = 0; }
	var pointsRate = parseFloat(orderForm.points_rate.value);
	if (isNaN(pointsRate)) { pointsRate = 1; }
	var priceObj = ""; 

	// initialize array for total values
	var totalValues = new Array();

	// get tax rates 
	var taxRates = prepareData("tax_rates", "tax_id=");

	// calculate order goods
	var goodsTotal = 0; var goodsPoints = 0; var goodsInclTax = 0;

	// get all order items 
	var orderItems = prepareData("order_items", "cart_item_id=");
	if (orderItems instanceof Array) {
		for (cartId in orderItems) {
			if(!(orderItems[cartId] instanceof Function)){
				var orderItem = orderItems[cartId];
				var subcomponentsShowType = orderItem["subcomponents_show_type"];
				var parentCartId = orderItem["parent_cart_id"];
				var quantity = orderItem["quantity"];
				// check pay points variable
				var payPoints = 0;
				if (subcomponentsShowType == 1 && parentCartId != "") {
					if (orderForm.elements["pay_points_" + parentCartId] && orderForm.elements["pay_points_" + parentCartId].checked) {
						payPoints = 1;
					}
				} else if (orderForm.elements["pay_points_" + cartId] && orderForm.elements["pay_points_" + cartId].checked) {
					payPoints = 1;
				}
				if (payPoints != 1) {
					var price = orderItem["price"];
					var itemQuantity = orderItem["quantity"];
					var itemTypeId = orderItem["item_type_id"];
					var itemTaxId = orderItem["tax_id"];
					var taxFreeOption = orderItem["tax_free"];
	
					var priceTotal = Math.round(price * quantity * 100) / 100;
					var itemTaxes = getTaxAmount(taxRates, itemTypeId, priceTotal, itemQuantity, itemTaxId, taxFreeOption, 2) 
					var priceTax = getTaxAmount(taxRates, itemTypeId, priceTotal, itemQuantity, itemTaxId, taxFreeOption, 1) 
					taxRates = addTaxValues(taxRates, itemTaxes, "goods");
	
					goodsTotal += priceTotal;
				} else {
					var pointsPrice = orderItem["points_price"];
					goodsPoints += (pointsPrice * quantity);
				}
			}
		}
		// check total values
		totalValues = calculateTotals(totalValues, goodsTotal, taxRates, "goods")
		goodsInclTax = totalValues["goods_incl_tax"];
		var goodsTotalControl = document.getElementById("goods_total_excl_tax");
		if (goodsTotalControl) {
			goodsTotalControl.innerHTML = orderCurrency(totalValues["goods_excl_tax"]);
		}
		var goodsTaxControl = document.getElementById("goods_tax_total");
		if (goodsTaxControl) {
			goodsTaxControl .innerHTML = orderCurrency(totalValues["goods_tax"]);
		}
		var goodsTotalInclTaxControl = document.getElementById("goods_total_incl_tax");
		if (goodsTotalInclTaxControl) {
			goodsTotalInclTaxControl.innerHTML = orderCurrency(totalValues["goods_incl_tax"]);
		}
	}
	// end of order goods calculations

	// calculate order properties
	var totalPropertiesPrice = 0; var totalPropertiesPoints = 0; var orderProperties = ""; var propertiesInclTax = 0;
	if (orderForm.order_properties) { orderProperties = orderForm.order_properties.value; }
	if (orderProperties != "") {

		var properties = orderProperties.split(",");
		for ( var i = 0; i < properties.length; i++) {
			var prID = properties[i];
			var prValue = "";
			var prValues = [];
			var propertyPrice = 0;
			var prPayPoints = 0;
			if (orderForm.elements["property_pay_points_" + prID] && orderForm.elements["property_pay_points_" + prID].checked) {
				prPayPoints = 1;
			}
			var prControl = orderForm.elements["op_control_" + prID].value;
			var taxFreeOption = parseInt(orderForm.elements["op_tax_free_" + prID].value);
			if (prControl == "LISTBOX") {
				prValue = orderForm.elements["op_" + prID].options[orderForm.elements["op_" + prID].selectedIndex].value;
				prValues.push(prValue);
			} else if (prControl == "RADIOBUTTON") {
				var radioControl = orderForm.elements["op_" + prID];
				if (radioControl.length) {
					for ( var ri = 0; ri < radioControl.length; ri++) {
						if (radioControl[ri].checked) {
							prValue = radioControl[ri].value;
							prValues.push(prValue);
							break;
						}
					}
				} else {
					if (radioControl.checked) {
						prValue = radioControl.value;
						prValues.push(prValue);
					}
				}
			} else if (prControl == "CHECKBOXLIST") {
				if (orderForm.elements["op_total_" + prID]) {
					var totalOptions = parseInt(orderForm.elements["op_total_" + prID].value);
					for ( var ci = 1; ci <= totalOptions; ci++) {
						if (orderForm.elements["op_" + prID + "_" + ci].checked) {
							prValue = orderForm.elements["op_" + prID + "_" + ci].value;
							prValues.push(prValue);
						}
					}
				}
			}
			// calculated property price for selected options
			for (var p = 0; p < prValues.length; p++) {
				prValue = prValues[p];
				if (orderForm.elements["op_option_price_" + prValue]) {
					var optionPrice = parseFloat(orderForm.elements["op_option_price_" + prValue].value);
					if (!isNaN(optionPrice) && optionPrice != 0) {
						propertyPrice += parseFloat(optionPrice);
					}
				}
			}
			// check if user select to pay with points
			if (prPayPoints == 1) {
				if (propertyPrice > 0) {
					totalPropertiesPoints += (propertyPrice * pointsRate);
				}
				propertyPrice = 0;
			}
			
			if (isNaN(propertyPrice)) { propertyPrice = 0; }
			var propertiesTaxes = getTaxAmount(taxRates, "properties", propertyPrice, 1, 0, taxFreeOption, 2) 
			var propertyTax = getTaxAmount(taxRates, "properties", propertyPrice, 1, 0, taxFreeOption, 1) 
			taxRates = addTaxValues(taxRates, propertiesTaxes, "properties");
			totalPropertiesPrice += propertyPrice;
			var propertyPrices = calculatePrices(propertyPrice, propertyTax);

			var priceControl = document.getElementById("op_price_excl_tax_" + prID);
			if (priceControl) {
				if (propertyPrice == 0) {
					priceControl.innerHTML = "";
				} else {
					priceControl.innerHTML = orderCurrency(propertyPrices["excl_tax"]);
				}
			}
			var taxControl = document.getElementById("op_tax_" + prID);
			if (taxControl) {
				if (propertyPrice == 0) {
					taxControl.innerHTML = "";
				} else {
					taxControl.innerHTML = orderCurrency(propertyTax);
				}
			}
			var priceInclTaxControl = document.getElementById("op_price_incl_tax_" + prID);
			if (priceInclTaxControl) {
				if (propertyPrice == 0) {
					priceInclTaxControl.innerHTML = "";
				} else {
					priceInclTaxControl.innerHTML = orderCurrency(propertyPrices["incl_tax"]);
				}
			}
		}
		// check total values
		totalValues = calculateTotals(totalValues, totalPropertiesPrice, taxRates, "properties")
		propertiesInclTax= totalValues["properties_incl_tax"];
	}
	// end of properties calculations

	// calculate shipping
	var shippingTotalCost = 0;
	var shippingTotalPoints = 0;
	var shippingIndex = 1; var shippingObj = "";
	var shippingInclTax = 0;
	// read shipping groups 
	var shippingTypeIds = new Array(); var shippingModuleIds = new Array();
	while (orderForm.elements["shipping_type_id_"+shippingIndex]) {
		var shippingTypeId  = ""; 
		var shippingModuleId  = ""; 
		var shippingCost = 0; var shippingPoints = 0; var shippingPayPoints = 0;
		var shippingObj = orderForm.elements["shipping_type_id_"+shippingIndex];
		var shippingControl = (shippingObj.length) ? shippingObj[0].type : shippingObj.type;
		if (shippingControl == "select-one") {
			var optionObj = shippingObj.options[shippingObj.selectedIndex];
			shippingTypeId = optionObj.value;
			shippingModuleId = optionObj.getAttribute("data-module-id");
		} else if (shippingControl == "radio") { 
			for(var i = 0; i < shippingObj.length; i++) {
				var radioShippingId = shippingObj[i].value;
				if (shippingObj[i].checked) {
					shippingTypeId = radioShippingId;
					shippingModuleId = shippingObj[i].getAttribute("data-module-id");
				}
			}
		} else {
			shippingModuleId = shippingObj.getAttribute("data-module-id");
			shippingTypeId = shippingObj.value;
		}
		if (shippingTypeId != "") {
			shippingTypeIds.push(shippingTypeId); 
			shippingModuleIds.push(shippingModuleId);
		}
		// check if user select to pay with points
		if (orderForm.elements["shipping_pay_points_"+shippingIndex] && orderForm.elements["shipping_pay_points_"+shippingIndex].checked) {
			shippingPayPoints = 1;
		}

		// get shipping methods for current group
		var shippingMethods = prepareData("shipping_methods_"+shippingIndex, "shipping_id=");
		// check shipping cost
		var shippingInclTax = ""; var shippingTax = ""; var shippingExclTax = "";
		if (shippingTypeId == "") {
			shippingObj = document.getElementById("shipping_cost_excl_tax_" + shippingIndex);
			if (shippingObj) { shippingObj.innerHTML = ""; }
			shippingObj = document.getElementById("shipping_tax_" + shippingIndex);
			if (shippingObj) { shippingObj.innerHTML = ""; }
			shippingObj= document.getElementById("shipping_cost_incl_tax_" + shippingIndex);
			if (shippingObj) { shippingObj.innerHTML = ""; }
		} else {
			shippingCost = parseFloat(shippingMethods[shippingTypeId]["cost"]);
			if (shippingPayPoints == 1) {
				shippingPoints = shippingCost * pointsRate;
				shippingCost = 0;
			}
			shippingTotalCost += shippingCost;
			shippingTotalPoints += shippingPoints;
			var shippingTaxFree = parseInt(shippingMethods[shippingTypeId]["tax_free"]); 
			var shippingTaxes = getTaxAmount(taxRates, "shipping", shippingCost, 1, 0, shippingTaxFree, 2) 
			var shippingTax = getTaxAmount(taxRates, "shipping", shippingCost, 1, 0, shippingTaxFree, 1) 
			if (shippingPayPoints == 1) {
				shippingTaxes = "";
				shippingTax = "";
			}
			taxRates = addTaxValues(taxRates, shippingTaxes, "shipping");
			var shippingPrices = calculatePrices(shippingCost, shippingTax);
			shippingExclTax = shippingPrices["excl_tax"];
			shippingInclTax = shippingPrices["incl_tax"];

			// update shipping cost in cart
			shippingObj = document.getElementById("shipping_cost_excl_tax_" + shippingIndex);
			if (shippingObj) { shippingObj.innerHTML = orderCurrency(shippingExclTax); }
			shippingObj = document.getElementById("shipping_tax_" + shippingIndex);
			if (shippingObj) { shippingObj.innerHTML = orderCurrency(shippingTax); }
			shippingObj= document.getElementById("shipping_cost_incl_tax_" + shippingIndex);
			if (shippingObj) { shippingObj.innerHTML = orderCurrency(shippingInclTax); }
		}

		// get next index
		shippingIndex++;
	}
	// end shipping checks

	// check custom shipping fields 
	var shippingFields = new Array();
	var shippingFieldsData = (orderForm.shipping_fields) ? orderForm.shipping_fields.value : "";
	if (shippingFieldsData!="") {
		try { shippingFields = JSON.parse(shippingFieldsData); } catch(e) {}
	}

	// hide/show shipping custom fields
	for(fieldName in shippingFields) {
		var fieldObj = document.getElementById(fieldName);
		var requiredObj = document.getElementById(fieldName+"_required");
		var fieldData = shippingFields[fieldName];
		var showField = "0"; var fieldRequired = "0"; 
		if (fieldData["required"]) {
			fieldRequired = fieldData["required"];	
		}
		var typeId = fieldData["shipping_type_id"];	
		var moduleId = fieldData["shipping_module_id"];	
	
		for (var sid in shippingTypeIds) {
			if (typeId != "0" && typeId == shippingTypeIds[sid]) {
				showField = "1";
			}
		} 
		for (var sid in shippingModuleIds) {
			if (moduleId != "0" && moduleId == shippingModuleIds[sid]) {
				showField = "1";
			}
		} 
		if (fieldObj) {
			if (showField == "1") {
				fieldObj.style.display = "block";
			} else {
				fieldObj.style.display = "none";
			}
		}
		if (requiredObj) {
			if (fieldRequired == "1") {
				requiredObj.style.display = "inline";
			} else {
				requiredObj.style.display = "none";
			}
		}
	}


	// calculate total values
	totalValues = calculateTotals(totalValues, shippingTotalCost, taxRates, "shipping")
	shippingInclTax = totalValues["shipping_incl_tax"];
	// end shipping calculations

	// calculate discounts
	var maxDiscount = goodsTotal; var totalDiscount = 0; var totalDiscountTax = 0; var discountInclTax = 0;
	var coupons = prepareData("order_coupons", "coupon_id=");
	if (coupons instanceof Array) {
		for (var couponId in coupons) {
			if(!(coupons[couponId] instanceof Function)){
				var coupon = coupons[couponId];
				var couponType = coupon["type"];
				var couponAmount = coupon["amount"];
				var couponTaxFree = coupon["tax_free"];
				var discountAmount = 0;
				var discountTax = 0;
				if (couponType == 1) {
					discountAmount = Math.round(goodsTotal * couponAmount) / 100;
				} else {
					discountAmount = parseFloat(couponAmount);
				}
				if (discountAmount > maxDiscount) {
					discountAmount = maxDiscount;
				}
				maxDiscount -= discountAmount;
				var discountTaxes = getDiscountTaxes(taxRates, totalValues, discountAmount, couponTaxFree, 2)
				var discountTax = getDiscountTaxes(taxRates, totalValues, discountAmount, couponTaxFree, 1)
				taxRates = addTaxValues(taxRates, discountTaxes, "discount");
				var discountPrices = calculatePrices(discountAmount, discountTax);

				totalDiscount += discountAmount;
				totalDiscountTax += discountTax;

				priceObj = document.getElementById("coupon_amount_excl_tax_" + couponId);
				if (priceObj) {
					priceObj.innerHTML = "- " + orderCurrency(discountPrices["excl_tax"]);
				}
				priceObj = document.getElementById("coupon_tax_" + couponId);
				if (priceObj) {
					priceObj.innerHTML = "- " + orderCurrency(discountTax);
				}
				priceObj = document.getElementById("coupon_amount_incl_tax_" + couponId);
				if (priceObj) {
					priceObj.innerHTML = "- " + orderCurrency(discountPrices["incl_tax"]);
				}
			}
		}
		// show discount and goods cost after discount total values
		totalValues = calculateTotals(totalValues, totalDiscount, taxRates, "discount")
		discountInclTax = totalValues["discount_incl_tax"];

		priceObj = document.getElementById("total_discount_excl_tax");
		if (priceObj) {
			priceObj.innerHTML = "- " + orderCurrency(totalValues["discount_excl_tax"]);
		}
		priceObj = document.getElementById("total_discount_tax");
		if (priceObj) {
			priceObj.innerHTML = "- " + orderCurrency(totalValues["discount_tax"]);
		}
		priceObj = document.getElementById("total_discount_incl_tax");
		if (priceObj) {
			priceObj.innerHTML = "- " + orderCurrency(totalValues["discount_incl_tax"]);
		}

		priceObj = document.getElementById("discounted_amount_excl_tax");
		if (priceObj) {
			priceObj.innerHTML = orderCurrency(totalValues["goods_excl_tax"] - totalValues["discount_excl_tax"]);
		}
		priceObj = document.getElementById("discounted_tax_amount");
		if (priceObj) {
			priceObj.innerHTML = orderCurrency(totalValues["goods_tax"] - totalValues["discount_tax"]);
		}
		priceObj = document.getElementById("discounted_amount_incl_tax");
		if (priceObj) {
			priceObj.innerHTML = orderCurrency(totalValues["goods_incl_tax"] - totalValues["discount_incl_tax"]);
		}
		
	}

	// calculate and show taxes
	var orderFixedTax = 0;
	var taxesTotal = 0;
	for (var taxId in taxRates) {
		var taxObj = document.getElementById("tax_" + taxId);
		var taxTotal = 0; var orderFixedAmount = 0;
		if (taxRates[taxId]["tax_total"]) {
			taxTotal = Math.round(taxRates[taxId]["tax_total"] * 100) / 100;
		}
		if (taxRates[taxId]["order_fixed_amount"]) {
			orderFixedAmount = Math.round(taxRates[taxId]["order_fixed_amount"] * 100) / 100;
			if (isNaN(orderFixedAmount)) { orderFixedAmount = 0; }
		}
		orderFixedTax += orderFixedAmount;
		taxesTotal += taxTotal + orderFixedAmount;
		if (taxObj) {
			taxObj.innerHTML = orderCurrency(taxTotal + orderFixedAmount);
		}
	}

	//var goodsTotal = parseFloat(orderForm.goods_value.value); todo delete
	// calculate order total
	var orderTotal = 0; var paidTotal = 0; var leftTotal = 0; var paidProcessingInclTax = 0; var paidProcessingTax = 0;
	if (userOrder == 1) {
		orderTotal = orderData["goods_total_incl_tax"] - orderData["total_discount_incl_tax"] + orderData["properties_incl_tax"] + orderData["shipments_incl_tax"];
		paidTotal = parseFloat(orderData["paid_total"]);
		paidProcessingInclTax = parseFloat(orderData["paid_processing_incl_tax"]); 
		paidProcessingTax = parseFloat(orderData["paid_processing_tax"]);
	} else {
		orderTotal = goodsTotal - totalDiscount + totalPropertiesPrice + shippingTotalCost;
		if (pricesType != 1) {
			orderTotal += taxesTotal;
		}
	}
	var partialOrderTotal = orderTotal;
	orderTotal += paidProcessingInclTax;
	taxesTotal += paidProcessingTax;
	leftTotal = orderTotal - paidTotal;

	// calculate gift vouchers
	var vouchersTotal = 0;
	var vouchers = prepareData("order_vouchers", "voucher_id=");
	if (vouchers instanceof Array) {
		for (var voucherId in vouchers) {
			if(!(vouchers[voucherId] instanceof Function)){
				var voucher = vouchers[voucherId];
				var voucherTitle = voucher["title"];
				var voucherMaxAmount = voucher["max_amount"];
				var voucherAmount = voucherMaxAmount;
				if (voucherAmount > orderTotal) {
					voucherAmount = orderTotal;
				}
				orderTotal -= voucherAmount;
				vouchersTotal += voucherAmount;
				priceObj = document.getElementById("voucher_amount_" + voucherId);
				if (priceObj) {
					if (voucherAmount > 0) {
						priceObj.innerHTML = "- " + orderCurrency(voucherAmount);
					} else {
						priceObj.innerHTML = "";
					}
				}
			}
		}
	}

	// check percentage and total amount user will pay
	var paymentAmount = leftTotal; var paymentPercentage = 100;
	if (orderForm.payment_percentage) {
		paymentPercentage = parseFloat(orderForm.payment_percentage.value);
		if (isNaN(paymentPercentage) || paymentPercentage <= 0) { paymentPercentage = 100; }
		paymentAmount = Math.round(partialOrderTotal * paymentPercentage) / 100;
	}
	if (paymentAmount > leftTotal) { paymentAmount = leftTotal; }

	// calculate processing fee based on order total and payment percentage
	var processingFee = 0; var processingTaxFree = 0; 
	if (orderForm.payment_id && orderData["payment_systems"]) {
		var paymentId = "";
		if (orderForm.payment_id.options) {
			paymentId = orderForm.payment_id.value;
		} else if (orderForm.payment_id.length > 0) {
			for (var i = 0; i < orderForm.payment_id.length; i++) {
				if (orderForm.payment_id[i].checked) {
					paymentId = orderForm.payment_id[i].value;
					break;
				}
			}
		} else {
			paymentId = orderForm.payment_id.value;
		}
		// update processing fee value based on percentage
		for (var recId in orderData["payment_systems"]) {
			var paymentData = orderData["payment_systems"][recId]
			var feeTaxFree = parseInt(paymentData.processing_tax_free);
			if (isNaN(feeTaxFree)) { feeTaxFree = 0; }
			var feePercent = parseFloat(paymentData.fee_percent);
			if (isNaN(feePercent)) { feePercent = 0; }
			var feeAmount = parseFloat(paymentData.fee_amount);
			if (isNaN(feeAmount)) { feeAmount = 0; }
			if (feePercent > 0 || feeAmount > 0) {
				var recName = paymentData.payment_name;
				var recFee = Math.round((feeAmount * 100) + (paymentAmount * feePercent)) / 100;
				if (recFee > 0) {
					recName += " (+ " + orderCurrency(recFee) + ")";
				} else if (recFee < 0) {
					recName += " (- " + Math.abs(orderCurrency(recFee)) + ")";
				}
				var nameObj = document.getElementById("ps_name_"+recId);
				if (nameObj) {
					nameObj.innerHTML = recName;
				}
				if (recId == paymentId) {
					processingFee = recFee; 
					processingTaxFree = feeTaxFree; 
				}
			}
		}
	}

	// calculate taxes for processing Fee
	var processingTaxes = getTaxAmount(taxRates, "processing", processingFee, 1, 0, processingTaxFree, 2) 
	var processingTax = getTaxAmount(taxRates, "processing", processingFee, 1, 0, processingTaxFree, 1) 
	taxRates = addTaxValues(taxRates, processingTaxes, "processing");
	var processingPrices = calculatePrices(processingFee, processingTax);
	var processingExclTax = processingPrices["excl_tax"];
	var processingInclTax = processingPrices["incl_tax"];
	orderTotal += processingInclTax;
	paymentAmount += processingInclTax;

	// update cart and user total
	var cartTotal = goodsInclTax + propertiesInclTax - discountInclTax - vouchersTotal + orderFixedTax;
	var cartTotalObj = document.getElementById("cartTotal");
	if (cartTotalObj) {
		cartTotalObj.innerHTML = orderCurrency(cartTotal);
	}
	var userTotalObj = document.getElementById("userTotal");
	if (userTotalObj) {
		userTotalObj.innerHTML = orderCurrency(cartTotal);
	}

	// update shipping total
	var shippingTotal = cartTotal + shippingInclTax;
	var shippingTotalObj = document.getElementById("shippingTotal");
	if (shippingTotalObj) {
		shippingTotalObj.innerHTML = orderCurrency(shippingTotal);
	}

	// update payment total
	var paymentTotalObj = document.getElementById("paymentTotal");
	if (paymentTotalObj) {
		paymentTotalObj.innerHTML = orderCurrency(orderTotal);
	}
	// check additional control for order total
	var orderTotalControl = document.getElementById("order_total_desc");
	if (orderTotalControl) {
		orderTotalControl.innerHTML = orderCurrency(orderTotal);
	}

	// on payment step show for user how much he will need to pay
	var paymentAmountObj = orderForm.querySelector(".fd-payment-amount");
	if (paymentAmountObj) {
		var amountObj = paymentAmountObj.querySelector(".fd-value");
		amountObj.innerHTML = orderCurrency(paymentAmount);
	}

	// calculate points if available
	var pointsBalance = parseFloat(orderForm.points_balance.value);
	var pointsDecimals = 0;
	if (orderForm.points_decimals && orderForm.points_decimals.value != "") {
		pointsDecimals = parseFloat(orderForm.points_decimals.value);
		if (isNaN(pointsDecimals)) { pointsDecimals = 0; }
	}

	var orderTotalPoints = goodsPoints + totalPropertiesPoints + shippingTotalPoints;
	var totalPointsControl = document.getElementById("total_points_amount");
	if (totalPointsControl) {
		totalPointsControl.innerHTML = formatNumber(orderTotalPoints, pointsDecimals);
	}
	var remainingPointsControl = document.getElementById("remaining_points");
	if (remainingPointsControl) {
		remainingPointsControl.innerHTML = formatNumber(pointsBalance - orderTotalPoints, pointsDecimals);
	}

}

//credit_amount
//fd-payment-amount

function getDiscountTaxes(taxRates, totalValues, discountAmount, taxFreeOption, returnType)
{
	var goodsTotal = totalValues["goods_total"];
	var taxAmount = 0;
	var taxesValues = new Array();
	if (taxFreeOption != 1) {
		if (taxRates instanceof Array) {
			for (taxId in taxRates) {
				if(!(taxRates[taxId] instanceof Function) && !(taxRates[taxId]["goods"] instanceof Function)){
					var goodsTax = taxRates[taxId]["goods"];
					var discountTax = Math.round((discountAmount * goodsTax * 100) / goodsTotal) / 100;
					taxesValues[taxId] = new Array();
					taxesValues[taxId]["tax_amount"] = discountTax;
					taxesValues[taxId]["price_amount"] = discountAmount;
					taxAmount += discountTax;
				}
			}
		}
	}

	if (returnType == 2) {
		return taxesValues;
	} else {
		return taxAmount;
	}
}

function getTaxAmount(taxRates, itemType, amount, quantity, itemTaxId, taxFreeOption, returnType) 
{
	var taxRound = 1;
	if (document.order_info.tax_round) {
		taxRound = parseInt(document.order_info.tax_round.value);
		if (isNaN(taxRound)) { taxRound = 1; }
	}

	var taxesValues = new Array();
	var pricesType = parseFloat(document.order_info.tax_prices_type.value);
	if (isNaN(pricesType)) { pricesType = 0; }

	// calculate summary tax
	var taxAmount = 0; var taxPercent = 0; var fixedTax = 0;
	if (taxFreeOption != 1) {
		// calculate summary tax
		if (taxRates instanceof Array) {
			for (taxId in taxRates) {
				if(!(taxRates[taxId] instanceof Function)){
					var taxRate = taxRates[taxId];
					var taxType = taxRate["tax_type"];

					// check if the tax coould be applied for current item
					if (taxType == 1 || (taxType == 2 && itemTaxId == taxId)) {

						var currentTaxPercent = 0; var currentFixedTax = 0; var currentItemTax = 0;
						// check tax percent
						if (taxRate["types"] && taxRate["types"][itemType] && taxRate["types"][itemType]["tax_percent"]) {
							currentTaxPercent = parseFloat(taxRate["types"][itemType]["tax_percent"]);
						} else {
							currentTaxPercent = parseFloat(taxRate["tax_percent"]);
						}
						// check fixed tax amount 
						if (taxRate["types"] && taxRate["types"][itemType] && taxRate["types"][itemType]["fixed_amount"]) {
							currentFixedTax = parseFloat(taxRate["types"][itemType]["fixed_amount"]) * quantity;
						} else if (taxRate["fixed_amount"]) {
							currentFixedTax = parseFloat(taxRate["fixed_amount"]) * quantity;
						} else {
							currentFixedTax = 0;
						}
						// calculate tax amount for each tax
						if (pricesType == 1) { // prices includes tax
							currentItemTax = (Math.round(amount * 100) - Math.round(amount * 10000 / ( 100 + currentTaxPercent))) / 100 - currentFixedTax; 
						} else {
							currentItemTax = Math.round(amount * currentTaxPercent) / 100 + currentFixedTax;
						}
						if (taxRound == 1) {
							currentItemTax = Math.round(currentItemTax * 100) / 100;
						}

						taxesValues[taxId] = new Array();
						//taxesValues[taxId]["tax_name"] = "";
						//taxesValues[taxId]["show_type"] = "";
						taxesValues[taxId]["tax_percent"] = currentTaxPercent;
						taxesValues[taxId]["fixed_value"] = currentFixedTax;
						taxesValues[taxId]["tax_amount"] = currentItemTax;
						taxesValues[taxId]["price_amount"] = amount;

						taxPercent += currentTaxPercent;
						fixedTax += currentFixedTax;
						taxAmount += currentItemTax;

					} // end tax check
				}
			}
		}
	} else {
		taxPercent = 0;
	}

	if (returnType == 2) {
		return taxesValues;
	} else {
		return taxAmount;
	}
}

function addTaxValues(taxRates, taxValues, amountType)
{
	var taxRound = 1;
	if (document.order_info.tax_round) {
		taxRound = parseInt(document.order_info.tax_round.value);
		if (isNaN(taxRound)) { taxRound = 1; }
	}

	if (taxValues instanceof Array) {
		for (taxId in taxValues) {
			if(!(taxValues[taxId] instanceof Function)){
				var taxInfo = taxValues[taxId];
				var taxAmount = parseFloat(taxInfo["tax_amount"]);
				if (taxRound == 1) {
					taxAmount = Math.round(taxAmount * 100) / 100;
				}
				if (!taxRates[taxId][amountType]) {
					taxRates[taxId][amountType] = 0;
				}
				if (!taxRates[taxId]["tax_total"]) {
					taxRates[taxId]["tax_total"] = 0;
				}
				taxRates[taxId][amountType] += taxAmount;
				if (amountType == "discount") {
					taxRates[taxId]["tax_total"] -= taxAmount;
				} else {
					taxRates[taxId]["tax_total"] += taxAmount;
				}
			}
		}
	}
	return taxRates;
}


function calculateTotals(totalValues, totalAmount, taxRates, amountType)
{
	var pricesType = parseFloat(document.order_info.tax_prices_type.value);
	if (isNaN(pricesType)) { pricesType = 0; }

	totalValues[amountType+"_total"] = totalAmount;
	totalValues[amountType+"_excl_tax"] = 0;
	totalValues[amountType+"_tax"] = 0;
	totalValues[amountType+"_incl_tax"] = 0;
	for (taxId in taxRates) {
		if(!(taxRates[taxId][amountType] instanceof Function)){
			if (taxRates[taxId][amountType]) {
				totalValues[amountType+"_tax"] += taxRates[taxId][amountType];
			}
		}
	}
	if (pricesType == 1) {
		totalValues[amountType+"_excl_tax"] += (totalAmount - totalValues[amountType+"_tax"]);
		totalValues[amountType+"_incl_tax"] += totalAmount;
	} else {
		totalValues[amountType+"_excl_tax"] += totalAmount;
		totalValues[amountType+"_incl_tax"] += 1 * totalAmount + totalValues[amountType+"_tax"];
	}

	return totalValues;
}

function calculatePrices(amount, tax)
{
	var prices = new Array();
	var pricesType = parseFloat(document.order_info.tax_prices_type.value);
	if (isNaN(pricesType)) { pricesType = 0; }

	prices["base"] = amount;
	prices["tax"] = tax;
	if (pricesType == 1) {                           
		prices["excl_tax"] = (amount - tax);
		prices["incl_tax"] = amount;
	} else {
		prices["excl_tax"] = amount;
		prices["incl_tax"] = 1 * amount + tax;
	}

	return prices;
}


function totalTaxValue(taxValues)
{
	var taxRound = 1;
	if (document.order_info.tax_round) {
		taxRound = parseInt(document.order_info.tax_round.value);
		if (isNaN(taxRound)) { taxRound = 1; }
	}

	var totalTax = 0;
	if (taxValues instanceof Array) {
		for (taxId in taxValues) {
			if(!(taxValues[taxId] instanceof Function)){
				var taxInfo = taxValues[taxId];
				var taxAmount = parseFloat(taxInfo["tax_amount"]);
				if (taxRound == 1) {
					taxAmount = Math.round(taxAmount * 100) / 100;
				}
				totalTax += taxAmount;
			}
		}
	}
	return totalTax;
}


function getTaxAmountOld(amount, taxPercent, taxFree, pricesType) 
{
	var taxAmount = 0;
	if (taxFree != 1) {
		if (pricesType == 1) {
			taxAmount = (Math.round(amount * 100) - Math.round(amount * 10000 / ( 100 + taxPercent))) / 100; 
		} else {
			taxAmount = Math.round(amount * taxPercent) / 100;
		}
	}
	return taxAmount;
}

function orderCurrency(numberValue)
{
	var orderForm = document.order_info;
	var currencyLeft = orderForm.currency_left.value;
	var currencyRight = orderForm.currency_right.value;
	var currencyRate = orderForm.currency_rate.value;
	var currencyDecimals = orderForm.currency_decimals.value;
	var currencyPoint = orderForm.currency_point.value;
	var currencySeparator = orderForm.currency_separator.value;
	return currencyLeft + formatNumber(numberValue * currencyRate, currencyDecimals, currencyPoint, currencySeparator) + currencyRight;
}

function formatNumber(numberValue, decimals, decimalPoint, thousandsSeparator)
{
	if (decimals == undefined) {
		decimals = 0;
	}
	if (thousandsSeparator == undefined) {
		thousandsSeparator = ",";
	}

	var numberParts = "";
	var roundValue = 1;
	for (var d = 0; d < decimals; d++) {
		roundValue *= 10;
	}
	numberValue = Math.round(numberValue * roundValue) / roundValue;
	var numberSign = "";
	if (numberValue < 0) {
		numberSign = "-";
		numberValue = Math.abs(numberValue);
	} 

	var numberText = new String(numberValue);
	var numberParts = numberText.split(".");
	var beforeDecimal = numberParts[0];
	var afterDecimal = "";
	numberText = "";
	if (numberParts.length == 2) {
		afterDecimal = numberParts[1];
	}
	while (beforeDecimal.length > 0) {
		if (beforeDecimal.length > 3) {
			numberText = thousandsSeparator + beforeDecimal.substring(beforeDecimal.length - 3, beforeDecimal.length) + numberText;
			beforeDecimal = beforeDecimal.substring(0, beforeDecimal.length - 3);
		} else {
			numberText = beforeDecimal + numberText;
			beforeDecimal = "";
		}
	}
	if (decimals > 0) {
		while (afterDecimal.length < decimals) {
			afterDecimal += "0";
		}
		if (decimalPoint == undefined) {
			decimalPoint = ".";
		}
		numberText += decimalPoint + afterDecimal;
	}
	numberText = numberSign + numberText;

	return numberText;
}

function changeCountry()
{
	var orderForm = document.order_info;
	var refreshPage = true;
	if (this.name == 'country_id') {
		if (orderForm.delivery_country_id) { refreshPage = false; }
	}
	if (refreshPage) {
		orderForm.operation.value = "refresh";
		orderForm.submit();
	}
}

function changeState()
{
	var orderForm = document.order_info;
	var refreshPage = true;
	if (this.name == 'state_id') {
		if (orderForm.delivery_state_id || orderForm.delivery_country_id || orderForm.delivery_state_id) {
			refreshPage = false;
		} else if (orderForm.country_id) {
			if (orderForm.country_id.selectedIndex == 0) { refreshPage = false; }
		} else if (orderForm.country_id) {
			if (orderForm.country_id.selectedIndex == 0) { refreshPage = false; }
		}
	} else if (orderForm.delivery_country_id) {
		if (orderForm.delivery_country_id.selectedIndex == 0) { refreshPage = false; }
	} else if (orderForm.delivery_country_id) {
		if (orderForm.delivery_country_id.selectedIndex == 0) { refreshPage = false; }
	}
	if (refreshPage) {
		orderForm.operation.value = "refresh";
		orderForm.submit();
	}
}

function changeZip()
{
	var orderForm = document.order_info;
	var refreshPage = true;
	if (this.name == 'zip') {
		if (orderForm.delivery_zip || orderForm.delivery_country_id) {
			refreshPage = false;
		} else if (orderForm.country_id) {
			if (orderForm.country_id.selectedIndex == 0) { refreshPage = false; }
		} else if (orderForm.country_id) {
			if (orderForm.country_id.selectedIndex == 0) { refreshPage = false; }
		}
		
	} else if (orderForm.delivery_country_id) {
		if (orderForm.delivery_country_id.selectedIndex == 0) { refreshPage = false; }
	} else if (orderForm.delivery_country_id) {
		if (orderForm.delivery_country_id.selectedIndex == 0) { refreshPage = false; }
	}
	if (refreshPage) {
		orderForm.operation.value = "refresh";
		orderForm.submit();
	}
}

function checkSame()
{
	var refreshPage = false;
	var orderForm = document.order_info;
	var pbId = orderForm.pb_id.value;
	var sameChecked = document.order_info.same_as_personal.checked;
	if (sameChecked) {
		var changeEvent;
		if (typeof(Event) === 'function') {
			changeEvent = new Event("change");
		} else {
			changeEvent = document.createEvent('HTMLEvents');
			changeEvent.initEvent("change", true, true);
		}
		var fieldName = "";
		var fields = new Array("name", "first_name", "middle_name", "last_name", "company_id", "company_name", "email",
			"address1", "address2", "address3","city", "province", "address1",
			"phone", "daytime_phone", "evening_phone", "cell_phone", "fax",
			"phone_code", "daytime_phone_code", "evening_phone_code", "cell_phone_code", "fax_code");
		for (var i = 0; i < fields.length; i++) {
			fieldName = fields[i];
			if (orderForm.elements[fieldName] && orderForm.elements["delivery_" + fieldName]) {
				if (orderForm.elements[fieldName].value != orderForm.elements["delivery_" + fieldName].value) {
					orderForm.elements["delivery_" + fieldName].value = orderForm.elements[fieldName].value;
					orderForm.elements["delivery_" + fieldName].dispatchEvent(changeEvent);
				}
			}
		}
		if (orderForm.country_id && orderForm.delivery_country_id) {
			if (orderForm.country_id.selectedIndex != orderForm.delivery_country_id.selectedIndex) {
				orderForm.delivery_country_id.selectedIndex = orderForm.country_id.selectedIndex;
				orderForm.delivery_country_id.dispatchEvent(changeEvent);
				updateStates(pbId, "delivery");
			}
		}
		if (orderForm.state_id && orderForm.delivery_state_id) {
			if (orderForm.state_id.selectedIndex != orderForm.delivery_state_id.selectedIndex) {
				var stateId = orderForm.state_id.options[orderForm.state_id.selectedIndex].value;
				for (var s = 0; s < orderForm.delivery_state_id.options.length; s++) {
					var deliveryStateId = orderForm.delivery_state_id.options[s].value;
					if (stateId == deliveryStateId) {
						orderForm.delivery_state_id.options[s].selected = true;
					}
				}
				orderForm.delivery_state_id.dispatchEvent(changeEvent);
			}
		}

		if (orderForm.zip && orderForm.delivery_zip) {
			if (orderForm.zip.value != orderForm.delivery_zip.value) {
				orderForm.delivery_zip.value = orderForm.zip.value;
				orderForm.delivery_zip.dispatchEvent(changeEvent);
			}
		}
	}
}

function uncheckSame()
{
	if (document.order_info.same_as_personal) {
		document.order_info.same_as_personal.checked = false;
	}
}

function checkMaxLength(obj, maxLength)
{
  return (obj.value.length < maxLength);
}

function checkBoxesMaxLength(e, itemForm, cpID, maxLength)
{
	var key;
	if (window.event) {
		key = window.event.keyCode; //IE
	} else {
		key = e.which; //Firefox
	}

	if (key == 8 || key == 9 || key == 16 || key == 17 || key == 35 || key == 36 || key == 37 || key == 39 || key == 46 || key == 116) {
		return true;
	}

	var totalOptions = parseInt(itemForm.elements["property_total_" + cpID].value);
	var totalLength = 0;
	for ( var ci = 1; ci <= totalOptions; ci++) {
		if (itemForm.elements["property_" + cpID + "_" + ci].value != "") {
			var valueText = itemForm.elements["property_" + cpID + "_" + ci].value;
			totalLength += valueText.length;
		}
	}
  return (totalLength < maxLength);
}

function prepareData(dataName, dataDelimiter)
{
	var data = new Array();
	var dataValue = document.order_info.elements[dataName].value;
	if (dataValue != "") {
		var records = dataValue.split(dataDelimiter);
		for (var t = 0; t < records.length; t++) {
			var record = records[t];
			var ampPos = record.indexOf("&");
			if (ampPos != -1) {
				var dataId = record.substring(0, ampPos);
				var recordValue = record.substring(ampPos+1, record.length);
				data[dataId] = new Array();
				// get record parameters
				var paramsPairs = recordValue.split("&");
				for (var p = 0; p < paramsPairs.length; p++) {
					var paramPair = paramsPairs[p];
					var equalPos = paramPair.indexOf("=");
					if (equalPos != -1) {
						var paramName = paramPair.substring(0, equalPos);
						var paramValue = paramPair.substring(equalPos + 1, paramPair.length);
						if (dataName == "tax_rates" && paramName.substring(0, 10) == "item_type_") { // special condition for taxes
							var itemTaxStr = paramName.substring(10, paramName.length);
							var undPos = itemTaxStr.indexOf("_");
							var itemTaxType = itemTaxStr.substring(0, undPos);
							var itemTaxCode = itemTaxStr.substring(undPos + 1, itemTaxStr.length);
							if(!data[dataId]["types"]) {
								data[dataId]["types"] = new Array();
							}
							if(!data[dataId]["types"][itemTaxCode]) {
								data[dataId]["types"][itemTaxCode] = new Array();
							}
							if (itemTaxType == "percent") {
								data[dataId]["types"][itemTaxCode]["tax_percent"] = decodeParamValue(paramValue);
							} else if (itemTaxType == "fixed") {
								data[dataId]["types"][itemTaxCode]["fixed_amount"] = decodeParamValue(paramValue);
							}
						} else {
							data[dataId][paramName] = decodeParamValue(paramValue);
						}
					}
				} // end of record parameters cycle
			}
		}
	}
	return data;
}

function decodeParamValue(paramValue)
{
	paramValue = paramValue.replace(/%0D/g, "\r");
	paramValue = paramValue.replace(/%0A/g, "\n");
	paramValue = paramValue.replace(/%27/g, "'");
	paramValue = paramValue.replace(/%22/g, "\"");
	paramValue = paramValue.replace(/%26/g, "&");
	paramValue = paramValue.replace(/%2B/g, "+");
	paramValue = paramValue.replace(/%25/g, "%");
	paramValue = paramValue.replace(/%3D/g, "=");
	paramValue = paramValue.replace(/%7C/g, "|");
	paramValue = paramValue.replace(/%23/g, "#");
	return paramValue;
}


function addressWindow(windowUrl)
{
	var addressWindow = window.open (windowUrl, 'addressWindow', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
	addressWindow.focus();
}

function ccUsersWindow(windowUrl)
{
	var usersWindow = window.open (windowUrl, 'usersWindow', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600');
	usersWindow.focus();
}

function ccSetUser(ui)
{
	var orderForm = document.order_info;
	var personalNumber = orderForm.personal_number.value;
	var deliveryNumber = orderForm.delivery_number.value;

	orderForm.cc_user_id.value = ui["user_id"];
	var loginObj = document.getElementById("cc_user_login");
	loginObj.innerHTML = ui["login"];

	var ccRemoveObj = document.getElementById("cc_remove_user");
	ccRemoveObj.style.display = "inline";

	
	if (personalNumber) {
		setAddress(1, ui["name"], ui["first_name"], ui["middle_name"], ui["last_name"], ui["company_id"], ui["company_name"], 
			ui["email"], ui["address1"], ui["address2"], ui["address3"], ui["city"], ui["province"], 
			ui["state_id"], ui["country_id"], ui["zip"], 
			ui["phone"], ui["daytime_phone"], ui["evening_phone"], ui["cell_phone"], ui["fax"]);
	}
	if (deliveryNumber) {
		setAddress(2, ui["delivery_name"], ui["delivery_first_name"], ui["delivery_middle_name"], ui["delivery_last_name"], ui["delivery_company_id"], ui["delivery_company_name"], 
			ui["delivery_email"], ui["delivery_address1"], ui["delivery_address2"], ui["delivery_address3"], ui["delivery_city"], ui["delivery_province"], 
			ui["delivery_state_id"], ui["delivery_country_id"], ui["delivery_zip"], 
			ui["delivery_phone"], ui["delivery_daytime_phone"], ui["delivery_evening_phone"], ui["delivery_cell_phone"], ui["delivery_fax"]);
	}
}

function ccRemoveUser()
{
	var orderForm = document.order_info;
	var personalNumber = orderForm.personal_number.value;
	var deliveryNumber = orderForm.delivery_number.value;

	orderForm.cc_user_id.value = "";
	var loginObj = document.getElementById("cc_user_login");
	loginObj.innerHTML = "";

	var ccRemoveObj = document.getElementById("cc_remove_user");
	ccRemoveObj.style.display = "none";
}

function setAddress(addressType, name, firstName, middleName, lastName, companyId, companyName, email, address1, address2, address3, city, province, stateId, countryId, postalCode, phone, daytimePhone, eveningPhone, cellPhone, fax)
{
	var orderForm = document.order_info;
	var prefix = "";	
	if (addressType == 2) {
		prefix = "delivery_";
	}
	if (orderForm.elements[prefix+"name"]) { orderForm.elements[prefix+"name"].value = name; }
	if (orderForm.elements[prefix+"first_name"]) { orderForm.elements[prefix+"first_name"].value = firstName; }
	if (orderForm.elements[prefix+"middle_name"]) { orderForm.elements[prefix+"middle_name"].value = middleName; }
	if (orderForm.elements[prefix+"last_name"]) { orderForm.elements[prefix+"last_name"].value = lastName; }
	if (orderForm.elements[prefix+"company_id"]) { 
		var control = orderForm.elements[prefix+"company_id"];
		control.selectedIndex = 0;
		for (var i = 0; i < control.options.length; i++) {
			if (control.options[i].value == companyId) {
				control.options[i].selected = true;
			}
		}
	}
	if (orderForm.elements[prefix+"company_name"]) { orderForm.elements[prefix+"company_name"].value = companyName; }
	if (orderForm.elements[prefix+"email"]) { orderForm.elements[prefix+"email"].value = email; }
	if (orderForm.elements[prefix+"address1"]) { orderForm.elements[prefix+"address1"].value = address1; }
	if (orderForm.elements[prefix+"address2"]) { orderForm.elements[prefix+"address2"].value = address2; }
	if (orderForm.elements[prefix+"address3"]) { orderForm.elements[prefix+"address3"].value = address3; }
	if (orderForm.elements[prefix+"city"]) { orderForm.elements[prefix+"city"].value = city; }
	if (orderForm.elements[prefix+"province"]) { orderForm.elements[prefix+"province"].value = province; }
	if (orderForm.elements[prefix+"country_id"]) { 
		var control = orderForm.elements[prefix+"country_id"];
		control.selectedIndex = 0;
		for (var i = 0; i < control.options.length; i++) {
			if (control.options[i].value == countryId) {
				control.options[i].selected = true;
			}
		}
	}
	// update states list first
	var pbId = orderForm.pb_id.value;
	if (addressType == 1) {
		updateStates(pbId, "personal");
	} else {
		updateStates(pbId, "delivery");
	}
	if (orderForm.elements[prefix+"state_id"]) { 
		var control = orderForm.elements[prefix+"state_id"];
		control.selectedIndex = 0;
		for (var i = 0; i < control.options.length; i++) {
			if (control.options[i].value == stateId) {
				control.options[i].selected = true;
			}
		}
	}
	if (orderForm.elements[prefix+"zip"]) { orderForm.elements[prefix+"zip"].value = postalCode; }
	if (orderForm.elements[prefix+"postal_code"]) { orderForm.elements[prefix+"postal_code"].value = postalCode; }
	if (orderForm.elements[prefix+"phone"]) { orderForm.elements[prefix+"phone"].value = phone; }
	if (orderForm.elements[prefix+"daytime_phone"]) { orderForm.elements[prefix+"daytime_phone"].value = daytimePhone; }
	if (orderForm.elements[prefix+"evening_phone"]) { orderForm.elements[prefix+"evening_phone"].value = eveningPhone; }
	if (orderForm.elements[prefix+"cell_phone"]) { orderForm.elements[prefix+"cell_phone"].value = cellPhone; }
	if (orderForm.elements[prefix+"fax"]) { orderForm.elements[prefix+"fax"].value = fax; }
}

function updateStates(pbId, controlType, countryValue)
{
	var blockObj = document.getElementById("pb_" + pbId);
	var countryClassName = ""; var stateClassName = ""; var controlPrefix = "";
	if (controlType == "personal") {
		countryClassName = "personal-country"; 
		stateClassName = "personal-state"; 
		provinceClassName = "personal-province"; 
	} else if (controlType == "delivery") {
		countryClassName = "delivery-country"; 
		stateClassName = "delivery-state"; 
		provinceClassName = "personal-province"; 
		controlPrefix = "delivery_";
	}
	var classObjs = ""; var provinceObj = "";
	if ((countryClassName != "" || countryValue) && stateClassName != "") {
		var stateObj = blockObj.getElementsByClassName(stateClassName)[0];
		if (!stateObj) { return; } // state field is not active
		classObjs = blockObj.getElementsByClassName(provinceClassName);
		if (classObjs) { provinceObj = classObjs[0]; }
		var stateControl = stateObj.getElementsByClassName("field-control")[0];
		var stateComments = stateObj.getElementsByClassName("field-comments")[0];
		var stateRequired = stateObj.getElementsByClassName("field-required")[0];
		var stateFieldName = stateObj.getElementsByClassName("field-name")[0];
		var countryId = ""; 
		var countryObj = blockObj.getElementsByClassName(countryClassName)[0];
		if (countryObj) { 
			var countryControl = countryObj.getElementsByClassName("field-control")[0];
			countryId = countryControl.options[countryControl.selectedIndex].value;
		} else if (countryValue) {
			countryId = countryValue;
		} else {
			// check default value
			var defaultCountry = document.querySelector("input[name=default_country_id]");
			if (defaultCountry) { countryId = defaultCountry.value; }
		}
		// update states information
		// remove old list
		var totalOptions = stateControl.options.length;
		for (var i = totalOptions - 1; i >= 1; i--) {
			stateControl.options[i] = null;
		}
		// check and add new states list
		if (countryId == "") {
			stateObj.style.display = "block";
			stateControl.style.display = "none";
			if (stateRequired) { stateRequired.style.display = "none"; }
			if (stateComments) {
				stateComments.innerHTML = selectCountryFirst;
				stateComments.style.display = "inline";
			}
			if (provinceObj) {
				provinceObj.style.display = "none";
			}
		} else if (states[countryId]) {
			stateObj.style.display = "block";
			var countryStates = new Array();
			for(stateId in states[countryId]){
				countryStates.push({name: states[countryId][stateId], id: stateId});			
			}
			countryStates.sort(function(a, b) {
				var nameA = a.name.toUpperCase(); var nameB = b.name.toUpperCase(); 
				if (nameA < nameB) { return -1; }
				if (nameA > nameB) { return 1;  }
			  return 0;
			});
			for (var s = 0; s < countryStates.length; s++) {
				stateControl.options[stateControl.length] = new Option(countryStates[s].name, countryStates[s].id);
			}
			stateControl.style.display = "inline";
			if (stateRequired) { stateRequired.style.display = "inline"; }
			if (stateComments) {
				stateComments.innerHTML = "";
				stateComments.style.display = "none";
			}
			// check for state field name
			if (stateNames && stateNames[countryId] && stateNames[countryId] != "") {
				stateFieldName.innerHTML = stateNames[countryId];
			} else if (defaultStateField && defaultStateField != "") {
				stateFieldName.innerHTML = defaultStateField;
			}
			if (provinceObj) {
				provinceObj.style.display = "none";
			}
		} else {
			if (provinceObj) {
				provinceObj.style.display = "block";
				stateObj.style.display = "none";
			} else {
				stateControl.style.display = "none";
				if (stateRequired) { stateRequired.style.display = "none"; }
				if (stateComments) {
					stateComments.innerHTML = noStatesForCountry;
					stateComments.style.display = "inline";
				}
			}
		}
	}
	// check phone code controls
	if (typeof vaSettings !== 'undefined' && vaSettings["phone_codes"] && vaSettings["phone_codes"][countryId]) {
		var countryCode = vaSettings["country_codes"][countryId];
		var phoneCode = vaSettings["phone_codes"][countryId];
		var phoneFields = new Array("phone", "daytime_phone", "evening_phone", "cell_phone", "fax");
		for (var fieldKey in phoneFields) {
			var fieldName = phoneFields[fieldKey];
			var fieldObj = blockObj.querySelector("input[name="+controlPrefix+fieldName+"]");
			var codeObj = blockObj.querySelector("select[name="+controlPrefix+fieldName+"_code]");
			if (codeObj && fieldObj && fieldObj.value == "") {
				codeObj.value = countryCode+":"+phoneCode;
			}
		}
	}

	// check if we need update delivery states if there is no separate country selection for it
	if (controlType == "personal") {
		var deliveryCountryObj = blockObj.getElementsByClassName("delivery-country")[0];
		if (!deliveryCountryObj) { updateStates(pbId, "delivery", countryId); }
	}
	
}

function refreshForm()
{
	var orderForm = document.order_info;
	orderForm.operation.value = 'refresh'; 
	orderForm.ajax.value = '0';
	orderForm.submit();
}