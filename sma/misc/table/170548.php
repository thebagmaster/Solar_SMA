<?php

function sanitize($data)
{
$data = str_replace("-", "", $data);
$data = str_replace(" ", "", $data);
return $data;
}


	chdir(__DIR__);	
	$log_file_handle = fopen(getcwd() . "\\" . "log_" . date('Y-m-d') . ".txt", a);
	
	//we connect to the database
	$databaseName = "sma";
	$connectionOptions = array("Database"=>$databaseName);
	$db_connection = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
	
	if (!$db_connection) {
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "Error connecting to SQL Server");
		die('Something went wrong while connecting to MSSQL');
	}
	fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "conected to 'sma' database, user pushFTP\r\n");
	
	echo "<br><br>---------------------------------------<br><br>";	

	$xml_file = simplexml_load_file(getcwd() . "\\170548.xml");
	
	$sql = 'USE sma CREATE TABLE wb170538 ( TimeStamp DATETIME PRIMARY KEY NOT NULL ';	
	
	foreach ($xml_file->MeanPublic as $MeanPublic) {
			$key = split(":", $MeanPublic->Key);		
			$sql .= ' ,' . sanitize($key[0]) . "_" . sanitize($key[1]) . "_" . sanitize($key[2]) . ' FLOAT '; 
		}	
	
	$sql .= ')';
	
	echo $sql;
	echo "<br><br>---------------------------------------<br><br>";

	$resultado = sqlsrv_query($db_connection,$sql);
	
	if( $resultado === false ) {
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				fwrite($log_file_handle, " - - " . "ERROR: problems with the query: SQLSTATE: ".$error[ 'SQLSTATE']."code: ".$error[ 'code']."message: ".$error[ 'message']);
			}
		}
		fwrite($log_file_handle,"\n".$sql."\n");
		fwrite($log_file_handle,"\n".$xml_file."\n");
	}
	else{
		fwrite($log_file_handle, " - - " . "REGISTER successfully inserted");
	}
	
	echo "<br>---------------------------------------<br><br>";
	
	if(sqlsrv_close($db_connection)){//we close the database connection
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "Connection to database closed\r\n"); 
	}
	else
	{
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "ERROR: the connection to the database could not be closed\r\n"); 
	}


?>