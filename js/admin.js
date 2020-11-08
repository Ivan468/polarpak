var userAgent = navigator.userAgent.toLowerCase();
var isIE = ((userAgent.indexOf("msie") != -1) && (userAgent.indexOf("opera") == -1) && (userAgent.indexOf("webtv") == -1));
var tid = new Array();

function getScrollBarWidth () 
{
	var inner = document.createElement('p');   
	inner.style.width = "100%";   
	inner.style.height = "200px";   
 
	var outer = document.createElement('div');   
	outer.style.position = "absolute";   
	outer.style.top = "0px";   
	outer.style.left = "0px";   
	outer.style.visibility = "hidden";   
	outer.style.width = "200px";   
	outer.style.height = "150px";   
	outer.style.overflow = "hidden";   
	outer.appendChild (inner);   
  
	document.body.appendChild (outer);   
	var w1 = inner.offsetWidth;   
	outer.style.overflow = 'scroll';   
	var w2 = inner.offsetWidth;   
	if (w1 == w2) w2 = outer.clientWidth;   
  
	document.body.removeChild (outer);   
  
	return (w1 - w2);   
};  

function reloadSite(formObj)
{
	formObj.operation.value = "";
	formObj.submit();
}

function showHint(parentObj, hintId){
	var hintObj = document.getElementById(hintId);
	var popupObj = document.createElement("div");
	var xPos = findPosX(parentObj, true) + 2;
	var yPos = findPosY(parentObj);
	popupObj.id        = hintId + "Popup";;
	popupObj.className = "hintPopup";
	popupObj.style.left = xPos + "px";
	popupObj.style.top = yPos + "px";
	popupObj.style.display  = 'block';
	popupObj.innerHTML = hintObj.innerHTML;
	
	document.body.insertBefore(popupObj, document.body.firstChild);
}

function hideHint(hintId)
{
	var popupObj = document.getElementById(hintId+"Popup");
	if (popupObj) {
		document.body.removeChild(popupObj);
	}
}

function show(parentName, subName)
{
	var parentMenu = document.getElementById(parentName);
	var subMenu = document.getElementById(subName);

	// hide all other sub menus
	var menu = new Array("shop_sub", "orders_sub", "cms_sub", "articles_sub", "helpdesk_sub", "helpdesk2_sub", "forum_sub", "manual_sub", "ads_sub", "registrations_sub", "users_sub", "system_sub");
	for (var i = 0; i < menu.length; i++) {
		if (subName != menu[i]) {
			var menuObj = document.getElementById(menu[i]);
			if (menuObj && menuObj.style.display == "block") {
				menuObj.style.display='none';
				if (tid[menu[i]]) {
					clearTimeout(tid[menu[i]]);
					tid[menu[i]] = "";
				}
			}
		}
	}


	if (subMenu) {
		if (parentName == 'book') {
			subMenu.style.top = findPosY(parentMenu, true) + "px";
		} else {
			subMenu.style.top = findPosY(parentMenu, true) + "px";
		}
		if (parentName == 'book') {
			subMenu.style.left = findPosX(parentMenu, false) + "px";
		} else {
			subMenu.style.left = findPosX(parentMenu, false) + "px";
		}
		subMenu.style.display='block';
		if (tid[subName]) {
			clearTimeout(tid[subName]);
			tid[subName] = "";
		}
	}
}

function hide(subName){
	tid[subName] = setTimeout("hideMenu('" + subName + "')", 500);
}

function hideMenu(subName){
	var subMenu = document.getElementById(subName);
	if (subMenu) {
		subMenu.style.display = 'none';
	}
}

