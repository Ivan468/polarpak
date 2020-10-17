// shopping javacript
function reloadCartBlocks(responseData) {
	if (typeof responseData === 'string') {
		try { 
			responseData = JSON.parse(responseData); 
		} catch(e){
			alert("Bad response: " + responseData);
		}
	}
	var cart = responseData.cart; // check action: COMPARE / ADD / WISHLIST
	var controlId = responseData.control_id; // cartParams[0];
	var isErrors = (responseData.errors && responseData.errors != "");
	if (controlId) { vaStopSpin(controlId);	}
	
	if (cart == "ADD") {
		// check items indexes to show 'in cart' message
		if (responseData.added_indexes) {
			for (itemIndex in responseData.added_indexes) {
				var inCartObj = document.querySelector("#in-cart"+itemIndex);
				if (inCartObj) {
					inCartObj.className = inCartObj.className.replace(/hidden-block/gi, "").trim();
				}
			}
		}
		// check items indexes to hide 'add to cart' button
		if (responseData.hide_add_indexes) {
			for (itemIndex in responseData.hide_add_indexes) {
				var inCartObj = document.querySelector("#add"+itemIndex);
				if (inCartObj) {
					inCartObj.className += " hidden-block";
				}
			}
		}
	}

	// set all quantity controls to zero if multi-add active
	var multiAdd = 0; var itemsForm;
	if (responseData.form_name) { itemsForm = document.forms[responseData.form_name];	}
	if (itemsForm && itemsForm.multi_add) {
		multiAdd = itemsForm.multi_add.value;
	}
	if (multiAdd == 1 && itemsForm.items_indexes && itemsForm.items_indexes.value != "") {
		var indexes = itemsForm.items_indexes.value.split(",");
		for (var i = 0; i < indexes.length; i++) {
			var idx = indexes[i];
			var controlName = "quantity" + idx;
			if (itemsForm.elements[controlName]) {
				var elementType = itemsForm.elements[controlName].type;
				if (elementType == "text") {
					itemsForm.elements[controlName].value = 0;
				} else if (elementType == "select-one") {
					itemsForm.elements[controlName].selectedIndex = 0;
				}
			}
		}
	}
	// clear wishlist type if it's available
	if (itemsForm && itemsForm.saved_type_id) {
		itemsForm.saved_type_id.value = "";
	}

	if ((cart == "ADD" || cart == "QTY" || cart == "CLR" || cart == "RM") && !isErrors) {
		var carts = document.querySelectorAll("[data-type='cart']");
		for (var c = 0; c < carts.length; c++) {
			var cartBlock = carts[c];	
			if ((cartBlock.id || cartBlock.hasAttribute("data-id")) && cartBlock.hasAttribute("data-pb-id")) {
				var pbId = cartBlock.getAttribute("data-pb-id");
				var blockId = (cartBlock.hasAttribute("data-id")) ? cartBlock.getAttribute("data-id") : cartBlock.id;
				vaSpin(blockId);
				reloadBlock(pbId, blockId, "pb_type=cart");
			}
		}
		if (window.opener) { 
			try {
				var carts = window.opener.document.querySelectorAll("[data-type='cart']");
				for (var c = 0; c < carts.length; c++) {
					var cartBlock = carts[c];	
					if ((cartBlock.id || cartBlock.hasAttribute("data-id")) && cartBlock.hasAttribute("data-pb-id")) {
						var blockId = (cartBlock.hasAttribute("data-id")) ? cartBlock.getAttribute("data-id") : cartBlock.id;
						var pbId = cartBlock.getAttribute("data-pb-id");
						window.opener.vaSpin(blockId);
						window.opener.reloadBlock(pbId, blockId, "pb_type=cart");
					}
				}
			} catch(e){
				//catch error 'Access Denied'
			}
		}
	}


	if (responseData.block && responseData.block != "") {
		// show cart message or block
		showPopupFrame(responseData.block);
	} else if (responseData.errors && responseData.errors != "") {
		showMessageBlock(responseData.errors, controlId);
	} else if (responseData.success == 1) {
		if (responseData.message && responseData.message != "") {
			showMessageBlock(responseData.message, controlId);
		}
	}

}

