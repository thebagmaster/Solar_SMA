<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>fgdgdf</title>
<meta name="generator" content="Bluefish 1.0.7w">
<meta name="author" content="kim">
<meta name="copyright" content="">
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="expires" content="0">
<meta http-equiv="refresh" content="5; URL=http://">
</head>
<body>


<?php

	
	//We define what we need to connect to the database 
	//YOU SHOULD CHANGE THIS
	const DB_SERVER = "localhost";
	const DB_NAME = "cat_backup";
	const DB_USER = "pushFTP";
	const DB_PASSWORD = "pushFTP";


	//we connect to the database
	$db_connection = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if ($db_connection->connect_error) {
    		echo date("[d/M/Y-H:i:s]") . " - - " . "ERROR: could not connect to 'cat_backup' database -- " . mysqli_connect_errno() . mysqli_connect_error() . "<br>";
	}
	else{
		echo date("[d/M/Y-H:i:s]") . " - - " . "connected to 'cat_backup' database, user pushFTP<br>";
	}
	
	echo "<br><br>---------------------------------------<br><br>";	
	
	/*We are openning the xml file we are going to use for creating the table
	in the database.
	We have previously prepared this file (from a file from the pushFTP). 
	In the database threre is a table for each device, so this file contents 
	data of only one device .
	The file must be in the same directory that this php file
	
	YOU SHOULD CHANGE THE NAME OF THE XML FILE TO THE ONE YOU HAVE PREPARED
	*/	
	$xml_file = simplexml_load_file(getcwd() . "\\13382.xml");
	
	/*We prepare the sql query for creating the table,
	the names of the fields are extracted from the "key" element in each node 
	of the xml file
	*/ 
	
	/*The name we give to the table is "13382" which is the serial number of the device
	
	YOU SHOULD CHANGE THIS 13382 TO THE SERIAL NUMBER OF THE NEW DEVICE
	*/	
	
	$sql = 'CREATE TABLE `13382` (' . ' `TimeStamp` DATETIME NOT NULL, ';	
	
	foreach ($xml_file->MeanPublic as $MeanPublic) {
			$key = split(":", $MeanPublic->Key);		
			$sql .= ' `' . $key[2] . '` FLOAT, '; 
		}	
	
	$sql .= 'PRIMARY KEY (`TimeStamp`)' . ' )' . ' TYPE = myisam';
	
	echo $sql;
	echo "<br><br>---------------------------------------<br><br>";

	$resultado = $db_connection->query($sql);
	
	echo var_dump($resultado) . "<br>";
	if (!$resultado){		
		echo date("[d/M/Y-H:i:s]") . " - - " . "ERROR: problems with the query -- " . $db_connection->error . "<br>";
	}
	else{
		echo "TABLE successfully created";
	}
	echo "<br>---------------------------------------<br><br>";
	
	if($db_connection->close()){
		echo date("[d/M/Y-H:i:s]") . " - - " . "Connection to database closed<br>"; 
	}
	else
	{
		echo date("[d/M/Y-H:i:s]") . " - - " . "ERROR: the connection to the database could not be closed<br>"; 
	}


?>

</body>
</html>