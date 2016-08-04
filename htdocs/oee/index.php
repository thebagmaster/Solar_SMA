<!DOCTYPE html>
<html>
  <head>
	<link rel="icon" type="../image/ico" href="favicon.ico"> 
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
	google.charts.load('current', {'packages':['corechart','bar']});
	google.charts.setOnLoadCallback(init);
	function init() {
		var options = {
			width: 850,
			height: 600,
			interpolateNulls: true,
			curveType: 'function',
			animation:{
				duration: 1000,
				easing: 'out',
			}
		};
		var options2 = {
			width: 200,
			height: 600,
			bar: { groupWidth: '100%' },
			legend: { position: "none" },
			animation:{
				duration: 1000,
				easing: 'out',
			}
		};
		var data;
		var lineChart = new google.visualization.LineChart(
			document.getElementById('line_chart_div'));
		
		var rejectChart = new google.charts.Bar(
			document.getElementById('reject_chart_div'));

		function drawChart() {
			var jsonData = $.ajax({
				url: "getData.php",
				dataType: "json",
				async: false
				}).responseText;
			var jsonData2 = $.ajax({
				url: "getData2.php",
				dataType: "json",
				async: false
				}).responseText;
				
			if(!data)
				data = new google.visualization.DataTable(jsonData);
			else
				data = google.visualization.data.join(data,new google.visualization.DataTable(jsonData),'full',[[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8]],[],[]);
			
			lineChart.draw(data, options);
			rejectChart.draw(new google.visualization.DataTable(jsonData2), google.charts.Bar.convertOptions(options2))
		}
		drawChart();
		setInterval(function(){ drawChart(); }, 10000);
	}

    </script>
  </head>
  <body>
    <table>
      <tr>
        <td><div id="line_chart_div" style="border: 1px solid #ccc"></div></td>
        <td><div id="reject_chart_div" style="border: 1px solid #ccc"></div></td>
      </tr>
    </table>
  </body>
</html>