var userAgent = navigator.userAgent.toLowerCase();
var isIE = ((userAgent.indexOf("msie") != -1) && (userAgent.indexOf("opera") == -1) && (userAgent.indexOf("webtv") == -1));
var popupImages = new Array();

var indicatorIcon = "images/icons/indicator.gif";
var closeIcon = "images/icons/close.gif";
var siteURL = "";
// zoom global vars
var zoomDisable = ""; 
var disableFlag = 0; 
var zoomOverImage = ""; // save here image name when we use zoom over it
var zoomWidth = "";
var zoomHeight = "";
var zoomMousePos = "";

function openImage(a, width, height)
{
	// when action is linked to element like <a href='/sample.jpg' title='sample' onclick='popupImage(this)'>view me</a>
	var image_href  = a.href;
	var image_title = a.title;
	
	// when action is linked to element like document.getElementById('sample').onmouseover = popupImage;
	if (!image_href) {
		var image_href  = this.href;
		var image_title = this.title;	
	}
	
	if (!image_href)
		return false;
		
	var scrollbars = "no";
	// add margins to image size
	if (width > 0 && height > 0) {
		width += 30; height += 30;
	}
	// check available sizes
	var availableHeight = window.screen.availHeight - 100;
	var availableWidth = window.screen.availWidth - 20;
	if (isNaN(availableHeight)) { availableHeight = 520; } 
	if (isNaN(availableWidth)) { availableWidth = 760; } 
	if (height > availableHeight || !height) { 
		height = availableHeight;
		scrollbars = "yes"; 
	}
	if (width > availableWidth || !width) {
		width = availableWidth;
		scrollbars = "yes";
	}
	var openImageWin = window.open (image_href, 'openImageWin', 'left=0,top=0,toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=' + scrollbars + ',resizable=yes,width=' + width + ',height=' + height);
	openImageWin.focus();
	return false;
}

/*************************** MouseOver Effect ***************************/

function popupImageMouseOver(a, globalURL){
	if (globalURL) {
		siteURL = globalURL;
	} else {
		siteURL = "";
	}
	var image_title = a.title;
	var image_href  = a.href;
	
	if (!image_href) {
		var image_href  = this.href;
		var image_title = this.title;	
	}
	
	if (!image_href)
		return false;
	
	var black_cloud = document.getElementById("black_cloud");
	if (!black_cloud) {
		var black_cloud     = document.createElement('div');			
		black_cloud.id      = "black_cloud";
		black_cloud.onclick = hideBlack;
		black_cloud.style.position = "fixed";
		black_cloud.style.zIndex   = "3500";
		black_cloud.style.top      = "0";
		black_cloud.style.left     = "0";
		black_cloud.style.bottom   = "0";
		black_cloud.style.right    = "0";
		black_cloud.style.opacity    = "0.7";
		black_cloud.style.mozOpacity = "0.7";
		black_cloud.style.filter     = "alpha(opacity=70)";
		black_cloud.style.backgroundColor = "";
		document.body.appendChild(black_cloud);
	} else {
		black_cloud.style.visibility = "visible";	
	}
	hideFlash();	
		
	var black_cloud_inner = document.getElementById("black_cloud_inner");
	if (!black_cloud_inner) {
		var black_cloud_inner            = document.createElement('div');			
		black_cloud_inner.id             = "black_cloud_inner";
		black_cloud_inner.style.position = "absolute";
		black_cloud_inner.style.zIndex   = "4000";
		black_cloud_inner.style.padding  = "10px";
		black_cloud_inner.style.backgroundColor = "white";
		document.body.appendChild(black_cloud_inner);		
	} else {
		black_cloud_inner.style.visibility = "visible";	
	}
	black_cloud_inner.style.border = "none";
	black_cloud_inner.innerHTML = "<img src='" + siteURL + indicatorIcon + "' alt='loading' />";
	centerScreen(black_cloud_inner, 10, 10);	

/******************************************/
	function loadedImageMouseOver(img){
		if (img) {
			var image_href   = img.src;
			var image_title  = img.alt;	
			var image_width  = img.width;
			var image_height = img.height;
		} 
		
		if (!image_href) {
			var image_href   = this.src;
			var image_title  = this.alt;	
			var image_width  = this.width;
			var image_height = this.height;
		}
		
		var black_cloud = document.getElementById("black_cloud");
		var black_cloud_inner = document.getElementById("black_cloud_inner");
		var black_image = document.getElementById("blackImg");
		var black_image_onmouseover = black_image.getAttribute('onmouseover');
		black_cloud_inner.innerHTML  = "";
		findImgPosition();
		black_cloud_inner.style.border = "1px solid #E2E1E1";
		black_image.onmouseout = function() {document.body.removeChild(black_cloud_inner)};
		black_cloud_inner.innerHTML += "<br/><center><img src='" + image_href + "' alt='" + image_title + "' /></center>";	
		black_cloud_inner.innerHTML += "<br/><div align='center'>";
		black_cloud_inner.innerHTML += image_title + "</div>";	
	}
/********************************************/
		
	if (popupImages[image_href]) {
		var img = popupImages[image_href];
		img.id  = "popupImage";
		loadedImageMouseOver(img);		
	} else {
		var img = document.createElement('img');
		img.id  = "popupImage";
		img.alt    = image_title;
		img.onload = loadedImageMouseOver;
		img.src    = image_href;
		popupImages[image_href] = img;
		
	}
	
	return false;
}

