var userAgent = navigator.userAgent.toLowerCase();
var vaNavActive = [];
var defaultDelay = 5000; // number in milliseconds 5000 - 5 seconds to show slide
var defaultDuration = 1000; // number in milliseconds 1000 - 1 second to change slides
var vaImages = new Array();

function findPosX(obj, addWidth)
{
	var curleft = 0;
	if (addWidth) { curleft += obj.offsetWidth; }
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft+=obj.offsetLeft
			obj=obj.offsetParent;
			var position = window.getComputedStyle(obj,null).getPropertyValue("position");
		}
	}
	return curleft;
}

function findPosY(obj, addHeight)
{
	var curtop = 0;
	if (addHeight) {
		curtop += obj.offsetHeight;
	}
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	return curtop;
}

function getMousePos(e) 
{
	var posX = 0;
	var posY = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) {
		posX = e.pageX;
		posY = e.pageY;
	}	else if (e.clientX || e.clientY) 	{
		posX = e.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posY = e.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}
	var mousePos = new Array(posX, posY);
	return mousePos;
}

function getPageSize(){
  var w = 0, h = 0;
  if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		// get page size without scroller
    w = document.documentElement.clientWidth; 
    h = document.documentElement.clientHeight;
  } else if ( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    w = document.body.clientWidth;
    h = document.body.clientHeight;
  } else if (window.innerWidth) { 
    w = window.innerWidth;
    h = window.innerHeight;
	}
	var pageSize= new Array(w, h);    
	return pageSize;
}

function getPageSizeWithScroll()
{
	var xWithScroll = 0; var yWithScroll = 0; 
	if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac         
		yWithScroll = document.body.scrollHeight;         
		xWithScroll = document.body.scrollWidth;     
	} else { // works in Explorer 6 Strict, Mozilla (not FF) and Safari         
		yWithScroll = document.body.offsetHeight;         
		xWithScroll = document.body.offsetWidth;       
	}     

	var arrayPageSizeWithScroll = new Array(xWithScroll,yWithScroll);    
	return arrayPageSizeWithScroll; 
} 

function getScroll()
{
	var w = window.pageXOffset ||
		document.body.scrollLeft ||
		document.documentElement.scrollLeft;
	var h = window.pageYOffset ||
		document.body.scrollTop ||
		document.documentElement.scrollTop;
	var arrayScroll = new Array(w, h);    
	return arrayScroll;
}

// function to show more filter options
function popupBlock(linkName, blockName, imageName)
{                              	
	var linkObj = document.getElementById(linkName);
	var blockObj = document.getElementById(blockName);
	var imageObj = document.getElementById(imageName);

	if (blockObj.style.display == "none" || blockObj.style.display == "") {
		//blockObj.style.left = findPosX(linkObj, 0) + "px";
		//blockObj.style.top = findPosY(linkObj, 1) + "px";
		blockObj.style.display = "block";
		if (imageObj) {
			imageObj.src = "images/icons/minus_small.gif";
		}
	} else {
		blockObj.style.display = "none";
		if (imageObj) {
			imageObj.src = "images/icons/plus_small.gif";
		}
	}
}

function openPopup(pageUrl, width, height){
	var scrollbars="yes";
	var popupWin = window.open(pageUrl,'popupWin','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=' + scrollbars + ',resizable=yes,width=' + width + ',height=' + height);
	popupWin.focus();
	return false;
}

function toggleMenu(){
	var headerBlock=document.getElementsByClassName("bk-header");
	var menuObj=headerBlock[0].getElementsByClassName("menu");
	var icoObj=headerBlock[0].getElementsByClassName("ico-menu");
	if (menuObj) {
		menuObj=menuObj[0];	
		icoObj=icoObj[0];	
		if (menuObj.style.display==""||menuObj.style.display=="none") {
			menuObj.style.display="block";
			icoObj.className="ico-menu ico-close";
		} else {
			menuObj.style.display="none";
			icoObj.className="ico-menu";
		}
	}
}

function expandBody(expandObj){
	var headObj = expandObj.parentNode;
	var blockObj = headObj.parentNode;
	var bodyObj = blockObj.getElementsByClassName("body")[0];
	if (expandObj.className == "expand") {
		headObj.className = "head head-open";
		expandObj.className = "expand expand-open";
		bodyObj.className = "body body-open";
	} else {
		headObj.className = "head";
		expandObj.className = "expand";
		bodyObj.className = "body";
	}

}

function vaLoadCSS()
{
	var l=document.createElement('link');l.rel='stylesheet';l.href='styles/silver.css';document.querySelector('head').appendChild(l);
}

function vaInit()
{
	var jsObjs = document.querySelectorAll("[data-js]");
	for (var j = 0; j < jsObjs.length; j++) {
		var jsObj = jsObjs[j];
		var jsValue = jsObj.getAttribute("data-js").toLowerCase();
		var jsType = jsObj.hasAttribute("data-js-type") ? jsObj.getAttribute("data-js-type").toLowerCase() : "";
		if (jsValue == "nav" || jsValue == "navigation") {
			// add javascript to li > a elements
			vaNavParse(jsObj, jsType, 0);
		} else if (jsValue == "subscribe") {
			vaSubscribeParse(jsObj);
		} else if (jsValue == "expand") {
			vaExpandParse(jsObj, jsType);
		} else if (jsValue == "select") {
			vaSelectParse(jsObj);
		} else if (jsValue == "tree") {
			vaTreeParse(jsObj, jsType, 0);
		} else if (jsValue == "tabs") {
			vaTabsParse(jsObj);
		} else if (jsValue == "images") {
			vaImagesParse(jsObj);
		} else if (jsValue == "expand-fields") {
			vaExpandFieldsParse(jsObj);
		} else if (jsValue == "phone") {
			vaPhoneParse(jsObj);
		} else if (jsValue == "rating") {
			vaRatingParse(jsObj);
		} else if (jsValue == "link") {
			vaLinkParse(jsObj);
		} else if (jsValue == "multicheck") {
			//vaMultiCheckParse(jsObj);
		}
	}
	window.addEventListener("load", vaPostInit, false);
}

function vaPostInit()
{
	var jsObjs = document.querySelectorAll("[data-js]");
	for (var j = 0; j < jsObjs.length; j++) {
		var jsObj = jsObjs[j];
		var jsValue = jsObj.getAttribute("data-js").toLowerCase();
		var jsType = jsObj.hasAttribute("data-js-type") ? jsObj.getAttribute("data-js-type").toLowerCase() : "";
		if (jsValue == "slideshow") {
			vaSlideShowParse(jsObj);
		} else if (jsValue == "slider") {
			vaSliderParse(jsObj, 1);
		}
	}
}

function vaResize()
{
	var jsObjs = document.querySelectorAll("[data-js]");
	for (var j = 0; j < jsObjs.length; j++) {
		var jsObj = jsObjs[j];
		var jsValue = jsObj.getAttribute("data-js").toLowerCase();
		var jsType = jsObj.hasAttribute("data-js-type") ? jsObj.getAttribute("data-js-type").toLowerCase() : "";
		if (jsValue == "slideshow") {
			vaSlideShowCalc(jsObj);
		}
	}
}

function vaParent(childElement, checkName, checkValue)
{
	var parentObj = childElement; 
	if (checkName.match(/^data\-/g)) {	
		// check data attribute
		var matchedElement = parentObj.hasAttribute(checkName);
		if (matchedElement && checkValue && parentObj.getAttribute(checkName) != checkValue) { matchedElement = false; }
		while (!matchedElement && parentObj) {
			parentObj = parentObj.parentNode;
			if (parentObj && parentObj.hasAttribute) { 
				matchedElement = parentObj.hasAttribute(checkName); 
				if (matchedElement && checkValue && parentObj.getAttribute(checkName) != checkValue) { matchedElement = false; }
			}
		}
	} else if (checkName.match(/^\.[a-z]/g)) {	
		// check class name
		var checkName = checkName.substring(1);
		var className = (parentObj.className) ? parentObj.className : "";
		while (className.indexOf(checkName) == -1 && parentObj) {
			parentObj = parentObj.parentNode;
			className = (parentObj.className) ? parentObj.className : "";
		}
	} else {
		checkName = checkName.toUpperCase();
		var tagName = parentObj.tagName.toUpperCase();
		while (tagName != checkName && parentObj) {
			parentObj = parentObj.parentNode;
			if (parentObj && parentObj.tagName) { tagName = parentObj.tagName.toUpperCase(); }
		}
	}
	return parentObj;
}

function vaParentJS(childElement)
{
	return vaParent(childElement, "data-js");
}

function vaParentLI(liObj)
{
	return vaParent(liObj, "LI");
}

