<!DOCTYPE html>
<html>
  <head>
	<link rel="icon" type="image/ico" href="favicon.ico"> 
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
	$(document).ready(function(){
		var fiveMinutes = 60 * 5,
        display = document.querySelector('#time');
		startTimer(fiveMinutes, display);
		
		google.load("visualization", "1", {packages:["corechart","gauge"],   
        callback:function(){drawChart();}});
	});
	  var jsonData;
	  var data;
	  var options;
	  var chart;
	  var postData;
	  var d;
	  var d2;
	  
	  	var i;
		var circle;
		var angle;
		var radius;
		
		var needle;
		var kwph;
		var dial_angle;
		var options_gauge;
		var chart_gauge;
		var data_gauge;
		var formatter;
		
		var radians;
		var x;
		var y;
		var e;
		var d;
		
		var timer;
		var minutes;
		var seconds;
		
		var str_percent;
	  
	  function getData(re){
		  
		  dates(re);
		  
		  str_percent=$.ajax({
		  url: "getPercent.php",
		  dataType:"string",
		  async: false
		  }).responseText;
		  
		  document.getElementById('percent').textContent = str_percent.split(",")[0];
		  kwph = parseInt(str_percent.split(",")[1]);
		  document.getElementById('kwhr').textContent = kwph;
		  //drawGauge();
			
		    if(chart_gauge==undefined)
			  chart_gauge = new google.visualization.Gauge(document.getElementById('gauge_div'));	  
		  
			if(data_gauge!=undefined)
				data_gauge.setValue(0, 1,kwph*0.22);
			else{
				data_gauge = new google.visualization.arrayToDataTable([
			  ['Label', 'Value'],
			  ['$/h', kwph*0.22]
			]);
			}
		  chart_gauge.draw(data_gauge, options_gauge);
		  
		  delete postData;
		  postData = $("#ajaxForm").serializeArray();
		  
		 jsonData = $.ajax({
		  url: "getData.php",
		  type: "POST",
		  data : postData,
		  dataType:"json",
		  async: false
		  }).responseText;
		  
		  if(chart!=undefined)
			  chart.clearChart();
		  else
			  chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
		  
		  delete data;
		  data = new google.visualization.DataTable(jsonData);
		  chart.draw(data, options);
	  }
      function drawChart() {
        options = {
          title: 'NTH Solar',
		  titleTextStyle: {fontSize:60},
		  colors: ['#ff9900','#3366cc','#dc3912','#0099c6','#109618'],
		  vAxes:[
				{title:'Kilowatts', textPosition: 'out'},
				{title:'Watts per Square Meter', textPosition: 'in', maxValue: 1200},
				{title:'Fahrenheit', textPosition: 'out', minValue:0, maxValue: 200}
				],
		  legend: {textStyle: {fontSize: 18}},
		  series: {
            0: { type: "area", targetAxisIndex: 0, areaOpacity: 0.6 },
            1: { type: "line", targetAxisIndex: 1 },
			2: { type: "line", targetAxisIndex: 2 },
			3: { type: "line", targetAxisIndex: 0 }
          },
		  animation:{
				duration: 10000,
				easing: 'out',
			}
        };
		
		options_gauge = {
          width: 350, height: 350,
		  greenColor:"#12ad1c",
		  yellowColor:"#0e7f14",
		  redColor:"#09510d",
          redFrom: 8, redTo: 9,
          yellowFrom:6, yellowTo: 8,
		  greenFrom:0, greenTo: 6,
          minorTicks: 5,
		  min: 0,
		  max: 9,
		  animation:{
				duration: 10000,
				easing: 'out',
			}
        };

		
		
		getData(true);
      }
	  function dates(force)
	  {
		if(force)
		{
			delete d2;
			delete d;
			d = new Date((new Date)*1 - 1000*3600*5);
			d2 = new Date(d);
			document.getElementById('time1').max = d2.toJSON().substring(0,19);
			d.setDate(d2.getDate()-5);
			document.getElementById('time1').value = d.toJSON().substring(0,19);
			
			document.getElementById('time2').max = d2.toJSON().substring(0,19);
			document.getElementById('time2').value = d2.toJSON().substring(0,19);
		}
	  }
	  
	function startTimer(duration, display) {
		drawCircle(duration);
		timer = duration;
		setInterval(function () {
        minutes = parseInt(timer / 60, 10)
        seconds = parseInt(timer % 60, 10);

        //minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

			if (--timer < 0) {
				window.clearInterval(window.cirtimer);
				document.getElementById("arcer").setAttribute("d", "M220 25");
				timer = duration;
				getData(true);
				drawCircle(duration);
			}
		}, 1000);
	}
	function drawCircle(duration) {
     	i = 0;
		circle = document.getElementById("arcer");
		angle = -90;
		radius = 10;
		window.cirtimer = window.setInterval(
		function() {
			
			angle +=5;  
			radians= (angle/180) * Math.PI;
			x = Math.cos(radians) * radius;
			y = Math.sin(radians) * radius+radius;
			e = circle.getAttribute("d");
	  
			d = e+ " a "+radius+" "+radius+", 0, "+((angle>90)?"1":"0")+", 1, "+ x + " " + y;
			d+=" L 220 35 Z M220 25"
			if (angle === 269 && i !== 0) {
				window.clearInterval(window.cirtimer);
				circle.setAttribute("d", "");
			}
			circle.setAttribute("d", d);
			i++;
		} 
	,duration/360*5000)
    }
	//function drawGauge(){
	//	dial_angle = (120*((40-kwph))/40.0+30);
	//	needle = document.getElementById("needle");
	//	needle.setAttribute("x2", 75+Math.cos((dial_angle/180.0)*Math.PI)*70);
	//	needle.setAttribute("y2", 80-Math.sin((dial_angle/180.0)*Math.PI)*70);
	//}
    </script>
  </head>
  <body onload="dates();">
		<center>
	  <form style="position:relative; left:50px; top:120px; z-index:1000;" id="ajaxForm">
	  <input type="datetime-local" name="time1" id="time1" min="2014-07-28T17:05">
	  to&nbsp;<input type="datetime-local" name="time2" id="time2" min="2014-07-28T17:05">
	  <input type="button" value="Submit" onClick="getData(false);">
	  </form>
	  </center>
	  
    <div id="chart_div" style="width: 1800px; height: 1000px;"></div>
	
	<div style="position:absolute; left:1300px; top:150px; text-align:center;">
	<svg width="300" height="100">
	<text text-anchor="start" x="150" y="20" font-family="Arial" font-size="18" stroke="none" stroke-width="0" fill="#222222">
	Refresh in
	</text>
	<text id="time" text-anchor="start" x="170" y="40" font-family="Arial" font-size="18" font-weight="bold" stroke="none" stroke-width="0" fill="#222222">
	</text>
	<circle cx="220" cy="35" r="10" stroke="#222222" stroke-width="2" fill="none" />
	<path id="arcer" d="M220 25" fill="#222222""/>
	<text text-anchor="start" x="0" y="20" font-family="Arial" font-size="18" stroke="none" stroke-width="0" fill="#222222">
	Operating at
	</text>
	<text id="percent" text-anchor="start" x="26" y="40" font-family="Arial" font-size="18" font-weight="bold" stroke="none" stroke-width="0" fill="#222222">
	</text>
	<text text-anchor="start" x="2" y="60" font-family="Arial" font-size="18" stroke="none" stroke-width="0" fill="#222222">
	of Expected
	</text>
	</svg>
	</div>
	<div id="gauge_div" style="position:absolute; left:1550px; top:20px; text-align:center;"></div>
	<div style="position:absolute; left:1600px; top:360px; text-align:center;">
	<svg width="200" height="50">
	<text id="kwhr" text-anchor="start" x="90" y="20" font-family="Arial" font-size="18" stroke="none" stroke-width="0" fill="#222222"></text>
	<text text-anchor="start" x="120" y="20" font-family="Arial" font-size="18" stroke="none" stroke-width="0" fill="#222222">KW/h</text>
	</svg>
	</div>
	<!--
	<div style="position:absolute; left:1300px; top:50px; text-align:center;">
	<svg width="150" height="120">
	<g stroke="red" stroke-width="3" stroke-linecap="round">
	<line id="needle" x1="75" y1="80" x2="75" y2="10" />
	<g stroke="black" stroke-width="1">
	<line x1="75" y1="80" x2="10" y2="45" />
	<line x1="75" y1="80" x2="140" y2="45" />
	<path d="M 140 45 A 70 70 0 0 0 10 45" fill="none"/>
	</svg>
	</div>
	-->
  </body>
</html>