function optionsWindow(pagename){
	var optionsWindow = window.open (pagename, 'optionsWindow', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
	optionsWindow.focus();
}

function openHelpWindow(pagename){
	var popupWin = window.open (pagename, 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
	popupWin.focus();
}

function openEditWindow(pagename)
{
	var editWin = window.open (pagename, 'editWin', 'toolbar=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=600,height=500');
	editWin.focus();
}

function addParam2URL(param, value, url)
{
	if (url.indexOf("?") == -1) {
		url = url + "?" +param + "=" + value;
	} else {
		url = url + "&" + param + "=" + value;
	}

	return url;
}

function mouseX(evt) {
	if (evt.pageX) {
		return evt.pageX;
	} else if (evt.clientX) {
		return evt.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	} else {
		return null;
	}
}

function mouseY(evt) {
	if (evt.pageY) {
		return evt.pageY;
	} else if (evt.clientY) {
		return evt.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	} else {
		return null;
	}
}

function showNotes(event, bookmarkID, bookmarkNotes)	{
	var bookNote = document.getElementById("notes_" + bookmarkID);
	var len = bookmarkNotes.length;
	if (bookNote && len!=0) {
	var leftPos = mouseX(event) - 380;
   	var topPos  = mouseY(event) - 100;
		bookNote.style.left = leftPos;
		bookNote.style.top = topPos;
		bookNote.style.display = "block";
	}
}

function hideNotes(event, bookmarkID)
{
	var bookNote = document.getElementById("notes_" + bookmarkID);
	if (bookNote) {
		bookNote.style.display = 'none';
	}
}

function changeTab(newTabName, formName)
{
	var formObj = "";
	if (formName) {
		formObj = document.forms[formName];
	} else {
		formObj = document.record;
	}
	var currentTabName = formObj.tab.value;
	if (currentTabName != newTabName) {
		currentTab = document.getElementById("tab_" + currentTabName);
		newTab = document.getElementById("tab_" + newTabName);

		currentData = document.getElementById("data_" + currentTabName);
		newData = document.getElementById("data_" + newTabName);

		currentTab.className = "adminTab";
		newTab.className = "adminTabActive";

		if (currentData) {
			currentData.style.display = "none";
		}
		if (newData) {
			newData.style.display = "block";
		}

		formObj.tab.value = newTabName;

		// check if we need change the rows
		var rowObj = newTab.parentNode;
		if (rowObj && rowObj.id && rowObj.id.substring(0, 7) == "tab_row") {
			var tabs = "";
			var activeRowId = rowObj.id;
			var rowId = 1;
			while ((rowObj = document.getElementById("tab_row_" + rowId))) {
				if (rowObj.id == activeRowId) {
					tabs += "<div id='"+rowObj.id+"' class='tabRow'>" + rowObj.innerHTML + "</div>";
				} else {
					tabs = "<div id='"+rowObj.id+"' class='tabRow'>" + rowObj.innerHTML + "</div>" + tabs;
				}
				rowId++;
			}
			var tabsObj = document.getElementById("tabs");
			if (tabsObj && tabs != "") {
				tabsObj.innerHTML = tabs;
			}
		}
	}
}

function overhid(cat) {
	var nameCat = document.getElementById(cat);
	if (nameCat.className != "leftNavActive") {
		nameCat.className = "leftNavActive";
	}
	else {
		nameCat.className = "leftNavNonActive";
	}
}

function settingsFrame(objId)
{
	var frameObj = document.getElementById(objId);
	if (frameObj) {
		if (frameObj.className == "settings-frame") {
			frameObj.className="settings-frame show";
		} else {
			frameObj.className="settings-frame";
		}
	}

}

/*using namespace BLZ*/
	var BLZ = BLZ || {};
	
	BLZ.utils = {
		addListener: null,
		removeListener: null
	};

	BLZ.utils.loader = function() {
		if (typeof window.addEventListener === 'function') {
			BLZ.utils.addListener = function (elem, ev, fn) {
				elem.addEventListener(ev, fn, false);
			};
			BLZ.utils.removeListener = function (elem, ev, fn) {
				elem.removeEventListener(ev, fn, false);
			};			
		}
		else if (typeof document.attachEvent === 'object') {
			BLZ.utils.addListener = function (elem, ev, fn) {
				elem.attachEvent('on' + ev, fn);
			};
			BLZ.utils.removeListener = function (elem, ev, fn) {
				elem.detachEvent('on' + ev, fn);
			};
		}
		else {
			BLZ.utils.addListener = function (elem, ev, fn) {
				elem['on' + ev] = fn;
			};
			BLZ.utils.removeListener = function (elem, ev, fn) {
				elem['on' + ev] = null;
			};
		}
	}();

function vaSetIpClass(updatedClass, operation)
{
	var ipObj = document.querySelector(".ip-data .ip-address");
	var editObj = document.querySelector(".ip-data .ip-edit");
	if (ipObj) {
		// remove highlighted ip classes first
		var className = ipObj.className.replace(/blocked-ip|block-ip|warn-ip|warning-ip|black-ip|blacklist-ip/gi, "").trim();
		if (operation == "remove" || operation == "delete") {
			if (editObj && editObj.hasAttribute("data-add-text")) {
				editObj.innerHTML = editObj.getAttribute("data-add-text");
			}
		} else {
			className += " "+updatedClass;
			if (editObj && editObj.hasAttribute("data-edit-text")) {
				editObj.innerHTML = editObj.getAttribute("data-edit-text");
			}
		}
		ipObj.className = className;
	}
}