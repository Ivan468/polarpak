function activateEditors(object_name)
{
	for (var i = 0; i < object_name.length; i++) {
		activateEditor(object_name[i]);
	}
}

function activateEditor(object_name)
{
	try{
		var execsubmit=eval(object_name);
		if (execsubmit){
			var content_name=execsubmit.name;
			if (execsubmit.isGecko) {
				var edit = document.getElementById(content_name).contentWindow.document;
				edit.designMode = "on"; 
			}
		}
	}
	catch (e) {
	}
}

function submit_editor(object_name)
{
	for (var i = 0; i < object_name.length; i++) {
		try{
			var execsubmit=eval(object_name[i]);
			if (execsubmit){
				execsubmit.post_data();
			}
		}
		catch (e) {
		}
	}
}

function clear_editor(object_name)
{
	for (var i = 0; i < object_name.length; i++) {
		try{
			var execsubmit=eval(object_name[i]);
			if (execsubmit){
				execsubmit.clear();
			}
		}
		catch (e) {
		}
	}
}


function VA_HTMLeditor(name, site_url, html_editor_type)
{
	this.name = name;
	this.width = '600';
	this.height = '300';
	this.htmlValue = '';
	this.isEditable = false;
	this.isIE = false;
	this.isGecko = false;
	this.isSafari = false;
	this.isKonqueror = false;
	this.isChrome = false;
	this.isTextMode = false;
	this.object_name = '';
	this.site_url = site_url;
	this.path_js = site_url+'js/';
	this.path_images = site_url+'images/WYSIWYG/';
	this.editor_type = '';
	this.text_field_name = '';
	this.formname = '';
	this.css_file = '';

	this.linkHref = '';
	this.linkAlt = '';
	this.linkText = '';
	this.linkTarget = '';

	this.tableRows = '';
	this.tableColumns = '';
	this.tableWidth = '';
	this.tableHeight = '';
	this.tableBorder = '';
	this.tableSpacing = '';
	this.tablePadding = '';
	this.tableColor = '';
	this.tableBGColor = '';
	this.tableAlign = '';

	this.imageUrl = '';
	this.imageAlt = '';
	this.imageWidth = '';
	this.imageHeight = '';
	this.imageBorder = '';
	this.imageVSpase = '';
	this.imageHSpase = '';
	this.imageAlign = '';
	this.imageLinkUrl = '';
	this.imagelinkTarget = '';

	this.tid = new Array();
	this.lastMenu = new Array();


	var browser = navigator.userAgent.toLowerCase();
	this.isIE = ((browser.indexOf("msie") != -1) && (browser.indexOf("opera") == -1) && (browser.indexOf("webtv") == -1));
	this.isGecko = (browser.indexOf("gecko") != -1);
	this.isSafari = (browser.indexOf("safari") != -1);
	this.isKonqueror = (browser.indexOf("konqueror") != -1);
	this.isOpera = (browser.indexOf("opera") != -1);
	this.isChrome = (browser.indexOf("chrome") !=-1);

	if (document && document.getElementById && document.designMode &&  (html_editor_type!='0'))
	{
		if (this.isIE) {
			var pos = browser.indexOf("msie");
			var version = parseFloat(browser.substring(pos + 5, pos + 8));
			this.isEditable = (version >= 5.5);

			this.editorCommand = editorCommand_msie;
			this.setMode = setMode_msie;
			this.addLink = addLink_msie;
			this.addTable = addTable_msie;
			this.addImage = addImage_msie;
			this.opendialog = opendialog_msie;
			this.designer = designer_msie;
			if (this.isEditable) {
				this.post_data = html_to_hidden_msie;
			} else {
				this.post_data = text_to_hidden;
			}
		} else if (this.isGecko || this.isChrome || this.isOpera || this.isSafari) {
			this.isEditable = true;
			this.editorCommand = editorCommand_gecko;
			this.setMode = setMode_gecko;
			this.addLink = addLink_gecko;
			this.addTable = addTable_gecko;
			this.addImage = addImage_gecko;
			this.opendialog = opendialog_gecko;
			this.designer = designer_gecko;
			this.insert_into_position_cursor = insert_into_position_cursor_gecko;
			this.add_node = add_node_gecko;
			if (this.isEditable) {
				this.post_data = html_to_hidden_gecko;
			} else {
				this.post_data = text_to_hidden;
			}
		} else {
			this.isEditable = false;
			this.post_data = text_to_hidden;
		}
	}
	else
	{
		this.isEditable = false;
		this.post_data = text_to_hidden;
	}

	this.show_layer = show_layer;
	this.displayEditor = displayEditor;
	this.createLink = createLink;
	this.createTable = createTable;
	this.createImage = createImage;
	this.hide_layer = hide_layer;
	this.randomNumber = randomNumber;
	this.clear = clear;
	this.updateIframeActions = updateIframeActions;
}

