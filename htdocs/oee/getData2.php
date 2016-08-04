<?php
	$colors = ['#3366CC','#DC3912','#FF9900','#109618','#990099','#3B3EAC','#0099C6','#DD4477','#66AA00','#B82E2E','#316395','#994499','#22AA99','#AAAA11','#6633CC','#E67300','#8B0707','#329262','#5574A6','#3B3EAC'];
	date_default_timezone_set('America/Chicago');

	$databaseName = "nth";
	$connectionOptions = array("Database"=>$databaseName);
	$conn = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
	if (!$conn)
		die('</script><h1>Something went wrong while connecting to MSSQL</h1><br><br>Error: ' . print_r( sqlsrv_errors(), true));
	
	echo '{"cols":[';
	echo '{"label": "Time", "type": "datetime"},';
	echo '{"label": "Line1", "type": "number"},';
	echo '{"label": "Line2", "type": "number"},';
	echo '{"label": "Line3", "type": "number"},';
	echo '{"label": "Line4", "type": "number"},';
	echo '{"label": "Line5", "type": "number"},';
	echo '{"label": "Line6", "type": "number"},';
	echo '{"label": "Line7", "type": "number"},';
	echo '{"label": "Line8", "type": "number"}';
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
			echo '{"c":[{"v": "Date(' . $row['time']->format('Y, ') . (intval($row['time']->format('m'))-1) . $row['time']->format(', d, H, i, s') . ',' . $row['time']->format('u')/1000 . ')"},';
			echo '{"v": ' . $row['parts'] . '}';
		}else{
			echo ',';
			echo '{"v": ' . $row['parts'] . '}';
		}
		}
	echo "]}]}";
	sqlsrv_close($conn);
?>