function confirmBuy(formName, selectedIndex, buttonType, controlId)
{
	if (controlId) { vaSpin(controlId);	}
	var scParams = safeJsonParse(formName, "sc_params");
	var msgRequiredProperty = (scParams["msgRequiredProperty"]) ? scParams["msgRequiredProperty"]: "Please specify {property_name} for {product_name}!";
	var msgMinMax = (scParams["msgMinMax"]) ? scParams["msgMinMax"] : "Please enter a value between {min_value} and {max_value}.";
	var msgAddProduct = (scParams["msgAddProduct"]) ? scParams["msgAddProduct"] : "Add this product to your Shopping Cart?";
	var msgSelectProduct = (scParams["msgSelectProduct"]) ? scParams["msgSelectProduct"] : "Please select at least one product first.";
	var confirmAdd = (scParams["confirmAdd"]) ? scParams["confirmAdd"] : 1;

	var itemsForm = document.forms[formName];
	var siteUrl = (itemsForm.site_url) ? itemsForm.site_url.value : "";
	if (itemsForm.item_index) {
		itemsForm.item_index.value = selectedIndex; // assign index of product to be added to cart
	} else {
		addFormHidden(itemsForm, "item_index", selectedIndex);
	}
	var startIndex = 1;
	if (itemsForm.start_index) {
		startIndex = itemsForm.start_index.value;
	}
	var idx = selectedIndex;
	// check and add cart redirect option
	var redirectToCart = "3"; // 
	if (itemsForm.redirect_to_cart) {
		redirectToCart = itemsForm.redirect_to_cart.value;
	} else {
		addFormHidden(itemsForm, "redirect_to_cart", redirectToCart);
	}
	// add cart,form_name,control_id parameters if they doesn't exists
	if (!itemsForm.cart) { addFormHidden(itemsForm, "cart", "ADD"); }
	if (!itemsForm.form_name) { addFormHidden(itemsForm, "form_name", formName); } 
	if (!itemsForm.control_id) {
		addFormHidden(itemsForm, "control_id", controlId);
	} else {
		itemsForm.control_id.value = controlId;
	}

	if (buttonType == "wishlist") {
		itemsForm.cart.value = "WISHLIST";
	} else if (buttonType == "shipping") {
		itemsForm.cart.value = "SHIPPING";
	} else if (buttonType == "SHIPPINGADD" || buttonType == "CHECKOUT" || buttonType == "GOTOCHECKOUT") {
		itemsForm.cart.value = buttonType;
	} else if (buttonType == "compare") {
		itemsForm.cart.value = "COMPARE";
	} else {
		itemsForm.cart.value = "ADD";
	}
	if (itemsForm.originalAction) {
		itemsForm.target = "";
		itemsForm.action = itemsForm.originalAction;
	}
	// check initial index if it wasn't selected
	var indexes = new Array();
	if (!itemsForm.elements["item_id"+idx]) {
		if (itemsForm.items_indexes && itemsForm.items_indexes.value != "") {
			indexes = itemsForm.items_indexes.value.split(",");
			idx = indexes[0];
		} else {
			idx = startIndex;
		}
	}

	// check products one by one
	var selectedItems = 0;
	var itemNo = 0;
	do {
		itemNo++;

		// check product quantity
		var quantity = 1;
		if (itemsForm.elements["quantity"+idx]) {
			if (itemsForm.elements["quantity"+idx].selectedIndex) {
				quantity = parseInt(itemsForm.elements["quantity"+idx].options[itemsForm.elements["quantity"+idx].selectedIndex].value);
			} else {
				quantity = parseInt(itemsForm.elements["quantity"+idx].value);
			}
			if (isNaN(quantity)) { quantity = 1; } 
		}

		if (quantity > 0) {
			selectedItems++;
			var params = getProductParams(itemsForm, idx);
			var basePrice = params["base_price"];
			var productData = "";
			if (itemsForm.elements["product_data"+idx]) {
				productData = itemsForm.elements["product_data"+idx].value;
				try {
					productData = JSON.parse(productData);
				} catch(e) {
					alert(e + "\n" + productData); 
				}	
			}
			
			// check what options were selected and what options is active
			var returnedValues = checkOptions(itemsForm, idx);
			var selectedOptions = returnedValues[0];
			var activeOptions = returnedValues[1];
			// check options for requirements
			var prMessage = msgRequiredProperty;
	
			var productName = params["item_name"];
			for (prID in activeOptions) {
				if (itemsForm.elements["property_control"+idx+"_" + prID]) { // check if it is property control
					var prRequired = itemsForm.elements["property_required"+idx+"_" + prID].value;
					var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
					if (prRequired == 1 && activeOptions[prID] && !selectedOptions[prID]) {
						var propertyName = itemsForm.elements["property_name"+idx+"_" + prID].value;
						prMessage = prMessage.replace("\{property_name\}", propertyName);
						prMessage = prMessage.replace("\{product_name\}", productName);
						alert(prMessage);   
						if (controlId) { vaStopSpin(controlId);	}
						if (prControl == "WIDTH_HEIGHT") {
							if (!itemsForm.elements["property_width"+idx+"_" + prID].value) {
								itemsForm.elements["property_width"+idx+"_" + prID].focus();
							} else {
								itemsForm.elements["property_height"+idx+"_" + prID].focus();
							}
						} else if (prControl != "RADIOBUTTON" && prControl != "CHECKBOXLIST" && prControl != "TEXTBOXLIST" && prControl != "LABEL") {
							itemsForm.elements["property"+idx+"_" + prID].focus();
						}
						return false;
					}
				}
			}

			// check for width & height control if data correct
			for (prID in activeOptions) {
				if (itemsForm.elements["property_control"+idx+"_" + prID]) { // check if it is property control
					var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
					if (prControl == "WIDTH_HEIGHT") {
						var widthValue = itemsForm.elements["property_width"+idx+"_" + prID].value;
						var heightValue = itemsForm.elements["property_height"+idx+"_" + prID].value;
						if (widthValue != "" || heightValue != "") {
							widthValue = parseFloat(widthValue);
							if (isNaN(widthValue)) { widthValue = 0; }
							heightValue = parseFloat(heightValue);
							if (isNaN(heightValue)) { heightValue = 0; }
							var prices = productData["options"][prID]["prices"]; 
							var minWidth; var maxWidth; var minHeight; var maxHeight;
							for (var curWidth in prices) {
								curWidth = parseFloat(curWidth);
								if (minWidth > curWidth || minWidth === undefined) { minWidth = curWidth; }
								if (maxWidth < curWidth || maxWidth === undefined) { maxWidth = curWidth; }
								for (var curHeight in prices[curWidth]) {
									curHeight = parseFloat(curHeight);
									if (minHeight > curHeight || minHeight === undefined) { minHeight= curHeight; }
									if (maxHeight < curHeight || maxHeight === undefined) { maxHeight = curHeight; }
								}
							}
						  //*
							if (widthValue < minWidth || widthValue > maxWidth) {
								var msg = msgMinMax.replace("\{min_value\}", minWidth);
								msg = msg.replace("\{max_value\}", maxWidth);
								alert(msg);
								itemsForm.elements["property_width"+idx+"_" + prID].focus();
								if (controlId) { vaStopSpin(controlId);	}
								return false;
							}
							if (heightValue < minHeight|| heightValue > maxHeight) {
								var msg = msgMinMax.replace("\{min_value\}", minHeight);
								msg = msg.replace("\{max_value\}", maxHeight);
								alert(msg);
								itemsForm.elements["property_height"+idx+"_" + prID].focus();
								if (controlId) { vaStopSpin(controlId);	}
								return false;
							}//*/
						}
					}
				}
			}

	  
			// calculate price for selected options
			var propertiesPrice = calculateOptionsPrice(itemsForm, idx, selectedOptions);
			var isPriceEdit = params["pe"];
			var productPrice = 0;
			if (typeof(isPriceEdit) != "undefined" && isPriceEdit) {
				var userPrice = parseFloat(itemsForm.elements["price"+idx].value);
				productPrice = userPrice + params["comp_price"] + propertiesPrice;
			} else {
				productPrice = basePrice + params["comp_price"] + propertiesPrice;
			}
	  
			if (params["zero_product_action"] == 2 && productPrice == 0) {
				alert(params["zero_product_warn"]);
				return false;
			}
		}

		// check next index
		idx = "";
		if (selectedIndex == "") {
			var nextIndex = "";
			if (indexes.length > 0) {
				nextIndex = (indexes.length > itemNo) ? indexes[itemNo] : "";
			} else {
				nextIndex = startIndex + itemNo;
			}
			if (nextIndex != "" && itemsForm.elements["item_id"+nextIndex]) {
				idx = nextIndex;
			}
		}
		// end index check
		
	} while (idx != "");

  // submit form
	if (buttonType == "wishlist") {
		var savedTypesHidden = "0";
		if (document.saved_types.saved_types_hidden) {
			// check if we don't need to show popup win
			savedTypesHidden = document.saved_types.saved_types_hidden.value; 
		}
		if (savedTypesHidden == "1") {
			// assign default type_id from hidden popup
			itemsForm.saved_type_id.value = (document.saved_types.type_id) ? document.saved_types.type_id.value : 0;
		}
		// check if type_id was selected 
		var savedTypeId = itemsForm.saved_type_id.value;
		if (savedTypeId === "") {
			showWishlistTypes(formName, selectedIndex);
		} else {
			if (redirectToCart == "3" || redirectToCart == "ajax" || redirectToCart == "popup") {
				itemsForm.rnd.value = ""; // don't need random values for AJAX
				postAjax(siteUrl+"cart_add.php", reloadCartBlocks, "", itemsForm);
			} else {
				itemsForm.submit();
			}
		}
		return false;
	} else if (buttonType == "shipping") {
		popupShippingFrame();
		itemsForm.originalAction = itemsForm.action; // save original action value
		itemsForm.action = "shipping_calculator.php?form_name="+encodeURIComponent(formName)+"&selected_index="+encodeURIComponent(selectedIndex)+"&control_id="+encodeURIComponent(controlId);
		itemsForm.target = "shipping_frame";
		itemsForm.submit();
		return false;
	} else if (buttonType == "CHECKOUT" || buttonType == "SHIPPINGADD") {
		// for checkout process we don't need to confirm purchase
		itemsForm.submit();
		return false;
	} else if (buttonType == "compare" || buttonType == "COMPARE") {
		if (redirectToCart == "3" || redirectToCart == "ajax" || redirectToCart == "popup") {
			itemsForm.rnd.value = ""; // don't need random values for AJAX
			postAjax(siteUrl+"cart_add.php", reloadCartBlocks, "", itemsForm);
		} else {
			itemsForm.submit();
		}
		return false;
	} else {
		if (selectedItems > 0) {
			// check and submit form to add product to the cart
			var submitForm = true;
			if (buttonType == "cart" && confirmAdd == "1") {
				submitForm = confirm(msgAddProduct);
			}
			if (submitForm) {
				if (redirectToCart == "3" || redirectToCart == "ajax" || redirectToCart == "popup") {
					// AJAX option selected
					submitForm = false; // don't need to submit form
					if (itemsForm.rnd) {
						itemsForm.rnd.value = ""; // don't need random values for AJAX
					}
					//var cartParams = new Array(controlId, formName);
					postAjax(siteUrl+"cart_add.php", reloadCartBlocks, "", itemsForm);
				} else {
					itemsForm.submit();
				}
			} else {
				if (controlId) { vaStopSpin(controlId);	}
			}
		} else {
			if (controlId) { vaStopSpin(controlId);	}
			alert(msgSelectProduct);
		}
		return false;
	}
}

function fastAdd(formName, controlId)
{
	var itemsForm = document.forms[formName];
	// check global redirect option
	var redirectToCart = "";
	if (itemsForm.redirect_to_cart) {
		redirectToCart = itemsForm.redirect_to_cart.value;
	}
	if (!itemsForm.form_name) { addFormHidden(itemsForm, "form_name", formName); } 
	if (!itemsForm.control_id) {
		addFormHidden(itemsForm, "control_id", controlId);
	} else {
		itemsForm.control_id.value = controlId;
	}

	// check and submit form to add product to the cart
	if (redirectToCart == "3" || redirectToCart == "ajax" || redirectToCart == "popup") {
		// AJAX option selected
		if (itemsForm.rnd) {
			itemsForm.rnd.value = ""; // don't need random values for AJAX
		}
		//var cartParams = new Array(controlId, formName);
		postAjax("cart_add.php", reloadCartBlocks, "", itemsForm);
	} else {
		itemsForm.submit();
	}
	return false;
}