function VA_Button(src, alt, title, onclick){
	this.src = src;
	this.alt = alt;
	this.title = title;
	this.onclick = onclick;
	this.prin_button = '<span class="editorMenuBlockA"><a class="ico" href="#" onclick="'+this.onclick+';return false;"><img class="ico" src="'+this.src+'" alt="'+this.alt+'" title="'+this.title+'" align="absmiddle"></a></span>';
}

function VA_ButtonColor(src, alt, title, div_id, im_back, object_name, options){
	this.src = src;
	this.alt = alt;
	this.title = title;
	if (options == 'forecolor') {
		var commandstr = object_name+'.editorCommand(\'forecolor\'';
	}
	if (options == 'backcolor') {
		var commandstr = object_name+'.editorCommand(\'backcolor\'';
	}
	this.prin_button = '<span class="editorMenuBlockA"><a class="ico" href="#" onclick="return false;"><img id="m_'+div_id+'" class="ico" src="'+this.src+'" alt="'+this.alt+'" title="'+this.title+'" onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" align="absmiddle"></a></span> ';
	this.prin_button += '<div id="sm_'+div_id+'" class="editorMenuBlock" onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');">';
	this.prin_button += '<table style="background-color: white;" border="0" cellspacing="1" cellpadding="0"><tr><td>';
	this.prin_button += '<table style="background-color: white;" border="0" cellspacing="0" cellpadding="0"><tr>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: black; background-color: black;" src="'+im_back+'" onclick="'+commandstr+',\'black\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: maroon; background-color: maroon;" src="'+im_back+'" onclick="'+commandstr+',\'maroon\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: green; background-color: green;" src="'+im_back+'" onclick="'+commandstr+',\'green\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: olive; background-color: olive;" src="'+im_back+'" onclick="'+commandstr+',\'olive\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: navy; background-color: navy;" src="'+im_back+'" onclick="'+commandstr+',\'navy\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: purple; background-color: purple;" src="'+im_back+'" onclick="'+commandstr+',\'purple\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: teal; background-color: teal;" src="'+im_back+'" onclick="'+commandstr+',\'teal\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: gray; background-color: gray;" src="'+im_back+'" onclick="'+commandstr+',\'gray\');"></a></td>';
	this.prin_button += '</tr><tr>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: silver; background-color: silver;" src="'+im_back+'" onclick="'+commandstr+',\'silver\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: red; background-color: red;" src="'+im_back+'" onclick="'+commandstr+',\'red\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: lime; background-color: lime;" src="'+im_back+'" onclick="'+commandstr+',\'lime\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: yellow; background-color: yellow;" src="'+im_back+'" onclick="'+commandstr+',\'yellow\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: blue; background-color: blue;" src="'+im_back+'" onclick="'+commandstr+',\'blue\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: fuchsia; background-color: fuchsia;" src="'+im_back+'" onclick="'+commandstr+',\'fuchsia\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: aqua; background-color: aqua;" src="'+im_back+'" onclick="'+commandstr+',\'aqua\');"></a></td>';
	this.prin_button += '<td><a class="ico_color" href="#" onclick="return false;"><img class="ico_color" style="color: white; background-color: white;" src="'+im_back+'" onclick="'+commandstr+',\'white\');"></a></td>';
	this.prin_button += '</tr></table>';
	this.prin_button += '</td></tr></table>';
	this.prin_button += '</div>';
}

function VA_ButtonCombo(src, alt, title, div_id, object_name, options){
	if (options == 'fontname') {
		var commandstr = object_name+'.editorCommand(\'fontname\'';
		this.prin_button = '<div class="TextDiv"><span class="TextBlock">&nbsp;Font&nbsp;</span><img id="m_'+div_id+'" class="ico" src="'+src+'" alt="'+alt+'" title="'+title+'" onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" align="absmiddle">';
		this.prin_button += '<div id="sm_'+div_id+'" class="editorMenuBlock">';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'arial\');" class="ComboMenu"><FONT face=arial>Arial</FONT></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'Comic Sans MS\');" class="ComboMenu"><FONT face=\'Comic Sans MS\'>Comic Sans MS</FONT></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'Courier New\');" class="ComboMenu"><FONT face=\'Courier New\'>Courier New</FONT></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'System\');" class="ComboMenu"><FONT face=\'System\'>System</FONT></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'Tahoma\');" class="ComboMenu"><FONT face=\'Tahoma\'>Tahoma</FONT></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'Times New Roman\');" class="ComboMenu"><FONT face=\'Times New Roman\'>Times New Roman</FONT></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'Verdana\');" class="ComboMenu"><FONT face=\'Verdana\'>Verdana</FONT></a>';
		this.prin_button += '</div></div>';
	}
	if (options == 'fontsize') {
		var commandstr = object_name+'.editorCommand(\'fontsize\'';
		this.prin_button = '<div class="TextDiv"><span class="TextBlock">&nbsp;Size&nbsp;</span><img id="m_'+div_id+'" class="ico" src="'+src+'" alt="'+alt+'" title="'+title+'" onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" align="absmiddle">';
		this.prin_button += '<div id="sm_'+div_id+'" class="editorMenuBlock">';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'1\');" class="ComboMenu"><CENTER>1</CENTER></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'2\');" class="ComboMenu"><CENTER>2</CENTER></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'3\');" class="ComboMenu"><CENTER>3</CENTER></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'4\');" class="ComboMenu"><CENTER>4</CENTER></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'5\');" class="ComboMenu"><CENTER>5</CENTER></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'6\');" class="ComboMenu"><CENTER>6</CENTER></a>';
		this.prin_button += '<a onmouseover="'+object_name+'.show_layer(\''+div_id+'\');" onmouseout="'+object_name+'.hide_layer(\''+div_id+'\');" href="javascript:'+commandstr+',\'7\');" class="ComboMenu"><CENTER>7</CENTER></a>';
		this.prin_button += '</div></div>';
	}
}