/*************************** End MouseOver Effect ***************************/

function popupImage(imgObj, globalURL){
	if (globalURL) {
		siteURL = globalURL;
	} else {
		siteURL = "";
	}
	// when action is linked to element like <a href='/sample.jpg' title='sample' onclick='popupImage(this)'>view me</a>
	// or when action is linked to element like document.getElementById('sample').onmouseover = popupImage;
	var enlargedImage = "";
	if (imgObj.tagName == "IMG") {
		enlargedImage = imgObj.getAttribute("data-image-enlarged");
		if (!enlargedImage) { enlargedImage = imgObj.getAttribute("data-image-enlarge"); }
		if (!enlargedImage) { enlargedImage = imgObj.getAttribute("data-image-click"); }
		if (!enlargedImage) { enlargedImage = imgObj.src; }
	} else {
		enlargedImage  = imgObj ? imgObj.getAttribute("href") : this.href;
	}
	var image_title = imgObj ? imgObj.title : this.title;
	
	if (!enlargedImage || enlargedImage == "#") {
		return false;
	}
	
	var fullSize = getPageSizeWithScroll();

	var black_cloud = document.getElementById("black_cloud");
	if (!black_cloud) {
		var black_cloud     = document.createElement('div');			
		black_cloud.id      = "black_cloud";
		black_cloud.onclick = hideBlack;
		black_cloud.style.position = "fixed";
		black_cloud.style.zIndex   = "3500";
		black_cloud.style.top      = "0";
		black_cloud.style.left     = "0";
		black_cloud.style.bottom   = "0";
		black_cloud.style.right    = "0";
		black_cloud.style.opacity    = "0.7";
		black_cloud.style.mozOpacity = "0.7";
		black_cloud.style.filter     = "alpha(opacity=70)";
		black_cloud.style.backgroundColor = "black";
		document.body.appendChild(black_cloud);
	} else {
		black_cloud.style.visibility = "visible";	
	}
	hideFlash();
		
	var black_cloud_inner = document.getElementById("black_cloud_inner");
	if (!black_cloud_inner) {
		var black_cloud_inner            = document.createElement('div');			
		black_cloud_inner.id             = "black_cloud_inner";
		black_cloud_inner.style.position = "absolute";
		black_cloud_inner.style.zIndex   = "4000";
		black_cloud_inner.style.padding  = "10px";
		black_cloud_inner.style.backgroundColor = "white";
		document.body.appendChild(black_cloud_inner);		
	} else {
		black_cloud_inner.style.visibility = "visible";	
	}
	black_cloud_inner.style.border = "none";
	black_cloud_inner.innerHTML = "<i class=\"spin\"></i>";
	centerScreen(black_cloud_inner, 10, 10);
	
/****************************************/
	function loadedImage(img){
		if (img) {
			var enlargedImage   = img.src;
			var image_title  = img.alt;	
			var image_width  = img.width;
			var image_height = img.height;
		} 
		
		if (!enlargedImage) {
			var enlargedImage   = this.src;
			var image_title  = this.alt;	
			var image_width  = this.width;
			var image_height = this.height;
		}
		
		var black_cloud = document.getElementById("black_cloud");
		black_cloud.style.backgroundColor = "black";
		var black_cloud_inner = document.getElementById("black_cloud_inner");	
		black_cloud_inner.innerHTML  = "";
		centerScreen(black_cloud_inner, image_width, image_height);
		//black_cloud_inner.innerHTML  = "<div align='right'><a href='#' onClick='hideBlack(); return false;'><img style='border:none;' src='" + siteURL + closeIcon +"' alt='close'></a></div>";		
		black_cloud_inner.innerHTML  = "<div align='right'><i class=\"ico-close\" title='close' onclick='hideBlack(); return false;'></i></div>";		
		black_cloud_inner.innerHTML += "<br/><center><img src='" + enlargedImage + "' alt='" + image_title + "' /></center>";	
		black_cloud_inner.innerHTML += "<br/><div align='center'>";
		black_cloud_inner.innerHTML += image_title + "</div>";	
	}

/*******************************************/
	
	if (popupImages[enlargedImage]) {
		var img = popupImages[enlargedImage];
		img.id = "popupImage";
		loadedImage(img);		
	} else {
		var img = document.createElement('img');
		img.id = "popupImage";
		img.alt    = image_title;
		img.onload = loadedImage;
		img.src    = enlargedImage;
		popupImages[enlargedImage] = img;		
	}
	
	return false;
}