function vaNavParse(parentUl, jsType, level)
{
	//var liObjs = ul.querySelectorAll(":scope > li"); // doesn't work in old browsers
	var liObjs = parentUl.childNodes;
	for (var l = 0; l < liObjs.length; l++) {	
 		var liObj = liObjs[l];
		if (liObj.nodeType == 1 && liObj.tagName == "LI") {
			// add event to li tag if it has ul child element
			var childUl = liObj.querySelector("ul");
			var liJsType = liObj.hasAttribute("data-js-type") ? liObj.getAttribute("data-js-type") : jsType;
			var childA = liObj.querySelector("a");
			if (childUl) {
				var className = liObj.className;
				var regStopJs = /js-stop|stop-js/g;
				var regNoJs = /js-no|no-js/g;
				liObj.setAttribute("data-level", level);
				if (!regNoJs.test(className) && !liObj.jsAdded) {
					if (liJsType == "click") {
						liObj.addEventListener("click", function (event) { vaNavClick(this, event); }, false);
						// disable A tag click
						if (childA) { childA.onclick = function() { return false; }; }
					} else {
						liObj.setAttribute("data-js-type", "hover");

						liObj.addEventListener("mouseover", function () { vaNavOver(this); }, false);
						liObj.addEventListener("mouseout", function () { vaNavOut(this); }, false);

						liObj.addEventListener("click", function (event) { vaNavClick(this, event); }, false);
						if (childA) {
							if (childA.href == "" || childA.href == "#") { 
								childA.onclick = function() { return false; }; 
							} else {
								childA.onclick = function() { return vaCheckClick(this, event); }; 
							}
						}

						//var expandObj = liObj.querySelector(".expand");
						//if (expandObj) { expandObj.addEventListener("click", function (event) { vaNavClick(this, event); }, false); }
					}
					liObj.jsAdded = true; // exclude double adding events
				}
				if (!regStopJs.test(className)) {
					vaNavParse(childUl, liJsType, (level + 1));
				}
			} else if (liJsType == "click") {
				// for last list element call onclick event to call stopPropagation
				//liObj.addEventListener("click", function (event) { vaNavClick(this, event); }, false);
				liObj.addEventListener("click", function (event) { event.stopPropagation(); }, false);
			} else {
				if (childA) { childA.onclick = function(event) { event.stopPropagation(); };  }
			}
		}
	}
}
function vaNavLi(liObj)
{
	// calculate level and check js type
	var dataLevel = 0; var jsType = "";
	var parentObj = liObj; 
	var hasDataJS = parentObj.hasAttribute("data-js");
	if (liObj.hasAttribute("data-js-type")) { jsType = liObj.getAttribute("data-js-type"); }
	while (!hasDataJS && parentObj) {
		parentObj = parentObj.parentNode;
		if (parentObj && parentObj.hasAttribute) { hasDataJS = parentObj.hasAttribute("data-js"); }
		if (parentObj.tagName == "LI") { dataLevel++; }
		if (jsType == "" && parentObj.hasAttribute("data-js-type")) { jsType = parentObj.getAttribute("data-js-type"); }
	}

	liObj.setAttribute("data-level", dataLevel);
	if (jsType == "click") {
		liObj.addEventListener("click", function (event) { vaNavClick(this, event); }, false);
		// disable A tag click
		var childA = liObj.querySelector("a");
		if (childA) { childA.onclick = function() { return false; }; }
	} else if (jsType == "hover") {
		liObj.addEventListener("mouseover", function () { vaNavOver(this); }, false);
		liObj.addEventListener("mouseout", function () { vaNavOut(this); }, false);
	}
}

function vaNavOver(liObj, navShowClass)
{
	if (!navShowClass) { navShowClass = "nav-open"; }
	// check for expand mode
	var parentUL = vaParentJS(liObj);
	var regExp = /expand-open/g;
	if (isMobileTablet() || (parentUL && parentUL.className && regExp.test(parentUL.className))) {
		// for mobile devices and expand mode don't use over effect
		return;
	}
	// check if we need stop over effect for certain width
	if (parentUL.hasAttribute("data-over-stop")) {
		var overStop = parseInt(parentUL.getAttribute("data-over-stop"));
		var winWidth = parseInt(window.innerWidth);
		if (winWidth > 0 && winWidth < overStop) {
			return;
		}
	}
	// check if there active expand object to ignore over effect
	var expandObj = liObj.querySelector(".expand");
	if (expandObj && expandObj.offsetWidth > 0 && expandObj.offsetWidth > 0) {
		return; 
	}

	var level = liObj.getAttribute("data-level");
	if (vaNavActive[level] && vaNavActive[level].obj != liObj) {
		vaNavHide(level);
	} 
	var childUl = liObj.querySelector("ul");
	var className = liObj.className;
	//regExp = /nav-open/g;
	regExp = new RegExp(navShowClass, "g");
	if (!regExp.test(className)) {
		liObj.className = (className+" "+navShowClass).trim();
		// add nav-open or node-show to parent ul element
		var parentUl = liObj.parentNode;
		className = parentUl.className;
		parentUl.className = (className+" "+navShowClass).trim();
	}
	if (vaNavActive[level]) {
		clearTimeout(vaNavActive[level].tid);
	}
	vaNavActive[level] = {"obj": liObj, "tid": null};
}

function vaNavOut(liObj)
{
	// check for expand mode
	var parentUL = vaParentJS(liObj);
	var regExp = /expand-open/g;
	if (isMobileTablet() || (parentUL && parentUL.className && regExp.test(parentUL.className))) {
		// for mobile devices and expand mode don't use over effect
		return;
	}
	if (parentUL.hasAttribute("data-over-stop")) {
		var overStop = parseInt(parentUL.getAttribute("data-over-stop"));
		var winWidth = parseInt(window.innerWidth);
		if (winWidth > 0 && winWidth < overStop) {
			return;
		}
	}
	// check if there active expand object to ignore over effect
	var expandObj = liObj.querySelector(".expand");
	if (expandObj && expandObj.offsetWidth > 0 && expandObj.offsetWidth > 0) {
		return; 
	}
	var level = liObj.getAttribute("data-level");
	var tid = setTimeout(function() { vaNavHide(level); }, 1000);
	if (vaNavActive[level]) {
		// if event was run save tid for it
		vaNavActive[level].tid = tid;
	}
}

function vaNavClick(liObj, event)
{
	liObj = vaParentLI(liObj); 
	var parentUL = vaParentJS(liObj);
	var className = liObj.className;
	var level = liObj.getAttribute("data-level");
	var jsType = liObj.getAttribute("data-js-type");
	var overStop = parentUL.hasAttribute("data-over-stop") ? parseInt(parentUL.getAttribute("data-over-stop")) : 0;
	if (isNaN(overStop)) { overStop = 0; }
	var winWidth = parseInt(window.innerWidth);
	// check for nav-expand element above
	var regExp = /expand-open/g;
	var expandMode = (parentUL && parentUL.className && regExp.test(parentUL.className));

	// check different criterions when we switch to click mode
	if (isMobileTablet() || (winWidth > 0 && winWidth < overStop) || expandMode) {
		// switch to click mode for mobile and tablet devices and also when we switch to small expand mode
		jsType = "click";
	}
	var childUl = liObj.querySelector("ul");
	if (!childUl || jsType == "hover") {
		// it the last element clicked so just ignore this click and call stopPropagation() so parent element doesn't close menu
		event.stopPropagation();
		return;
	}
	var regExp = /nav-open/g;
	if (liObj.className && liObj.className.match(regExp)) {
		vaNavHide(level);
		event.stopPropagation(); // close only child submenu
	} else {
		if (vaNavActive[level] && vaNavActive[level].obj != liObj) {
			vaNavHide(level);
		} 
		var regExp = /nav-open/g;
		if (!regExp.test(className)) {
			liObj.className = (className + " nav-open").trim();
			// add nav-open to parent ul element
			var parentUl = liObj.parentNode;
			className = parentUl.className;
			parentUl.className = (className + " nav-open").trim();
		}
		vaNavActive[level] = {"obj": liObj, "tid": null};
		event.stopPropagation();
	}
}

function vaCheckClick(aObj)
{
	var liObj = vaParentLI(aObj); 
	var parentUL = vaParentJS(liObj);
	var jsType = liObj.getAttribute("data-js-type");
	var overStop = parentUL.hasAttribute("data-over-stop") ? parseInt(parentUL.getAttribute("data-over-stop")) : 0;
	if (isNaN(overStop)) { overStop = 0; }
	var winWidth = parseInt(window.innerWidth);
	// check for nav-expand element above
	var regExp = /expand-open/g;
	var expandMode = (parentUL && parentUL.className && regExp.test(parentUL.className));
	if (isMobileTablet() || (winWidth > 0 && winWidth < overStop) || expandMode) {
		return false;
	} else {
		return true;
	}
}

function vaNavHide(level)
{
	for (var m = (vaNavActive.length - 1); m >= level; m--) {
		if (vaNavActive[m].tid) { clearTimeout(vaNavActive[m].tid); }
		var liObj = vaNavActive[m].obj;
		vaNavActive.pop();
		var childUl = liObj.querySelector("ul");
		var className = liObj.className.replace(/nav-open|node-show/gi, "").trim();
		liObj.className = className;
		// remove nav-open or node-show from parent ul element
		var parentUl = liObj.parentNode;
		className = parentUl.className.replace(/nav-open|node-show/gi, "").trim();
		parentUl.className = className;
	}
}



function vaSubscribeParse(block)
{
	var field = block.querySelector("input[type=text]");
	field.addEventListener("keyup", function (e) { vaSubscribeField(e); }, false);
	var buttons = block.querySelectorAll("input[type=button]");
	for (var b = 0; b < buttons.length; b++) {
		buttons[b].addEventListener("click", function () { vaSubscribeButton(this); }, false);
	}
}

function vaSubscribeField(e)
{
	if (e.keyCode == 13) {
		var field = e.target;
		vaSubscribeEmail(field);
	}
}

function vaSubscribeButton(buttonObj)
{
	var parentObj = vaParentJS(buttonObj);
	var field = parentObj.querySelector("input[type=text]");
	vaSubscribeEmail(field);
}

function vaSubscribeEmail(field)
{
	var parentObj = vaParentJS(field);
	var errorBlock = parentObj.querySelector(".error-block");
	if (errorBlock) { errorBlock.style.display = "none"; }
	var successBlock = parentObj.querySelector(".success-block");
	if (successBlock) { successBlock.style.display = "none"; }
	var url = "ajax_subscribe.php?ajax=1&operation=subscribe&email=" + encodeURIComponent(field.value);
	callAjax(url, vaSubscribeResult, field);
}

function vaSubscribeResult(response, field)
{
	var data = JSON.parse(response);
	var parentObj = vaParentJS(field);
	var msgBlock = null;
	if (data.result == "error") {
		msgBlock = parentObj.querySelector(".error-block");
	} else {
		msgBlock = parentObj.querySelector(".success-block");
	}
	if (msgBlock) {
		msgBlock.style.display = "block";
		msgBlock.onclick = function() { this.style.display = "none" ;};
		var msgObj = msgBlock.querySelector("span");
		if (!msgObj) { msgObj = msgBlock.querySelector(".message"); }
		if (msgObj) { msgObj.innerHTML = data.message; }
	}
}

