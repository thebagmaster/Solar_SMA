<?php
			date_default_timezone_set('America/Chicago');
			$databaseName = "sma";
			$connectionOptions = array("Database"=>$databaseName);
			$conn = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
			if (!$conn)
				die('</script><h1>Something went wrong while connecting to MSSQL</h1><br><br>Error: ' . print_r( sqlsrv_errors(), true));
			$sql = "SELECT TOP 1 TimeStamp, SENS0722_12440_IntSolIrr, SENS0722_12440_TmpMdulF, WR7KU009_2002143210_Pac, WR7KU009_2002143257_Pac, WR7KU009_2002143337_Pac, WR7KU009_2002144138_Pac, WR7KU009_2002144187_Pac, WR7KU009_2002144289_Pac FROM wb170538 ORDER BY TimeStamp DESC";
			$stmt = sqlsrv_query( $conn, $sql );
			if($stmt)
			while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {				
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
				echo number_format($percent) . "%,";
				echo floor($total/1000);
			}
			sqlsrv_close($conn);
		  ?>