function openSuperImage(imgObj)
{
	var scrollbars = "no";

	var imageUrl = imgObj.getAttribute("data-image-enlarged"); 
	if (!imageUrl) { imageUrl = imgObj.getAttribute("data-image-enlarge"); }
	if (!imageUrl) { imageUrl = imgObj.getAttribute("data-image-click"); }
	if (!imageUrl) { imageUrl = imgObj.src; }
	// check available sizes
	var width = 0; var height = 0;
	var availableHeight = window.screen.availHeight - 60;
	var availableWidth = window.screen.availWidth - 20;
	if (isNaN(availableHeight)) { availableHeight = 520; } 
	if (isNaN(availableWidth)) { availableWidth = 760; } 
	if (height > availableHeight || height == 0) { 
		height = availableHeight;
		scrollbars = "yes"; 
	}
	if (width > availableWidth || width == 0) {
		width = availableWidth;
		scrollbars = "yes";
	}
	var superImageWin = window.open (imageUrl, 'superImageWin', 'left=0,top=0,toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=' + scrollbars + ',resizable=yes,width=' + width + ',height=' + height);
	superImageWin.focus();
	return false;
}

function hideBlack(){
	var black_cloud = document.getElementById("black_cloud");
	var black_cloud_inner = document.getElementById("black_cloud_inner");
	showFlash();
	document.body.removeChild(black_cloud);
	document.body.removeChild(black_cloud_inner);
	return false;
}

function centerScreen(popupObj, objWidth, objHeight)
{
	// center absolute image
	var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
	var scrollTop = document.documentElement.scrollTop || document.body.scrollTop; 
	
	var winWidth = window.innerWidth;
	var winHeight = window.innerHeight;
	if (objWidth > winWidth) {
		popupObj.style.left = scrollLeft+"px";
	} else {
		popupObj.style.left = (scrollLeft+((winWidth-objWidth)/2))+"px";
	}
	if (objHeight > winHeight) {
		popupObj.style.top = scrollTop+"px";
	} else {
		popupObj.style.top = (scrollTop+((winHeight-objHeight)/2))+"px";
	}

}

function findImgPosition()	{
	var black_cloud_inner = document.getElementById("black_cloud_inner");
	var el = document.getElementById("blackImg").getElementsByTagName("img")[0];
	var x = findPosX(el);
	var y = findPosY(el);
	
	function findPosX(obj) {
		var curleft = 0;
		if (obj.offsetParent) {
			while (obj.offsetParent) {
				curleft+=obj.offsetLeft;
				if (!obj.offsetParent) {
					break;
				}
				obj=obj.offsetParent;
			}
		} else if (obj.x) {
			curleft+=obj.x;
		}
		black_cloud_inner.style.left = curleft + el.offsetWidth + 50 + "px";
		return curleft;
	}
	
	function findPosY(obj) {
		var curtop = 0;
		if (obj.offsetParent) {
			while (obj.offsetParent) {
				curtop+=obj.offsetTop;
				if (!obj.offsetParent) {
					break;
				}
				obj=obj.offsetParent;
			}
		} else if (obj.y) {
			curtop+=obj.y;
		}
		black_cloud_inner.style.top = curtop + "px";
		return curtop;		
	}
}

function hideFlash() {
	flash = document.getElementsByTagName('object');
	for (var i = 0; i < flash.length; i++) {
		flash[i].style.visibility = 'hidden';
	}
}

function showFlash() {
	flash = document.getElementsByTagName('object');
	for (var i = 0; i < flash.length; i++) { 
		flash[i].style.visibility = 'visible';
	}
}

var rolloverImages = new Array();