function show_layer(menuName) 
{
	var actMenu = new Array();
	var addWidth = false; var addHeight = true;
	var parentMenu = document.getElementById("m_" + menuName);
	var subMenu = document.getElementById("sm_" + menuName);

	if (subMenu) {
		actMenu["sm_" + menuName] = 1;
		subMenu.style.top = findPosY(parentMenu, addHeight) + "px";
		subMenu.style.left = findPosX(parentMenu, addWidth) + "px";
		subMenu.style.display='block';
		if (this.tid[menuName]) {
			clearTimeout(this.tid[menuName]);
			this.tid[menuName] = "";
		}
	}

	for (menuName in this.lastMenu) {
		if (!actMenu[menuName]) {
			var menuObj = document.getElementById(menuName);
				menuObj.style.display='none';
			if (menuObj && menuObj.style.display == "block") {
			}
		}
	}
	this.lastMenu = actMenu;

}

function hide_layer(menuName)
{
	this.tid[menuName] = setTimeout('hide_Menu(\'' + menuName + '\')', 300);
}

function hide_Menu(menuName)
{
	var subMenu = document.getElementById("sm_" + menuName);
	if (subMenu) {
		subMenu.style.display='none';
	}
}

function findPosX(obj, addWidth)
{
	var curleft = 0;
	if (addWidth) {
		curleft += obj.offsetWidth;
	}
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
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
	} else if (obj.y) {
		curtop += obj.y;
	}
	return curtop;
}

function html_to_hidden_msie(){
	var mainField = document.getElementById(this.name).contentWindow;
	var cont;

	if (!this.isTextMode) {
		cont=mainField.document.body.innerHTML;
		document.forms[this.formname].elements[this.text_field_name].value = msie_unblock(cont);
	} else {
		cont=mainField.document.body.innerText;
		document.forms[this.formname].elements[this.text_field_name].value = msie_unblock(cont);
	}
}

function html_to_hidden_gecko(){
	var mainField = document.getElementById(this.name).contentWindow;
	if (!this.isTextMode) {
		this.setMode();
	}
	var html = mainField.document.body.ownerDocument.createRange();
	html.selectNodeContents(mainField.document.body);
	// always remove <br> from the end before save
	html = html.toString().replace(/<br>\s*$/gi, "");
	document.forms[this.formname].elements[this.text_field_name].value = html;
}

function text_to_hidden(){
	document.forms[this.formname].elements[this.text_field_name].value = document.forms[this.formname].elements[this.name].value;
}

