
<?php
/*
 SMAwebBox_dataExtractor
Version: 1.0
Last revision 05 October 2010
Licence: GNU GPL
By Carlos Alonso Gabizón

Please send any bug, feedback, etc. to cagabi@lapiluka.org

*/



	//require_once('pclzip.lib.php');	
	$zip = new ZipArchive;
	function sanitize($data){
$data = str_replace("-", "", $data);
$data = str_replace(" ", "", $data);
return $data;
}
	
	function move_file($origin, $destination){
		global $log_file_handle;		
		if(copy($origin, $destination)){
    	    fwrite($log_file_handle, " - - " . " copied to " . $destination);
    	    if(unlink($origin)){
				fwrite($log_file_handle, " - - " . "original file deleted\r\n");
				return true;
			}
			else{
				fwrite($log_file_handle, " - - " . "ERROR: " . "original file could not be deleted\r\n");
				return false;
			}
    	}
	    else{
    		fwrite($log_file_handle, " - - " . "ERROR: " . "could not be copied to " . $destination . "\r\n");
    		return false;
    	 }							
	}
	

	function morefiles_indir($origin){
		global $log_file_handle;		
		if (!$dir_handle = opendir($origin)){
			fwrite($log_file_handle, " - - " . "ERROR in the function 'morefile_indir()'");
			exit;
		}
		$filename = readdir($dir_handle); 
		while($filename !== false){
			if(is_file($origin . "\\" . $filename)){
				fwrite($log_file_handle, " - - " . "there are more files in the directory\r\n");				
				return true;
			}
			$filename = readdir($dir_handle);
		}
		fwrite($log_file_handle, " - - " . "no more files in the directory, the extraction has finished\r\n");
		return false;
	}		
    	    	
    	    	
	function extract_zipfile($origin, $destiny){
		global $log_file_handle;
		global $zip;
		// $zip_archive = new PclZip($origin);
		// $extracted_file = $zip_archive->extract(PCLZIP_OPT_PATH, $destiny);					
		// if($extracted_file == 0) {
			// fwrite($log_file_handle, " - - " . 'ERROR unzipping ' . $filename . ': ' . $zip_archive->errorInfo(true));
			// return false;
		// }
		// else{
			// fwrite($log_file_handle, " - - " . ' unzipped');
			// return true;    			
		// }   
		
		if ($zip->open($origin) === TRUE) {
			$zip->extractTo($destiny);
			$zip->close();
			fwrite($log_file_handle, " - - " . ' unzipped');
			return true;   
		} else {
			fwrite($log_file_handle, " - - " . 'ERROR unzipping ' . $filename);
			return false;
		}		
	}
	
	function delete_file($origin){
		global $log_file_handle;		
		if(unlink($origin)){
  			fwrite($log_file_handle, " - - " . " deleted\r\n");
			return true;  		
  		}
  		else{
  			fwrite($log_file_handle, " - - " . "ERROR: " . "couldn´t be deleted\r\n");
  			return false;
  		}
     }
     
     
     function extract_xmlfile($origin, $db_connection){
		global $log_file_handle;		
		$xml_file = simplexml_load_file($origin);
		if($xml_file->MeanPublic[0]){ //if there are MeanPublic elements in the file, it means that $xml_file contents data			
		fwrite($log_file_handle,"xml_query_exec");
			$i = 0;	
			
			$table_name = "wb170538";			
			$fields_list = "TimeStamp";
			$values_list = "'" . str_replace('T', ' ', $xml_file->MeanPublic[$i]->TimeStamp). "'";
			
			while($xml_file->MeanPublic[$i]){			
				$key = split(":", $xml_file->MeanPublic[$i]->Key);
				//$cur_table = $key[1];
				//do{
					$key = split(":", $xml_file->MeanPublic[$i]->Key);					
					$fields_list .= ", " . sanitize($key[0]) . "_" . sanitize($key[1]) . "_" . sanitize($key[2]);
					$values_list .= ", " . $xml_file->MeanPublic[$i]->Mean;
					$i++;
					$key = split(":", $xml_file->MeanPublic[$i]->Key);
				//}while($cur_table == $key[1]); 						
			}
			
			$sql_query = 'INSERT INTO ' . $table_name . ' (' . $fields_list . ') VALUES (' .$values_list .')'; 	
			$resultado = sqlsrv_query($db_connection,$sql_query);
			
			if( $resultado === false ) {
				if( ($errors = sqlsrv_errors() ) != null) {
					foreach( $errors as $error ) {
						fwrite($log_file_handle, " - - " . "ERROR: problems with the query: SQLSTATE: ".$error[ 'SQLSTATE']."code: ".$error[ 'code']."message: ".$error[ 'message']);
						fwrite($log_file_handle, $sql_query);
					}
				}
			}
			else{
				fwrite($log_file_handle, " - - " . "REGISTER successfully inserted");
			}
		}
		else if($xml_file->Event[0]){

		}
		else{
			fwrite($log_file_handle, " - - " . "This file is not a 'Mean.' neither 'Log.' file, it was not possible to extract any data");
			return false;		
		}
 		fwrite($log_file_handle, " - - " . "end of the .xml file");
		return true;
     }	
     
     
     /*----------------------------------------------------------*/
	
	/*When this application is thrown using CLI (Command Line Interface), the working directory isn´t where the application is. The working directory is where php-win.exe is. 
	When we use CLI we need to change the working directory to the one where SMAwebBox_dataExtractor.php is
	*/
	chdir(__DIR__);	
	
	//we open the log file, if it doesn´t exist it is created
	$log_file_handle = fopen(getcwd() . "\\log\\" . "log_" . date('Y-m-d') . ".txt", a);

	//we write in the log file that the applicatoin has been launched
	fwrite($log_file_handle,date("[d/M/Y-H:i:s]") . " - - " . "PushFTP application launched\r\n");	
		 
	//we open the directory where the PushFTP function is uploading the files	
	if ($pushFTP_handle = opendir("pushFTP_files")){
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "'pushFTP_files' directory opened\r\n");
	}
	else{
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "ERROR opening 'pushFTP_files' directory\r\n");
	}
	
	//we connect to the database
	$databaseName = "sma";
	$connectionOptions = array("Database"=>$databaseName);
	$db_connection = sqlsrv_connect('nth-server-12\SQLEXPRESS',$connectionOptions);
	
	if (!$db_connection) {
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "Error connecting to SQL Server");
		die('Something went wrong while connecting to MSSQL');
	}
	fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "conected to 'sma' database, user pushFTP\r\n");

	//we start reading the files in the directory and deal with them	
	$process_finished = false;
	while(!$process_finished){
		$filename = readdir($pushFTP_handle);//we read the next file in the directory, if it´s the first time we read the first file
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . $filename . " readed");
		if ($filename === false){//if we are pointing to the end of the directory
			fwrite($log_file_handle, " - - " . "EOF");
			if(morefiles_indir(getcwd() . "\\pushFTP_files")){//we check if there are more files in the directory, if so we point to the beginning of the directory, if not we have finished
				rewinddir($pushFTP_handle);
			}			
			else{
				$process_finished = true;
			}
		}		
		else if (is_dir(getcwd() . "\\pushFTP_files\\" . $filename)){//if we are not pointing to the end of the directory but pointing to a directory we don´t do anything
			fwrite($log_file_handle, " - - " . "is a directory\r\n");		
		}		
		else if(is_file(getcwd() . "\\pushFTP_files\\" . $filename)){//if we are pointing to a file	we check if it is a .zip, .xml or something different
			switch (substr($filename, -4)) {
				case ".zip":
        				fwrite($log_file_handle, " - - " . "is a .zip file");
        				if(!extract_zipfile(getcwd() . "\\pushFTP_files\\" . $filename, getcwd() . "\\pushFTP_files\\")){//if there is a probelm unzipping the file we moved it to the unextracted_files directory
        					if(!move_file(getcwd() . "\\pushFTP_files\\" . $filename, getcwd() . "\\unextractedFILES\\" . $filename)){
							$process_finished = true;//if we can´t move/delete the file we exit the program, otherwise we will be stack in an eternal loop. The application ends when there are not more files in the directory; if we can´t move/delete the files from the directory (and we don´t force the program to finish) there will always be files in there and the program would never end
							}
						}
						else{//if we have succeded unzipping the file, we check if it is the original one uploaded from the webBox (it start wirh 'wb'), in this case we move it to the zip_files directory for keeping the as redundant information (just in case), otherwise we just delete it
							if ($filename[0] == 'w' && $filename[1] == 'b'){						
								if(!move_file(getcwd() . "\\pushFTP_files\\" . $filename, getcwd() . "\\zipFILES\\" . $filename)){
									$process_finished = true;//if we can´t move/delete the file we exit the program, otherwise we will be stack in an eternal loop. The application ends when there are not more files in the directory; if we can´t move/delete the files from the directory (and we don´t force the program to finish) there will always be files in there and the program would never end
								}
							}
							else{
								if(!delete_file(getcwd() . "\\pushFTP_files\\" . $filename)){
									$process_finished = true;//if we can´t move/delete the file we exit the program, otherwise we will be stack in an eternal loop. The application ends when there are not more files in the directory; if we can´t move/delete the files from the directory (and we don´t force the program to finish) there will always be files in there and the program would never end
								}
							}
						}			
	        			break;
    				case ".xml":
    	    			fwrite($log_file_handle, " - - " . " is a .xml file");
    	    			$process_finished = false;
    	    			if(!extract_xmlfile(getcwd() . "\\pushFTP_files\\" . $filename, $db_connection)){//if there are problems extracting/inserting the content of the xml file, we move it to the unextracted_files directory, otherwise we delete it
							if(!move_file(getcwd() . "\\pushFTP_files\\" . $filename, getcwd() . "\\unextractedFILES\\" . $filename)){
								$process_finished = true;//if we can´t move/delete the file we exit the program, otherwise we will be stack in an eternal loop. The application ends when there are not more files in the directory; if we can´t move/delete the files from the directory (and we don´t force the program to finish) there will always be files in there and the program would never end
							}
						}
						else{
							if(!delete_file(getcwd() . "\\pushFTP_files\\" . $filename)){
								$process_finished = true;//if we can´t move/delete the file we exit the program, otherwise we will be stack in an eternal loop. The application ends when there are not more files in the directory; if we can´t move/delete the files from the directory (and we don´t force the program to finish) there will always be files in there and the program would never end
							}
						}			
	        			break;

	    			default:// if the file isn´t a zip or xml file we move it to the unextracted_files directory
		        		fwrite($log_file_handle, " - - " . " is not a directory, a .xml / .zip file or EOF");
						if(!move_file(getcwd() . "\\pushFTP_files\\" . $filename, getcwd() . "\\unextractedFILES\\" . $filename)){
							$process_finished = true;//if we can´t move/delete the file we exit the program, otherwise we will be stack in an eternal loop. The application ends when there are not more files in the directory; if we can´t move/delete the files from the directory (and we don´t force the program to finish) there will always be files in there and the program would never end
						}
						break;
			}
		}
		

	}	

	closedir($pushFTP_handle); // we close the "connection" to the directory
	fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "pushFTP directory closed\r\n");
	
	if(sqlsrv_close($db_connection)){//we close the database connection
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "Connection to database closed\r\n"); 
	}
	else
	{
		fwrite($log_file_handle, date("[d/M/Y-H:i:s]") . " - - " . "ERROR: the connection to the database could not be closed\r\n"); 
	}
	
	fclose($log_file_handle); //we close the log file
	
?> 

