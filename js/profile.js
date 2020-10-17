function openWindow(pagename, filetype)
{
	var uploadWin = window.open (pagename + '?filetype=' + filetype, 'uploadWin', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=300');
	uploadWin.focus();
}

function setFilePath(filepath, filetype)
{

	if(filepath != "" && filetype == "personal_image")
	{
		document.user_profile.personal_image.value = filepath;
		document.user_profile.personal_image.focus();
	}
}

function setFileName(filename, filetype)
{
	if(filename != "" && filetype == "personal")
	{
		document.user_profile.personal_image.value = "images/users/" + filename;
		document.user_profile.personal_image.focus();
	}
}

function checkSame()
{
	var sameChecked = document.user_profile.same_as_personal.checked;
	if(sameChecked) {
		var changeEvent;
		if (typeof(Event) === 'function') {
			changeEvent = new Event("change");
		} else {
			changeEvent = document.createEvent('HTMLEvents');
			changeEvent.initEvent("change", true, true);
		}
		var pbId = (document.user_profile.pb_id) ? document.user_profile.pb_id.value : "";
		var fieldName = "";
		var fields = new Array("name", "first_name", "last_name", "company_id", "company_name", "email", 
			"address1", "address2", "city", "province", "address1", "state_code", "state_id", "zip", "country_code",
			"phone", "daytime_phone", "evening_phone", "cell_phone", "fax",
			"phone_code", "daytime_phone_code", "evening_phone_code", "cell_phone_code", "fax_code");
		var orderForm = document.user_profile;
		for (var i = 0; i < fields.length; i++) {
			fieldName = fields[i];
			if (orderForm.elements[fieldName] && orderForm.elements["delivery_" + fieldName]) {
				orderForm.elements["delivery_" + fieldName].value = orderForm.elements[fieldName].value;
			}
		}
		if (pbId && orderForm.country_id && orderForm.delivery_country_id) {
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
	}
}

function uncheckSame()
{
	if (document.user_profile.same_as_personal) {
		document.user_profile.same_as_personal.checked = false;
	}
}

function changeAffiliateCode()
{
	var siteURL = document.user_profile.site_url.value;
	var affiliateHelp = document.user_profile.affiliate_help.value;
	var affiliateCode = document.user_profile.affiliate_code.value;
	if (affiliateCode == "") {
		affiliateCode = "type_your_code_here";
	}
	var affiliateURL = siteURL + "?af=" + affiliateCode;
	affiliateHelp = affiliateHelp.replace("\{affiliate_url\}", affiliateURL);

	var affiliateHelpConrol = document.getElementById("affiliate_help_info");
	if (affiliateHelpConrol) {
		affiliateHelpConrol.innerHTML = affiliateHelp;
	}
}


function updateStates(pbId, controlType)
{
	var blockObj = document.getElementById("pb_" + pbId);
	var peronsalCountryObj = document.getElementById("country_id_" + pbId);
	var deliveryCountryObj = document.getElementById("delivery_country_id_" + pbId);
	var fcCountryObj = document.getElementById("fast_checkout_country_id_" + pbId);
	var personalStateObj = document.getElementById("state_id_" + pbId);
	var deliveryStateObj = document.getElementById("delivery_state_id_" + pbId);
	var fcStateObj = document.getElementById("fast_checkout_state_id_" + pbId);
	var personalStateComObj = document.getElementById("state_id_comments_" + pbId);
	var deliveryStateComObj = document.getElementById("delivery_state_id_comments_" + pbId);
	var fcStateComObj = document.getElementById("fast_checkout_state_id_comments_" + pbId);
	var personalStateReqObj = document.getElementById("state_id_required_" + pbId);
	var deliveryStateReqObj = document.getElementById("delivery_state_id_required_" + pbId);
	var fcStateReqObj = document.getElementById("fast_checkout_state_id_required_" + pbId);

	var countryId = ""; var stateObj = ""; var stateCommentsObj = ""; var stateRequiredObj = ""; var controlPrefix = "";
	if (controlType == "personal") {
		if (peronsalCountryObj) {
			countryId = peronsalCountryObj.options[peronsalCountryObj.selectedIndex].value;
		}
		if (personalStateObj) { stateObj = personalStateObj; }
		if (personalStateComObj) { stateCommentsObj = personalStateComObj; }
		if (personalStateReqObj) { stateRequiredObj = personalStateReqObj; }
	} else if (controlType == "fast_checkout") {
		if (fcCountryObj) {
			countryId = fcCountryObj.options[fcCountryObj.selectedIndex].value;
		}
		if (fcStateObj) { stateObj = fcStateObj; }
		if (fcStateComObj) { stateCommentsObj = fcStateComObj; }
		if (fcStateReqObj) { stateRequiredObj = fcStateReqObj; }
	} else {
		controlPrefix = "delivery_";
		if (deliveryCountryObj) {
			countryId = deliveryCountryObj.options[deliveryCountryObj.selectedIndex].value;
		}
		if (deliveryStateObj) { stateObj = deliveryStateObj; }
		if (deliveryStateComObj) { stateCommentsObj = deliveryStateComObj; }
		if (deliveryStateReqObj) { stateRequiredObj = deliveryStateReqObj; }
	}
	if (stateObj) {
		// remove old list
		var totalOptions = stateObj.options.length;
		for (var i = totalOptions - 1; i >= 1; i--) {
			stateObj.options[i] = null;
		}
		// check and add new states list
		if (countryId == "") {
			stateObj.style.display = "none";
			if (stateRequiredObj) { stateRequiredObj.style.display = "none"; }
			if (stateCommentsObj) {
				stateCommentsObj.innerHTML = selectCountryFirst;
				stateCommentsObj.style.display = "inline";
			}
		} else if (states[countryId]) {
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
				stateObj.options[stateObj.length] = new Option(countryStates[s].name, countryStates[s].id);
			}
			stateObj.style.display = "inline";
			if (stateRequiredObj) { stateRequiredObj.style.display = "inline"; }
			if (stateCommentsObj) {
				stateCommentsObj.innerHTML = "";
				stateCommentsObj.style.display = "none";
			}
		} else {
			stateObj.style.display = "none";
			if (stateRequiredObj) { stateRequiredObj.style.display = "none"; }
			if (stateCommentsObj) {
				stateCommentsObj.innerHTML = noStatesForCountry;
				stateCommentsObj.style.display = "inline";
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
}