function displayEditor(object_name, width, height)
{
	this.object_name = object_name;
	this.htmlValue = document.forms[this.formname].elements[this.text_field_name].value;
	width = parseInt(width);
	height = parseInt(height);
	if (width != 0 && !isNaN(width)) this.width = width;
	if (height != 0 && !isNaN(height)) this.height = height;
	if(this.isEditable)
	{
		buttons = new Array();
		buttons[0] = new VA_Button(this.path_images+'source.gif', 'Source', 'Source', object_name+'.setMode()');
		buttons[1] = new VA_Button(this.path_images+'cut.gif', 'Cut', 'Cut (Ctrl+X)', object_name+'.editorCommand(\'cut\')');
		buttons[2] = new VA_Button(this.path_images+'copy.gif', 'Copy', 'Copy (Ctrl+C)', object_name+'.editorCommand(\'copy\')');
		buttons[3] = new VA_Button(this.path_images+'paste.gif', 'Paste', 'Paste (Ctrl+V)', object_name+'.editorCommand(\'paste\')');
		buttons[4] = new VA_Button(this.path_images+'bold.gif', 'Bold', 'Bold (Ctrl+B)', object_name+'.editorCommand(\'Bold\')');
		buttons[5] = new VA_Button(this.path_images+'underline.gif', 'Underline', 'Underline (Ctrl+U)', object_name+'.editorCommand(\'underline\')');
		buttons[6] = new VA_Button(this.path_images+'italic.gif', 'Italic', 'Italic (Ctrl+I)', object_name+'.editorCommand(\'italic\')');
		buttons[7] = new VA_Button(this.path_images+'aleft.gif', 'Align Left', 'Align Left', object_name+'.editorCommand(\'justifyleft\')');
		buttons[8] = new VA_Button(this.path_images+'acenter.gif', 'Align Center', 'Align Center', object_name+'.editorCommand(\'justifycenter\')');
		buttons[9] = new VA_Button(this.path_images+'aright.gif', 'Align Right', 'Align Right', object_name+'.editorCommand(\'justifyright\')');
		buttons[10] = new VA_Button(this.path_images+'ajustify.gif', 'Align Justify', 'Align Justify', object_name+'.editorCommand(\'justifyfull\')');
		buttons[11] = new VA_Button(this.path_images+'nlist.gif', 'Numbered List', 'Numbered List', object_name+'.editorCommand(\'insertorderedlist\')');
		buttons[12] = new VA_Button(this.path_images+'blist.gif', 'Bulleted List', 'Bulleted List', object_name+'.editorCommand(\'insertunorderedlist\')');
		buttons[13] = new VA_Button(this.path_images+'indent.gif', 'Decrease Indent', 'Decrease Indent', object_name+'.editorCommand(\'outdent\')');
		buttons[14] = new VA_Button(this.path_images+'outdent.gif', 'Increase Indent', 'Increase Indent', object_name+'.editorCommand(\'indent\')');
		buttons[15] = new VA_Button(this.path_images+'undo.gif', 'Undo', 'Undo', object_name+'.editorCommand(\'undo\')');
		buttons[16] = new VA_Button(this.path_images+'redo.gif', 'Redo', 'Redo', object_name+'.editorCommand(\'redo\')');
		buttons[17] = new VA_Button(this.path_images+'wlink.gif', 'Add link', 'Add link', object_name+'.createLink()');
		buttons[18] = new VA_Button(this.path_images+'table.gif', 'Add table', 'Add table', object_name+'.createTable()');
		buttons[19] = new VA_Button(this.path_images+'image.gif', 'Add image', 'Add image', object_name+'.createImage()');
		buttons[20] = new VA_ButtonColor(this.path_images+'forecolor.gif', 'Color', 'Color', object_name+'cl', this.path_images+'empty.gif', object_name, 'forecolor')
		buttons[21] = new VA_ButtonColor(this.path_images+'backcolor.gif', 'BackGround Color', 'BackGround Color', object_name+'bcl', this.path_images+'empty.gif', object_name, 'backcolor')
		buttons[22] = new VA_ButtonCombo(this.path_images+'combo.gif', 'Font Name', 'Font Name', object_name+'f', object_name, 'fontname');
		buttons[23] = new VA_ButtonCombo(this.path_images+'combo.gif', 'Size', 'Size', object_name+'s', object_name, 'fontsize');

		document.writeln('<table cellspacing="0" cellpadding="1" border="0" width="'+this.width+'">');
		document.writeln('<tr><td valign="MIDDLE">');
		var count_width=0;
		for (var i = 0; i < 24; i++){
			count_width=count_width+30;
			document.writeln(buttons[i].prin_button);
		}
		document.writeln('</td></tr>');
		document.writeln('</table>');


		document.writeln('<iframe id="' + this.name + '" width="' + this.width + '" height="' + this.height + '"></iframe>');
		document.forms[this.formname].elements[this.text_field_name].style.display = "none";
		this.designer();
		// added to keep html templates without changes
		updateIframeActions(this.editor_type);
	}
}

function designer_msie(){
	if (!document.getElementById(this.name).contentWindow && !document.getElementById(this.name).contentWindow.document)
	{
		alert('No document.getElementById(\'' + this.name + '\').contentWindow');
		this.isEditable = false;
		this.displayEditor('','', '');
	}

	var mainContent= '<head><base href="'+site_url+'"><link rel="stylesheet" href="'+this.css_file+'" type="text/css"></head><body class="editor">' + this.htmlValue + '</body>';
	var edit = document.getElementById(this.name).contentWindow.document;
	edit.open();
	//edit.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	edit.write(mainContent);
	edit.close();
	edit.designMode =  "On";
}

function designer_gecko(){
	if (!document.getElementById(this.name).contentWindow && !document.getElementById(this.name).contentWindow.document)
	{
		alert('No document.getElementById(\'' + this.name + '\').contentWindow');
		this.isEditable = false;
		this.displayEditor('','', '');
	}
	var mainContent= '<head><base href="'+site_url+'"></head><body class="editor">' + this.htmlValue + '</body>';
	var edit = document.getElementById(this.name).contentWindow.document;
	edit.open();
	//edit.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	edit.write('<!DOCTYPE html>');
	edit.write(mainContent);
	edit.close();
	var style = document.createElement('link');
	style.rel = 'stylesheet';
	style.type = 'text/css';
	style.href = this.css_file;
	if(this.css_file != ''){
		edit.getElementsByTagName('head')[0].appendChild(style);
	}
	try
	{
		edit.designMode =  "On";
	}
	catch (e) {	}
}