function removeCartItem(cartId, pdId)
{
	var cartParams = new Array();
	cartParams["blockParams"] = new Array();
	cartParams["blockParams"]["active_pb_id"] = pdId;
	var url = "cart_add.php?cart=RM&cart_id="+encodeURIComponent(cartId);
	callAjax(url, reloadCartBlocks, cartParams);
}

function widgetCall(widgetAction, itemId, controlId, formName, itemIndex)
{
	if (controlId) { vaSpin(controlId);	}

	var urlObj = document.getElementById(formName+"site_url");
	var siteUrl = (urlObj) ? urlObj.value : "";
	var qtyObj = document.getElementById("quantity"+formName+"_"+itemIndex);
	var qty = 1;
	if (qtyObj && qtyObj.value) {
		qty = qtyObj.value;
		if (isNaN(qty)) { qty = 1; }
	}

	var cartAddedPopup = 1;
	var cartPopupView = 1;
	var cartPopupCheckout = 1;

	var cartUrl = siteUrl+"cart_add.php";
	cartUrl += "?cart=" + encodeURIComponent(widgetAction);;
	cartUrl += "&redirect_to_cart=3";
	cartUrl += "&item_id=" + encodeURIComponent(itemId);
	cartUrl += "&quantity=" + encodeURIComponent(qty);
	cartUrl += "&form_name=" + encodeURIComponent(formName);
	cartUrl += "&control_id=" + encodeURIComponent(controlId);
	cartUrl += "&cart_added_popup=" + encodeURIComponent(cartAddedPopup);
	cartUrl += "&cart_popup_view=" + encodeURIComponent(cartPopupView);
	cartUrl += "&cart_popup_checkout=" + encodeURIComponent(cartPopupCheckout);
	cartUrl += "&callback=reloadCartBlocks";

	// check page language to use it for widget
	var metaObj = document.querySelector("meta[http-equiv='Content-Language']")
	if (metaObj) {
		var languageCode = metaObj.getAttribute("content");
		cartUrl += "&language_code="+ encodeURIComponent(languageCode);
	}

	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = cartUrl;
	document.getElementsByTagName("head")[0].appendChild(script);
	return false;
}

function confirmSubscription(itemForm)
{
	var scParams = safeJsonParse(formName, "sc_params");
	var msgAddSubscription = (scParams["msgAddSubscription"]) ? scParams["msgAddSubscription"] : "Add this subscription to your Shopping Cart?";
	var confirmAdd = (scParams["confirmAdd"]) ? scParams["confirmAdd"] : 1;

	if (confirmAdd == "1") {
		return confirm(msgAddSubscription);
	} else {
		return true;
	}
}

function addToWishlist()
{
	var formName = document.saved_types.form_name.value;
	if (formName != "") {
		var itemsForm = document.forms[formName];
		var typesForm = document.saved_types;
		var typesTotal = parseInt(document.saved_types.saved_types_total.value);
		var typeId = "";
		if (typesTotal > 1) {
			typeId = document.saved_types.type_id.options[document.saved_types.type_id.selectedIndex].value;
		} else {
			typeId = (document.saved_types.type_id) ? document.saved_types.type_id.value : 0; 
		}
		if (typeId === "") {
			alert("Please select a type");
		} else {
			var controlId = "";
			if (itemsForm.control_id) {
				controlId = itemsForm.control_id.value;
			}
			itemsForm.saved_type_id.value = typeId;
			var itemIndex = typesForm.item_index.value;
			hideWishlistTypes();
			confirmBuy(formName, itemIndex, "wishlist", controlId);
		}
	} else {
		alert("Error: can't find formName parameter.");
	}
}

