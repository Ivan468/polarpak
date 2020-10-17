var max_number = 0;
function getCategory(pbId, select_obj, pcategory, level) {
		var siteUrl 	= "products.php";
		var isIE6 		= (navigator.userAgent.toLowerCase().indexOf('msie 6') != -1) && (navigator.userAgent.toLowerCase().indexOf('msie 7') == -1);
		
		var categoryBox = document.getElementById("categories_box");
		var selectBox   = categoryBox.getElementsByTagName("select");
		var linkList    = categoryBox.getElementsByTagName("a");
		
		for (var i = 0; i < selectBox.length; i++)	{
			selectBox[i].setAttribute('disabled','disabled');
		}
		for (var i = 0; i < linkList.length; i++)	{
			categoryBox.removeChild(linkList[i]);	
		}

		var url = "block.php?pb_id="+encodeURIComponent(pbId)+"&ajax=1&is_ajax=1&pcategory="+encodeURIComponent(pcategory)+"&level"+encodeURIComponent(level);
		callAjax(url, getCategoryFinished, {'pb_id': pbId, 'select_obj': select_obj, 'pcategory':pcategory, 'level':level} );
/*	
		callAjax(
				 siteUrl + '?is_ajax=1&pcategory='+ pcategory +'&level='+ level,
				 getCategoryFinished, 
				 {'select_obj': select_obj, 'pcategory':pcategory, 'level':level}
		);
*/
		
		function getCategoryFinished(data, someParams)	{
			var pbId = someParams['pb_id'];
			var select_obj  = someParams['select_obj'];
			
			var categoryBox = document.getElementById("categories_box");
			var selectBox   = categoryBox.getElementsByTagName("select");			
			
			for (var i = 0; i < selectBox.length; i++)	{	
				selectBox[i].removeAttribute('disabled');
			}
			
			// create new reference to the category
			var goLink  	 = document.createElement('a');
			goLink.innerHTML = "Go";
			
			// remove all selects with number >= max_number
			max_number = level + 1;			
			if(select_obj.nextSibling)	{
				var allSelects = selectBox.length;
				for(i = level+1; i <= max_number; i++)	{
				while(allSelects > max_number)	{
					var next = select_obj.nextSibling;
					categoryBox.removeChild(next);
					--allSelects;	
					}
				}
			}
			
			if (data) {				
				// parse JSON
				var obj = eval('('+data+')');
				
				// if no subcategories in this category
				if(obj == null){					
					// add reference to the category
					if (someParams['pcategory'] != -1)	{	// if category_id is not empty
						goLink.href  = 'products.php?category_id=' + someParams['pcategory'];
						categoryBox.insertBefore(goLink, select_obj.nextSibling);
					}
				} else	{
					// create new select
					var selectNew 	= document.createElement('select');
					if(isIE6)	{
						selectNew.onchange = function(){getCategory(pbId, this, this.value, " + max_number + ");};
					} else	{
						selectNew.setAttribute("onChange", "getCategory("+pbId+", this, this.value, " + max_number + "); return false;");
					}
					selectNew.setAttribute("id", "category_" + max_number);
					
					// create default option
					var optionSelect = document.createElement('option');
					optionSelect.setAttribute("value", "-1");
					optionSelect.innerHTML = "Select category";
					selectNew.appendChild(optionSelect);
					
					//fill options with JSON data	
					for(var id in obj) {
				    var catInfo = obj[id];
						var key  = catInfo["id"];
						var name = catInfo["name"];				
						var optionNew = document.createElement('option');
						optionNew.setAttribute("value", key);
						optionNew.innerHTML = name;
						selectNew.appendChild(optionNew);
					}
					
					// add new select and reference to the category	
					categoryBox.insertBefore(selectNew, select_obj.nextSibling);
					if((someParams['pcategory']) == -1)	{	// if category_id is empty
						alert("Please, select category");
					} else	{						
						goLink.href  = 'products.php?category_id=' + someParams['pcategory'];
						categoryBox.insertBefore(goLink, select_obj.nextSibling);
					}
				}
			} else	{
				if(someParams['pcategory'] != -1)	{	// if category_id is not empty
					goLink.href  = 'products.php?category_id=' + someParams['pcategory'];
					categoryBox.insertBefore(goLink, select_obj.nextSibling);
				}
			}
		}
		return false;
}