function editorCommand_msie(command, option){
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;
	try
	{
		mainField.focus();
		if (option == null) mainField.document.execCommand(command);
		else mainField.document.execCommand(command, false, option);
		mainField.focus();
	}
	catch (e) {
	}
}

function editorCommand_gecko(command, option)
{
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;
	if (this.isGecko && command == 'backcolor') {command = 'hilitecolor';}
	try{
		mainField.focus();
		if (option == null) mainField.document.execCommand(command, false, null);
		else mainField.document.execCommand(command, false, option);
		mainField.focus();
	} catch (e) {}
}

function msie_block(t) {
	return t.replace(/href=["']([^"']*)["']/g, 'href="viart://viart.com/$1"');
}

function msie_unblock(t) {
	return t.replace(/href=["']viart:\/\/viart\.com\/([^"']*)["']/g, 'href="$1"');
}
	
function setMode_msie() {
	var mainField = document.getElementById(this.name).contentWindow;
	var cont;

	if (!this.isTextMode) {
		cont=mainField.document.body.innerHTML;
		mainField.document.body.innerText=msie_unblock(cont);
		this.isTextMode=true;
	} else {
		cont=mainField.document.body.innerText;
		mainField.document.body.innerHTML = msie_block(cont);
		this.isTextMode=false;
	}

	mainField.focus();
}

function setMode_gecko() {
	var mainField = document.getElementById(this.name).contentWindow;
	if (!this.isTextMode) {
		html = document.createTextNode(mainField.document.body.innerHTML);
		mainField.document.body.innerHTML = "";
		html = mainField.document.importNode(html,false);
		mainField.document.body.appendChild(html);
		this.isTextMode=true;
	} else {
		html = mainField.document.body.ownerDocument.createRange();
		html.selectNodeContents(mainField.document.body);
		mainField.document.body.innerHTML = html.toString();
		this.isTextMode=false;
	}
	//mainField.focus();
}

function createLink() {
	if (this.isTextMode) {return;}
	this.opendialog(this.path_js+'dialogs/insert_link.html', 400, 300);
}

function addLink_msie() {
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;
	var Alt = '';
	var target = '';
	mainField.focus();

	if ((this.linkHref != null) && (this.linkHref != "http://")) {
		if (this.linkAlt != '') Alt = "ALT=\""+this.linkAlt+"\"";
		if (this.linkTarget != '') target = " target=\""+this.linkTarget+"\"";
		if (mainField.document.selection.type=="None" || mainField.document.selection.type=="Text") {
			if ((this.linkText == null) || (this.linkText == '')) {
				if(mainField.document.selection.createRange().text != ''){
					this.linkText = mainField.document.selection.createRange().text;
				}else{
					this.linkText = this.linkHref;
				}
			}
			var nowDate=new Date();
			var id=this.randomNumber(0,100)+'_'+nowDate+'_'+this.randomNumber(0,100);
			var sel=mainField.document.selection.createRange();
			sel.pasteHTML("<A ID=\""+id+"\" HREF=\""+"./"+this.linkHref+"\""+Alt+target+">"+this.linkText+"</A>")
			sel.select();
			mainField.document.getElementById(id).setAttribute("href", this.linkHref);
			mainField.document.getElementById(id).removeAttribute("id");
		}else this.editorCommand("CreateLink",this.linkHref);
	}else mainField.focus();
}

function addLink_gecko() {
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;
	var Alt = '';
	var target = '';
	mainField.focus();

	if ((this.linkHref != null) && (this.linkHref != "http://")) {
		if ((this.linkText == null) || (this.linkText == '')) this.linkText = this.linkHref;
		if (this.linkAlt != '') Alt = "ALT=\""+this.linkAlt+"\"";
		if (this.linkTarget != '') target = " target=\""+this.linkTarget+"\"";
		cont = "<A HREF=\""+this.linkHref+"\""+Alt+target+">"+this.linkText+"</A>";
		this.insert_into_position_cursor(cont);
	}else mainField.focus();
}

function createTable() {
	if (this.isTextMode) {return;}
	this.opendialog(this.path_js+'dialogs/insert_table.html', 400, 200);
}