function rolloverImage(imageId, imageSrc, imageName, superId, superSrc)
{
	// check first if main image exists
	if (!document.images[imageName]) { return; }
	if (!rolloverImages[imageId]) {
		rolloverImages[imageId] = new Image();
		rolloverImages[imageId].src = imageSrc;
		// set correct loaded image size 
		rolloverImages[imageId].addEventListener("load", function(){
			if (rolloverImages[imageId].height) {
				document.images[imageName].height = rolloverImages[imageId].height;
			}
			if (rolloverImages[imageId].width) {
				document.images[imageName].width = rolloverImages[imageId].width;
			}
	    document.images[imageName].src = rolloverImages[imageId].src;
			document.images[imageName].setAttribute("data-image-enlarged", superSrc);
    }, false);

	} else {
		document.images[imageName].height = rolloverImages[imageId].height;
		document.images[imageName].width = rolloverImages[imageId].width;
		document.images[imageName].src = rolloverImages[imageId].src;
		document.images[imageName].setAttribute("data-image-enlarged", superSrc);
	}

	document.images[imageName].src = rolloverImages[imageId].src;
	if (rolloverImages[imageId].height) {
		document.images[imageName].height = rolloverImages[imageId].height;
	}
	if (rolloverImages[imageId].width) {
		document.images[imageName].width = rolloverImages[imageId].width;
	}
	if (superId) {
		var linkSuperObj = document.getElementById(superId);
		if (linkSuperObj) {
			if (superSrc != "") {
				linkSuperObj.href = superSrc;
				linkSuperObj.style.display = "inline";
			} else {
				linkSuperObj.style.display = "none";
			}
		}
	}
}

function activateZoom(evt, imgObj) {
	// clear any zoom before create new
	disableZoom();
	disableFlag = 0;
	if (typeof imgObj != "object") {
		imgObj = document.images[imgObj];
	}
	zoomOverImage = imgObj;
	var imgWidth = imgObj.offsetWidth;
	var imgHeight = imgObj.offsetHeight;

	zoomWidth = parseInt(imgObj.getAttribute("data-zoom-width"));
	zoomHeight = parseInt(imgObj.getAttribute("data-zoom-height"));
	if (isNaN(zoomWidth) || isNaN(zoomHeight) || zoomWidth <= 0 || zoomHeight <= 0) { 
		zoomWidth = imgWidth; 
		zoomHeight = imgHeight;
	}
	zoomMousePos = getMousePos(evt);
	// automatically disable zoom if user move mouse out of main image before large image loaded
	zoomDisable = setTimeout("disableZoom()", 100);
	// check enlarge image for zoom
	var enlargedImage = imgObj.getAttribute("data-image-enlarged");
	if (!enlargedImage) { enlargedImage = imgObj.getAttribute("data-image-enlarge"); }
	if (!enlargedImage) { return; }
 
	if (popupImages[enlargedImage]) {
		var zoomImgObj = popupImages[enlargedImage];
		zoomImgObj.id = "zoomImg";
		zoomImgObj.style.position = "relative";
		zoomImgObj.style.maxWidth= "none";
		loadZoomImage(zoomImgObj);
	} else {
		var zoomImgObj = document.createElement('img');
		zoomImgObj.id = "zoomImg";
		zoomImgObj.src  = enlargedImage;
		zoomImgObj.style.position = "relative";
		zoomImgObj.style.maxWidth= "none";
		zoomImgObj.onload = function () { loadZoomImage(zoomImgObj) };
		vaSpin(imgObj); 
	}
}