function vaPhoneParse(parentObj)
{
	var inputObj;
  if (parentObj.tagName == "INPUT") {
		inputObj = parentObj;
		var divObj = document.createElement("div");
		divObj.style.display = "inline-block";
		var inputParent = inputObj.parentNode;
		divObj = inputParent.insertBefore(divObj, inputObj);
		inputObj = divObj.appendChild(inputObj);
		parentObj = divObj;
		parentObj.setAttribute("data-js", "phone");
		parentObj.setAttribute("data-format", inputObj.getAttribute("data-format"));
		inputObj.removeAttribute("data-js");
		inputObj.removeAttribute("data-format");
	} else {
		inputObj = parentObj.querySelector("input");
	}
	inputObj.setAttribute("data-box-shadow", window.getComputedStyle(inputObj,null).getPropertyValue("box-shadow"));
	// add necessary styles to parent object and input objecs
	parentObj.style.position = "relative";
	inputObj.style.backgroundColor = "transparent";
	inputObj.style.position = "relative";
	inputObj.style.zIndex = "2";

	// check for hint object and add it if not exists
	var hintObj = parentObj.querySelector("[data-hint]");
	if (!hintObj) {
		divObj = document.createElement("div");
		divObj.style.position = "absolute";
		divObj.style.top = "0px";
		divObj.style.left = "0px";
		divObj.style.border = window.getComputedStyle(inputObj,null).getPropertyValue("border");
		divObj.style.padding = window.getComputedStyle(inputObj,null).getPropertyValue("padding");
		divObj.style.margin = window.getComputedStyle(inputObj,null).getPropertyValue("margin");
		divObj.style.font = window.getComputedStyle(inputObj,null).getPropertyValue("font");
		divObj.style.color = "#000";
		divObj.style.borderColor = "transparent";
		divObj.style.opacity = "0.2";
		divObj.style.zIndex= "1";
		divObj.setAttribute("data-hint", "hint");
		divObj.innerHTML = parentObj.getAttribute("data-format");
		hintObj = parentObj.appendChild(divObj);
	}
	hintObj.setAttribute("data-color", window.getComputedStyle(hintObj,null).getPropertyValue("color"));
	hintObj.setAttribute("data-opacity", window.getComputedStyle(hintObj,null).getPropertyValue("opacity"));

	inputObj.addEventListener("input", function () { vaPhoneFormat(this, true); }, false);
	inputObj.addEventListener("blur", function () { vaPhoneCheck(this); }, false);
	inputObj.addEventListener("focus", function () { vaFieldFocus(this); }, false);

	vaPhoneFormat(inputObj, false); // format initial value
	vaPhoneCheck(inputObj); // check initial value
}

