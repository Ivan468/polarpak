function chartOver(event) {
	var svgObj = event.currentTarget;
	var chartData = svgObj.getAttribute("data-chart");
	try {
		chartData = JSON.parse(chartData);
	} catch(e) { 
		return false;
	}	
	var svgX = event.layerX;
	var svgY = event.layerY; 
	var baseWidth = svgObj.width.baseVal.value;
	var shownWidth = svgObj.getBoundingClientRect().width; 
	var widthCoef = baseWidth / shownWidth;
	var chartStepX = parseFloat(svgObj.getAttribute("data-step-x"));
	var chartStartX = parseInt(svgObj.getAttribute("data-chart-x"));
	var chartStartY = parseInt(svgObj.getAttribute("data-chart-y"));
	var chartWidth = parseInt(svgObj.getAttribute("data-chart-width"));
	var chartHeight = parseInt(svgObj.getAttribute("data-chart-height"));
	var dataIndex = Math.round(((svgX - chartStartX) / chartStepX) * widthCoef);
	if (chartData[dataIndex]) {
		var pointData = chartData[dataIndex];
		var pointerX = svgObj.getElementsByClassName("pointer-x")[0];
    pointerX.setAttribute('x1', pointData["pos-x"]);
    pointerX.setAttribute('y1', pointData["pos-y"]);
    pointerX.setAttribute('x2', pointData["pos-x"]);
    pointerX.setAttribute('y2', (chartStartY + chartHeight));
		/*
		var pointerY = svgObj.getElementsByClassName("pointer-y")[0];
    pointerY.setAttribute('x1', pointData["pos-x"]);
    pointerY.setAttribute('y1', pointData["pos-y"]);
    pointerY.setAttribute('x2', chartStartX + chartWidth);
    pointerY.setAttribute('y2', pointData["pos-y"]);//*/
		var pointerC = svgObj.getElementsByClassName("pointer-c")[0];
    pointerC.setAttribute('cx', pointData["pos-x"]);
    pointerC.setAttribute('cy', pointData["pos-y"]);
    pointerC.setAttribute('r', "2");

		var indentX = 5; var indentY = 10;
		var dataY = svgObj.getElementsByClassName("data-y")[0];
		dataY.innerHTML = ((typeof pointData["y-text"] === 'undefined')) ? pointData["y"] : pointData["y-text"];
    dataY.setAttribute('x', pointData["pos-x"] + 3 + indentX);
    dataY.setAttribute('y', pointData["pos-y"] - 1 + indentY);

		var dataX = svgObj.getElementsByClassName("data-x")[0];
		dataX.innerHTML = (pointData["x-text"]) ? pointData["x-text"] : pointData["x"];
    dataX.setAttribute('x', pointData["pos-x"] + 3 + indentX);
    dataX.setAttribute('y', pointData["pos-y"] - 15 + indentY);

		var dataBox = svgObj.getElementsByClassName("data-box")[0];
    dataBox.setAttribute('x', pointData["pos-x"] + indentX);
    dataBox.setAttribute('y', pointData["pos-y"] - 29 + indentY);
    dataBox.style.display = "block";

		var dataShadow = svgObj.getElementsByClassName("data-shadow")[0];
    dataShadow.setAttribute('x', pointData["pos-x"] + 3 + indentX);
    dataShadow.setAttribute('y', pointData["pos-y"] - 26 + indentY);
    dataShadow.style.display = "block";
	}
}
