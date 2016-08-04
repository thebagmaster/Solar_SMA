<?php
	
	$lines = 8;
	$maxParts = 30;
	$maxReject = 0.85;
	
	$randmax = getrandmax();
	$varPart = 0.75;
	$varReject = 0.75;
	
	date_default_timezone_set('America/Chicago');

	$databaseName = "nth";
	$connectionOptions = array("Database"=>$databaseName);
	$conn = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
	if (!$conn)
		die('</script><h1>Something went wrong while connecting to MSSQL</h1><br><br>Error: ' . print_r( sqlsrv_errors(), true));
	
	for ($p = 1; $p <= $maxParts; $p++) {
		for ($i = 1; $i <= $lines; $i++) {
			if(rand() < $randmax*$varPart){
				$pass=0;
				if(rand() < $randmax*$varReject){
					$pass=1;
				}
				$sql = "INSERT INTO parts_log VALUES (getdate(),$i,$pass)";  
				if( ($stmt = sqlsrv_query( $conn, $sql )) === false ) {die( print_r( sqlsrv_errors(), true));}
			}
		}
	}
	echo '{"cols":[';
	echo '{"label": "Time", "type": "datetime"},';
	echo '{"label": "Line1", "type": "number"},';
	echo '{"label": "Line2", "type": "number"},';
	echo '{"label": "Line3", "type": "number"},';
	echo '{"label": "Line4", "type": "number"},';
	echo '{"label": "Line5", "type": "number"},';
	echo '{"label": "Line6", "type": "number"},';
	echo '{"label": "Line7", "type": "number"},';
	echo '{"label": "Line8", "type": "number"},';
	echo '],"rows": [';
	
	//$sql = "SELECT * FROM parts_log WHERE time > dateadd(minute, -1, GetDate())";  
	$sql = "SELECT CAST(AVG(CAST(time AS FLOAT)) AS DATETIME)AS time,line,COUNT(line) AS parts FROM parts_log WHERE time > dateadd(second, -60, GetDate()) GROUP BY line ORDER BY line";
	$comma=false;
	$line = array();
	$stmt = sqlsrv_query( $conn, $sql );
	if($stmt)
	while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
		if(!$comma){
			$comma=true;
		}else{
			echo ',';
		}
		$l = $row['line'];
		echo '{"c":[{"v": "Date(' . $row['time']->format('Y, ') . (intval($row['time']->format('m'))-1) . $row['time']->format(', d, H, i, s') . ',' . $row['time']->format('u')/1000 . ')"}';
		for ($i = 1; $i <= $lines; $i++) {
			$parts = ($l === $i?$row['parts']:'null');
			echo ', {"v": ' . $parts . '}';
		}
		echo ']}';
		}
	echo "]}";
	sqlsrv_close($conn);
?>