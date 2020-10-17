// check key press events
document.onkeyup = function(evt) {
	evt = evt || window.event;
	docKeyUp(evt.keyCode);
};


function reloadBlock(pbId, htmlId, pbParams)
{
	var url = "block.php?ajax=1&pb_id=" + encodeURIComponent(pbId);
	if (pbParams) { url += "&" + pbParams; }
	if (!htmlId) { htmlId = "pb_"+pbId; }
	vaSpin(htmlId); // start spinning effect till block loaded
	callAjax(url, vaBlockData, htmlId);
}

function vaBlockData(responseData, htmlId) {
	try { 
		responseData = JSON.parse(responseData); 
	} catch(e){
		alert("Bad response: " + responseData);
	}
	replaceBlock(responseData.block, htmlId);
}

function replaceBlock(blockContent, htmlId)
{
	var oldBlockObj = document.getElementById(htmlId);
	var parentObj = oldBlockObj.parentNode;
	var divObj = document.createElement('div'); 
	divObj.innerHTML = blockContent.trim(); 
	var newBlockObj = divObj.firstChild;
	parentObj.replaceChild(newBlockObj, oldBlockObj);
	if (newBlockObj.tagName == "LI") {
		vaNavLi(newBlockObj);
	} else {
		// check children expand and pagination objects
		var jsObjs = document.querySelectorAll("[data-js]");
		for (var j = 0; j < jsObjs.length; j++) {
			var jsObj = jsObjs[j];
			var jsValue = jsObj.getAttribute("data-js").toLowerCase();
			var jsType = jsObj.hasAttribute("data-js-type") ? jsObj.getAttribute("data-js-type").toLowerCase() : "";
			if (jsValue == "expand") {
				vaExpandParse(jsObj, jsType);
			} else if (jsValue == "link") {
				vaLinkParse(jsObj);
			} else if (jsValue == "rating") {
				vaRatingParse(jsObj);
			} 
		}

		// check parent expand object
		if (newBlockObj.hasAttribute("data-js") && newBlockObj.getAttribute("data-js").toLowerCase() == "expand") {
			vaExpandParse(newBlockObj);
		}
	}
	vaStopSpin(htmlId);
}

function showMessageBlock(msgText, controlId)
{
	// delete popup block if it was initialized before
	hideMessageBlock();

	var controlObj = document.getElementById(controlId);
	if (!controlObj) {
		if (/[\s;,]/.test(controlId)) {
			var ids = controlId.split(/[\s;,]/);
			controlObj = document.getElementById(ids[0]); 
		}
		if (!controlObj) { return; }
	}
	var pageSize = getPageSize()
	var pageScroll = getScroll();
	var popupObj = document.createElement("div");
	popupObj.id = "messageBlock";
	popupObj.style.zIndex = "20000"; 
	popupObj.style.position = "absolute";
	popupObj.onclick = function() { hideMessageBlock(); };
	popupObj.innerHTML = msgText;
	document.body.insertBefore(popupObj, document.body.firstChild);
	var popupWidth = popupObj.offsetWidth;
	var popupHeight = popupObj.offsetHeight
	// calculate message block position
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

function showPopupBlock(msg)
{
	if (msg.match(/^[a-z0-9\-\_]+$/) ) {
		if (document.getElementById(msg)) {
			msg = document.getElementById(msg).innerHTML;
		}
	}

	// delete popup block if it was initialized before
	hidePopupFrame();

	var pageSize = getPageSize()

	// add popup area
	var areaObj = document.createElement("div");
	areaObj.id = "popupArea";
	areaObj.className = "popup-area";
	document.body.insertBefore(areaObj, document.body.firstChild);

	var popupObj = document.getElementById("popupBlock");
	if (popupObj) {
		var popupBody = popupObj.querySelector(".popup-body");
		popupBody.innerHTML = msg;
		popupObj.style.display = "block";

		// move popup to the center
		var popupWidth = popupObj.offsetWidth;
		var popupHeight = popupObj.offsetHeight
		var popupLeft = (pageSize[0] - popupWidth)/2;
		var popupTop = (pageSize[1] - popupHeight)/2;
		if (popupLeft < 0) { popupLeft = 0; }
		if (popupTop < 0) { popupTop = 0; }
		// if page was scrolled move popup window to scrolled height
		if (document.body.scrollTop || document.documentElement.scrollTop) {
			popupTop += (document.body.scrollTop || document.documentElement.scrollTop);
		}
  
		popupObj.style.left = popupLeft + "px";
		popupObj.style.top = popupTop + "px";

	} else {
		areaObj.innerHTML = msg;
	}
}

function hidePopupBlock()
{
	var popupArea = document.getElementById("popupArea");
	if (popupArea) {
		var parentObj = popupArea.parentNode;
		parentObj.removeChild(popupArea);
	}
	var popupObj = document.getElementById("popupBlock");
	if (popupObj) {
		popupObj.style.display = "none";
	}
}

function showPopupFrame(msgText)
{
	// delete popup block if it was initialized before
	hidePopupFrame();

	var pageSize = getPageSize()
	var popupObj = document.createElement("div");
	popupObj.id = "popupArea";
	popupObj.className = "popupArea";
	popupObj.style.zIndex = "111";
	popupObj.style.position = "absolute";
	popupObj.style.left = "0px";
	popupObj.style.top = "0px";
	popupObj.style.backgroundColor = "rgba(0, 0, 0, 0.6)";
	popupObj.innerHTML = msgText;
	document.body.insertBefore(popupObj, document.body.firstChild);
	// check size for shadow background
	var fullSize = getPageSizeWithScroll();
	popupObj.style.width = fullSize[0]+"px";
	popupObj.style.height = fullSize[1]+"px";

	// move frame to the center
	var popupFrame = document.getElementById("popupFrame");
	var frameWidth = popupFrame.offsetWidth;
	var frameHeight = popupFrame.offsetHeight
	var frameLeft = (pageSize[0] - frameWidth)/2;
	var frameTop = (pageSize[1] - frameHeight)/2;
	if (frameLeft < 0) { frameLeft = 0; }
	if (frameTop < 0) { frameTop = 0; }
	// if page was scrolled move popup window to scrolled height
	if (document.body.scrollTop || document.documentElement.scrollTop) {
		frameTop += (document.body.scrollTop || document.documentElement.scrollTop);
	}

	popupFrame.style.left = frameLeft + "px";
	popupFrame.style.top = frameTop + "px";

}

function hidePopupFrame()
{
	var popupObj = document.getElementById("popupArea");
	if (popupObj) {
		var parentObj = popupObj.parentNode;
		parentObj.removeChild(popupObj);
	}
}


function hideMessageBlock()
{
	var messageObj = document.getElementById("messageBlock");
	if (messageObj) {
		var parentObj = messageObj.parentNode;
		parentObj.removeChild(messageObj);
	}
}


function docKeyUp(keyCode)
{
	if (keyCode == 27) {
		var messageObj = document.getElementById("messageBlock");
		var popupObj = document.getElementById("popupArea");
		if (messageObj) {
			var parentObj = messageObj.parentNode;
			parentObj.removeChild(messageObj);
		} else if (popupObj) {
			var parentObj = popupObj.parentNode;
			parentObj.removeChild(popupObj);
		}
		var popupObj = document.getElementById("popupBlock");
		if (popupObj) {
			popupObj.style.display = "none";
		}
		var blackCloudObj = document.getElementById("black_cloud");
		if (blackCloudObj ) {
			hideBlack();
		}
	}
}