function vaPhoneFormat(inputObj, setFocus)
{
	var phoneValue = inputObj.value;
	var parentObj = vaParentJS(inputObj);

	if (phoneValue) {
		var phoneFormat = parentObj.getAttribute("data-format");
		var reFormat = phoneFormat.substring(0, phoneValue.length);

		var digitsLimit = phoneFormat.replace(/[^#]/g, "").length; 
  
		reFormat = reFormat.replace(/\(/g, "\\(");
		reFormat = reFormat.replace(/\)/g, "\\)");
		reFormat = reFormat.replace(/\-/g, "\\-");
		reFormat = reFormat.replace(/\+/g, "\\+");
		reFormat = reFormat.replace(/\#/g, "\\d");
		reFormat = reFormat.replace(/\./g, "\\.");

		var regExp = new RegExp("^"+reFormat+"$");
		if (phoneValue && !regExp.test(phoneValue)) {
			// check selection position before rebuild phone value
		 	var startPos = inputObj.selectionStart;
			var endPos = inputObj.selectionEnd;
			var startLength = phoneValue.length;

			var phoneDigits = phoneValue.replace(/[^\d]/g, "");
			phoneValue = ""; // re-build phone value

			for (var d = 0; d < phoneDigits.length; d++) {
				var digit = phoneDigits.substring(d, d + 1);
				do {
					var symbolMatched = false;
					var fSymbol = phoneFormat.substring(0, 1);
					phoneFormat = phoneFormat.substring(1, phoneFormat.length);
					if (fSymbol == "#" || fSymbol == digit) {
						symbolMatched = true;
						phoneValue += digit.toString();
					} else {
						phoneValue += fSymbol;
					}
				} while (!symbolMatched && phoneFormat.length > 0);
			}
			inputObj.value = phoneValue;
			if (setFocus) {
				// use setTimeout to move cursor to the end for mobile devices
				setTimeout(function() { inputObj.focus(); inputObj.setSelectionRange(inputObj.value.length, inputObj.value.length); }, 0);
			}
		}
	}
	// update hint HTML
	var valLength = phoneValue.length;
	phoneFormat = parentObj.getAttribute("data-format");
	var hintHTML = "<span style=\"opacity: 0; color: transparent;\">"+phoneFormat.substring(0, valLength)+"</span>";
	hintHTML += phoneFormat.substring(valLength, phoneFormat.length);

	var hintObj = parentObj.querySelector("[data-hint]");
	hintObj.innerHTML = hintHTML;
}

function vaPhoneCheck(inputObj)
{
	var phoneValue = inputObj.value;
	var parentObj = vaParentJS(inputObj);

	var reFormat = parentObj.getAttribute("data-format");
	reFormat = reFormat.replace(/\(/g, "\\(");
	reFormat = reFormat.replace(/\)/g, "\\)");
	reFormat = reFormat.replace(/\-/g, "\\-");
	reFormat = reFormat.replace(/\+/g, "\\+");
	reFormat = reFormat.replace(/\#/g, "\\d");
	reFormat = reFormat.replace(/\./g, "\\.");
	var regExp = new RegExp("^"+reFormat+"$");
	if (phoneValue && !regExp.test(phoneValue)) {
		var hintObj = parentObj.querySelector("[data-hint]");
		hintObj.style.color = "#f77";
		hintObj.style.opacity = "1";
		inputObj.style.boxShadow = "0 0 10px #f00";
	}
}

function vaRatingParse(parentObj)
{
	// check current rating if it was set
	var selectedRating = 0;
	var fieldName = parentObj.hasAttribute("data-field") ? parentObj.getAttribute("data-field") : "rating";
	var formObj = vaParent(parentObj, "FORM");
	if (formObj) { 
		selectedRating = parseInt(formObj.elements[fieldName].value); 
		if (isNaN(selectedRating)) { selectedRating = 0; }
	}
	// parse fields
	var stars = parentObj.querySelectorAll("[data-rating]");
	for (var s = 0; s < stars.length; s++) {
		stars[s].addEventListener("click", function (event) { vaRating(this, event); }, false);
		var rating = parseInt(stars[s].getAttribute("data-rating"));
		if (rating <= selectedRating) {
			stars[s].className = stars[s].className + " star-selected";
		}
	}
}

function vaRating(starObj, event)
{
	var parentObj = vaParentJS(starObj);
	var selectedRating = parseInt(starObj.getAttribute("data-rating"));
	var stars = parentObj.querySelectorAll("[data-rating]");
	for (var s = 0; s < stars.length; s++) {
		var rating = parseInt(stars[s].getAttribute("data-rating"));
		stars[s].className = stars[s].className.replace(/star-selected/gi, "").trim();
		if (rating <= selectedRating) {
			stars[s].className = stars[s].className + " star-selected";
		}
	}
	// update rating field
	var fieldName = parentObj.hasAttribute("data-field") ? parentObj.getAttribute("data-field") : "rating";
	var formObj = vaParent(parentObj, "FORM");
	if (formObj) { formObj.elements[fieldName].value = selectedRating; }
	event.stopPropagation()
}

function vaLinkParse(linkObj)
{
	linkObj.addEventListener("click", function(event) { vaLink(this, event); }, false);
}

function vaLink(linkObj, event)
{
	var parentObj = vaParent(linkObj, "data-pb-id");
	if (linkObj.href && parentObj) {
		var pbId = parentObj.getAttribute("data-pb-id");
		var htmlId = parentObj.getAttribute("data-html-id");
		var pbType = parentObj.getAttribute("data-pb-type");
		var pbParams = "";
		var globalParams = parentObj.getAttribute("data-pb-params");
		if (globalParams) { pbParams += "&"+globalParams; }
		var linkParams = new URL(linkObj.href).search;
		if (linkParams) { pbParams += "&"+linkParams.substring(1); }
		if (pbType) { pbParams += "&pb_type="+encodeURIComponent(pbType); }
		reloadBlock(pbId, htmlId, pbParams);
		event.preventDefault();
	}
}

function vaFieldFocus(fieldObj)
{
	if (fieldObj.hasAttribute("data-box-shadow")) {
		fieldObj.style.boxShadow = fieldObj.getAttribute("data-box-shadow");
	}
	var hintObj = fieldObj.parentNode.querySelector("[data-hint]");
	if (hintObj) {
		if (hintObj.hasAttribute("data-color")) {
			hintObj.style.color = hintObj.getAttribute("data-color");
		}
		if (hintObj.hasAttribute("data-opacity")) {
			hintObj.style.opacity = hintObj.getAttribute("data-opacity");
		}
	}
}

function vaExpandParse(block, jsType)
{
	var links = block.querySelectorAll("[data-type='expand']");
	if (links.length == 0) { links = block.querySelectorAll(".expand-link"); }
	if (links.length > 0) {
		for (var l = 0; l < links.length; l++) {
			if (jsType == "hover") {
				links[l].addEventListener("mouseover", function () { vaExpand(this, 'open'); }, false);
				links[l].addEventListener("mouseout", function () { vaExpand(this, 'hide'); }, false);
			} else {
				links[l].addEventListener("click", function () { vaExpand(this); }, false);
			}
		}
	} else {
		if (jsType == "hover") {
			block.addEventListener("mouseover", function () { vaExpand(this, 'open'); }, false);
			block.addEventListener("mouseout", function () { vaExpand(this, 'hide'); }, false);
		} else {
			block.addEventListener("click", function () { vaExpand(this); }, false);
		}
	}
}

function vaSelectParse(block)
{
	vaExpandParse(block);
	block.addEventListener("keydown", function (e) { vaSelectKeyDown(e, this); }, false);
	block.addEventListener("blur", function () { vaSelectClose(this); }, false);

	// add events to each option
	var options = block.querySelectorAll("[data-type='option']");
	if (options && options.length > 0) {
		var optionIndex = 0;
		for (var o = 0; o < options.length; o++) {
			optionIndex++;
			options[o].setAttribute("data-index", optionIndex);
			options[o].addEventListener("mouseover", function () { vaOptionOver(this); }, false);
			var descObj = options[o].querySelector("[data-type='description']");
			if (descObj) {
				descObj.addEventListener("click", function () { vaSelectOption(this); }, false);
			} else {
				options[o].addEventListener("click", function () { vaSelectOption(this); }, false);
			}
			var checkObj = options[o].querySelector("[data-type='check']");
			if (checkObj) {
				checkObj.addEventListener("click", function () { vaCheckOption(this); }, false);
			}
		}
	} 
	// add event to close control 
	var closeObj = block.querySelector("[data-type='close']");
	if (closeObj) {
		closeObj.addEventListener("click", function () { vaSelectClose(this); }, false);
	}
	// add event to clear control 
	var clearObj = block.querySelector("[data-type='clear']");
	if (clearObj) {
		clearObj.addEventListener("click", function () { vaSelectClear(this); }, false);
	}
}

function vaTreeParse(parentUl, jsType, level)
{
	//var liObjs = ul.querySelectorAll(":scope > li"); // doesn't work in old browsers
	var liObjs = parentUl.childNodes;
	for (var l = 0; l < liObjs.length; l++) {	
 		var liObj = liObjs[l];
		if (liObj.nodeType == 1 && liObj.tagName == "LI") {
			liObj.setAttribute("data-level", level);
			var childUl = liObj.querySelector("ul");
			var expandObj = liObj.querySelector(".expand");
			// add event to expand tag if it has ul child element or marked with nav-childs class
			var className = liObj.className;
			var regExp = /childs/gi;
			if ((childUl || regExp.test(liObj.className)) && expandObj) {
				expandObj.addEventListener("click", vaTreeClick, false);
			}
			if (childUl && jsType == "popup") {
				// add event to li tag if it has ul child element 
				liObj.addEventListener("mouseover", function () { vaNavOver(this,"node-show"); }, false);
				liObj.addEventListener("mouseout", function () { vaNavOut(this,"node-show"); }, false);
			}
			if (childUl) {
				vaTreeParse(childUl, jsType, (level + 1));
			}
		}
	}
}

function vaTreeClick()
{
	var liObj = vaParentLI(this); 
	var parentUL = vaParentJS(liObj);
	var id = liObj.getAttribute("data-id");
	var childUl = liObj.querySelector("ul");
	var regExp = /node-open/g;
	if (!regExp.test(liObj.className)) {
		liObj.className = (liObj.className + " node-open").trim();
		if (!childUl) {
			var url;
			if (parentUL.hasAttribute("data-script")) {
				url = parentUL.getAttribute("data-script");
			} else {
				url = "block.php";
			}
			url += "?ajax=1&id="+encodeURIComponent(id);
			if (parentUL.hasAttribute("data-pb-id")) {
				url += "&pb_id="+encodeURIComponent(parentUL.getAttribute("data-pb-id"));
			}
			if (parentUL.hasAttribute("data-type")) {
				url += "&type="+encodeURIComponent(parentUL.getAttribute("data-type"));
			}
			vaSpin(liObj.id); // show loading progress
			callAjax(url, vaTreeLoaded, liObj.id);
		}

	} else {
		liObj.className = liObj.className.replace(/node-open/gi, "").trim();
	}
}

function vaTreeLoaded(treeHTML, treeId)
{
	vaStopSpin(treeId);
	var treeObj = document.getElementById(treeId);
	// check level js type
	var level = treeObj.hasAttribute("data-level") ? parseInt(treeObj.getAttribute("data-level")) : 0;
	var parentUL = vaParentJS(treeObj);
	var jsType = parentUL.hasAttribute("data-js-type") ? parentUL.getAttribute("data-js-type").toLowerCase() : "";
	if (isNaN(level)) { level = 0; }

	treeObj.innerHTML += treeHTML;
	// add events to loaded tree elements
	var expandObj = treeObj.querySelector(".expand");
	if (expandObj) {
		expandObj.addEventListener("click", vaTreeClick, false);
	}
	var childUl = treeObj.querySelector("ul");
	if (childUl) {
		vaTreeParse(childUl, jsType, level + 1);
	}
}

function vaExpand(linkObj, expandType) 
{
	var expandObj = "";
	var expandObjs = [];
	var parentObj = vaParentJS(linkObj);
	var jsType = parentObj.getAttribute("data-js").toLowerCase();
	if (jsType == "select") {
		expandObjs.push(parentObj.querySelector("[data-type='options']"));
	} else { 
		if (parentObj.hasAttribute("data-tag")) {
			var tags = parentObj.getAttribute("data-tag").split(/[\s;,]/);
			for (var t = 0; t < tags.length; t++) {
				var tagName = tags[t];
				expandObj = parentObj.querySelector(tagName);
				if (!expandObj) {
					// try one level up
					var upParentObj = parentObj.parentNode;
					expandObj = upParentObj.querySelector(tagName);
				}
				if (expandObj) { expandObjs.push(expandObj); }
			}
		} 
		if (parentObj.hasAttribute("data-class")) {
			var classes = parentObj.getAttribute("data-class").split(/[\s;,]/);
			for (var c = 0; c < classes.length; c++) {
				var objClass = classes[c];
				expandObj = parentObj.querySelector("."+objClass);
				if (!expandObj) { // try one level up
					var upParentObj = parentObj.parentNode;
					expandObj = upParentObj.querySelector("."+objClass);
				}
				if (!expandObj) { // try top level 
					expandObj = document.querySelector("."+objClass);
				}
				if (expandObj) { expandObjs.push(expandObj); }
			}
		} 
		if (parentObj.hasAttribute("data-id")) {
			var objId = parentObj.getAttribute("data-id")
			expandObj = document.getElementById(objId);
			expandObjs.push(expandObj);
		}
	}

	// check expand type if it wasn't specified
	var regExp = /expand-open/g;
	if (!expandType) { expandType = (regExp.test(parentObj.className)) ? "hide" : "open"; }
	// update parent object class 
	parentObj.className = parentObj.className.replace(/expand-open/gi, "").trim();
	if (expandType== "open") {
		parentObj.className = (parentObj.className + " expand-open").trim();
	}
	// update related objects
	for (var e = 0; e < expandObjs.length; e++) {
		var expandObj = expandObjs[e];
		if (expandObj) {
			// expand object found could open or close it
			expandObj.className = expandObj.className.replace(/expand-open/gi, "").trim();
			if (expandType== "open") {
				expandObj.className = (expandObj.className + " expand-open").trim();
			}
		}
	}

	if (parentObj.hasAttribute("data-ajax")) {
		var ajaxCall = parentObj.getAttribute("data-ajax").toLowerCase();
		var ajaxParams = (expandType == "open") ? parentObj.getAttribute("data-ajax-open-params") : parentObj.getAttribute("data-ajax-hide-params");
		if (ajaxCall == "admin") {
			callAjax("admin_ajax.php?"+ajaxParams, function(){});
		}
	}
}

function vaSelectOption(optionObj)
{
	// check parent option object
	var optionObj = vaParent(optionObj, "data-type", "option");
	// check parent select control object
	var selectObj = vaParentJS(optionObj);
	// check select type single or multiple
	var dataSelect = selectObj.getAttribute("data-select");

	if (dataSelect != "multiple") {
		var options = selectObj.querySelectorAll("[data-type='option']");
		if (options && options.length > 0) {
			for (var o = 0; o < options.length; o++) {
				options[o].setAttribute("data-checked", "");
			}
		}
	}
	optionObj.setAttribute("data-checked", "checked");

	// when click on description close control
	vaSelectClose(selectObj);

	vaSelectChange(selectObj);
}

function vaCheckOption(optionObj)
{
	// check parent option object
	var optionObj = vaParent(optionObj, "data-type", "option");
	var optionChecked = optionObj.getAttribute("data-checked");
	// check parent control object
	var selectObj = vaParentJS(optionObj);
	// check select type single or multiple
	var dataSelect = selectObj.getAttribute("data-select");

	if (dataSelect != "multiple") {
		var options = selectObj.querySelectorAll("[data-type='option']");
		if (options && options.length > 0) {
			for (var o = 0; o < options.length; o++) {
				options[o].setAttribute("data-checked", "");
			}
		}
	}
	if (optionChecked == "checked") {
		optionObj.setAttribute("data-checked", "");
	} else {
		optionObj.setAttribute("data-checked", "checked");
	}
	vaSelectChange(selectObj);
}

function vaSelectChange(selectObj)
{
	var formName = selectObj.getAttribute("data-form");
	var inputName = selectObj.getAttribute("data-input");
	var optionValue = selectObj.getAttribute("data-value");
	var dataSeparator = selectObj.getAttribute("data-separator");
	if (!dataSeparator) { dataSeparator = "; "; }

	var selectedDesc = []; var selectedValues = [];
	var options = selectObj.querySelectorAll("[data-type='option']");
	if (options && options.length > 0) {
		for (var o = 0; o < options.length; o++) {
			var optionObj = options[o];
			if(optionObj.getAttribute("data-checked").toLowerCase() == "checked") {
				var optionDesc = "";
				if (optionObj.hasAttribute("data-desc")) {
					optionDesc = optionObj.getAttribute("data-desc")
				} else {
					var descObj = optionObj.querySelector("[data-type='description']");
					optionDesc = (descObj) ? descObj.innerHTML : optionObj.innerHTML;
				}
				selectedDesc.push(optionDesc);
				selectedValues.push(optionObj.getAttribute("data-value"));
			}
		}
	}

	document.forms[formName].elements[inputName].value = selectedValues.join(",");
	var selectedObj = selectObj.querySelector("[data-type='selected']");
	if (selectedObj) {
		selectedObj.innerHTML = selectedDesc.join(dataSeparator);
	}
	if (selectedValues.length > 0) {
		selectObj.setAttribute("data-selected", "selected");
	} else {
		selectObj.removeAttribute("data-selected");
	}

	// check and call bind events
	var changeEvent = document.createEvent('Event');
	changeEvent.initEvent("change", false, true);
	document.forms[formName].elements[inputName].dispatchEvent(changeEvent);
}

function vaSelectClear(clearObj)
{
	var selectObj = vaParentJS(clearObj);
	var options = selectObj.querySelectorAll("[data-type='option']");
	if (options && options.length > 0) {
		for (var o = 0; o < options.length; o++) {
			options[o].setAttribute("data-checked", "");
		}
	}
	vaSelectChange(selectObj);
}

function vaSelectClose(closeObj)
{
	var selectObj = vaParentJS(closeObj);
	var optionsDiv = selectObj.querySelector("[data-type='options']");
	optionsDiv.scrollTop = 0;
	var options = selectObj.querySelectorAll("[data-type='option']");
	var position = selectObj.getAttribute("data-position");
	if(position >= 1 && position <= options.length) {
		options[position-1].removeAttribute("data-active");
	}
	selectObj.removeAttribute("data-position");
	vaExpand(selectObj, "hide");
}

function vaOptionOver(optionObj)
{
	var selectObj = vaParentJS(optionObj);
	var options = selectObj.querySelectorAll("[data-type='option']");
	var position = selectObj.getAttribute("data-position");
	if(position >= 1 && position <= options.length) {
		options[position-1].removeAttribute("data-active");
	}
	var optionIndex = optionObj.getAttribute("data-index");
	optionObj.setAttribute("data-active", "active");
	selectObj.setAttribute("data-position", optionIndex);
	selectObj.focus(); // set focus so we can use keyboard on control if we lose it
}

function vaSelectKeyDown(e, selectObj)
{
	e = e || window.event;
	if (e.keyCode == '46') { // Del key
		vaSelectClear(selectObj);
		e.preventDefault();
	} else if (selectObj.className.match(/expand-open/g)) {	
		// if drop down was expanded then check more keys
		var optionsDiv = selectObj.querySelector("[data-type='options']");
		var optionsHeight = optionsDiv.offsetHeight;
		var options = selectObj.querySelectorAll("[data-type='option']");
		var position = selectObj.getAttribute("data-position");
		if (e.keyCode == '27') { // Esc key
			vaSelectClose(selectObj);
		} else if (e.keyCode == '13') { // Enter key 
			if (position) { vaSelectOption(options[position-1]); }
			e.preventDefault();
		} else if (e.keyCode == '32') { // Space key
			if (position) { vaCheckOption(options[position-1]); }
			e.preventDefault();
		} else if (e.keyCode == '38') { // Up Arrow key
			if (position > 1) {
				options[position-1].removeAttribute("data-active", "");
				position--;
				options[position-1].setAttribute("data-active", "active");
				selectObj.setAttribute("data-position", position);
				// check if we need to offset drop-down 
				var optionOffset = options[position-1].offsetTop;
				if (optionOffset < optionsDiv.scrollTop) {
					optionsDiv.scrollTop = optionOffset;
				}
			}
			e.preventDefault();
		} else if (e.keyCode == '40') { // Down Arrow key
			if (position > 0 && position < options.length) {
				options[position-1].removeAttribute("data-active", "");
			}
			if (position < options.length) {
				position++;
				options[position-1].setAttribute("data-active", "active");
				selectObj.setAttribute("data-position", position);
				// check if we need to offset drop-down 
				var optionOffset = options[position-1].offsetTop;
				var optionHeight = options[position-1].offsetHeight;
				if ((optionOffset + optionHeight - optionsHeight) > optionsDiv.scrollTop) {
					optionsDiv.scrollTop = (optionOffset + optionHeight) - optionsHeight;
				}
			}
			e.preventDefault();
		}
	} else if (e.keyCode == '13' || e.keyCode == '32' || e.keyCode == '40') { // Show drop-down if Enter, Space or Down Arrow key was pressed
		vaExpand(selectObj, "open"); 
		if (e.keyCode == '40') { // For Down Arrow move to first element
			var options = selectObj.querySelectorAll("[data-type='option']");
			if (options.length) {
				options[0].setAttribute("data-active", "active");
				selectObj.setAttribute("data-position", 1);
			}
		}
		e.preventDefault();
	}
}

function vaTabsParse(parentUL)
{
	// check active tab element
	var tabActive = ""; var formObj; var tabData;
	var parentBlock = parentUL.parentNode;
	if (parentUL.hasAttribute("data-form")) {
		var formObj = document.forms[parentUL.getAttribute("data-form")];
		if (formObj) {
			if (formObj.tab) {
				tabActive = formObj.tab.value;
			} else {
				var tabObj = document.createElement("input");
				tabObj.type = "hidden";
				tabObj.name = "tab";
				formObj.appendChild(tabObj);
			}
		} else {
			parentUL.removeAttribute("data-form");
		}
	} 
	if (!parentUL.hasAttribute("data-form")) {
		tabActive = parentUL.hasAttribute("data-tab-active") ? parentUL.getAttribute("data-tab-active") : "";
	}
	var liObjs = parentUL.childNodes;
	for (var l = 0; l < liObjs.length; l++) {	
 		var liObj = liObjs[l];
		if (liObj.nodeType == 1 && liObj.tagName == "LI") {
			var tabName = liObj.getAttribute("data-tab");
			if (tabActive == "") { tabActive = tabName; }
			liObj.addEventListener("click", function () { vaTab(this); }, false);
			var aObj = liObj.querySelector("a");
			if (aObj) { aObj.onclick = function() { return false; }; }
			// get tab data element
			tabData = document.getElementById(tabName+"_data");
			if (!tabData) { tabData = parentBlock.querySelector(".tab-"+tabName); }
			if (!tabData) { tabData = parentBlock.querySelector("."+tabName); }
			if (!tabData) { tabData = document.getElementById("data_"+tabName); } // old way name
			if (tabName == tabActive) {
				var regExp = /tab-active/g;
				if (!regExp.test(liObj.className)) {
					liObj.className = (liObj.className + " tab-active").trim();
				}
				tabData.className = tabData.className.replace(/tab-show|tab-hide/gi, "").trim();
				tabData.className = (tabData.className + " tab-show").trim();
			} else {
				liObj.className = liObj.className.replace(/tab-active/gi, "").trim();
				if (tabData) {
					tabData.className = tabData.className.replace(/tab-show|tab-hide/gi, "").trim();
					tabData.className = (tabData.className + " tab-hide").trim();
				}
			}
		}
	}
	if (formObj) {
		formObj.tab.value = tabActive;
		// call onchange event for tab element change
		var changeEvent = document.createEvent('Event');
		changeEvent.initEvent("change", false, true);
		formObj.tab.dispatchEvent(changeEvent);
	} else {
		parentUL.setAttribute("data-tab-active", tabActive);
	}
}

function vaTab(tabObj)
{
	var parentUL = vaParentJS(tabObj); 
	var parentBlock = parentUL.parentNode;
	var tabCurrent = ""; var formObj;
	if (parentUL.hasAttribute("data-form")) {
		formObj = document.forms[parentUL.getAttribute("data-form")];
		tabCurrent = formObj.tab.value;
	} else {
		tabCurrent = parentUL.getAttribute("data-tab-active");
	}
	var tabActive = tabObj.getAttribute("data-tab");
	if (tabActive == tabCurrent) { return false; }
	// update tabs
	var liObjs = parentUL.querySelectorAll("li")
	for (var l = 0; l < liObjs.length; l++) {	
 		var liObj = liObjs[l];
		var tabName = liObj.getAttribute("data-tab");
		if (tabName == tabActive) {
			var regExp = /tab-active/g;
			if (!regExp.test(liObj.className)) {
				liObj.className = (liObj.className + " tab-active").trim();
			}
		} else {
			liObj.className = liObj.className.replace(/tab-active/gi, "").trim();
		}
	}

	var tabObjs = [];
	// hide old tab data
	var tabData = document.getElementById(tabCurrent+"_data");
	if (tabData) { tabObjs.push(tabData); }
	tabData = parentBlock.querySelectorAll(".tab-"+tabCurrent); 
	for (var t = 0; t < tabData.length; t++) {
		tabObjs.push(tabData[t]); 
	}
	tabData = parentBlock.querySelectorAll("."+tabCurrent); 
	for (var t = 0; t < tabData.length; t++) {
		tabObjs.push(tabData[t]); 
	}
	for (var t = 0; t < tabObjs.length; t++) {
		tabData = tabObjs[t];
		tabData.className = tabData.className.replace(/tab-show|tab-hide/gi, "").trim();
		tabData.className = (tabData.className + " tab-hide").trim();
	}
	// show new tab data
	tabObjs = [];
	tabData = document.getElementById(tabActive+"_data");
	if (tabData) { tabObjs.push(tabData); }
	tabData = parentBlock.querySelectorAll(".tab-"+tabActive); 
	for (var t = 0; t < tabData.length; t++) { 
		tabObjs.push(tabData[t]); 
	}
	tabData = parentBlock.querySelectorAll("."+tabActive); 
	for (var t = 0; t < tabData.length; t++) { 
		tabObjs.push(tabData[t]); 
	}
	for (var t = 0; t < tabObjs.length; t++) {
		tabData = tabObjs[t];
		tabData.className = tabData.className.replace(/tab-show|tab-hide/gi, "").trim();
		tabData.className = (tabData.className + " tab-show").trim();
	}
	// set new active tab
	if (formObj) {
		formObj.tab.value = tabActive;
		// call onchange event for tab element change
		var changeEvent = document.createEvent('Event');
		changeEvent.initEvent("change", false, true);
		formObj.tab.dispatchEvent(changeEvent);
	} else {
		parentUL.setAttribute("data-tab-active", tabActive);
	}
}

function vaSliderParse(block, resizeEvent)
{
	if (resizeEvent) { // add resize event
		window.addEventListener("resize", function () { vaSliderParse(block, false); }, false);
	}
	var bodyObj = block.querySelector("[data-type='body']");
	var leftObj = block.querySelector("[data-type='left']");
	var rightObj = block.querySelector("[data-type='right']");
	var bodyWidth = bodyObj.offsetWidth;
	bodyObj.style.overflow = "hidden";

	leftObj.addEventListener("click", function () { vaSliderLeft(block); }, false);
	rightObj.addEventListener("click", function () { vaSliderRight(block); }, false);

	var slider = block.querySelector("[data-type='slider']");
	var slides = slider.querySelectorAll("[data-type='slide']");
	var sliderWidth = 0;
	for (var li = 0; li < slides.length; li++) {
		var slide = slides[li];
		var slideWidth = slide.offsetWidth;
		var slideHeight = slide.offsetHeight;
		sliderWidth += slideWidth;
	}
	var maxMove = 0;
	for (var li = 0; li < slides.length; li++) {
		var slide = slides[li];
		var slideWidth = slide.offsetWidth;
		if ((maxMove + bodyWidth) <= sliderWidth) {
			maxMove += slideWidth;
		} else {
			break;
		}
	}
	block.setAttribute("data-slider-width", sliderWidth);
	block.setAttribute("data-max-move", maxMove);
	leftObj.style.visibility = "hidden";
	if (bodyWidth > sliderWidth) {
		rightObj.style.visibility = "hidden";
	} else {
		rightObj.style.visibility = "visible";
	}
	slider.style.position = "relative";
	slider.style.width = sliderWidth+"px";
	slider.style.left = "0";
}

function vaSliderRight(block)
{
	var bodyObj = block.querySelector("[data-type='body']");
	var slider = block.querySelector("[data-type='slider']");
	var slides = slider.querySelectorAll("[data-type='slide']");
	var sliderWidth = parseInt(block.getAttribute("data-slider-width"));
	var maxMove = parseInt(block.getAttribute("data-max-move"));
	var leftObj = block.querySelector("[data-type='left']");
	var rightObj = block.querySelector("[data-type='right']");
	var bodyWidth = bodyObj.offsetWidth;

	// calculate width we can move
	var moveWidth = 0;
	var sliderLeft = Math.abs(parseInt(slider.style.left));
	var endWidth = sliderLeft + bodyWidth;
	for (var li = 0; li < slides.length; li++) {
		var slide = slides[li];
		var slideWidth = slide.offsetWidth;
		if ((moveWidth + slideWidth) <= endWidth) {
			moveWidth += slideWidth;
		} else {
			break;
		}
	}
	if (moveWidth > maxMove) { moveWidth = maxMove;  }
	if (moveWidth > 0) { leftObj.style.visibility = "visible"; }
	if (moveWidth >= maxMove) { rightObj.style.visibility = "hidden";	}
	vaSliderMove(slider, moveWidth);
}

function vaSliderLeft(block)
{
	var bodyObj = block.querySelector("[data-type='body']");
	var slider = block.querySelector("[data-type='slider']");
	var slides = slider.querySelectorAll("[data-type='slide']");
	var sliderWidth = parseInt(block.getAttribute("data-slider-width"));
	var maxMove = parseInt(block.getAttribute("data-max-move"));
	var leftObj = block.querySelector("[data-type='left']");
	var rightObj = block.querySelector("[data-type='right']");
	var bodyWidth = bodyObj.offsetWidth;

	// calculate width we can move
	var moveWidth = 0;
	var sliderLeft = Math.abs(parseInt(slider.style.left));
	var endWidth = sliderLeft - bodyWidth;
	for (var li = 0; li < slides.length; li++) {
		var slide = slides[li];
		var slideWidth = slide.offsetWidth;
		if (moveWidth < endWidth) {
			moveWidth += slideWidth;
		} else {
			break;
		}
	}
	if (moveWidth == 0) { leftObj.style.visibility = "hidden"; }
	if (moveWidth < maxMove) { rightObj.style.visibility = "visible";	}
	vaSliderMove(slider, moveWidth);
}

function vaSliderMove(slider, finalLeft)
{
	var moveStep = 10;
	var sliderLeft = Math.abs(parseInt(slider.style.left));
	if (sliderLeft > finalLeft) {
		sliderLeft -= moveStep; 
		if (sliderLeft < finalLeft) { sliderLeft = finalLeft; }
	} else if (sliderLeft < finalLeft) {
		sliderLeft += moveStep
		if (sliderLeft > finalLeft) { sliderLeft = finalLeft; }
	}
	slider.style.left = "-"+sliderLeft+"px";
	if (sliderLeft != finalLeft) {
		setTimeout(function() { vaSliderMove(slider, finalLeft); }, 10);
	}
}

function vaSlideShowParse(block)
{
	// set for main block relative position 
	block.style.position = "relative";
	block.style.overflow = "hidden";

	// check block id
	var pbId = block.hasAttribute("data-pb-id") ? block.getAttribute("data-pb-id") : "";
	// check slider type
	var sliderType = parseInt(block.getAttribute("data-slider-type"));
	if (isNaN(sliderType)) { sliderType = 5; }
	block.setAttribute("data-slider-type", sliderType);

	// check slider speed
	var sliderSpeed = parseInt(block.getAttribute("data-slider-speed"));
	if (isNaN(sliderSpeed)) { sliderSpeed = 1; }
	block.setAttribute("data-slider-speed", sliderSpeed);

	// check slides and get max height 
	var slideIndex = 0; var maxHeight = 0; var leftPos = 0; var topPos = 0; var rightPos = 0; var bottomPos = 0;
	var slides = block.querySelectorAll("[data-slide]"); 
	for (var s = 0; s < slides.length; s++) {	
		slideIndex++;
		var slide = slides[s];
		slide.style.position = "absolute";
		slide.setAttribute("data-slide", slideIndex);
		var slideHeight = slide.offsetHeight;
		var slideWidth = slide.offsetWidth;
		if (slideHeight > maxHeight) { maxHeight = slideHeight; }
		if (sliderType == 1) { // vertical
			slide.style.top = topPos+"px";
			slide.style.left = "0";
			topPos += slideHeight;
		} else if (sliderType == 2) { // horizontal
			slide.style.top = "0";
			slide.style.left = leftPos+"px";
			leftPos += slideWidth;
		} else if (sliderType == 3) { // vertical 
			slide.style.bottom = bottomPos+"px";
			slide.style.left = "0";
			bottomPos += slideHeight;
		} else if (sliderType == 4) { // horizontal
			slide.style.top = "0";
			slide.style.right = rightPos+"px";
			rightPos += slideWidth;
		} else {
			// for slideshow use maximum size for all slides so they can properly shown and resized
			slide.style.top = "0";
			slide.style.left = "0";
			slide.style.bottom = "0";
			slide.style.right = "0";
			if (slideIndex > 1) {
				// keep only first slide to show on start and hide all following
				slide.style.visibility = "hidden";
			}
		}
	}
	// set height for slider
	if (!block.style.height) {
		block.style.height = maxHeight+"px";
		block.setAttribute("data-slides-height", "auto");
	}
	// set slides max positions
	block.setAttribute("data-slides-top", topPos);
	block.setAttribute("data-slides-left", leftPos);
	block.setAttribute("data-slides-bottom", bottomPos);
	block.setAttribute("data-slides-right", rightPos);

	// set active slide and number of slides
	var slidesTotal = slideIndex;
	block.setAttribute("data-slides", slideIndex);
	block.setAttribute("data-active-slide", 1);

	// check transition parameters 
	var transitionDelay = block.getAttribute("data-transition-delay");
	if (transitionDelay.match(/ms$/)) {
		transitionDelay = parseInt(transitionDelay.replace("ms", ""));
	} else {
		transitionDelay = parseInt(transitionDelay.replace("s", "")) * 1000;
	}
	if (isNaN(transitionDelay)) { transitionDelay = defaultDelay ; }
	block.setAttribute("data-transition-delay", transitionDelay);

	var transitionDuration = block.getAttribute("data-transition-duration");
	if (transitionDuration.match(/ms$/)) {
		transitionDuration = parseInt(transitionDuration.replace("ms", ""));
	} else {
		transitionDuration = parseInt(transitionDuration.replace("s", "")) * 1000;
	}
	if (isNaN(transitionDuration)) { transitionDuration = defaultDuration; }
	block.setAttribute("data-transition-duration", transitionDuration);

	if (sliderType >= 1 && sliderType <= 4) {
		// slide moves all the times up, down, left, right
		//vaInitSlideShowMove(pbId, sliderType); delete old code
		block.onmouseover = function (){ this.setAttribute("data-pause", 1); }; 
		block.onmouseout = function (){ this.removeAttribute("data-pause"); };
	
		setTimeout(function() { vaMoveSlider(block); }, 500);
	} else if (slideIndex > 1) {
		// slide changes one by one
		// check where we need to show navigation inside or outside main slider block
		var navType = "";
		if (block.hasAttribute("data-slider-nav-type")) {
			navType = block.getAttribute("data-slider-nav-type");
		} else if (block.hasAttribute("data-slider-nav-type")) {
			navType = block.getAttribute("data-slider-nav");
		}

		var navObj = document.createElement("div");
		navObj.className = "slider-nav";
		if (navType == "out" || navType == "outside") {
			block.parentNode.insertBefore(navObj, block.nextSibling);
		} else {
			block.appendChild(navObj);
		}
	  
		for (var s = 1; s <= slidesTotal; s++) {
			var slideNavObj = document.createElement("a");
			slideNavObj.setAttribute("data-slide", s);
			if (s == 1) {
				slideNavObj.className = "slide-nav slide-active";
			} else {
				slideNavObj.className = "slide-nav ";
			}
			slideNavObj.innerHTML = "<span>"+s+"</span>";
			slideNavObj.onclick = function () { vaSlideActivate(block, this); return false; };
			navObj.appendChild(slideNavObj);
		}
	  
		var tid = setTimeout(function() { vaSlideShow(block); }, transitionDelay);
		block.setAttribute("data-tid", tid);
	}
}

function vaSlideShowCalc(block)
{
	// recalculate slides height when window was resized or some new elements were loaded
	var slidesHeight = (block.hasAttribute("data-slides-height")) ? block.getAttribute("data-slides-height") : "";
	if (slidesHeight.toLowerCase() != "auto") {
		return;
	}
	block.style.removeProperty("height");
	var sliderType = parseInt(block.getAttribute("data-slider-type"));
	// check slides and get max height 
	var slideIndex = 0; var maxHeight = 0; var leftPos = 0; var topPos = 0; var rightPos = 0; var bottomPos = 0;
	var slides = block.querySelectorAll("[data-slide]"); 
	for (var s = 0; s < slides.length; s++) {	
		slideIndex++;
		var slide = slides[s];
		slide.style.top = "0";
		slide.style.left = "0";
		slide.style.removeProperty("bottom");
		slide.style.removeProperty("right");
		var slideHeight = slide.offsetHeight;
		var slideWidth = slide.offsetWidth;
		if (slideHeight > maxHeight) { maxHeight = slideHeight; }
		if (sliderType == 1) { // vertical
			slide.style.top = topPos+"px";
			topPos += slideHeight;
		} else if (sliderType == 2) { // horizontal
			slide.style.left = leftPos+"px";
			leftPos += slideWidth;
		} else if (sliderType == 3) { // vertical 
			slide.style.bottom = bottomPos+"px";
			bottomPos += slideHeight;
		} else if (sliderType == 4) { // horizontal
			slide.style.right = rightPos+"px";
			rightPos += slideWidth;
		} else {
			// for slideshow don't need to change any parameters
			slide.style.bottom = "0";
			slide.style.right = "0";
		}
	}
	// set new height for slider
	block.style.height = maxHeight+"px";
	// set slides max positions
	block.setAttribute("data-slides-top", topPos);
	block.setAttribute("data-slides-left", leftPos);
	block.setAttribute("data-slides-bottom", bottomPos);
	block.setAttribute("data-slides-right", rightPos);
}

function vaMoveSlider(block)
{
	// check for pause first
	if (block.hasAttribute("data-pause")) { 
		setTimeout(function() { vaMoveSlider(block); }, 25);
		return; 
	}

	var sliderType = parseInt(block.getAttribute("data-slider-type"));
	var sliderSpeed = parseInt(block.getAttribute("data-slider-speed"));
	var topPos = block.getAttribute("data-slides-top");
	var leftPos = block.getAttribute("data-slides-left");
	var bottomPos = block.getAttribute("data-slides-bottom");
	var rightPos = block.getAttribute("data-slides-right");
	if (sliderType == 1) {
		topPos -= sliderSpeed;
	} else if (sliderType == 2) {
		leftPos -= sliderSpeed;
	} else if (sliderType == 3) {
		bottomPos -= sliderSpeed;
	} else if (sliderType == 4) {
		rightPos -= sliderSpeed;
	}

	var slides = block.querySelectorAll("[data-slide]"); 
	for (var s = 0; s < slides.length; s++) {	
		var slide = slides[s];
		if (sliderType == 1) {
			var slideTop = parseInt(slide.style.top) - sliderSpeed;
			if (-slideTop > slide.offsetHeight) { 	
				slideTop = (topPos > block.offsetHeight) ? topPos : block.offsetHeight;
				slide.style.top = slideTop +"px";
				topPos = slideTop + slide.offsetHeight;
			} else {
				slide.style.top = slideTop +"px";
			}
		} else if (sliderType == 2) {
			var slideLeft = parseInt(slide.style.left) - sliderSpeed;
			if (-slideLeft > slide.offsetWidth) { 	
				slideLeft = (leftPos > block.offsetWidth) ? leftPos : block.offsetWidth;
				slide.style.left = slideLeft +"px";
				leftPos = slideLeft + slide.offsetWidth;
			} else {
				slide.style.left = slideLeft +"px";
			}
		} else if (sliderType == 3) {
			var slideBottom = parseInt(slide.style.bottom) - sliderSpeed;
			if (-slideBottom > slide.offsetHeight) { 	
				slideBottom = (bottomPos > block.offsetHeight) ? bottomPos : block.offsetHeight;
				slide.style.bottom = slideBottom +"px";
				bottomPos = slideBottom + slide.offsetHeight;
			} else {
				slide.style.bottom = slideBottom +"px";
			}
		} else if (sliderType == 4) {
			var slideRight = parseInt(slide.style.right) - sliderSpeed;
			if (-slideRight > slide.offsetWidth) { 	
				slideRight = (rightPos > block.offsetWidth) ? rightPos : block.offsetWidth;
				slide.style.right = slideRight +"px";
				rightPos = slideRight + slide.offsetWidth;
			} else {
				slide.style.right = slideRight +"px";
			}
		}
	}
	// update slides max positions
	block.setAttribute("data-slides-top", topPos);
	block.setAttribute("data-slides-left", leftPos);
	block.setAttribute("data-slides-bottom", bottomPos);
	block.setAttribute("data-slides-right", rightPos);

	setTimeout(function() { vaMoveSlider(block); }, 25);
}

function vaSlideShow(block)
{
	if (block.hasAttribute("data-transition-stop")) { return; }

	var activeSlide = parseInt(block.getAttribute("data-active-slide"));
	var slidesNumber = parseInt(block.getAttribute("data-slides"));
	var newSlide = (activeSlide >= slidesNumber) ? 1 : activeSlide+1;

	var curObj = block.querySelector("[data-slide='"+activeSlide+"']");
	var newObj = block.querySelector("[data-slide='"+newSlide+"']");
	block.setAttribute("data-transition-step", "0");

	curObj.style.opacity = "1";
	newObj.style.opacity = "0";
	newObj.style.visibility = "visible";
				

	var tid = setTimeout(function() { vaSlideShowChange(block, curObj, newObj); }, 20);
	block.setAttribute("data-tid", tid);
}

function vaSlideShowChange(block, curObj, newObj)
{
	if (block.hasAttribute("data-transition-stop")) { 
		// stop slide changes and hide new slide
		newObj.style.visibility = "hidden";
		newObj.style.opacity = "";
		return; 
	}

	var step = parseInt(block.getAttribute("data-transition-step"));
	var transitionDuration = parseInt(block.getAttribute("data-transition-duration"));
	var changeTimeout = Math.ceil(transitionDuration / 100);

	// increase step number up to 100
	step = step + 1;
	block.setAttribute("data-transition-step", step);

	curObj.style.opacity = (100-step)/100; 
	newObj.style.opacity = step/100;


	if (step < 100) {
		var tid = setTimeout(function() { vaSlideShowChange(block, curObj, newObj); }, changeTimeout);
	} else {
		curObj.style.visibility = "hidden";
		newObj.style.visibility = "visible";
		// remove opacity 
		curObj.style.opacity = "";
		newObj.style.opacity = "";

		block.setAttribute("data-active-slide", newObj.getAttribute("data-slide"));

		// update navigation
		var activeSlide = curObj.getAttribute("data-slide");
		var newSlide = newObj.getAttribute("data-slide");
		var sliderObj = block.querySelector(".slider-nav");
		if (!sliderObj) {
			sliderObj = block.parentNode.querySelector(".slider-nav");
		}
		var curNavObj = sliderObj.querySelector("[data-slide='"+activeSlide+"']");
		var newNavObj = sliderObj.querySelector("[data-slide='"+newSlide+"']");
		curNavObj.className = curNavObj.className.replace(/slide-active/gi, "").trim();
		newNavObj.className = (newNavObj.className + " slide-active").trim();

		var transitionDelay = parseInt(block.getAttribute("data-transition-delay"));
		var tid = setTimeout(function() { vaSlideShow(block); }, transitionDelay);
		block.setAttribute("data-tid", tid);
	}
}

function vaSlideActivate(block, slideNavObj)
{
	var tid = parseInt(block.getAttribute("data-tid"));
	if (tid) { clearTimeout(tid); }
	block.setAttribute("data-transition-stop", 1); // set attribute to stop any previous slide changes

	var transitionDuration = parseInt(block.getAttribute("data-transition-duration"));
	var changeTimeout = Math.ceil(transitionDuration / 50);

	setTimeout(function() { vaSlideDisplay(block, slideNavObj); }, changeTimeout);
}

function vaSlideDisplay(block, slideNavObj)
{
	// hide active object
	var activeSlide = parseInt(block.getAttribute("data-active-slide"));
	var curObj = block.querySelector("[data-slide='"+activeSlide+"']");
	curObj.style.opacity = "";
	curObj.style.visibility = "hidden";
	// activate new object
	var newSlide = parseInt(slideNavObj.getAttribute("data-slide")); 
	var newObj = block.querySelector("[data-slide='"+newSlide+"']");
	newObj.style.opacity = "";
	newObj.style.visibility = "visible";
	block.setAttribute("data-active-slide", newSlide);

	// restart slide show
	block.removeAttribute("data-transition-stop");
	var transitionDelay = parseInt(block.getAttribute("data-transition-delay"));
	var tid = setTimeout(function() { vaSlideShow(block); }, transitionDelay);
	block.setAttribute("data-tid", tid);

	var sliderObj = block.querySelector(".slider-nav");
	if (!sliderObj) {
		sliderObj = block.parentNode.querySelector(".slider-nav");
	}
	var curNavObj = sliderObj.querySelector("[data-slide='"+activeSlide+"']");
	var newNavObj = sliderObj.querySelector("[data-slide='"+newSlide+"']");
	curNavObj.className = curNavObj.className.replace(/slide-active/gi, "").trim();
	newNavObj.className = (newNavObj.className + " slide-active").trim();
}

function vaImagesParse(imageBlock)
{
	var mainClass = imageBlock.hasAttribute("data-main-class") ? imageBlock.getAttribute("data-main-class") : "img-default";
	var subClass = imageBlock.hasAttribute("data-sub-class") ? imageBlock.getAttribute("data-sub-class") : "sub-images";
	var mainImage = document.querySelector("."+mainClass);
	if (mainImage) {
		mainImage.addEventListener("click", vaImageClick, false);
	}
	var subImages = document.querySelectorAll("."+subClass + " img");
	for (var i = 0; i < subImages.length; i++) {
		var subImage = subImages[i];
		if (subImage.hasAttribute("data-image-over")) {
			subImage.addEventListener("mouseover", vaImageOver, false);
		}
		if (subImage.hasAttribute("data-image-click")) {
			subImage.addEventListener("click", vaImageClick, false);
		}
	}
}

function vaImageOver()
{
	var parentObj = vaParentJS(this);
	var mainClass = parentObj.hasAttribute("data-main-class") ? parentObj.getAttribute("data-main-class") : "img-default";
	var mainImage = document.querySelector("."+mainClass);
	if (mainImage) {
		// update image on click on main image
		var imageClick = this.hasAttribute("data-image-click") ? this.getAttribute("data-image-click") : "";
		mainImage.setAttribute("data-image-click", imageClick);
		// change main image src
		var imageOver = this.hasAttribute("data-image-over") ? this.getAttribute("data-image-over") : "";

		if (!vaImages[imageOver]) {
			vaImages[imageOver] = new Image();
			vaImages[imageOver].src = imageOver;
			vaImages[imageOver].addEventListener("load", function(){ mainImage.src = vaImages[imageOver].src; }, false);
		} else {
			mainImage.src = vaImages[imageOver].src;
		}
	}
}

function vaImageClick()
{
	var imgSrc = (this.hasAttribute("data-image-click")) ? this.getAttribute("data-image-click") : this.src;
	if (!imgSrc) { imgSrc = this.src; }
	popupImage(this);
}

function vaExpandFieldsParse(buttonObj)
{
	buttonObj.addEventListener("click", function () { vaExpandFields(this, false); }, false);
	vaExpandFields(buttonObj, true);
}

	function vaExpandFields(buttonObj, initFields)	
	{
    var fieldsObj;
		var fieldsClass = buttonObj.getAttribute("data-fields-class");
		if (fieldsClass) {	
			fieldsObj = vaParent(buttonObj, "."+fieldsClass);
			if (!fieldsObj) {
				fieldsObj = document.querySelector("."+fieldsClass);	
			}
		} else {
			fieldsObj = vaParent(buttonObj, "FORM");
			if (!fieldsObj) {
				fieldsObj = vaParent(buttonObj, "FORM");
			}
		}

		var fieldsExpandClass = buttonObj.getAttribute("data-fields-expand-class");
		if (!fieldsExpandClass) { fieldsExpandClass = "fields-open"; }
		var fieldClass = buttonObj.getAttribute("data-field-class");
		if (!fieldClass) { fieldClass = "field"; }
		var fieldHideClass = buttonObj.getAttribute("data-field-hide-class");
		if (!fieldHideClass) { fieldHideClass = "field"; }

		var regExp = new RegExp(fieldsExpandClass, "gi");
		var showFields = true;
		if (initFields || regExp.test(fieldsObj.className)) {
			showFields = false;
			fieldsObj.className = fieldsObj.className.replace(regExp, "").trim();
			buttonObj.className = buttonObj.className.replace(regExp, "").trim();
		} else {
			showFields = true;
			fieldsObj.className = fieldsObj.className + " " + fieldsExpandClass;
			buttonObj.className = buttonObj.className + " " + fieldsExpandClass;
		}

		var searchFields = fieldsObj.querySelectorAll(".field");
		if (showFields) {
			for (var f = 0; f < searchFields.length; f++) {
				var field = searchFields[f];
				field.className = field.className.replace(/hide-block|hidden-block/gi, "").trim();
			}
		} else {
			var visibleFields = 0;
			for (var f = 0; f < searchFields.length; f++) {
				var field = searchFields[f];
				if (field.hasAttribute("data-control")) {
					var nonDefault = false;
					var defaultValue = (field.hasAttribute("data-default-value")) ? field.getAttribute("data-default-value") : "";
					var controls = field.getAttribute("data-control").split(/[\s;,]/);
					for (var c = 0; c < controls.length; c++) {
						var controlName = controls[c];
						var control = fieldsObj.querySelector("[name="+controlName+"]");
						if (control.type == "radio") {
							var radioControls = fieldsObj.querySelectorAll("[name="+controlName+"]");
							for (var rc = 0; rc < radioControls.length; rc++) {
								if (radioControls[rc].checked && radioControls[rc].value != defaultValue) {
									nonDefault = true;
								}
							}
						} else {
							var controlValue = control.value;
							if (controlValue != "" && controlValue != defaultValue) {
								nonDefault = true;
							}
						}
					}
					if (nonDefault) {
						visibleFields++;
					} else {
						field.className = field.className + " hide-block";
					}
				}
			}
			if (visibleFields == 0) {
				// show default fields
				var defaultFields = fieldsObj.querySelectorAll("[data-default-field='1']");
				for (var f = 0; f < defaultFields.length; f++) {                       
					var field = defaultFields[f];
					field.className = field.className.replace(/hide-block|hidden-block/gi, "").trim();
				}
			}
		}
	}


function vaRemoveRow(rowObj)
{
	var height, opactiy;
	if (!rowObj.hasAttribute("data-height")) {
		height = rowObj.offsetHeight;
		rowObj.setAttribute("data-height", height);
		rowObj.style.opacity = 1;
		rowObj.style.height = height+"px";
	} else {
		opactiy = parseFloat(rowObj.style.opacity);
		height = parseInt(rowObj.getAttribute("data-height"));
		if (opactiy > 0) {
			opactiy -= 0.05;
			var rowCells = rowObj.querySelectorAll("td");
			for (var r = 0; r < rowCells.length; r++) {
				rowCells[r].style.opacity = opactiy;
				if (opactiy == 0) {
					rowCells[r].innerHTML = "";
					rowCells[r].style.padding = 0;
				}
			}
			rowObj.style.opacity = opactiy;
		} else {
			height -= 1;
			if (height > 0) {
				rowObj.style.height = height+"px";
				rowObj.setAttribute("data-height", height);
			} else {
				rowObj.parentNode.removeChild(rowObj);		
			}
		}
	}
	if (height > 0) { setTimeout(function() { vaRemoveRow(rowObj); }, 5); }
}

function vaFormOperation(buttonObj, operation) 
{
	if (!buttonObj) { return false; }
	var formObj = vaParent(buttonObj, "FORM");
	if (!formObj || !formObj.operation) { return false; }
	formObj.operation.value = operation;
	var parentObj = buttonObj.parentNode; 
	vaSpin(parentObj);
}

function vaSpin(parentObj, someFunction, someParams, bgOpacity)
{
	var parentObjId = "";
	if (typeof parentObj == "object") {
		if (!parentObj.id) { parentObj.id = Date.now().toString()+"_"+Math.random().toString().replace("0.",""); } // generate unique id
		parentObjId = parentObj.id;
	} else {
		parentObjId = parentObj;
		parentObj = document.getElementById(parentObjId);
		if (!parentObj) {
			if (/[\s;,]/.test(parentObjId)) {
				var ids = parentObjId.split(/[\s;,]/);
				for (var i = 0; i < ids.length; i++) {
					vaSpin(ids[i], someFunction, someParams, bgOpacity);
				}
			}
			return;
		}
	}

	// check if shadow object was already added to exlude duplicated objects
	var shadowObjId = "pb_shadow_" + parentObjId;
	var shadowObj = document.getElementById(shadowObjId);
	if (shadowObj) { return; }

	if (typeof bgOpacity == "undefined") {
		bgOpacity = "0.3";
	}

	//var xPos = findPosX(parentObj);
	//var yPos = findPosY(parentObj);
	var blockWidth = parentObj.offsetWidth;
	if (blockWidth == 0) { blockWidth = parseInt(window.getComputedStyle(parentObj,null).getPropertyValue("width")); }
	if (!blockWidth) { blockHeight = 100; }
	var blockHeight = parentObj.offsetHeight;
	if (blockHeight == 0) { blockHeight = parseInt(window.getComputedStyle(parentObj,null).getPropertyValue("height")); }
	if (!blockHeight) { blockHeight = 100; }

	// set relative position if current position is not absolute or fixed for parent object to set shadow and show progress 
	var parentPos = window.getComputedStyle(parentObj,null).getPropertyValue("position");
	if (parentPos != "absolute" && parentPos != "fixed") {
		parentObj.style.position = "relative";
	}

	// create a new shadow object to add spin icon inside
	shadowObj = document.createElement("div");
	shadowObj.id = shadowObjId;
	shadowObj.style.zIndex = "999";
	shadowObj.style.position = "absolute";
	shadowObj.style.backgroundColor = "rgba(127,127,127,0.3)";
	shadowObj.style.textAlign = "center";
	shadowObj.style.left = "0";
	shadowObj.style.top  = "0";
	shadowObj.style.right = "0";
	shadowObj.style.bottom = "0";
	shadowObj.style.fontSize = "48px"; 
	/*
	shadowObj.style.width = blockWidth + "px";
	shadowObj.style.height = blockHeight + "px";//*/
	shadowObj.onclick    = function() { vaStopSpin(parentObjId, someFunction, someParams); };
	parentObj.insertBefore(shadowObj, parentObj.firstChild);

	var spinHeight = 16;
	if (blockHeight >= 40 && blockHeight <= 100) {
		spinHeight = Math.round(blockHeight / 2);
	} else if (blockHeight > 100) {
		spinHeight = Math.round(blockHeight / 4);
	}
	var spinObj = document.createElement("i");
	spinObj.className = "spin";
	spinObj.style.zIndex = "1000";
	spinObj.style.fontSize = spinHeight + "px";
	spinObj.style.position = "absolute";
	spinObj.style.left = Math.round((blockWidth-spinHeight)/2) + "px";
	spinObj.style.top  = Math.round((blockHeight-spinHeight)/2) + "px";
	shadowObj.appendChild(spinObj);
}

function vaStopSpin(parentObj, someFunction, someParams)
{
	var parentObjId = "";
	if (typeof parentObj === "object") {
		parentObjId = parentObj.id;
	} else {
		parentObjId = parentObj;
		parentObj = document.getElementById(parentObjId);
		if (!parentObj) {
			if (/[\s;,]/.test(parentObjId)) {
				var ids = parentObjId.split(/[\s;,]/);
				for (var i = 0; i < ids.length; i++) {
					vaStopSpin(ids[i], someFunction, someParams);
				}
			}
			return;
		}
	}

	var parentObj = (typeof parentObjId == "object") ? parentObjId : document.getElementById(parentObjId);
	if (parentObj) {
		var shadowObj = document.getElementById("pb_shadow_" + parentObjId);
		if (shadowObj) {
			parentObj.removeChild(shadowObj);
		}
	}
	if (someFunction) { someFunction(someParams); }
}

function isMobile() {
  var check = false;
  (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
  return check;
};

function isMobileTablet() {
  var check = false;
  (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
  return check;
};

function vaCloseCustomPopup()
{
	var jsObjs = document.querySelectorAll(".bk-custom-popup");
	for (var j = 0; j < jsObjs.length; j++) {
		var jsObj = jsObjs[j];
		jsObj.parentNode.removeChild(jsObj);
	}
}

function vaGetKey(event)
{
	var key = event.key;
	if (typeof key === "undefined") {
		if (event.keyCode == 27) {
			key = "Escape"
		} else if (event.keyCode == 13) {
			key = "Enter"
		} else if (event.keyCode == 17) {
			key = "Control"
		} else if (event.keyCode == 18) {
			key = "Alt"
		} else {
			key = event.keyCode;
		}
	}
	return key;
}

function vaKeyUp(event)
{
	event = event || window.event;
	var key = vaGetKey(event);
	if (key == "Escape") {
		vaCloseCustomPopup(); // close custom popup
		if (typeof vaDisablePopupVideo !== 'undefined' && typeof vaDisablePopupVideo === 'function') {
			vaDisablePopupVideo(); // disaple popup video if it was opened
		}
	}
}

window.addEventListener("DOMContentLoaded", vaInit, false);
document.addEventListener("keyup", function(event) { vaKeyUp(event); }, false);
window.addEventListener('resize', vaResize);