function addTable_msie() {
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;
	if ((this.tableRows != 0) && (this.tableRows != "") && (this.tableColumns != 0) && (this.tableColumns != "")) {
		mytable     = document.createElement("table");
		mytablebody = document.createElement("tbody");
		for(var j = 0; j < this.tableRows; j++) {
			mycurrent_row = document.createElement("tr");
			for(var i = 0; i < this.tableColumns; i++) {
				mycurrent_cell = document.createElement("td");
				mycurrent_row.appendChild(mycurrent_cell);
			}
			mytablebody.appendChild(mycurrent_row);
		}
		mytable.appendChild(mytablebody);
		if (this.tableWidth != '') mytable.setAttribute("width", this.tableWidth);
		if (this.tableHeight != '') mytable.setAttribute("height", this.tableHeight);
		if (this.tableBorder != '') mytable.setAttribute("border", this.tableBorder);
		if (this.tableSpacing != '') mytable.setAttribute("cellSpacing", this.tableSpacing);
		if (this.tablePadding != '') mytable.setAttribute("cellPadding", this.tablePadding);
		if (this.tableColor != '') mytable.setAttribute("bordercolor", this.tableColor);
		if (this.tableBGColor != '') mytable.setAttribute("bgcolor", this.tableBGColor);
		if (this.tableAlign != '') mytable.setAttribute("align", this.tableAlign);
		cont=mytable.outerHTML;
		mainField.focus();
		if (mainField.document.selection.type=="None") {
			var sel=mainField.document.selection.createRange();
			sel.pasteHTML(cont);
			sel.select();
		};
		mainField.focus();
	}else mainField.focus();
}

function addTable_gecko() {
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;
	if ((this.tableRows != 0) && (this.tableRows != "") && (this.tableColumns != 0) && (this.tableColumns != "")) {
		body = mainField.document.createElement("body");
		mytable = mainField.document.createElement("table");
		if (this.tableWidth != '') mytable.setAttribute("width", this.tableWidth);
		if (this.tableHeight != '') mytable.setAttribute("height", this.tableHeight);
		if (this.tableBorder != '') mytable.setAttribute("border", this.tableBorder);
		if (this.tableSpacing != '') mytable.setAttribute("cellSpacing", this.tableSpacing);
		if (this.tablePadding != '') mytable.setAttribute("cellPadding", this.tablePadding);
		if (this.tableColor != '') mytable.setAttribute("bordercolor", this.tableColor);
		if (this.tableBGColor != '') mytable.setAttribute("bgcolor", this.tableBGColor);
		if (this.tableAlign != '') mytable.setAttribute("align", this.tableAlign);
		mytablebody = mainField.document.createElement("tbody");
		for(var j = 0; j < this.tableRows; j++) {
			mycurrent_row = mainField.document.createElement("tr");
			for(var i = 0; i < this.tableColumns; i++) {
				mycurrent_cell = mainField.document.createElement("td");
				br = mainField.document.createElement("br");
				mycurrent_cell.appendChild(br);
				mycurrent_row.appendChild(mycurrent_cell);
			}
			mytablebody.appendChild(mycurrent_row);
		}
		mytable.appendChild(mytablebody);
		body.appendChild(mytable);
		cont = body.innerHTML;
		textInsert = body.innerHTML;
		this.insert_into_position_cursor(cont);
	}else mainField.focus();
}

function createImage() {
	if (this.isTextMode) {return;}
	this.opendialog(this.path_js+'dialogs/insert_image.html', 450, 400);
}

function addImage_msie() {
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;

	if ((this.imageUrl != 0) && (this.imageUrl != '')) {
		myimage     = document.createElement("img");
		myimage.src = this.imageUrl;
		myimage.alt = this.imageAlt;
		myimage.width = (!isNaN(this.imageWidth) && this.imageWidth != '') ? parseInt(this.imageWidth) : 100;
		myimage.height = (!isNaN(this.imageHeight) && this.imageHeight != '') ? parseInt(this.imageHeight) : 100;
		myimage.border = (!isNaN(this.imageBorder) && this.imageBorder != '') ? parseInt(this.imageBorder) : 0;
		myimage.vspace = (!isNaN(this.imageVSpase) && this.imageVSpase != '') ? parseInt(this.imageVSpase) : 0;
		myimage.hspace = (!isNaN(this.imageHSpase) && this.imageHSpase != '') ? parseInt(this.imageHSpase) : 0;
		if (this.imageAlign != '') {myimage.align = this.imageAlign;}
		cont=myimage.outerHTML;
		if ((this.imageLinkUrl != null) && (this.imageLinkUrl != "http://")) {
			var nowDate=new Date();
			var id=this.randomNumber(0,100)+'_'+nowDate+'_'+this.randomNumber(0,100);
			var Alt = '';
			var target = '';
			if (this.imageAlt != '') Alt = "ALT=\""+this.imageAlt+"\"";
			if (this.imagelinkTarget != '') target = " target=\""+this.imagelinkTarget+"\"";
			cont = "<A ID=\""+id+"\" HREF=\""+this.imageLinkUrl+"\""+Alt+target+">"+cont+"</A>"
		}
		mainField.focus();
		if (mainField.document.selection.type=="None") {
			var sel=mainField.document.selection.createRange();
			sel.pasteHTML(cont);
			sel.select();
			if ((this.imageLinkUrl != null) && (this.imageLinkUrl != "http://")) {
				mainField.document.getElementById(id).setAttribute("href", this.imageLinkUrl);
				mainField.document.getElementById(id).removeAttribute("id");
			}
		}
		mainField.focus();
		if (this.changable == true) {
			mainField.document.selection.clear();
			var sel=mainField.document.selection.createRange();
			sel.pasteHTML(cont);
			sel.select();
		}
		mainField.focus();
	}else mainField.focus();
}