function popupShippingWin(shippingUrl)
{
	var shippingWin = window.open (shippingUrl, 'shippingWin', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=400');
	shippingWin.focus();
}

function popupShippingFrame(itemForm)
{							   
	var shippingOpacity = document.getElementById("shipping_opacity");
	shippingOpacity.style.opacity	= "0.6";
	shippingOpacity.style.mozOpacity = "0.6";
	shippingOpacity.style.filter	 = "alpha(opacity=60)";
	var shippingShadow = document.getElementById("shipping_shadow");

	var pageSize = getPageSize();
	var pageScroll = getScroll();
	var arrayPageSizeWithScroll = getPageSizeWithScroll();

	var winLeft = 5; var winTop = 5;
	if (pageSize[0] > 620) {
		winLeft = pageScroll[0] + (pageSize[0]-600) / 2;
	}
	if (pageSize[1] > 420) {
		winTop = pageScroll[1] + (pageSize[1]-400) / 2;
	}
	shippingShadow.style.left = winLeft + "px";
	shippingShadow.style.top = winTop + "px";

	shippingOpacity.style.width = arrayPageSizeWithScroll[0] + "px";
	shippingOpacity.style.height = arrayPageSizeWithScroll[1] + "px";

	shippingShadow.style.display = "block";		 
	shippingOpacity.style.display = "block";			
}

function addFormHidden(formObj, hiddenName, hiddenValue)
{
	var inputObj = document.createElement('input');
	inputObj.type = "hidden";
	inputObj.name = hiddenName;
	inputObj.value = hiddenValue;
	formObj.appendChild(inputObj);
}

function hideShippingFrame()
{							   
	var shippingOpacity = document.getElementById("shipping_opacity");
	var shippingShadow = document.getElementById("shipping_shadow");
	var shippingPage = document.getElementById("shipping_page");

	shippingOpacity.style.display = "none";		 
	shippingShadow.style.display = "none";		  
	shippingPage.src = "";
}

function showWishlistTypes(formName, selectedIndex)
{		
	var itemsForm = document.forms[formName];
	var typesForm = document.saved_types;
	if (typesForm.form_name) {
		typesForm.form_name.value = formName;
	} else {
		addFormHidden(typesForm, "form_name", formName);
	}
	if (typesForm.item_index) {
		typesForm.item_index.value = selectedIndex; 
	} else {
		addFormHidden(typesForm, "item_index", selectedIndex);
	}
	// show wishlist shadow area
	var wishlistArea = document.getElementById("wishlist_area");
	wishlistArea.style.display = "block";		  
	// show wishlist types block below pressed button
	var pageSize = getPageSize()
	var pageScroll = getScroll();
	var popupObj = document.getElementById("wishlist_popup");
	popupObj.style.display = "block";		  
	var popupWidth = popupObj.offsetWidth;
	var popupHeight = popupObj.offsetHeight
	var controlObj = document.getElementById("wishlist" + selectedIndex);
	var popupLeft = 0; var popupTop = 0;
	if (controlObj) {
		popupLeft = findPosX(controlObj);
		popupTop = findPosY(controlObj, true);
	} else {
		popupLeft = pageScroll[0] + pageSize[0]/2 - popupWidth/2;
		popupTop = pageScroll[1] + pageSize[1]/2 - popupHeight/2;
	}
	if (popupWidth > pageSize[0]) {
		popupLeft = 0;
	} else if (popupLeft + popupWidth > pageSize[0]) {
		popupLeft = pageSize[0] - popupWidth;
	}
	popupObj.style.left = popupLeft+"px";
	popupObj.style.top  = popupTop+"px";
}

function hideWishlistTypes()
{	
	var typesForm = document.saved_types;
	var itemIndex = "";
	if (typesForm.item_index) {
		itemIndex = typesForm.item_index.value;
		typesForm.item_index.value = "";
	}
	if (itemIndex) { vaStopSpin("wishlist"+itemIndex); }
	typesForm.form_name.value = "";
	var wishlistArea = document.getElementById("wishlist_area");
	wishlistArea.style.display = "none";		  
	var wishlistPopup = document.getElementById("wishlist_popup");
	wishlistPopup.style.display = "none";		  
}

function changeWishlistType()
{
	var prevTypeId = document.saved_types.prev_type_id.value;
	var typeIdControl = document.saved_types.type_id;
	var selectedTypeId = typeIdControl.options[typeIdControl.selectedIndex].value;
	document.saved_types.prev_type_id.value = selectedTypeId;
	if (prevTypeId != selectedTypeId) {
		if (prevTypeId != "") {
			var typeDescBlock = document.getElementById("type_desc_" + prevTypeId);
			typeDescBlock.style.display = "none";		   
		}
		if (selectedTypeId != "") {
			var typeDescBlock = document.getElementById("type_desc_" + selectedTypeId);
			typeDescBlock.style.display = "block";		  
		}
	}
}

function optionImageSelect(formName, idx, optionId, newValueId)
{
	var itemsForm = document.forms[formName];
	var viewType = itemsForm.type.value;
	var productData = itemsForm.elements["product_data"+idx].value;;
	productData = JSON.parse(productData);
	var optionValues = productData["options"][optionId]["values"];
	var valueData = productData["options"][optionId]["values"][newValueId];
	var valueTitle = valueData["value"];
	var imageSmall = valueData["image_small"];
	var imageLarge = valueData["image_large"];
	var imageSuper = valueData["image_super"];
	var mainImage = (viewType == "details") ? imageLarge : imageSmall;
	if (mainImage != "") {
		rolloverImage('option'+newValueId, mainImage, 'image_'+idx, 'super_link_'+idx, imageSuper);
	}
	var oldValueId = itemsForm.elements["property"+idx+"_"+optionId].value; // get old value to allow deselect value
	for (keyId in optionValues) {
		var imageObj = document.getElementById("option_image"+idx+"_"+keyId);
		if (keyId == newValueId && keyId != oldValueId) {
			imageObj.className = 'imageSelected';
		} else {
			imageObj.className = 'imageSelect';
		}
	}
	// save id and value title
	var oldValueId = itemsForm.elements["property"+idx+"_"+optionId].value; // get old value to allow deselect value
	if (newValueId == oldValueId) {
		itemsForm.elements["property"+idx+"_"+optionId].value = "";
	} else {
		itemsForm.elements["property"+idx+"_"+optionId].value = newValueId;
	}
	var valueObj = document.getElementById("optionValue"+idx+"_"+optionId);
	if (valueObj) {
		if (newValueId == oldValueId) {
			valueObj.innerHTML = "";
		} else {
			valueObj.innerHTML = valueTitle;
		}
	}
	// property was changed
	changeProperty(formName, idx);
}


function optionImageShow(formName, idx, optionId, newValueId)
{
	var itemsForm = document.forms[formName];
	var viewType = itemsForm.type.value;
	var productData = itemsForm.elements["product_data"+idx].value;;
	productData = JSON.parse(productData);
	try {
		var optionValues = productData["options"][optionId]["values"];
		var valueData = productData["options"][optionId]["values"][newValueId];
		var imageSmall = (typeof valueData["image_small"] === "undefined") ? "" : valueData["image_small"];
		var imageLarge = (typeof valueData["image_large"] === "undefined") ? "" : valueData["image_large"];
		var imageSuper = (typeof valueData["image_super"] === "undefined") ? "" : valueData["image_super"];
		var mainImage = (viewType == "details") ? imageLarge : imageSmall;
		if (mainImage != "") {
			rolloverImage('option'+newValueId, mainImage, 'image_'+idx, 'super_link_'+idx, imageSuper);
		}
	} catch (e) {}
}

function optionImagesToggle(idx, optionId)
{

	var toggleImage = document.getElementById("toggleImage"+idx+"_"+optionId);
	var imagesBlock = document.getElementById("optionImages"+idx+"_"+optionId);
	var optionBlock = document.getElementById("pr"+idx+"_"+optionId);
	if (imagesBlock.className == "optionImages") {
		toggleImage.src = "images/icons/close.png";
		imagesBlock.className = "optionImagesOpen";
		optionBlock.style.border = "1px solid #656565";
	} else {
		toggleImage.src = "images/icons/arrow-down.png";
		imagesBlock.className = "optionImages";
		optionBlock.style.border = "none";
	}

}

function changeProperty(formName, idx)
{
	var itemsForm = document.forms[formName];

	var selectedOptions = new Array();
	var priceControl = "";
	var htmlControl = false;
	var itemId = itemsForm.elements["item_id"+idx].value;;
	var taxPercent = 0;
	var productData = itemsForm.elements["product_data"+idx].value;;
	productData = JSON.parse(productData);

	var params = getProductParams(itemsForm, idx);
	var taxNote = params["tax_note"];
	var pointsBase = params["base_points_price"];
	var prIDs = params["properties_ids"];
	var formId = params["form_id"];
	var stockLevel = getParamValue(params, "sl", "int");
	var useStockLevel = getParamValue(params, "use_sl", "int")
	var inStock = getParamValue(params, "in_sm", "txt");
	var outStock = getParamValue(params, "out_sm", "txt")

	if (itemsForm.elements["tax_percent"+idx] && itemsForm.elements["tax_percent"+idx].value != "") {
		taxPercent = parseFloat(itemsForm.elements["tax_percent"+idx].value);
		if (isNaN(taxPercent)) { taxPercent = 0; }
	}

	if (itemId != "" && document.getElementById) {
		priceControl = document.getElementById("sales_price" + idx);
		if (!priceControl) {
			priceControl = document.getElementById("price" + idx);
		}
	} 
	var pointsPriceControl = document.getElementById("points_price" + idx);

	// check what options were selected and what options is active
	var returnedValues = checkOptions(itemsForm, idx);
	var selectedOptions = returnedValues[0];
	var activeOptions = returnedValues[1];
	// calculate price for selected options
	var totalAdditionalPrice = calculateOptionsPrice(itemsForm, idx, selectedOptions);

	// check stock levels for options
	var optionUseStock = 0; var optionStockLevel = 0; 
	for (prID in selectedOptions) {
		if (itemsForm.elements["property_control"+idx+"_" + prID]) { // check if it is property control
			var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
			if (prControl == "LISTBOX" || prControl == "RADIOBUTTON") {
				optionUseStock = getOptionValue(itemsForm, "use_sl_" + selectedOptions[prID]);
				if (optionUseStock == 1) {
					optionStockLevel = getOptionValue(itemsForm, "sl_" + selectedOptions[prID]);
					if (useStockLevel == 0 || stockLevel > optionStockLevel) {
						stockLevel = optionStockLevel;
						useStockLevel = 1;
					}
				}
			} else if (prControl == "CHECKBOXLIST" || prControl == "TEXTBOXLIST") {
				var values = selectedOptions[prID];
				for (valueId in values) {
					optionUseStock = getOptionValue(itemsForm, "use_sl_" + valueId);
					if (optionUseStock == 1) {
						optionStockLevel = getOptionValue(itemsForm, "sl_" + valueId);
						if (useStockLevel == 0 || stockLevel > optionStockLevel) {
							stockLevel = optionStockLevel;
							useStockLevel = 1;
						}
					}
				}   
			}
		}
	}
	// end options stock levels

	// change stock level and stock message
	var obj = document.getElementById("sl" + idx);
	var blockObj = document.getElementById("block_sl" + idx);
	if (obj) {
		if (useStockLevel == 1) {
			obj.innerHTML = stockLevel;
			if (blockObj) { blockObj.style.display = "block"; }
		} else {
			obj.innerHTML = "";
			if (blockObj) { blockObj.style.display = "none"; }
		}
	}
	obj = document.getElementById("sm" + idx);
	blockObj = document.getElementById("block_sm" + idx);
	if (obj) {
		var stockMessage = "";
		if (useStockLevel == 0 || stockLevel > 0) {
			stockMessage = inStock;
		} else {
			stockMessage = outStock;
		}
		obj.innerHTML = stockMessage;
		if (blockObj) { 
			if (stockMessage == "") {
				blockObj.style.display = "none";
			} else {
				blockObj.style.display = "block"; 
			}
		}
	}

	// hide or show property blocks
	for (prID in activeOptions) {
		if (itemsForm.elements["property_control" + idx + "_" + prID]) { // check if it is property control
			var propertyBlock = document.getElementById("pr" + idx + "_" + prID);
			if (propertyBlock) { // custom HTML code in table view may not have it
				if (activeOptions[prID]) {
					propertyBlock.style.display = "block";			  
				} else {
					propertyBlock.style.display = "none";			   
				}
			}
		}
	}

	// show hide image for subcomponents and update main image if it was set for option
	for (prID in activeOptions) {
		if (itemsForm.elements["property_control"+idx+"_" + prID]) { // check if it is property control
			var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
			if (activeOptions[prID] && (prControl == "LISTBOX" || prControl == "RADIOBUTTON")) {
				var prValue = selectedOptions[prID];

				optionImageShow(formName, idx, prID, prValue); // check main image for update
	  
				var objId = formId + "_" + prID; // id for current product option
				if (prValue != "") {
					var image_button = document.getElementById("option_image_action"+idx+"_" + prID);
					if (!image_button) {
						var image_button	   = document.createElement('a');			   
						image_button.id		= "option_image_action"+idx+"_" + prID;
						image_button.href	  = "#";
						image_button.onclick   = popupImage;
						image_button.style.display = "none";
						image_button.innerHTML = "<img src='images/icons/view_page.gif' alt='View' border='0'/>";
						var propertyObj = document.getElementById("pr"+idx+"_" + prID);
						if (propertyObj) { propertyObj.appendChild(image_button); }
					}			   
					if (itemsForm.elements["option_image"+idx+"_" + prValue]) {
						var image = itemsForm.elements["option_image"+idx+"_" + prValue].value;
						if (itemsForm.elements["option_image_action"+idx+"_" + prValue]) {
							image_button.onclick = (itemsForm.elements["option_image_action"+idx+"_" + prValue].onclick);
						}				   
						image_button.style.display = "inline";
						image_button.href  = image;
						image_button.title = itemsForm.elements["property"+idx+"_" + prID].options[itemsForm.elements["property"+idx+"_" + prID].selectedIndex].text;
					} else {
						image_button.style.display = "none";
					}
				} else {
					var image_button = document.getElementById("option_image_action"+idx+"_" + prID);
					if (image_button) {
						image_button.style.display = "none";
					}
				}
			}
		}
	}

	var basePrice = 0;
	if (params["pe"] == "1") {
		// get base price from textbox control when price could be edit
		if (itemsForm.elements["price"+idx]) {
			basePrice = itemsForm.elements["price"+idx].value;
			basePrice = basePrice / params["crate"];
		}
	} else {
		basePrice = params["base_price"]; 
	}
	var baseTax = 0;
	// check product quantity
	var quantity = 1;
	if (itemsForm.elements["quantity"+idx]) {
		if (itemsForm.elements["quantity"+idx].selectedIndex) {
			quantity = parseInt(itemsForm.elements["quantity"+idx].options[itemsForm.elements["quantity"+idx].selectedIndex].value);
		} else {
			quantity = parseInt(itemsForm.elements["quantity"+idx].value);
		}
		if (isNaN(quantity)) { quantity = 1; } 
	}
	var isQuantityPrice = false;
	if(params["quantity_price"]) { 
		var prices = params["quantity_price"]; 
		if (prices != "") {
			prices = prices.split(",");
			for (var p = 0; p < prices.length; p = p + 5) {
				var minQuantity = parseInt(prices[p]);
				var maxQuantity = parseInt(prices[p + 1]);
				if (quantity >= minQuantity && quantity <= maxQuantity) {
					isQuantityPrice = true;
					basePrice = parseFloat(prices[p + 2]);
					baseTax = parseFloat(prices[p + 3]);
					var propertiesDiscount = parseFloat(prices[p + 4]);
					if (propertiesDiscount > 0) {
						totalAdditionalPrice -= (Math.round(totalAdditionalPrice * propertiesDiscount) / 100);
					}
					break;
				}
			}
		}
	}
	
	var price = basePrice + totalAdditionalPrice;
	var taxAmount = 0; var productPrice = 0; var taxPrice = 0; var priceExcl = 0;
	if (params["tax_prices_type"] == 1) {
		// price already includes tax
		if (isQuantityPrice) {
			taxPrice = Math.round((price) * 100) / 100; 
			// calculate options tax
			var optionsTax = (Math.round(totalAdditionalPrice * 100) - Math.round(totalAdditionalPrice * 10000 / ( 100 + taxPercent))) / 100; 
			taxAmount = baseTax + optionsTax; 
		} else {
			taxPrice = Math.round((price + params["comp_price"]) * 100) / 100; 
			taxAmount = (Math.round(price * 100) - Math.round(price * 10000 / ( 100 + taxPercent))) / 100; 
		}
		if (isQuantityPrice) {
			productPrice = Math.round((price - taxAmount) * 100) / 100;
		} else {
			productPrice = Math.round((price - taxAmount + params["comp_price"] - params["comp_tax"]) * 100) / 100;
		}
		priceExcl = productPrice;
	} else {
		if (isQuantityPrice) {
			// calculate options tax
			var optionsTax = Math.round(totalAdditionalPrice * taxPercent) / 100; 
			taxAmount = baseTax + optionsTax; 
			productPrice = Math.round((price) * 100) / 100;
			taxPrice = Math.round((productPrice + taxAmount) * 100) / 100; 
		} else {
			taxAmount = Math.round(price * taxPercent) / 100; 
			productPrice = Math.round((price + params["comp_price"]) * 100) / 100;
			taxPrice = Math.round((productPrice + taxAmount + params["comp_tax"]) * 100) / 100; 
		}
		priceExcl = productPrice;
	}

	if (params["show_prices"] == 2) {
		productPrice = taxPrice;
		taxPrice = priceExcl;
	} else if (params["show_prices"] == 3) {
		productPrice = taxPrice;
	}

	if (priceControl) {
		if (params["pe"] == "1") {
			// if user can edit price do nothing
		} else {
			if (params["zero_price_type"] != 0 && productPrice == 0) {
				if (params["zero_price_type"] == 1) { params["zero_price_message"] = ""; }
				priceControl.innerHTML = params["zero_price_message"];
			} else {
				priceControl.innerHTML = params["cleft"] + formatNumber(productPrice * params["crate"], params["cdecimals"], params["cpoint"], params["cseparator"]) + params["cright"];
			}
			priceBlockControl = document.getElementById("price_block"+idx);
			if (priceBlockControl) {
				if (params["zero_price_type"] == 1 && productPrice == 0) {
					priceBlockControl.style.display = "none";
				} else {
					priceBlockControl.style.display = "block";
				}
			}
		}
	}
	taxPriceControl = document.getElementById("tax_price" + idx);
	if (taxPriceControl) {
		if (params["zero_price_type"] != 0 && taxPrice == 0) {
			taxPriceControl.innerHTML = "";
		} else {
			if (taxNote != "") { taxNote = " " + taxNote; }
			taxPriceControl.innerHTML = "(" + params["cleft"] + formatNumber(taxPrice * params["crate"], params["cdecimals"], params["cpoint"], params["cseparator"]) + params["cright"] + taxNote + ")";
		}
	}
	if (pointsPriceControl) {
		var pointsPrice = 0;
		if (params["pe"] == "1") {
			pointsPrice = (basePrice + totalAdditionalPrice) * params["points_rate"];
		} else {
			pointsPrice = pointsBase + (totalAdditionalPrice * params["points_rate"]);
		}
		pointsPriceControl.innerHTML = formatNumber(pointsPrice, params["points_decimals"]);
	}

}

function checkOptions(itemsForm, idx)
{
	var params = getProductParams(itemsForm, idx);
	var prIDs = params["properties_ids"];
	var selectedOptions = new Array();
	var activeOptions = new Array();
	var returnValues = new Array();

	// first check of all selected options if properties available for the product block
	if (prIDs && prIDs != "") {
		var properties = prIDs.split(",");
		for ( var i = 0; i < properties.length; i++) {
			var prID = properties[i];
			var prValue = ""; 
			if (itemsForm.elements["property_control"+idx+"_" + prID]){  //P
				var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
			}

			if (prControl == "LISTBOX") {
				prValue = itemsForm.elements["property"+idx+"_" + prID].options[itemsForm.elements["property"+idx+"_" + prID].selectedIndex].value;
				if (prValue != "") {
					selectedOptions[prID] = prValue;
				}
			} else if (prControl == "RADIOBUTTON") {
				var radioControl = itemsForm.elements["property"+idx+"_" + prID];
				if (radioControl) { // check if there any active values for radio button available
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
				}
				if (prValue != "") {
					selectedOptions[prID] = prValue;
				}
			} else if (prControl == "CHECKBOXLIST") {
				if (itemsForm.elements["property_total"+idx+"_" + prID]) {
					var totalOptions = parseInt(itemsForm.elements["property_total"+idx+"_" + prID].value);
					for ( var ci = 1; ci <= totalOptions; ci++) {
						if (itemsForm.elements["property"+idx+"_" + prID + "_" + ci].checked) {
							var checkedValue = itemsForm.elements["property"+idx+"_" + prID + "_" + ci].value;
							if (!selectedOptions[prID]) {
								selectedOptions[prID] = new Array();
							}
							selectedOptions[prID][checkedValue] = 1;
						}
					}
				} 
			} else if (prControl == "TEXTBOXLIST") {
				if (itemsForm.elements["property_total"+idx+"_" + prID]) {
					var totalOptions = parseInt(itemsForm.elements["property_total"+idx+"_" + prID].value);
					for ( var ci = 1; ci <= totalOptions; ci++) {
						if (itemsForm.elements["property"+idx+"_" + prID + "_" + ci].value != "") {
							var valueId = itemsForm.elements["property_value"+idx+"_" + prID + "_" + ci].value;
							var valueText = itemsForm.elements["property"+idx+"_" + prID + "_" + ci].value;
							if (!selectedOptions[prID]) {
								selectedOptions[prID] = new Array();
							}
							selectedOptions[prID][valueId] = valueText;
						}
					}
				} 
			} else if (prControl == "LABEL"){
				// get from hidden control
				if (itemsForm.elements["property"+idx+"_" + prID]) {
					prValue = itemsForm.elements["property"+idx+"_" + prID].value;
					if (prValue != "") {
						selectedOptions[prID] = prValue;
					}
				}
			} else if (prControl == "WIDTH_HEIGHT"){
				var widthValue = itemsForm.elements["property_width"+idx+"_" + prID].value;
				var heightValue = itemsForm.elements["property_height"+idx+"_" + prID].value;
				if (widthValue != "" && heightValue != "") {
					selectedOptions[prID] = new Array(); 
					selectedOptions[prID]["width"] = widthValue;
					selectedOptions[prID]["height"] = heightValue;
				}
			} else {
				// check if property exists on page
				prValue = (itemsForm.elements["property"+idx+"_" + prID]) ? itemsForm.elements["property"+idx+"_" + prID].value : "";
				if (prValue != "") {
					selectedOptions[prID] = prValue;
				}
			}
		}
	}

	// second check for active options and correct selected options if necessary
	if (prIDs && prIDs != "") {
		do {
			// save how many selected options we have at start
			var startSelectedNumber = selectedOptions.length;
			// check availability of parent options	 
			var properties = prIDs.split(",");
			for ( var i = 0; i < properties.length; i++) {
				var prID = properties[i];
				var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
				if (itemsForm.elements["property_parent_id"+idx+"_" + prID]){ //P
					var parentPropertyId = itemsForm.elements["property_parent_id"+idx+"_" + prID].value;
				}			   
				if (itemsForm.elements["property_parent_value_id"+idx+"_" + prID]){ //P
					var parentValueId = itemsForm.elements["property_parent_value_id"+idx+"_" + prID].value;
				}			   
				var showProperty = true;
				if (parentPropertyId != "") {
					if (!selectedOptions[parentPropertyId]) {
						showProperty = false;
					} else if (parentValueId != "") {
						// check parent control
						var parentControl = itemsForm.elements["property_control"+idx+"_" + parentPropertyId].value;
						// for multi-listing we need to different check of selected value then for single control
						if ((parentControl == "CHECKBOXLIST" || parentControl == "TEXTBOXLIST") && !selectedOptions[parentPropertyId][parentValueId]) {
							showProperty = false;
						} else if (parentControl != "CHECKBOXLIST" && parentControl != "TEXTBOXLIST" && selectedOptions[parentPropertyId] != parentValueId) {
							showProperty = false;
						}
					}
				}
				activeOptions[prID] = showProperty;
				if (!showProperty) {
					// delete from selected
					if (selectedOptions[prID]) {
						delete selectedOptions[prID];
					}
	  
					// clear all options
					var prControl = itemsForm.elements["property_control"+idx+"_" + prID].value;
					if (prControl == "LISTBOX") {
						var selectedIndex = itemsForm.elements["property"+idx+"_" + prID].selectedIndex;
						if (selectedIndex > 0) {
							itemsForm.elements["property"+idx+"_" + prID].options[0].selected = true;
						}
					} else if (prControl == "RADIOBUTTON") {
						var radioControl = itemsForm.elements["property"+idx+"_" + prID];
						if (radioControl) { // check if there any active values for radio button available
							if (radioControl.length) {
								for ( var ri = 0; ri < radioControl.length; ri++) {
									radioControl[ri].checked = false;
								}
							} else {
								radioControl.checked = false;
							}
						}	  
					} else if (prControl == "CHECKBOXLIST") {
						var totalOptions = parseInt(itemsForm.elements["property_total"+idx+"_" + prID].value);
						for ( var ci = 1; ci <= totalOptions; ci++) {
							itemsForm.elements["property"+idx+"_" + prID + "_" + ci].checked = false;
						}
					} else if (prControl == "TEXTBOXLIST") {
						var totalOptions = parseInt(itemsForm.elements["property_total"+idx+"_" + prID].value);
						for ( var ci = 1; ci <= totalOptions; ci++) {
							// don't erase user or default text in textbox controls
							//itemsForm.elements["property"+idx+"_" + prID + "_" + ci].value = "";
						}
					} else if (prControl == "TEXTBOX" || prControl == "TEXTAREA") {
						// don't erase user or default text in textbox controls
						//itemsForm.elements["property"+idx+"_" + prID].value = "";
					} else if (prControl == "WIDTH_HEIGHT") {
					}
				}
			}
		} while (startSelectedNumber != selectedOptions.length);
	}

	returnValues[0] = selectedOptions;
	returnValues[1] = activeOptions;

	return returnValues;
}

function calculateOptionsPrice(itemsForm, idx, selectedOptions)
{
	var productData = ""; // 
	// product data parameter available only on listing and details blocks
	if (itemsForm.elements["product_data"+idx]) {
		productData = itemsForm.elements["product_data"+idx].value;
		productData = JSON.parse(productData);
	}

	var params = getProductParams(itemsForm, idx);
	var propertiesPrice = 0;
	var prPrice = 0;
	for (var prID in selectedOptions) {
		if (itemsForm.elements["property_control"+idx+"_" + prID]) { // check if it is property control
			prPrice = calculateOptionPrice(itemsForm, idx, selectedOptions, prID, false);
			productData["options"][prID]["price"] = prPrice;
			propertiesPrice += prPrice;
		}
	}   

	// check if we need to update some option prices
	var optionsData = productData["options"];
	var nameDelimiter = productData["name_delimiter"];
	var positivePriceRight = productData["positive_price_right"];
	var positivePriceLeft = productData["positive_price_left"];
	var negativePriceRight = productData["negative_price_right"];
	var negativePriceLeft = productData["negative_price_left"];
	for (var prID in optionsData) {
		var optionData = optionsData[prID];
		var controlType = optionData["control_type"];
		var percentagePriceType = optionData["percentage_price_type"];
		var percentagePropertyId = optionData["percentage_property_id"];
		var parentPrice = 0;
		if (productData["options"][percentagePropertyId] && productData["options"][percentagePropertyId]["price"]) {
			parentPrice = productData["options"][percentagePropertyId]["price"];
		}
		if (percentagePriceType == 2 && percentagePropertyId != "") {
			var optionValues = optionData["values"];
			if (controlType == "LISTBOX") {
				var listObj = itemsForm.elements["property"+idx+"_" + prID];
				for (var l = 0; l < listObj.options.length; l++) {	
					var listVal = listObj.options[l].value;
					if (listVal && optionValues[listVal]) {
						var valueDesc = optionValues[listVal]["desc"];
						var valuePercentage = parseFloat(optionValues[listVal]["percentage_price"]);			
						if (isNaN(valuePercentage)) { valuePercentage = 0; }
						if (valuePercentage != 0) {
							var valuePrice = Math.round(parentPrice * valuePercentage) / 100;
							if (valuePrice > 0) {
								listObj.options[l].text = valueDesc+" "+positivePriceRight+currencyFormat(valuePrice, productData["currency"])+positivePriceLeft;
							} else if (valuePrice < 0) {
								listObj.options[l].text = valueDesc+" "+negativePriceRight+currencyFormat(valuePrice, productData["currency"])+negativePriceLeft;
							} else {
								listObj.options[l].text = valueDesc;
							}
						}
					}
				}
			}
		}
	}
	return propertiesPrice;
}


function calculateOptionPrice(itemsForm, idx, selectedOptions, prID, subPrice)
{
	var params = getProductParams(itemsForm, idx);
	var productData = itemsForm.elements["product_data"+idx].value;
	productData = JSON.parse(productData);
	var optionData = productData["options"][prID];
	var optionValues = optionData["values"];

	// check if we need to get parent option price
	var parentPrice = 0;
	var percentagePriceType = optionData["percentage_price_type"];
	var percentagePropertyId = optionData["percentage_property_id"];
	if (percentagePriceType == 2 && percentagePropertyId != "" && !subPrice) {
		parentPrice = calculateOptionPrice(itemsForm, idx, selectedOptions, percentagePropertyId, true);
	}

	// start calculation
	var totalPrice = 0; var valueId; var valuePrice;
	var usedControls = 0; var controlText = ""; var freeLetters = 0;
	var priceType = parseInt(optionData["property_price_type"]);
	var priceAmount = parseFloat(optionData["property_price"]);
	if (isNaN(priceAmount)) { priceAmount = 0; }
	var freePriceType = parseInt(optionData["free_price_type"]);
	var freePriceAmount = optionData["free_price_amount"];
	var freeControls = 0;
	if (freePriceType == 1) {
		freePriceAmount = parseFloat(freePriceAmount);
	} else {
		freePriceAmount = parseInt(freePriceAmount);
	}
	if (isNaN(freePriceAmount)) { freePriceAmount = 0; }
	if (freePriceType == 2) {
		freeControls = freePriceAmount;
	} else if (freePriceType == 3 || freePriceType == 4) {
		freeLetters = freePriceAmount;
	}
	
	var prControl = optionData["control_type"];
	if (prControl == "LISTBOX" || prControl == "RADIOBUTTON") {
		valueId = selectedOptions[prID];
		usedControls++;
		//prPrice = getOptionPrice(itemsForm, valueId);
		prPrice = getOptionPrice(productData, prID, valueId); 

		if (percentagePriceType == 2 && parentPrice != 0 && optionValues[valueId]) {
			var valuePercentage = parseFloat(optionValues[valueId]["percentage_price"]);			
			if (isNaN(valuePercentage)) { valuePercentage = 0; }
			totalPrice += Math.round(parentPrice * valuePercentage) / 100;
		}
		totalPrice += prPrice;
	} else if (prControl == "CHECKBOXLIST" || prControl == "TEXTBOXLIST") {
		var values = selectedOptions[prID];
		for (valueId in values) {
			usedControls++;
			//prPrice = getOptionPrice(itemsForm, valueId);
			prPrice = getOptionPrice(productData, prID, valueId); 
			totalPrice += prPrice;
			if (prControl == "TEXTBOXLIST") {
				controlText += selectedOptions[prID][valueId];
				if (freeControls >= usedControls) {
					if (priceType == 3) {
						freeLetters = controlText.length;
					} else if (priceType == 4) {
						freeLetters = controlText.replace(/[\n\r\t\s]/g, "").length;
					}
				}
			}
		}   
	} else if (prControl == "WIDTH_HEIGHT") {
		usedControls++;
		var prices = optionData["prices"]; 
		var selectedWidth = parseFloat(selectedOptions[prID]["width"]);
		var selectedHeight = parseFloat(selectedOptions[prID]["height"]);
		var minWidth; var maxWidth; var minHeight; var maxHeight;
		for (var curWidth in prices) {
			if (curWidth > selectedWidth || curWidth == selectedWidth) {
				for (var curHeight in prices[curWidth]) {
					if (curHeight > selectedHeight || curHeight == selectedHeight) {
						totalPrice += parseFloat(prices[curWidth][curHeight]);
						break;
					}
				}
				break;
			}
		}
	} else {
		usedControls++;
		if (prControl == "TEXTAREA" || prControl == "TEXTBOX") {
			controlText = selectedOptions[prID];
			if (freeControls >= usedControls) {
				if (priceType == 3) {
					freeLetters = controlText.length;
				} else if (priceType == 4) {
					freeLetters = controlText.replace(/[\n\r\t\s]/g, "").length;
				}
			}
		}
	}
	if (priceType == 1) {
		totalPrice += priceAmount;
	} else if (priceType == 2) {
		if (usedControls > freeControls) {
			totalPrice += (priceAmount * (usedControls - freeControls));
		}
	} else if (priceType == 3) {
		var textLength = controlText.length;
		if (textLength > freeLetters) {
			totalPrice += (priceAmount * (textLength - freeLetters));
		}
	} else if (priceType == 4) {
		var textLength = controlText.replace(/[\n\r\t\s]/g, "").length;
		if (textLength > freeLetters) {
			totalPrice += (priceAmount * (textLength - freeLetters));
		}
	}
	if (freePriceType == 1) {
		totalPrice -= freePriceAmount;
	}
	// end of calculation

	return totalPrice;
}
function changeQuantity(formName, itemIndex)
{
	changeProperty(formName, itemIndex);
}

function productsWin(pagename)
{
	var productsWin = window.open (pagename, 'productsWin', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600');
	productsWin.focus();
}

function properyImageUpload(uploadUrl)
{
	var uploadWin = window.open (uploadUrl, 'uploadWin', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600');
	uploadWin.focus();
}

function openPreviewWin(previewUrl, width, height)
{
	var previewWin = window.open (previewUrl, 'previewWin', 'left=0,top=0,toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=' + width + ',height=' + height);
	previewWin.focus();
	return false;
}

function setFilePath(filepath, filetype, controlName, formName) 
{
	if(filepath != "" && controlName != "" && formName != "") {
		document.forms[formName].elements[controlName].value = filepath;
		document.forms[formName].elements[controlName].focus();
	}
}

function getOptionPrice(productData, prID, valueId)
{
	var optionPrice = 0;
	try {
		optionPrice = parseFloat(productData["options"][prID]["values"][valueId]["price"]);
	} catch (e) {}
	if (isNaN(optionPrice)) { optionPrice = 0; }
	return optionPrice;
}

function getOptionValue(itemForm, valueName)
{
	var optionPrice = 0;
	if (valueName != "") {
		if(itemForm.elements[valueName]) {
			optionPrice = parseInt(itemForm.elements[valueName].value);
			if(isNaN(optionPrice)) {
				optionPrice = 0;
			}
		}
	}
	return optionPrice;
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

function currencyFormat(numberValue, currency)
{
	var formatted = currency["left"] + formatNumber(numberValue*currency["rate"], currency["decimals"], currency["point"], currency["separator"]) + currency["right"];
	return formatted;
}

function getParamValue(params, paramName, paramType)
{
	var paramValue = "";
	if (params[paramName]) {
		paramValue = params[paramName];
	}
	if (paramType == "int") {
		paramValue = parseInt(paramValue);
		if(isNaN(paramValue)) { paramValue = 0; }
	} else if (paramType == "float") {
		paramValue = parseFloat(paramValue);
		if(isNaN(paramValue)) { paramValue = 0; }
	}
	return paramValue;
}

function getProductParams(itemsForm, idx)
{
	var params = new Array();
	var paramsList = (itemsForm.elements["product_params"+idx]) ? itemsForm.elements["product_params"+idx].value : ""; 
	var paramsPairs = paramsList.split("#");
	for (var p = 0; p < paramsPairs.length; p++) {
		var paramPair = paramsPairs[p];
		var equalPos = paramPair.indexOf("=");
		if(equalPos == -1) {
			params[paramPair] = "";
		} else {
			var paramName = paramPair.substring(0, equalPos);
			var paramValue = paramPair.substring(equalPos + 1, paramPair.length);
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
			params[paramName] = paramValue;
		}
	}
	// check params values
	var checkParams = new Array();
	checkParams["base_price"] = 0;
	checkParams["crate"] = 1;
	checkParams["pe"] = 0;
	checkParams["zero_product_action"] = 1;
	checkParams["zero_price_type"] = 0;
	checkParams["show_prices"] = 1;
	checkParams["tax_prices_type"] = 0;
	checkParams["points_rate"] = 1;
	checkParams["points_decimals"] = 0;
	checkParams["points_decimals"] = 0;
	checkParams["comp_price"] = 0;
	checkParams["comp_tax"] = 0;
	checkParams["base_points_price"] = 0;
	checkParams["base_reward_points"] = 0;
	checkParams["base_reward_credits"] = 0;
	for (paramName in checkParams) {
		if (params[paramName]) {
			params[paramName] = parseFloat(params[paramName]);
			if (isNaN(params[paramName])) { params[paramName] = checkParams[checkParams]; }
		} else {
			params[paramName] = checkParams[checkParams];
		}
	}
	return params;
}


function saveInputValue(obj)
{
	obj.setAttribute("data-value", obj.value);
}

function checkInputLength(obj, maxLength, limitType)
{
	var savePos = obj.selectionStart;
	var objText = obj.value;
	var calcText = (limitType == 3 || limitType == 4) ? objText.replace(/[\n\r\t\s]/g, "") : objText;
	if (calcText.length > 0 && calcText.length > maxLength) {
		obj.value = obj.getAttribute("data-value");
		if (obj.setSelectionRange) {
			obj.setSelectionRange(savePos-1, savePos-1);
		}
	} else {
		saveInputValue(obj);
	}
}

function checkMaxLength(e, obj, maxLength, limitType)
{
	var key;
	if (window.event) {
		key = window.event.keyCode; //IE
	} else {
		key = e.which; //Firefox
	}
	var objText = obj.value;
	var selectedText = "";
  if (obj.selectionEnd) {
	selectedText = objText.substring(obj.selectionStart, obj.selectionEnd);
  } else if (document.selection && document.selection.createRange) {
	selectedText = document.selection.createRange().text;
  } 
	if (limitType == 3 || limitType == 4) {
		selectedText = selectedText.replace(/[\n\r\t\s]/g, "");
	}
	if (selectedText.length > 0) {
		return true;
	}
	if (key == 0 || key == 8 || key == 9 || key == 16 || key == 17 || key == 35 || key == 36 || key == 37 || key == 39 || key == 46 || key == 116) {
		return true;
	}

	if (limitType == 3 || limitType == 4) {
		objText = objText.replace(/[\n\r\t\s]/g, "");
	}
  return (objText.length < maxLength);
}

function checkBoxesMaxLength(e, obj, formName, idx, prID, maxLength, limitType)
{
	var itemsForm = document.forms[formName];

	var key;
	if (window.event) {
		key = window.event.keyCode; //IE
	} else {
		key = e.which; //Firefox
	}

	var objText = obj.value;
	var selectedText = "";
	var selectedText = "";
  if (obj.selectionEnd) {
	selectedText = objText.substring(obj.selectionStart, obj.selectionEnd);
  } else if (document.selection && document.selection.createRange) {
	selectedText = document.selection.createRange().text;
  } 
	if (limitType == 3 || limitType == 4) {
		selectedText = selectedText.replace(/[\n\r\t\s]/g, "");
	}
	if (selectedText.length > 0) {
		return true;
	}

	if (key == 0 || key == 8 || key == 9 || key == 16 || key == 17 || key == 35 || key == 36 || key == 37 || key == 39 || key == 46 || key == 116) {
		return true;
	}

	var totalOptions = parseInt(itemsForm.elements["property_total"+idx+"_" + prID].value);
	var totalLength = 0;
	for ( var ci = 1; ci <= totalOptions; ci++) {
		if (itemsForm.elements["property"+idx+"_" + prID+ "_" + ci].value != "") {
			var valueText = itemsForm.elements["property"+idx+"_" + prID+ "_" + ci].value;
			if (limitType == 3 || limitType == 4) {
				valueText = valueText.replace(/[\n\r\t\s]/g, "");
			}
			totalLength += valueText.length;
		}
	}
  return (totalLength < maxLength);
}

function moveSpecialOffer(e)
{
	var mousePos = getMousePos(e);
	var pageSize = getPageSize();
	var scrollSize = getScroll();

	var popObj = document.getElementById("popupBlock");
	if (popObj) {		   
		popObj.style.display = "block";
		var blockWidth = popObj.offsetWidth;
		var blockHeight = popObj.offsetHeight;
		// get default position
		var posX = mousePos[0] + 30;
		var posY = mousePos[1] - blockHeight/2;
		// check better position 
		if (posY < scrollSize[1]) {
			posY = scrollSize[1];
		} else if (posY + blockHeight > pageSize[1] + scrollSize[1]) {
			posY -= (posY + blockHeight - pageSize[1] - scrollSize[1]);
		} 
		if (posX > pageSize[0] / 2) {
			posX = mousePos[0] - blockWidth - 30;
		}
		popObj.style.left = posX + "px";
		popObj.style.top  = posY + "px";
	}
}

function popupSpecialOffer(objName, displayValue)
{
	var scrollSize = getScroll();
	var itemObj = document.getElementById(objName);
	var popObj = document.getElementById("popupBlock");
	var soObj = document.getElementById("soPopupBox");
	if (displayValue == "block") {
		// delete popup block if it was initialized before
		var divTag = document.getElementById("popupBlock");
		if (divTag) {
			document.body.removeChild(divTag);
		}

		divTag = document.createElement("div");
		divTag.id = "popupBlock";
		divTag.className = itemObj.className;
		divTag.style.zIndex = "999";
		divTag.style.position = "absolute";
		divTag.style.left = "10px";
		divTag.style.top  = "10px";
		divTag.innerHTML = itemObj.innerHTML;
		document.body.insertBefore(divTag, document.body.firstChild);
	} else {
		var popupObj = document.getElementById("popupBlock");
		if (popupObj) {
			document.body.removeChild(popupObj);
		}
	}
}

function loadCategories(pbId, categoryId)
{
	var catObjName = "c_" + pbId + "_" + categoryId;
	var scObjName = "sc_" + pbId + "_" + categoryId;
	var scObj = document.getElementById(scObjName);
	if (scObj) {
		var largeRegExp = /large/i;
		var imgObj = document.getElementById("img_" + pbId + "_" + categoryId);

		if (scObj.style.display == "none") {
			scObj.style.display = "block";
			if (imgObj) { 
				if (imgObj.src.match(largeRegExp)) {
					imgObj.src = "images/icons/minus_large.png"; 
				} else {
					imgObj.src = "images/icons/minus.gif"; 
				}
			}
		} else {
			scObj.style.display = "none";
			if (imgObj) { 
				if (imgObj.src.match(largeRegExp)) {
					imgObj.src = "images/icons/plus_large.png"; 
				} else {
					imgObj.src = "images/icons/plus.gif"; 
				}
			}
		}
	} else {
		var url = "block.php?pb_id="+encodeURIComponent(pbId)+"&ajax=1&category_id="+encodeURIComponent(categoryId);
		var params = new Array(pbId, categoryId);
		vaSpin(catObjName); // show loading progress
		callAjax(url, categoriesLoaded, params);
	}
}

function categoriesLoaded(categoriesHTML, params)
{
	var pbId = params[0];
	var categoryId = params[1];
	var catObjName = "c_" + pbId + "_" + categoryId;
	var catObj= document.getElementById(catObjName);
	vaStopSpin(catObjName);
	catObj.innerHTML += categoriesHTML;
	var imgObj = document.getElementById("img_" + pbId + "_" + categoryId);
	if (imgObj) { 
		var regExp = /large/i;
		if (imgObj.src.match(regExp)) {
			imgObj.src = "images/icons/minus_large.png"; 
		} else {
			imgObj.src = "images/icons/minus.gif"; 
		}
	}
}

function safeJsonParse(formName, paramName)
{
	var formObj = document.forms[formName];
	var jsonText = (formObj.elements[paramName]) ? formObj.elements[paramName].value : "";
	var jsonData = new Array();
	try {
		jsonData = JSON.parse(jsonText);
	} catch (e) {}
	return jsonData;
}
