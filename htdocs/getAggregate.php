<?php
			date_default_timezone_set('America/Chicago');
		  
			echo '{"cols":[';
			echo '{"label": "Time", "type": "datetime"},';
			echo '{"label": "Power Gen (kW)", "type": "number"},';
			echo '{"label": "Solar Irradiation (W/m^2)", "type": "number"},';
			echo '{"label": "Module Temp (F)", "type": "number"},';
			echo '{"label": "Expected To Grid (kW)", "type": "number"}],';
			echo '"rows": [';

			$databaseName = "sma";
			$connectionOptions = array("Database"=>$databaseName);
			$conn = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
			if (!$conn)
				die('</script><h1>Something went wrong while connecting to MSSQL</h1><br><br>Error: ' . print_r( sqlsrv_errors(), true));
			
			if(isset($_POST["time1"])){
				$date1 = strtotime($_POST["time1"])-60*60*5;  //weird 5 hour offset
				$date2 = strtotime($_POST["time2"])-60*60*5;
				$sql = "SELECT * FROM (SELECT TimeStamp, SENS0722_12440_IntSolIrr, SENS0722_12440_TmpMdulF, WR7KU009_2002143210_Pac, WR7KU009_2002143257_Pac, WR7KU009_2002143337_Pac, WR7KU009_2002144138_Pac, WR7KU009_2002144187_Pac, WR7KU009_2002144289_Pac FROM wb170538 WHERE TimeStamp BETWEEN DATEADD(second, $date1, '1970/01/01 00:00:00') AND DATEADD(second, $date2, '1970/01/01 00:00:00')) A ORDER BY A.TimeStamp ASC";  
			}else{
				$sql = "SELECT * FROM (SELECT TOP 1500 TimeStamp, SENS0722_12440_IntSolIrr, SENS0722_12440_TmpMdulF, WR7KU009_2002143210_Pac, WR7KU009_2002143257_Pac, WR7KU009_2002143337_Pac, WR7KU009_2002144138_Pac, WR7KU009_2002144187_Pac, WR7KU009_2002144289_Pac FROM wb170538 ORDER BY TimeStamp DESC) A ORDER BY A.TimeStamp ASC";  
			}
			$rowsread = 0;
			$first = 0;
			$row_count = 0;
			$stmt = sqlsrv_query( $conn, $sql , array(), array("Scrollable"=>"buffered"));
			if($stmt)
			{
				$row_count = sqlsrv_num_rows( $stmt );
				$mod = ((int) ($row_count/1500))+1;
			}
			$stmt = sqlsrv_query( $conn, $sql );
			if($stmt)
			while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
				$rowsread++;
				if(($rowsread % $mod) == 0)
					{				
					$total = $row['WR7KU009_2002143210_Pac']+$row['WR7KU009_2002143257_Pac']+$row['WR7KU009_2002143337_Pac']+$row['WR7KU009_2002144138_Pac']+$row['WR7KU009_2002144187_Pac']+$row['WR7KU009_2002144289_Pac']+1;
					$total /= 1;
					
					$tc = -0.0045;
					$totDC = 49500;
					$energy2Grid = 0.872;
					$tempC = ($row['SENS0722_12440_TmpMdulF']-32)*5/9;
					
					$efficiency = $totDC*($row['SENS0722_12440_IntSolIrr']/1000)*(1+(($tempC-25)*$tc));
					$expected = $efficiency * $energy2Grid;
					$expected /= 1;
					$percent = ($efficiency)?$total/$expected*100:0;
					if($first > 0)
						echo ",";
					echo '{"c":[{"v": "Date(' . $row['TimeStamp']->format('Y, ') . 
					(intval($row['TimeStamp']->format('m'))-1) . $row['TimeStamp']->format(', d, H, i, s') . ')"}, {"v": ' . 
					round(floatval($total),2) . '}, {"v":' . 
					round(floatval($row['SENS0722_12440_IntSolIrr']),2) . '}, {"v": ' . 
					round(floatval($row['SENS0722_12440_TmpMdulF']),2) . '}, {"v": ' . 
					round(floatval($expected),2) . '}]}';
					$first = 1;
					//echo "<h1>$mod</h1>:";
					}
				}
			echo "]}";
			sqlsrv_close($conn);
		  ?>