function addImage_gecko(){
	if (this.isTextMode) {return;}
	var mainField = document.getElementById(this.name).contentWindow;

	if ((this.imageUrl != 0) && (this.imageUrl != '')) {
		myimage     = document.createElement("img");
		myimage.src = this.imageUrl;
		myimage.alt = this.imageAlt;
		if (!isNaN(this.imageWidth) && this.imageWidth != "") {
			myimage.width = parseInt(this.imageWidth);
		}
		if (!isNaN(this.imageHeight) && this.imageHeight != "") {
			myimage.height = parseInt(this.imageHeight);
		}
		myimage.border = (!isNaN(this.imageBorder) && this.imageBorder != '') ? parseInt(this.imageBorder) : 0;
		myimage.vspace = (!isNaN(this.imageVSpase) && this.imageVSpase != '') ? parseInt(this.imageVSpase) : 0;
		myimage.hspace = (!isNaN(this.imageHSpase) && this.imageHSpase != '') ? parseInt(this.imageHSpase) : 0;
		if (this.imageAlign != '') {myimage.align = this.imageAlign;}
		if ((this.imageLinkUrl != null) && (this.imageLinkUrl != "http://")) {
			// add image with link
			var linkNode = document.createElement("a");
			linkNode.href = this.imageLinkUrl;
			if (this.imagelinkTarget != "") {
				linkNode.target = this.imagelinkTarget;
			}
			this.add_node(linkNode);
			this.add_node(myimage, linkNode);
		} else {
			// add only image
			this.add_node(myimage);
		}
	} 
	mainField.focus();
}

function insert_into_position_cursor_gecko(textInsert){
	var mainField = document.getElementById(this.name).contentWindow;
	var sel = mainField.getSelection();
	var rangeCount = sel.rangeCount;
	if (rangeCount == 0) {
		var frameDoc = document.getElementById(this.name).contentDocument;
		var body = frameDoc.getElementsByTagName("body")[0];
		var spanNode = document.createElement("span");
		spanNode.innerHTML = textInsert;
		body.appendChild(spanNode);
	} else {
		var range = sel.getRangeAt(0);
		sel.removeAllRanges();
		range.deleteContents();

		var container = range.startContainer;
		var pos = range.startOffset;

		range=document.createRange();
		if (container && container.nodeType==3) {
			var textNode = container;
			container = textNode.parentNode;
			var text = textNode.nodeValue;
  
			var textBefore = text.substr(0,pos);
			var textAfter = text.substr(pos);
  
			var beforeNode = document.createTextNode(textBefore);
			var afterNode = document.createTextNode(textAfter);
			var isertbody = document.createElement("span");
			isertbody.innerHTML = textInsert.toString();
			var insertNode = isertbody;
  
			container.insertBefore(afterNode, textNode);
			container.insertBefore(insertNode, afterNode);
			container.insertBefore(beforeNode, insertNode);
  
			container.removeChild(textNode);
		} else {
			var isertbody = document.createElement("span");
			isertbody.innerHTML = textInsert.toString();
			var insertNode = isertbody;
			afterNode = container.childNodes[pos];
			container.insertBefore(insertNode, afterNode);
		}
  
		try {
			range.setEnd(afterNode, 0);
			range.setStart(afterNode, 0);
			sel.addRange(range);
			mainField.focus();
		}
		//suppress unexpected chrome DOM error
		finally {
			return;
		}
	}
}

function add_node_gecko(newNode, parentNode) {
	var addedNode;
	if (parentNode) {
		addedNode = parentNode.appendChild(newNode);
		return addedNode;
	} else {
		var mainField = document.getElementById(this.name).contentWindow;
		var sel = mainField.getSelection();
		var rangeCount = sel.rangeCount;
		if (rangeCount == 0) {
			var frameDoc = document.getElementById(this.name).contentDocument;
			var body = frameDoc.getElementsByTagName("body")[0];
			addedNode = body.appendChild(newNode);
			return addedNode;
		} else {
			var range = sel.getRangeAt(0);
			sel.removeAllRanges();
			range.deleteContents();
  
			var container = range.startContainer;
			var pos = range.startOffset;
  
			range=document.createRange();
			if (container && container.nodeType==3) {
				var textNode = container;
				container = textNode.parentNode;
				var text = textNode.nodeValue;
    
				var textBefore = text.substr(0,pos);
				var textAfter = text.substr(pos);
    
				var beforeNode = document.createTextNode(textBefore);
				var afterNode = document.createTextNode(textAfter);
    
				container.insertBefore(afterNode, textNode);
				addedNode = container.insertBefore(newNode, afterNode);
				container.insertBefore(beforeNode, newNode);
    
				container.removeChild(textNode);
			} else {
				afterNode = container.childNodes[pos];
				addedNode = container.insertBefore(newNode, afterNode);
			}
			return addedNode;
		}
	}
}



