<meta http-equiv="refresh" content="120">
<html>
  <head>
	<link rel="icon" type="image/ico" href="favicon.ico"> 
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
		  <?php
			date_default_timezone_set('America/Chicago');
			echo "['Time', 'Expected To Grid (W)', 'Power 3210', 'Power 3257', 'Power 3337', 'Power 4138', 'Power 4187', 'Power 4289']";
			$databaseName = "sma";
			$connectionOptions = array("Database"=>$databaseName);
			$conn = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
			if (!$conn)
				die('Something went wrong while connecting to MSSQL');
				
			if(isset($_GET["time1"])){
				$date1 = strtotime($_GET["time1"])-60*60*5;  //weird 5 hour offset
				$date2 = strtotime($_GET["time2"])-60*60*5;
				$sql = "SELECT * FROM (SELECT TimeStamp, SENS0722_12440_IntSolIrr, SENS0722_12440_TmpMdulF, WR7KU009_2002143210_Pac, WR7KU009_2002143257_Pac, WR7KU009_2002143337_Pac, WR7KU009_2002144138_Pac, WR7KU009_2002144187_Pac, WR7KU009_2002144289_Pac FROM wb170538 WHERE TimeStamp BETWEEN DATEADD(second, $date1, '1970/01/01 00:00:00') AND DATEADD(second, $date2, '1970/01/01 00:00:00')) A ORDER BY A.TimeStamp ASC";  
			}else{
				$sql = "SELECT * FROM (SELECT TOP 100 TimeStamp, SENS0722_12440_IntSolIrr, SENS0722_12440_TmpMdulF, WR7KU009_2002143210_Pac, WR7KU009_2002143257_Pac, WR7KU009_2002143337_Pac, WR7KU009_2002144138_Pac, WR7KU009_2002144187_Pac, WR7KU009_2002144289_Pac FROM wb170538 ORDER BY TimeStamp DESC) A ORDER BY A.TimeStamp ASC";  
			}
			$stmt = sqlsrv_query( $conn, $sql );
			
			while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
				if($row['SENS0722_12440_IntSolIrr'] > 0.1)
				{
					$total = $row['WR7KU009_2002143210_Pac']+$row['WR7KU009_2002143257_Pac']+$row['WR7KU009_2002143337_Pac']+$row['WR7KU009_2002144138_Pac']+$row['WR7KU009_2002144187_Pac']+$row['WR7KU009_2002144289_Pac']+1;
					
					$tc = -0.0045;
					$totDC = 49500;
					$energy2Grid = 0.872;
					$tempC = ($row['SENS0722_12440_TmpMdulF']-32)*5/9;
					
					$efficiency = $totDC*($row['SENS0722_12440_IntSolIrr']/1000)*(1+(($tempC-25)*$tc));
					$expected = $efficiency * $energy2Grid;
					$expected = $expected/6;
					$percent = ($efficiency)?$total/$expected*100:1;
					
					echo ",['" . $row['TimeStamp']->format('m-d H:i') . "', " . $expected;
					echo ", " . ($row['WR7KU009_2002143210_Pac']?$row['WR7KU009_2002143210_Pac']:"");
					echo ", " . ($row['WR7KU009_2002143257_Pac']?$row['WR7KU009_2002143257_Pac']:"");
					echo ", " . ($row['WR7KU009_2002143337_Pac']?$row['WR7KU009_2002143337_Pac']:"");
					echo ", " . ($row['WR7KU009_2002144138_Pac']?$row['WR7KU009_2002144138_Pac']:"");
					echo ", " . ($row['WR7KU009_2002144187_Pac']?$row['WR7KU009_2002144187_Pac']:"");
					echo ", " . ($row['WR7KU009_2002144289_Pac']?$row['WR7KU009_2002144289_Pac']:"0") . "]";
				}
			}
			
			sqlsrv_close($conn);
		  ?>
        ]);

        var options = {
          title: 'NTH Solar',
		  legend: {position: 'top'},
		  series: {0: {type: "line"}}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
		
      }
	  function dates()
	  {
		<?php
		if(isset($_GET["time1"])){
			$date1 = strtotime($_GET["time1"]);
			$date2 = strtotime($_GET["time2"]);
			echo "document.getElementById('time1').value = '" . date("Y-m-d\TH:i", $date1) . "';";
			echo "document.getElementById('time2').value = '" . date("Y-m-d\TH:i",  $date2) . "';";
		}else{
			echo "document.getElementById('time1').max = '" . date("Y-m-d\TH:i") . "';";
			echo "document.getElementById('time1').value = '" . date("Y-m-d", time()) . "\T00:00:00';";
			echo "document.getElementById('time2').max = '" . date("Y-m-d\TH:i") . "';";
			echo "document.getElementById('time2').value = '" . date("Y-m-d\TH:i",  time()) . "';";
		}
		?>
	  }
	  function addDay()
	  {
		<?php
		if(isset($_GET["time1"])){
			$date1 = strtotime($_GET["time1"]);
			$date2 = strtotime($_GET["time2"]);
			echo "document.getElementById('time1').value = '" . date("Y-m-d\TH:i", $date1+60*60*24) . "';";
			echo "document.getElementById('time2').value = '" . date("Y-m-d\TH:i",  $date2+60*60*24) . "';";
		}
		?>
	  }
	  function preDay()
	  {
		<?php
		if(isset($_GET["time1"])){
			$date1 = strtotime($_GET["time1"]);
			$date2 = strtotime($_GET["time2"]);
			echo "document.getElementById('time1').value = '" . date("Y-m-d\TH:i", $date1-60*60*24) . "';";
			echo "document.getElementById('time2').value = '" . date("Y-m-d\TH:i",  $date2-60*60*24) . "';";
		}
		?>
	  }
    </script>
  </head>
  <body onload="dates();">
	<center>
	<form method=get style="position:relative; top:140px; z-index:1000;">
	<input value="Previous Day" type="submit" onclick="preDay();">
	<input type="datetime-local" name="time1" id="time1" min="2014-07-28T17:05">
	to&nbsp;<input type="datetime-local" name="time2" id="time2" min="2014-07-28T17:05">
	<input type="submit">
	<input value="Next Day" type="submit" onclick="addDay();">
	</form>
	</center>
    <div id="chart_div" style="top:0px; left:-100px; width: 2200px; height: 1000px;"></div>
  </body>
</html>