function loadZoomImage(zoomImgObj)
{
	imgObj = zoomOverImage;
	// disable progress bar
	vaStopSpin(imgObj); 
	// save all loaded images into array
	popupImages[zoomImgObj.src] = zoomImgObj;
	// check if blocks wasn't disabled
	if (disableFlag == 1) {
		return;
	}

	imgObj.onmouseout = "";
	var posX = findPosX(imgObj, true) + 10;
	var posY = findPosY(imgObj, false);
	             	
	// check zoom image
	var zoomImgWidth = zoomImgObj.width;
	var zoomImgHeight = zoomImgObj.height;
	// correct zoom window size if it's bigger than image
	if (zoomWidth > zoomImgWidth) { zoomWidth = zoomImgWidth; }
	if (zoomHeight > zoomImgHeight) { zoomHeight = zoomImgHeight; }

	var zoomObj = document.createElement("div");
	zoomObj.id = "popupBlock";
	zoomObj.style.zIndex = "999";
	zoomObj.style.position = "absolute";
	zoomObj.style.left = posX + "px";
	zoomObj.style.top  = posY + "px";
	zoomObj.style.width = zoomWidth + "px";
	zoomObj.style.height = zoomHeight + "px";
	zoomObj.style.border = "1px solid black";
	zoomObj.style.overflow = "hidden";
	document.body.insertBefore(zoomObj, document.body.firstChild);
	// add image for zoom
	zoomObj.appendChild(zoomImgObj);

	var mousePos = zoomMousePos; // last active position
	var mousePosX = mousePos[0];
	var mousePosY = mousePos[1];

	var imgPosX = findPosX(imgObj);
	var imgPosY = findPosY(imgObj);
	var imgWidth = imgObj.offsetWidth;
	var imgHeight = imgObj.offsetHeight;

	var moveDivWidth = (imgWidth * zoomWidth) / zoomImgWidth;
	var moveDivHeight = (imgHeight * zoomHeight) / zoomImgHeight;

	var moveObj = document.createElement("div");
	moveObj.id = "moveBlock";
	moveObj.style.zIndex = "1999";
	moveObj.style.position = "absolute";
	moveObj.style.opacity    = "0.6";
	moveObj.style.mozOpacity = "0.6";
	moveObj.style.filter     = "alpha(opacity=60)";
	moveObj.style.backgroundColor = "#D0D0D0";
	moveObj.style.width = moveDivWidth + "px";
	moveObj.style.height = moveDivHeight + "px";
	moveObj.style.border = "1px solid black";
	moveObj.style.cursor = "move";
	// calculate initial div position
	var posX = mousePos[0] - moveDivWidth / 2;
	var posY = mousePos[1] - moveDivHeight / 2;

	if (posX < imgPosX) { posX = imgPosX; }
	if (posX > (imgPosX + imgWidth - moveDivWidth)) { posX = imgPosX + imgWidth - moveDivWidth; }
	if (posY < imgPosY) { posY = imgPosY; }
	if (posY > (imgPosY + imgHeight - moveDivHeight)) { posY = imgPosY + imgHeight - moveDivHeight; }
	moveObj.style.left = posX + "px";
	moveObj.style.top  = posY + "px";

	moveObj.onmousemove = moveZoom;
	moveObj.onmouseout = disableZoom;

	document.body.insertBefore(moveObj, document.body.firstChild);
}


function disableZoom() 
{
	disableFlag = 1;
	var popupObj = document.getElementById("popupBlock");
	if (popupObj) {
		var imageObj = document.getElementById("zoomImg");
		if (imageObj) {
			popupObj.removeChild(imageObj);
		}
		document.body.removeChild(popupObj);
	}
	var moveObj = document.getElementById("moveBlock");
	if (moveObj) {
		document.body.removeChild(moveObj);
	}
}

function moveZoom(e)
{
	clearTimeout(zoomDisable);
	zoomDisable = "";

	var mousePos = getMousePos(e);
	var mousePosX = mousePos[0];
	var mousePosY = mousePos[1];

	var moveObj = document.getElementById('moveBlock');

	var zoomImg = document.getElementById("zoomImg");
	var popupObj = document.getElementById("popupBlock");

	var imgObj = zoomOverImage;
	var imgPosX = findPosX(imgObj);
	var imgPosY = findPosY(imgObj);
	var imgWidth = imgObj.offsetWidth;
	var imgHeight = imgObj.offsetHeight;
	var popupWidth = popupObj.offsetWidth;
	var popupHeight = popupObj.offsetHeight;

	if (zoomImg && zoomImg.width) {

		var zoomImgWidth = zoomImg.width;
		var zoomImgHeight = zoomImg.height;

		var moveDivWidth = (imgWidth * popupWidth) / zoomImgWidth;
		var moveDivHeight = (imgHeight * popupHeight) / zoomImgHeight;
		var mousePos = getMousePos(e);
		var moveObj = document.getElementById("moveBlock");
		var posX = mousePos[0] - moveDivWidth / 2;
		var posY = mousePos[1] - moveDivHeight / 2;

		if (posX < imgPosX) { posX = imgPosX; }
		if (posX > (imgPosX + imgWidth - moveDivWidth)) { posX = imgPosX + imgWidth - moveDivWidth; }
		if (posY < imgPosY) { posY = imgPosY; }
		if (posY > (imgPosY + imgHeight - moveDivHeight)) { posY = imgPosY + imgHeight - moveDivHeight; }
		moveObj.style.left = posX + "px";
		moveObj.style.top  = posY + "px";

		if (zoomImg) {
			var zoomImgPosX = (posX - imgPosX) * (zoomImgWidth / imgWidth);
			var zoomImgPosY = (posY - imgPosY) * (zoomImgHeight / imgHeight);
			zoomImg.style.left = -(zoomImgPosX) + "px";
			zoomImg.style.top  = -(zoomImgPosY) + "px";
		}
	}

}
	