function opendialog_msie(open_url, Width, Height){
	Arguments = new Array();
	Arguments[0] = self;
	Arguments[1] = this.object_name;
	Arguments[2] = this.editor_type;
	var vReturn = window.showModalDialog(open_url, Arguments, 'dialogWidth:' + Width + 'px;dialogHeight:' + Height + 'px;help:no;scroll:no;status:no');
}

function opendialog_gecko(open_url, Width, Height){
	var left = (screen.width - Width)/2;
	var top = (screen.height - Height)/2;

	var Modal = window.open(open_url+'?object_editor='+this.object_name+'&editor_type='+this.editor_type,'','modal=1,toolbar=no,scrollbars=no,resizable=no,width='+Width+',height='+Height+',left='+left+',top='+top);
	var ModalFocus = function()
	{
		if(!Modal.closed){Modal.focus();}
		else{Modal =null;window.removeEventListener(ModalFocus,"focus");ModalFocus = null; };					
	}
	window.addEventListener( "focus",ModalFocus, false ); 
}

function randomNumber (m,n)
{
	m = parseInt(m);
	n = parseInt(n);
	return Math.floor( Math.random() * (n - m + 1) ) + m;
}

function clear(){
	var mainField = document.getElementById(this.name).contentWindow;
	mainField.document.body.innerHTML = "";
}

function updateIframeActions(ed_t) {
	var editor_type = ed_t;
	var isIE = ((navigator.userAgent.toLowerCase().indexOf("msie") != -1) && (navigator.userAgent.toLowerCase().indexOf("opera") == -1) && (navigator.userAgent.toLowerCase().indexOf("webtv") == -1));
	var re = new RegExp('_\\w{1,2}$','i');
	for (var i = 0; i < frames.length; i++) {
		var iframeid = document.getElementsByTagName('IFRAME')[i].id;
		var ed_postf = iframeid.match(re);
		object_name = 'editor' + ed_postf;
		
		if 	(window.frames[i].document.images.length != 0) {
			for (j = 0; j < window.frames[i].document.images.length; j++) {
			
				if (window.frames[i].document.images[j].eventAdded == undefined || window.frames[i].document.images[j].ieEventAdded == undefined) {
					//ff etc
					if (isIE == false && window.frames[i].document.images[j].eventAdded == undefined) { 
							window.frames[i].document.images[j].addEventListener('dblclick', function(objN){ return function () {
							if (this.isTextMode) {return;}
							imagePath = this.getAttribute("src");
							var Width = 450;
							var Height = 400;
							var left = (screen.width - Width)/2;
							var top = (screen.height - Height)/2;
							var Modal = window.open('js/dialogs/insert_image.html?object_editor='+objN+'&editor_type='+editor_type+'&imageSrc='+imagePath,'','modal=1,toolbar=no,scrollbars=no,resizable=no,width='+Width+',height='+Height+',left='+left+',top='+top);
							try {
							var ModalFocus = function() {
									if(!Modal.closed){Modal.focus();}
									else{Modal = null;window.removeEventListener(ModalFocus,"focus");ModalFocus = null; };					
								}
								window.addEventListener( 'focus',ModalFocus, false );
							}
							catch (e) {}
						}
						}(object_name));
						window.frames[i].document.images[j].eventAdded = true;
					}
				
					else if (isIE == true && window.frames[i].document.images[j].ieEventAdded == undefined) { 
					//ie
						imgSrc = window.frames[i].document.images[j].src;
						window.frames[i].document.images[j].attachEvent('ondblclick', function(objN,imagePath){ return function () {
							if (this.isTextMode) {return;}
							var Width = 450;
							var Height = 400;
							var open_url = '../js/dialogs/insert_image.html';
							Arguments = new Array();
							Arguments[0] = self;
							Arguments[1] = objN;
							Arguments[2] = editor_type;
							Arguments[3] = imagePath;
							Arguments[4] = true;
							var vReturn = window.showModalDialog(open_url, Arguments, 'dialogWidth:' + Width + 'px;dialogHeight:' + Height + 'px;help:no;scroll:no;status:no');
						}
						}(object_name, imgSrc));
						window.frames[i].document.images[j].ieEventAdded = true;
					}
				}
			}
		}
	}
}


